<html>
<?php require_once('getSeismicData.php'); ?>
<!DOCTYPE html>

<head>
    <title>Seismokraft: Audio Processor 2</title>
    <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>

    <link href="seismokraft.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="applicationWindow">
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDvk54xl6pCp98naC9huck8a_qEblkdYiY&amp;sensor=false"
        type="text/javascript"></script>
<script type="text/javascript">
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




//alternate method via CORS request
var eventOneAudioURL = "<?php echo $eventOne->stationAudioURL ?>";
var eventTwoAudioURL = "<?php echo $eventTwo->stationAudioURL ?>";
var eventThreeAudioURL = "<?php echo $eventThree->stationAudioURL ?>";




var eventOneBuffer = null;
var eventTwoBuffer = null;
var eventThreeBuffer = null;
// Fix prefixing
window.AudioContext = window.AudioContext || window.webkitAudioContext;
var context = new AudioContext();


context.decodeAudioData(eventOneAudio, function(buffer){eventOneBuffer = buffer});
context.decodeAudioData(eventTwoAudio, function(buffer){eventTwoBuffer = buffer});
context.decodeAudioData(eventThreeAudio, function(buffer){eventThreeBuffer = buffer});


</script>
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
    <p><button style="display: block">Play/pause</button>
    <h2>CROSSFADE</h2>
    <p>1<input type="range" min="0" step="0.01" max="100" value="0" onchange="sample.crossfade(this);">3</p>

    <h2>TUNE</h2>
    <p>slow<input type="range" min="0" step="0.01" max = "100" value="3" onchange="sample.setPlaybackRate(this)"></p>

    <h2>FILTER</h2>
    <p><input type="checkbox" id="c1" checked="false" onchange="sample.toggleFilter(this);">
        <label for="c1"><span></span>enable</label></p>
    <h4>Type</h4>
    <p><input type="radio" name="filtertype" value="0" class="audioFx" checked
              onclick="sample.changeFilterType(0)"> low pass</p>
    <p><input type="radio" name="filtertype" value="1" class="audioFx" checked
              onclick="sample.changeFilterType(1)"> hi pass</p>
    <p><input type="radio" name="filtertype" value="2" class="audioFx" checked
              onclick="sample.changeFilterType(2)"> band pass</p>
    <p><input type="radio" name="filtertype" value="3" class="audioFx" checked
              onclick="sample.changeFilterType(3)"> low shelf</p>
    <p><input type="radio" name="filtertype" value="4" class="audioFx" checked
              onclick="sample.changeFilterType(4)"> hi shelf</p>
    <p><input type="radio" name="filtertype" value="5" class="audioFx" checked
              onclick="sample.changeFilterType(5)"> peaking</p>
    <p><input type="radio" name="filtertype" value="6" class="audioFx" checked
              onclick="sample.changeFilterType(6)"> notch</p>
    <p><input type="radio" name="filtertype" value="7" class="audioFx" checked
              onclick="sample.changeFilterType(7)"> all pass</p>
    <h4>Frequency</h4>
    <p>10 Hz<input type="range" min="0" step="0.001" max="1" value="0" onchange="sample.changeFreq(this);">22.5 kHz</p>
    <h4>Q</h4>
    <p>narrow<input type="range" min="0" step="0.001" max="1" value="0" onchange="sample.changeQ(this);">wide</p>

    <canvas></canvas>
    <script src="AudioLoader.js"></script>
    <script src="AudioChain.js"></script>
    <script>
        var sample = new AudioChain('http://service.iris.edu/irisws/timeseries/1/query?net=CM&sta=CAP2&cha=BHZ&start=2014-09-20T14:00:40.0000&dur=8000&envelope=true&output=audio&audiocompress=true&audiosamplerate=44100&loc=01',
            'sound.wav',
            'http://service.iris.edu/irisws/timeseries/1/query?net=CM&sta=CAP2&cha=BHZ&start=2014-09-20T14:00:40.0000&dur=8000&envelope=true&output=audio&audiocompress=true&audiosamplerate=44100&loc=01');
        document.querySelector('button').addEventListener('click', function() {
            sample.togglePlayback()
        });
    </script>
</div>
</body>
</html>