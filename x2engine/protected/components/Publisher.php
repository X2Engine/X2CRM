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

/**
 * Widget class for displaying all available inline actions.
 * 
 * Displays tabs for "log a call","new action" and the like.
 * 
 * @package X2CRM.components 
 */
class Publisher extends X2Widget {
	public $associationType;		// type of record to associate actions with
	public $associationId = '';		// record to associate actions with
	public $assignedTo = null;	// user actions will be assigned to by default
	
	// show all tabs by default
	public $showLogACall = true;
	public $showNewAction = true;
	public $showNewComment = true;
	public $showNewEvent = false;
	public $halfWidth = false;
	
	public function run() {
		$model = new Actions;
		$model->associationType = $this->associationType;
		$model->associationId = $this->associationId;
		if($this->assignedTo)
			$model->assignedTo = $this->assignedTo;
		else
			$model->assignedTo = Yii::app()->user->getName();
        
        Yii::app()->clientScript->registerScript('loadEmails',"
            function loadFrame(id,type){
                if(type!='Action'){
                    var frame='<iframe style=\"width:99%;height:99%\" src=\"".(Yii::app()->controller->createUrl('/actions/viewEmail/'))."?id='+id+'\"></iframe>';
                }else{
                    var frame='<iframe style=\"width:99%;height:99%\" src=\"".(Yii::app()->controller->createUrl('/actions/viewAction/'))."?id='+id+'&publisher=true\"></iframe>';
                }
                if(typeof x2ViewEmailDialog != 'undefined') {
                    if($(x2ViewEmailDialog).is(':hidden')){
                        $(x2ViewEmailDialog).remove();
                        
                    }else{
                        return;
                    }
                }

                x2ViewEmailDialog = $('<div></div>', {id: 'x2-view-email-dialog'});

                x2ViewEmailDialog.dialog({
                    title: 'View '+type, 
                    autoOpen: false,
                    resizable: true,
                    width: '650px',
                    show: 'fade'
                });
                jQuery('body')
                    .bind('click', function(e) {
                        if(jQuery('#x2-view-email-dialog').dialog('isOpen')
                            && !jQuery(e.target).is('.ui-dialog, a')
                            && !jQuery(e.target).closest('.ui-dialog').length
                        ) {
                            jQuery('#x2-view-email-dialog').dialog('close');
                        }
                    });

                x2ViewEmailDialog.data('inactive', true); 
                if(x2ViewEmailDialog.data('inactive')) {
                    x2ViewEmailDialog.append(frame);
                    x2ViewEmailDialog.dialog('open').height('400px');
					x2ViewEmailDialog.data('inactive', false);
                } else {
                    x2ViewEmailDialog.dialog('open');
                }
            }
            $(document).on('ready',function(){
                var t;
                $(document).on('mouseenter','.email-frame',function(){
                    var id=$(this).attr('id');
                    t=setTimeout(function(){loadFrame(id,'Email')},500);
                });
                $(document).on('mouseleave','.email-frame',function(){
                    clearTimeout(t);
                });
                $('.quote-frame').mouseenter(function(){
                    var id=$(this).attr('id');
                    t=setTimeout(function(){loadFrame(id,'Quote')},500);
                }).mouseleave(function(){
                    clearTimeout(t);
                });
            });
        ",CClientScript::POS_HEAD);
		
		$this->render($this->halfWidth? 'publisherHalfWidth':'publisher',
			array(
				'model' => $model,
				'showLogACall'=>$this->showLogACall,
				'showNewAction'=>$this->showNewAction,
				'showNewComment'=>$this->showNewComment,
				'showNewEvent'=>$this->showNewEvent,
			)
		);
	}
}