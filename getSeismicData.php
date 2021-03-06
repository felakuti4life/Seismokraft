<?php

/****************************
 * SEISMOKRAFT 1.0
 * by Ethan Geller and Elizabeth Davis
 *
 * Copyright (c) 2013, Ethan Geller
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials
 * provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * SUMMARY: This script handles all of the server side queries to the IRIS server and pulls metadata about the recent seismic events, as well as time series data.
 */

$ERROR = "<h3>ERROR: Something went wrong with this particular search. Try using different parameters and reloading.</h3>";
$error_printed = false;
/** PARAMETERS */

//caught exception backup seismic URL
$backupAudioURL = 'http://service.iris.edu/irisws/timeseries/1/query?net=CM&sta=CAP2&cha=BHZ&start=2014-09-20T14:00:40.0000&dur=8000&envelope=true&output=audio&audiocompress=true&audiosamplerate=44100&loc=01&scale=AUTO';
$backupPlotURL = 'http://service.iris.edu/irisws/timeseries/1/query?net=CM&sta=CAP2&cha=BHZ&start=2014-09-20T14:00:40.0000&dur=8000&envelope=true&output=plot&loc=01&scale=AUTO';
$backupSampleRate = '20.0';

//set Seismic Event indices: 0 represents latest seismic event above the minimum magnitude given, 1 the event after that, etc.
$eventOneIndex = 0;
$eventTwoIndex = 1;
$eventThreeIndex = 2;

//in samples
$LENGTH = 8000;

//Whether we are printing debug statements
$DEBUG = false;

if($DEBUG) ini_set('display_errors', 'On');
else ini_set('display_errors', 'Off');
//whether we check for further location codes
$CHECK_MORE_LOCATION_CODES = false;

/** CONSTANTS */
//current date:
$DATE = date('Y-m-d');

//Base url for FDSN webservice
$FDSN_URL = "http://service.iris.edu/fdsnws/";

//Start time for the time range for when we are getting quakes for. Always an arbitrarily early time, since we get the three most recent
$START_TIME = "2010-02-27T06:30:00";

//Minimum maginitude for the seismic event:
$MIN_MAG = 5.5;

//Maximum magnitude for the seismic event:
$MAX_MAG = 9.0;

//Our result code if our query fails:
$NODATA = "404";

//parse optional URL args
if(isset($_GET["length"])) $LENGTH = floatval($_GET["length"]);
if(isset($_GET["min_mag"])) $MIN_MAG = floatval($_GET["min_mag"]);
if(isset($_GET["max_mag"])) $MAX_MAG = floatval($_GET["max_mag"]);
if(isset($_GET["ev1"]) && !is_null($_GET["ev1"])) $eventOneIndex = intval($_GET["ev1"]);
if(isset($_GET["ev2"]) && !is_null($_GET["ev1"])) $eventTwoIndex = intval($_GET["ev2"]);
if(isset($_GET["ev3"]) && !is_null($_GET["ev1"])) $eventThreeIndex = intval($_GET["ev3"]);

$url = $FDSN_URL .
    "event/1/query?" .
    "starttime=" . $START_TIME .
    "&endtime=" . $DATE .
    "&minmag=" . strval($MIN_MAG) .
    "&maxmag=" . strval($MAX_MAG) .
    "&includeallorigins=" . "true" .
    "&orderby=" . "time" .
    "&format=" . "xml" .
    "&limit=" . "16" .
    "&nodata=" . $NODATA;
if($DEBUG) echo 'First URL:' . $url;
$xml = file_get_contents($url);
$quakeTable = new SimpleXMLElement($xml);

//class definitions
class Location
{
    var $depth;
    var $lng;
    var $lat;

    function __construct()
    {
        $this->depth = 0.0;
        $this->lng = 0.0;
        $this->lat = 0.0;
    }

}

class SeismicEvent
{
    var $location;
    var $locationDescription;
    var $impulseDate;
    var $timeSeriesStartDate;
    var $magnitude;
    var $nearestNetworkCode;
    var $nearestStationCode;
    var $stationUrlTest;
    var $channelUrlTest;
    var $channelCode;
    var $locationCode;
    var $stationAudioURL;
    var $stationPlotURL;
    var $audioBuffer;
    var $sampleRate;
    var $maxRadius = 3.5;
    var $networkIdx;
    var $stationIdx;

    var $failed = false;

    function __construct($eventIndex)
    {
        //set variables based on the quakeTable
        global $quakeTable;
        $this->location = new Location();
        $event = $quakeTable->eventParameters->event[$eventIndex];
        $origin = $event->origin;
        $this->location->depth = floatval($origin->depth->value);
        $this->location->lng = floatval($origin->longitude->value);
        $this->location->lat = floatval($origin->latitude->value);
        $this->locationDescription = $event->description->text;
        $this->impulseDate = $origin->time->value;
        $this->magnitude = floatval($event->magnitude->mag->value);
        $this->setNetworkAndStations(0,0);
        $this->setTimeSeriesStartDate();
        $this->setChannelAndLocation(0);
        $this->setAudioAndPlotURL();
    }


