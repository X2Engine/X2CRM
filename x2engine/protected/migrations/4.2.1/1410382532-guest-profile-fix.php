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




/*
Ensure that guest profile exists and has correct id.
Give profiles to users that don't have them.
*/


$guestProfileFix = function () {
    $guestProfileUsername = '__x2_guest_profile__';

    //print_r ('deleting guest profile'."\n");
    Yii::app()->db->createCommand ("
        delete from x2_profile where username=:guestProfileUsername
    ")->execute (array (
        ':guestProfileUsername' => $guestProfileUsername
    ));
    //print_r ('inserting new guest profile'."\n");
    Yii::app()->db->createCommand ("
        INSERT INTO x2_profile (id, fullName, username, emailAddress, status)
		    VALUES (-1, '', :guestProfileUsername, '', '0')
    ")->execute (array (
        ':guestProfileUsername' => $guestProfileUsername
    ));
    $users = Yii::app()->db->createCommand ("
        select *
        from x2_users
    ")->queryAll ();

    // look for users without a profile record and create one
    foreach ($users as $row) {
        $id = $row['id'];
        $profileCount = intval (Yii::app()->db->createCommand ("
            select count(*)
            from x2_profile
            where id=:id
        ")->queryScalar (array (
            ':id' => $id
        )));
        if ($profileCount === 0) {
            //print_r ('creating missing profile record'."\n");
            Yii::app()->db->createCommand ("
                INSERT INTO x2_profile (
                    `fullName`, `username`, `allowPost`, `emailAddress`, `status`, `id`)
                    VALUES (:fullName, :username, :allowPost, :emailAddress, :status, :id)
            ")->execute (array (
                ':fullName' => $row['firstName'].' '.$row['lastName'],
                ':username' => $row['username'],
                ':allowPost' => 1,
                ':emailAddress' => $row['emailAddress'],
                ':status' => 1,
                ':id' => $row['id']
            ));
        }
    }
};

$guestProfileFix ();
