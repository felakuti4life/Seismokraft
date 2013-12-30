<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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
	
	public function __construct($eventIndex=0){
		global $quake_table;
		$this->location->depth = floatval($quake_table->eventParameters->event[$eventIndex]->origin->depth->value);
		$this->location->lng = floatval($quake_table->eventParameters->event[$eventIndex]->origin->longitude->value);
		$this->location->lat = floatval($quake_table->eventParameters->event[$eventIndex]->origin->latitude->value);
		$this->impulseDate = $quake_table->eventParameters->event[$eventIndex]->origin->time->value;
		$this->magnitude = floatval($quake_table->eventParameters->event[$eventIndex]->magnitude->mag->value);
		$this->setNetworkAndStations();
		$this->setTimeSeriesStartDate();
		$this->setChannelAndLocation();
		$this->setAudioAndPlotURL();
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
		"&maxradius=7.0&nodata=404";
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
		//FIXME: Check for empty locationCode string
		$this->channelCode = $channel_table->Network->Station->Channel[0]['code'];
		$this->locationCode = $channel_table->Network->Station->Channel[0]['locationCode'];
		$i = 1;
		while($this->locationCode=''){
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
	}
}

//TODO: move these to the __construct function

//set Seismic Events
$eventOneIndex = 1;
$eventOne = new SeismicEvent($eventOneIndex);

$eventTwoIndex = 2;
$eventTwo = new SeismicEvent($eventTwoIndex);

$eventThreeIndex = 3;
$eventThree = new SeismicEvent($eventThreeIndex);


//setting up Seismic Event One


//setting up Seismic Event Two
$eventTwo->location->depth = floatval($quake_table->eventParameters->event[$eventTwoIndex]->origin->depth->value);
$eventTwo->location->lng = floatval($quake_table->eventParameters->event[$eventTwoIndex]->origin->longitude->value);
$eventTwo->location->lat = floatval($quake_table->eventParameters->event[$eventTwoIndex]->origin->latitude->value);
$eventTwo->impulseDate = $quake_table->eventParameters->event[$eventTwoIndex]->origin->time->value;
$eventTwo->magnitude = floatval($quake_table->eventParameters->event[$eventTwoIndex]->magnitude->mag->value);
$eventTwo->setNetworkAndStations();
$eventTwo->setTimeSeriesStartDate();
$eventTwo->setChannelAndLocation();
$eventTwo->setAudioAndPlotURL();

//setting up Seismic Event Three
$eventThree->location->depth = floatval($quake_table->eventParameters->event[$eventThreeIndex]->origin->depth->value);
$eventThree->location->lng = floatval($quake_table->eventParameters->event[$eventThreeIndex]->origin->longitude->value);
$eventThree->location->lat = floatval($quake_table->eventParameters->event[$eventThreeIndex]->origin->latitude->value);
$eventThree->impulseDate = $quake_table->eventParameters->event[$eventThreeIndex]->origin->time->value;
$eventThree->magnitude = floatval($quake_table->eventParameters->event[$eventThreeIndex]->magnitude->mag->value);
$eventThree->setNetworkAndStations();
$eventThree->setTimeSeriesStartDate();
$eventThree->setChannelAndLocation();
$eventThree->setAudioAndPlotURL();
?>



<title>Seismokraft</title>
</head>

<body>
"hello!"
<?php
//tests:
echo "<p>" . "hello!"."</p>"; ?>
<p>
<?php
echo "URL: " . $url;
?>
</p>
<p>
<?php echo "Event Location: " . $quake_table->eventParameters->event[0]->description->text; ?>
</p>
<p>
<?php echo "Magnitude: ", $eventOne->magnitude, "<br>", $eventOne->impulseDate; ?>
</p>
<p>
<?php echo "Location: ", $eventOne->location->lat, "<br>", $eventOne->location->lng, "<br>", "Date: ", $eventOne->impulseDate, "<br>", "Time series start: ",
 $eventOne->timeSeriesStartDate;?>
</p>
<p>
<?php echo "Station URL: ", $eventOne->stationUrlTest; ?>
</p>
<p>
<?php echo "Channel URL: ", $eventOne->channelUrlTest; ?>
</p>
<p>
<?php echo "Nearest Network Code: ", $eventOne->nearestNetworkCode, "<br>", "Nearest Station Code:", $eventOne->nearestStationCode; ?>
</p>
<p>
<?php echo "Nearest Channel Code: ", $eventOne->channelCode, "<br>", "Nearest Location Code:", $eventOne->locationCode; ?>
</p>
<p>
<?php echo "Seismic Event One Audio Link: <a>", $eventOne->stationAudioURL, "</a>"; ?>
</p>
<img src="<?php echo $eventOne->stationPlotURL; ?>" />
<?php echo "Image source: ", $eventOne->stationPlotURL; ?>

<p>
<?php echo "Seismic Event Two Audio Link: <a>", $eventTwo->stationAudioURL, "</a>"; ?>
</p>
<img src="<?php echo $eventTwo->stationPlotURL; ?>" />
<?php echo "Image source: ", $eventTwo->stationPlotURL; ?>

<p>
<?php echo "Seismic Event Three Audio Link: <a href=\"", $eventTwo->stationAudioURL, "\"> here </a>"; ?>
</p>
<img src="<?php echo $eventTwo->stationPlotURL; ?>" />
<?php echo "Image source: ", $eventTwo->stationPlotURL; ?>
</body>
</html>