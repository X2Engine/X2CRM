<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/






Yii::import ('application.models.*');

class MigrateGoogleCredentials extends X2DbTestCase {
    
    protected static $skipAllTests = true;

    private static $_oldSchema;
    private static $_oldAdmin;

    private static $_testSchema = "
CREATE TABLE `x2_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timeout` int(11) DEFAULT NULL,
  `webLeadEmail` varchar(255) DEFAULT NULL,
  `webLeadEmailAccount` int(11) NOT NULL DEFAULT '-1',
  `webTrackerCooldown` int(11) DEFAULT '60',
  `enableWebTracker` tinyint(4) DEFAULT '1',
  `currency` varchar(3) DEFAULT NULL,
  `chatPollTime` int(11) DEFAULT '2000',
  `locationTrackingFrequency` int(11) DEFAULT '60',
  `defaultTheme` int(11) DEFAULT NULL,
  `ignoreUpdates` tinyint(4) DEFAULT '0',
  `rrId` int(11) DEFAULT '0',
  `leadDistribution` varchar(255) DEFAULT NULL,
  `onlineOnly` tinyint(4) DEFAULT NULL,
  `actionPublisherTabs` text,
  `disableAutomaticRecordTagging` tinyint(4) DEFAULT '0',
  `emailBulkAccount` int(11) NOT NULL DEFAULT '-1',
  `emailNotificationAccount` int(11) NOT NULL DEFAULT '-1',
  `emailFromName` varchar(255) NOT NULL DEFAULT 'X2Engine',
  `emailFromAddr` varchar(255) NOT NULL DEFAULT '1@1.com',
  `emailBatchSize` int(11) NOT NULL DEFAULT '200',
  `emailInterval` int(11) NOT NULL DEFAULT '60',
  `emailCount` int(11) NOT NULL DEFAULT '0',
  `emailStartTime` bigint(20) DEFAULT NULL,
  `emailUseSignature` varchar(5) DEFAULT 'user',
  `emailSignature` text,
  `emailType` varchar(20) DEFAULT 'mail',
  `emailHost` varchar(255) DEFAULT NULL,
  `emailPort` int(11) DEFAULT '25',
  `emailUseAuth` varchar(5) DEFAULT 'user',
  `emailUser` varchar(255) DEFAULT NULL,
  `emailPass` varchar(255) DEFAULT NULL,
  `emailSecurity` varchar(10) DEFAULT NULL,
  `enableColorDropdownLegend` tinyint(4) DEFAULT '0',
  `enforceDefaultTheme` tinyint(4) DEFAULT '0',
  `installDate` bigint(20) NOT NULL,
  `updateDate` bigint(20) NOT NULL,
  `updateInterval` int(11) NOT NULL DEFAULT '0',
  `quoteStrictLock` tinyint(4) DEFAULT NULL,
  `locationTrackingSwitch` tinyint(4) DEFAULT NULL,
  `googleIntegration` tinyint(4) DEFAULT NULL,
  `googleClientId`           VARCHAR(255),
  `googleClientSecret`       VARCHAR(255),
  `googleAPIKey`           VARCHAR(255),
  `inviteKey` varchar(255) DEFAULT NULL,
  `workflowBackdateWindow` int(11) NOT NULL DEFAULT '-1',
  `workflowBackdateRange` int(11) NOT NULL DEFAULT '-1',
  `workflowBackdateReassignment` tinyint(4) NOT NULL DEFAULT '1',
  `unique_id` varchar(32) NOT NULL DEFAULT 'none',
  `edition` varchar(10) NOT NULL DEFAULT 'opensource',
  `serviceCaseEmailAccount` int(11) NOT NULL DEFAULT '-1',
  `serviceCaseFromEmailAddress` text,
  `serviceCaseFromEmailName` text,
  `serviceCaseEmailSubject` text,
  `serviceCaseEmailMessage` text,
  `srrId` int(11) DEFAULT '0',
  `sgrrId` int(11) DEFAULT '0',
  `serviceDistribution` varchar(255) DEFAULT NULL,
  `serviceOnlineOnly` tinyint(4) DEFAULT NULL,
  `corporateAddress` text,
  `eventDeletionTime` int(11) DEFAULT NULL,
  `eventDeletionTypes` text,
  `properCaseNames` int(11) DEFAULT '1',
  `contactNameFormat` varchar(255) DEFAULT NULL,
  `gaTracking_public` varchar(20) DEFAULT NULL,
  `gaTracking_internal` varchar(20) DEFAULT NULL,
  `sessionLog` tinyint(4) DEFAULT '0',
  `userActionBackdating` tinyint(4) DEFAULT '0',
  `historyPrivacy` varchar(20) DEFAULT 'default',
  `batchTimeout` int(11) DEFAULT '300',
  `locationTrackingDistance` int(11) DEFAULT '1',
  `massActionsBatchSize` int(11) DEFAULT '10',
  `externalBaseUrl` varchar(255) DEFAULT NULL,
  `externalBaseUri` varchar(255) DEFAULT NULL,
  `appName` varchar(255) DEFAULT NULL,
  `appDescription` varchar(255) DEFAULT NULL,
  `x2FlowRespectsDoNotEmail` tinyint(4) DEFAULT '0',
  `doNotEmailPage` longtext,
  `doNotEmailLinkText` varchar(255) DEFAULT NULL,
  `twitterCredentialsId` int(10) unsigned DEFAULT NULL,
  `twitterRateLimits` text,
  `triggerLogMax` int(10) unsigned DEFAULT '1000000',
  `googleCredentialsId` int(10) unsigned DEFAULT NULL,
  `imapPollTimeout` int(11) DEFAULT '10',
  `emailDropbox` text,
  `appliedPackages` text,
  `api2` text,
  `accessControlMethod` varchar(15) DEFAULT 'blacklist',
  `ipWhitelist` text,
  `ipBlacklist` text,
  `loginTimeout` int(11) DEFAULT '900',
  `failedLoginsBeforeCaptcha` int(11) DEFAULT '5',
  `maxFailedLogins` int(11) DEFAULT '100',
  `maxLoginHistory` int(11) DEFAULT '5000',
  `enableFingerprinting` tinyint(4) DEFAULT '1',
  `identityThreshold` int(11) DEFAULT '13',
  `maxAnonContacts` int(11) DEFAULT '5000',
  `maxAnonActions` int(11) DEFAULT '10000',
  `performHostnameLookups` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `twitterCredentialsId` (`twitterCredentialsId`),
  KEY `googleCredentialsId` (`googleCredentialsId`),
  CONSTRAINT `x2_admin_ibfk_1` FOREIGN KEY (`twitterCredentialsId`) REFERENCES `x2_credentials` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `x2_admin_ibfk_2` FOREIGN KEY (`googleCredentialsId`) REFERENCES `x2_credentials` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8";

