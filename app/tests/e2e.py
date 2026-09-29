#!/usr/bin/env python3
"""
End to end test suite for TakaLagbe!

Drives the real application over HTTP the way a browser would: it signs
users up, signs them in, moves money, pays bills and then checks the
database to prove the balances and the ledger agree.

This wipes every table before it runs, so it will not touch the database the
app normally uses. Point TKL_DB at a scratch copy first:

    mysql -u root -e "CREATE DATABASE takalagbe_test"
    mysql -u root takalagbe_test < takalagbe.sql
    set TKL_DB=takalagbe_test && python tests/e2e.py

Run:  python3 tests/e2e.py            (server must be on 127.0.0.1:8080)
"""
import csv, os, re, shutil, subprocess, sys, threading
from datetime import date, timedelta
import requests

BASE = os.environ.get("TKL_BASE", "http://127.0.0.1:8080")
DB_USER = os.environ.get("TKL_DB_USER", "root")
DB_PASS = os.environ.get("TKL_DB_PASS", "")
DB_NAME = os.environ.get("TKL_DB", "takalagbe_test")
PASS, FAIL = [], []

TODAY = date.today()
LAST_MONTH = (TODAY.replace(day=1) - timedelta(days=1)).strftime("%B %Y")
SEMESTER = "Fall %d" % TODAY.year


def _client():
    """
    Finds the command line client. XAMPP ships `mysql`; a Linux box usually
    has `mariadb`. Set TKL_MYSQL to point at it directly if it is not on PATH,
    for example  set TKL_MYSQL=C:\\xampp\\mysql\\bin\\mysql.exe
    """
    override = os.environ.get("TKL_MYSQL")
    if override:
        return override
    for name in ("mysql", "mariadb"):
        found = shutil.which(name)
        if found:
            return found
    for guess in (r"C:\xampp\mysql\bin\mysql.exe",
                  r"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"):
        if os.path.isfile(guess):
            return guess
    sys.exit(
        "Could not find the mysql client.\n"
        "Add it to PATH, or set TKL_MYSQL to its full path, for example:\n"
        r"    set TKL_MYSQL=C:\xampp\mysql\bin\mysql.exe"
    )


MYSQL = _client()


def _args(extra):
    args = [MYSQL, "-u", DB_USER]
    if DB_PASS:
        args.append("-p" + DB_PASS)
    return args + extra


def db(sql):
    out = subprocess.run(_args(["-N", "-B", DB_NAME, "-e", sql]),
                         capture_output=True, text=True)
    if out.returncode:
        raise RuntimeError(out.stderr)
    return [line.split("\t") for line in out.stdout.strip().splitlines() if line]


def run_sql(sql):
    subprocess.run(_args([DB_NAME, "-e", sql]), check=True)


def val(sql):
    r = db(sql)
    return r[0][0] if r else None


def check(name, cond, extra=""):
    (PASS if cond else FAIL).append(name)
    print(("  ok   " if cond else "  FAIL ") + name + (("  -> " + str(extra)) if (extra and not cond) else ""))


class User:
    def __init__(self, phone, pw="secret123", pin="481596"):
        self.s = requests.Session()
        self.phone, self.pw, self.pin = phone, pw, pin

    def tokens(self, path):
        """The CSRF token, plus the one shot token a money form carries."""
        html = self.s.get(BASE + "/" + path).text
        csrf = re.search(r'name="csrf" value="([a-f0-9]+)"', html)
        once = re.search(r'name="once" value="([a-f0-9]+)"', html)
        return (csrf.group(1) if csrf else ""), (once.group(1) if once else None)

    def form(self, path, data):
        """Loads the form and fills in its tokens, without sending it yet."""
        csrf, once = self.tokens(path)
        data = dict(data, csrf=csrf)
        if once:
            data["once"] = once
        return data

    def post(self, path, data):
        return self.s.post(BASE + "/" + path, data=self.form(path, data), allow_redirects=True)

    def get(self, path):
        return self.s.get(BASE + "/" + path)


def signup(u, first, last, nid, email, dob="1999-05-05", gender="Male"):
    return u.post("signup.php", {
        "Phone": u.phone, "Firstname": first, "Lastname": last, "Nid": nid,
        "Email": email, "Dob": dob, "Gender": gender,
        "Password": u.pw, "ConfirmPassword": u.pw,
        "Pin": u.pin, "PinConfirm": u.pin})


def login(u):
    return u.post("login.php", {"Phone": u.phone, "Password": u.pw})


