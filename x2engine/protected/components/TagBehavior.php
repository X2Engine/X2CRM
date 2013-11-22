<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * TagBehavior class file.
 *
 * @package X2CRM.components
 * TagBehavior adds and removes tags from x2_tags when a record is created, updated or deleted
 */
class TagBehavior extends CActiveRecordBehavior {
	/**
	 * @var a cache of all tags associated with the owner model
	 */
	protected $_tags = null;

	/**
	 * Responds to {@link CModel::onAfterSave} event.

	 * Matches tags provided they:
	 *    - start with a #
	 *    - consist of these characters: UTF-8 letters, numbers, _ and - (but only in the middle of the tag)
	 *    - come after a space or . or are at the beginning
	 *    - are not in quotes
	 *
	 * Looks up any current tag records, and saves a tag record for each new tag.
	 * Note: does not delete tags when they are removed from text fields (this would screw with manual tagging)
	 *
	 * @param CModelEvent $event event parameter
	 */
	public function afterSave($event) {
		// look up current tags
		$oldTags = $this->getTags();
		$newTags = array();

		foreach($this->scanForTags() as $tag) {
			if(!in_array($tag,$oldTags)) {	// don't add duplicates if there are already tags
				$tagModel = new Tags;
				$tagModel->tag = $tag;		// includes the #
				$tagModel->type = get_class($this->getOwner());
				$tagModel->itemId = $this->getOwner()->id;
				$tagModel->itemName = $this->getOwner()->name;
				$tagModel->taggedBy = Yii::app()->getSuModel()->username;
				$tagModel->timestamp = time();
				if($tagModel->save())
					$newTags[] = $tag;
			}
		}
		$this->_tags = $newTags + $oldTags;	// update tag cache

		if(!empty($newTags)) {
			X2Flow::trigger('RecordTagAddTrigger',array(
				'model'=>$this->getOwner(),
				'tags'=>$newTags,
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
		$tags = array();
		foreach($this->getOwner()->getFields(true) as $fieldName => $field) {
			if($field->type === 'varchar' || $field->type === 'text') {
				$matches = array();
				if(preg_match_all('/(?:^|\s|\.)(#\w+[-\w]+\w+|#\w+)(?:$|[^\'"])/u',$this->getOwner()->$fieldName,$matches)) {		// extract the tags
					foreach($matches[1] as $match) {
						if(!in_array($match,$tags))
							$tags[] = $match;
					}
				}
			}
		}
		return $tags;
	}

	/**
	 * Looks up the tags associated with the owner model.
	 * Uses {@link $tags} as a cache to prevent repeated queries.
	 *
	 * @return array an array of tags
	 */
	public function getTags() {
		if($this->_tags === null) {
			$this->_tags = Yii::app()->db->createCommand()
				->select('tag')
				->from(CActiveRecord::model('Tags')->tableName())
				->where('type=:type AND itemId=:itemId',array(':type'=>get_class($this->getOwner()),':itemId'=>$this->getOwner()->id))
				->queryColumn();
		}
		return $this->_tags;
	}

	/**
	 * Tests whether the owner model has any (OR mode) or all (AND mode) of the provided tags
	 *
	 * @param mixed $tags sring or array of strings containing tags
	 * @param array $mode logic mode (either "AND" or "OR") for the test
	 * @return boolean the test result
	 */
	public function hasTags($tags,$mode='OR') {
		$matches = array_intersect($this->getTags(),(array)$tags);

		if($mode === 'AND')
			return count($matches) === count((array)$tags);		// all tags must be present
		else
			return count($matches) > 0;		// at least one tag must be present
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
            
		foreach((array)$tags as $tagName) {
			if(empty($tagName))
				continue;
			if(!in_array($tagName,$this->getTags())) {	// check for duplicate tag
				$tag = new Tags;
                $tag->tag = $tagName;
				$tag->itemId = $this->getOwner()->id;
				$tag->type = get_class($this->getOwner());
				$tag->taggedBy = Yii::app()->getSuModel()->username;
				$tag->timestamp = time();
				$tag->itemName = $this->getOwner()->name;

				if($tag->save()) {
					$this->_tags[] = $tag->tag;	// update tag cache
					$addedTags[] = $tagName;
					$result = true;
				} else {
					throw new CHttpException('Failed saving tag: '.json_encode($tag->attributes));
				}
			}
		}
		X2Flow::trigger('RecordTagAddTrigger',array(
			'model'=>$this->getOwner(),
			'tags'=>$addedTags,
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

		foreach((array)$tags as $tag) {
			if(empty($tag))
				continue;

			$attributes = array(
				'type'=>get_class($this->getOwner()),
				'itemId'=>$this->getOwner()->id,
				'tag'=>$tag
			);
			if(in_array($tag,$this->getTags()) && 
               CActiveRecord::model('Tags')->deleteAllByAttributes($attributes) > 0) {

				if(false !== $offset = array_search($tag,$this->_tags))
					unset($this->_tags[$offset]);	// update tag cache

				$removedTags[] = $tag;
				$result = true;
			}
		}
		X2Flow::trigger('RecordTagRemoveTrigger',array(
			'model'=>$this->getOwner(),
			'tags'=>$removedTags,
		));

		return $result;
	}

	/**
	 * Deletes all tags associated with the owner model
	 */
	public function clearTags() {
		$this->_tags = array();	// clear tag cache

		return (bool)CActiveRecord::model('Tags')->deleteAllByAttributes(array(
			'type'=>get_class($this->getOwner()),
			'itemId'=>$this->getOwner()->id)
		);
	}
}
