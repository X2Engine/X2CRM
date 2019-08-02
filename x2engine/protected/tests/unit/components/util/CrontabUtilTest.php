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






Yii::import('application.components.util.CrontabUtil');

/**
 * @package application.tests.unit.components.util
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CrontabUtilTest extends X2TestCase {

	/**
	 * Test that preparing the cron table for use by X2Engine doesn't disturb existing table entries
	 */
	public function testAddCronMarker() {
		$crontab = "
# Ignored line
* * * * * curl http://foo.com &>/dev/null # ignored cron entry
";
		$newCrontab = $crontab."
".CrontabUtil::CRONTAB_MANAGED_BEGIN."
".CrontabUtil::CRONTAB_MANAGED_END."
";
		CrontabUtil::addCronMarker($crontab);
		$this->assertEquals($newCrontab,$crontab);
	}

	/**
	 * Test parsing an existing cron table managed by X2Engine
	 */
	public function testCrontabToArray() {
		$emptyCrontab = "
# this line should be ignored
";
		$this->assertEquals(array(),CrontabUtil::crontabToArray($emptyCrontab));

		$delim = CrontabUtil::CRONTAB_FIELD_DELIM;
		$crontab="
# This line should be ignored
0 5 * * 1 tar -zcf /var/backups/home.tgz /home/ # Example line from default Ubuntu crontab comments
".CrontabUtil::CRONTAB_MANAGED_BEGIN."
0,30\t * *  * 1 curl http://google.com {$delim}helloworld{$delim}Hello, world
0 * * * * whoami # Ignored
0 * * *   * whoami {$delim}HODOR{$delim}Hodor hodor hoDOR HOdor
@hourly sync {$delim}flushbuffers{$delim}Flush filesystem buffers
@daily apt-get update {$delim}update{$delim}Refresh system package info
@weekly apt-get upgrade {$delim}upgrade{$delim}Automatically upgrade
@monthly /path/to/crm/protected/yiic update app --lock=1 {$delim}app_update{$delim}Update X2Engine
@yearly wall /etc/happynewyear {$delim}auldlangsyne{$delim}Sing happy new year
".CrontabUtil::CRONTAB_MANAGED_END;
		$originalCrontab = $crontab;

		$expected = array(
			'helloworld' => array(
				'tag' => 'helloworld',
				'desc' => 'Hello, world',
				'min' => array('0','30'),
				'hour' => '*',
				'dayOfMonth' => '*',
				'month' => '*',
				'dayOfWeek' => array('1'),
				'cmd' => 'curl http://google.com'
			),
			'HODOR' => array(
				'tag' => 'HODOR',
				'desc' => 'Hodor hodor hoDOR HOdor',
				'min' => array('0'),
				'hour' => '*',
				'dayOfMonth' => '*',
				'month' => '*',
				'dayOfWeek' => '*',
				'cmd' => 'whoami'
			),
            'flushbuffers' => array(
                'tag' => 'flushbuffers',
                'desc' => 'Flush filesystem buffers',
                'cmd' => 'sync',
                'schedule' => 'hourly'                
            ),
            'update' => array(
                'tag' => 'update',
                'desc' => 'Refresh system package info',
                'cmd' => 'apt-get update',
                'schedule' => 'daily'
            ),
            'upgrade' => array(
                'tag' => 'upgrade',
                'desc' => 'Automatically upgrade',
                'cmd' => 'apt-get upgrade',
                'schedule' => 'weekly'
            ),
            'app_update' => array(
                'tag' => 'app_update',
                'desc' => 'Update X2Engine',
                'cmd' => '/path/to/crm/protected/yiic update app --lock=1',
                'schedule' => 'monthly'
            ),
            'auldlangsyne' => array(
                'tag' => 'auldlangsyne',
                'desc' => 'Sing happy new year',
                'cmd' => 'wall /etc/happynewyear',
                'schedule' => 'yearly'
            ),
		);

		$parsedCrontab = CrontabUtil::crontabToArray($crontab);
		// In this case, the cron table shouldn't be altered because it already has delimiters
		$this->assertEquals($originalCrontab,$crontab);

		$this->assertEquals(array_keys($expected),array_keys($parsedCrontab),'Failed asserting the correct set of lines were parsed.');
		
		foreach($expected as $tag => $lineCfg) {
			$this->assertEquals($lineCfg,$parsedCrontab[$tag],"Failed asserting that line $tag was parsed properly.");
		}
	}


	public function testArrayToCrontab() {
		$delim = CrontabUtil::CRONTAB_FIELD_DELIM;
		$valuesIn = array(
            'helloworld' => array(
                'tag' => 'helloworld',
                'desc' => 'Hello, world',
                'min' => array('0', '30'),
                'hour' => '*',
                'dayOfMonth' => '*',
                'month' => '*',
                'dayOfWeek' => array('1'),
                'cmd' => 'curl http://google.com'
            ), # a preexisting command that is not edited, and is preserved
            'HODOR' => array(
                'tag' => 'HODOR',
                'desc' => 'Hodor hodor hoDOR HOdor HODOR',
                'min' => array('0'),
                'hour' => '*',
                'dayOfMonth' => '*',
                'month' => '*',
                'dayOfWeek' => '*',
                'cmd' => 'whoami'
            ), # another preexisting command, this one edited
            'happyNewYear' => array(
                'tag' => 'happyNewYear',
                'desc' => 'Says happy new year to everyone',
                'min' => array('59'),
                'hour' => array('11'),
                'dayOfMonth' => array('31'),
                'month' => array('12'),
                'dayOfWeek' => '*',
                'cmd' => 'wall -t 60 /etc/happyNewYearMessage'
            ),
            'flushbuffers' => array(
                'tag' => 'flushbuffers',
                'desc' => 'Flush filesystem buffers',
                'cmd' => 'sync',
                'schedule' => 'hourly'
            ),
            'update' => array(
                'tag' => 'update',
                'desc' => 'Refresh system package info',
                'cmd' => 'apt-get update',
                'schedule' => 'daily'
            ),
            'upgrade' => array(
                'tag' => 'upgrade',
                'desc' => 'Automatically upgrade',
                'cmd' => 'apt-get upgrade',
                'schedule' => 'weekly'
            ),
            'app_update' => array(
                'tag' => 'app_update',
                'desc' => 'Update X2Engine',
                'cmd' => '/path/to/crm/protected/yiic update app --lock=1',
                'schedule' => 'monthly'
            ),
        );
		$crontab="
# This line should be ignored
0 5 * * 1 tar -zcf /var/backups/home.tgz /home/ # Example line from default Ubuntu crontab comments
".CrontabUtil::CRONTAB_MANAGED_BEGIN."
0,30\t * *  * 1 curl http://google.com {$delim}helloworld{$delim}Hello, world
0 * * * * whoami # Ignored
0 * * *   * whoami {$delim}HODOR{$delim}Hodor hodor hoDOR HOdor
* * * * * pwd {$delim}deleted{$delim}This cron job to be deleted
".CrontabUtil::CRONTAB_MANAGED_END."
# Stuff that should be preserved";

		$expectedCrontab = "
# This line should be ignored
0 5 * * 1 tar -zcf /var/backups/home.tgz /home/ # Example line from default Ubuntu crontab comments
".CrontabUtil::CRONTAB_MANAGED_BEGIN."
0,30 * * * 1 curl http://google.com {$delim}helloworld{$delim}Hello, world
0 * * * * whoami {$delim}HODOR{$delim}Hodor hodor hoDOR HOdor HODOR
59 11 31 12 * wall -t 60 /etc/happyNewYearMessage {$delim}happyNewYear{$delim}Says happy new year to everyone
@hourly sync {$delim}flushbuffers{$delim}Flush filesystem buffers
@daily apt-get update {$delim}update{$delim}Refresh system package info
@weekly apt-get upgrade {$delim}upgrade{$delim}Automatically upgrade
@monthly /path/to/crm/protected/yiic update app --lock=1 {$delim}app_update{$delim}Update X2Engine
".CrontabUtil::CRONTAB_MANAGED_END."
# Stuff that should be preserved";
		CrontabUtil::arrayToCrontab($crontab,$valuesIn);
		$this->assertEquals($expectedCrontab,$crontab);

		// Test inserting into a completely empty cron table:
		$crontab = "";
		$expectedCrontab = "
".CrontabUtil::CRONTAB_MANAGED_BEGIN."
0,30 * * * 1 curl http://google.com {$delim}helloworld{$delim}Hello, world
0 * * * * whoami {$delim}HODOR{$delim}Hodor hodor hoDOR HOdor HODOR
59 11 31 12 * wall -t 60 /etc/happyNewYearMessage {$delim}happyNewYear{$delim}Says happy new year to everyone
@hourly sync {$delim}flushbuffers{$delim}Flush filesystem buffers
@daily apt-get update {$delim}update{$delim}Refresh system package info
@weekly apt-get upgrade {$delim}upgrade{$delim}Automatically upgrade
@monthly /path/to/crm/protected/yiic update app --lock=1 {$delim}app_update{$delim}Update X2Engine
".CrontabUtil::CRONTAB_MANAGED_END."
";
		CrontabUtil::arrayToCrontab($crontab,$valuesIn);
		$this->assertEquals($expectedCrontab,$crontab);

	}

	public function testProcessForm() {
		// Now, processing of data from the cron form:

		// Simple Schedule: hourly
		$cronPost = array (
			'cmd' => 'cmd',
			'desc' => 'desc',
			'tag' => 'tag',
			'schedule' => 'hourly',
            'use_schedule' => 1
		);
		$expected = array(
			'cmd' => 'cmd',
			'tag' => 'tag',
			'desc' => 'desc',
            'schedule' => 'hourly'
		);
		$this->assertEquals($expected,CrontabUtil::processForm($cronPost),'Failed to process special case: hourly');

		// Simple schedule: daily
		$cronPost = array (
			'cmd' => 'cmd',
			'tag' => 'tag',
			'desc' => 'desc',
			'schedule' => 'daily',
            'use_schedule' => 1
		);
		$expected = array(
			'cmd' => 'cmd',
			'tag' => 'tag',
			'desc' => 'desc',
			'schedule' => 'daily',
		);
		$this->assertEquals($expected,CrontabUtil::processForm($cronPost),'Failed to process special case: daily');
		
		// Simple schedule: weekly (Sunday)
		$cronPost = array (
			'cmd' => 'cmd',
			'desc' => 'desc',
			'tag' => 'tag',
			'schedule' => 'weekly',
            'use_schedule' => 1
		);
		$expected = array(
			'cmd' => 'cmd',
			'tag' => 'tag',
			'desc' => 'desc',
            'schedule' => 'weekly',
		);
		$this->assertEquals($expected,CrontabUtil::processForm($cronPost),'Failed to process special case: weekly');

		$cronPost = array (
			'cmd' => 'cmd',
			'desc' => 'desc',
			'tag' => 'tag',
			'schedule' => 'monthly',
            'use_schedule' => 1
		);
		$expected = array(
			'cmd' => 'cmd',
			'tag' => 'tag',
			'desc' => 'desc',
			'schedule' => 'monthly',
		);
		$this->assertEquals($expected,CrontabUtil::processForm($cronPost),'Failed to process special case: monthly');

		$cronPost = array (
			'cmd' => 'cmd',
			'desc' => 'desc',
			'tag' => 'tag',
			'schedule' => 'yearly',
            'use_schedule' => 1
		);
		$expected = array(
			'cmd' => 'cmd',
			'tag' => 'tag',
			'desc' => 'desc',
			'schedule' => 'yearly',
		);
		$this->assertEquals($expected,CrontabUtil::processForm($cronPost),'Failed to process special case: yearly');

		// Everything selected, so basically, minute-ly cron
		$cronPost = array (
			'cmd' => 'cmd',
			'desc' => 'desc',
			'tag' => 'tag',
			'all_min' => '1',
			'all_hour' => '1',
			'all_dayOfMonth' => '1',
			'all_month' => '1',
			'all_dayOfWeek' => '1',
		);
		$expected = array(
			'cmd' => 'cmd',
			'tag' => 'tag',
			'desc' => 'desc',
			'min' => '*',
			'hour' => '*',
			'dayOfMonth' => '*',
			'month' => '*',
			'dayOfWeek' => '*',
		);
		$this->assertEquals($expected,CrontabUtil::processForm($cronPost),'Failed to process selection of all-all-all-all-all');

		// Test setting a cron field normaly:
		$tens = array('0','10','20','30','40','50');
		$cronPost = array (
			'cmd' => 'cmd',
			'desc' => 'desc',
			'tag' => 'tag',
			'special_def' => '0',
			'special' => 'yearly',
			'all_min' => '0',
			'min' => $tens,
			'all_hour' => '1',
			'all_dayOfMonth' => '1',
			'all_month' => '1',
			'all_dayOfWeek' => '1',
		);
		$ca = CrontabUtil::processForm($cronPost);
		$this->assertEquals($tens,$ca['min'],'mins didn\'t get set');
		// Test overriding the cron field
		$cronPost['all_min'] = 1;
		$ca = CrontabUtil::processForm($cronPost);
		$this->assertEquals('*',$ca['min'],'mins not overridden by all_mins');


	}
}

?>