def reset_db():
    if DB_NAME == "takalagbe":
        sys.exit("Refusing to wipe 'takalagbe'. Set TKL_DB to a scratch database.")
    run_sql("SET FOREIGN_KEY_CHECKS=0;"
            "TRUNCATE transactions; TRUNCATE notifications;"
                    "TRUNCATE login_attempts; TRUNCATE audit_log;"
                    "TRUNCATE favourites; TRUNCATE savings_goals;"
                    "TRUNCATE money_requests; TRUNCATE bill_payments;"
                    "TRUNCATE education_fee; TRUNCATE mobile_recharge;"
                    "TRUNCATE support_messages;"
                    "DELETE FROM account; DELETE FROM user;"
                    "UPDATE organizations SET total_received_amount=0;"
                    "UPDATE institutes SET total_received_amount=0;"
                    "UPDATE merchants SET total_received_amount=0;"
                    "UPDATE agent_details SET cash_in_amount=50000;"
                    "SET FOREIGN_KEY_CHECKS=1;")


# balances set directly in SQL by the suite, so the ledger cannot explain them
SEEDED = {}


def seed_balance(phone, amount, limit=None):
    sql = "UPDATE account SET balance=%s WHERE phone='%s';" % (amount, phone)
    if limit is not None:
        sql += "UPDATE user SET daily_limit=%s WHERE phone='%s';" % (limit, phone)
    run_sql(sql)
    SEEDED[phone] = SEEDED.get(phone, 0) + float(amount) - _ledger_balance(phone)


def _ledger_balance(phone):
    total = 0.0
    for frm, to, amt, fee, cb in db("""SELECT transaction_from, transaction_to, amount, fee,
                                              cashback FROM transactions WHERE status='success'"""):
        if frm == phone:
            total += -float(amt) - float(fee) + float(cb)
        if to == phone:
            total += float(amt)
    return round(total, 2)


# =====================================================================
#  The suite
# =====================================================================

