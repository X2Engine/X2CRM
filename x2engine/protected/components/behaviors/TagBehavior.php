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
 * TagBehavior class file.
 *
 * @package application.components
 * TagBehavior adds and removes tags from x2_tags when a record is created, updated or deleted
 */
class TagBehavior extends ActiveRecordBehavior {

    /**
     * @var bool $disableTagScanning
     */
    public $disableTagScanning = false; 

    /**
     * @var a cache of all tags associated with the owner model
     */
    protected $_tags = null;

    private $flowTriggersEnabled = true; 

    public function rules () {
        return array (
            array ('tags', 'safe', 'on' => 'search'),
        );
    }

    public function enableTagTriggers () {
        $this->flowTriggersEnabled = true;
    }

    public function disableTagTriggers () {
        $this->flowTriggersEnabled = false;
    }

    /**
     * Responds to {@link CModel::onAfterSave} event.
     *
     * Matches tags provided they:
     *    - start with a #
     *    - consist of these characters: UTF-8 letters, numbers, _ and - (but only in the middle 
     *        of the tag)
     *    - come after a space or . or are at the beginning
     *    - are not in quotes
     *
     * Looks up any current tag records, and saves a tag record for each new tag.
     * Note: does not delete tags when they are removed from text fields (this would screw with 
     *  manual tagging)
     *
     * @param CModelEvent $event event parameter
     */
    public function afterSave($event) {
        // look up current tags
        $oldTags = $this->getTags();
        $newTags = array();

        foreach ($this->scanForTags() as $tag) {
            if (!$this->hasTag ($tag, $oldTags)) { // don't add duplicates if there are already tags
                $tagModel = new Tags;
                $tagModel->tag = $tag;  
                $tagModel->type = get_class($this->getOwner());
                $tagModel->itemId = $this->getOwner()->id;
                $tagModel->itemName = $this->getOwner()->name;
                $tagModel->taggedBy = Yii::app()->getSuName();
                $tagModel->timestamp = time();
                if ($tagModel->save())
                    $newTags[] = $tag;
            }
        }
        $this->_tags = $newTags + $oldTags; // update tag cache

        if (!empty($newTags) && $this->flowTriggersEnabled) {
            X2Flow::trigger('RecordTagAddTrigger', array(
                'model' => $this->getOwner(),
                'tags' => $newTags,
            ));
        }
    }

    /**
     * Responds to {@link CActiveRecord::onAfterDelete} event.
     * Deletes all the tags for this model
     *
     * @param CModelEvent $event event parameter
     */
    public function afterDelete($event) {
        $this->clearTags();
    }

    /**
     * Scans through every 'varchar' and 'text' field in the owner model for tags.
     *
     * @return array an array of tags
     */
    public function scanForTags() {
        if ($this->disableTagScanning) return array ();
        $tags = array();

        if (Yii::app()->settings->disableAutomaticRecordTagging) {
            return array();
        }

        // Type of fields to search in 
        $fieldTypes = array (
            'text'
        );

        foreach ($this->getOwner()->getFields(true) as $fieldName => $field) {
            if (!in_array($field->type, $fieldTypes)) {
                continue;
            }

            $text = $this->owner->$fieldName;

            $matches = $this->matchTags ($text);
            $tags = array_merge($matches, $tags);
        }
        $tags = array_unique($tags);
        return $tags;
    }

    /**
     * Finds all tag matches in text
     * @param string $text
     * @return array
     */
    public function matchTags($text) {

        // Array of excludes such as style tags, href attributes, etc
        $excludes = array(
            '/<style[^<]*<\/style>/',
            '/style="[^"]*"/',
            '/style=\'[^\']*\'/',
        );

        foreach ($excludes as $exp) {
            $text = preg_replace($exp, '', $text);
        }

        // Primary expression to filter out tags
        $exp = '/(?:|\s)(#(?:\w+|\w[-\w]+\w))(?:$|\s)/u';

        $matches = array();
        preg_match_all($exp, $text, $matches);

        return $matches[1];
    }

    /**
     * @param string $tag 
     * @param array|null $oldTags 
     * @return true if record has tag already, false otherwise
     */
    public function hasTag ($tag, array $oldTags=null, $refresh=false) {
        $oldTags = $oldTags === null ? $this->getTags ($refresh) : $oldTags;
        return in_array (strtolower (Tags::normalizeTag ($tag)), array_map (function ($tag) {
            return strtolower ($tag); 
        }, $oldTags));
    }

    /**
     * Tests whether the owner model has any (OR mode) or all (AND mode) of the provided tags
     *
     * @param mixed $tags sring or array of strings containing tags
     * @param array $mode logic mode (either "AND" or "OR") for the test
     * @return boolean the test result
     */
    public function hasTags($tags, $mode = 'OR') {
        $matches = array_intersect($this->getTags(), Tags::normalizeTags((array) $tags));

        if ($mode === 'AND')
            return count($matches) === count((array) $tags);  // all tags must be present
        else
            return count($matches) > 0;  // at least one tag must be present
    }

