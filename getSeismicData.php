<?php
/****************************
	SEISMOKRAFT 1.0
	by Ethan Geller
	
	Copyright (c) 2013, Ethan Geller
	All rights reserved.

	Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

	Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
	Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials
	provided with the distribution.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
	MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
	EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
	CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
	EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	
	SUMMARY: This script handles all of the serverside queries to the IRIS server and pulls metadata about the recent seismic events, as well as time series data.

*/
$date = date('Y-m-d');
//pull event info for last three seismic events
$url = "http://service.iris.edu/fdsnws/event/1/query?starttime=2010-02-27T06:30:00&endtime=" .$date ."&minmag=3.5&maxmag=5.0&includeallorigins=true&orderby=time&format=xml&limit=8&nodata=404";
$xml = file_get_contents($url);
$quake_table = new SimpleXMLElement($xml);

//class definitions
class Location {
	var $depth;
	var $lng;
	var $lat;
	
	public function __construct(){
	}
}

class SeismicEvent {
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
	
	public function __construct(){
	}
	
	public function setTimeSeriesStartDate(){
		$parsedDate = new DateTime('2013-10-06T14:00:40.1000');
		$parsedDate->modify('-8 hours');
		$dateString = $parsedDate->format('c');
		$this->timeSeriesStartDate = substr_replace($dateString, ".0000", 19);
	}
	
	public function setNetworkAndStations(){
		$stationUrl="http://service.iris.edu/fdsnws/station/1/query?starttime=2013-06-07T01:00:00&endtime=".$this->impulseDate.
		"&level=station&format=xml&lat=".strval($this->location->lat).
		"&lon=".strval($this->location->lng).
		"&maxradius=6.0&nodata=404";
		$stationXml = file_get_contents($stationUrl);
		$station_table = new SimpleXMLElement($stationXml);
		
		$this->stationUrlTest = $stationUrl;
		$this->nearestNetworkCode = $station_table->Network[0]['code'];
		//TODO: Implement a search for closest station to event. For now, first station alphabetically is retrieved.
		$this->nearestStationCode = $station_table->Network[0]->Station[0]['code'];
	}
	
	public function setChannelAndLocation(){
		$channelUrl="http://service.iris.edu/fdsnws/station/1/query?net=".$this->nearestNetworkCode.
		"&sta=".$this->nearestStationCode."&starttime=2013-06-07T01:00:00&endtime=".$this->impulseDate.
		"&level=channel&format=xml&nodata=404";
		$channelXml= file_get_contents($channelUrl);
		$channel_table = new SimpleXMLElement($channelXml);
		
		$this->channelUrlTest=$channelUrl;
		
		$this->channelCode = $channel_table->Network->Station->Channel[0]['code'];
		$this->locationCode = $channel_table->Network->Station->Channel[0]['locationCode'];
		$i = 1;
		while($this->locationCode==""){
			$this->channelCode = $channel_table->Network->Station->Channel[$i]['code'];
			$this->locationCode = $channel_table->Network->Station->Channel[$i]['locationCode'];
			$i++;
		}
	}
	
	public function setAudioAndPlotURL(){
		$this->stationAudioURL="http://service.iris.edu/irisws/timeseries/1/query?net=".$this->nearestNetworkCode.
		"&sta=".$this->nearestStationCode.
		"&cha=".$this->channelCode.
		"&start=".$this->timeSeriesStartDate."&dur=8000&envelope=true&output=audio&audiocompress=true&audiosamplerate=3000&loc=".$this->locationCode/*."&taper=0.5,HAMMING"*/;
		
		$this->stationPlotURL="http://service.iris.edu/irisws/timeseries/1/query?net=".$this->nearestNetworkCode.
		"&sta=".$this->nearestStationCode.
		"&cha=".$this->channelCode.
		"&start=".$this->timeSeriesStartDate.
		"&dur=8000&envelope=true&output=plot&loc=".$this->locationCode/*."&taper=0.5,HAMMING"*/;
		
		$this->audioBuffer = file_get_contents($this->stationAudioURL);
	}
}

//TODO: move these to the __construct function

//set Seismic Events
$eventOne = new SeismicEvent;
$eventOneIndex = 0;
$eventTwo = new SeismicEvent;
$eventTwoIndex = 2;
$eventThree = new SeismicEvent;
$eventThreeIndex = 3;

//setting up Seismic Event One
$eventOne->location= new Location();
$eventOne->location->depth = floatval($quake_table->eventParameters->event[$eventOneIndex]->origin->depth->value);
$eventOne->location->lng = floatval($quake_table->eventParameters->event[$eventOneIndex]->origin->longitude->value);
$eventOne->location->lat = floatval($quake_table->eventParameters->event[$eventOneIndex]->origin->latitude->value);
$eventOne->locationDescription = $quake_table->eventParameters->event[$eventOneIndex]->description->text;
$eventOne->impulseDate = $quake_table->eventParameters->event[$eventOneIndex]->origin->time->value;
$eventOne->magnitude = floatval($quake_table->eventParameters->event[$eventOneIndex]->magnitude->mag->value);
$eventOne->setNetworkAndStations();
$eventOne->setTimeSeriesStartDate();
$eventOne->setChannelAndLocation();
$eventOne->setAudioAndPlotURL();

//setting up Seismic Event Two
$eventTwo->location= new Location();
$eventTwo->location->depth = floatval($quake_table->eventParameters->event[$eventTwoIndex]->origin->depth->value);
$eventTwo->location->lng = floatval($quake_table->eventParameters->event[$eventTwoIndex]->origin->longitude->value);
$eventTwo->location->lat = floatval($quake_table->eventParameters->event[$eventTwoIndex]->origin->latitude->value);
$eventTwo->locationDescription = $quake_table->eventParameters->event[$eventTwoIndex]->description->text;
$eventTwo->impulseDate = $quake_table->eventParameters->event[$eventTwoIndex]->origin->time->value;
$eventTwo->magnitude = floatval($quake_table->eventParameters->event[$eventTwoIndex]->magnitude->mag->value);
$eventTwo->setNetworkAndStations();
$eventTwo->setTimeSeriesStartDate();
$eventTwo->setChannelAndLocation();
$eventTwo->setAudioAndPlotURL();

//setting up Seismic Event Three
$eventThree->location= new Location();
$eventThree->location->depth = floatval($quake_table->eventParameters->event[$eventThreeIndex]->origin->depth->value);
$eventThree->location->lng = floatval($quake_table->eventParameters->event[$eventThreeIndex]->origin->longitude->value);
$eventThree->location->lat = floatval($quake_table->eventParameters->event[$eventThreeIndex]->origin->latitude->value);
$eventThree->locationDescription = $quake_table->eventParameters->event[$eventThreeIndex]->description->text;
$eventThree->impulseDate = $quake_table->eventParameters->event[$eventThreeIndex]->origin->time->value;
$eventThree->magnitude = floatval($quake_table->eventParameters->event[$eventThreeIndex]->magnitude->mag->value);
$eventThree->setNetworkAndStations();
$eventThree->setTimeSeriesStartDate();
$eventThree->setChannelAndLocation();
$eventThree->setAudioAndPlotURL();
?>