//Much of this script is from Boris Smus' excellent Web API demos.
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

var smooth_factor = 0.8;
var fft_buf_size = 2048;

//Q fudge factor
var Q_mul = 30;

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
        /* FILTER STUFF */
        var filter = context.createBiquadFilter();
        filter.type = filter.LOWPASS;
        filter.frequency.value = 5000;
        filter.connect(this.fft);

        /* VISUALIZER STUFF */

        this.startTime = context.currentTime;
        console.log('started at', this.startOffset);

        // Start playback, but make sure we stay in bound of the buffer.
        // Start visualizer.
        requestAnimFrame(this.draw.bind(this));

        /*CROSSFADE STUFF */
        // Create two sources.
        this.ctl1 = createSource(this.event1);
        this.ctl2 = createSource(this.event2);
        this.ctl3 = createSource(this.event3);

        this.ctl1.gainNode.gain.value = 0;

        var onName = this.ctl1.source.start ? 'start' : 'noteOn';
        this.ctl1.source[onName](0);
        this.ctl2.source[onName](0);
        this.ctl3.source[onName](0);
        // Set the initial crossfade to be just source 1.
        this.crossfade(0);

        function createSource(buffer) {
            var source = context.createBufferSource();
            var gainNode = context.createGain();
            source.buffer = buffer;
            source.loop = true;
            source.connect(gainNode);
            gainNode.connect(filter);

            return {
                source: source,
                gainNode: gainNode
            };
        }

    this.filter = filter;
    }
    this.isPlaying = !this.isPlaying;
}


AudioChain.prototype.draw = function() {
    this.fft.smoothingTimeConstant = smooth_factor;
    this.fft.fftSize = fft_buf_size;
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

    //Time domain
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

/* PLAYBACK RATE CHANGE */
AudioChain.prototype.setPlaybackRate = function(element) {
    var x = parseInt(element.value) / parseInt(element.max);
    minPlaybackRate = 0.1;
    maxPlaybackRate = 4.0;
    rate = (x*(maxPlaybackRate-minPlaybackRate) + minPlaybackRate);
    this.ctl1.source.playbackRate.value = rate;
    this.ctl2.source.playbackRate.value = rate;
    this.ctl3.source.playbackRate.value = rate;
};

AudioChain.prototype.getFrequencyValue = function(freq) {
    var nyquist = context.sampleRate/2;
    var index = Math.round(freq/nyquist * this.freqs.length);
    return this.freqs[index];
}

/* CROSSFADING */
AudioChain.prototype.crossfade = function(element) {
    var x = parseInt(element.value) / parseInt(element.max);
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

/* FILTERING */
AudioChain.prototype.changeFreq = function(element) {
    var minValue = 40;
    var maxValue = context.sampleRate / 2;
    var numberOfOctaves = Math.log(maxValue / minValue) / Math.LN2;
    var multiplier = Math.pow(2, numberOfOctaves * (element.value - 1.0));

    this.filter.frequency.value = maxValue * multiplier;
};

AudioChain.prototype.changeQ = function(element) {
    this.filter.Q.value = element.value * Q_mul;
};

AudioChain.prototype.toggleFilter = function(element) {
    this.ctl1.gainNode.disconnect(0);
    this.ctl2.gainNode.disconnect(0);
    this.ctl3.gainNode.disconnect(0);
    this.filter.disconnect(0);

    if (element.checked) {
        // Connect through the filter.
        this.ctl1.gainNode.connect(this.filter);
        this.ctl2.gainNode.connect(this.filter);
        this.ctl3.gainNode.connect(this.filter);
        this.filter.connect(this.fft);
    } else {
        // Otherwise, connect directly.
        this.ctl1.gainNode.connect(this.fft);
        this.ctl2.gainNode.connect(this.fft);
        this.ctl3.gainNode.connect(this.fft);
    }
};

AudioChain.prototype.changeFilterType = function(num) {
    switch(num) {
        case 0:
            this.filter.type = "lowpass";
            break;
        case 1:
            this.filter.type = "highpass";
            break;
        case 2:
            this.filter.type = "bandpass";
            break;
        case 3:
            this.filter.type = "lowshelf";
            break;
        case 4:
            this.filter.type = "highshelf";
            break;
        case 5:
            this.filter.type = "peaking";
            break;
        case 6:
            this.filter.type = "notch";
            break;
        case 7:
            this.filter.type = "allpass";
            break;
    }
};
