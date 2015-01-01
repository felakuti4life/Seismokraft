<?php require_once('getSeismicData.php'); ?>
<!DOCTYPE html>
<head>
    <title>Seismokraft: Audio Processor 2</title>
    <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="output_bubble.js"></script>
    <link href="seismokraft.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="applicationWindow">
<style type="text/css">
    #transportOne {
        background-image: "<?php echo $eventOne->stationPlotURL ?> ";
    }

    #transportTwo {
        background-image: "<?php echo $eventTwo->stationPlotURL ?> ";
    }

    #transportThree {
        background-image: "<?php echo $eventThree->stationPlotURL ?> ";
    }
</style>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCJe8gm6aPqX280tYK6yCjms2NgA_fUzh0&amp;sensor=false"
        type="text/javascript"></script>
<script>
    /***************************
     GOOGLE MAP IMPLEMENTATION
     */
    function initializeMap() {
        var mapOptions = {
            zoom: 0,
            mapTypeId: google.maps.MapTypeId.SATELLITE
        };

        var flagImg = "images/mapMarkerIcon.png";

        var eventOneCoordinates = new google.maps.LatLng(<?php echo $eventOne->location->lat.", ".$eventOne->location->lng; ?>);
        var eventTwoCoordinates = new google.maps.LatLng(<?php echo $eventTwo->location->lat.", ".$eventTwo->location->lng; ?>);
        var eventThreeCoordinates = new google.maps.LatLng(<?php echo  $eventThree->location->lat.", ".$eventThree->location->lng; ?>);


        var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

        var markerOne = new google.maps.Marker({
            position: eventOneCoordinates,
            map: map,
            title: "Event One",
            icon: flagImg
        });

        var markerTwo = new google.maps.Marker({
            position: eventTwoCoordinates,
            map: map,
            title: "Event Two",
            icon: flagImg
        });

        var markerThree = new google.maps.Marker({
            position: eventThreeCoordinates,
            map: map,
            title: "Event One",
            icon: flagImg
        });

        markerOne.setMap(map);
        markerTwo.setMap(map);
        markerThree.setMap(map);
    }

    google.maps.event.addDomListener(window, 'load', initializeMap);
</script>
<!--
/****************************
 SEISMOKRAFT 1.0
 by Ethan Geller and Elizabeth Davis

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
 -->


<div id="eventsInfo">
    <div class="eventInfoBlock" id="eventOneSummary">
        <h1><a href="<?php echo $eventOne->stationAudioURL; ?>">1</a></h1>

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
        <h1><a href="<?php echo $eventTwo->stationAudioURL; ?>">2</a></h1>

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
        <h1><a href="<?php echo $eventThree->stationAudioURL; ?>">3</a></h1>

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
<br>

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

<div id="mainVolume">Content for id "mainVolume" Goes Here</div>
<p>

<p>
    <button style="display: block">Play/pause</button>
<h2>CROSSFADE</h2>

<p><input type="range" name="crossfade" min="0" step="0.01" max="100" value="0" onchange="sample.crossfade(this);"
          oninput="sample.crossfade(this);">
    <output for="crossfade">1</output>
</p>

<h2>TUNE</h2>

<p><input type="range" name="tune" min="0" step="0.01" max="100" value="3" onchange="sample.setPlaybackRate(this);"
          oninput="sample.setPlaybackRate(this);">
    <output for="tune">1</output>
</p>

<h2>FILTER</h2>

<p><input type="checkbox" id="c1" checked="false" onchange="sample.toggleFilter(this);">
    <label for="c1"><span></span>enable</label></p>
<h4>Type</h4>

<p><input type="radio" id="lowpass" value="0" class="audioFx" checked
          onclick="sample.changeFilterType(0)"> <label for="lowpass"><span><span></span></span>Low Pass</label></p>

<p><input type="radio" id="hipass" value="1" class="audioFx" checked
          onclick="sample.changeFilterType(1)"> <label for="hipass"><span><span></span></span>Hi Pass</label></p>

<p><input type="radio" id="bandpass" value="2" class="audioFx" checked
          onclick="sample.changeFilterType(2)"> <label for="bandpass"><span><span></span></span>Band Pass</label></p>

<p><input type="radio" id="lowshelf" value="3" class="audioFx" checked
          onclick="sample.changeFilterType(3)"> <label for="lowshelf"><span><span></span></span>Low Shelf</label></p>

<p><input type="radio" id="hishelf" value="4" class="audioFx" checked
          onclick="sample.changeFilterType(4)"> <label for="hishelf"><span><span></span></span>Hi Shelf</label></p>

<p><input type="radio" id="peaking" value="5" class="audioFx" checked
          onclick="sample.changeFilterType(5)"> <label for="peaking"><span><span></span></span>Peaking</label></p>

<p><input type="radio" id="notch" value="6" class="audioFx" checked
          onclick="sample.changeFilterType(6)"> <label for="notch"><span><span></span></span>Notch</label></p>

<p><input type="radio" id="allpass" value="7" class="audioFx" checked
          onclick="sample.changeFilterType(7)"> <label for="allpass"><span><span></span></span>All Pass</label></p>
<h4>Frequency</h4>

<p><input type="range" name="filterFreq" min="0" step="0.001" max="1" value="0" onchange="sample.changeFreq(this);"
          oninput="sample.changeFreq(this);">
    <output for="filterFreq">1</output>
</p>
<h4>Q</h4>

<p><input type="range" name="filterQ" min="0" step="0.001" max="1" value="0" onchange="sample.changeQ(this);"
          oninput="sample.changeQ(this);">
    <output for="filterQ">1</output>
</p>

<canvas></canvas>
<script src="AudioLoader.js"></script>
<script src="AudioChain.js"></script>
<script>
    var sample = new AudioChain('http://service.iris.edu/irisws/timeseries/1/query?net=CM&sta=CAP2&cha=BHZ&start=2014-09-20T14:00:40.0000&dur=8000&envelope=true&output=audio&audiocompress=true&audiosamplerate=44100&loc=01',
        'sound.wav',
        'http://service.iris.edu/irisws/timeseries/1/query?net=CM&sta=CAP2&cha=BHZ&start=2014-09-20T14:00:40.0000&dur=8000&envelope=true&output=audio&audiocompress=true&audiosamplerate=44100&loc=01');
    document.querySelector('button').addEventListener('click', function () {
        sample.togglePlayback()
    });
</script>
</div>
</body>