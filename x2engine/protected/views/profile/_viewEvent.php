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




if (class_exists($data->associationType)) {
    if (count(X2Model::model($data->associationType)->findAllByPk($data->associationId)) > 0) {
        $associatedModel = X2Model::model($data->associationType)->findByPk($data->associationId);
        if (!$this->checkPermissions($associatedModel, 'view')) {
            return;
        }
        if ($associatedModel instanceof Actions) {
            $modelName = X2Model::getModelName($associatedModel->associationType, true);
            if ($modelName) {
                if (count(X2Model::model($modelName)->findAllByPk($associatedModel->associationId)) > 0) {
                    $actionModel = X2Model::model($modelName)->findByPk($associatedModel->associationId);
                    if (!$this->checkPermissions($actionModel, 'view')) {
                        return;
                    }
                }
            }
        }
    }
}
Yii::app()->params->isAdmin = Yii::app()->user->checkAccess('AdminIndex');
$profId = User::getUserId ($data->user);

$themeUrl = Yii::app()->theme->getBaseUrl();
$typeFile = $data->type;
if (in_array($data->type, array('email_sent', 'email_opened'))) {
    // The above types have special icons for sub-types
    if (in_array($data->subtype, array('quote', 'invoice')))
        $typeFile .= "_{$data->subtype}";
}
// Distinct call logging icon:
if ($data->type == 'record_create') {
    switch ($data->subtype) {
        case 'call':
            $typeFile = 'voip_call';
            break;
        case 'time':
            $typeFile = 'log_time';
            break;
    }
}

$authorRecord = $data->userObj;
if (isset($authorRecord)) {
    if (Yii::app()->user->getName() == $data->user) {
        $author = Yii::t('app', 'You');
    } else {
        $author = $authorRecord->name;
    }
} else {
    $author = '';
}
$commentCount = $data->comments ()->count ();
$likeCount = Yii::app()->db->createCommand()
        ->select('count(postId)')
        ->from('x2_like_to_post')
        ->where('postId=:postId', array(':postId' => $data->id))
        ->queryScalar();
$likedPost = Yii::app()->db->createCommand()
        ->select('count(userId)')
        ->from('x2_like_to_post')
        ->where('userId=:userId and postId=:postId', array(':userId' => Yii::app()->user->id, ':postId' => $data->id))
        ->queryScalar();
?>


<?php
if ($data->sticky) {
    if (!isset($_SESSION['stickyFlag']) || !$_SESSION['stickyFlag']) {
        $_SESSION['stickyFlag'] = true;
        echo "<div class='view top-level date-break sticky-section-headert'>".Yii::t('app','Sticky')."</div>";
    }
} else {
    if (!isset($noDateBreak) || !$noDateBreak) {
        if (isset($_SESSION['lastDate']) && $_SESSION['lastDate'] !== date("M j", $data->timestamp)) {
            echo
            "<div class='view top-level date-break" . ($_SESSION['firstFlag'] ? " first" : "") . "'
                  id='" . "date-break-" . ($data->timestamp) . "'>
                    " . (date("M j", time()) == date("M j", $data->timestamp) ?
                    Yii::t('app', "Today") :
                    Yii::app()->locale->dateFormatter->format(
                            'EEEE', $data->timestamp) . ', ' .
                    Yii::app()->locale->dateFormatter->formatDateTime(
                            $data->timestamp, 'long', null)) . "
                </div>";
            $_SESSION['lastDate'] = date("M j", $data->timestamp);
            $_SESSION['firstFlag'] = false;
        } else {
            $_SESSION['lastDate'] = date("M j", $data->timestamp);
        }
    }
}
$style = "";
// if ($data->important && isset($data->color)) {
//     $data->color = str_replace('%23', '#', $data->color);
//     $style = "background-color:{$data->color};";
// } elseif ($data->important && empty($data->color)) {
//     $style = "background-color:#FFFFC2;";
// }
// if ($data->important && isset($data->fontColor)) {
//     $data->fontColor = str_replace('%23', '#', $data->fontColor);
//     $style.="color:{$data->fontColor};";
// }

$important = $data->important ? 'important-action' : '';
?>


