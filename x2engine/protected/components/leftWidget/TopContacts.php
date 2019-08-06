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




Yii::import('application.components.leftWidget.LeftWidget');

/**
 * Widget for displaying the "Top Contacts" portlet
 * @package application.components 
 */
class TopContacts extends LeftWidget {

    const ITEM_SEPARATOR = ',';
    const PROP_SEPARATOR = ':';

	public $id = 'top-contacts';

	public $widgetLabel = 'Favorites';

    public static function addBookmark (CActiveRecord $record) {
        $type = get_class ($record);
        $id = $record->id;
        $user = Yii::app()->params->profile->user;
        if (empty ($user->topContacts)) {
            $bookmarks = array ();
        } else {
            $bookmarks = explode (self::ITEM_SEPARATOR, $user->topContacts);
        }
        foreach ($bookmarks as $item) {
            $parts = explode (self::PROP_SEPARATOR, $item);
            if (count ($parts) === 1 && $type === 'Contacts' && $id === $item) {
                return false;
            }
            if ($type === $parts[0] && $parts[1] === $id) {
                return false;
            }
        }
        $bookmarks[] = $type.':'.$id;
        $user->topContacts = implode (self::ITEM_SEPARATOR, $bookmarks);
        if ($user->update ('topContacts')) return true;
    }

    public static function removeBookmark (CActiveRecord $record) {
        $type = get_class ($record);
        $id = $record->id;
        $user = Yii::app()->params->profile->user;
        if (empty ($user->topContacts)) {
            $bookmarks = array ();
        } else {
            $bookmarks = explode (self::ITEM_SEPARATOR, $user->topContacts);
        }
        $found = false;
        $count = count ($bookmarks);
        for ($i = 0; $i < $count; $i++) {
            $item = $bookmarks[$i];
            $parts = explode (self::PROP_SEPARATOR, $item);
            if (count ($parts) === 1 && $type === 'Contacts' && $id === $item) {
                $found = true;
                unset ($bookmarks[$i]);
                break;
            }
            if ($type === $parts[0] && $parts[1] === $id) {
                $found = true;
                unset ($bookmarks[$i]);
                break;
            }
        }
        $user->topContacts = implode (self::ITEM_SEPARATOR, $bookmarks);
        if ($found && $user->update ('topContacts')) return true;
    }

    public static function getBookmarkedRecords () {
        $user = Yii::app()->params->profile->user;

        $bookmarks = empty($user->topContacts) ? 
            array() : explode(TopContacts::ITEM_SEPARATOR, $user->topContacts);

        $bookmarkRecords = array();
        foreach($bookmarks as $item){
            $parts = explode (TopContacts::PROP_SEPARATOR, $item);
            if (count ($parts) === 1) {
                $record = X2Model::model ('Contacts')->findByPk($item);
            } elseif (count ($parts) === 2) {
                $type = $parts[0];
                try {
                    $model = X2Model::model ($type, false);
                } catch (CHttpException $e) {
                    continue;
                }
                $id = $parts[1];
                $record = $model->findByPk ($id);
            } else {
                continue;
            }
            if(!is_null($record)) //only include contact if the contact ID exists
                $bookmarkRecords[] = $record;
        }
        return $bookmarkRecords;
    }

	protected function renderContent() {
            Yii::t('app','Favorites');
		$this->render('topContacts',array(
			'bookmarkRecords'=>User::getTopContacts()
		));
	}
}

?>