    public $adminFixture = array (
        'admin' => array ('Admin', '.MigrateGoogleCredentials'),
    );

    /**
     * Set up a mid-update 5.2.1 admin table schema 
     */
    public static function setUpBeforeClass () {
        $results = Yii::app()->db->createCommand ("  
            show create table x2_admin;
        ")->query ()->readAll ();
        self::$_oldSchema = $results[0]['Create Table'];
        $admin = Yii::app()->db->createCommand ("  
            select * from x2_admin where id=1
        ")->queryRow ();
        self::$_oldAdmin = $admin;

        return parent::setUpBeforeClass ();
    }

    /**
     * Set up a mid-update 5.2.1 admin table schema 
     */
    public function setUp () {
        Yii::app()->db->createCommand ("  
            drop table x2_admin;
        ")->execute ();
        Yii::app()->db->createCommand (self::$_testSchema)->execute ();
        Yii::app()->db->schema->refresh ();

        if (self::$loadFixturesForClassOnly)
            $this->getFixtureManager ()->loadFixtures = true;

        $fm = Yii::app()->getComponent('fixture');
        $fm->load($this->adminFixture);
        $ret = parent::setUp ();
    }

    /**
     * Reinstate old admin schema
     */
    public function tearDown () {
        Yii::app()->db->createCommand ("  
            drop table x2_admin;
        ")->execute ();
        Yii::app()->db->createCommand (self::$_oldSchema)->execute ();
        Yii::app()->db->schema->refresh ();
        Yii::app()->db->createCommand ()->insert ('x2_admin', self::$_oldAdmin);
        return parent::tearDown ();
    }

    /**
     * Runs migration script 
     * Asserts that pre-5.0 reports were correctly migrated to post-5.0 reports
     */
    public function testMigrationScript () {
        list ($oldAdmin, $admin) = $this->runMigrationScript ();

        $credentials = Yii::app()->db->createCommand ("
            select * from x2_credentials where id=:id
        ")->queryRow (true, array (
            ':id' => $admin['googleCredentialsId']
        ));
        $this->assertTrue ((bool) $credentials);

        // ensure that values were correctly migrated and encrypted
        $key = Yii::app()->basePath.'/config/encryption.key';
        $iv = Yii::app()->basePath.'/config/encryption.iv';
        EncryptedFieldsBehavior::setup($key, $iv);
        $encryption = EncryptedFieldsBehavior::$encryption;
        $creds = CJSON::decode ($encryption->decrypt ($credentials['auth']));
        $this->assertEquals ($oldAdmin['googleClientId'], $creds['clientId']);
        $this->assertEquals ($oldAdmin['googleClientSecret'], $creds['clientSecret']);

        // sanity check 
        $model = Yii::app()->settings;
        $model->refresh ();
        $creds = $model->getGoogleIntegrationCredentials ();
        $this->assertEquals ($oldAdmin['googleClientId'], $creds['clientId']);
        $this->assertEquals ($oldAdmin['googleClientSecret'], $creds['clientSecret']);

    }

    /**
     * Ensure that encryption can be initialized if iv/key files don't already exist.
     */
    public function testMigrationWithEncryptionSetup () {
        $this->assertTrue (unlink (Yii::app()->basePath."/config/encryption.iv"));
        $this->assertTrue (unlink (Yii::app()->basePath."/config/encryption.key"));
        $this->assertFalse (file_exists (Yii::app()->basePath."/config/encryption.iv"));
        $this->assertFalse (file_exists (Yii::app()->basePath."/config/encryption.key"));
        $this->testMigrationScript ();
        $this->assertTrue (file_exists (Yii::app()->basePath."/config/encryption.iv"));
        $this->assertTrue (file_exists (Yii::app()->basePath."/config/encryption.key"));
    }

   /**
    * Ensure that migration script functions as expected when encryption requirements
    * aren't met or encryption fails.
    */
//    public function testMigrationWithEncryptionFailure () {
//        $this->assertTrue (unlink (Yii::app()->basePath."/config/encryption.iv"));
//        $this->assertTrue (unlink (Yii::app()->basePath."/config/encryption.key"));
//        $this->assertFalse (file_exists (Yii::app()->basePath."/config/encryption.iv"));
//        $this->assertFalse (file_exists (Yii::app()->basePath."/config/encryption.key"));
//        list ($oldAdmin, $admin) = $this->runMigrationScript ();
//
//        $credentials = Yii::app()->db->createCommand ("
//            select * from x2_credentials where id=:id
//        ")->queryRow (true, array (
//            ':id' => $admin['googleCredentialsId']
//        ));
//        $this->assertFalse ((bool) $credentials);
//    }

    private function runMigrationScript () {
        Yii::app()->db->createCommand ("
            delete from x2_credentials where 1;
        ")->execute ();

        // ensure that obsolete columns are in test table
        $oldAdmin = Yii::app()->db->createCommand ("
            select * from x2_admin where id=1;
        ")->queryRow ();
        $this->assertTrue (isset ($oldAdmin['googleClientId']));
        $this->assertTrue (isset ($oldAdmin['googleClientSecret']));
        $this->assertTrue (isset ($oldAdmin['googleAPIKey']));

        // run migration script
        $command = Yii::app()->basePath . '/yiic runmigrationscript ' .
            'migrations/5.3/1444104573-migrate-google-credentials.php';
        $return_var;
        $output = array ();
        if (X2_TEST_DEBUG_LEVEL > 1) 
            X2_TEST_DEBUG_LEVEL > 1 && print_r (exec ($command, $return_var, $output));
        else 
            exec ($command, $return_var, $output);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($return_var);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($output);

        // ensure that obsolete columns have been removed
        $admin = Yii::app()->db->createCommand ("
            select * from x2_admin where id=1;
        ")->queryRow ();
        $this->assertFalse (in_array ('googleClientId', array_keys ($admin)));
        $this->assertFalse (in_array ('googleClientSecret', array_keys ($admin)));
        $this->assertFalse (in_array ('googleAPIKey', array_keys ($admin)));

        return array ($oldAdmin, $admin);
    }

}


?>