<div class="view top-level activity-feed <?php echo $important ?>" style="<?php echo $style; ?>" id="<?php echo $data->id; ?>-feed-box">
        <div class="img-box test <?php 
        if (!$data->isTypeFeed()) echo CHtml::encode($typeFile) . " " . (($data->type == 'record_create') ? $data->associationType . '-create' : ""); ?>" title="<?php echo CHtml::encode($data->parseType($data->type)); ?>" style="width:45px;float:left;margin-right:5px;">
            <?php
            if ($data->isTypeFeed()) {
                echo Profile::renderFullSizeAvatar($profId, 35);
            } if ($data->type == 'record_create') {
                $fileName = strtolower($data->associationType);
                if ($fileName == 'opportunity')
                    $fileName = 'opportunities';
                if ($fileName == 'product')
                    $fileName = 'products';
                if ($fileName == 'quote')
                    $fileName = 'quotes';
                if (file_exists('themes/' . Yii::app()->theme->name . '/images/' . $fileName . '.png')) {
                    // echo "<div class='img-box plus-sign'></div>";
                }
            } else if ($data->type == 'calendar_event') {
                echo X2DateUtil::actionDate($data->timestamp, 1);
            }
            ?>
            <!--<div class='stacked-icon'></div>-->
        </div>
    <div class="event-text-box">
        <div class="deleteButton">
            <?php
            if (($data->isTypeFeed ()) && ($data->user == Yii::app()->user->getName() || Yii::app()->params->isAdmin)) {
                echo CHtml::link('', array('/profile/updatePost', 'id' => $data->id, 'profileId' => $profileId), array('class' => 'fa fa-edit')) . " ";
            }
            if ((($data->user == Yii::app()->user->getName() || $data->associationId == Yii::app()->user->getId()) && ($data->isTypeFeed ())) || Yii::app()->params->isAdmin)
                echo CHtml::link('', '#', array('class' => 'fa fa-close delete-link', 'id' => $data->id . '-delete'));
            ?>
        </div>
        <span class="event-text">
            <?php
            echo Formatter::convertLineBreaks(x2base::convertUrls($data->getText()));
            ?>

                <?php
                    $location = $data->location;
                    if ($location) {          
                ?>
                    <a target="_blank" href='<?php echo $location->getLocationLink(X2Html::fa('crosshairs'),true); ?>'><?php echo X2Html::fa('fa-location-arrow'); ?></a>  
                <?php
                    }
                ?>
        </span>
        <div class='event-bottom-row'>
            <span class="comment-age x2-hint" id="<?php echo $data->id . "-" . $data->timestamp; ?>" 
                  style="<?php echo $style; ?>"
                  title="<?php echo Formatter::formatFeedTimestamp($data->timestamp); ?>">
                      <?php echo Formatter::formatFeedTimestamp($data->timestamp); ?>
            </span> 
            <span>

            </span>
            <span class='event-icon-button-container'>
                <?php
                echo CHtml::link(
                        CHtml::tag(
                                'span', array(
                            'class' => 'feed-comment-icon fa fa-comment-o active-icon',
                            'title' => Yii::t('profile', 'Comment on this post')
                                ), ' ') .
                        '<span title="' . CHtml::encode(Yii::t('profile', 'View comments')) . '"
                           id="' . $data->id . '-comment-count" class="comment-count" 
                           val="' . $commentCount . '">' .
                        ($commentCount > 0 ? "<b>" . $commentCount . "</b>" : $commentCount) .
                        '</span>', '#', array(
                    'class' => 'comment-link', 'id' => $data->id . '-link'));
                ?>
                <?php
                echo CHtml::link(
                        CHtml::tag(
                                'span', array(
                            'class' => 'feed-comment-icon fa fa-comment inactive-icon',
                            'title' => Yii::t('profile', 'Hide comments'),
                            'style' => 'font-weight: bold;'
                                ), ' '), '#', array(
                    'class' => 'comment-hide-link', 'id' => $data->id . '-hide-link',
                    'style' => 'display:none;'
                        )
                );
                ?>

                <?php
                $important = ($data->important == 1);
                //echo CHtml::link(Yii::t('app','Broadcast Event'),'#',array('class'=>'important-link x2-hint','id'=>$data->id.'-important-link','style'=>($important?'display:none;':''),'title'=>Yii::t('app','Broadcasting an event will make it visible to any user viewing your events on the activity feed--regardless of type filters.')));
                // echo " | ";
                echo CHtml::link(
                        CHtml::tag('span', array(
                            'class' => 'feed-make-important-icon fa fa-exclamation-circle active-icon',
                                ), ' '), '#', array(
                    'class' => 'important-link x2-hint', 'id' => $data->id . '-important-link',
                    'style' => ($important ? 'display:none;' : ''), 'title' => Yii::t('app', 'Designating an event as important will make it visible to any user viewing ' .
                            'your events on the activity feed--regardless of type filters.')
                        )
                );
                echo CHtml::link(
                        CHtml::tag('span', array(
                            'class' => 'feed-make-unimportant-icon fa fa-exclamation-circle inactive-icon',
                            'title' => CHtml::encode(Yii::t('profile', 'Make unimportant'))
                                ), ' '), '#', array(
                    'class' => 'unimportant-link', 'id' => $data->id . '-unimportant-link',
                    'style' => ($important ? '' : 'display:none;')
                        )
                );
                ?>

                <?php
                if (Yii::app()->params->isAdmin) {
                    // echo " | ";
                    $sticky = ($data->sticky == 1);
                    echo CHtml::link(
                            CHtml::tag('span', array(
                                'class' => 'sticky-icon fa fa-thumb-tack active-icon',
                                    ), ' '), '#', array(
                        'class' => 'sticky-link x2-hint', 'id' => $data->id . '-sticky-link',
                        'style' => ($sticky ? 'display:none;' : ''),
                        'title' => Yii::t('app', 'Making an event sticky will cause it to always ' .
                                'show up at the top of the feed.')
                            )
                    );
                    echo CHtml::link(
                            CHtml::tag('span', array(
                                'class' => 'unsticky-icon fa fa-thumb-tack inactive-icon',
                                'title' => Yii::t('profile', 'Undo Sticky')
                                    ), ' '), '#', array(
                        'class' => 'unsticky-link', 'id' => $data->id . '-unsticky-link',
                        'style' => ($sticky ? '' : 'display:none;')
                            )
                    );
                }
                ?>
                <?php
                $likeDisplay = $likedPost ? 'display:none' : '';
                $unlikeDisplay = !$likedPost ? 'display:none' : '';
                // echo " | ";
                echo CHtml::tag('span', array(
                    'id' => $data->id . '-like-button',
                    'class' => 'like-button',
                    'style' => $likeDisplay
                        ), X2Html::fa('fa-thumbs-up', array(
                            'class' => 'like-icon active-icon',
                            'title' => CHtml::encode(Yii::t('app', 'Like this post')),
                        ))
                );
                echo CHtml::link(
                        CHtml::tag('span', array(
                            'class' => 'unlike-icon fa fa-thumbs-up inactive-icon',
                            'title' => CHtml::encode(Yii::t('app', 'Unlike this post')),
                                ), ' '), '#', array(
                    'id' => $data->id . '-unlike-button',
                    'class' => 'unlike-button',
                    'style' => $unlikeDisplay
                        )
                );
                echo CHtml::link(
                        $likeCount, '#', array(
                    'id' => $data->id . '-like-count',
                    'class' => 'like-count active-icon',
                        )
                );
                // echo " | ";
                echo CHtml::link(
                        CHtml::tag('span', array(
                            'class' => 'broadcast-icon fa fa-bullhorn active-icon',
                            'title' => CHtml::encode(Yii::t('app', 'Broadcast this post')),
                                ), ' '), '#', array(
                    'id' => $data->id . '-broadcast-button',
                    'class' => 'broadcast-button',
                        )
                );

                if (Yii::app()->settings->enableMaps && ($data->location || (isset($associatedModel) && $associatedModel instanceof Actions && $associatedModel->location))) {
                    $location = $data->location ? $data->location : $associatedModel->location;
                    echo '<span style="margin: 10px;">';
                    echo $location->getLocationLink(X2Html::fa('crosshairs'));
                    echo '</span>';
                }
                ?>
            </span>
        </div>
                <?php ?>
    </div>
    <div id="<?php echo $data->id ?>-like-history-box" class="like-history-box" 
         style="display:none;clear:both;">
        <div id="<?php echo $data->id ?>-likes" ></div>
    </div>
    <div id="<?php echo $data->id ?>-comment-box" class="comment-box" 
         style="display:none;clear:both;">
        <div id="<?php echo $data->id ?>-comments" ></div>
