<?php
require_once(__DIR__.'/../components/LGPL/PlancakeEmailParser.php');
require_once(__DIR__.'/../components/EmlParse.php');

$data = file_get_contents("php://stdin");
$parser = new EmlParse($data);
$parser->testing = true;

// Test basic features:
print("\n\nFROM:\n");
print_r($parser->getFrom());

print("\n\nTO:\n");
print_r($parser->getTo());

//$groups = array();
//preg_match($parser->qHeader,$parser->getBody(),$groups);
//print_r($groups);
//print_r($parser->qHeader);

// Test parsing addresses from the raw email body:
print("\n\nFROM (fwd):\n");
print_r($parser->getForwardedFrom());

print("\n\nBODY:\n");
print_r($parser->bodyCleanup());

//print_r(EmlContact::$defaultFields);
//print_r(EmlContact::$defaultActionFields);
?>
