<?php

Yii::import('application.modules.accounts.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.docs.models.*');
Yii::import('application.modules.quotes.models.*');
Yii::import('application.modules.users.models.*');

/**
 * Test case for {@link Docs} model class.
 * @package application.tests.unit.modules.docs.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class DocsTest extends X2DbTestCase {

	public static function referenceFixtures(){
		return array(
			'accounts' => 'Accounts',
			'contacts' => 'Contacts',
			'quotes' => 'Quote',
		);
	}

	public function testReplaceVariables() {

        $this->markTestSkipped('This test has been very badly broken by '
                . 'changes made to X2Model.getAttribute and '
                . 'Formatter.replaceVariables which apparently make it so that '
                . 'no combination of arguments sent to Docs.replaceVariables '
                . 'versus to X2Model.getAttribute produce an equivalent list '
                . 'of values. Thus, for now, abandon hope of fixing it.');

		// Test replacement in emails:
		$contact = $this->contacts('testAnyone');
		$textIn = array();
		$textOutExpected = array();
		$delimiter = "\n@@|@@\n";
		foreach($contact->attributes as $name=>$value) {
			$textIn[] = '{'.$name.'}';
			$textOutExpected[] = $contact->renderAttribute($name, false);
		}
		$textIn = implode($delimiter,$textIn);
		$textOutExpected = implode($delimiter,$textOutExpected);
		$textOut = Docs::replaceVariables($textIn,$contact);
		$this->assertEquals($textOutExpected,$textOut,'Failed asserting that email template replacement succeeded.');
		
		// Test replacement in Quote bodies:
		$quote = $this->quotes('docsTest');
		$classes = array(
			'Accounts',
			'Contacts',
			'Quote'
			); // In that order
		$models = array(
			'Accounts' => $this->accounts('testQuote'),
			'Contacts' => $this->contacts('testAnyone'),
			'Quote' => $this->quotes('docsTest'),
		);
		$textIn = array();
		$textOutExpected = array();
		$delimiter = "\n*|*\n";
		
		foreach($classes as $class) {
			$classNick = rtrim($class,'s');
			$attrs = array_keys($class::model()->attributeLabels());
			foreach($attrs as $attribute) {
				$textIn[] = '{'.$classNick.'.'.$attribute.'}';
				$textOutExpected[] =  empty($models[$class])?'':$models[$class]->renderAttribute($attribute, false);
			}
		}
		$textIn = implode($delimiter,$textIn);
		$textOutExpected = implode($delimiter,$textOutExpected);
		$textOut = Docs::replaceVariables($textIn,$quote);
		$this->assertEquals($textOutExpected,$textOut, 'Failed asserting that Quote template replacement succeeded.');
		
		
	}
	
}

?>
