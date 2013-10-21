<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Seismokraft</title>
<script type="text/javascript">
window.onload = init;
var context;
var bufferLoader;

function init() {
  // Fix up prefixing
  window.AudioContext = window.AudioContext || window.webkitAudioContext;
  context = new AudioContext();

  bufferLoader = new BufferLoader(
    context,
    [
      '<?php echo $eventOne->stationAudioURL; ?>',
      '<?php echo $eventTwo->stationAudioURL; ?>',
	  '<?php echo $eventThree->stationAudioURL; ?>',
    ],
    finishedLoading
    );

  bufferLoader.load();
}

function finishedLoading(bufferList) {
  // Create two sources and play them both together.
  var eventOneSource = context.createBufferSource();
  var eventTwoSource = context.createBufferSource();
  var eventThreeSource = context.createBufferSource();
  eventOneSource.buffer = bufferList[0];
  eventTwoSource.buffer = bufferList[1];
  eventThreeSource.buffer = bufferList[2];
	
	
  gainNodeOne = context.createGain();
  gainNodeTwo = context.createGain();
  gainNodeThree = context.createGain();	
  eventOneSource.connect(context.gainNodeOne);
  eventTwoSource.connect(context.gainNodeTwo);
  eventThreeSource.connect(context.gainNodeThree);
  playSound(eventOneSource, 0);
  
}

function playSound(buffer, time) {
  var source = context.createBufferSource();
  source.buffer = buffer;
  source.connect(context.destination);
  source.start(time);
}

</script>
</head>

<body>
</body>
</html>