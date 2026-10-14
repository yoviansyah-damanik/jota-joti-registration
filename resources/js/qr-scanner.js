import QrScanner from 'qr-scanner';

// Komponen Alpine scanner QR absensi; hasil decode dikirim ke Livewire lewat $wire.scan()
export default (messages) => ({
    scanner: null,
    active: false,
    error: null,
    last: '',
    lastAt: 0,
    audio: null,

    async start() {
        this.error = null;

        if (!navigator.mediaDevices?.getUserMedia) {
            this.error = messages.insecure;
            return;
        }

        if (!(await QrScanner.hasCamera())) {
            this.error = messages.noCamera;
            return;
        }

        // aktifkan dulu agar elemen video terlihat sebelum kamera dinyalakan
        this.active = true;
        await this.$nextTick();

        this.scanner = new QrScanner(this.$refs.video, (result) => this.onScan(result.data), {
            preferredCamera: 'environment',
            highlightScanRegion: true,
            highlightCodeOutline: true,
            maxScansPerSecond: 5,
            returnDetailedScanResult: true,
        });

        try {
            await this.scanner.start();
        } catch (e) {
            this.stop();
            this.error = /permission|denied|notallowed/i.test(String(e?.name ?? e)) ? messages.denied : String(e?.message ?? e);
        }
    },

    stop() {
        this.scanner?.stop();
        this.scanner?.destroy();
        this.scanner = null;
        this.active = false;
    },

    // kode yang sama dalam 3 detik diabaikan agar satu kartu tidak tercatat berulang
    onScan(data) {
        const now = Date.now();
        if (data === this.last && now - this.lastAt < 3000) return;

        this.last = data;
        this.lastAt = now;
        this.$wire.scan(data);
    },

    beep(status) {
        try {
            this.audio ??= new (window.AudioContext || window.webkitAudioContext)();
            const tones = { success: [880], duplicate: [660, 660], error: [220] }[status] ?? [440];

            tones.forEach((frequency, i) => {
                const oscillator = this.audio.createOscillator();
                const gain = this.audio.createGain();
                const start = this.audio.currentTime + i * 0.18;

                oscillator.frequency.value = frequency;
                gain.gain.setValueAtTime(0.15, start);
                gain.gain.exponentialRampToValueAtTime(0.001, start + 0.15);
                oscillator.connect(gain).connect(this.audio.destination);
                oscillator.start(start);
                oscillator.stop(start + 0.16);
            });
        } catch {
            // audio tidak tersedia; abaikan
        }
    },

    destroy() {
        this.stop();
    },
});
