/**
 * CaFEE Brückenmühle – Eröffnungs-Landingpage
 * Countdown, QR-Download, Lesezeichen, Teilen-Funktionen, FAQ
 */
(function () {
    'use strict';

    // ----------------------------------------
    // Konfiguration
    // ----------------------------------------
    var OPENING_DATE = new Date('2026-07-11T09:00:00+02:00');
    var SHARE_URL = 'https://cafee-brueckenmuehle.de/eroeffnung';
    var SHARE_TITLE = 'Große Eröffnung CaFEE Brückenmühle';
    var SHARE_TEXT =
        'Große Eröffnung im CaFEE Brückenmühle am 11. Juli! ' +
        'Sichere dir 10% Eröffnungsrabatt – nur am 11. Juli gültig, einfach den QR-Code im CaFEE vorzeigen:';

    // ----------------------------------------
    // Toast
    // ----------------------------------------
    var toastEl = document.getElementById('toast');
    var toastTimer = null;
    function toast(message) {
        if (!toastEl) { return; }
        toastEl.textContent = message;
        toastEl.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toastEl.classList.remove('is-visible');
        }, 3200);
    }

    // ----------------------------------------
    // Countdown
    // ----------------------------------------
    function pad(value) {
        return value < 10 ? '0' + value : String(value);
    }

    function updateCountdown() {
        var cd = document.getElementById('countdown');
        var elDays = document.getElementById('cdDays');
        if (!cd || !elDays) { return; }

        var diff = OPENING_DATE.getTime() - Date.now();

        if (diff <= 0) {
            cd.classList.add('is-live');
            cd.innerHTML = '<span class="ero-live-msg">Wir haben eröffnet – willkommen!</span>';
            return true; // stop interval
        }

        var totalSeconds = Math.floor(diff / 1000);
        var days = Math.floor(totalSeconds / 86400);
        var hours = Math.floor((totalSeconds % 86400) / 3600);
        var mins = Math.floor((totalSeconds % 3600) / 60);
        var secs = totalSeconds % 60;

        elDays.textContent = String(days);
        document.getElementById('cdHours').textContent = pad(hours);
        document.getElementById('cdMins').textContent = pad(mins);
        document.getElementById('cdSecs').textContent = pad(secs);
        return false;
    }

    if (document.getElementById('countdown')) {
        if (!updateCountdown()) {
            var cdInterval = setInterval(function () {
                if (updateCountdown()) { clearInterval(cdInterval); }
            }, 1000);
        }
    }

    // ----------------------------------------
    // QR-Download (Fallback, falls download-Attribut blockiert)
    // ----------------------------------------
    var downloadQr = document.getElementById('downloadQr');
    if (downloadQr) {
        downloadQr.addEventListener('click', function () {
            toast('QR-Code wird heruntergeladen …');
        });
    }

    // ----------------------------------------
    // Lesezeichen speichern
    // ----------------------------------------
    var bookmarkBtn = document.getElementById('bookmarkBtn');
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', function () {
            var isMac = /Mac|iPod|iPhone|iPad/.test(navigator.platform);
            // Legacy-API (nur ältere IE/Firefox) – moderne Browser blockieren das.
            try {
                if (window.external && 'AddFavorite' in window.external) {
                    window.external.AddFavorite(SHARE_URL, SHARE_TITLE);
                    toast('Seite zu deinen Lesezeichen hinzugefügt!');
                    return;
                }
            } catch (e) { /* ignorieren */ }

            toast(isMac
                ? 'Drücke ⌘ + D, um diese Seite als Lesezeichen zu speichern.'
                : 'Drücke Strg + D, um diese Seite als Lesezeichen zu speichern.');
        });
    }

    // ----------------------------------------
    // Teilen
    // ----------------------------------------
    var waBtn = document.getElementById('shareWhatsApp');
    if (waBtn) {
        waBtn.href = 'https://wa.me/?text=' +
            encodeURIComponent(SHARE_TEXT + ' ' + SHARE_URL);
    }

    var mailBtn = document.getElementById('shareMail');
    if (mailBtn) {
        mailBtn.href = 'mailto:?subject=' +
            encodeURIComponent(SHARE_TITLE + ' – 10% Rabatt') +
            '&body=' + encodeURIComponent(SHARE_TEXT + '\n\n' + SHARE_URL);
    }

    var copyBtn = document.getElementById('copyLink');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            var text = SHARE_URL;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    toast('Link kopiert – jetzt mit Freunden teilen!');
                }).catch(function () {
                    legacyCopy(text);
                });
            } else {
                legacyCopy(text);
            }
        });
    }

    function legacyCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            toast('Link kopiert – jetzt mit Freunden teilen!');
        } catch (e) {
            toast('Link: ' + text);
        }
        document.body.removeChild(ta);
    }

    // Native Share (mobil) – Button nur anzeigen, wenn verfügbar
    var nativeBtn = document.getElementById('shareNative');
    if (nativeBtn && navigator.share) {
        nativeBtn.hidden = false;
        nativeBtn.addEventListener('click', function () {
            navigator.share({
                title: SHARE_TITLE,
                text: SHARE_TEXT,
                url: SHARE_URL
            }).catch(function () { /* abgebrochen */ });
        });
    }

    // ----------------------------------------
    // FAQ: nur ein Eintrag gleichzeitig offen
    // ----------------------------------------
    var faqItems = document.querySelectorAll('#faqList .ero-faq-item');
    faqItems.forEach(function (item) {
        item.addEventListener('toggle', function () {
            if (item.open) {
                faqItems.forEach(function (other) {
                    if (other !== item) { other.open = false; }
                });
            }
        });
    });
})();
