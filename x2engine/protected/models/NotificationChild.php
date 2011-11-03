<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NotificationChild
 *
 * @author Jake
 */
class NotificationChild extends Notifications {
    
    
    public static function parseLink($str){
        $pieces=explode(":",$str);
        return CHtml::link('Link',Yii::app()->request->baseUrl.'/index.php/'.lcfirst($pieces[0]).'/'.$pieces[1]);
    }
}

?>
