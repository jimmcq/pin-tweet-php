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
    if(empty($serialConnection->_dbstate) || $serialConnection->_dbstate != SERIAL_DEVICE_SET) {
        exit("Could not set serial device.\n");
    }

    $serialConnection->confBaudRate(57600);
    $serialConnection->confCharacterLength(8);
    $serialConnection->confStopBits(1);
    $serialConnection->confParity("none");
    $serialConnection->confFlowControl("none");

    $serialConnection->deviceOpen('r+');

    $serialConnection->sendMessage("zc ver");
    $response = $serialConnection->readPort();

    // check that the version higher or equal to 1.18
    $pattern = '/([0-9]+\.[0-9]{1,2})/';
    preg_match($pattern, $response, $matches);
    if(floatval($matches[0]) < 1.18)
    {
        exit("Communication patch v1.18 or later must be installed.\n");
    }

    return TRUE;

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

    for($i = 1; $i <= $maxPlayers; $i++) {
        $serialConnection->sendMessage('zc mod 0x5c073564 '.$i);
        $response = $serialConnection->readPort();

        $pattern = '/=([0-9a-fA-F]+)/';
        preg_match($pattern, $response, $matches);
        $scores[$i] = hexdec($matches[1]);

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

if(file_exists('scores.json')) {
    $prevScores = json_decode(file_get_contents('scores.json'), TRUE);
}

initSerial($config['serial']);

// setup signal handlers
if (function_exists('imap_open')) {
    pcntl_signal(SIGINT, "shutdown");
    pcntl_signal(SIGTERM, "shutdown");
    pcntl_signal(SIGHUP,  "shutdown");
}

$prevScore = !empty($prevScores['highscore']) ? $prevScores['highscore'] : 0;
while(TRUE) {

    $newScore = queryScores($config['machine']['maxPlayers']);

    if(!empty($newScore)) {
        if($newScore < $prevScore) {
            $status = 'Score of '.$prevScore.' posted to '.$config['machine']['name'];

            $result = postTweet($config['OAuth'], $status);
            if(empty($result)) {
                exit ("Tweet not posted\n");
            }

             file_put_contents('scores.json', json_encode(array('highscore'=>$prevScore), JSON_PRETTY_PRINT));
        }

        $prevScore = $newScore;
    }
    sleep(10);
}
