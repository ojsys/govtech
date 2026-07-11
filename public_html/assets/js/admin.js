/* =========================================================================
 * Admin — check-in scanner.
 * Uses the native BarcodeDetector API when available (Chrome/Edge/Android).
 * Always offers manual code entry as a fallback. Posts to /admin/checkin/scan.
 * ========================================================================= */
(function () {
  'use strict';
  var grid = document.querySelector('.scan-grid');
  if (!grid) return;

  var scanUrl = grid.getAttribute('data-scan-url');
  var csrf = grid.getAttribute('data-csrf');
  var prefill = grid.getAttribute('data-prefill') || '';

  var video = document.getElementById('scanVideo');
  var verdict = document.getElementById('verdict');
  var camToggle = document.getElementById('camToggle');
  var camHint = document.getElementById('camHint');
  var manualForm = document.getElementById('manualForm');
  var manualInput = document.getElementById('manualCode');
  var statChecked = document.getElementById('statChecked');

  var stream = null, scanning = false, lastCode = '', lastAt = 0, detector = null;

  function setVerdict(cls, big, detailHtml) {
    verdict.className = 'scan-verdict ' + (cls || '');
    verdict.innerHTML = '<div class="big">' + big + '</div><div class="det">' + (detailHtml || '') + '</div>';
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
    });
  }

  function submit(code) {
    code = (code || '').trim();
    if (!code) return;
    setVerdict('', 'Checking…', esc(code));
    var body = new URLSearchParams();
    body.set('_token', csrf);
    body.set('code', code);
    fetch(scanUrl, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString()
    }).then(function (r) { return r.json(); }).then(function (d) {
      render(d);
    }).catch(function () {
      setVerdict('bad', 'Error', 'Could not reach the server. Try again.');
    });
  }

  function render(d) {
    var t = d.ticket || {};
    var detail = '';
    if (t.holder) detail += '<b>' + esc(t.holder) + '</b><br>';
    if (t.type) detail += esc(t.type) + '<br>';
    if (t.reference) detail += '<span class="muted">' + esc(t.reference) + '</span><br>';
    if (d.result === 'already' && t.checked_in_at) detail += '<span class="muted">at ' + esc(t.checked_in_at) + '</span>';

    if (d.result === 'ok') {
      setVerdict('ok', '✓ Admit', detail || esc(d.message));
      if (statChecked) statChecked.textContent = (parseInt(statChecked.textContent, 10) || 0) + 1;
      beep(true);
    } else if (d.result === 'already') {
      setVerdict('already', '⚠ Already in', detail || esc(d.message));
      beep(false);
    } else {
      setVerdict('bad', '✕ ' + esc(d.message || 'Invalid'), detail);
      beep(false);
    }
  }

  function beep(good) {
    try {
      var ac = new (window.AudioContext || window.webkitAudioContext)();
      var o = ac.createOscillator(), g = ac.createGain();
      o.connect(g); g.connect(ac.destination);
      o.frequency.value = good ? 880 : 220; g.gain.value = 0.05;
      o.start(); setTimeout(function () { o.stop(); ac.close(); }, good ? 120 : 240);
    } catch (e) { /* no audio, no problem */ }
  }

  /* ---- manual entry ---- */
  manualForm.addEventListener('submit', function (e) {
    e.preventDefault();
    submit(manualInput.value);
    manualInput.value = '';
    manualInput.focus();
  });
  if (prefill) submit(prefill);

  /* ---- camera scanning (BarcodeDetector) ---- */
  function supported() {
    return 'BarcodeDetector' in window && navigator.mediaDevices && navigator.mediaDevices.getUserMedia;
  }

  async function startCamera() {
    if (!supported()) {
      camHint.textContent = 'Live scanning isn\'t supported on this browser — please type the code.';
      camToggle.disabled = true;
      return;
    }
    try {
      detector = new window.BarcodeDetector({ formats: ['qr_code'] });
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
      video.srcObject = stream;
      await video.play();
      scanning = true;
      camToggle.textContent = 'Stop camera';
      loop();
    } catch (err) {
      camHint.textContent = 'Camera unavailable: ' + (err && err.message ? err.message : 'permission denied') + '. Type the code instead.';
    }
  }

  function stopCamera() {
    scanning = false;
    if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
    video.srcObject = null;
    camToggle.textContent = 'Start camera';
  }

  async function loop() {
    if (!scanning) return;
    try {
      var codes = await detector.detect(video);
      if (codes && codes.length) {
        var raw = codes[0].rawValue || '';
        var now = Date.now();
        // De-dupe: ignore the same code within 3s.
        if (raw && (raw !== lastCode || now - lastAt > 3000)) {
          lastCode = raw; lastAt = now;
          submit(raw);
        }
      }
    } catch (e) { /* keep looping */ }
    requestAnimationFrame(loop);
  }

  camToggle.addEventListener('click', function () {
    if (scanning) stopCamera(); else startCamera();
  });
})();

/* =========================================================================
 * Ticket preview modal. Any element with [data-ticket-preview="<url>"] opens
 * a modal and loads that URL's HTML fragment (the designed ticket).
 * ========================================================================= */
(function () {
  'use strict';
  var modal = document.getElementById('ticketModal');
  if (!modal) return;
  var body = document.getElementById('modalBody');
  var titleEl = document.getElementById('modalTitle');

  function open(url, title) {
    titleEl.textContent = title || 'Ticket preview';
    body.innerHTML = '<div class="loading">Loading ticket…</div>';
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.text(); })
      .then(function (html) { body.innerHTML = html; })
      .catch(function () { body.innerHTML = '<div class="loading">Could not load the ticket.</div>'; });
  }

  function close() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    body.innerHTML = '';
  }

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-ticket-preview]');
    if (trigger) {
      e.preventDefault();
      open(trigger.getAttribute('data-ticket-preview'), trigger.getAttribute('data-title'));
      return;
    }
    if (e.target.closest('[data-close]')) close();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('open')) close();
  });
})();
