<?php
Yii::import('application.commands.*');

/**
 * A test area for executing experimental PHP code inside of a Yii run environment.
 *
 * @package application.commands
 */
class EmailBounceHandlingCommand extends CConsoleCommand
{

    public function run($args)
    {

        if (!extension_loaded('imap')) {
            throw new Exception('Processing requires the PHP IMAP extension.');
        }
        $staticModel = Credentials::model();
        $criteria = new CDbCriteria();
        $criteria->addCondition('disableInbox=0');
        $criteria->addCondition("isBounceAccount=1");
        $credRecords = $staticModel->findAll($criteria);
        
        foreach ($credRecords as $credential) {
            $bouncedBehaviour = new BouncedEmailBehavior();
            $bouncedBehaviour->executeMailbox($credential->id);
        }
    }
}
?>