    public function setTimeSeriesStartDate()
    {
        $parsedDate = new DateTime('2014-09-21T14:00:40.1000');
        $parsedDate->modify('-24 hours');
        $dateString = $parsedDate->format('c');
        $this->timeSeriesStartDate = substr_replace($dateString, ".0000", 19);
    }

    public function setNetworkAndStations($i, $k)
    {
        global $FDSN_URL, $NODATA, $DEBUG;
        $MAX_NETWORK_ATTEMPTS = 5;
        $MAX_STATION_ATTEMPTS = 10;
        $MAX_RADIUS_ATTEMPT = 12;

        if($k > $MAX_STATION_ATTEMPTS){
            if($DEBUG) echo "Warning: Max station attempts reached! ";
            $this->stationIdx =0;
            $this->networkIdx++;
            $this->setNetworkAndStations($this->networkIdx, $this->stationIdx);
        }
        else if($i > $MAX_NETWORK_ATTEMPTS){
            if($DEBUG) echo "Warning: Max network attempts reached! ";
            $this->stationIdx =0;
            $this->networkIdx =0;
            $this->maxRadius++;
            $this->setNetworkAndStations($this->networkIdx, $this->stationIdx);
        }

        else if($this->maxRadius > $MAX_RADIUS_ATTEMPT) return;

        try {
            $stationUrl = $FDSN_URL .
                "station/1/query?" .
                "starttime=" . "2013-06-07T01:00:00" .
                "&endtime=" . $this->impulseDate .
                "&level=" . "station" .
                "&format=" . "xml" .
                "&lat=" . strval($this->location->lat) .
                "&lon=" . strval($this->location->lng) .
                "&maxradius=" . strval($this->maxRadius) .
                "&includeavailability=" . "true" .
                "&includerestricted=" . "false" .
                "&nodata=" . $NODATA;

            $stationXml = file_get_contents($stationUrl);
            $station_table = new SimpleXMLElement($stationXml);

            $this->stationUrlTest = $stationUrl;
            $this->nearestNetworkCode = $station_table->Network[$i]['code'];
            //TODO: Implement a search for closest station to event. For now, first station alphabetically is retrieved.
            $this->nearestStationCode = $station_table->Network[$i]->Station[$k]['code'];
        }
        catch(Exception $e){
            $this->failed = true;
            if($DEBUG) echo 'Warning: '.$e->getMessage() . 'for Station URL: ' . $stationUrl;
            $this->stationIdx++;
            //$this->setNetworkAndStations($this->networkIdx, $this->stationIdx);

        }
    }

    public function setChannelAndLocation($i)
    {
        //TODO: Modify getter to only pull waveforms from channels with code ?HZ
        global $FDSN_URL, $NODATA, $CHECK_MORE_LOCATION_CODES, $DEBUG;
        //How many channel attempts will occur before we give up
        $MAX_CHANNEL_ATTEMPTS = 10;
        if ($i > $MAX_CHANNEL_ATTEMPTS) {
            if($DEBUG) echo "Warning: Max Channel and Locations attempts reached!";
            $this->stationIdx++;

            $this->setNetworkAndStations($this->networkIdx,$this->stationIdx);
        }
        try {


            $channelUrl = $FDSN_URL .
                "station/1/query?" .
                "net=" . $this->nearestNetworkCode .
                "&sta=" . $this->nearestStationCode .
                "&starttime=" . "2013-06-07T01:00:00" .
                "&endtime=" . $this->impulseDate .
                "&level=" . "channel" .
                "&format=" . "xml" .
                "&nodata=" . $NODATA;
            $channelXml = file_get_contents($channelUrl);
            $channel_table = new SimpleXMLElement($channelXml);

            $this->channelUrlTest = $channelUrl;

            $this->channelCode = $channel_table->Network->Station->Channel[$i]['code'];
            $this->locationCode = $channel_table->Network->Station->Channel[$i]['locationCode'];
            $this->sampleRate = $channel_table->Network->Station->Channel[$i]->SampleRate;
            if ($CHECK_MORE_LOCATION_CODES) $this->check_further_location_codes($MAX_CHANNEL_ATTEMPTS, $channel_table);
            //use -- for empty location codes
            if ($this->location_code_is_empty()) $this->locationCode = '--';
            if ($this->channelCode != 'BHZ') $this->setChannelAndLocation($i + 1);
        }
        catch(Exception $e){
            $this->failed = true;
            if($DEBUG) echo 'Warning: '.$e->getMessage();
        }
    }

