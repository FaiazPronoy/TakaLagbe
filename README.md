# TakaLagbe!

A mobile money wallet in PHP and MySQL: send money, pay bills and tuition,
recharge, save towards goals, split bills, with a back office for the operator.
Built for CSE370 Database Systems at BRAC University, then rebuilt.

**Live demo:** _link goes here once it is deployed_ ·
**Releases:** [v1.0, the coursework, and v2.0, the rebuild](https://github.com/FaiazPronoy/TakaLagbe/releases)

## Sign in to the demo

| Role | Where | Sign in with |
|---|---|---|
| Customer | the home page | `01711482093` / `demo1234`, transaction PIN `482915` |
| A second customer | the home page | `01819337406` / `demo1234`, same PIN |
| Admin | `/admin_login.php` | `admin` / `admin123` |

The data is made up: twelve people with six months of transfers, bills,
recharges and savings. No real money moves.

## v1 and v2

| | v1.0 (Spring 2024 coursework) | v2.0 (the rebuild) |
|---|---|---|
| Passwords | stored and compared as plain text | bcrypt, plus a separate hashed six digit PIN for payments |
| SQL | form input pasted into query strings; the sign in form was injectable | prepared statements everywhere |
| Moving money | two separate UPDATEs, no transaction | one transaction, both accounts locked in a fixed order |
| Forms | no CSRF protection | CSRF token on every form, one time token on money forms |
| Schema | 13 tables, four copies of the bill table | 18 tables, foreign keys, CHECK constraints, two triggers |
| Front end | Bootstrap from a CDN, emoji icons | hand written CSS with design tokens, light and dark themes, SVG icons, works with JavaScript off |
| Tests | none | an HTTP suite that checks the database after each step, and browser checks (crawl, WCAG contrast in both themes, keyboard, no-JS, 390px) |

The v1 source is in the v1.0 release. The code for v2 is in
[`app/`](app/), and its README explains how the money is
kept consistent, the data model, and how to run it and the tests.

## Bugs found and fixed while rebuilding

- The committed test suite was failing: the money forms had gained a one time token the tests never sent.
- Paying with JavaScript off was impossible, because the PIN boxes had no name; every try used up a PIN attempt.
- Keyboard users could not send money: Enter on the hold-to-send button did nothing.
- Several cash outs at the same moment could take an agent's float below zero.
- The same bill or semester fee could be paid more than once by requests arriving together.
- Recharges, bills and fees could be charged twice by resending the same form.
- Sending any amount to anyone settled whatever request was attached to the link.
- Declined cash outs, bills, recharges, payments and fees were not recorded, although the help page said they were.
- A statement for a past period closed on today's balance, and listed declined attempts as money out.
- Money put into savings counted against the daily sending limit, and as spending in Insights.
- Nonsense dates in the address bar printed 1970 on the statement.
- The statement ran one query per row.
- Signing out was a plain link that any other site could trigger.
- The schema file, the tests and the config folder could be downloaded from the web server.
- White initials on the brass and gold avatar colours failed contrast.
- One of the original clip art images still carried a stock photo watermark, and the support page listed a real company's helpline.

## Contributors

- Faiaz Abrar Zaman Pronoy
- Sinthia Khandoker
- Jahirul Islam Zishan

## Licence

All rights reserved, see [LICENSE](LICENSE).
