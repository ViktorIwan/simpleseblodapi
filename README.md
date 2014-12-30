Simpleseblodapi
===============
Version: 0.1
Last Update: 30 December 2014

Simple Api for SEBLOD Developer to integrate seblod with 3rd Party Component/Plugin/Module


# What It Is ?
The main idea for this add-on is to create an easy to use interface between seblod and 3rd party component or custom module inside Joomla CMS

# My Dream Goal
Seblod is a great tools for joomla, the connection really like bread and butter. The Big Dream of this simple api would be create an interface through standalone apps so developer can input data, retrieve data through independent apps as well.

# Guide
## Init
```
$content =   new simpleSeblodAPI(array(objectType));
```
*objectType* is the main Object of Seblod, One of the following:
- joomla_article // Related with main content (#__content)
- joomla_category // Related with main category (#__category)
- joomla_user // Related with user system (#__user)

## Insert Seblod Content
```
$status     =   $content->create(
                 $contentTypeName,
                 $mainField,
                 $customField 
             );            
```
*$contentTypeName* - String, the name of content type
*$mainField* - Array, the field in core joomla
*$customField* - Array, the Additional field made by seblod

return value - ID of new content

For Example:
```
$status     =   $content->create(
                 'staff',
                 array(
                     'title'=>"Jaya P",
                     'catid'=>10,
                     'state'=>1,
                     'language'=>'*',
                     'publish_up'=>JFactory::getDate()->toSql(),
                     'created_by'=> 943
                 ),
                 array(
                 	'staff_level'="Manager",
                 	'staff_pic'="Albert.jpg",
                 )
             );
```

## Load Seblod Content
```
$data=$content->load($id);
```
*$id* is the ID of content

return value - Content Type's object


## To Do List
- Make the api able to render custom/json format
- fieldX and groupX support