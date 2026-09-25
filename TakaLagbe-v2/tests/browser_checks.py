#!/usr/bin/env python3
"""
Browser checks for TakaLagbe!, run in a real browser through Playwright.

  1. crawl     every page a customer and an admin can reach: status and console errors
  2. contrast  every piece of text on every page, light and dark, against WCAG AA
  3. ui        theme, dialogs, keyboard, hold to send, filters, pages, 390px width
  4. nojs      the same flows with scripting switched off
  5. writes    sign up, create, edit, delete and a refused peek, checked in the database
  6. shots     screenshots of the main pages, desktop and phone, both themes

Point it at a server running on a copy of the database filled by
tools/seed_demo.php, because it signs people up and moves money:

  php tools/seed_demo.php --yes          (with TKL_DB_NAME=takalagbe_test)
  python tests/browser_checks.py

It uses the Edge that comes with Windows; set TKL_BROWSER=chrome for Chrome.
"""
import os, re, subprocess, sys, time
from urllib.parse import urljoin, urlparse
from playwright.sync_api import sync_playwright

BASE    = os.environ.get("TKL_BASE", "http://127.0.0.1:8090")
DB_NAME = os.environ.get("TKL_DB", "takalagbe_test")
MYSQL   = os.environ.get("TKL_MYSQL", r"C:\xampp\mysql\bin\mysql.exe")
CHANNEL = os.environ.get("TKL_BROWSER", "msedge")
OUT     = os.path.join(os.path.dirname(__file__), "out")
USER, OTHER, PASSWORD, PIN = "01711482093", "01819337406", "demo1234", "482915"

PASS, FAIL = [], []


def check(name, ok, extra=""):
    (PASS if ok else FAIL).append(name)
    print(("  ok   " if ok else "  FAIL ") + name + ("  -> " + str(extra) if extra and not ok else ""))


def val(sql):
    out = subprocess.run([MYSQL, "-u", "root", "-N", "-B", DB_NAME, "-e", sql],
                         capture_output=True, text=True, check=True).stdout.strip()
    return out.split("\t")[0] if out else None


def sign_in(page, phone=USER, password=PASSWORD):
    page.goto(BASE + "/login.php")
    page.fill("#Phone", phone)
    page.fill("#Password", password)
    page.press("#Password", "Enter")
    page.wait_for_url("**/account.php")


def admin_in(page):
    page.goto(BASE + "/admin_login.php")
    page.fill("#username", "admin")
    page.fill("#password", "admin123")
    page.click("button[type=submit]")
    page.wait_for_url("**/admin_dashboard.php")


# ------------------------------------------------------------------ crawl

SKIP = re.compile(r"logout|format=csv|#|^mailto:")


def crawl(context, start, limit=150):
    """Follows every same-site link from start. Returns {url: (status, errors)}."""
    page = context.new_page()
    errors = []
    page.on("console", lambda m: errors.append(m.text) if m.type == "error" else None)
    page.on("pageerror", lambda e: errors.append(str(e)))
    page.on("requestfailed", lambda r: errors.append("failed " + r.url))
    seen, queue, result = set(), [start], {}
    per_shape = {}
    while queue and len(result) < limit:
        url = queue.pop(0)
        if url in seen:
            continue
        seen.add(url)
        # a hundred receipts are one page as far as the crawl is concerned
        shape = re.sub(r"=[^&]*", "=", url)
        per_shape[shape] = per_shape.get(shape, 0) + 1
        if per_shape[shape] > 3:
            continue
        errors.clear()
        resp = page.goto(url)
        page.wait_for_load_state("networkidle")
        result[url] = (resp.status if resp else 0, list(errors))
        for href in page.eval_on_selector_all("a[href]", "els => els.map(a => a.getAttribute('href'))"):
            if not href or SKIP.search(href):
                continue
            full = urljoin(url, href)
            if urlparse(full).netloc == urlparse(BASE).netloc and full not in seen:
                queue.append(full)
    page.close()
    return result


# --------------------------------------------------------------- contrast

