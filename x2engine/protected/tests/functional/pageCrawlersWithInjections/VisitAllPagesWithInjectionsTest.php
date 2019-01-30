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




Yii::import('application.components.behaviors.ApplicationConfigBehavior');
Yii::import('application.tests.functional.pageCrawlers.BaseVisitAllPagesTest');

/**
 * Visit all pages and assert there are no injection vulnerabilities present
 * @package application.tests.functional
 */
class VisitAllPagesWithInjectionsTest extends BaseVisitAllPagesTest {

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
     * @param array $pages array of URIs 
     */
    protected function visitPages ($pages) {
        foreach ($pages as $page) {
            X2_TEST_DEBUG_LEVEL > 1 && print ('visiting page ' .$page."\n");
            $this->openX2($page);
            $this->assertElementNotPresent ('css=.TESTX2INJECTION', 'Injection present on '.$page);
        }
    }

    /**
     * Manually inject the database
     */
    public function setup() {
        parent::setup();

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
            'x2_events_to_media', 'x2_topic_replies', 'x2_role_to_permission', 'x2_actions_to_media',
            'x2_calendar_invites', 'x2_calendar_permissions', 'x2_twofactor_auth'
        );
        $ignoreFields = array(
            'nameId', 'actionDescription', 'actionId', 'existingProducts', 'products',
            'masterId', 'username', 'parameters', 'twitterCredentialsId', 'modelClass',
            'workflowId', 'stageNumber', 'googleCredentialsId', 'parentFolder', 'folderId',
            'defaultWorkflow', 'roleId', 'stageId', 'replacementId', 'fieldId', 'topicId',
            'backgroundInfo', 'description', 'nextAction', 'calendarId',
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
            if(array_key_exists('dupeCheck',$fields)){
                $columns['dupeCheck'] = 1;
            }
            // Set artifical user's status to active
            if ($table == 'x2_users') {
                $columns['status'] = 1;
                $columns['username'] = self::INJECTION_USERNAME;
                $columns['emailAddress'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 16);;
            } else if ($table == 'x2_profile') {
                $columns['username'] = self::INJECTION_USERNAME;
                $columns['emailAddress'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 16);;
            }

            if (!empty($table) && !empty($columns))
                Yii::app()->db->createCommand()->insert($table, $columns);
        }
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
            'contacts/shareContact/id/67890',
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
            
            // XSS Injection Not Possible on Create Pages
            // If we ever check for XSS in default field values, these will need to be removed
            'contacts/createList',
            'accounts/create',
            'marketing/create',
            'x2Leads/create',
            'opportunities/create',
            'services/create',
            'actions/create',
            'docs/create',
            'docs/createEmail',
            'docs/createQuote',
            'workflow/create',
            'products/create',
            'quotes/create',
            'groups/create',
            'bugReports/create',
            'users/create',
            'admin/createModule',
            'admin/createPage',
        );
        $injectionPages = array_diff ($injectionPages, $skipPages);

        // Process pages and set id to the 'injected' record
        $pageList = array();
        foreach ($injectionPages as $page) {
            if (preg_match('/\d+(\?|$)/', $page))
                $page = preg_replace('/\d+(\?|$)/', self::INJECTION_TESTID.'\1', $page);
            $pageList[] = $page;
        }

        $this->visitPages ($pageList);
    }
}
