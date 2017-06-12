<?php
return array(
    'excluded' => array(
        'id' => '38',
        'listId' => '22',
        'type' => 'attribute',
        'attribute' => 'email',
        'comparison' => 'contains',
        'value' => 'mailinator.com',
    ),
    'testMultiple' => array(
        'id' => '39',
        'listId' => '22',
        'type' => 'attribute',
        'attribute' => 'c_dropdown',
        'comparison' => 'list',
        'value' => 'First Option,SecondOption',
    ),
    'testSingle' => array(
        'id' => '40',
        'listId' => '22',
        'type' => 'attribute',
        'attribute' => 'c_dropdown',
        'comparison' => 'notList',
        'value' => 'SecondOption',
    ),
    'testNone' => array(
        'id' => '41',
        'listId' => '22',
        'type' => 'attribute',
        'attribute' => 'c_dropdown',
        'comparison' => 'list',
        'value' => '',
    ),
);
?>
