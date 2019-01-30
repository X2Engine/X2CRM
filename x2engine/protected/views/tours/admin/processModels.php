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




echo Tours::tips (array(
    array (
        "content" => "<h3>".Yii::t('admin',"Welcome to the Record Importer!")."</h3> ".Yii::t('admin',"This page allows you to define your mapping of CSV fields to the fields in X2CRM before initiating the import process. There are also various options to process the records after import."),
        'type' => 'flash',
    ),
    array (
        'content' => Yii::t('admin','First, select which fields in your CSV belong in your fields in X2CRM. You can also select DO NOT MAP to ignore the field, CREATE NEW FIELD to add a new custom field, or APPLY TAGS to treat the column as a list of tags.'),
        'target' => '#import-map select',
    ),
    array (
        'content' => Yii::t('admin','If you will be processing multiple CSVs, you can export your mapping here to use next time.'),
        'target' => '#export-map',
        'highlight' => true
    ),
    array (
        'content' => Yii::t('admin','When this option is checked, records referred to in a lookup field will be created if the do not exist yet.'),
        'target' => '#create-records-box',
        'highlight' => true
    ),
    array (
        'content' => Yii::t('admin','Select this option to update your existing records with the data contained in the CSV. This is useful for performing a bulk edit of records in another application such as Excel.'),
        'target' => '#update-records-box',
        'highlight' => true
    ),
));

?>
