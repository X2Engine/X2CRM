#YiiChat

The most simple jQuery based chat in the world for Yii based applications. Not dependent on any data model, it is an abstract chat component, it will connects to a class who implements a required interface. You can provide your own class or mechanism to provide real data to this chat, but by default it comes with a read-for-use database handler.

![yiichat screenshot](http://yiiframeworkenespanol.org/wiki/images/6/6b/Yiichat.jpg "Yiichat Screenshot")

##Basic Installation

1) Clone it using GIT. Or perform a direct download from [repository download](https://bitbucket.org/christiansalazarh/yiichat "repository")
~~~
$ cd /home/yourapp/protected/extensions
$ git clone https://bitbucket.org/christiansalazarh/yiichat.git
~~~

2) In config/main, in your imports, add:
~~~
'imports'=>array(
        ..bla..
        'application.extensions.yiichat.*',
    ),
~~~

3) Edit your file: 'protected/controllers/siteController.php' and add the static action int actions() array. This step is required ONCE no matter if your application inserts more than one yiichat widgets.
~~~
class SiteController extends Controller
{
    public function actions()
    {
        return array(
            'captcha'=>array(
                'class'=>'CCaptchaAction',
                'backColor'=>0xFFFFFF,
            ),
            'page'=>array(
                'class'=>'CViewAction',
            ),
            'yiichat'=>array('class'=>'YiiChatAction'), // <- ADD THIS LINE
        );
    }
 ..bla..
}
~~~

4) Setup widget. IMPORTANT: please note the 'model'=>new MyYiiChatHandler(). this is a *demo handler* to shows you the minimal requirements for provide demo data to this chat. It uses no database. For database please read about: "Using Yii Chat with a Database" (next topic).
~~~
<h1>Yii Chat Demo</h1>
<div id='chat'></div>
<?php 
    $this->widget('YiiChatWidget',array(
        'chat_id'=>'123',                   // a chat identificator
        'identity'=>1,                      // the user, Yii::app()->user->id ?
        'selector'=>'#chat',                // were it will be inserted
        'minPostLen'=>2,                    // min and
        'maxPostLen'=>10,                   // max string size for post
        'model'=>new MyYiiChatHandler(),    // the class handler. **** FOR DEMO, READ MORE LATER IN THIS DOC ****
        'data'=>'any data',                 // data passed to the handler
        // success and error handlers, both optionals.
        'onSuccess'=>new CJavaScriptExpression(
            "function(code, text, post_id){   }"),
        'onError'=>new CJavaScriptExpression(
            "function(errorcode, info){  }"),
    ));
?>
~~~

5) STEP FIVE is in topic: "Using Yii Chat with a Database" only required if you want this widget using the provided database mechanism.

##Using Yii Chat with a Database

1) Create a new file named: "protected/components/ChatHandler.php" having this content:
~~~
<?php
class ChatHandler extends YiiChatDbHandlerBase {
    //
    // IMPORTANT:
    // in any time here you can use this available methods:
    //  getData(), getIdentity(), getChatId()
    //
    protected function getDb(){
        // the application database
        return Yii::app()->db;
    }
    protected function createPostUniqueId(){
        // generates a unique id. 40 char.
        return hash('sha1',$this->getChatId().time().rand(1000,9999));      
    }
    protected function getIdentityName(){
        // find the identity name here
        // example: 
        //  $model = MyPeople::model()->findByPk($this->getIdentity());
        //  return $model->userFullName();
        return "jhonn doe"; 
    }
    protected function getDateFormatted($value){
        // format the date numeric $value
        return Yii::app()->format->formatDateTime($value);
    }
    protected function acceptMessage($message){
        // return true for accept this message. false reject it.
        return true;
    }
}
?>
~~~

2) In your widget setup, change the model attribute, by the following value:
~~~
'model'=>new ChatHandler(), // the class handler using database
~~~

3) In your database, create the yii_chat table, using the following sql script:
(you can import the provided: "post.sql" script provided in the yiichat package)
~~~
/* mysql */
CREATE TABLE `yiichat_post` (
  `id` CHAR(40),
  `chat_id` CHAR(40) NULL ,
  `post_identity` CHAR(40) NULL ,
  `owner` CHAR(20) NULL ,
  `created` BIGINT(30) NULL ,
  `text` BLOB NULL ,
  `data` BLOB NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `yiichat_chat_id` (`chat_id` ASC),  
  INDEX `yiichat_chat_id_identity` (`chat_id` ASC, `post_identity` ASC) 
)ENGINE = InnoDB;
~~~

##Using the ChatHandler.php