CONTRAST_JS = r"""
() => {
  const parse = c => {
    const m = c && c.match(/rgba?\(([^)]+)\)/);
    if (!m) return null;
    const p = m[1].split(/[\s,\/]+/).filter(Boolean).map(Number);
    return {r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1};
  };
  const lin = v => { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); };
  const lum = c => 0.2126 * lin(c.r) + 0.7152 * lin(c.g) + 0.0722 * lin(c.b);
  const over = (top, under) => ({r: top.r * top.a + under.r * (1 - top.a),
                                 g: top.g * top.a + under.g * (1 - top.a),
                                 b: top.b * top.a + under.b * (1 - top.a), a: 1});
  const ratio = (a, b) => { const x = lum(a), y = lum(b); return (Math.max(x, y) + .05) / (Math.min(x, y) + .05); };

  // every background this element could be sitting on; a gradient gives one per stop
  const backgrounds = el => {
    let stack = [];
    for (let e = el; e; e = e.parentElement) {
      const cs = getComputedStyle(e);
      if (cs.backgroundImage.includes('gradient')) {
        // a see-through stop (a glow laid over the card) is painted on top of
        // the solid stops underneath it, so test it mixed with each of them
        const stops = [...cs.backgroundImage.matchAll(/rgba?\([^)]+\)/g)].map(m => parse(m[0]));
        const solid = stops.filter(s => s.a >= 1), glass = stops.filter(s => s.a < 1);
        const under = solid.length ? solid : [{r: 255, g: 255, b: 255, a: 1}];
        const all = under.concat(...glass.map(g => under.map(u => over(g, u))));
        return all.map(s => stack.reduceRight((acc, layer) => over(layer, acc), s));
      }
      const c = parse(cs.backgroundColor);
      if (c && c.a > 0) {
        stack.push(c);
        if (c.a >= 1) break;
      }
    }
    let base = {r: 255, g: 255, b: 255, a: 1};
    if (!stack.length || stack[stack.length - 1].a < 1) stack.push(base);
    return [stack.reduceRight((acc, layer) => acc ? over(layer, acc) : layer, null)];
  };

  const bad = [];
  let checked = 0;
  for (const el of document.querySelectorAll('body *')) {
    const text = [...el.childNodes].filter(n => n.nodeType === 3).map(n => n.textContent).join('').trim();
    if (!text) continue;
    const cs = getComputedStyle(el);
    const box = el.getBoundingClientRect();
    if (cs.visibility === 'hidden' || cs.display === 'none' || box.width === 0 || box.height === 0) continue;
    if (el.closest('[aria-hidden="true"], .sr-only, [hidden], option, script, style')) continue;
    if (el.closest('button:disabled, input:disabled')) continue;
    const fg = parse(cs.color);
    if (!fg) continue;
    const size = parseFloat(cs.fontSize), weight = parseInt(cs.fontWeight, 10);
    const need = (size >= 24 || (size >= 18.66 && weight >= 700)) ? 3 : 4.5;
    for (const bg of backgrounds(el)) {
      const r = ratio(fg.a < 1 ? over(fg, bg) : fg, bg);
      checked++;
      if (r < need) {
        bad.push({text: text.slice(0, 40), ratio: Math.round(r * 100) / 100, need,
                  cls: (el.className && el.className.baseVal === undefined ? el.className : '') || el.tagName});
        break;
      }
    }
  }
  return {checked, bad};
}
"""


def contrast(context, urls, theme):
    page = context.new_page()
    page.emulate_media(reduced_motion="reduce", color_scheme=theme)
    page.add_init_script("try { localStorage.setItem('tkl-theme', '%s') } catch (e) {}" % theme)
    failures, total = [], 0
    for url in urls:
        page.goto(url)
        page.wait_for_load_state("networkidle")
        res = page.evaluate(CONTRAST_JS)
        total += res["checked"]
        for b in res["bad"]:
            failures.append((urlparse(url).path + ("?" + urlparse(url).query if urlparse(url).query else ""), b))
    page.close()
    return total, failures


# --------------------------------------------------------------------- main

