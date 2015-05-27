Simpleseblodapi
===============
Version: 1.1
Last Update: 28 Mei 2015
Change Log:
^ Compatible with Seblod 3.6.x & Joomla 3.4.1
+ Support FieldX

This is a Tweak of JCCKContent that result Simple Api for SEBLOD Developer to integrate seblod with 3rd Party Component/Plugin/Module


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
- 'joomla_article' // Related with main content (#__content)
- 'joomla_category' // Related with main category (#__category)
- 'joomla_user' // Related with user system (#__user)

## Insert Seblod Content
```
$status     =   $content->create(
                 $contentTypeName,
                 $mainField,
                 $customField,
                 $fieldXField 
             );            
```
*$contentTypeName* - String, the name of content type
*$mainField* - Array, the field in core joomla
*$customField* - Array, the Additional field made by seblod
*$fieldXField* - Array, the Additional fieldX made by seblod

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
                 ),
                 array(
                     'dx_member_skill_x'=>array("PHP","MySQL")
                 )
             );
```
note: 'dx_member_skill_x' is a field_x in a content type

## Update Seblod Content
$status     =   $content->create(
                 $id,
                 $mainField,
                 $customField,
                 $fieldXField 
             );            
```
*$id* - content id (taken from #__content)
*$mainField* - Array, the field in core joomla
*$customField* - Array, the Additional field made by seblod
*$fieldXField* - Array, the Additional fieldX made by seblod

return value - ID of updated content

Sample Usage:
$id=11;
$mainField=array(
    'title'=>'Victor Doxa',
    'state'=>'1',
    );
$customField=array(
    'member_position'=>'Developer',
    'motto'=>'Never Give Up'  
    );
$fieldXField=array(
    'dx_member_skill_x'=>array("HTML/JS","Copywriting","JQuery")
    );
$data=$content->updateContent(
     $id,
     $mainField,
     $customField,
     $fieldXField);



## Load Seblod Content
```
$data=$content->load($id);
```
*$id* is the ID of content

return value - Content Type's object with all properties


## Road Plan
- Render data in json and custom format
- GroupX Support