<?php

$menuItems = array(
            array('label' => Yii::t('app', 'Main Menu'), 'url' => array('site/home/')),
        );

$this->widget('MenuList', array(
        'id' => 'main-menu',
        'items' => $menuItems
    ));

$usersLength = count ($users);
for ($i = 0; $i < $usersLength; ++$i){
	$user = $users[$i];
	if ($i !== $usersLength - 1)
    	echo "<span><b>$user</b> | </span>";
	else
    	echo "<span><b>$user</b></span>";
}

?>

