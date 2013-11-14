<?php require_once('getSeismicData.php'); ?>
<!DOCTYPE html>

<head>
<title>Seismokraft App</title>
<link rel="stylesheet" type="text/css" href="jqueryStyle/css/Seismokraft/jquery-ui-1.10.3.custom.min.css">
<style type="text/css">
.eventInfoBlock {
	padding: 5px;
	margin-right: 4%;
	float: left;
	width: 20%;
	height: 200px;
	border: 2px solid #CCCCCC;
	border-radius: 5px;
	background-color: #FFE292;
}
.eventInfoBlock h1 {
	padding: 1px;
	text-align: center;
	font-weight: bolder;
	font-variant: small-caps;
	color: #333333;
	text-decoration: underline;
}
.eventInfoBlock h3 {
	letter-spacing: 0.4em;
	text-align: center;
	font-weight: bolder;
	text-decoration: underline overline;
	color: #000000;
}
.eventInfoBlock h4 {
	text-transform: capitalize;
	font-size: 14px;
	text-align: center;
	font-variant: small-caps;
}
.eventInfoBlock p {
	font-size: 12.5px;
	font-style: italic;
	color: #0099FF;
	text-align: right;
	float: right;
	padding-top: -5px;
}
#transportWindow {
	height: 400px;
	width: 90%;
	margin-top: 20px;
	border: 2px solid #CCCCCC;
	clear: both;
	text-align: center;
	padding-left: 2%;
}
.transport {
	width: 30%;
	float: left;
	margin-right: 2%;
}
#transportOne {
 background-image: "<?php echo $eventOne->stationPlotURL
?>;
"
}
#transportTwo {
 background-image: "<?php echo $eventTwo->stationPlotURL
?>;
"
}
#transportThree {
 background-image: "<?php echo $eventThree->stationPlotURL
?>;
"
}
#map-canvas {
	width: 80%;
}
</style>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script type="text/javascript" src="jqueryStyle/js/jquery-ui-1.10.3.custom.min.js"></script>
</head>
<body>
<div id="applicationWindow"> 
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDvk54xl6pCp98naC9huck8a_qEblkdYiY&sensor=false"
  type="text/javascript"></script> 
  <script type="text/javascript">
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
	
	SUMMARY: This script is passed the seismic data/ArrayBuffers from getSeismicData.php and handles all of the webAudio API implementation.

*/




//alternate method via CORS request
var eventOneAudioURL = "<?php echo $eventOne->stationAudioURL ?>"
var eventTwoAudioURL = "<?php echo $eventTwo->stationAudioURL ?>"
var eventThreeAudioURL = "<?php echo $eventThree->stationAudioURL ?>"

function loadEvent(url, eventBuffer) {
  var request = new XMLHttpRequest();
  request.open('GET', url, true);
  request.responseType = 'arraybuffer';

  // Decode asynchronously
  request.onload = function() {
    context.decodeAudioData(request.response, function(buffer) {
      eventBuffer = buffer;
    }, function(){console.log("Error!");});
  }
  request.send();
}



var eventOneBuffer = null;
var eventTwoBuffer = null;
var eventThreeBuffer = null;
// Fix prefixing
window.AudioContext = window.AudioContext || window.webkitAudioContext;
var context = new AudioContext();

/*
context.decodeAudioData(eventOneAudio, function(buffer){eventOneBuffer = buffer});
context.decodeAudioData(eventTwoAudio, function(buffer){eventTwoBuffer = buffer});
context.decodeAudioData(eventThreeAudio, function(buffer){eventThreeBuffer = buffer});
*/
loadEvent(eventOneAudioURL, eventOneBuffer);
loadEvent(eventTwoAudioURL, eventTwoBuffer);
loadEvent(eventThreeAudioURL, eventThreeBuffer);

var sourceOne = null;
var sourceTwo = null;
var sourceThree = null;

var gainNodeOne = context.createGain();
var gainNodeTwo = context.createGain();
var gainNodeThree = context.createGain();
var gainNodeMain = context.createGain();

gainNodeMain.connect(context.destination);

var filter = context.createBiquadFilter();

/**********
there are three seperate gain nodes for each event source, but they are all mixed down into the same filter

road map:
eventOne->gainNodeOne->mainFilter
eventTwo->gainNodeTwo->mainFilter
eventThree->gainNodeThree->mainFilter

then,
mainFilter->gainNodeMain->destination
*/
function toggleFilter(element){
	gainNodeOne.disconnect(0);
	gainNodeTwo.disconnect(0);
	gainNodeThree.disconnect(0);
	
	if(element.checked){
		gainNodeOne.connect(filter);
		gainNodeTwo.connect(filter);
		gainNodeThree.connect(filter);
		
		filter.connect(gainNodeMain);
		gainNodeMain.connect(context.destination);
	}
	else {
		gainNodeOne.connect(gainNodeMain);
		gainNodeTwo.connect(gainNodeMain);
		gainNodeThree.connect(gainNodeMain);
	}
}



function playSound(anybuffer, anysource, anygain) {
  anysource = context.createBufferSource();
  anysource.loop = true;
  anysource.buffer = anybuffer;
  anysource.connect(anygain);
  anygain.connect(filter);
  
  if(!anysource.start)
  	anysource.start=anysource.noteOn;
  anysource.start(0);
}

function stopSound(anysource){
	if(!anysource.stop)
		anysource.stop = anysource.noteOff;
	anysource.stop(0);
}

