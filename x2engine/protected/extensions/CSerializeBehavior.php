<?php
/**
 * CSerializeBehavior class file.
 *
 * @author Kenrick Buchanan <nsbucky@gmail.com>
 * @license http://www.yiiframework.com/license/
 * 
 */

/**
 * CSerializeBehavior allows a model to specify some attributes to be
 * arrays and serialized upon save and unserialized after a Find() function
 * is called on the model.
 *
 *<pre>
 * public function behaviors()
 *	{
 *		return array(
 *			'CSerializeBehavior' => array(
 *				'class' => 'application.behaviors.CSerializeBehavior',
 *				'serialAttributes' => array('validator_options'),
 *			)
 *		);
 *	}
 * </pre>
 * 
*/
class CSerializeBehavior extends CActiveRecordBehavior {
	/**
	* @var array The name of the attribute(s) to serialize/unserialize
	*/
    public $serialAttributes = array();
	
	/**
	* Responds to {@link CModel::onBeforeSave} event.
	* Sets the values of the creation or modified attributes as configured
	* 
	* @param CModelEvent event parameter
	*/
	public function beforeSave($event) {		
        if (count($this->serialAttributes)) {
            foreach($this->serialAttributes as $attribute) {
                $_att = $this->getOwner()->$attribute;
                
                // check if the attribute is an array, and serialize it
                if(is_array($_att)) {
                    $this->getOwner()->$attribute = serialize($_att);			
                } else {
                    // if its a string, lets see if its unserializable, if not
                    // fuck it set it to null
                    if(is_scalar($_att)) {
                        $a = @unserialize($_att);
                        if($a === false) {
                            $this->getOwner()->$attribute = null;
                        }
                    }
                }
            }
        }
	}
	
	/** convert the saved as a serialized string back into an array, cause
	 *  thats how we want to use it anyways ya know?
	 */
	public function afterSave($event)
	{
		if(count($this->serialAttributes)) {
			foreach($this->serialAttributes as $attribute) {
				$_att = $this->getOwner()->$attribute;
				if(!empty($_att)
				   && is_scalar($_att)) {
					$a = @unserialize($_att);
					if($a !== false) {
						$this->getOwner()->$attribute = $a;
					} else {
						$this->getOwner()->$attribute = null;
					}
				}
			}			
		}
	}
    
    public function afterFind($event)
    {		
        if(count($this->serialAttributes)) {
            foreach($this->serialAttributes as $attribute) {				
                $_att = $this->getOwner()->$attribute;
                if(!empty($_att)
                   && is_scalar($_att)) {
                    $a = @unserialize($_att);					
                    if($a !== false) {
                        $this->getOwner()->$attribute = $a;
                    } else {
						$this->getOwner()->$attribute = array();
					}
                }
            }
        }
    }
}
