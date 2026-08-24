/* =====================================================================
   TakaLagbe! front end behaviour. Plain JavaScript, no library.
   Everything degrades gracefully: with JS off, every form still posts.
   ===================================================================== */
(function () {
  'use strict';

  /* dark mode */
  var root = document.documentElement;

  window.toggleTheme = function () {
    var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    try { localStorage.setItem('tkl-theme', next); } catch (e) {}
    var b = document.getElementById('themeBtn');
    if (b) b.textContent = next === 'dark' ? '☀️' : '🌙';
  };

  /* mobile menu */
  window.toggleNav = function () {
    var n = document.getElementById('navLinks');
    if (n) n.classList.toggle('open');
  };

  /* balance hiding */
  window.toggleBalance = function () {
    var a = document.getElementById('balanceAmount');
    if (!a) return;
    var hidden = a.dataset.hidden === '1';
    a.textContent = hidden ? a.dataset.real : '৳ ••••••';
    a.dataset.hidden = hidden ? '0' : '1';
    try { localStorage.setItem('tkl-hide-balance', hidden ? '0' : '1'); } catch (e) {}
    var b = document.getElementById('eyeBtn');
    if (b) b.textContent = hidden ? 'Hide' : 'Show';
  };

  /* amount chips */
  function wireChips() {
    document.querySelectorAll('[data-chip-target]').forEach(function (chip) {
      chip.addEventListener('click', function () {
        var input = document.getElementById(chip.dataset.chipTarget);
        if (!input) return;
        input.value = chip.dataset.value;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        chip.parentNode.querySelectorAll('.chip').forEach(function (c) {
          c.classList.remove('on');
        });
        chip.classList.add('on');
      });
    });
  }

  /* live charge preview */
  function wireCharge() {
    document.querySelectorAll('[data-charge]').forEach(function (input) {
      var out = document.getElementById(input.dataset.charge);
      if (!out) return;
      var pct  = parseFloat(input.dataset.feePct  || '0');
      var flat = parseFloat(input.dataset.feeFlat || '0');
      var cb   = parseFloat(input.dataset.cashbackPct || '0');
      var bal  = parseFloat(input.dataset.balance || '0');

      function draw() {
        var a = parseFloat(input.value || '0');
        if (!a || a <= 0) { out.innerHTML = ''; return; }
        var fee = a * pct / 100 + flat;
        var back = a * cb / 100;
        var total = a + fee;
        var left = bal - total + back;
        var html = '<div class="between tiny"><span class="muted">Amount</span>'
                 + '<b>৳ ' + a.toFixed(2) + '</b></div>';
        if (fee > 0) {
          html += '<div class="between tiny"><span class="muted">Charge</span>'
                + '<b>৳ ' + fee.toFixed(2) + '</b></div>';
        }
        if (back > 0) {
          html += '<div class="between tiny"><span class="muted">Cashback</span>'
                + '<b class="pos">+ ৳ ' + back.toFixed(2) + '</b></div>';
        }
        html += '<hr class="dashed" style="margin:.5rem 0">'
             +  '<div class="between tiny"><span class="muted">Total to pay</span>'
             +  '<b>৳ ' + total.toFixed(2) + '</b></div>'
             +  '<div class="between tiny"><span class="muted">Balance after</span><b class="'
             +  (left < 0 ? 'neg' : '') + '">৳ ' + left.toFixed(2) + '</b></div>';
        if (left < 0) {
          html += '<div class="err">This is more than your available balance.</div>';
        }
        out.innerHTML = html;
      }
      input.addEventListener('input', draw);
      draw();
    });
  }

  /* long press to send money */
  function wireHold() {
    document.querySelectorAll('.hold').forEach(function (btn) {
      var need = parseInt(btn.dataset.holdMs || '900', 10);
      var fill = btn.querySelector('.fill');
      var txt  = btn.querySelector('.txt');
      var label = txt ? txt.textContent : '';
      var timer = null, start = 0, done = false;

      function stop() {
        clearInterval(timer); timer = null;
        if (!done && fill) fill.style.width = '0%';
        if (!done && txt) txt.textContent = label;
      }
      function begin(ev) {
        if (done || btn.disabled) return;
        ev.preventDefault();
        var form = btn.form || btn.closest('form');
        if (form && typeof form.reportValidity === 'function' && !form.reportValidity()) return;
        start = Date.now();
        timer = setInterval(function () {
          var pct = Math.min(100, (Date.now() - start) / need * 100);
          if (fill) fill.style.width = pct + '%';
          if (txt) txt.textContent = 'Hold… ' + Math.round(pct) + '%';
          if (pct >= 100) {
            done = true; clearInterval(timer);
            if (txt) txt.textContent = 'Sending…';
            btn.disabled = true;
            if (form) { if (btn.name) addHidden(form, btn.name, btn.value); form.submit(); }
          }
        }, 30);
      }
      function addHidden(form, n, v) {
        var i = document.createElement('input');
        i.type = 'hidden'; i.name = n; i.value = v; form.appendChild(i);
      }
      btn.addEventListener('mousedown', begin);
      btn.addEventListener('touchstart', begin, { passive: false });
      ['mouseup', 'mouseleave', 'touchend', 'touchcancel'].forEach(function (ev) {
        btn.addEventListener(ev, stop);
      });
      btn.addEventListener('click', function (e) { e.preventDefault(); });
    });
  }

  /* PIN boxes */
  function wirePin() {
    document.querySelectorAll('[data-pin]').forEach(function (box) {
      var hidden = document.getElementById(box.dataset.pin);
      var cells = box.querySelectorAll('input');

      function sync() {
        var v = '';
        cells.forEach(function (c) { v += c.value; });
        if (hidden) hidden.value = v;
      }
      cells.forEach(function (cell, i) {
        cell.addEventListener('input', function () {
          cell.value = cell.value.replace(/\D/g, '').slice(-1);
          if (cell.value && cells[i + 1]) cells[i + 1].focus();
          sync();
        });
        cell.addEventListener('keydown', function (e) {
          if (e.key === 'Backspace' && !cell.value && cells[i - 1]) cells[i - 1].focus();
        });
        cell.addEventListener('paste', function (e) {
          e.preventDefault();
          var digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '');
          cells.forEach(function (c, j) { c.value = digits[j] || ''; });
          sync();
          if (cells[Math.min(digits.length, cells.length - 1)]) {
            cells[Math.min(digits.length, cells.length - 1)].focus();
          }
        });
      });
    });
  }

  /* password strength */
  function wireStrength() {
    document.querySelectorAll('[data-strength]').forEach(function (input) {
      var meter = document.getElementById(input.dataset.strength);
      if (!meter) return;
      var bar = meter.querySelector('span');
      input.addEventListener('input', function () {
        var v = input.value, s = 0;
        if (v.length >= 6) s++;
        if (v.length >= 10) s++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
        if (/\d/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        var pct = [0, 20, 40, 60, 80, 100][s];
        var col = ['#DCE6F0', '#E03131', '#F08C00', '#F5C518', '#12B886', '#0B7A5A'][s];
        bar.style.width = pct + '%';
        bar.style.background = col;
      });
    });
  }

  /* confirm dialogs */
  function wireConfirm() {
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
      el.addEventListener('submit', function (e) {
        if (!window.confirm(el.dataset.confirm)) e.preventDefault();
      });
      if (el.tagName === 'A' || el.tagName === 'BUTTON') {
        el.addEventListener('click', function (e) {
          if (!window.confirm(el.dataset.confirm)) e.preventDefault();
        });
      }
    });
  }

  /* live table filter */
  function wireFilter() {
    document.querySelectorAll('[data-filter]').forEach(function (input) {
      var scope = document.getElementById(input.dataset.filter);
      if (!scope) return;
      input.addEventListener('input', function () {
        var needle = input.value.toLowerCase().trim();
        var shown = 0;
        scope.querySelectorAll('[data-row]').forEach(function (row) {
          var hit = row.textContent.toLowerCase().indexOf(needle) !== -1;
          row.style.display = hit ? '' : 'none';
          if (hit) shown++;
        });
        var none = document.getElementById(input.dataset.filter + '-none');
        if (none) none.style.display = shown ? 'none' : '';
      });
    });
  }

  /* modals */
  window.openModal = function (id) {
    var m = document.getElementById(id);
    if (m) { m.classList.add('open'); var f = m.querySelector('input,select,textarea');
             if (f) setTimeout(function () { f.focus(); }, 40); }
  };
  window.closeModal = function (id) {
    var m = document.getElementById(id);
    if (m) m.classList.remove('open');
  };
  function wireModals() {
    document.querySelectorAll('.modal').forEach(function (m) {
      m.addEventListener('click', function (e) { if (e.target === m) m.classList.remove('open'); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        document.querySelectorAll('.modal.open').forEach(function (m) { m.classList.remove('open'); });
      }
    });
  }

  /* copy to clipboard */
  window.copyText = function (text, btn) {
    var done = function () {
      if (!btn) return;
      var old = btn.textContent;
      btn.textContent = 'Copied';
      setTimeout(function () { btn.textContent = old; }, 1400);
    };
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text).then(done, function () {});
    } else {
      var t = document.createElement('textarea');
      t.value = text; document.body.appendChild(t); t.select();
      try { document.execCommand('copy'); done(); } catch (e) {}
      document.body.removeChild(t);
    }
  };

  /* operator detection */
  function wireOperator() {
    document.querySelectorAll('[data-operator]').forEach(function (input) {
      var out = document.getElementById(input.dataset.operator);
      var map = { '017': 'Grameenphone', '013': 'Grameenphone', '019': 'Banglalink',
                  '014': 'Banglalink', '018': 'Robi', '016': 'Airtel', '015': 'Teletalk' };
      input.addEventListener('input', function () {
        var name = map[input.value.slice(0, 3)] || '';
        if (out) {
          out.textContent = name ? 'Operator: ' + name : '';
          var sel = document.getElementById('operatorField');
          if (sel && name) sel.value = name;
        }
      });
    });
  }

  /* dismiss the flash toast */
  function wireToast() {
    var t = document.getElementById('flashToast');
    if (!t) return;
    setTimeout(function () {
      t.style.transition = 'opacity .4s';
      t.style.opacity = '0';
      setTimeout(function () { t.remove(); }, 400);
    }, 5000);
    t.addEventListener('click', function () { t.remove(); });
  }

  /* stop a double submit on every form */
  function wireOnce() {
    document.querySelectorAll('form[data-once]').forEach(function (f) {
      f.addEventListener('submit', function () {
        var b = f.querySelector('button[type=submit],input[type=submit]');
        if (b && !b.classList.contains('hold')) {
          setTimeout(function () { b.disabled = true; b.textContent = 'Please wait…'; }, 10);
        }
      });
    });
  }

  /* live clock */
  function wireClock() {
    var el = document.getElementById('clock');
    if (!el) return;
    function tick() {
      el.textContent = new Date().toLocaleTimeString('en-GB',
        { hour: '2-digit', minute: '2-digit' });
    }
    tick(); setInterval(tick, 20000);
  }

  /* bootstrap */
  document.addEventListener('DOMContentLoaded', function () {
    wireChips(); wireCharge(); wireHold(); wirePin(); wireStrength();
    wireConfirm(); wireFilter(); wireModals(); wireOperator();
    wireToast(); wireOnce(); wireClock();

    try {
      if (localStorage.getItem('tkl-hide-balance') === '1') window.toggleBalance();
    } catch (e) {}
  });
})();
