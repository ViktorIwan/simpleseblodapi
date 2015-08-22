<?php
$app =JFactory::getApplication();
if (!$app->isSite()){
	return;
}

class simpleSeblodAPI extends JCckContent{
	
	
	// create
	public function create( $cck, $data_content, $data_more = null)
	{
		$db = JFactory::getDbo();
		if ( $this->_id ) {
			return;
		}

		//if object is joomla_user do validate first
		//login and email must be unique
		if($this->_object=="joomla_user"){
			$username=$data_content['username'];
			$email=$data_content['email'];
			$query = $db->getQuery(true);
			$field=array('id');
			$table='#__users';
        	$query->select($db->quoteName($field));
        	$query->from($db->quoteName($table));
        	$query->where($db->quoteName('username') . '= '. $db->quote($username),'OR');
        	$query->where($db->quoteName('email') . '= '. $db->quote($email));
            $db->setQuery($query);
        	$result = $db->loadObject();
        	if($result){
		   	   return false;
		    }	
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

		return $this->_pk;
	}
	
	// save
	public function save( $instance_name, $data )
	{
		$session =&JFactory::getSession();
		$field_x=$session->get('seblodapi_fieldx');

		
		$updatesession=0;
		if($instance_name!="more"){
			$status	=	$this->{'_instance_'.$instance_name}->bind( $data );
			$status	=	$this->{'_instance_'.$instance_name}->check();
			$status	=	$this->{'_instance_'.$instance_name}->store();
		}else{			
			$db = JFactory::getDbo();
			foreach ($data as $key => $value) {
				if($key=="usergroup" && $this->_object=="joomla_user"){
					//Check IF Exist
					$uid=$this->_instance_core->id;
					$field=array('user_id','group_id');
					$table='#__user_usergroup_map';
		        	$query = $db->getQuery(true);
		        	$query->select($db->quoteName($field));
		        	$query->from($db->quoteName($table));
		        	$query->where($db->quoteName('user_id') . '= '. $db->quote($uid));
		        	$db->setQuery($query);
		        	$result = $db->loadObject();
		        	if($result){ //if found then update
		        		$query = $db->getQuery(true);
						// Fields to update.
						$fields = array(
						    $db->quoteName('user_id') . ' = ' .  $db->quote($uid),
						    $db->quoteName('group_id') . '= '. $db->quote($value)	    
						);
						// Conditions for which records should be updated.
						$conditions = array(
						    $db->quoteName('user_id') . '= '. $db->quote($uid)
						);
						$query->update($db->quoteName($table))->set($fields)->where($conditions);
				    	// Set the query using our newly populated query object and execute it.
				    	$db->setQuery($query);
				    	$db->execute();
		        	}else{
		        		$query = $db->getQuery(true);
						// Fields to update.
						$fields = array('user_id','group_id');
						$values = array($uid,$value);
						    
						$query->insert($db->quoteName($table))
						->columns($db->quoteName($fields))
						->values(implode(',',$values));
				    	// Set the query using our newly populated query object and execute it.
				    	$db->setQuery($query);
				    	$db->execute();
		        	}
				}
				
				$store="";
				if(is_array($value)){
					//Get a db connection.
					    if(!isset($field_x[$key])){
				        	$query = $db->getQuery(true);
				        	$query->select($db->quoteName(array('extended','storage_table','storage_field')));
				        	$query->from($db->quoteName('#__cck_core_fields'));
				        	$query->where($db->quoteName('type') . '= '. $db->quote('field_x'));
				        	$query->where($db->quoteName('name') . '= '. $db->quote($key)); 
				        	$db->setQuery($query);
				        	$result = $db->loadObject();
				        	$updatesession=1;
			        	}else{
			        		$result = new stdClass();
   							$result->extended = $field_x[$key]['extended'];
   							$result->storage_table = $field_x[$key]['storage_table'];
   							$result->storage_field = $field_x[$key]['storage_field'];

			        	}
						if($result){
							$field_x[$key]= array(
							"extended"=>$result->extended ,
							"storage_table"=>$result->storage_table ,
							"storage_field"=>$result->storage_field  
							);
							$counter=0;
							$store.="<br />::".$key."::".count($value)."::/".$key."::<br />";
							if(isset($result->extended)){
				        		foreach ($value as $fieldXValue) {
				        			$f_name=$result->extended;
				        			$store	.=	'<br />::'.$f_name.'|'.$counter.'|'.$key.'::'.$fieldXValue.'::/'.$f_name.'|'.$counter.'|'.$key.'::';
				        			$counter++;
				        		}
				        		$store.="<br />";
				        	}
				        	if($field_x[$key]['storage_table']!="#__content"){
				        	//Replace array to custom format
				        		$data[$result->storage_field]=$store;
				        	}else{
				        		//DO UPDATE INSTEAD
				        		$id=$this->_instance_core->id;
				        		$table=$field_x[$key]['storage_table'];
				        		$storagefield=$field_x[$key]['storage_field'];
				        		$extendedfield=$field_x[$key]['extended'];
				        		$cckfield=$key;
				        		$totalData=count($value);
				        		$value=$store;
				        		$check_update=$this->update_fieldX($id,$table,$extendedfield,$storagefield,$cckfield,$totalData,$value);
				        	} 				        	
						}
    	

						
					
				}
			}
			if($updatesession==1){
				$session->set('seblodapi_fieldx',$field_x);
			}

			//STORE
		    $status	=	$this->{'_instance_'.$instance_name}->bind( $data );
			$status	=	$this->{'_instance_'.$instance_name}->check();
			$status	=	$this->{'_instance_'.$instance_name}->store();

		}
		if ( $status ) {
			switch( $instance_name ) {
				case 'base':
					$this->_id	=	$this->{'_instance_'.$instance_name}->id;
					if ( property_exists( $this->_instance_core, $this->_columns['custom'] ) ) {
						$this->_instance_core->{$this->_columns['custom']}	=	'::cck::'.$this->_id.'::/cck::';
					}
					$this->store( 'core' );
					break;
				case 'core':
					$this->_pk	=	$this->{'_instance_'.$instance_name}->id;
					break;
				case 'more':
					break;
			}
		}
		
		return $status;
	}

	 function update_fieldX($id,$table,$extendedfield,$storagefield,$cckfield,$totalData,$value){
    	$db = JFactory::getDbo();
    	$query='SELECT a.'.$storagefield.' FROM '.$table.' AS a WHERE a.id = '.(int)$id;
    	
    	//load field data
        $temp=	JCckDatabase::loadObject($query);
        $thefielddata=$temp->{$storagefield};
        //Delete Old Total
        $pattern="/<br \/>::".$cckfield."::[0-9]*::\/".$cckfield."::/i";
	    $thefielddata=preg_replace($pattern, "", $thefielddata);

	    //Delete Old Data
    	$pattern="/<br \/>::".$extendedfield."\|.*".$cckfield."::/i";
    	$thefielddata=preg_replace($pattern, "", $thefielddata);
    	//Assign New Total and Data
    	if(is_array($value)){
    		$newstore="<br />::".$cckfield."::".$totalData."::/".$cckfield."::";
    		$thefielddata.=$newstore;
    		$counter=0;
    		foreach ($value as $fieldXValue) {
	        			$f_name=$extendedfield;
	        			$store[]=	'::'.$f_name.'|'.$counter.'|'.$cckfield.'::'.$fieldXValue.'::/'.$f_name.'|'.$counter.'|'.$cckfield.'::';
	        			$counter++;
	        		}
	        		$store=implode("<br />", $store);
	        $thefielddata.="<br />".$store;
    	}else{
    		$thefielddata.=$value;
    	}
    	$thefielddata=str_replace("<br /><br />", "", $thefielddata);
    	// Create a new query object.
    	$query = $db->getQuery(true);
    	
		// Fields to update.
		$fields = array(
		    $db->quoteName($storagefield) . ' = ' .  $db->quote($thefielddata)	    
		);
		 
		// Conditions for which records should be updated.
		$conditions = array(
		    $db->quoteName('id') . '= '. $db->quote($id)
		);
		 
		$query->update($db->quoteName($table))->set($fields)->where($conditions);

    	// Set the query using our newly populated query object and execute it.
    	$db->setQuery($query);
    	$db->execute();
	
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
		c.type,
		c.extended,
		c.storage_table,
		c.storage_field
		FROM
		aura_cck_core_types AS a
		Left Join aura_cck_core_type_field As b ON b.typeid = a.id
		Left Join aura_cck_core_fields As c ON c.id = b.fieldid
		WHERE
		a.`name` = \''.$base->cck.'\' AND
		c.type = \'field_x\'
		');

        if(is_array($this->_fieldx)){
        	
        	//FOUND FIELD X
        	foreach ($this->_fieldx as $key => $value) {
        		
        		$data=$this->_properties->{$value->storage_field};
        		

        		$field_x=$value->name;
        		$pattern = "/::".$field_x."::(.*)::\/".$field_x."::/i";
        		preg_match($pattern, $data, $matches);


        		if(count($matches)>0){
	        		$total_array=$matches[1];
	        		if($total_array>0){
	        			$this->_properties->{$field_x}=array();
		        		for($i=0;$i<$total_array;$i++){
		        			$pattern="/::.*\|$i\|$field_x::(.*)::\/.*\|$i\|$field_x::/i";	
		        			preg_match($pattern, $data, $matches);
		        			$this->_properties->{$field_x}[]=$matches[1];
		        		}
	        		}
        		}

        	}
        }

        if($this->_object=="joomla_user"){
        	$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$field=array('group_id');
			$table='#__user_usergroup_map';
        	$query->select($db->quoteName($field));
        	$query->from($db->quoteName($table));
        	$query->where($db->quoteName('user_id') . '= '. $db->quote($id));
            $db->setQuery($query);
        	$result = $db->loadObject();
        	if($result){
		   	   $this->_properties->usergroup=$result->group_id;
		    }	
		}
		
		return($this->_properties);
	}

   

	//
	function updateContent($id,$data_content=null, $data_more = null){
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
        	$updatesession=0;
        	// Fields to update.

        	foreach ($data_more as $key => $value) {
        		//usergroup process
        		if($this->_object=="joomla_user"&&$key=="usergroup"){
					//Check IF Exist
					$uid=$id;
					$table='#__user_usergroup_map';
	        		$query = $db->getQuery(true);
					// Fields to update.
					$fields2 = array(
					    $db->quoteName('user_id') . ' = ' .  $db->quote($uid),
					    $db->quoteName('group_id') . '= '. $db->quote($value)	    
					);
					// Conditions for which records should be updated.
					$conditions2 = array(
					    $db->quoteName('user_id') . '= '. $db->quote($uid)
					);
					$query->update($db->quoteName($table))->set($fields2)->where($conditions2);
			    	// Set the query using our newly populated query object and execute it.
			    	$db->setQuery($query);
			    	$db->execute();

				}


				//FIELDX OR NOT
        		if(!is_array($value)){
        			if(!($this->_object=="joomla_user"&&$key=="usergroup")){
        				$fields[]=$db->quoteName($key).'='.$db->quote($value);
        			}
        		}else{
        			$session =&JFactory::getSession();
					$field_x=$session->get('seblodapi_fieldx');
					//Get a db connection.
				    if(!isset($field_x[$key])){
			        	$query = $db->getQuery(true);
			        	$query->select($db->quoteName(array('extended','storage_table','storage_field')));
			        	$query->from($db->quoteName('#__cck_core_fields'));
			        	$query->where($db->quoteName('type') . '= '. $db->quote('field_x'));
			        	$query->where($db->quoteName('name') . '= '. $db->quote($key)); 
			        	$db->setQuery($query);
			        	$result = $db->loadObject();
			        	$updatesession=1;
		        	}else{
		        		$result = new stdClass();
							$result->extended = $field_x[$key]['extended'];
							$result->storage_table = $field_x[$key]['storage_table'];
							$result->storage_field = $field_x[$key]['storage_field'];

		        	}
		        	$store="";
		        	if($result){
							$field_x[$key]= array(
							"extended"=>$result->extended ,
							"storage_table"=>$result->storage_table ,
							"storage_field"=>$result->storage_field  
							);
							$counter=0;
							$store.="<br />::".$key."::".count($value)."::/".$key."::<br />";
							if(isset($result->extended)){
				        		foreach ($value as $fieldXValue) {
				        			$f_name=$result->extended;
				        			$store	.=	'<br />::'.$f_name.'|'.$counter.'|'.$key.'::'.$fieldXValue.'::/'.$f_name.'|'.$counter.'|'.$key.'::';
				        			$counter++;
				        		}
				        		$store.="<br />";
				        	}
				        	
				        		//DO UPDATE INSTEAD
				        		$table=$field_x[$key]['storage_table'];
				        		$storagefield=$field_x[$key]['storage_field'];
				        		$extendedfield=$field_x[$key]['extended'];
				        		$cckfield=$key;
				        		$totalData=count($value);
				        		$value=$store;
				        		$check_update=$this->update_fieldX($id,$table,$extendedfield,$storagefield,$cckfield,$totalData,$value);	        	
						}
					if($updatesession==1){
						$session->set('seblodapi_fieldx',$field_x);
					}
        		}
        		
        	}

			 $query = $db->getQuery(true);
			// Conditions for which records should be updated.
			$conditions = array(
			    $db->quoteName('id') . '= '. $id
			);
			
			 $query->update($db->quoteName('#__cck_store_form_'.$this->_type))->set($fields)->where($conditions);
		
         	// Set the query using our newly populated query object and execute it.
         	$db->setQuery($query);
         	$db->execute();
		 }

	    return $this->_pk;
	}


}
?>