function fadeBetweenSources(value, maximum){
	var x = parseInt(value) / parseInt(maximum);
  // Using an equal-power crossfading curve:
  var gain1 = Math.cos(x * 0.33*Math.PI);
  var gain2 = Math.cos((x-0.33)*0.33*Math.PI)
  var gain3 = Math.cos((1.0 - x) * 0.67*Math.PI);
  gainNodeOne.gain.value = gain1;
  gainNodeTwo.gain.value = gain2;
  gainNodeThree.gain.value = gain3;
}

function tuneSources(element){
	var rate = (parseInt(element.value) / parseInt(element.max)) * 6.0;
	sourceOne.playbackRate.value = rate;
	sourceTwo.playbackRate.value = rate;
	sourceThree.playbackRate.value = rate;
}

/***************************
GOOGLE MAP IMPLEMENTATION
*/
function initialize() {
  var mapOptions = {
    zoom: 0,
    mapTypeId: google.maps.MapTypeId.SATELLITE
  }
  
  var flagImg = "/images/mapMarkerIcon.png"
  
  var eventOneCoordinates= new google.maps.LatLng(<?php echo $eventOne->location->lat.", ".$eventOne->location->lng; ?>);
  var eventTwoCoordinates= new google.maps.LatLng(<?php echo $eventTwo->location->lat.", ".$eventTwo->location->lng; ?>);
  var eventThreeCoordinates= new google.maps.LatLng(<?php echo $eventThree->location->lat.", ".$eventThree->location->lng; ?>);
  
  
  var markerOne = new google.maps.Marker({
    position: eventOneCoordinates,
    map: map,
    title:"Event One",
	icon: flagImg
	});
	
  var markerTwo = new google.maps.Marker({
    position: eventTwoCoordinates,
    map: map,
    title:"Event Two",
	icon: flagImg
	});

	var markerThree = new google.maps.Marker({
    position: eventThreeCoordinates,
    map: map,
    title:"Event One",
	icon: flagImg
	});
	
	
  var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
}

google.maps.event.addDomListener(window, 'load', initialize);



/*******
jQuery Objects
*/

	$(document).ready(function() {
		playSound(EventOneBuffer, SourceOne, GainNodeOne);
		playSound(EventTwoBuffer, SourceTwo, GainNodeTwo);
		playSound(EventThreeBuffer, SourceThree, GainNodeThree);
        $(function(){
		$("#channelFaderSlider").slider({
			max:500,
			animate: "slow"
			});
		});
		
	$("#channelFaderSlider").on("slidechange", 
		fadeBetweenSources($("#channelFaderSlider").slider("option", "value"), $("#channelFaderSlider").slider("option", "max"));
	
	$(function(){
		$("#transportSliderOne").slider({
			max: sourceOne.buffer.duration,
			
			
		});
	});
    });
	
	$(function(){
		$("#channelFaderSlider").slider({
			max:500,
			animate: "slow",
			});
		});
		
	$("#channelFaderSlider").on("slidechange", 
		fadeBetweenSources($("#channelFaderSlider).slider("option", "value"), $("#channelFaderSlider).slider("option", "max"));
	
	$(function(){
		$("#transportSliderOne").slider({
			max: sourceOne.buffer.duration,
			
			
		});
	});
</script>
  <div id="eventsInfo">
    <div class="eventInfoBlock" id="eventOneSummary">
      <h1>1</h1>
      <h3>M<?php echo $eventOne->magnitude; ?></h3>
      <h4><?php echo $eventOne->locationDescription; ?></h4>
      <p>at
        <?php 
				$DateEventOne = date_create($eventOne->impulseDate);
				echo date_format($DateEventOne, 'g:ia \o\n l\, F jS\, Y'); 
				?>
      </p>
    </div>
    <div class="eventInfoBlock" id="eventTwoSummary">
      <h1>2</h1>
      <h3>M<?php echo $eventTwo->magnitude; ?></h3>
      <h4><?php echo $eventTwo->locationDescription; ?></h4>
      <p>at
        <?php 
				$DateEventTwo = date_create($eventOne->impulseDate);
				echo date_format($DateEventTwo, 'g:ia \o\n l\, F jS\, Y'); 
				?>
      </p>
    </div>
    <div class="eventInfoBlock" id="eventThreeSummary">
      <h1>3</h1>
      <h3>M<?php echo $eventThree->magnitude; ?></h3>
      <h4><?php echo $eventThree->locationDescription; ?></h4>
      <p>at
        <?php 
				$DateEventThree = date_create($eventThree->impulseDate);
				echo date_format($DateEventThree, 'g:ia \o\n l\, F jS\, Y'); 
				?>
      </p>
    </div>
  </div>
  <div id="map-canvas"></div>
  <div id="transportWindow">
    <div class="transport" id="transportOne">
      <div id="transportSliderOne"></div>
    </div>
    <div class="transport" id="transportTwo">
      <div id="transportSliderTwo"></div>
    </div>
    <div class="transport" id="transportThree">
      <div id="transportSliderThree"></div>
    </div>
  </div>
  <div id="channelFader">
    <div id="channelFaderSlider"></div>
  </div>
  <div id="tunerSlider">Content for  id "tunerSlider" Goes Here</div>
  <div id="filterWindow">
    <div class="parameters">Content for  class "parameters" Goes Here</div>
    <div id="fftAnalysis">Content for  id "fftAnalysis" Goes Here</div>
  </div>
  <div id="mainVolume">Content for  id "mainVolume" Goes Here</div>
</div>
</body>
</html>