The ChatHandler.php file provides an extension for your application, in this file you are required to provide the database connection, create a sequential unique id for each post, etc etc, it serves for you to kwnow about incoming messages.

**Did you see the "jhonn doe" user name always in your chat ?**

This is because you *must* provide the way in how to recognize the user in your own database returning the name in the getIdentityName() method using the getIdentity() value to recognize it. In the widget configuration you pass a "chat_id" and an "identity", this Identity is current user identificator (it can be Yii::app()->user->id).

As an example, when a new message arrives it is catched and sent to your ChatHandler, you can accept or reject it or take your own actions usign the provided acceptMessage() method. 

You can use: getData(), getIdentity() and getChatId() all the time to read the current widget arguments, the same widget can be used by many different users, you recognize them by getIdentity(). If you are using Yiichat in an view common for all your people (leaving behind the rbac issued for now) then the unique way you have in yiichat to check wich user is sending a specific message is reading the $this->getIdentity(), in this way you can difference which user is sending the post.  The chat_id is your business, you are required to provide and connect your chat to your existing model and in consecuence to give a chat_id to this widget. The getData() is the same passed to the widget argument.

##YiiChat Internals

Nerd Zone. Be Aware.

###Data Mechanism Provided by YiiChat

The widget must be configured using a "model" for save and retrieve the messages, this "model" must implements an specific [IYiiChat Interface](https://bitbucket.org/christiansalazarh/yiichat/src/17b8314eac0b6e236cbb69ba1ceb4cd9f68e3f68/IYiiChat.php?at=master "IYiiChat Interface"), by default and for help the developper the yiichat extension comes with a [MyYiiChatHandler.php](https://bitbucket.org/christiansalazarh/yiichat/src/17b8314eac0b6e236cbb69ba1ceb4cd9f68e3f68/MyYiiChatHandler.php?at=master "MyYiiChatHandler.php") who implements this required interface, this demo handler shows you the minimal requirements to implement your own data model.

In adittion, and again, to help the developper to start over, the yiichat comes with another specific class named [YiiChatDbHandlerBase.php](https://bitbucket.org/christiansalazarh/yiichat/src/17b8314eac0b6e236cbb69ba1ceb4cd9f68e3f68/YiiChatDbHandlerBase.php?at=master "YiiChatDbHandlerBase.php"), this class implements the required IYiiChat Interface too, but as different as the demo handler it uses a database to save and restore the messages using an specific [mysql table](https://bitbucket.org/christiansalazarh/yiichat/src/17b8314eac0b6e236cbb69ba1ceb4cd9f68e3f68/post.sql?at=master "mysql table") named yii_post.

_This YiiChatDbHandlerBase.php is an abstract class, so you can't use it directly_, so, in order to give you an extension for your own code and needs you are required to create a new class extending the [YiiChatDbHandlerBase.php](https://bitbucket.org/christiansalazarh/yiichat/src/17b8314eac0b6e236cbb69ba1ceb4cd9f68e3f68/YiiChatDbHandlerBase.php?at=master "YiiChatDbHandlerBase.php"), and, use the provided methods to serve your own application. See also: ChatHandler.php up later in this article.

In resume, you can create any class that implements [IYiiChat Interface](https://bitbucket.org/christiansalazarh/yiichat/src/17b8314eac0b6e236cbb69ba1ceb4cd9f68e3f68/IYiiChat.php?at=master "IYiiChat Interface"), and it can serve the yiichat extension, but for rapid development i create the class [YiiChatDbHandlerBase.php](https://bitbucket.org/christiansalazarh/yiichat/src/17b8314eac0b6e236cbb69ba1ceb4cd9f68e3f68/YiiChatDbHandlerBase.php?at=master "YiiChatDbHandlerBase.php") in were you can extends a new class for you (the ChatHandler.php).

###Rbac and BizRules

Your yii auth mechanism must include a [bizrule](http://www.yiiframework.com/doc/api/1.1/CAuthItem#bizRule-detail "bizrule yii auth") as aditional rbac rule. This is due to the following situation you could have in your application:

Suppose all your users has assigned the role named "CHAT USERS". This role only ensures that they can give access to -the action in a controller- having a yiichat widget view. But, what happens when you must ensure a subset of users chating in one specific "chat_id", denying the others users to give access to this specific chat_id ? This work is done by the [bizrule](http://www.yiiframework.com/doc/api/1.1/CAuthItem#bizRule-detail "bizrule yii auth").

This wiki is not focused in show you how to use a rbac and a bizrule, it is a large theme. The Yiichat widget has no responsability to offer a way to incorporate access control, this work is done by the Yii Authentication, not yiichat.