    /**
     * Looks up the tags associated with the owner model.
     * Uses {@link $tags} as a cache to prevent repeated queries.
     *
     * @return array an array of tags
     */
    public function getTags($refreshCache = false) {
        if ($this->_tags === null || $refreshCache) {
            $this->_tags = Yii::app()->db->createCommand()
                ->select('tag')
                ->from(CActiveRecord::model('Tags')->tableName())
                ->where(
                    'type=:type AND itemId=:itemId', 
                    array(
                        ':type' => get_class($this->getOwner()), 
                        ':itemId' => $this->getOwner()->id))
                ->queryColumn();
        }
        return $this->_tags;
    }

    public function setTags ($tags, $rawInput=false) {
        if (!$rawInput)
            $tags = is_string ($tags) ? array_map (function ($tag) {
                return trim ($tag);
            }, explode (Tags::DELIM, $tags)) : $tags;
        $this->_tags = $tags;
    }

    public function compareTags (CDbCriteria $criteria) {
        $tags = $this->tags;
        $inQuery = array ();
        $params = array (
            ':type' => get_class ($this->owner),
        );
        for ($i = 0; $i < count ($tags); $i++) {
            if ($tags[$i] === '') {
                unset ($tags[$i]);
                $i--;
                continue;
            } else {
                $inQuery[] = 'b.tag LIKE :'.$i;
                $params[':'.$i] = '%'.$tags[$i].'%';
            }
        }
        $tagConditions = implode (' OR ',$inQuery);

        if ($tagConditions) {
            $criteria->distinct = true;
            $criteria->join .= ' JOIN x2_tags b ON (b.itemId=t.id AND b.type=:type '.
                'AND ('.$tagConditions.'))';
            $criteria->params = $params;
        }

        return $criteria;
    }

    public function renderTagInput () {
        $clone = clone $this->owner;  
        $clone->setTags (implode (', ', $this->tags), true);

        return CHtml::activeTextField ($clone, 'tags');
    }

    /**
     * Adds the specified tag(s) to the owner model, but not
     * if the tag has already been added.
     * @param mixed $tags a string or array of strings containing tags
     * @return boolean whether or not at least one tag was added successfully
     */
    public function addTags($tags) {
        $result = false;
        $addedTags = array();

        foreach ((array) $tags as $tagName) {
            if (empty($tagName))
                continue;
            if (!$this->hasTag ($tagName)) { // check for duplicate tag
                $tag = new Tags;
                $tag->tag = Tags::normalizeTag ($tagName);
                $tag->itemId = $this->getOwner()->id;
                $tag->type = get_class($this->getOwner());
                $tag->taggedBy = Yii::app()->getSuName();
                $tag->timestamp = time();
                $tag->itemName = $this->getOwner()->name;

                if ($tag->save()) {
                    $this->_tags[] = $tag->tag; // update tag cache
                    $addedTags[] = $tagName;
                    $result = true;
                } else {
                    throw new CHttpException(
                        422, 'Failed saving tag due to errors: ' . json_encode($tag->errors));
                }
            }
        }
        if ($this->flowTriggersEnabled)
            X2Flow::trigger('RecordTagAddTrigger', array(
                'model' => $this->getOwner(),
                'tags' => $addedTags,
            ));

        return $result;
    }

    /**
     * Removes the specified tag(s) from the owner model
     * @param mixed $tags a string or array of strings containing tags
     * @return boolean whether or not at least one tag was deleted successfully
     */
    public function removeTags($tags) {
        $result = false;
        $removedTags = array();
        $tags = Tags::normalizeTags((array) $tags);

        foreach ((array) $tags as $tag) {
            if (empty($tag))
                continue;

            $attributes = array(
                'type' => get_class($this->getOwner()),
                'itemId' => $this->getOwner()->id,
                'tag' => $tag
            );
            if ($this->hasTag ($tag) &&
                CActiveRecord::model('Tags')->deleteAllByAttributes($attributes) > 0) {

                if (false !== $offset = array_search($tag, $this->_tags))
                    unset($this->_tags[$offset]); // update tag cache

                $removedTags[] = $tag;
                $result = true;
            }
        }
        if ($this->flowTriggersEnabled)
            X2Flow::trigger('RecordTagRemoveTrigger', array(
                'model' => $this->getOwner(),
                'tags' => $removedTags,
            ));

        return $result;
    }

    /**
     * Deletes all tags associated with the owner model
     */
    public function clearTags() {
        $this->_tags = array(); // clear tag cache

        return (bool) CActiveRecord::model('Tags')->deleteAllByAttributes(array(
            'type' => get_class($this->getOwner()),
            'itemId' => $this->getOwner()->id)
        );
    }

}
