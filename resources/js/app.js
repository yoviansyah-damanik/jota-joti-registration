import troopNumberPair from './troop-number';
import qrScanner from './qr-scanner';
import documentViewer from './document-viewer';
import registerConfirm from './confirm';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('troopNumberPair', troopNumberPair);
    window.Alpine.data('qrScanner', qrScanner);
    window.Alpine.data('documentViewer', documentViewer);
    registerConfirm(window.Alpine);
});