def main():
    print("\n=== resetting the database ===")
    reset_db()

    # ---------------------------------------------------------- signup
    print("\n=== sign up and sign in ===")
    a = User("01711111111", pw="faiaz2026", pin="481596")
    b = User("01822222222", pw="rafid2026", pin="334455")
    c = User("01911111111", pw="nabila2026", pin="907152")

    signup(a, "Faiaz", "Abrar", "1234567890", "faiaz@test.com")
    signup(b, "Rafid", "Hasan", "1234567891", "rafid@test.com")
    signup(c, "Nabila", "Rahman", "1234567892", "nabila@test.com")
    check("three users created", val("SELECT COUNT(*) FROM user") == "3")
    check("every user has a wallet", val("SELECT COUNT(*) FROM account") == "3")
    check("passwords are hashed, never stored raw",
          val("SELECT password_hash FROM user WHERE phone='01711111111'").startswith("$2y$"))
    check("PINs are hashed too",
          val("SELECT pin_hash FROM user WHERE phone='01711111111'").startswith("$2y$"))

    r = signup(User("01711111111"), "Copy", "Cat", "9999999999", "copy@test.com")
    check("duplicate number is refused", "already registered" in r.text)

    u = User("01712345678")
    r = signup(u, "Too", "Young", "1234567899", "young@test.com", dob="2015-01-01")
    check("under 18 is refused", "at least 18" in r.text)

    r = login(a)
    check("sign in works", r.url.endswith("account.php"))
    bad = User("01711111111", pw="wrongpass")
    r = login(bad)
    check("wrong password is refused", "Wrong number or password" in r.text)

    login(b); login(c)

    # ------------------------------------------------------- add money
    print("\n=== putting money in ===")
    a.post("add_money.php", {"bank": "BRAC Bank", "amount": "20000", "pin": a.pin})
    b.post("add_money.php", {"bank": "City Bank", "amount": "8000", "pin": b.pin})
    c.post("cash_in.php", {"agent_id": "AG1001", "cash_in_amount": "5000", "pin": c.pin})
    check("A has 20000", val("SELECT balance FROM account WHERE phone='01711111111'") == "20000.00")
    check("B has 8000", val("SELECT balance FROM account WHERE phone='01822222222'") == "8000.00")
    check("C has 5000 after cash in", val("SELECT balance FROM account WHERE phone='01911111111'") == "5000.00")
    check("agent float went up",
          val("SELECT cash_in_amount FROM agent_details WHERE agent_id='AG1001'") == "55000.00")

    r = a.post("add_money.php", {"bank": "BRAC Bank", "amount": "99999", "pin": a.pin})
    check("add money cap is enforced", "cannot add more than" in r.text)
    r = a.post("add_money.php", {"bank": "BRAC Bank", "amount": "500", "pin": "000000"})
    check("add money needs the right PIN", "Wrong transaction PIN" in r.text)

    # ----------------------------------------------------- send money
    print("\n=== sending money ===")
    r = a.post("sendmoney.php", {"receiver_phone": "01822222222", "amount": "1500.75",
                                 "note": "lunch", "pin": a.pin, "save_favourite": "1"})
    check("transfer lands on a receipt", "receipt.php" in r.url)
    check("sender debited exactly",
          val("SELECT balance FROM account WHERE phone='01711111111'") == "18499.25")
    check("receiver credited exactly",
          val("SELECT balance FROM account WHERE phone='01822222222'") == "9500.75")
    check("balance after is recorded on the ledger row",
          val("SELECT sender_balance_after FROM transactions WHERE transaction_type='Send Money'") == "18499.25")
    check("favourite saved with the transfer",
          val("SELECT saved_phone FROM favourites WHERE owner_phone='01711111111'") == "01822222222")
    check("receiver was notified",
          val("SELECT COUNT(*) FROM notifications WHERE user_phone='01822222222' "
              "AND title='Money received'") == "1")
    check("sender got their own confirmation",
          val("SELECT COUNT(*) FROM notifications WHERE user_phone='01711111111' "
              "AND title='Send Money'") == "1")

    r = a.post("sendmoney.php", {"receiver_phone": "01711111111", "amount": "100", "pin": a.pin})
    check("cannot send to yourself", "your own account" in r.text)
    r = a.post("sendmoney.php", {"receiver_phone": "01700000000", "amount": "100", "pin": a.pin})
    check("unregistered number is refused", "not registered" in r.text)
    r = c.post("sendmoney.php", {"receiver_phone": "01711111111", "amount": "999999", "pin": c.pin})
    check("per transfer cap is enforced", "cannot send more than" in r.text)
    r = c.post("sendmoney.php", {"receiver_phone": "01711111111", "amount": "24000", "pin": c.pin})
    check("cannot send money you do not have", "Insufficient balance" in r.text)
    check("the refusal is written to the ledger",
          int(val("SELECT COUNT(*) FROM transactions WHERE status='failed'")) >= 1)

    # -------------------------------------------------------- charges
    print("\n=== charges and cashback ===")
    before = float(val("SELECT balance FROM account WHERE phone='01711111111'"))
    a.post("cash_out.php", {"agent_id": "AG1002", "cash_out_amount": "2000", "pin": a.pin})
    after = float(val("SELECT balance FROM account WHERE phone='01711111111'"))
    check("cash out takes amount plus 1.85 percent",
          abs((before - after) - (2000 + 37.00)) < 0.001, before - after)

    before = after
    a.post("mobilerecharge.php", {"rechargePhoneNumber": "01712345678",
                                  "rechargeAmount": "500", "connection": "Prepaid", "pin": a.pin})
    after = float(val("SELECT balance FROM account WHERE phone='01711111111'"))
    check("recharge gives one percent back", abs((before - after) - (500 - 5)) < 0.001, before - after)
    check("operator detected from the prefix",
          val("SELECT operator FROM mobile_recharge") == "Grameenphone")

    before = after
    a.post("payment.php", {"merchant_id": "M103", "amount": "1000",
                           "reference": "table 7", "pin": a.pin})
    after = float(val("SELECT balance FROM account WHERE phone='01711111111'"))
    check("merchant cashback applied", abs((before - after) - (1000 - 20)) < 0.001, before - after)
    check("merchant total updated",
          val("SELECT total_received_amount FROM merchants WHERE merchant_id='M103'") == "1000.00")

    before = after
    a.post("electricity_bills.php", {"organization_id": "444", "consumer_no": "MTR-9981",
                                     "amount": "1200", "month": LAST_MONTH, "pin": a.pin})
    after = float(val("SELECT balance FROM account WHERE phone='01711111111'"))
    check("bill takes a flat 5 charge", abs((before - after) - 1205) < 0.001, before - after)
    r = a.post("electricity_bills.php", {"organization_id": "444", "consumer_no": "MTR-9981",
                                         "amount": "1200", "month": LAST_MONTH, "pin": a.pin})
    check("the same bill cannot be paid twice", "already paid" in r.text)

    a.post("education_fee.php", {"institute_id": "555", "student_id": "21301234",
                                 "student_name": "faiaz abrar", "semester": SEMESTER,
                                 "amount": "3000", "pin": a.pin})
    check("institute total updated",
          val("SELECT total_received_amount FROM institutes WHERE institute_id='555'") == "3000.00")
    r = a.post("education_fee.php", {"institute_id": "555", "student_id": "21301234",
                                     "student_name": "faiaz abrar", "semester": SEMESTER,
                                     "amount": "3000", "pin": a.pin})
    check("the same semester cannot be paid twice", "already been paid" in r.text)

    # --------------------------------------------------------- limits
    print("\n=== limits and velocity ===")
    d = User("01313131313", pw="limit2026", pin="112233")
    signup(d, "Limit", "Test", "1234567893", "limit@test.com"); login(d)
    seed_balance("01313131313", 50000, limit=3000)
    d.post("sendmoney.php", {"receiver_phone": "01711111111", "amount": "2500", "pin": d.pin})
    r = d.post("sendmoney.php", {"receiver_phone": "01711111111", "amount": "1000", "pin": d.pin})
    check("daily limit blocks the transfer that would cross it", "daily limit" in r.text)
    check("the balance was not touched by the blocked transfer",
          val("SELECT balance FROM account WHERE phone='01313131313'") == "47500.00")

    # ------------------------------------------------------- savings
    print("\n=== savings goals ===")
    a.post("savings.php", {"action": "create", "title": "New laptop",
                           "target_amount": "60000",
                           "deadline": (date.today() + timedelta(days=90)).isoformat()})
    gid = val("SELECT id FROM savings_goals WHERE phone='01711111111'")
    before = float(val("SELECT balance FROM account WHERE phone='01711111111'"))
    a.post("savings.php", {"action": "deposit", "goal_id": gid, "amount": "2000", "pin": a.pin})
    check("saving moves money out of the spendable balance",
          abs(float(val("SELECT balance FROM account WHERE phone='01711111111'")) - (before - 2000)) < 0.001)
    check("the goal holds it", val("SELECT saved_amount FROM savings_goals WHERE id=%s" % gid) == "2000.00")
    r = a.post("savings.php", {"action": "withdraw", "goal_id": gid, "amount": "9999", "pin": a.pin})
    check("cannot take out more than the goal holds", "only holds" in r.text)
    a.post("savings.php", {"action": "close", "goal_id": gid})
    check("closing a goal returns the money",
          abs(float(val("SELECT balance FROM account WHERE phone='01711111111'")) - before) < 0.001)

    # ---------------------------------------------------- requests
    print("\n=== requests and split bill ===")
    a.post("requests.php", {"action": "request", "from_phone": "01822222222",
                            "amount": "300", "note": "chai"})
    rid = val("SELECT id FROM money_requests WHERE requester_phone='01711111111'")
    r = b.post("sendmoney.php", {"receiver_phone": "01711111111", "amount": "300",
                                 "pin": b.pin, "request_id": rid})
    check("paying a request closes it",
          val("SELECT status FROM money_requests WHERE id=%s" % rid) == "paid")

    a.post("requests.php", {"action": "split", "total": "1200",
                            "people": "01822222222, 01911111111", "split_note": "dinner"})
    shares = db("SELECT amount FROM money_requests WHERE split_group IS NOT NULL")
    check("split creates one request per person", len(shares) == 2, shares)
    check("each share is the total divided three ways",
          all(s[0] == "400.00" for s in shares), shares)

    r = a.post("requests.php", {"action": "request", "from_phone": "01711111111", "amount": "50"})
    check("cannot request money from yourself", "from yourself" in r.text)

    # ------------------------------------------------------ security
    print("\n=== security ===")
    fresh = requests.Session()
    r = fresh.get(BASE + "/account.php", allow_redirects=False)
    check("dashboard needs a session", r.status_code == 302 and "login" in r.headers.get("Location", ""))
    r = fresh.get(BASE + "/admin_dashboard.php", allow_redirects=False)
    check("admin needs a session", r.status_code == 302 and "admin_login" in r.headers.get("Location", ""))

    r = a.s.post(BASE + "/sendmoney.php", data={"receiver_phone": "01822222222",
                 "amount": "100", "pin": a.pin, "csrf": "forged"})
    check("a forged CSRF token is rejected", r.status_code == 400)

    injected = "01822222222' OR '1'='1"
    r = a.post("sendmoney.php", {"receiver_phone": injected, "amount": "10", "pin": a.pin})
    check("SQL injection in a form field does nothing", "valid 11 digit" in r.text)
    r = a.get("history.php?q=" + requests.utils.quote("' OR 1=1 -- "))
    check("SQL injection in the search box does nothing", r.status_code == 200 and "Fatal" not in r.text)
    check("the user table is still there", val("SELECT COUNT(*) FROM user") is not None)

    xss = '<script>alert(1)</script>'
    a.post("sendmoney.php", {"receiver_phone": "01822222222", "amount": "50",
                             "note": xss, "pin": a.pin})
    r = a.get("history.php")
    check("a script tag in a note is escaped, not executed", "<script>alert(1)</script>" not in r.text)

    victim = User("01414141414", pw="victim2026", pin="778899")
    signup(victim, "Pin", "Victim", "1234567894", "victim@test.com"); login(victim)
    seed_balance("01414141414", 5000)
    for _ in range(5):
        victim.post("sendmoney.php", {"receiver_phone": "01711111111",
                                      "amount": "100", "pin": "000000"})
    check("five wrong PINs freeze the account",
          val("SELECT status FROM user WHERE phone='01414141414'") == "frozen")

    locked = User("01515151515", pw="whatever")
    for _ in range(6):
        login(locked)
    r = login(locked)
    check("repeated bad sign ins lock the number", "Too many failed attempts" in r.text)

    stranger_trx = val("SELECT trx_id FROM transactions "
                       "WHERE transaction_from='01822222222' AND transaction_to='01711111111' "
                       "AND status='success' LIMIT 1")
    r = c.get("receipt.php?id=" + str(stranger_trx))
    check("you cannot open someone else's receipt",
          r.status_code == 404 and "Nothing here" in r.text, r.status_code)
    r = a.get("receipt.php?id=" + str(stranger_trx))
    check("but a party to it can", r.status_code == 200 and stranger_trx in r.text)

    # --------------------------------------------------- concurrency
    print("\n=== two transfers at the same moment ===")
    seed_balance("01911111111", 1000, limit=100000)
    results = []
    def race():
        s = User("01911111111", pw="nabila2026", pin="907152")
        login(s)
        rr = s.post("sendmoney.php", {"receiver_phone": "01711111111",
                                      "amount": "800", "pin": s.pin})
        results.append("receipt.php" in rr.url)
    threads = [threading.Thread(target=race) for _ in range(2)]
    for t in threads: t.start()
    for t in threads: t.join()
    bal = float(val("SELECT balance FROM account WHERE phone='01911111111'"))
    check("only one of two racing transfers went through", sum(results) == 1, results)
    check("the balance never went negative", bal >= 0, bal)
    check("the balance is exactly right", abs(bal - 200) < 0.001, bal)

    # ------------------------------------------------------ sanity
    print("\n=== the data still makes sense ===")
    check("no balance is negative anywhere",
          val("SELECT COUNT(*) FROM account WHERE balance < 0") == "0")
    check("every transaction id is unique",
          val("SELECT COUNT(*) FROM transactions") ==
          val("SELECT COUNT(DISTINCT trx_id) FROM transactions"))

    # --------------------------------------------------------- admin
    print("\n=== admin console ===")
    ad = User("admin")
    r = ad.post("admin_login.php", {"username": "admin", "password": "admin123"})
    check("admin can sign in", r.url.endswith("admin_dashboard.php"))
    r = ad.post("admin_user.php", {"action": "unfreeze", "phone": "01414141414"})
    check("admin can reopen a frozen account",
          val("SELECT status FROM user WHERE phone='01414141414'") == "active")
    check("the unfreeze is in the audit log",
          int(val("SELECT COUNT(*) FROM audit_log WHERE action='user_unfreeze'")) >= 1)

    victim2 = User("01414141414", pw="victim2026", pin="778899")
    check("the reopened user can sign in again", login(victim2).url.endswith("account.php"))

    # --------------------------------------------------------- pages
    print("\n=== every page renders ===")
    pages = ["account.php", "sendmoney.php", "add_money.php", "cash_in.php", "cash_out.php",
             "mobilerecharge.php", "payment.php", "paybill.php", "electricity_bills.php",
             "gas_bills.php", "water_bills.php", "internet_bills.php", "education_fee.php",
             "history.php", "analytics.php", "statement.php", "savings.php", "requests.php",
             "favourites.php", "notifications.php", "profile.php", "qr.php", "checkbalance.php",
             "supports.php", "currency.php", "about.php", "organizations_list.php",
             "institutes_list.php"]
    broken = []
    for p in pages:
        rr = a.get(p)
        if rr.status_code != 200 or any(x in rr.text for x in
                ("Fatal error", "Parse error", "Warning:", "Deprecated:", "Notice:")):
            broken.append(p)
    check("all %d customer pages render cleanly" % len(pages), not broken, broken)

    admin_pages = ["admin_dashboard.php", "admin_user.php", "admin_merchant.php",
                   "admin_education.php", "admin_supports.php", "admin_issue.php"]
    broken = []
    for p in admin_pages:
        rr = ad.get(p)
        if rr.status_code != 200 or any(x in rr.text for x in
                ("Fatal error", "Parse error", "Warning:", "Deprecated:", "Notice:")):
            broken.append(p)
    check("all %d admin pages render cleanly" % len(admin_pages), not broken, broken)

    r = a.get("statement.php?format=csv")
    check("the CSV statement downloads", "text/csv" in r.headers.get("Content-Type", ""))
    check("the CSV has a row per transaction", r.text.count("TKL") >= 5)

    r = a.get("qr.php")
    check("the QR code is drawn inline as SVG", "<svg" in r.text and "viewBox" in r.text)


    # ------------------------------------------------- regressions
    print("\n=== awkward inputs ===")

    ad0 = User("adminprobe")
    r = ad0.post("admin_login.php",
                 {"username": "administrator-with-a-long-name", "password": "nope"})
    check("a very long admin username is refused cleanly",
          r.status_code == 200 and "Wrong username" in r.text, r.status_code)

    long_title = "Emergency fund for the whole family"
    a.post("savings.php", {"action": "create", "title": long_title,
                           "target_amount": "100000"})
    gid = val("SELECT id FROM savings_goals ORDER BY id DESC LIMIT 1")
    before = float(val("SELECT balance FROM account WHERE phone='01711111111'"))
    a.post("savings.php", {"action": "deposit", "goal_id": gid,
                           "amount": "500", "pin": a.pin})
    after = float(val("SELECT balance FROM account WHERE phone='01711111111'"))
    check("a goal with a long title still takes a deposit",
          abs((before - after) - 500) < 0.001, before - after)
    check("the long counterparty is stored in full",
          (val("SELECT transaction_to FROM transactions "
               "WHERE transaction_type='Savings' ORDER BY id DESC LIMIT 1") or "")
          == "Savings: " + long_title)
    a.post("savings.php", {"action": "close", "goal_id": gid})

    a.post("payment.php", {"merchant_id": "M104", "amount": "300", "pin": a.pin})
    check("a merchant payment keeps the merchant's category",
          val("SELECT category FROM transactions WHERE transaction_to='Star Kabab' "
              "ORDER BY id DESC LIMIT 1") == "Food")

    # a recharge should never show up in the recipient's own history
    before_rows = int(val("SELECT COUNT(*) FROM transactions "
                          "WHERE transaction_to='01822222222'"))
    a.post("mobilerecharge.php", {"rechargePhoneNumber": "01822222222",
                                  "rechargeAmount": "100",
                                  "connection": "Prepaid", "pin": a.pin})
    after_rows = int(val("SELECT COUNT(*) FROM transactions "
                         "WHERE transaction_to='01822222222'"))
    check("recharging a registered number does not touch their ledger",
          after_rows == before_rows, (before_rows, after_rows))
    recharge_trx = val("SELECT trx_id FROM transactions "
                       "WHERE transaction_type='Mobile Recharge' ORDER BY id DESC LIMIT 1")
    r = b.get("receipt.php?id=" + str(recharge_trx))
    check("and they cannot open the payer's recharge receipt", r.status_code == 404,
          r.status_code)

    # a refusal should still leave a record
    failed_before = int(val("SELECT COUNT(*) FROM transactions WHERE status='failed'"))
    c.post("sendmoney.php", {"receiver_phone": "01822222222", "amount": "24000",
                             "pin": c.pin})
    failed_after = int(val("SELECT COUNT(*) FROM transactions WHERE status='failed'"))
    check("a refused transfer is written to the ledger too",
          failed_after == failed_before + 1, (failed_before, failed_after))

    # refused attempts should not count towards the velocity brake
    poor = User("01313000001", pw="poor2026", pin="221133")
    signup(poor, "Velocity", "Probe", "1234567895", "velocity@test.com")
    login(poor)
    seed_balance("01313000001", 5000)
    for _ in range(11):
        poor.post("sendmoney.php", {"receiver_phone": "01711111111",
                                    "amount": "999999", "pin": poor.pin})
    r = poor.post("sendmoney.php", {"receiver_phone": "01711111111",
                                    "amount": "100", "pin": poor.pin})
    check("failed attempts do not trip the velocity brake",
          "receipt.php" in r.url, r.url)

    # a long name plus a long note used to overflow the notification column
    longname = User("01313000002", pw="longer2026", pin="553311")
    signup(longname, "Mohammad" * 5, "Chowdhury" * 5, "1234567896", "long@test.com")
    login(longname)
    seed_balance("01313000002", 3000)
    r = longname.post("sendmoney.php", {
        "receiver_phone": "01711111111", "amount": "150",
        "note": "x" * 120, "pin": longname.pin, "save_favourite": "1"})
    check("a very long name and note still complete the transfer",
          "receipt.php" in r.url, r.url)
    check("and the favourite is saved without overflowing",
          val("SELECT COUNT(*) FROM favourites WHERE owner_phone='01313000002'") == "1")

    # the schema itself
    fks = int(val("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS "
                  "WHERE CONSTRAINT_SCHEMA='" + DB_NAME + "' AND CONSTRAINT_TYPE='FOREIGN KEY'"))
    check("the schema declares its relationships as real foreign keys", fks >= 10, fks)

    # the running totals are kept by a trigger, not by PHP
    org_total = float(val("SELECT total_received_amount FROM organizations "
                          "WHERE organization_id='444'") or 0)
    paid = float(val("SELECT COALESCE(SUM(amount),0) FROM bill_payments "
                     "WHERE organization_id='444'") or 0)
    check("the trigger keeps the biller total in step with its payments",
          abs(org_total - paid) < 0.001, (org_total, paid))

    # ------------------------------------------- found in the v2 audit
    print("\n=== v2 audit regressions ===")

    def balance(u):
        return float(val("SELECT balance FROM account WHERE phone='%s'" % u.phone))

    def failed_count():
        return int(val("SELECT COUNT(*) FROM transactions WHERE status='failed'"))

    seed_balance(a.phone, 30000, limit=100000)
    seed_balance(b.phone, 30000, limit=100000)
    seed_balance(c.phone, 30000, limit=100000)

    before = balance(a)
    data = a.form("mobilerecharge.php", {"rechargePhoneNumber": a.phone, "rechargeAmount": "99",
                                         "connection": "Prepaid", "pin": a.pin})
    a.s.post(BASE + "/mobilerecharge.php", data=data)
    r = a.s.post(BASE + "/mobilerecharge.php", data=data)
    check("a replayed recharge is refused", r.status_code == 409, r.status_code)
    check("and the recharge was only charged once", abs((before - balance(a)) - 98.01) < 0.001,
          before - balance(a))

    for page, fields in [("gas_bills.php", {"organization_id": "111", "consumer_no": "TGL-1001",
                                            "amount": "1080", "month": LAST_MONTH}),
                         ("education_fee.php", {"institute_id": "556", "student_id": "2106001",
                                                "student_name": "rafid hasan", "semester": SEMESTER,
                                                "amount": "5000"})]:
        data = b.form(page, dict(fields, pin=b.pin))
        b.s.post(BASE + "/" + page, data=data)
        r = b.s.post(BASE + "/" + page, data=data)
        check("a replayed %s post is refused" % page, r.status_code == 409, r.status_code)

    a.post("requests.php", {"action": "request", "from_phone": b.phone, "amount": "350",
                            "note": "kacchi at Sultans Dine"})
    rid = val("SELECT id FROM money_requests WHERE requester_phone='%s' AND amount=350 "
              "ORDER BY id DESC LIMIT 1" % a.phone)
    r = b.post("sendmoney.php?request=%s" % rid, {"receiver_phone": c.phone, "amount": "10",
                                                   "pin": b.pin, "request_id": rid})
    check("sending ten taka to someone else does not settle a request",
          val("SELECT status FROM money_requests WHERE id=%s" % rid) == "pending")
    check("and the page says what the request was for", "That request is for" in r.text)
    b.post("sendmoney.php?request=%s" % rid, {"receiver_phone": a.phone, "amount": "350",
                                              "pin": b.pin, "request_id": rid})
    check("paying the right person the right amount settles it",
          val("SELECT status FROM money_requests WHERE id=%s" % rid) == "paid")

    n = failed_count()
    poor = User("01313000003", pw="poorer2026", pin="662244")
    signup(poor, "Declined", "Probe", "1234567897", "declined@test.com")
    login(poor)
    seed_balance(poor.phone, 500)
    poor.post("cash_out.php", {"agent_id": "AG1001", "cash_out_amount": "2000", "pin": poor.pin})
    poor.post("gas_bills.php", {"organization_id": "112", "consumer_no": "KGD-2002", "amount": "900",
                                "month": LAST_MONTH, "pin": poor.pin})
    poor.post("payment.php", {"merchant_id": "M102", "amount": "800", "pin": poor.pin})
    check("declined cash out, bill and payment are all on the ledger", failed_count() == n + 3,
          (n, failed_count()))

    yesterday = (TODAY - timedelta(days=1)).isoformat()
    r = a.get("statement.php?format=csv&from=%s&to=%s"
              % ((TODAY - timedelta(days=30)).isoformat(), yesterday))
    closing = [row for row in csv.reader(r.text.splitlines()) if row[:1] == ["Closing balance"]]
    check("a statement that ends yesterday closes on yesterday's balance, not today's",
          closing == [["Closing balance", "0.00"]], closing)

    c.post("sendmoney.php", {"receiver_phone": a.phone, "amount": "25000", "pin": c.pin})
    c.post("sendmoney.php", {"receiver_phone": a.phone, "amount": "9000", "pin": c.pin})
    r = c.get("statement.php?format=csv")
    check("declined attempts are left out of the statement", ",failed" not in r.text)

    r1 = a.get("statement.php?from=zzz&to=yyy")
    r2 = a.get("history.php?from=not-a-date&to=2026-13-45")
    check("nonsense dates fall back to a sensible period",
          r1.status_code == 200 and r2.status_code == 200 and "1970" not in r1.text)

    saver = User("01313000004", pw="saver2026", pin="771155")
    signup(saver, "Saving", "Probe", "1234567898", "saver@test.com")
    login(saver)
    seed_balance(saver.phone, 10000, limit=5000)
    saver.post("savings.php", {"action": "create", "title": "Eid", "target_amount": "8000"})
    gid = val("SELECT id FROM savings_goals WHERE phone='%s'" % saver.phone)
    saver.post("savings.php", {"action": "deposit", "goal_id": gid, "amount": "4500", "pin": saver.pin})
    r = saver.post("sendmoney.php", {"receiver_phone": a.phone, "amount": "1000", "pin": saver.pin})
    check("money moved into savings does not use up the daily sending limit",
          "receipt.php" in r.url, r.url)

    def questions():
        return int(val("SELECT VARIABLE_VALUE FROM information_schema.GLOBAL_STATUS "
                       "WHERE VARIABLE_NAME='QUESTIONS'"))
    q0 = questions(); a.get("statement.php?from=2000-01-01&to=2000-01-31"); empty = questions() - q0
    q0 = questions(); a.get("statement.php"); full = questions() - q0
    rows = int(val("SELECT COUNT(*) FROM transactions WHERE (transaction_from='%s' OR transaction_to='%s') "
                   "AND status='success'" % (a.phone, a.phone)))
    check("a statement costs the same number of queries whether it has 0 or %d rows" % rows,
          full - empty <= 1, (empty, full))

    out = subprocess.run(_args([DB_NAME, "-e",
                                "UPDATE agent_details SET cash_in_amount=-1 WHERE agent_id='AG1001'"]),
                         capture_output=True, text=True)
    check("the database itself refuses a negative agent float", out.returncode != 0)

    # three people at the same counter, the same bill, the same fee, all at once
    def race(jobs):
        gate = threading.Barrier(len(jobs))
        def go(u, page, data):
            gate.wait()
            u.s.post(BASE + "/" + page, data=data)
        ts = [threading.Thread(target=go, args=j) for j in jobs]
        [t.start() for t in ts]
        [t.join() for t in ts]

    run_sql("UPDATE agent_details SET cash_in_amount=1000 WHERE agent_id='AG1003'")
    race([(u, "cash_out.php", u.form("cash_out.php", {"agent_id": "AG1003", "cash_out_amount": "800",
                                                        "pin": u.pin})) for u in (a, b, c)])
    check("three cash outs at once cannot take more than the agent holds",
          val("SELECT cash_in_amount FROM agent_details WHERE agent_id='AG1003'") == "200.00",
          val("SELECT cash_in_amount FROM agent_details WHERE agent_id='AG1003'"))

    race([(u, "water_bills.php", u.form("water_bills.php", {
        "organization_id": "224", "consumer_no": "KWASA-7007", "amount": "600",
        "month": LAST_MONTH, "pin": u.pin})) for u in (a, b, c)])
    check("three people paying one bill at once pay it once",
          val("SELECT COUNT(*) FROM bill_payments WHERE consumer_no='KWASA-7007'") == "1")

    # a race does not always lose, so run it a few times
    for sid in ("NIC-7781", "NIC-7782", "NIC-7783"):
        race([(u, "education_fee.php", u.form("education_fee.php", {
            "institute_id": "558", "student_id": sid, "student_name": "sadia islam",
            "semester": SEMESTER, "amount": "1000", "pin": u.pin})) for u in (a, b, c)])
    check("three people paying one semester fee at once pay it once",
          val("SELECT COUNT(*) FROM education_fee WHERE student_id LIKE 'NIC-778%'") == "3")
    check("and nobody lost money to the ones that were refused",
          int(val("SELECT COUNT(*) FROM transactions WHERE status='success' "
                  "AND note LIKE '%NIC-778%'")) == 3)

    if "Apache" in fresh.get(BASE + "/login.php").headers.get("Server", ""):
        r = fresh.get(BASE + "/takalagbe.sql")
        check("the schema file cannot be downloaded", r.status_code == 404, r.status_code)
        r = fresh.get(BASE + "/config/config.php")
        check("the config folder is not reachable", r.status_code == 404, r.status_code)
        r = fresh.get(BASE + "/no-such-page.php")
        check("an unknown address gets the app's own 404",
              r.status_code == 404 and "Nothing here" in r.text, r.status_code)
    else:
        print("  skip file access checks: .htaccess needs Apache")

    a.get("logout.php")
    check("opening logout.php as a link does not sign you out",
          a.s.get(BASE + "/account.php", allow_redirects=False).status_code == 200)
    csrf, _ = a.tokens("account.php")
    a.s.post(BASE + "/logout.php", data={"csrf": csrf})
    check("the sign out button does",
          a.s.get(BASE + "/account.php", allow_redirects=False).status_code == 302)

    r = saver.get("analytics.php")
    check("insights do not count money moved into savings as spending",
          '<ul class="legend">' in r.text
          and "Savings" not in r.text.split('<ul class="legend">')[1].split("</ul>")[0])

    # the pages have to work with scripting switched off
    money_pages = ["sendmoney.php", "add_money.php", "cash_in.php", "cash_out.php", "mobilerecharge.php",
                   "payment.php", "electricity_bills.php", "education_fee.php", "checkbalance.php"]
    no_pin = [p for p in money_pages
              if not re.search(r'<input class="pin" type="password" id="\w+" name="pin"', b.get(p).text)
              or 'type="hidden" name="pin"' in b.get(p).text]
    check("every money form posts its PIN from a real field, not one filled in by script", not no_pin, no_pin)

    dialogs = {"requests.php": ["askMoney", "splitBill"], "savings.php": ["newGoal"], "favourites.php": ["addFav"]}
    missing = [(p, d) for p, ids in dialogs.items() for d in ids
               if 'href="#%s"' % d not in b.get(p).text or '<dialog id="%s"' % d not in b.get(p).text]
    check("every dialog opens from a plain link to its id", not missing, missing)
    check("no inline handler is needed to open anything",
          not any("openModal(" in b.get(p).text for p in dialogs))

    check("history rows are links to their receipts", 'href="receipt.php?id=' in b.get("history.php").text)
    check("the support page does not list a real company's helpline", "16247" not in b.get("supports.php").text)

    # -------------------------------------------------------- result
    print("\n" + "=" * 62)
    print("  %d passed, %d failed" % (len(PASS), len(FAIL)))
    if FAIL:
        for f in FAIL:
            print("   FAILED: " + f)
    print("=" * 62)
    return 1 if FAIL else 0


if __name__ == "__main__":
    sys.exit(main())
