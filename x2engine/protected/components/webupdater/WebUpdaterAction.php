<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

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
 * @package X2CRM.components.webupdater
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class WebUpdaterAction extends CAction{

	/**
	 * Override of CAction's construct; all child classes need to have the
	 * behavior {@link UpdaterBehavior} attached and enabled.
	 * 
	 * @param type $controller
	 * @param type $id 
	 */
	public function __construct($controller, $id){
		parent::__construct($controller, $id);
		$this->attachBehaviors(array(
			'UpdaterBehavior' => array(
				'class' => 'application.components.UpdaterBehavior',
				'isConsole' => false,
			)
		));
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
