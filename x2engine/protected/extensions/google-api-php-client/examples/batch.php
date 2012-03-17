<?php
session_start();

require_once "../src/apiClient.php";

$client = new apiClient();
$client->discover('plus');
$client->setScopes(array('https://www.googleapis.com/auth/plus.me'));

if (isset($_GET['logout'])) {
  unset($_SESSION['token']);
}

if (isset($_GET['code'])) {
  $client->authenticate();
  $_SESSION['token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
  $ret = apiBatch::execute(
    $client->plus->activities->list(array('userId' => 'me', 'collection' => 'public'), 'listActivities'),
    $client->plus->people->get(array('userId' => 'me'), 'getPerson')
  );

  print "<pre>" . filter_var(print_r($ret, true), FILTER_SANITIZE_STRING) . "</pre>";
} else {
  $client->authenticate();
}