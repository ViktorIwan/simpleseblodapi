<?php
$app =JFactory::getApplication();
if (!$app->isSite()){
	return;
}

class simpleSeblodAPI extends JCckContent{
	
	
	// create
	public function create( $cck, $data_content, $data_more = null,$data_fieldx=null )
	{
		if ( $this->_id ) {
			return;
		}
		
		$this->_type	=	$cck;
		
		if ( empty( $this->_object ) ) {
			$this->_object		=	JCckDatabaseCache::loadResult( 'SELECT storage_location FROM #__cck_core_types WHERE name = "'.$this->_type.'"' );
			$this->_columns		=	$this->_getProperties();
		}
		
		$this->_instance_core	=	JTable::getInstance( $this->_columns['table_object'][0], $this->_columns['table_object'][1] );
		
		$author_id 		=	0; // TODO: Get default author id
		$parent_id		=	0; // TODO: Get default parent_id
		
		// Set the author_id
		if ( isset( $this->_columns['author'] ) && $this->_columns['author'] && isset( $data_content[$this->_columns['author']] ) ) {
			$author_id	=	$data_content[$this->_columns['author']];
		} else {
			$user_id	=	JFactory::getUser()->get( 'id' );
			
			if ( $user_id ) {
				$author_id	=	$user_id;
			}
		}
		
		// Set the parent_id
		if ( isset( $this->_columns['parent'] ) && $this->_columns['parent'] && isset( $data_content[$this->_columns['parent']] ) ) {
			$parent_id	=	$data_content[$this->_columns['parent']];
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
	
		if ( is_array( $data_more ) && count( $data_more ) ) {
			$this->_instance_more	=	JCckTable::getInstance( '#__cck_store_form_'.$this->_type );
			$this->_instance_more->load( $this->_pk, true );
					
			if ( !( $this->save( 'more', $data_more ) ) ) {
				return false;
			}

		}
		
        /*
        Field X Processing By Default Stored in introtext
        DX-VI
        */
        //Get a db connection.
        	$db = JFactory::getDbo();
        	$query = $db->getQuery(true);
        	$query->select($db->quoteName(array('introtext')));
        	$query->from($db->quoteName('#__content'));
        	$query->where($db->quoteName('id') . '= '. $db->quote($this->_pk));
        	$db->setQuery($query);
        	$result = $db->loadObject();
        	$store=$result->introtext;
        if(is_array($data_fieldx)){
        	//load field data
        	foreach ($data_fieldx as $key => $value) {
        		// Create a new query object.
	        	$query = $db->getQuery(true);
	        	 
	        	// Select all records from the user profile table where key begins with "custom.".
	        	// Order it by the ordering field.
	        	$query->select($db->quoteName(array('extended','storage','storage_location','storage_table','storage_field')));
	        	$query->from($db->quoteName('#__cck_core_fields'));
	        	$query->where($db->quoteName('name') . '= '. $db->quote($key));
	        	 
	        	// Reset the query using our newly populated query object.
	        	$db->setQuery($query);
	        	 
	        	// Load the results as a list of stdClass objects (see later for more options on retrieving data).
	        	$result = $db->loadObject();
	        	$counter=0;
	        	$store.="<br/>::".$key."::".count($value)."::/".$key."::<br/>";
	        	
	        	if(isset($result->extended)){
	        		foreach ($value as $fieldXValue) {
	        			$f_name=$result->extended;
	        			$store	.=	'<br />::'.$f_name.'|'.$counter.'|'.$key.'::'.$fieldXValue.'::/'.$f_name.'|'.$counter.'|'.$key.'::';
	        			$counter++;
	        		}
	        		$store.="<br />";
	        	}
	        	
	        }

        	 // Create a new query object.
        	$query = $db->getQuery(true);
        	
        	// Fields to update.
			$fields = array(
			    $db->quoteName('introtext') . ' = ' . $db->quote($store)			    
			);
			 
			// Conditions for which records should be updated.
			$conditions = array(
			    $db->quoteName('id') . '= '. $db->quote($this->_pk)
			);
			 
			$query->update($db->quoteName('#__content'))->set($fields)->where($conditions);
        	 
        	// Set the query using our newly populated query object and execute it.
        	$db->setQuery($query);
        	$db->execute();

        }

		return $this->_pk;
	}
	
	// load
	public function loadContent( $id )
	{
		
		$this->_columns			=	$this->_getProperties();
	    $this->_instance_core	=	JTable::getInstance( $this->_columns['table_object'][0], $this->_columns['table_object'][1] );	
	    if( !isset( $id) ) {
				return;
			}
		$base					=	JCckDatabase::loadObject( 'SELECT id, cck, pk, storage_location FROM #__cck_core WHERE storage_location = "'.(string)$this->_object.'" AND pk = '.(int)$id);
	    
	    $this->_type				=	$base->cck;
		$this->_pk					=	$base->pk;
		$this->_id					=	$base->id;

		
		if ( !$this->_columns['table'] ) {
			return;
		}
		
		$this->_table	=	$this->_columns['table'];

		$this->_properties	=	JCckDatabase::loadObject( 'SELECT a.*, b.* FROM '.$this->_table.' AS a'
															. ' LEFT JOIN #__cck_store_form_'.$this->_type.' AS b ON b.id = a.'.$this->_columns['key']
															. ' WHERE a.'.$this->_columns['key'].' = '.(int)$this->_pk );
		//look for fieldx
		$this->_fieldx=JCckDatabase::loadObjectlist('SELECT
		c.`name`,
		c.type
		FROM
		aura_cck_core_types AS a
		Left Join aura_cck_core_type_field As b ON b.typeid = a.id
		Left Join aura_cck_core_fields As c ON c.id = b.fieldid
		WHERE
		a.`name` = \''.$base->cck.'\' AND
		c.type = \'field_x\'
		');
   
        if(is_array($this->_fieldx)){
        	$introtext=$this->_properties->introtext;
        	//FOUND FIELD X
        	foreach ($this->_fieldx as $key => $value) {
        		$field_x=$value->name;
        		$pattern = "/::".$field_x."::(.*)::\/".$field_x."::/i";
        		preg_match($pattern, $introtext, $matches);
        		if(count($matches)>0){
        		$total_array=$matches[1];
	        		if($total_array>0){
	        			$this->_properties->{$field_x}=array();
		        		for($i=0;$i<$total_array;$i++){
		        			$pattern="/::.*\|$i\|$field_x::(.*)::\/.*\|$i\|$field_x::/i";	
		        			preg_match($pattern, $introtext, $matches);
		        			$this->_properties->{$field_x}[]=$matches[1];
		        		}
	        		}
        		}

        	}
        }
		
		return($this->_properties);
	}


	//
	function updateContent($id,$data_content=null, $data_more = null,$data_fieldx=null ){
		$this->_columns			=	$this->_getProperties();
	    $this->_instance_core	=	JTable::getInstance( $this->_columns['table_object'][0], $this->_columns['table_object'][1] );	
	    if( !isset( $id) ) {
				return;
			}
		$base					=	JCckDatabase::loadObject( 'SELECT id, cck, pk, storage_location FROM #__cck_core WHERE storage_location = "'.(string)$this->_object.'" AND pk = '.(int)$id);
	    
	    $this->_type				=	$base->cck;
		$this->_pk					=	$base->pk;
		$this->_id					=	$base->id;

		
		if ( !$this->_columns['table'] ) {
			return;
		}

	
		$this->_table	=	$this->_columns['table'];
		$db = JFactory::getDbo();

		//MAIN
		if(count($data_content)>0){
			
        	 // Create a new query object.
        	$query = $db->getQuery(true);
        	
        	// Fields to update.
        	foreach ($data_content as $key => $value) {
        		$fields[]=$db->quoteName($key).'='.$db->quote($value);
        	}

			 
			// Conditions for which records should be updated.
			$conditions = array(
			    $db->quoteName('id') . '= '. $id
			);
			 
			$query->update($db->quoteName($this->_table))->set($fields)->where($conditions);
        	 
         	// Set the query using our newly populated query object and execute it.
         	$db->setQuery($query);
         	$db->execute();
		 }
         
         $fields=null;
         //EXTRA
		 if(count($data_more)>0){
		 	 // Create a new query object.
        	$query = $db->getQuery(true);
        	
        	// Fields to update.
        	foreach ($data_more as $key => $value) {
        		$fields[]=$db->quoteName($key).'='.$db->quote($value);
        	}

			 
			// Conditions for which records should be updated.
			$conditions = array(
			    $db->quoteName('id') . '= '. $id
			);
			
			 $query->update($db->quoteName('#__cck_store_form_'.$this->_type))->set($fields)->where($conditions);
        	 
         	// Set the query using our newly populated query object and execute it.
         	$db->setQuery($query);
         	$db->execute();
		 }

		//FIELDX
		if(is_array($data_fieldx)){
        	//load field data
        	$temp=	JCckDatabase::loadObject( 'SELECT a.introtext FROM '.$this->_table.' AS a'
															. ' WHERE a.'.$this->_columns['key'].' = '.(int)$this->_pk );
        	$introtext=$temp->introtext;
        	
        	foreach ($data_fieldx as $key => $value) {
        		// Create a new query object.
	         	$query = $db->getQuery(true);
	        	 
         	
	         	$query->select($db->quoteName(array('extended','storage','storage_location','storage_table','storage_field')));
	         	$query->from($db->quoteName('#__cck_core_fields'));
	         	$query->where($db->quoteName('name') . '= '. $db->quote($key));
	        	 
	         	// Reset the query using our newly populated query object.
	         	$db->setQuery($query);
	        	 
	        // 	// Load the results as a list of stdClass objects (see later for more options on retrieving data).
	         	$result = $db->loadObject();


	         	$counter=0;
	         	$newstore="::".$key."::".count($value)."::/".$key."::";
	         	$pattern="/::".$key."::[0-9]*::\/".$key."::/i";
	         	//Total
	     	    $introtext=preg_replace($pattern, $newstore, $introtext);

	     	    if(isset($result->extended)){
	     	    	//Delete OldValue
	     	    	$pattern="/::".$result->extended."\|.*".$key."::/i";
	     	    	$introtext=preg_replace($pattern, "", $introtext);
	     	 
	        		foreach ($value as $fieldXValue) {
	        			$f_name=$result->extended;
	        			$store[]=	'<br />::'.$f_name.'|'.$counter.'|'.$key.'::'.$fieldXValue.'::/'.$f_name.'|'.$counter.'|'.$key.'::';
	        			$counter++;
	        		}
	        		$store=implode("<br />", $store);
	        		$introtext.=$store;
	        	}
	        }
	        		
        	$query = $db->getQuery(true);
        	
        	// Fields to update.
			$fields = array(
			    $db->quoteName('introtext') . ' = ' .  $db->quote($introtext)	    
			);
			 
			// Conditions for which records should be updated.
			$conditions = array(
			    $db->quoteName('id') . '= '. $db->quote($this->_pk)
			);
			 
			$query->update($db->quoteName('#__content'))->set($fields)->where($conditions);

        	// Set the query using our newly populated query object and execute it.
        	$db->setQuery($query);
        	$db->execute();
	        	
	    } 
	    return $this->_pk;
	}
}
?>