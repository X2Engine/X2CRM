<?php

Yii::setPathOfAlias(
        'Sabre', Yii::getPathOfAlias('application.integration.SabreDAV'));

use Sabre\VObject;

class DavController extends CController {

    public function actionIndex() {
        
        $calendar = X2Model::model('X2Calendar')->findByPk(1);
        $action = Actions::model()->findByPk(82);
        $calendar->sync();
        $calendar->syncActionToCalendar($action);
        echo "Done.";
        exit;
        
        // URLS and Config
        $calendarId = 'b6nsg47rfe658o2fv92opv8v6s@group.calendar.google.com';
        $calendarBaseUrl = 'https://apidata.googleusercontent.com';
        $calendarUrl = $calendarBaseUrl . '/caldav/v2/' . $calendarId . '/events';
        $baseUri = 'http://x2developer.com/x2jake/x2engine';
        $syncToken = ' /caldav/v2/b6nsg47rfe658o2fv92opv8v6s%40group.calendar.google.com/events/sync/ChQIwOfI/MHBxAIQwOfI/MHBxAIYBQ==';

        // Parameters
        $davBase = '{DAV:}';
        $calBase = '{http://calendarserver.org/ns/}';
        $calDataBase = '{urn:ietf:params:xml:ns:caldav}';

        $displayNameKey = $davBase . 'displayname';
        $ctagKey = $calBase . 'getctag';
        $etagKey = $davBase . 'getetag';
        $dataKey = $calDataBase . 'calendar-data';
        $syncTokenKey = $davBase . 'sync-token';

        $auth = new GoogleAuthenticator();
        $auth->flushCredentials(false);
        $token = $auth->getAccessToken(Yii::app()->user->getId());
        $accessToken = json_decode($token, true);

        $client = new X2CalDavClient(array(
            'baseUri' => $baseUri,
            'oAuthToken' => $accessToken['access_token'],
        ));
        
        $result = $client->propFind($calendarUrl, array(
            $displayNameKey,
            $ctagKey,
            $syncTokenKey,
        ));
        echo "Calendar Information";
        printR($result);

        $calendarResult = $client->report($calendarUrl, array(
            $etagKey,
            $dataKey,
        ));

        echo "Event Data";
        $testEvent = null;
        $testEtag = null;
        $testPath = '';
        foreach ($calendarResult as $path => $event) {
            $eventUrl = $calendarBaseUrl . $path;
            $data = $event[$dataKey];
            $obj = VObject\Reader::read($data);
            $testEvent = $obj;
            $testEtag = $event[$etagKey];
            $testPath = $path;
            printR(array(
                $eventUrl,
                $event[$etagKey],
                $obj->vevent->summary->value
            ));
        }

        //$syncResult = $client->sync($calendarUrl, $syncToken);
        //echo "New Data Since Initial Sync Token";
        //printR($syncResult);

        $eventPaths = array_keys($calendarResult);
        $eventPath = $eventPaths[0];
        $getResult = $client->get($calendarBaseUrl, $eventPath);
        echo "Get One Event";
        printR($getResult['etag']);

        $multigetResult = $client->multiget($calendarUrl, $eventPaths, array(
            $etagKey,
            $dataKey,
        ));
        echo "Get Multiple Events";
        printR(count($multigetResult));

        $tempSummary = $testEvent->vevent->summary->value;
        $testEvent->vevent->summary->value.= " + UPDATE!";
        $putResult = $client->put($calendarBaseUrl, $testPath, $testEvent->serialize(), $testEtag);
        echo "Update An Event";
        printR($putResult);
        $testEvent->vevent->summary->value = $tempSummary;
        $testGetResult = $client->get($calendarBaseUrl, $testPath);
        $client->put($calendarBaseUrl, $testPath, $testEvent->serialize(), $testGetResult['etag']);

        // TEST CREATE
        $uniqueId = UUID::v4();
        echo "New Event ID";
        printR($uniqueId);
        $newEvent = new VObject\Component\VCalendar();
        $vevent = new VObject\Component\VEvent('VEVENT');
        $startTime = new VObject\Property\DateTime('DTSTART');
        $startTime->setDateTime(new \DateTime('2015-03-25 10:44:00', new \DateTimeZone('America/Los_Angeles')));
        $endTime = new VObject\Property\DateTime('DTEND');
        $endTime->setDateTime(new \DateTime('2015-03-25 11:44:00', new \DateTimeZone('America/Los_Angeles')));
        $vevent->add($startTime);
        $vevent->add($endTime);
        $vevent->add('SUMMARY', 'Test Create Event');
        $vevent->add('UID', $uniqueId);
        $newEvent->add($vevent);
        
        $createResult = $client->put($calendarUrl, '/' . $uniqueId . '.ics', $newEvent->serialize());
        echo "Create An Event";
        printR($createResult);

        // TEST DELETE
        //$delGet = $client->get($calendarUrl, '/' . $uniqueId . '.ics');
        //$deleteResult = $client->delete($calendarUrl,'/' . $uniqueId . '.ics', $delGet['etag']);
        //echo "Delete An Event";
        //printR($deleteResult);
 
        echo "End of Script";
    }

}
