<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

Yii::import('application.components.webupdater.*');

/**
 * Action that applies the update.
 *
 * This action takes the longest of any action to execute and is the most
 * critical point in the update process.
 * 
 * @package X2CRM.components.webupdater
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EnactX2CRMChangesAction extends WebUpdaterAction {

	public function run($scenario = null, $autoRestore = false){
		set_error_handler('UpdaterBehavior::respondWithError');
		set_exception_handler('UpdaterBehavior::respondWithException');
		$autoRestore = (bool) $autoRestore;
		$locked = $this->enactChanges($scenario, $_POST, $autoRestore);
		$this->addResponseProperty('locked',$locked);
		if(! (bool) $locked)
			self::respond(Yii::t('admin', 'All done.'));
		else
			self::respond(Yii::t('admin', 'An operation that began {t} is in progress (to apply database and file changes to X2CRM). If you are seeing this message, and the stated time is less than a minute ago, this is most likely because your web browser made a duplicate request to the server. Please stand by while the operation completes. Otherwise, you may delete the lock file {file} and try again.',array('{t}'=>strftime('%h %e, %r',$locked),'{file}'=>$this->lockFile)),1);
	}

}

?>
