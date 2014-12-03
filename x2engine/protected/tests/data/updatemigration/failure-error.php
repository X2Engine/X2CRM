<?php

$failAtMigrating = function() {
    // This SQL is to be successful
    $cmd = Yii::app()->db->createCommand();
    $cmd->setText('CREATE TABLE some_new_table(id INT, name VARCHAR(20));');
    $cmd->execute();

    // Now use an undefined variable to trigger a recoverable error
    echo $testingFailure;
};

$failAtMigrating();
?>
