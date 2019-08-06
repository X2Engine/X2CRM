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




Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->getBaseUrl() . '/css/components/sortableWidget/views/twitterFeedWidget.css');

foreach ($tweets as $tweet) {

    $statusClickLink = $statusLink . $tweet->id_str;
    ?>
    <div class='tweet-container'>
        <div class='tweet-container-inner'>
            <div class='x2-col'>
                <a href='https://twitter.com/<?php echo urlencode($tweet->user->screen_name); ?>'> 
                    <img src="<?php echo $tweet->user->profile_image_url_https; ?>" />
                </a>
            </div>
            <div class='x2-col'>
                <div class='x2-row'>
                    <a href='https://twitter.com/<?php echo urlencode($tweet->user->screen_name); ?>' 
                       class='author-name'>
                           <?php
                           echo CHtml::encode($tweet->user->name);
                           ?>
                    </a>
                    <a href='https://twitter.com/<?php echo urlencode($tweet->user->screen_name); ?>' 
                       class='author-username'>
                           <?php
                           echo '@' . CHtml::encode($tweet->user->screen_name);
                           ?>
                    </a>

                    <?php
                    $timestamp = strtotime($tweet->created_at);
                    $date = getDate($timestamp);
                    $nowTs = time();
                    $now = getDate($nowTs);
                    $formattedTimestamp = '';
                    if ($date['year'] !== $now['year']) { // long format
                        $formattedTimestamp = Yii::app()->dateFormatter->format(
                                'd MMM yy', $timestamp);
                    } else if ($date['yday'] !== $now['yday']) { // month day format
                        $formattedTimestamp = Yii::app()->dateFormatter->format(
                                'd MMM', $timestamp);
                    } else if ($now['hours'] - $date['hours'] > 1) { // hour format
                        $diffTs = $nowTs - $timestamp;
                        $formattedTimestamp = floor($diffTs / 60 / 60) . 'h';
                    } else if ($now['minutes'] - $date['minutes'] > 1) { // minute format
                        $diffTs = $nowTs - $timestamp;
                        $formattedTimestamp = floor($diffTs / 60) . 'm';
                    } else { // second format
                        $diffTs = $nowTs - $timestamp;
                        $formattedTimestamp = $diffTs . 's';
                    }
                    echo '<a style="text-decoration: none; color: grey;" href="' . $statusClickLink . '">' .
                    CHtml::encode($formattedTimestamp) .
                    '</a>';
                    ?>

                </div>
                <div class='x2-row'>
                    <?php
                    echo $tweet->text;
                    ?>
                </div>
                <div class='x2-row button-row'>
                    <div class='buttons-container'>
                        <a title="<?php echo CHtml::encode(Yii::t('app', 'Reply')); ?>" 
                           href='https://twitter.com/intent/tweet?in_reply_to=<?php echo $tweet->id_str; ?>'
                           class='reply-button pseudo-link'></a>
                        <a title="<?php echo CHtml::encode(Yii::t('app', 'Retweet')); ?>" 
                           href='https://twitter.com/intent/retweet?tweet_id=<?php echo $tweet->id_str; ?>'
                           class='retweet-button pseudo-link'></a>
                        <a title="<?php echo CHtml::encode(Yii::t('app', 'Favorite')); ?>" 
                           href='https://twitter.com/intent/favorite?tweet_id=<?php echo $tweet->id_str; ?>'
                           class='favorite-button pseudo-link'></a>
                    </div>
                </div>
            </div>
            <div class='clearfix'></div>
        </div>
    </div>
    <?php
}