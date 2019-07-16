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




$templateRec = Yii::app()->db->createCommand()->select('nameId,name')->from('x2_docs')->where("type='quote'")->queryAll();
$templates = array();
$templates[null] = '(none)';
foreach($templateRec as $tmplRec){
	$templates[$tmplRec['nameId']] = $tmplRec['name'];
}
echo '<div style="display:inline-block; margin: 8px 11px;" ' .
    'id="quote-template-dropdown">';
echo '<strong>'.$form->label($model, 'template').'</strong>&nbsp;';
echo $form->dropDownList($model, 'template', $templates).'&nbsp;'
    .X2Html::hint2 (
        Yii::t('quotes', 
            'To create a template for {quotes} and invoices, go to the {docs} module and select '.
            '"Create {quote}".', array(
                '{quotes}' => lcfirst(Modules::displayName()),
                '{quote}' => Modules::displayName(false),
                '{docs}' => Modules::displayName(true, "Docs"),
            )));
echo '</div><br />'; 
echo '	<div class="row buttons" style="padding-left:0;">'."\n";
echo $quick?CHtml::button(Yii::t('app','Cancel'),array('class'=>'x2-button right','id'=>'quote-cancel-button','tabindex'=>24))."\n":'';
echo CHtml::submitButton(Yii::t('app', $action), array('class' => 'x2-button'.($quick?' highlight':''), 'id' => 'quote-save-button', 'tabindex' => 25, 'onClick' => 'return x2.quoteslineItems.validateAllInputs ();'))."\n";
echo "	</div>\n";
echo '<div id="quotes-errors"></div>';

?>