def main():
    os.makedirs(OUT, exist_ok=True)
    with sync_playwright() as p:
        browser = p.chromium.launch(channel=CHANNEL)

        # ---- 1. crawl --------------------------------------------------
        print("\n=== crawl ===")
        cust = browser.new_context()
        sign_in(cust.new_page())
        pages = crawl(cust, BASE + "/account.php")
        adm = browser.new_context()
        admin_in(adm.new_page())
        admin_pages = crawl(adm, BASE + "/admin_dashboard.php")
        anon = browser.new_context()
        public = crawl(anon, BASE + "/login.php", limit=10)
        everything = {**pages, **admin_pages, **public}

        not_ok = {u: s for u, (s, _) in everything.items() if s != 200}
        noisy = {u: e for u, (_, e) in everything.items() if e}
        check("crawled %d pages (%d customer, %d admin, %d signed out)"
              % (len(everything), len(pages), len(admin_pages), len(public)), len(everything) > 40, len(everything))
        check("every crawled page answers 200", not not_ok, not_ok)
        check("no page logs a console error or a failed request", not noisy, noisy)

        r = anon.new_page().goto(BASE + "/no-such-page.php")
        check("an unknown address answers 404 on purpose", r.status == 404, r.status)
        r = anon.new_page().goto(BASE + "/account.php")
        check("a signed out visitor is sent to sign in", r.url.endswith("login.php"), r.url)

        # ---- 2. contrast -----------------------------------------------
        print("\n=== contrast, WCAG AA ===")
        for theme in ("light", "dark"):
            total, fails = contrast(cust, list(pages), theme)
            t2, f2 = contrast(adm, list(admin_pages), theme)
            t3, f3 = contrast(anon, list(public), theme)
            fails = fails + f2 + f3
            check("%s theme: %d text and background pairs all reach 4.5:1 (3:1 for large text)"
                  % (theme, total + t2 + t3), not fails, fails[:12])

        # ---- 3. interactions -------------------------------------------
        print("\n=== interactions ===")
        page = cust.new_page()
        page.goto(BASE + "/account.php")
        before = page.get_attribute("html", "data-theme")
        page.click("#themeBtn")
        after = page.get_attribute("html", "data-theme")
        page.reload()
        check("the theme toggle switches and is remembered after a reload",
              before != after and page.get_attribute("html", "data-theme") == after, (before, after))
        head = page.content().split("</head>")[0]
        check("the theme is set by a script in the head, before the stylesheet can paint",
              head.find("data-theme") < head.find("app.css"))
        page.click("#themeBtn")

        page.goto(BASE + "/account.php")
        page.keyboard.press("Tab")
        check("the first Tab lands on the skip link",
              page.evaluate("document.activeElement.className") == "skip")

        page.goto(BASE + "/requests.php")
        page.click('a[href="#askMoney"]')
        check("the ask dialog opens as a modal", page.evaluate("document.getElementById('askMoney').open"))
        inside = []
        for _ in range(12):
            page.keyboard.press("Tab")
            inside.append(page.evaluate("!!document.activeElement.closest('#askMoney')"))
        check("Tab stays inside the open dialog", all(inside), inside)
        page.keyboard.press("Escape")
        check("Escape closes it", not page.evaluate("document.getElementById('askMoney').open"))
        check("and focus goes back to the button that opened it",
              page.evaluate("document.activeElement.getAttribute('href')") == "#askMoney")

        page.goto(BASE + "/sendmoney.php")
        page.click('[data-fill="amount"][data-value="500"]')
        check("an amount chip fills the amount", page.input_value("#amount") == "500")
        check("the cost of the transfer is worked out as you type", "Balance after" in page.inner_text("#sendSum"))

        bal0 = float(val("SELECT balance FROM account WHERE phone='%s'" % USER))
        page.fill("#receiver_phone", OTHER)
        page.fill("#amount", "25")
        page.fill("#pin", PIN)
        page.click(".hold")                      # a quick click must not send
        time.sleep(.6)
        check("a quick click on the hold button does not send", page.url.endswith("sendmoney.php"))
        page.focus("#note")
        page.keyboard.press("Enter")             # the keyboard sends straight away
        page.wait_for_url("**/receipt.php*")
        check("Enter sends the money", abs(bal0 - 25 - float(val(
              "SELECT balance FROM account WHERE phone='%s'" % USER))) < .001)

        page.goto(BASE + "/sendmoney.php")
        page.fill("#receiver_phone", OTHER)
        page.fill("#amount", "15")
        page.fill("#pin", PIN)
        page.locator(".hold").scroll_into_view_if_needed()
        box = page.locator(".hold").bounding_box()
        page.mouse.move(box["x"] + 20, box["y"] + 10)
        page.mouse.down(); time.sleep(1.1); page.mouse.up()
        page.wait_for_url("**/receipt.php*")
        check("holding the button sends the money", "receipt.php" in page.url)

        page.goto(BASE + "/history.php?type=Payment")
        kinds = set(page.eval_on_selector_all("tbody tr td:first-child", "t => t.map(x => x.innerText.split('\\n')[0].trim())"))
        check("the type filter shows only that type", kinds == {"Payment"}, kinds)
        first = page.inner_text("tbody")
        page.click("a[rel=next]")
        check("the next page shows different rows", page.inner_text("tbody") != first and "page=2" in page.url)
        page.goto(BASE + "/history.php?q=Star+Kabab")
        rows = page.eval_on_selector_all("tbody tr", "t => t.map(x => x.innerText)")
        check("search narrows the history to matching rows", rows and all("Star Kabab" in r for r in rows), len(rows))
        page.goto(BASE + "/history.php?q=zzzz-nothing")
        check("a search with no hits says so", "Nothing matches" in page.content())

        page.goto(BASE + "/qr.php")
        check("the QR code is drawn", page.locator(".qr-lg svg path").count() == 1)

        ap = adm.new_page()
        ap.goto(BASE + "/admin_merchant.php")
        ap.fill("#orgFilter", "wasa")
        shown = ap.eval_on_selector_all("#orgTable tbody tr:not([hidden])", "r => r.length")
        check("the biller filter narrows the table", 0 < shown < 16, shown)

        # ---- phone width
        phone = browser.new_context(viewport={"width": 390, "height": 844}, is_mobile=True, has_touch=True)
        sign_in(phone.new_page())
        pp = phone.new_page()
        wide = []
        for url in list(pages) + list(public):
            pp.goto(url)
            if pp.evaluate("document.documentElement.scrollWidth > window.innerWidth"):
                wide.append(urlparse(url).path)
        pa = browser.new_context(viewport={"width": 390, "height": 844}, is_mobile=True)
        admin_in(pa.new_page())
        pap = pa.new_page()
        for url in admin_pages:
            pap.goto(url)
            if pap.evaluate("document.documentElement.scrollWidth > window.innerWidth"):
                wide.append(urlparse(url).path)
        check("no page scrolls sideways at 390px", not wide, sorted(set(wide)))

        rm = browser.new_context(reduced_motion="reduce")
        sign_in(rm.new_page())
        rp = rm.new_page()
        rp.goto(BASE + "/account.php")
        check("with reduced motion there is no veil and nothing animates",
              rp.evaluate("getComputedStyle(document.querySelector('.veil')).display") == "none"
              and rp.evaluate("getComputedStyle(document.querySelector('.hero .rosette')).animationName") == "none")

        # ---- 4. no script ----------------------------------------------
        # with scripting off Playwright cannot wait for an element to
        # stop moving, so these clicks go straight through
        print("\n=== with scripting off ===")
        nojs = browser.new_context(java_script_enabled=False)
        np_ = nojs.new_page()
        sign_in(np_)
        bal0 = float(val("SELECT balance FROM account WHERE phone='%s'" % USER))
        np_.goto(BASE + "/sendmoney.php")
        np_.fill("#receiver_phone", OTHER)
        np_.fill("#amount", "40")
        np_.fill("#pin", PIN)
        np_.click("main form.card button[type=submit]", force=True)
        np_.wait_for_url("**/receipt.php*")
        check("money can be sent with scripting off", abs(bal0 - 40 - float(val(
              "SELECT balance FROM account WHERE phone='%s'" % USER))) < .001)

        np_.goto(BASE + "/requests.php")
        np_.click('a[href="#askMoney"]', force=True)
        check("a dialog opens from its link with scripting off", np_.is_visible("#askMoney .sheet"))
        n0 = int(val("SELECT COUNT(*) FROM money_requests WHERE requester_phone='%s'" % USER))
        np_.fill("#from_phone", OTHER)
        np_.fill("#reqAmount", "120")
        np_.click("#askMoney button[type=submit]", force=True)
        check("and its form works", int(val("SELECT COUNT(*) FROM money_requests WHERE requester_phone='%s'" % USER)) == n0 + 1)

        np_.goto(BASE + "/savings.php")
        gid = val("SELECT id FROM savings_goals WHERE phone='%s' AND status='active' ORDER BY id LIMIT 1" % USER)
        saved0 = float(val("SELECT saved_amount FROM savings_goals WHERE id=%s" % gid))
        np_.click("section[aria-labelledby=g%s] summary" % gid, force=True)
        np_.fill("#amt%s" % gid, "300")
        np_.fill("#pin%s" % gid, PIN)
        np_.click("section[aria-labelledby=g%s] details button[type=submit]" % gid, force=True)
        check("a savings deposit works with scripting off",
              abs(float(val("SELECT saved_amount FROM savings_goals WHERE id=%s" % gid)) - saved0 - 300) < .001)

        # ---- 5. writes -------------------------------------------------
        print("\n=== write flows ===")
        fresh = browser.new_context().new_page()
        fresh.goto(BASE + "/signup.php")
        new_phone = "0161" + str(int(time.time()))[-7:]
        for k, v in {"#Firstname": "Raisa", "#Lastname": "Chowdhury", "#Phone": new_phone,
                     "#Nid": "19" + new_phone[3:] + "001", "#Email": "raisa%s@example.com" % new_phone[-4:],
                     "#Dob": "2001-04-17", "#Password": "raisa2026", "#ConfirmPassword": "raisa2026",
                     "#Pin": "730541", "#PinConfirm": "730541"}.items():
            fresh.fill(k, v)
        fresh.check("input[name=Gender][value=Female]")
        fresh.click("button[type=submit]")
        check("sign up creates the user and the wallet",
              val("SELECT COUNT(*) FROM user u JOIN account a USING(phone) WHERE u.phone='%s'" % new_phone) == "1")
        sign_in(fresh, new_phone, "raisa2026")
        check("the new user can sign in", fresh.url.endswith("account.php"))
        check("and starts on an empty state, not a blank box", "Nothing here yet" in fresh.content())

        page.goto(BASE + "/favourites.php")
        page.click('a[href="#addFav"]')
        page.fill("#saved_phone", new_phone)
        page.fill("#label", "Raisa")
        page.click("#addFav button[type=submit]")
        fid = val("SELECT id FROM favourites WHERE owner_phone='%s' AND saved_phone='%s'" % (USER, new_phone))
        check("a favourite is created", fid is not None)
        page.click("section[aria-label=Raisa] summary")
        page.fill("#lbl%s" % fid, "Raisa (cousin)")
        page.click("section[aria-label=Raisa] details button[type=submit]")
        check("renamed", val("SELECT label FROM favourites WHERE id=%s" % fid) == "Raisa (cousin)")
        page.on("dialog", lambda d: d.accept())
        page.click("section[aria-label='Raisa (cousin)'] button[aria-label^=Remove]")
        page.wait_for_load_state("networkidle")
        check("and removed", val("SELECT COUNT(*) FROM favourites WHERE id=%s" % fid) == "0")

        theirs = val("SELECT trx_id FROM transactions WHERE transaction_from='01913765240' "
                     "AND transaction_to<>'%s' AND status='success' LIMIT 1" % USER)
        r = page.goto(BASE + "/receipt.php?id=" + theirs)
        check("someone else's receipt is a 404, not a peek", r.status == 404 and theirs not in page.content())
        r = fresh.goto(BASE + "/admin_user.php")
        check("a customer cannot open the admin console", fresh.url.endswith("admin_login.php"))

        # ---- 6. screenshots --------------------------------------------
        print("\n=== screenshots ===")
        shots = ["login.php", "account.php", "sendmoney.php", "history.php", "analytics.php", "savings.php",
                 "requests.php", "receipt.php?id=" + val("SELECT trx_id FROM transactions WHERE transaction_to='%s' "
                                                       "AND status='success' ORDER BY id DESC LIMIT 1" % USER),
                 "statement.php", "admin_dashboard.php"]
        n = 0
        for theme in ("light", "dark"):
            for label, vp in (("desktop", {"width": 1280, "height": 860}), ("phone", {"width": 390, "height": 844})):
                ctx = browser.new_context(viewport=vp, reduced_motion="reduce", color_scheme=theme,
                                          device_scale_factor=1 if label == "desktop" else 2)
                ctx.add_init_script("try { localStorage.setItem('tkl-theme', '%s') } catch (e) {}" % theme)
                out_page = ctx.new_page()          # the sign in page, before signing in
                out_page.goto(BASE + "/login.php")
                out_page.screenshot(path=os.path.join(OUT, "login-%s-%s.png" % (label, theme)), full_page=True)
                n += 1
                sign_in(ctx.new_page())
                admin_in(ctx.new_page())
                sp = ctx.new_page()
                for s in shots[1:]:
                    sp.goto(BASE + "/" + s)
                    sp.wait_for_load_state("networkidle")
                    name = re.sub(r"\W+", "_", s.split("?")[0].replace(".php", ""))
                    sp.screenshot(path=os.path.join(OUT, "%s-%s-%s.png" % (name, label, theme)), full_page=True)
                    n += 1
                ctx.close()
        check("%d screenshots written to tests/out" % n, n == len(shots) * 4)

        browser.close()

    print("\n" + "=" * 62)
    print("  %d passed, %d failed" % (len(PASS), len(FAIL)))
    for f in FAIL:
        print("   FAILED: " + f)
    print("=" * 62)
    return 1 if FAIL else 0


if __name__ == "__main__":
    sys.exit(main())
