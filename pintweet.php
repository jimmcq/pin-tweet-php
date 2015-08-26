<?php

require "PHP-Serial/src/PhpSerial.php";

require "twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

// tick use required as of PHP 4.3.0
declare(ticks = 1);

function initSerial($serialData) {
    global $serialConnection;

    $serialConnection = new PhpSerial;
    $serialConnection->deviceSet($serialData['device']);

    $serialConnection->confBaudRate(57600);
    $serialConnection->confCharacterLength(8);
    $serialConnection->confStopBits(1);
    $serialConnection->confParity("none");
    $serialConnection->confFlowControl("none");

    $serialConnection->deviceOpen('r+');

    $serialConnection->sendMessage("zc ver");
    $response = $serialConnection->readPort();

    // check that it's higher or equal to 1.18
}

// signal handler function
function shutdown($signo) {
    global $serialConnection;

    $serialConnection->deviceClose();

}

function queryScores($maxPlayers) {
    global $serialConnection;

    if ($maxPlayers < 1) {
        exit("maxPlayers must be at least 1.\n");
    }

    $scores = array();

    for($i = 1; $i < $maxPlayers; $i++) {
        $serial->sendMessage('zc mod 0x5c073564 '.$i);
        $response = $serial->readPort();

        // Parse score= with hex2dec
    }

    return max($scores);
}

function postTweet($OAuth, $status) {
    if (empty($status)) {
        return FALSE;
    }

    $connection = new TwitterOAuth($OAuth['consumer_key'], $OAuth['consumer_secret'], $OAuth['access_token'], $OAuth['access_token_secret']);
    $result = $connection->post("statuses/update", array("status" => $status));

    if (empty($result->id)) {
        exit("Error posting tweet.\n");
    } else {
        return TRUE;
    }

}

$config = json_decode(file_get_contents('config.json'), TRUE);
if(empty($config)) {
    exit("Couldn't load config.json file. Look at config-sample.json for examples of how to format your config.json\n");
}

$prevScores = json_decode(file_get_contents('scores.json'), TRUE);

initSerial($config['serial']);

while(TRUE) {

    $newScore = queryScores($config['maxPlayers']);

    if(!empty($newScores)) {

        // DO STUFF

        /*
        $result = postTweet($config['OAuth'], $status);
        if(empty($result)) {
            exit ("Tweet not posted\n");
        }
        */

        // file_put_contents('scores.json', json_encode($newScores, JSON_PRETTY_PRINT));

    }
}
