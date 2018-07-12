<?php

include_once "../appg/_settings.php";

$em = new ECShiftManager($Connection, $WarningCollector, $Session);

function parseEventData($inputpath){
    $payload = json_decode(file_get_contents($inputpath), true);

    // Unpack event structure
    $unpack_event = function($event) {
        $evt = $event['msys'];
        $evtclass = array_keys($evt)[0];
        return $evtclass;
    };

    $events = array_map($unpack_event, $payload);

    if ($events[0] == 'message_event'){
        $messageId   = $payload[0]['msys']['message_event']['message_id'];
        $emailStatus = $payload[0]['msys']['message_event']['type'];
        $ecEmailId  = $payload[0]['msys']['message_event']['rcpt_meta']['ecshiftid'];
        $regionId  = $payload[0]['msys']['message_event']['rcpt_meta']['regionid'];

        return [$messageId, $emailStatus, $ecEmailId, $regionId];
    }
    else if ($events[0] == 'track_event'){
        $messageId   = $payload[0]['msys']['track_event']['message_id'];
        $emailStatus = $payload[0]['msys']['track_event']['type'];
        $ecEmailId  = $payload[0]['msys']['track_event']['rcpt_meta']['ecshiftid'];
        $regionId  = $payload[0]['msys']['track_event']['rcpt_meta']['regionid'];

        return [$messageId, $emailStatus, $ecEmailId, $regionId];
    }
    else {
        return false;
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    list($messageId, $emailStatus, $ecEmailId, $regionId) = parseEventData('php://input');
    header('Content-Type: application/json');

    if(regionId == $regionId) {
        $status = $em->GetEmailStatus($emailStatus);
        $em->SaveECReportEmailData($status, $emailStatus, $ecEmailId);
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
} else {
    header('HTTP/1.1 405 Method not allowed');
    header('Allow: GET, POST');
    echo('Not allowed');
}
