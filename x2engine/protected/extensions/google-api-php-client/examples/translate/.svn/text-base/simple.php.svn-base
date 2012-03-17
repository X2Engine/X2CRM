<?php
require_once '../../src/apiClient.php';
require_once '../../src/contrib/apiTranslateService.php';

$client = new apiClient();
$client->setApplicationName('Google Translate PHP Starter Application');

// Visit https://code.google.com/apis/console?api=translate to generate your
// client id, client secret, and to register your redirect uri.
// $client->setDeveloperKey('insert_your_developer_key');
$service = new apiTranslateService($client);

$langs = $service->languages->listLanguages();
print "<h1>Languages</h1><pre>" . print_r($langs, true) . "</pre>";

$translations = $service->translations->listTranslations('Hello', 'hi');
print "<h1>Translations</h1><pre>" . print_r($translations, true) . "</pre>";
