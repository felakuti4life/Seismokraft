<?php require_once('getSeismicData.php'); ?>
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
var eventOneAudio = <?php echo $eventOne->audioBuffer; ?>;
var eventTwoAudio = <?php echo $eventTwo->audioBuffer; ?>;
var eventThreeAudio = <?php echo $eventThree->audioBuffer; ?>;


var eventOneBuffer = null;
var eventTwoBuffer = null;
var eventThreeBuffer = null;
// Fix prefixing
window.AudioContext = window.AudioContext || window.webkitAudioContext;
var context = new AudioContext();

context.decodeAudioData(eventOneAudio, function(buffer){eventOneBuffer = buffer});
context.decodeAudioData(eventTwoAudio, function(buffer){eventTwoBuffer = buffer});
context.decodeAudioData(eventThreeAudio, function(buffer){eventThreeBuffer = buffer});

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
eventOne->gainNodeOne->mainFilter->destination
eventTwo->gainNodeTwo->mainFilter->destination
eventThree->gainNodeThree->mainFilter->destination


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
  
  if(!anysource.start)
  	anysource.start=anysource.noteOn;
  anysource.start(0);
}

function stopSound(anysource){
	if(!anysource.stop)
		anysource.stop = anysource.noteOff;
	anysource.stop(0);
}
</script>