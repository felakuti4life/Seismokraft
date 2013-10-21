<?php require_once('getSeismicData.php'); ?>
<script type="text/javascript">
var eventOneAudio = <?php echo json_encode($eventOne->audioBuffer); ?>;
var eventTwoAudio = <?php echo json_encode($eventTwo->audioBuffer); ?>;
var eventThreeAudio = <?php echo json_encode($eventThree->audioBuffer); ?>;


var eventOneBuffer = null;
var eventTwoBuffer = null;
var eventThreeBuffer = null;
// Fix prefixing
window.AudioContext = window.AudioContext || window.webkitAudioContext;
var context = new AudioContext();

context.decodeAudioData(eventOneAudio, function(buffer){eventOneBuffer = buffer});
context.decodeAudioData(eventTwoAudio, function(buffer){eventTwoBuffer = buffer});
context.decodeAudioData(eventThreeAudio, function(buffer){eventThreeBuffer = buffer});


function loadSeismicSound(url, eventBuffer) {
  var request = new XMLHttpRequest();
  request.open('GET', url, true);
  request.responseType = 'arraybuffer';

  // Decode asynchronously
  request.onload = function() {
    context.decodeAudioData(request.response, function(buffer) {
      eventBuffer = buffer;
    }, onError);
  }
  request.send();
}

loadSeismicSound(eventOneAudioURL, eventOneBuffer);
loadSeismicSound(eventTwoAudioURL, eventTwoBuffer);
loadSeismicSound(eventThreeAudioURL, eventThreeBuffer);

</script>