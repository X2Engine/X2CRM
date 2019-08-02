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






Yii::import ('application.modules.reports.models.*');
Yii::import ('application.components.sortableWidget.*');
Yii::import ('application.components.sortableWidget.dataWidgets.*');

class MigrateReports extends X2DbTestCase {

    // skipped since migration script tests aren't relevant after corresponding release
    protected static $skipAllTests = true;

//    public $fixtures = array (
//    );

    public static function setUpBeforeClass () {
        Yii::app()->db->createCommand ("
            delete
            from x2_reports_2
            where true;
            alter table x2_reports_2 auto_increment=1000;
        ")->execute ();
        // create pre-5.0 reports table and insert saved reports
        Yii::app()->db->createCommand ("
            DROP TABLE IF EXISTS x2_reports;
            CREATE TABLE x2_reports(
                id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                field1     VARCHAR(250),
                field2     VARCHAR(250),
                start      BIGINT,
                end        BIGINT,
                dateRange  VARCHAR(250),
                cellType   VARCHAR(250),
                cellData   VARCHAR(250),
                zero       TINYINT,
                createDate BIGINT,
                createdBy  VARCHAR(250),
                rowPie     TEXT,
                type       VARCHAR(250),
                parameters TEXT
            ) COLLATE = utf8_general_ci;
            INSERT INTO `x2_reports` VALUES (1,'assignedTo','leadSource',1352188800,1354867199,'custom','','',1,1402847232,'admin',NULL,'grid',NULL),(2,NULL,NULL,1293868800,1325404799,'lastYear',NULL,NULL,NULL,1402847233,'admin',NULL,'deal','{\"model\":\"contacts\",\"start\":\"November 6, 2012\",\"end\":\"December 6, 2012\",\"range\":\"lastYear\",\"Contacts\":{\"assignedTo\":\"\",\"leadSource\":\"\",\"company_id\":\"\",\"company\":\"\"},\"yt0\":\"Go\",\"resultsPerPage\":\"100\"}'),(3,'assignedTo','leadSource',1414134000,1416902399,'custom','','',1,1416865041,'admin',NULL,'grid','{\"module\":\"Contacts\",\"start\":\"October 24, 2014\",\"end\":\"Today\",\"range\":\"custom\",\"field1\":\"assignedTo\",\"field2\":\"leadSource\",\"zero\":\"1\",\"cellType\":\"\",\"yt0\":\"Go\"}'),(4,'assignedTo','leadSource',1414134000,1416902399,'custom','sum','dealvalue',1,1416865598,'admin',NULL,'grid','{\"module\":\"Contacts\",\"start\":\"October 24, 2014\",\"end\":\"Today\",\"range\":\"custom\",\"field1\":\"assignedTo\",\"field2\":\"leadSource\",\"zero\":\"1\",\"cellType\":\"sum\",\"cellData\":\"dealvalue\",\"yt0\":\"Go\"}'),(5,NULL,NULL,1414134000,1416902399,'custom',NULL,NULL,NULL,1416870281,'admin',NULL,'deal','{\"model\":\"opportunity\",\"start\":\"October 24, 2014\",\"end\":\"Today\",\"range\":\"custom\",\"strict\":\"1\",\"Opportunity\":{\"assignedTo\":\"bto\",\"leadSource\":\"Google\",\"accountName\":\"Lobsters Direct2U\"},\"Contacts\":{\"company_id\":\"9\"},\"yt0\":\"Go\"}'),(6,NULL,NULL,1412146800,1414825199,'lastMonth',NULL,NULL,NULL,1416870324,'admin',NULL,'deal','{\"model\":\"contacts\",\"start\":\"October 1, 2014\",\"end\":\"October 31, 2014\",\"range\":\"lastMonth\",\"Contacts\":{\"assignedTo\":\"chames\",\"leadSource\":\"\",\"company_id\":\"\",\"company\":\"\"},\"yt0\":\"Go\"}'),(7,NULL,NULL,1414188390,1416870390,'custom',NULL,NULL,NULL,1416870398,'admin',NULL,'deal','{\"model\":\"contacts\"}'),(8,NULL,NULL,0,1416902399,'all',NULL,NULL,NULL,1416870542,'admin',NULL,'deal','{\"model\":\"accounts\",\"start\":\"\",\"end\":\"Today\",\"range\":\"all\",\"Accounts\":{\"assignedTo\":\"\"},\"yt0\":\"Go\"}'),(9,'assignedTo','impact',0,1416902399,'all','','',1,1416870609,'admin',NULL,'grid','{\"module\":\"Services\",\"start\":\"October 24, 2014\",\"end\":\"Today\",\"range\":\"all\",\"field1\":\"assignedTo\",\"field2\":\"impact\",\"zero\":\"1\",\"cellType\":\"\",\"yt0\":\"Go\"}');
        ")->execute ();

        parent::setUpBeforeClass ();
    }

    /**
     * Runs migration script 
     * Asserts that pre-5.0 reports were correctly migrated to post-5.0 reports
     */
    public function testMigrationScript () {
        $oldReports = Yii::app()->db->createCommand ("
            select *
            from x2_reports
        ")->queryAll ();
        $reportMaxId = intval (Yii::app()->db->createCommand ("
            select max(id)
            from x2_reports_2
        ")->queryScalar ());
        if ($reportMaxId < 1000) $reportMaxId = 999;

        // run migration script
        $command = Yii::app()->basePath . '/yiic runmigrationscript ' .
            'migrations/pending/1416863327-migrate-reports.php';
        $return_var;
        $output = array ();
        if (X2_TEST_DEBUG_LEVEL > 1) 
            X2_TEST_DEBUG_LEVEL > 1 && print_r (exec ($command, $return_var, $output));
        else 
            exec ($command, $return_var, $output);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($return_var);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($output);

        $this->assertEquals (2, intval (Yii::app()->db->createCommand ("
            select count(distinct(name))
            from x2_reports_2
            where name='Services Report' or name='Deal Report'
        ")->queryScalar ()));

        foreach ($oldReports as $rep) {
            $rep['parameters'] = json_decode ($rep['parameters'], true);
            $newReport = Reports::model ()->findByPk (++$reportMaxId);
            $this->assertNotNull ($newReport);
            $formModelName = $newReport->getFormModelName ();
            $formModel = new $formModelName;
            $formModel->setAttributes (json_decode ($newReport->settings, true), false);
            $dateRange = array ();
            if ($rep['dateRange'] === 'custom') {
                $dateRange['start'] = $rep['start'];
                $dateRange['end'] = $rep['end'];
            } else {
                $dateRange = X2DateUtil::parseDateRange (
                    $rep['dateRange'], date ('Y-m-d', $rep['start']), date ('Y-m-d', $rep['end']));
                if ($rep['dateRange'] === 'all') {
                    // if end date was generated from time (), ignore it
                    foreach ($formModel->allFilters as &$filter) {
                        if ($filter['operator'] === '<=') {
                            $filter['value'] = null;
                        }
                    }
                    $dateRange['end'] = null;
                }
            }

            if ($rep['type'] === 'grid') {
                $this->assertEquals ($newReport->type, 'grid');
                $this->assertEquals (
                    $formModel->primaryModelType,
                    isset ($rep['parameters']['module']) ? 
                        ucfirst ($rep['parameters']['module']) : 'Contacts');
                $this->assertContains ($formModel->primaryModelType, array (
                    'Contacts', 'Accounts', 'Opportunity', 'Services',
                ));
                $this->assertEquals ($formModel->rowField, $rep['field1']);
                $this->assertEquals ($formModel->columnField, $rep['field2']);
                $this->assertEquals (
                    $formModel->cellDataType, 
                    !empty ($rep['cellType']) ? $rep['cellType'] : 'count');
                if ($formModel->cellDataType !== 'count') {
                    $this->assertEquals ($formModel->cellDataField, $rep['cellData']);
                }
                $this->assertEquals ($formModel->allFilters[0], array (
                    'name' => 'createDate',
                    'operator' => '>=',
                    'value' => $dateRange['start'],
                ));
                $this->assertEquals ($formModel->allFilters[1], array (
                    'name' => 'createDate',
                    'operator' => '<=',
                    'value' => $dateRange['end'],
                ));
            } elseif ($rep['type'] === 'deal') {
                $this->assertEquals ($newReport->type, 'rowsAndColumns');
                $this->assertEquals (
                    $formModel->primaryModelType, ucfirst ($rep['parameters']['model']));
                $this->assertContains ($formModel->primaryModelType, array (
                    'Contacts', 'Accounts', 'Opportunity',
                ));
                $expectedFilters = array ();
                if ($formModel->primaryModelType === 'Contacts' ||
                    $formModel->primaryModelType === 'Accounts') {

                    $expectedFilters[] = array (
                        'name' => 'closedate',
                        'operator' => '>=',
                        'value' => $dateRange['start'],
                    );
                    $expectedFilters[] = array (
                        'name' => 'closedate',
                        'operator' => '<=',
                        'value' => $dateRange['end'],
                    );
                } else {
                    $expectedFilters[] = array (
                        'name' => 'expectedCloseDate',
                        'operator' => '>=',
                        'value' => $dateRange['start'],
                    );
                    $expectedFilters[] = array (
                        'name' => 'expectedCloseDate',
                        'operator' => '<=',
                        'value' => $dateRange['end'],
                    );
                }
                if (!empty ($rep['parameters']['strict'])) {
                    $expectedFilters[] = array (
                        'name' => 'createDate',
                        'operator' => '>=',
                        'value' => $dateRange['start'],
                    );
                    $expectedFilters[] = array (
                        'name' => 'createDate',
                        'operator' => '<=',
                        'value' => $dateRange['end'],
                    );
                }
                switch ($formModel->primaryModelType) {
                    case 'Contacts':
                        $this->assertEquals ($formModel->columns, array (
                            "name","assignedTo","company","leadscore","closedate","dealvalue",
                            "dealstatus","rating","lastUpdated",
                        ));
                        break;
                    case 'Opportunity':
                        $this->assertEquals ($formModel->columns, array (
                            "name", "assignedTo", "accountName", "quoteAmount", "expectedCloseDate",
                            "probability", "salesStage", "leadSource", "lastUpdated",
                        ));
                        break;
                    case 'Accounts':
                        $this->assertEquals ($formModel->columns, array (
                            "name", "createDate", "assignedTo", "type", "employees", 
                            "annualRevenue", "website", "lastUpdated",
                        ));
                        break;
                }
                if (isset ($rep['parameters'][$formModel->primaryModelType])) {
                    $attributes = $rep['parameters'][$formModel->primaryModelType];
                    if (is_array ($attributes)) {
                        foreach ($attributes as $name => $val) {
                            if (empty($val)) continue;
                            $expectedFilters[] = array (
                                'name' => $name,
                                'operator' => '=',
                                'value' => $val,
                            );
                        }
                    }
                }
                $this->assertEquals ($expectedFilters, $formModel->allFilters);
            }
            $this->assertEquals ($newReport->createDate, $rep['createDate']);
            $this->assertEquals ($newReport->createdBy, $rep['createdBy']);
            $this->assertEquals ($newReport->version, '5.0');
            $newReport->deleteByPk ($reportMaxId - 1); // clean up
        }
    }

}


?>
