<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

Yii::import('application.components.ApplicationConfigBehavior');
Yii::import('application.tests.functional.pageCrawlers.VisitAllPagesTest');

/**
 * Visit all pages and assert there are no injection vulnerabilities present
 * @package application.tests.functional
 */
class VisitAllPagesWithInjections extends VisitAllPagesTest {

    /**
     * ID to assign models for injection vulnerability testing
     */
    const INJECTION_TESTID = 10666;
    const INJECTION_USERNAME = 'hacker x';

    /**
     * Crawl as admin to check all pages without restrictions
     * @var array
     */
    public $login = array(
        'username' => 'admin',
        'password' => 'admin',
    );

    /**
     * Manually inject the database
     */
    private static $prepared = false;
    public function setup() {
        parent::setup();
        if (self::$prepared) return;

        // Prepare DB with injection statements
        // See: https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet for more
        // example injection strings. This will be more useful as we test bypassing filters
        $injectionStrings = array(
            '<h1 class="TESTX2INJECTION">{XSS}</h1>',
        );

        // TODO most of these tables are ignored due to constraints, it'd be nice to find a
        // workaround so that these can be tested for injection vulns
        $ignoreTables = array(
            'x2_action_meta_data', 'x2_auth_assignment', 'x2_auth_item_child', 'x2_auth_cache',
            'x2_auth_item', 'x2_cron_events', 'x2_chart_settings', 'x2_email_reports',
            'x2_credentials_default', 'x2_gallery_photo', 'x2_fields', 'x2_forwarded_email_patterns',
            'x2_gallery_to_model', 'x2_list_criteria', 'x2_list_items', 'x2_trigger_logs',
            'x2_password_reset', 'x2_timezones', 'x2_workflow_stages', 'x2_sessions',// 'x2_profile',
            'x2_action_to_record', 'x2_role_to_workflow', 'x2_failed_logins', 'x2_credentials',
        );
        $ignoreFields = array(
            'nameId', 'actionDescription', 'actionId', 'existingProducts', 'products',
            'masterId', 'username', 'parameters', 'twitterCredentialsId', 'modelClass',
        );

        // Prepare a mapping of table names to an array of string-type attributes
        $tables = array();
        $tableSchema = Yii::app()->db->schema->getTables();
        foreach ($tableSchema as $table => $schema) {
            if (in_array($table, $ignoreTables))
                continue;
            $columns = array();
            foreach ($schema->columns as $column => $attrs) {
                if (!in_array($attrs->type, array('string', 'integer'))
                        || in_array($column, $ignoreFields))
                    continue;
                $columns[$column] = $attrs->type;
            }
            $tables[$table] = $columns;
        }

        // Cycle over the possible injection strings when inserting
        $currentInjectionString = 0;
        foreach ($tables as $table => $fields) {
            $columns = array();
            foreach ($fields as $field => $type) {
                $value = $injectionStrings[ $currentInjectionString ];
                $identifier = "XSS from $field in $table";
                $columns[$field] = str_replace('{XSS}', $identifier, $value);
                $currentInjectionString = ($currentInjectionString + 1) % count($injectionStrings);
            }

            if (array_key_exists('id', $fields)) {
                Yii::app()->db->createCommand()->delete($table, 'id = :id',
                    array(':id' => self::INJECTION_TESTID));
                $columns['id'] = self::INJECTION_TESTID;
            }
            // Set artifical user's status to active
            if ($table == 'x2_users') {
                $columns['status'] = 1;
                $columns['username'] = self::INJECTION_USERNAME;
            } else if ($table == 'x2_profile')
                $columns['username'] = self::INJECTION_USERNAME;

            if (!empty($table) && !empty($columns))
                Yii::app()->db->createCommand()->insert($table, $columns);
        }
        self::$prepared = true;
    }

    /**
     * Visit all pages in the app to assert no JavaScript alerts are raised
     */
    public function testInjectionOnAllPages() {
        $injectionPages = array_merge(
            $this->allPages,
            $this->adminPages
        );
        // Skip pages that render rich text: this data is passed through
        // HTMLPurifier before persisting in the database
        $skipPages = array(
            'contacts/shareContact/id/1195',
            'accounts/shareAccount/id/1',
            'marketing/5',
            'weblist/view?id=18',
            'actions/shareAction/id/1',
            'docs/1',
            'docs/update/id/1',
            'quotes/1',
            'quotes/convertToInvoice/id/1',
            'quotes/update/id/1',
            'profile/update/1',
        );
        $injectionPages = array_diff ($injectionPages, $skipPages);

        // Process pages and set id to the 'injected' record
        $pageList = array();
        foreach ($injectionPages as $page) {
            if (preg_match('/\d+(\?|$)/', $page))
                $page = preg_replace('/\d+(\?|$)/', self::INJECTION_TESTID.'\1', $page);
            $pageList[] = $page;
        }

        $this->visitPages ($pageList, true);
    }

    /**
     * Override testPages() to skip
     */
    public function testPages () {
        $this->markTestSkipped('Skipping testPages(). This can be executed from the ordinary '.
            'VisitAllPagesTest class');
    }
}
