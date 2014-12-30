<?php
$app =JFactory::getApplication();
if (!$app->isSite()){
	return;
}

class simpleSeblodAPI extends JCckContent{
	public function create( $cck, $data_content, $data_more = null )
	{
		if ( $this->_id ) {
			return;
		}
	
		$this->_type	=	$cck;
		$this->_instance_core	=	JTable::getInstance( $this->_object_table[$this->_object] );
		if ( is_array( $data_more ) ) { // Note: Not All Content Type have extra table
			$this->_instance_more	=	JCckTable::getInstance( '#__cck_store_form_'.$this->_type );
		}
	
		$author_id 		=	0; // TODO : Get default author id
		$parent_id		=	0;
	
		// Set the author_id
		$author_key =	$this->_columns['author'];
	
		if ( isset( $data_content[$author_key] ) ) {
			$author_id	=	$data_content[$author_key];
		} else {
			$user_id	=	JFactory::getUser()->get('id');
				
			if ( $user_id ) {
				$author_id	=	$user_id;
			}
		}
	
		//Set the parent_id
		$parent_key = $this->_columns['parent'];
	
		if ( isset( $data_content[$parent_key] ) ) {
			$parent_id	=	$data_content[$parent_key];
		}
	
		// -------- -------- --------
		if ( !( $this->save( 'core', $data_content ) ) ) {
			return false;
		}
	
		if ( !( $this->save( 'base', array(
				'cck'=>$this->_type,
				'pk'=>$this->_pk,
				'storage_location'=>$this->_object,
				'author_id'=>$author_id,
				'parent_id'=>$parent_id,
				'date_time'=>JFactory::getDate()->toSql()
		) ) ) ) {
			return false;
		}
	
		if ( is_array( $data_more ) ) {
			$this->_instance_more->load( $this->_pk, true );
				
			if ( !( $this->save( 'more', $data_more ) ) ) {
				return false;
			}
	
		}
	
		//TODO : Load instance info
		return $this->_pk;
	}
	
	// load
	public function load( $identifier, $data = true )
	{
		$this->_type	=	'';
		$this->_pk		=	'';
		$this->_id		=	'';
	
		if ( is_array( $identifier ) ) {
			$this->_object	=	$identifier[0];
			$this->_columns	=	$this->_getProperties();
			$this->_instance_core	=	JTable::getInstance( $this->_object_table[$this->_object] );
				
			if( !isset( $identifier[1] ) ) {
				return;
			}
				
			$base	=	JCckDatabase::loadObject( 'SELECT id, cck, pk, storage_location FROM #__cck_core WHERE storage_location = "'.(string)$identifier[0].'" AND pk = '.(int)$identifier[1] );
		} else {
			$base	=	JCckDatabase::loadObject( 'SELECT id, cck, pk, storage_location FROM #__cck_core WHERE id = '.(int)$identifier );
			$this->_object	=	$base->storage_location;
			$this->_columns	=	$this->_getProperties();
			$this->_instance_core	=	JTable::getInstance( $this->_object_table[$this->_object] );
		}
	
		$this->_type	=	$base->cck;
		$this->_pk		=	$base->pk;
		$this->_id		=	$base->id;
		$this->_instance_core->load( $this->_pk );
		$this->_instance_more		=	JCckTable::getInstance( '#__cck_store_form_'.$this->_type );
		$this->_instance_more->load( $this->_id );
			
		if ( !$this->_columns['table'] ) {
			return;
		}
	
		$this->_table	=	$this->_columns['table'];
	
		if ( $data === true ) {
			$this->_properties	=	JCckDatabase::loadObject( 'SELECT a.*, b.* FROM '.$this->_table.' AS a'
					. ' LEFT JOIN #__cck_store_form_'.$this->_type.' AS b ON b.id = a.'.$this->_columns['key']
					. ' WHERE a.'.$this->_columns['key'].' = '.(int)$this->_pk );
				
		} elseif ( is_array( $data ) ) {
			if ( isset( $data[$this->_table] ) ) {
				$select	=	implode( ',', $data[$this->_table] );
				unset( $data[$this->_table] );
			} else {
				$select	=	'*';
			}
			$b	=	'a';
			$i	=	98;
			foreach ( $data as $k=>$v ) {
				$a		=	chr($i);
				$select	.=	', '.$a.'.'.implode( ', '.$a.'.', $v );
				$join	.=	' LEFT JOIN '.$k.' AS '.$a.' ON '.$a.'.id = '.$b.'.'.$this->_columns['key'];
				$b		=	$a;
				$i++;
			}
			$query	=	'SELECT a.'.$select.' FROM '.$this->_table.' AS a'
					.	$join
					.	' WHERE a.'.$this->_columns['key'].' = '.(int)$this->_pk;
			$this->_properties	=	JCckDatabase::loadObject( $query );
		}
		
		return $this->_properties;
	}

}
?>