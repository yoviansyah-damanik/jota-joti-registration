// Modal pratinjau dokumen (gambar/PDF); elemen <dialog x-ref="dialog"> berada dalam scope komponen
export default () => ({
    doc: { url: '', title: '', name: '', kind: '', download: '' },
    loading: false,

    view(doc) {
        this.doc = doc;
        this.loading = true;
        this.$refs.dialog.showModal();
    },

    close() {
        this.$refs.dialog.close();
    },

    // kosongkan sumber agar PDF/gambar berhenti dimuat setelah modal ditutup
    closed() {
        this.doc = { url: '', title: '', name: '', kind: '', download: '' };
        this.loading = false;
    },
});
