//Much of this script is from Boris Smus' excellent Web API demos:
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

var w = 1028;
var h = 360;

var smooth_factor = 0.8;
var fft_buf_size = 2048;

//Q fudge factor
var Q_mul = 30;

function AudioChain(url1, url2, url3, Fs) {
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

    this.Fs = Fs;
}

// Toggle playback
AudioChain.prototype.togglePlayback = function() {
    if (this.isPlaying) {
        /* CROSSFADE STUFF */
        var offName = this.ctl1.source.stop ? 'stop' : 'noteOff';
        this.ctl1.source[offName](0);
        this.ctl2.source[offName](0);
        this.ctl3.source[offName](0);

        /* VISUALIZER STUFF */
        this.startOffset += context.currentTime - this.startTime;
    } else {
        /* FILTER STUFF */
        var filter = context.createBiquadFilter();
        filter.type = filter.LOWPASS;
        filter.frequency.value = 5000;
        filter.connect(this.fft);

        /* VISUALIZER STUFF */
        this.startTime = context.currentTime;

        requestAnimFrame(this.draw.bind(this));

        /*CROSSFADE STUFF */
        // Create three sources.
        this.ctl1 = createSource(this.event1);
        this.ctl2 = createSource(this.event2);
        this.ctl3 = createSource(this.event3);

        this.ctl1.gainNode.gain.value = 0;

        var onName = this.ctl1.source.start ? 'start' : 'noteOn';
        this.ctl1.source[onName](0);
        this.ctl2.source[onName](0);
        this.ctl3.source[onName](0);
        this.crossfade(0.01);

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
    //dynamic width
    w = window.innerWidth
        || document.documentElement.clientWidth
        || document.body.clientWidth;
    //width of container
    w = w * 0.93;
    canvas.width = w;
    canvas.height = h;
    //Frequency domain
    for (var i = 0; i < this.fft.frequencyBinCount; i++) {
        var value = this.freqs[i];
        var percent = value / 256;
        var height = h * percent;
        var offset = h - height - 1;
        var barWidth = w/this.fft.frequencyBinCount;
        var hue = i/this.fft.frequencyBinCount * -120;
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
    var tuneLabel = document.getElementById('tune_status');
    var x = parseFloat(element.value) / parseFloat(element.max);
    minPlaybackRate = 0.1;
    maxPlaybackRate = 4.0;
    rate = (x*(maxPlaybackRate-minPlaybackRate) + minPlaybackRate);
    this.ctl1.source.playbackRate.value = rate;
    this.ctl2.source.playbackRate.value = rate;
    this.ctl3.source.playbackRate.value = rate;
    var playbackVsFs = (rate*context.sampleRate) / this.Fs;
    tuneLabel.innerHTML = (rate*context.sampleRate).toFixed(0) + " samples per second (x" + playbackVsFs.toFixed(2) + " actual speed)";
};

AudioChain.prototype.getFrequencyValue = function(freq) {
    var nyquist = context.sampleRate/2;
    var index = Math.round(freq/nyquist * this.freqs.length);
    return this.freqs[index];
}

/* CROSSFADING */
AudioChain.prototype.crossfade = function(element) {
    var x = parseFloat(element.value) / parseFloat(element.max);
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
};

/* FILTERING */
AudioChain.prototype.changeFreq = function(element) {
    var freqLabel = document.getElementById('filter_freq_status');
    var x = parseFloat(element.value) / parseFloat(element.max);
    var minValue = 40;
    var maxValue = context.sampleRate / 2;
    var numberOfOctaves = Math.log(maxValue / minValue) / Math.LN2;
    var multiplier = Math.pow(2, numberOfOctaves * (x - 1.0));

    this.filter.frequency.value = maxValue * multiplier;
    freqLabel.innerHTML = this.filter.frequency.value.toFixed(2) + " Hz";
};

AudioChain.prototype.changeQ = function(element) {
    var qLabel = document.getElementById('filter_q_status');
    var x = parseFloat(element.value) / parseFloat(element.max);
    this.filter.Q.value = x * Q_mul;
    qLabel.innerHTML = this.filter.Q.value.toFixed(2);
};

AudioChain.prototype.toggleFilter = function(element) {
    var enabledLabel = document.getElementById('filter_enabled_status');
    this.ctl1.gainNode.disconnect(0);
    this.ctl2.gainNode.disconnect(0);
    this.ctl3.gainNode.disconnect(0);
    this.filter.disconnect(0);

    if (element.checked) {
        this.ctl1.gainNode.connect(this.filter);
        this.ctl2.gainNode.connect(this.filter);
        this.ctl3.gainNode.connect(this.filter);
        this.filter.connect(this.fft);

        enabledLabel.innerHTML = "enabled";
    } else {
        this.ctl1.gainNode.connect(this.fft);
        this.ctl2.gainNode.connect(this.fft);
        this.ctl3.gainNode.connect(this.fft);
        enabledLabel.innerHTML = "disabled";
    }
};

AudioChain.prototype.changeFilterType = function(num) {
    var typeLabel = document.getElementById('filter_type_status');
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
    typeLabel.innerHTML = this.filter.type;
};
