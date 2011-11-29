<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SessionChild
 *
 * @author Jake
 */
class SessionChild extends Sessions {
    
    
    public static function getOnlineUsers(){
        $sessions=Sessions::model()->findAll();
        $temp=array();
        foreach($sessions as $session)
            $temp[]=$session->user;
        
        return $temp;
    }
}

?>
