<?php

$menuItems = array(
            array('label' => Yii::t('app', 'Main Menu'), 'url' => array('site/home/')),
        );

$this->widget('MenuList', array(
        'id' => 'main-menu',
        'items' => $menuItems
    ));

foreach($users as $user){
    echo "<span><b>$user</b> | </span>";
}

?>
