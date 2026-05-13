import './bootstrap';
import 'flowbite';

import Alpine from 'alpinejs';

// ── Audio Player Alpine.js Component ─────────────────────────────────────────
// Used in: learning/guidebook, learn/guidebook, learning/pretest, learning/posttest
// Usage: x-data="audioPlayer('/storage/path/to/file.mp3')"  x-init="init()"
Alpine.data('audioPlayer', (src) => ({
    audio: null,
    playing: false,
    loading: false,
    progress: 0,
    timeDisplay: '0:00',

    init() {
        this.audio = new Audio(src);
        this.audio.preload = 'none';

        this.audio.addEventListener('loadstart',   () => { this.loading = true; });
        this.audio.addEventListener('canplay',     () => { this.loading = false; });
        this.audio.addEventListener('ended',       () => { this.playing = false; this.progress = 0; });
        this.audio.addEventListener('timeupdate',  () => {
            if (this.audio.duration) {
                this.progress    = (this.audio.currentTime / this.audio.duration) * 100;
                this.timeDisplay = this.formatTime(this.audio.currentTime);
            }
        });
    },

    toggle() {
        if (this.playing) {
            this.audio.pause();
            this.playing = false;
        } else {
            this.loading = true;
            this.audio.play()
                .then(() => { this.playing = true; this.loading = false; })
                .catch(() => { this.loading = false; });
        }
    },

    seek(event) {
        if (!this.audio.duration) return;
        const rect = event.currentTarget.getBoundingClientRect();
        this.audio.currentTime = ((event.clientX - rect.left) / rect.width) * this.audio.duration;
    },

    formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    },
}));

window.Alpine = Alpine;
Alpine.start();
