/*
 * Copyright 2013 Boris Smus. All Rights Reserved.

 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


var w = 640;
var h = 360;

var SMOOTHING = 0.8;
var FFT_SIZE = 2048;

function AudioChain(url1, url2, url3) {
    this.fft = context.createAnalyser();

    this.fft.connect(context.destination);
    this.fft.minDecibels = -140;
    this.fft.maxDecibels = 0;
    loadSounds(this, {
        event1: url1,
        event2: url2,
        event3: url3
    });
    this.freqs = new Uint8Array(this.fft.frequencyBinCount);
    this.times = new Uint8Array(this.fft.frequencyBinCount);
    //autoplay on load?
    this.isPlaying = false;
    this.startTime = 0;
    this.startOffset = 0;
}

// Toggle playback
AudioChain.prototype.togglePlayback = function() {
    if (this.isPlaying) {
        /* CROSSFADE STUFF */
        var offName = this.ctl1.source.stop ? 'stop' : 'noteOff';
        this.ctl1.source[offName](0);
        this.ctl2.source[offName](0);
        this.ctl3.source[offName](0);
        // Stop playback

        /* VISUALIZER STUFF */
        this.startOffset += context.currentTime - this.startTime;
        console.log('paused at', this.startOffset);
        // Save the position of the play head.
    } else {
        /* VISUALIZER STUFF */

        this.startTime = context.currentTime;
        console.log('started at', this.startOffset);

        // Start playback, but make sure we stay in bound of the buffer.
        // Start visualizer.
        requestAnimFrame(this.draw.bind(this));

        /*CROSSFADE STUFF */
        // Create two sources.
        this.ctl1 = createSource(this, this.event1);
        this.ctl2 = createSource(this, this.event2);
        this.ctl3 = createSource(this, this.event3);
        // Mute the second source.
        this.ctl1.gainNode.gain.value = 0;
        // Start playback in a loop
        var onName = this.ctl1.source.start ? 'start' : 'noteOn';
        this.ctl1.source[onName](0);
        this.ctl2.source[onName](0);
        this.ctl3.source[onName](0);
        // Set the initial crossfade to be just source 1.
        this.crossfade(0);

        function createSource(self, buffer) {
            var source = context.createBufferSource();
            var gainNode = context.createGain();
            source.buffer = buffer;
            // Turn on looping
            source.loop = true;
            // Connect source to gain.
            source.connect(gainNode);
            // Connect gain to destination.
            gainNode.connect(self.fft);

            return {
                source: source,
                gainNode: gainNode
            };
        }


    }
    this.isPlaying = !this.isPlaying;
}


AudioChain.prototype.draw = function() {
    this.fft.smoothingTimeConstant = SMOOTHING;
    this.fft.fftSize = FFT_SIZE;

    // Get the frequency data from the currently playing music
    this.fft.getByteFrequencyData(this.freqs);
    this.fft.getByteTimeDomainData(this.times);

    var width = Math.floor(1/this.freqs.length, 10);

    var canvas = document.querySelector('canvas');
    var drawContext = canvas.getContext('2d');
    canvas.width = w;
    canvas.height = h;
    //Frequency domain
    for (var i = 0; i < this.fft.frequencyBinCount; i++) {
        var value = this.freqs[i];
        var percent = value / 256;
        var height = h * percent;
        var offset = h - height - 1;
        var barWidth = w/this.fft.frequencyBinCount;
        var hue = i/this.fft.frequencyBinCount * 120;
        drawContext.fillStyle = 'hsl(' + hue + ', 100%, 30%)';
        drawContext.fillRect(i * barWidth, offset, barWidth, height);
    }

    // time domain
    for (var i = 0; i < this.fft.frequencyBinCount; i++) {
        var value = this.times[i];
        var percent = value / 256;
        var height = h * percent;
        var offset = h - height - 1;
        var barWidth = w/this.fft.frequencyBinCount;
        drawContext.fillStyle = 'white';
        drawContext.fillRect(i * barWidth, offset, 1, 2);
    }

    if (this.isPlaying) {
        requestAnimFrame(this.draw.bind(this));
    }
}

AudioChain.prototype.getFrequencyValue = function(freq) {
    var nyquist = context.sampleRate/2;
    var index = Math.round(freq/nyquist * this.freqs.length);
    return this.freqs[index];
}

/* CROSSFADING */
// Fades between 0 (all source 1) and 1 (all source 2)
AudioChain.prototype.crossfade = function(element) {
    var x = parseInt(element.value) / parseInt(element.max);
    //TODO: make this a three way thing
    var gain1;
    if (x > 0.5) gain1 = 0;
    else gain1 = Math.cos(x * Math.PI);
    var gain2 =  Math.cos((x-0.5) * Math.PI);
    var gain3;
    if (x < 0.5) gain3 = 0;
    else gain3 = Math.cos((1 - x) * Math.PI);
    this.ctl1.gainNode.gain.value = gain1;
    this.ctl2.gainNode.gain.value = gain2;
    this.ctl3.gainNode.gain.value = gain3;
    console.log("Crossfade at " + x + ": gain 1 " + gain1 + " gain 2 " + gain2 + " gain 3 " + gain3);
};

