<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2022 X2 Engine Inc.
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




$uid = User::getUserId($data['assignedTo']);
$image = Profile::renderAvatarImage($uid, 32, 32, ['class'=>'rounded']);
$image = $image ? $image : Profile::renderFullSizeAvatar($uid,32);

if ($data['assignedTo'] == Yii::app()->user->name) { ?>

    <div class="row py-3">
      <div class="col-10 d-flex">
        <div class="p-2">
          <?php
            echo $image;
          ?>
        </div>
        <div class="py-1">
          <div class="">
              <span class="font-weight-bold">Me</span>
              <span class="small font-italic">
                <?php echo Formatter::formatFeedTimestamp($data['createDate']);?>
              </span>
          </div>
          <div class="text-left">
              <?php echo $data['text']; ?>
          </div>
        </div>
      </div>
    </div>

<?php } else { ?>

  <div class="row py-3">
    <div class="col-10 offset-2 d-flex">
      <div class="ml-auto py-1">
        <div class="text-right">
          <?php $date = Formatter::formatFeedTimestamp($data['createDate']);
          $user = User::model()->find('username=:username',[':username'=>$data['assignedTo']]);
          echo "<span class='small font-italic pr-1'>$date</span>",
               "<span class='font-weight-bold'>$user->name</span>";?>
        </div>
        <div class="text-right ">
            <?php echo $data['text']; ?>
        </div>
      </div>
      <div class="p-2">
        <?php
          echo $image;
        ?>
      </div>
    </div>
  </div>

<?php }