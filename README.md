Simpleseblodapi
===============
Version: 1.5 (Update on August 2015)

Discussion: http://www.seblod.com/community/forums/general-discussions/simple-seblod-api-by-vic

#Description
This is a Tweak of JCCKContent that result Simple Api for SEBLOD Developer to integrate seblod with 3rd Party Component/Plugin/Module. This is based on our project on managing content type with hundreds of item in a Web Real Estate Project. 


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
                 $customField
             );            
```
*$contentTypeName* - String, the name of content type
*$mainField* - Array, the field in core joomla
*$customField* - Array, the Additional table field in custom table made by seblod

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

## Update Seblod Content
$status     =   $content->create(
                 $id,
                 $mainField,
                 $customField
             );            

*$id* - content id (taken from #__content)
*$mainField* - Array, the field in core joomla
*$customField* - Array, the Additional table field in custom table made by Seblod


return value - ID of updated content

Sample Usage:
```
$id=11;
$mainField=array(
    'title'=>'Victor Doxa',
    'state'=>'1',
    );
$customField=array(
    'member_position'=>'Developer',
    'motto'=>'Never Give Up'  
    );

$data=$content->updateContent(
     $id,
     $mainField,
     $customField);


```
## Load Seblod Content
```
$data=$content->load($id);
```
*$id* is the ID of content

return value - Content Type's object with all properties

Sample Usage:
```
$id=11;
$data=$content->load($id);

```

## Special Paramater
### field_x Support
You can include field_x in array format. For example:
```
$content->create(
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
                    'dx_member_skill_x'=>array("PHP","MySQL")
                 ),
```
note: dx_members_skill_x **is a field_x**
Rules for field_x:
 - value in array
 - use registered CCK Field Name instead of Table Field/Column Name in array's key. **This is very important**
![enter image description here](http://joomlamastery.com/images/simpleapi.png)

### usergroup support
When dealing with user, you can use special (reserved) variable 'usergroup' , to assign a user group. For example:
```
$mainField=array(
    'name'=>'Mario Doxa',
    'username'=>'mariodoxa',
    'email'=>'mario@doxadigital.com',
    'password'=>JUserHelper::hashPassword('mariosukses'),     
    'sendEmail'=> 0,
    'block'=> 0,
    'registerDate'=>JFactory::getDate()->toSql());
 $customField=array(
      'division'=>'Marketing',
      'usergroup'=>'8'
    );
```
Rules for usergroup:
 - Hash the password manually using JUserHelper::hashPassword('mariosukses'), no hashing made inside API
 - Always use 'usergroup' as the array's key
 
## Road Plan
- Render data in json and custom format
- GroupX Support <- the hardest challange

```
## Change Log
Version: 1.5

Last Update 22 August 2015
^ Simplified Format - combine fieldX parameter into custom field
^ FieldX Storage Targeting - It detect fieldX storage beside introtext as well
+ Joomla User - usergroup support - Now when you create new joomla user, you can directly assign usergroup for this user

Version: 1.1
Last Update: 28 Mei 2015
^ Compatible with Seblod 3.6.x & Joomla 3.4.1
+ Support FieldX
```
