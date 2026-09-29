/* TakaLagbe! front end. Plain JavaScript, no library.
   Everything here is extra: with scripting off every form still posts and
   every dialog still opens, through a plain link to its #id. */
(function () {
  'use strict';

  var root = document.documentElement;
  var $ = function (sel, el) { return (el || document).querySelector(sel); };
  var $$ = function (sel, el) { return Array.prototype.slice.call((el || document).querySelectorAll(sel)); };
  // the page's words for anything the script says, in the reader's language
  var words = window.TKL_TEXT || {};
  var t = function (s) { return words[s] || s; };
  var taka = function (n) {
    return '৳ ' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  /* theme */
  function theme() {
    var btn = $('#themeBtn');
    if (!btn) return;
    var sync = function () {
      var dark = root.getAttribute('data-theme') === 'dark';
      btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
      btn.setAttribute('aria-label', dark ? t('Switch to light theme') : t('Switch to dark theme'));
    };
    sync();
    btn.addEventListener('click', function () {
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      try { localStorage.setItem('tkl-theme', next); } catch (e) {}
      sync();
    });
  }

  /* dialogs: the link to #id opens the <dialog> as a real modal, which
     keeps focus inside it and closes on Escape */
  function dialogs() {
    if (typeof HTMLDialogElement !== 'function') return;
    var open = function (d, opener) {
      if (!d.open) d.showModal();
      d.opener = opener || null;
      var f = $('input:not([type=hidden]),select,textarea', d);
      if (f) f.focus();
    };
    $$('a[href^="#"]').forEach(function (a) {
      var d = document.getElementById(a.getAttribute('href').slice(1));
      if (!d || d.tagName !== 'DIALOG') return;
      a.addEventListener('click', function (e) { e.preventDefault(); open(d, a); });
    });
    $$('dialog').forEach(function (d) {
      $$('[data-close]', d).forEach(function (c) {
        c.addEventListener('click', function (e) { e.preventDefault(); d.close(); });
      });
      d.addEventListener('click', function (e) { if (e.target === d) d.close(); });
      // a modal dialog already keeps the page behind it inert; this also stops
      // Tab wandering off into the browser's own toolbar
      d.addEventListener('keydown', function (e) {
        if (e.key !== 'Tab') return;
        var f = $$('a[href],button:not([disabled]),input:not([type=hidden]),select,textarea', d)
          .filter(function (x) { return x.offsetParent !== null; });
        if (!f.length) return;
        var first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      });
      d.addEventListener('close', function () {
        if (location.hash === '#' + d.id) history.replaceState(null, '', location.pathname + location.search);
        if (d.opener) d.opener.focus();
      });
    });
    // arrived with #id in the address, for example after a form error
    var h = location.hash && document.getElementById(location.hash.slice(1));
    if (h && h.tagName === 'DIALOG') {
      history.replaceState(null, '', location.pathname + location.search);
      open(h);
    }
  }

  /* hold to send. A pointer has to hold; Enter or a keyboard click
     (detail 0) submits straight away, as does the button with no script */
  function hold() {
    $$('.hold').forEach(function (btn) {
      var need = parseInt(btn.getAttribute('data-hold-ms') || '800', 10);
      var fill = $('.fill', btn), txt = $('.txt', btn), label = txt ? txt.textContent : '';
      var timer = null, done = false;
      var form = btn.form;

      function stop() {
        clearInterval(timer); timer = null;
        if (done) return;
        if (fill) fill.style.width = '0';
        if (txt) txt.textContent = label;
      }
      function start(e) {
        if (done || btn.disabled || (e.button !== undefined && e.button !== 0)) return;
        e.preventDefault();
        if (form && !form.reportValidity()) return;
        var t0 = Date.now();
        timer = setInterval(function () {
          var p = Math.min(100, (Date.now() - t0) / need * 100);
          if (fill) fill.style.width = p + '%';
          if (p >= 100) {
            done = true; clearInterval(timer);
            if (txt) txt.textContent = t('Sending…');
            if (form) form.requestSubmit ? form.requestSubmit() : form.submit();
          }
        }, 25);
      }
      btn.addEventListener('pointerdown', start);
      ['pointerup', 'pointerleave', 'pointercancel'].forEach(function (ev) { btn.addEventListener(ev, stop); });
      btn.addEventListener('click', function (e) { if (e.detail > 0 && !done) e.preventDefault(); });
    });
  }

  /* what a payment will cost, worked out as the amount is typed */
  function charges() {
    $$('[data-charge]').forEach(function (input) {
      var out = document.getElementById(input.getAttribute('data-charge'));
      if (!out) return;
      function draw() {
        var a = parseFloat(input.value);
        if (!(a > 0)) { out.innerHTML = ''; return; }
        var pct = parseFloat(input.getAttribute('data-fee-pct') || '0');
        var flat = parseFloat(input.getAttribute('data-fee-flat') || '0');
        var cb = parseFloat(input.getAttribute('data-cashback-pct') || '0');
        var bal = parseFloat(input.getAttribute('data-balance') || '0');
        var fee = Math.round((a * pct / 100 + flat) * 100) / 100;
        var back = Math.round(a * cb) / 100;
        var after = bal - a - fee + back;
        var rows = [[t('Amount'), taka(a)]];
        if (fee) rows.push([t('Charge'), taka(fee)]);
        if (back) rows.push([t('Cashback'), '+ ' + taka(back)]);
        rows.push([t('Balance after'), taka(after)]);
        out.innerHTML = rows.map(function (r) {
          return '<div><span>' + r[0] + '</span><b>' + r[1] + '</b></div>';
        }).join('') + (after < 0 ? '<span class="err">' + t('That is more than you have.') + '</span>' : '');
      }
      input.addEventListener('input', draw);
      draw();
    });
    // a merchant's cashback rate follows the merchant chosen
    $$('[data-cashback-from]').forEach(function (sel) {
      var input = document.getElementById(sel.getAttribute('data-cashback-from'));
      sel.addEventListener('change', function () {
        var o = sel.options[sel.selectedIndex];
        input.setAttribute('data-cashback-pct', o.getAttribute('data-cb') || '0');
        input.dispatchEvent(new Event('input'));
      });
      if (sel.value) sel.dispatchEvent(new Event('change'));
    });
  }

  function chips() {
    $$('[data-fill]').forEach(function (chip) {
      chip.addEventListener('click', function () {
        var input = document.getElementById(chip.getAttribute('data-fill'));
        if (!input) return;
        input.value = chip.getAttribute('data-value');
        input.dispatchEvent(new Event('input', { bubbles: true }));
        $$('[data-fill]', chip.parentNode).forEach(function (c) { c.setAttribute('aria-pressed', 'false'); });
        chip.setAttribute('aria-pressed', 'true');
        input.focus();
      });
    });
  }

  function confirms() {
    $$('form[data-confirm]').forEach(function (f) {
      f.addEventListener('submit', function (e) {
        if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault();
      });
    });
  }

  /* stop a second click sending the same form while the first is on its way */
  function once() {
    $$('form[data-once]').forEach(function (f) {
      f.addEventListener('submit', function () {
        var b = $('button[type=submit]', f);
        if (b) setTimeout(function () { b.disabled = true; }, 0);
      });
    });
  }

  function filters() {
    $$('[data-filter]').forEach(function (input) {
      var scope = document.getElementById(input.getAttribute('data-filter'));
      var none = document.getElementById(input.getAttribute('data-filter') + '-none');
      input.addEventListener('input', function () {
        var q = input.value.toLowerCase().trim(), shown = 0;
        $$('[data-row]', scope).forEach(function (r) {
          var hit = r.textContent.toLowerCase().indexOf(q) !== -1;
          r.hidden = !hit;
          if (hit) shown++;
        });
        if (none) none.hidden = shown > 0;
      });
    });
  }

  function copies() {
    $$('[data-copy]').forEach(function (b) {
      b.addEventListener('click', function () {
        var label = b.getAttribute('aria-label') || '';
        navigator.clipboard && navigator.clipboard.writeText(b.getAttribute('data-copy')).then(function () {
          b.setAttribute('aria-label', t('Copied'));
          b.classList.add('on');
          setTimeout(function () { b.setAttribute('aria-label', label); b.classList.remove('on'); }, 1400);
        });
      });
    });
  }

  function operators() {
    var map = { '017': 'Grameenphone', '013': 'Grameenphone', '019': 'Banglalink', '014': 'Banglalink',
                '018': 'Robi', '016': 'Airtel', '015': 'Teletalk' };
    $$('[data-operator]').forEach(function (input) {
      var out = document.getElementById(input.getAttribute('data-operator'));
      var draw = function () { out.textContent = map[input.value.slice(0, 3)] ? t('Operator: {name}').replace('{name}', map[input.value.slice(0, 3)]) : ''; };
      input.addEventListener('input', draw);
      draw();
    });
  }

  /* split a bill: show each share before the requests go out */
  function split() {
    var total = $('[data-split-total]'), people = $('[data-split-people]'), out = $('#splitPreview');
    if (!total || !people || !out) return;
    var draw = function () {
      var sum = parseFloat(total.value), n = people.value.split(/[\s,;]+/).filter(Boolean).length;
      out.textContent = sum > 0 && n
        ? t('Split {n} ways, {each} each, you included.').replace('{n}', n + 1)
            .replace('{each}', taka(Math.round(sum / (n + 1) * 100) / 100))
        : t('Add the total and at least one number to see each share.');
    };
    total.addEventListener('input', draw);
    people.addEventListener('input', draw);
    draw();
  }

  /* hide the balance on a shared screen, and remember the choice */
  function balance() {
    var btn = $('#eyeBtn'), fig = $('#balanceFigure');
    if (!btn || !fig) return;
    var set = function (hide) {
      fig.hidden = false;
      fig.classList.toggle('masked', hide);
      $('.real', fig).hidden = hide;
      $('.dots', fig).hidden = !hide;
      btn.setAttribute('aria-pressed', hide ? 'true' : 'false');
      btn.setAttribute('aria-label', hide ? t('Show balance') : t('Hide balance'));
      $('.i-eye', btn).style.display = hide ? '' : 'none';
      $('.i-eye-off', btn).style.display = hide ? 'none' : '';
    };
    var hidden = false;
    try { hidden = localStorage.getItem('tkl-hide-balance') === '1'; } catch (e) {}
    set(hidden);
    btn.addEventListener('click', function () {
      hidden = !hidden;
      set(hidden);
      try { localStorage.setItem('tkl-hide-balance', hidden ? '1' : '0'); } catch (e) {}
    });
  }

  function toast() {
    var t = $('#toast');
    if (!t) return;
    setTimeout(function () { t.remove(); }, 6000);
    var b = $('button', t);
    if (b) b.addEventListener('click', function () { t.remove(); });
  }

  /* an eye inside every password and PIN box, to check what was typed */
  function peeks() {
    var eye = '<svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" '
            + 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            + '<path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
    $$('input[type=password]').forEach(function (input) {
      var wrap = document.createElement('span');
      wrap.className = 'pw' + (input.classList.contains('pin') ? ' pin-wrap' : '');
      input.parentNode.insertBefore(wrap, input);
      wrap.appendChild(input);
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'peek';
      b.innerHTML = eye;
      b.setAttribute('aria-pressed', 'false');
      b.setAttribute('aria-label', t('Show what you typed'));
      b.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        b.setAttribute('aria-pressed', show ? 'true' : 'false');
        b.setAttribute('aria-label', show ? t('Hide it again') : t('Show what you typed'));
        input.focus();
      });
      wrap.appendChild(b);
    });
  }

  function demo() {
    var b = $('#tryDemo');
    if (!b) return;
    b.addEventListener('click', function () {
      $('#Phone').value = b.getAttribute('data-phone');
      $('#Password').value = b.getAttribute('data-password');
      $('#demoPin').hidden = false;
      $('#Password').focus();
    });
  }

  /* sections rise in as they are scrolled to, a beat apart */
  function reveals() {
    var els = $$('.reveal');
    if (!('IntersectionObserver' in window)) { els.forEach(function (el) { el.classList.add('in'); }); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px' });
    els.forEach(function (el) { io.observe(el); });
  }

  document.addEventListener('DOMContentLoaded', function () {
    theme(); dialogs(); hold(); charges(); chips(); confirms(); once(); filters();
    copies(); operators(); split(); balance(); toast(); peeks(); demo(); reveals();
  });
})();
