<?php

Yii::import('application.modules.accounts.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.docs.models.*');
Yii::import('application.modules.quotes.models.*');
Yii::import('application.modules.quotes.controllers.*');
Yii::import('application.modules.quotes.*');
Yii::import('application.controllers.*');
Yii::import('application.modules.users.models.*');

/**
 * Test case for {@link Docs} model class.
 * @package application.tests.unit.modules.docs.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class DocsTest extends X2DbTestCase {

	public $fixtures = array(
        'accounts' => 'Accounts',
        'contacts' => 'Contacts',
        'quotes' => 'Quote',
        'users' => 'User',
        'docs' => array ('Docs', '.DocsTest'),
    );

    public static $customQuotesTitle = 'DocsTestQuotesTitle';
    public static $customContactsTitle = 'DocsTestContactsTitle';
    public static function setUpBeforeClass () {
        Yii::app()->controller = new QuotesController (
            'quote', new QuotesModule ('quotes', null));

        Yii::app()->db->createCommand ("
            update x2_modules set title=:title where name='quotes'
        ")->execute (array (
            ':title' => self::$customQuotesTitle
        ));
        Yii::app()->db->createCommand ("
            update x2_modules set title=:title where name='contacts'
        ")->execute (array (
            ':title' => self::$customContactsTitle
        ));
        Modules::displayName(false, "Quotes"); // add titles to Modules title cache
        Modules::displayName(false, "Contacts"); // add titles to Modules title cache
        parent::setUpBeforeClass ();
    }

    public static function tearDownAfterClass () {
        Yii::app()->db->createCommand ("
            update x2_modules set title=:title where name='quotes'
        ")->execute (array (
            ':title' => 'Quotes',
        ));
        Yii::app()->db->createCommand ("
            update x2_modules set title=:title where name='contacts'
        ")->execute (array (
            ':title' => 'Contacts',
        ));
        parent::tearDownAfterClass ();
    }

    public function testDocsPermissions () {
        $auth = TestingAuxLib::loadAuthManagerMock ();
        TestingAuxLib::loadX2NonWebUser ();

        // user has docs update access
        $user = $this->users ('testUser');
        $auth->setAccess ('AdminIndex', $user->id, array (), false);
        TestingAuxLib::suLogin ('testuser');
        $auth->setAccess ('DocsAdmin', $user->id, array (), false);
        $auth->setAccess ('DocsUpdateAccess', $user->id, array (
            'X2Model' => new Docs
        ), true);

        // can't be edited since edit permissions list is empty
        $doc = $this->docs ('0'); 
        $this->assertFalse ((bool) $doc->checkEditPermissions ());

        // "testuser" is in the edit permissions list
        $doc = $this->docs ('1'); 
        $this->assertTrue ((bool) $doc->checkEditPermissions ());
        $doc = $this->docs ('3'); 
        $this->assertTrue ((bool) $doc->checkEditPermissions ());

        // testuser created the the doc
        $doc = $this->docs ('2'); 
        $this->assertTrue ((bool) $doc->checkEditPermissions ());




        // user has docs private update access
        $auth->clearCache ();
        $auth->setAccess ('AdminIndex', $user->id, array (), false);
        $auth->setAccess ('DocsAdmin', $user->id, array (), false);
        $auth->setAccess ('DocsUpdateAccess', $user->id, array (
            'X2Model' => new Docs
        ), false);
        $auth->setAccess ('DocsPrivateUpdateAccess', $user->id, array (
            'X2Model' => new Docs
        ), true);

        // can't be edited since edit permissions list is empty
        $doc = $this->docs ('0'); 
        $this->assertFalse ((bool) $doc->checkEditPermissions ());

        // "testuser" is in the edit permissions list but since testuser only has private update 
        // access, doc cannot be edited
        $doc = $this->docs ('1'); 
        $this->assertFalse ((bool) $doc->checkEditPermissions ());
        $doc = $this->docs ('3'); 
        $this->assertFalse ((bool) $doc->checkEditPermissions ());

        // testuser created the the doc, so they can edit it
        $doc = $this->docs ('2'); 
        $this->assertTrue ((bool) $doc->checkEditPermissions ());




        // user has docs admin access
        $auth->clearCache ();
        $auth->setAccess ('AdminIndex', $user->id, array (), false);
        $auth->setAccess ('DocsAdmin', $user->id, array (), true);
        $auth->setAccess ('DocsUpdateAccess', $user->id, array (
            'X2Model' => new Docs
        ), false);
        $auth->setAccess ('DocsPrivateUpdateAccess', $user->id, array (
            'X2Model' => new Docs
        ), false);

        // user is docs admin
        $doc = $this->docs ('0'); 
        $this->assertTrue ((bool) $doc->checkEditPermissions ());

        // user is docs admin
        $doc = $this->docs ('1'); 
        $this->assertTrue ((bool) $doc->checkEditPermissions ());

        // user is docs admin
        $doc = $this->docs ('2'); 
        $this->assertTrue ((bool) $doc->checkEditPermissions ());

        TestingAuxLib::restoreX2WebUser ();
        TestingAuxLib::restoreX2AuthManager ();
    }

	public function testReplaceVariables() {
        $quote = $this->quotes ('docsTest'); 
        $contact = $this->contacts ('testAnyone'); 
        $attrs = $quote->getAttributes ();
        $contactAttrs = $contact->getAttributes ();

//        $quoteTemplate = array ();
//        foreach ($attrs as $name => &$val) {
//            $quoteTemplate[$name] = "{Quote.$name}";
//        }
//        foreach ($contactAttrs as $name => &$val) {
//            $quoteTemplate[$name] = "{Contact.$name}";
//        }
//        foreach (array_intersect (array_keys ($contactAttrs), array_keys ($attrs)) as $name) {
//            unset ($quoteTemplate[$name]);
//            unset ($attrs[$name]);
//            unset ($contactAttrs[$name]);
//        }
//        $quoteTemplate = CJSON::encode ($quoteTemplate);
//        $str = Docs::replaceVariables ($quoteTemplate, $quote, array (), false , false); 
//        $this->assertEquals (
//            array_merge (
//                array_map (function ($elem) { return (string) $elem; }, $attrs),
//                array_map (function ($elem) { return (string) $elem; }, $contactAttrs)
//            ),
//            CJSON::decode ($str)
//        );

        // ensure that tokens with references to customized class names get properly replaced
        $quoteTemplate = array ();
        foreach ($attrs as $name => $val) {
            $quoteTemplate[$name] = "{".self::$customQuotesTitle.".$name}";
        }
        foreach ($contactAttrs as $name => $val) {
            $quoteTemplate[$name] = "{".self::$customContactsTitle.".$name}";
        }
        foreach (array_intersect (array_keys ($contactAttrs), array_keys ($attrs)) as $name) {
            unset ($quoteTemplate[$name]);
            unset ($attrs[$name]);
            unset ($contactAttrs[$name]);
        }
        // add quotes template-specific token
        $quoteTemplate['dateNow'] = '{'.self::$customQuotesTitle.'.quoteOrInvoice}';
        $quoteTemplate = CJSON::encode ($quoteTemplate);
        $str = Docs::replaceVariables ($quoteTemplate, $quote, array (), false , false); 
        $this->assertEquals (
            array_merge (
                array_map (function ($elem) { return (string) $elem; }, $attrs),
                array_map (function ($elem) { return (string) $elem; }, $contactAttrs),
                array (
                    'dateNow' => 
                        Yii::t('quotes', $quote->type=='invoice' ? 
                            'Invoice' : self::$customQuotesTitle),
                )
            ),
            CJSON::decode ($str)
        );

// old test
//        $this->markTestSkipped('This test has been very badly broken by '
//                . 'changes made to X2Model.getAttribute and '
//                . 'Formatter.replaceVariables which apparently make it so that '
//                . 'no combination of arguments sent to Docs.replaceVariables '
//                . 'versus to X2Model.getAttribute produce an equivalent list '
//                . 'of values. Thus, for now, abandon hope of fixing it.');
//
//		// Test replacement in emails:
//		$contact = $this->contacts('testAnyone');
//		$textIn = array();
//		$textOutExpected = array();
//		$delimiter = "\n@@|@@\n";
//		foreach($contact->attributes as $name=>$value) {
//			$textIn[] = '{'.$name.'}';
//			$textOutExpected[] = $contact->renderAttribute($name, false);
//		}
//		$textIn = implode($delimiter,$textIn);
//		$textOutExpected = implode($delimiter,$textOutExpected);
//		$textOut = Docs::replaceVariables($textIn,$contact);
//		$this->assertEquals($textOutExpected,$textOut,'Failed asserting that email template replacement succeeded.');
//		
//		// Test replacement in Quote bodies:
//		$quote = $this->quotes('docsTest');
//		$classes = array(
//			'Accounts',
//			'Contacts',
//			'Quote'
//			); // In that order
//		$models = array(
//			'Accounts' => $this->accounts('testQuote'),
//			'Contacts' => $this->contacts('testAnyone'),
//			'Quote' => $this->quotes('docsTest'),
//		);
//		$textIn = array();
//		$textOutExpected = array();
//		$delimiter = "\n*|*\n";
//		
//		foreach($classes as $class) {
//			$classNick = rtrim($class,'s');
//			$attrs = array_keys($class::model()->attributeLabels());
//			foreach($attrs as $attribute) {
//				$textIn[] = '{'.$classNick.'.'.$attribute.'}';
//				$textOutExpected[] =  empty($models[$class])?'':$models[$class]->renderAttribute($attribute, false);
//			}
//		}
//		$textIn = implode($delimiter,$textIn);
//		$textOutExpected = implode($delimiter,$textOutExpected);
//		$textOut = Docs::replaceVariables($textIn,$quote);
//		$this->assertEquals($textOutExpected,$textOut, 'Failed asserting that Quote template replacement succeeded.');
//		
//		
	}
	
}

?>
