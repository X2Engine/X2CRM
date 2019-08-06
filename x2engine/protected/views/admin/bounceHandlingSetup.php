<?php
Yii::app()->clientScript->registerScript('manageCredentialsScript', "

    function validate () {
        auxlib.destroyErrorFeedbackBox ($('#class'));
        if ($('#class').val () === '') {
            auxlib.createErrorFeedbackBox ({
                'prevElem': $('#class'),
                'message': '" . Yii::t('app', 'Account type required') . "'
            });
            return false;
        }
        return true;
    }
    function isOptionSelected () {
        auxlib.destroyErrorFeedbackBox ($('#emailSelectorBulk'));
        if ($('#emailSelectorBulk').val () === '') {
            auxlib.createErrorFeedbackBox ({
                'prevElem': $('#emailSelectorBulk'),
                'message': '" . Yii::t('app', 'Account Selection required') . "'
            });
            return false;
        }
        auxlib.pageLoading();
        return true;
    }


", CClientScript::POS_HEAD);
?>

<div class="page-title"><h2><?php echo Yii::t('admin', 'Email Bounce Handling Account Configuration'); ?></h2></div>

<div class="credentials-storage">
    <h4><?php echo Yii::t('admin', 'List of Exisitng Bounce Handling Account in the system'); ?></h4>
    <?php
    $crit = new CDbCriteria(array(
            'condition' => '(userId=:uid OR userId=-1) AND isBounceAccount =1 AND modelClass != "TwitterApp" AND 
        modelClass != "GoogleProject"',
            'order' => 'name ASC',
            'params' => array(':uid' => Yii::app()->user->id),
        )
    );
    $dp = new CActiveDataProvider('Credentials', array(
        'criteria' => $crit,
    ));
    $this->widget('zii.widgets.CListView', array(
        'dataProvider' => $dp,
        'itemView' => '../profile/_credentialsView',
        'itemsCssClass' => 'credentials-list',
        'summaryText' => '',
        'emptyText' => 'There is no Email account setup yet'
    ));
    ?>

    <?php
    echo CHtml::beginForm(
        array('/profile/createUpdateCredentials'), 'get', array(
            'onSubmit' => 'return validate ();'
        )
    );?>
    <h4><?php echo Yii::t('admin', 'Add New Bounce Handling Account'); ?></h4>
    <p><?php echo Yii::t('admin', 'Setup or add more bounce handling account in the system to use for dufferent types of campaigns.'); ?></p>
    <?php
    echo CHtml::submitButton(
        Yii::t('app', 'Add New'), array('class' => 'x2-button', 'style' => 'float:left;margin-top:0'));
    $modelLabels = Credentials::model()->validBouncedModels;
    $types = array_merge(array(null => '- ' . Yii::t('app', 'select a type') . ' -'), $modelLabels);
    echo CHtml::dropDownList(
        'class', 'EmailAccount', $types, array(
            'options' => array_merge(
                array(null => array('selected' => 'selected')), array_fill_keys(array_keys($modelLabels), array('selected' => false))),
            'class' => 'left x2-select'
        )
    );
    echo CHtml::hiddenField('bounced','1');
    echo CHtml::endForm();
    ?>
    <h4><?php echo Yii::t('admin', 'Execute Bounce Handling Account to Update the statistics for Campaigns'); ?></h4>
    <p><?php echo Yii::t('admin', 'Select Bounce Handling Account to traverse email messages for the bounced emails for overall CRM.'); ?></p>
    <div class="row">
        <div class="cell">
            <?php
            echo CHtml::beginForm(
                array('/admin/bounceHandlingSetup'), 'post', array(
                    'onSubmit' => 'return isOptionSelected ();'
                )
            );
            echo Credentials::selectorField($model, 'emailBulkAccount', 'email', Credentials::$sysUseId['bulkEmail'], array('class' => 'email-selector', 'id' => 'emailSelectorBulk'), true, false, true);

            echo CHtml::submitButton(
                Yii::t('app', 'Process Bounce EMail Accounts for Updates'), array('class' => 'x2-button','id' => 'bouncedEmails'));
            echo CHtml::endForm();
            ?>
        </div>
    </div>
</div>
