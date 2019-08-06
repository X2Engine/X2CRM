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




Yii::import('application.components.util.*');

/**
 * Base action class for actions associated with the updater utility.
 *
 * The updater is kept separate from the rest of the application like this to
 * enable updating it to the latest version pre-emptively without harming any
 * other part of the application with incompatibilities. For this reason,
 * coupling between actions and the controller is intentionally kept very loose
 * and limited to use of ubiquitous methods like {@link CController::render()},
 * with the exception of "error500" (which was in AdminController as of the
 * switch to the new self-contained updater utility).
 *
 * References to the application singleton and controller are thus accompanied
 * by or wrapped in a slew of conditional statements for purposes of backwards
 * compatibility.
 *
 * @package application.components.webupdater
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class WebUpdaterAction extends CAction{

    public function behaviors() {
        return array(
			'UpdaterBehavior' => array(
				'class' => 'application.components.UpdaterBehavior'
			)
		);
    }

	/**
	 * Override of CAction's construct; all child classes need to have the
	 * behavior {@link UpdaterBehavior} attached and enabled.
	 * 
	 * @param type $controller
	 * @param type $id 
	 */
	public function __construct($controller, $id){
		parent::__construct($controller, $id);
		$this->attachBehaviors($this->behaviors());
		// Be certain we can continue safely:
		$this->requireDependencies();
	}

    /**
	 * Wrapper for {@link UpdaterBehavior::updateUpdater} that displays errors
	 * in a user-friendly way and reloads the page.
     *
     * It contains hideous references to the controller (specifically,
     * {@link AdminController}) only to avoid code duplication while at the same
     * time remaining backwards-compatible with earlier versions (which will
     * download AdminController, hence necessitating that AdminController have
     * all the necessary functions for throwing errors in cases of missing
     * dependencies that can't be auto-retrieved).
	 */
	public function runUpdateUpdater($updaterCheck, $redirect){
        try{
            if(count($classes = $this->updateUpdater($updaterCheck))){
                $this->output(Yii::t('admin', 'One or more dependencies of AdminController are missing and could not be automatically retrieved. They are {classes}', array('{classes}' => implode(', ', $classes))), 'error', 1);
                $this->controller->missingClassesException($classes);
            }
            $this->output(Yii::t('admin', 'The updater is now up-to-date and compliant with the updates server.'));
            $this->controller->redirect($redirect);
        }catch(Exception $e){
            $this->controller->error500($e->getMessage());
        }
    }
}
?>
