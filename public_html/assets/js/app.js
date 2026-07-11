/* =========================================================================
 * Nigeria GovTech Conference & Awards — front-end behaviour
 * Speakers & tickets are rendered server-side (PHP); this drives motion,
 * the live countdown, scroll reveals, counters, and the ticket stepper.
 * ========================================================================= */
(function () {
  'use strict';

  /* ---------- guilloché generator (signature element) ---------- */
  function hypotrochoid(R, r, d, turns, scale, cx, cy) {
    var pts = [], steps = turns * 120;
    for (var i = 0; i <= steps; i++) {
      var t = i / 120 * 2 * Math.PI;
      var x = (R - r) * Math.cos(t) + d * Math.cos((R - r) / r * t);
      var y = (R - r) * Math.sin(t) - d * Math.sin((R - r) / r * t);
      pts.push((cx + x * scale).toFixed(2) + ',' + (cy + y * scale).toFixed(2));
    }
    return 'M' + pts.join(' L');
  }
  function buildGuilloche(el) {
    if (!el) return;
    var C = 100;
    var rings = [
      { R: 5, r: 3, d: 5, turns: 3, scale: 9, stroke: '#C9A227', op: 0.55, w: 0.4 },
      { R: 7, r: 4, d: 4, turns: 4, scale: 7, stroke: '#16B47A', op: 0.4, w: 0.4 },
      { R: 8, r: 3, d: 6, turns: 3, scale: 6, stroke: '#C9A227', op: 0.35, w: 0.35 },
      { R: 11, r: 6, d: 5, turns: 6, scale: 4.2, stroke: '#16B47A', op: 0.3, w: 0.3 }
    ];
    var inner = rings.map(function (g, i) {
      return '<path class="' + (i % 2 ? 'ring2' : '') + '" d="' +
        hypotrochoid(g.R, g.r, g.d, g.turns, g.scale, C, C) +
        '" stroke="' + g.stroke + '" stroke-width="' + g.w +
        '" fill="none" opacity="' + g.op + '" style="transform-origin:' + C + 'px ' + C + 'px"/>';
    }).join('');
    el.innerHTML = '<svg viewBox="0 0 200 200" fill="none">' +
      '<circle cx="100" cy="100" r="95" stroke="#C9A227" stroke-width=".4" opacity=".4"/>' +
      '<circle cx="100" cy="100" r="60" stroke="#16B47A" stroke-width=".4" opacity=".3"/>' +
      inner + '</svg>';
  }
  buildGuilloche(document.getElementById('guilloche'));

  /* ---------- medallion rays ---------- */
  (function () {
    var host = document.getElementById('medRays');
    if (!host) return;
    var rays = '';
    for (var i = 0; i < 72; i++) {
      var a = i * 5 * Math.PI / 180;
      var x1 = 100 + 72 * Math.cos(a), y1 = 100 + 72 * Math.sin(a);
      var x2 = 100 + 88 * Math.cos(a), y2 = 100 + 88 * Math.sin(a);
      rays += '<line x1="' + x1 + '" y1="' + y1 + '" x2="' + x2 + '" y2="' + y2 +
        '" stroke="#C9A227" stroke-width="' + (i % 2 ? '.4' : '.8') + '" opacity=".5"/>';
    }
    host.innerHTML = rays;
  })();

  /* ---------- countdown (target injected by server) ---------- */
  (function () {
    var box = document.getElementById('countdown');
    if (!box) return;
    var target = new Date(box.getAttribute('data-target') || '2026-10-07T09:00:00+01:00').getTime();
    var cd_d = document.getElementById('cd-d'), cd_h = document.getElementById('cd-h'),
        cd_m = document.getElementById('cd-m'), cd_s = document.getElementById('cd-s');
    var p = function (n) { return String(n).padStart(2, '0'); };
    function tick() {
      var diff = Math.max(0, target - Date.now());
      cd_d.textContent = p(Math.floor(diff / 86400000));
      cd_h.textContent = p(Math.floor(diff % 86400000 / 3600000));
      cd_m.textContent = p(Math.floor(diff % 3600000 / 60000));
      cd_s.textContent = p(Math.floor(diff % 60000 / 1000));
    }
    tick();
    setInterval(tick, 1000);
  })();

  /* ---------- nav scroll ---------- */
  var nav = document.getElementById('nav');
  if (nav) {
    window.addEventListener('scroll', function () {
      nav.classList.toggle('scrolled', window.scrollY > 40);
    });
  }

  /* ---------- mobile drawer ---------- */
  var drawer = document.getElementById('drawer');
  var menuToggle = document.getElementById('menuToggle');
  var drawerClose = document.getElementById('drawerClose');
  if (drawer && menuToggle) {
    menuToggle.onclick = function () { drawer.classList.add('open'); };
    if (drawerClose) drawerClose.onclick = function () { drawer.classList.remove('open'); };
    drawer.querySelectorAll('a').forEach(function (a) {
      a.onclick = function () { drawer.classList.remove('open'); };
    });
  }

  /* ---------- reveal on scroll ---------- */
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });

  /* ---------- animated counters ---------- */
  var cio = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (!en.isIntersecting) return;
      var el = en.target, to = +el.dataset.to, t0 = null, dur = 1600;
      function step(ts) {
        if (!t0) t0 = ts;
        var prog = Math.min(1, (ts - t0) / dur);
        var ease = 1 - Math.pow(1 - prog, 3);
        el.textContent = Math.floor(ease * to);
        if (prog < 1) requestAnimationFrame(step); else el.textContent = to;
      }
      requestAnimationFrame(step);
      cio.unobserve(el);
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.count').forEach(function (el) { cio.observe(el); });

  /* ---------- ticket stepper + order bar (server-rendered cards) ---------- */
  (function () {
    var grid = document.getElementById('tkGrid');
    var bar = document.getElementById('orderBar');
    if (!grid || !bar) return;
    var totEl = document.getElementById('orderTot');
    var cntEl = document.getElementById('orderCnt');
    var checkoutBtn = document.getElementById('checkoutBtn');
    var fmt = function (n) { return n.toLocaleString('en-NG'); };

    function refresh() {
      var total = 0, count = 0;
      grid.querySelectorAll('.tk').forEach(function (card) {
        var q = +card.getAttribute('data-qty') || 0;
        var price = +card.getAttribute('data-price') || 0;
        total += q * price;
        count += q;
        var qEl = card.querySelector('.qty');
        if (qEl) qEl.textContent = q;
      });
      totEl.textContent = fmt(total);
      cntEl.textContent = count
        ? count + ' pass' + (count > 1 ? 'es' : '') + ' selected'
        : 'No passes selected';
      return count;
    }

    grid.querySelectorAll('.stepper button').forEach(function (b) {
      b.onclick = function () {
        var card = b.closest('.tk');
        var q = +card.getAttribute('data-qty') || 0;
        q = Math.max(0, q + (+b.getAttribute('data-op')));
        card.setAttribute('data-qty', q);
        refresh();
      };
    });

    if (checkoutBtn) {
      checkoutBtn.onclick = function (e) {
        e.preventDefault();
        var count = refresh();
        if (!count) {
          cntEl.textContent = 'Select at least one pass to continue';
          return;
        }
        // Build the cart and hand off to registration (Phase 2).
        var items = [];
        grid.querySelectorAll('.tk').forEach(function (card) {
          var q = +card.getAttribute('data-qty') || 0;
          if (q > 0) items.push(card.getAttribute('data-id') + ':' + q);
        });
        window.location.href = '/register?cart=' + encodeURIComponent(items.join(','));
      };
    }
  })();

  /* ---------- speaker detail modal ---------- */
  (function () {
    var modal = document.getElementById('spkModal');
    if (!modal) return;
    var img = document.getElementById('spkModalImg');
    var fb = document.getElementById('spkModalFallback');
    var nameEl = document.getElementById('spkModalName');
    var roleEl = document.getElementById('spkModalRole');
    var bioEl = document.getElementById('spkModalBio');
    var lastFocus = null;

    function open(card) {
      var name = card.getAttribute('data-name') || '';
      var role = card.getAttribute('data-role') || '';
      var photo = card.getAttribute('data-photo') || '';
      var initials = card.getAttribute('data-initials') || '';
      var bio = card.getAttribute('data-bio') || '';
      nameEl.textContent = name;
      roleEl.textContent = role;
      roleEl.style.display = role ? '' : 'none';
      bioEl.textContent = bio || 'Full profile coming soon.';
      if (photo) {
        img.src = photo; img.alt = name; img.hidden = false; fb.hidden = true;
      } else {
        fb.textContent = initials; fb.hidden = false; img.hidden = true;
      }
      lastFocus = document.activeElement;
      modal.classList.add('open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }
    function close() {
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      img.src = '';
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    document.querySelectorAll('.spk[role="button"]').forEach(function (card) {
      card.addEventListener('click', function () { open(card); });
      card.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(card); }
      });
    });
    modal.querySelectorAll('[data-close]').forEach(function (el) {
      el.addEventListener('click', close);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('open')) close();
    });
  })();

  /* ---------- gallery edition filter ---------- */
  (function () {
    var tabs = document.querySelectorAll('.gal-tabs .gal-tab');
    var grid = document.getElementById('galGrid');
    if (!tabs.length || !grid) return;
    var items = grid.querySelectorAll('.gal');
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) { t.classList.remove('active'); });
        tab.classList.add('active');
        var ed = tab.getAttribute('data-ed');
        items.forEach(function (it) {
          var show = ed === '*' || it.getAttribute('data-edition') === ed;
          it.style.display = show ? '' : 'none';
        });
      });
    });
  })();
})();
