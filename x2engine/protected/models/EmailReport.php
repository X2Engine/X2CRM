<?php

/**
 * 
 */
class EmailReport extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return EmailReport the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_email_reports';
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => Yii::t('profile','ID'),
            'name' => Yii::t('profile','Name'),
            'user' => Yii::t('profile','User'),
            'cronId' => Yii::t('profile','Cron ID'),
            'schedule' => Yii::t('profile','Schedule'),
        );
    }

    public function relations() {
        return array(
            'cronEvent' => array(self::BELONGS_TO, 'CronEvent', 'cronId'),
        );
    }

}