<?php
echo "<div style='margin-left:70px;margin-top:5px;'>" .
 CHtml::link(
        '<span class="fa fa-plus"></span>' .
        Yii::t('app', "Add Comment"), '#', array(
    'onclick' =>
    '$(this).toggle();
                         $("#' . $data->id . '-comment-form").show();
                         return false;'
        )
) . "</div>";
echo "<div style='margin-left:10px;display:none;' id='" . $data->id . "-comment-form'>";
echo CHtml::beginForm(
        '', 'get', array(
    'id' => 'addReply-' . $data->id,
        // 'onsubmit'=>'commentSubmit('.$data->id.');return false;'
));
echo CHtml::textArea($data->id . '-comment', '', array('class' => 'comment-textbox x2-textarea'));
echo CHtml::submitButton(
        Yii::t('app', 'Submit'), array('class' => 'x2-button comment-submit', 'style' => 'margin-left: 60px;'));
echo CHtml::endForm();

echo "</div>";
?>
    </div>
</div>
        <?php
        if ($data->important && !empty($data->linkColor)) {
            Yii::app()->clientScript->registerScript($data->id . '-link-colors', "
    $('#{$data->id}-feed-box a').css('color','" . str_replace('%23', '#', $data->linkColor) . "');
");
        }
        /*
          <div class="view">
          <div class="deleteButton">
          <?php echo CHtml::link('[x]',array('deleteNote','id'=>$data->id)); //,array('class'=>'x2-button') ?>
          <?php //echo CHtml::link("<img src='".Yii::app()->request->baseUrl."/images/deleteButton.png' />",array("deleteNote","id"=>$data->id)); ?>
          </div>

          <b><?php echo CHtml::encode($data->getAttributeLabel('createdBy')); ?>:</b>
          <?php echo CHtml::encode($data->createdBy); ?>
          <br />

          <b><?php echo CHtml::encode($data->getAttributeLabel('createDate')); ?>:</b>
          <?php echo CHtml::encode($data->createDate); ?>
          <br /><br />
          <b><?php echo CHtml::encode($data->getAttributeLabel('note')); ?>:</b>
          <?php echo CHtml::encode($data->note); ?>
          <br />
          </div>
         */
        ?>
