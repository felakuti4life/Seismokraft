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
 * SUMMARY: This script handles all of the serverside queries to the IRIS server and pulls metadata about the recent seismic events, as well as time series data.

 */


//set Seismic Event indices: 0 represents latest seismic event above the minimum magnitude given, 1 the event after that, etc.
$eventOneIndex = 0;
$eventTwoIndex = 2;
$eventThreeIndex = 3;

$DATE = date('Y-m-d');
//pull event info for last three seismic events
$url = "http://service.iris.edu/fdsnws/event/1/query?starttime=2010-02-27T06:30:00&endtime=" . $DATE . "&minmag=3.5&maxmag=5.0&includeallorigins=true&orderby=time&format=xml&limit=8&nodata=404";
$xml = file_get_contents($url);
$quakeTable = new SimpleXMLElement($xml);

//class definitions
class Location
{
    var $depth;
    var $lng;
    var $lat;

    public function __construct()
    {
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

    function __construct($eventIndex)
    {
        global $quakeTable;
        $this->location = new Location();
        $this->location->depth = floatval($quakeTable->eventParameters->event[$eventIndex]->origin->depth->value);
        $this->location->lng = floatval($quakeTable->eventParameters->event[$eventIndex]->origin->longitude->value);
        $this->location->lat = floatval($quakeTable->eventParameters->event[$eventIndex]->origin->latitude->value);
        $this->locationDescription = $quakeTable->eventParameters->event[$eventIndex]->description->text;
        $this->impulseDate = $quakeTable->eventParameters->event[$eventIndex]->origin->time->value;
        $this->magnitude = floatval($quakeTable->eventParameters->event[$eventIndex]->magnitude->mag->value);
        $this->setNetworkAndStations();
        $this->setTimeSeriesStartDate();
        $this->setChannelAndLocation();
        $this->setAudioAndPlotURL();
    }


    public function setTimeSeriesStartDate()
    {
        $parsedDate = new DateTime('2013-10-06T14:00:40.1000');
        $parsedDate->modify('-8 hours');
        $dateString = $parsedDate->format('c');
        $this->timeSeriesStartDate = substr_replace($dateString, ".0000", 19);
    }

    public function setNetworkAndStations()
    {
        $stationUrl = "http://service.iris.edu/fdsnws/station/1/query?starttime=2013-06-07T01:00:00&endtime=" . $this->impulseDate .
            "&level=station&format=xml&lat=" . strval($this->location->lat) .
            "&lon=" . strval($this->location->lng) .
            "&maxradius=6.0&nodata=404";
        $stationXml = file_get_contents($stationUrl);
        $station_table = new SimpleXMLElement($stationXml);

        $this->stationUrlTest = $stationUrl;
        $this->nearestNetworkCode = $station_table->Network[0]['code'];
        //TODO: Implement a search for closest station to event. For now, first station alphabetically is retrieved.
        $this->nearestStationCode = $station_table->Network[0]->Station[0]['code'];
    }

    public function setChannelAndLocation()
    {
        $channelUrl = "http://service.iris.edu/fdsnws/station/1/query?net=" . $this->nearestNetworkCode .
            "&sta=" . $this->nearestStationCode . "&starttime=2013-06-07T01:00:00&endtime=" . $this->impulseDate .
            "&level=channel&format=xml&nodata=404";
        $channelXml = file_get_contents($channelUrl);
        $channel_table = new SimpleXMLElement($channelXml);

        $this->channelUrlTest = $channelUrl;

        $this->channelCode = $channel_table->Network->Station->Channel[0]['code'];
        $this->locationCode = $channel_table->Network->Station->Channel[0]['locationCode'];
        $limit = 1;
        while (trim($this->locationCode, " ") == '' && $limit < 5) {
            $this->channelCode = $channel_table->Network->Station->Channel[$limit]['code'];
            $this->locationCode = $channel_table->Network->Station->Channel[$limit]['locationCode'];
            $limit++;
        }
        if (trim($this->locationCode) == '') {
            $this->locationCode = "00";
        }
    }

    public function setAudioAndPlotURL()
    {
        $this->stationAudioURL = "http://service.iris.edu/irisws/timeseries/1/query?net=" . $this->nearestNetworkCode .
            "&sta=" . $this->nearestStationCode .
            "&cha=" . $this->channelCode .
            "&start=" . $this->timeSeriesStartDate . "&dur=8000&envelope=true&output=audio&audiocompress=true&audiosamplerate=3000&loc=" . $this->locationCode/*."&taper=0.5,HAMMING"*/
        ;

        $this->stationPlotURL = "http://service.iris.edu/irisws/timeseries/1/query?net=" . $this->nearestNetworkCode .
            "&sta=" . $this->nearestStationCode .
            "&cha=" . $this->channelCode .
            "&start=" . $this->timeSeriesStartDate .
            "&dur=8000&envelope=true&output=plot&loc=" . $this->locationCode/*."&taper=0.5,HAMMING"*/
        ;

        $this->audioBuffer = file_get_contents($this->stationAudioURL);
    }
}

//TODO: move these to the __construct function


$eventOne = new SeismicEvent($eventOneIndex);
$eventTwo = new SeismicEvent($eventTwoIndex);
$eventThree = new SeismicEvent($eventThreeIndex);
?>