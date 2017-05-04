<?php
 /**
 * YiiChatAction class file.
 *
 *	@example:
 *		public function actions() { return array('yiichat'=>array('class'=>'YiiChatAction')); }
 *
 * @author Christian Salazar <christiansalazarh@gmail.com>
 * @license http://opensource.org/licenses/bsd-license.php
 */
class YiiChatAction extends CAction {
	public function run(){
		$inst = new YiiChatWidget();
		$inst->runAction($_GET['action'],$_GET['data']);
	}
 }

