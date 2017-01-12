<?php

// CONFIGURATION

// Test database:
$dbHost = '127.0.0.1';
$dbUser = 'isaiah';
$dbPass = 'SV7SOnNyHPyQfR';
$dbName = 'isaiah';

$con = mysqli_connect($dbHost,$dbUser,$dbPass,$dbName) or die('Could not connect: ' . mysqli_error($con));

$zero_t = 0;
$short_t = 5;
$medium_t = 20;
$long_t = 60;

function post_event ($con, $time, $user, $text) {
        sleep($time);
        mysqli_query($con, sprintf("INSERT INTO x2_events (type, text, user, timestamp, lastUpdated) VALUES ('feed', '%s', '%s', %s, %s)", $text, $user, time(), time()));
        return mysqli_insert_id($con);
}

function post_comment ($con, $time, $event, $user, $text) {
        sleep($time);
        mysqli_query($con, sprintf("INSERT INTO x2_events (type, text, associationType, associationId, user, timestamp, lastUpdated) VALUES ('comment', '%s', 'Events', %s, '%s', %s, %s)", $text, $event, $user, time(), time()));
}


/** REAL TIME EVENTS */

$last_id = post_event($con, $short_t, 'admin', 'I love the color scheme of this new update!');
post_comment($con, $short_t, $last_id, 'bto', 'Exactly my thoughts!');

mysqli_close($con);
