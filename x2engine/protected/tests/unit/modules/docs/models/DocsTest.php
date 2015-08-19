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

    public static $contactsField = 'associatedContacts';
    public static $accountsField = 'accountName';

    public function testReplaceVariables() {
        $quote = $this->quotes ('docsTest'); 
        $contact = $this->contacts ('testAnyone'); 
        $account = $this->accounts('testQuote');
        $attrs = $quote->getAttributes ();
        $contactAttrs = $contact->getAttributes ();
        $accountAttrs = $account->getAttributes();

        // ensure that tokens with references to customized class names get properly replaced
        $quoteTemplate = array ();
        foreach ($attrs as $name => $val) {
            $quoteTemplate[$name] = "{".$name."}";
        }
        foreach ($contactAttrs as $name => $val) {
            $quoteTemplate[$name] = "{".self::$contactsField.".$name}";
        }
        foreach ($accountAttrs as $name => $val) {
            $quoteTemplate[$name] = "{" . self::$accountsField . ".$name}";
        }
        foreach (array_intersect (array_keys ($contactAttrs), array_keys ($attrs), array_keys($accountAttrs)) as $name) {
            unset ($quoteTemplate[$name]);
            unset ($attrs[$name]);
            unset ($contactAttrs[$name]);
            unset ($accountAttrs[$name]);
        }
        // add quotes template-specific token
        $quoteTemplate['dateNow'] = '{dateNow}';
        $quoteTemplate['lineItems'] = '{lineItems}';
        $quoteTemplate['quoteOrInvoice'] = '{quoteOrInvoice}';
        $quoteTemplate = CJSON::encode ($quoteTemplate);
        $str = Docs::replaceVariables ($quoteTemplate, $quote, array (), false , false); 
        $this->assertEquals (
            array_merge (
                array_map (function ($elem) { return (string) $elem; }, $attrs),
                array_map (function ($elem) { return (string) $elem; }, $contactAttrs),
                array_map (function ($elem) { return (string) $elem; }, $accountAttrs),
                array (
                    'lineItems' => preg_replace( "/\r|\n/", "", $quote->productTable(true)),
                    'dateNow' => date("F d, Y", time()),
                    'quoteOrInvoice' => 
                        Yii::t('quotes', $quote->type=='invoice' ? 
                            'Invoice' : Modules::displayName(false, "Quotes")),
                )
            ),
            CJSON::decode ($str)
        );
    }
	
}

?>
