<?php
class UniqueAttributesValidator extends CValidator {

	/**
	 * The attributes boud in the unique contstraint with attribute
	 * @var string
	 */
	public $with;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object,$attribute) {
		$with = explode(",", $this->with);
		if (count($with) < 1)
			throw new Exception("Attribute 'with' not set");
		$uniqueValidator = new CUniqueValidator();
		$uniqueValidator->attributes = array($attribute);
		$uniqueValidator->message = $this->message;
		$uniqueValidator->on = $this->on;
		$conditionParams = array();
		$params = array();
		foreach ($with as $attribute) {
			$conditionParams[] = "`{$attribute}`=:{$attribute}";
			$params[":{$attribute}"] = $object->$attribute;
		}
		$condition = implode(" AND ", $conditionParams);
		$uniqueValidator->criteria = array(
			'condition'=>$condition,
			'params'=>$params
		);
		$uniqueValidator->validate($object);
	}

}