    public function setAudioAndPlotURL()
    {
        global $backupAudioURL, $backupPlotURL, $backupSampleRate, $DEBUG, $ERROR, $LENGTH, $error_printed;
        try {
            $IRIS_URL = "http://service.iris.edu/irisws/";

            $this->stationAudioURL = $IRIS_URL .
                "timeseries/1/query?" .
                "net=" . $this->nearestNetworkCode .
                "&sta=" . $this->nearestStationCode .
                "&cha=" . $this->channelCode .
                "&start=" . $this->timeSeriesStartDate .
                "&dur=" . strval($LENGTH) .
                "&envelope=" . "true" .
                "&output=" . "audio" .
                "&audiocompress=" . "true" .
                "&audiosamplerate=" . "44100" .
                "&scale=" . "AUTO" .
                "&loc=" . $this->locationCode;

            $this->stationPlotURL = $IRIS_URL .
                "timeseries/1/query?" .
                "net=" . $this->nearestNetworkCode .
                "&sta=" . $this->nearestStationCode .
                "&cha=" . $this->channelCode .
                "&start=" . $this->timeSeriesStartDate .
                "&dur=" . strval($LENGTH) .
                "&envelope=" . "true" .
                "&output=" . "plot" .
                "&scale=" . "AUTO" .
                "&loc=" . $this->locationCode;

            //$this->audioBuffer = file_get_contents($this->stationAudioURL);
        }
        catch(Exception $e){
            $this->failed = true;
            if($DEBUG) echo 'Warning: '.$e->getMessage();
        }
        //check for 404s
        $this->is404($this->stationAudioURL);
        $this->is404($this->stationPlotURL);

        //use the backup
        if($this->failed){
            $this->stationAudioURL = $backupAudioURL;
            $this->stationPlotURL = $backupPlotURL;
            $this->sampleRate = $backupSampleRate;
            $this->locationDescription = "COLUMBIA";
            $this->magnitude = 5.5;
            $this->impulseDate = "2014-09-20T14:00:40.0000";
            if(!$error_printed) echo $ERROR;
            $error_printed = true;
        }
    }

    /** Check down channel list for other location codes */
    public function check_further_location_codes($MAX_CHANNEL_ATTEMPTS, $channel_table)
    {
        $i = 1;
        while ($this->location_code_is_empty() && $i < $MAX_CHANNEL_ATTEMPTS) {
            $this->channelCode = $channel_table->Network->Station->Channel[$i]['code'];
            $this->locationCode = $channel_table->Network->Station->Channel[$i]['locationCode'];
            $i++;
        }
    }

    /*
     * @return true if the url specified returns a 404 result code
     */
    public function is404($url){
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if($httpCode == 404) {
            $this->failed = true;
            curl_close($handle);
            return true;
        }

        curl_close($handle);
        return false;
    }

    /**
     * @return bool
     */
    public function location_code_is_empty()
    {
        return trim($this->locationCode, ' ') == '';
    }
}


$eventOne = new SeismicEvent($eventOneIndex);
while($eventOne->failed){
    $eventOneIndex++;
    $eventOne = new SeismicEvent($eventOneIndex);
}
$eventTwoIndex = $eventOneIndex +1;
$eventTwo = new SeismicEvent($eventTwoIndex);
while($eventTwo->failed){
    $eventTwoIndex++;
    $eventTwo = new SeismicEvent($eventTwoIndex);
}
$eventThreeIndex = $eventTwoIndex + 1;
$eventThree = new SeismicEvent($eventThreeIndex);
while($eventThree->failed){
    $eventThreeIndex++;
    $eventThree = new SeismicEvent($eventThreeIndex);
}

/** UNIT TESTS */
function echo_endpoints($event)
{
    global $url;
    echo "<h5> quaketable url:" . $url . "</h5>";
    echo "<h5> station and network query: " . $event->stationUrlTest . "</h5>";
    echo "<h5> channel and location query: " . $event->channelUrlTest . "</h5>";
    echo "<h5> approx. radius away: " . $event->maxRadius . "</h5>";
    echo "<h5> audio query: <a href=\"" . $event->stationAudioURL . "\"> here </a></h5>";
}

function echo_event($event)
{
    echo "<p> Event in " . $event->locationDescription . "</p>" .
        "<p> Magnitude: " . $event->magnitude .
        "</p> <p>Longitude: " . $event->location->lng .
        "</p> <p>Latitude: " . $event->location->lat .
        "</p> <p>Depth: " . $event->location->depth .
        "</p> <p>Network: " . $event->nearestNetworkCode .
        "</p> <p>Station: " . $event->nearestStationCode .
        "</p> <p>Channel: " . $event->channelCode .
        "</p> <p>Location: " . $event->locationCode .
        "</p> <p>Sample Rate: " . $event->sampleRate .
        "</p> <p>Date:" . $event->impulseDate .
        "</p>";
}

if ($DEBUG) {
    echo_endpoints($eventOne);
    echo_event($eventOne);
    echo_endpoints($eventTwo);
    echo_event($eventTwo);
    echo_endpoints($eventThree);
    echo_event($eventThree);
}
?>