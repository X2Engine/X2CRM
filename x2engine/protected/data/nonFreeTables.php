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




/**
 * All tables that do not exist in the open source edition
 */
$allEditions = array_keys(require(dirname(__FILE__).DIRECTORY_SEPARATOR.'editionHierarchy.php'));
$tables = array_fill_keys($allEditions,array());
// Professional Edition
$tables['pro'][] = 'x2_action_timers';
$tables['pro'][] = 'x2_reports_2';
$tables['pro'][] = 'x2_charts';
$tables['pro'][] = 'x2_forwarded_email_patterns';
$tables['pro'][] = 'x2_gallery';
$tables['pro'][] = 'x2_gallery_photo';
$tables['pro'][] = 'x2_gallery_to_model';
// Platinum Edition
$tables['pla'][] = 'x2_anon_contact';
$tables['pla'][] = 'x2_fingerprint';

return $tables;
 
///**
// * All tables that do not exist in the open source edition
// */
//$allEditions = array_keys(require(dirname(__FILE__).DIRECTORY_SEPARATOR.'editionHierarchy.php'));
//$tables = array_fill_keys($allEditions,array());
//
//$protected = dirname(__FILE__).'/..';
//
//$tableNamesCommand = "grep --exclude-dir='runtime' --exclude-dir='tests' -i 'CREATE TABLE' | perl -pe 's/.*(x2_[^(` ]*).*/$1/;'";
//
//#exec ("cd $protected && find . -path ./tests -prune -o -name '*.sql' -print | xargs $tableNamesCommand", $allTables);
//exec ("cd $protected && find . -path ./tests -prune -o -name '*.sql' -print | xargs grep -l '@edition:pro' | xargs $tableNamesCommand", $proTablesTagExcluded);
//exec ("cd $protected && find . -path ./tests -prune -o -name '*.sql' -print | xargs grep -l '@edition:pla' | xargs $tableNamesCommand", $plaTablesTagExcluded);
//
//exec ("cd $protected && find . -path ./tests -prune -o -name '.pro' -print | xargs -n 1 dirname | xargs -I {} find {} -name '*.sql' | xargs $tableNamesCommand", $proTablesDirExcluded);
//exec ("cd $protected && find . -path ./tests -prune -o -name '.pla' -print | xargs -n 1 dirname | xargs -I {} find {} -name '*.sql' | xargs $tableNamesCommand", $plaTablesDirExcluded);
//
//
//$tables['pla'] = array_merge ($plaTablesDirExcluded, $plaTablesTagExcluded);
//$tables['pro'] = array_merge ($proTablesDirExcluded, $proTablesTagExcluded);
//
//return $tables;
