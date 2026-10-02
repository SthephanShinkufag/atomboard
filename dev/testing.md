# Characterization tests

Run `make test` for the PDO/MySQL path, `make test-mysqli` for the mysqli path,
`make test-pgsql` for PostgreSQL/PDO database and rendering tests, and
`make test-js` for the board's browser script.
The MySQL commands start a fresh MariaDB and Apache board under the separate
`atomboard-tests` Compose project, run PHPUnit, write coverage reports, and
remove the test containers and database. The PostgreSQL command uses an
isolated PostgreSQL container without Apache. The Node command runs locally.
The live `/test/` board and its volumes are untouched. The test Apache board
is reachable only inside the test network.

CLI reports are generated at `dev/coverage/pdo/html/index.html` and
`dev/coverage/mysqli/html/index.html`; Clover XML is beside each report. Combined
CLI and web line coverage is in each mode's `http-summary.txt` and
`http-summary.json`. The suite uses PHPUnit 11 and PCOV in dedicated PHP 8.3
test images. The PHP suites require Docker and Make; the browser script tests
also require Node.js.

The tests characterize current behavior. Unit tests cover text, IP, passcode
time rules, thumbnail creation, and HTML rendering. Database integration tests
cover posts and replies, moderation state, likes, reports, passcodes, bans,
staff accounts, thread updates, generated pages, and cached IP lookups. Web
request tests cover CAPTCHA image delivery, passcode login and logout, thread
and reply POSTs, image and video uploads, local embed responses, file size
limits, formatted messages and limits, likes, reports, deletion passwords,
administrator setup, staff editing and deletion, thread controls, management
screens, and CSRF checks. The same assertions run against both MySQL database
implementations. PostgreSQL uses the same database, function, and rendering
tests in a separate container. Node tests exercise theme persistence, saved
posting passwords, passcode display, like updates, and media expansion in
`js/atomboard.js`.

The HTTP test image replaces only the CAPTCHA answer selection with the fixed
number `48291`. It still renders an image and stores the same answer in the
session. It also supplies a local embed response. Both the production and test
images include video thumbnail tools. The repository source and production image retain the random CAPTCHA
and ordinary embed configuration. This lets tests submit correct, wrong, and
reused answers without OCR or a public embed API.

## Coverage baseline (2026-10-02)

| Run | Tests | Assertions | CLI coverage | CLI + web coverage |
| --- | ---: | ---: | ---: | ---: |
| PDO/MySQL | 37 | 303 | 955 / 3,448 (27.70%) | 2,187 / 3,448 (63.43%) |
| mysqli | 37 | 303 | 947 / 3,398 (27.87%) | 2,173 / 3,398 (63.95%) |
| PDO/PostgreSQL | 29 | 136 | 952 / 3,448 (27.61%) | No HTTP run |

The browser script has 5 passing Node tests. It has no JavaScript line
coverage report.

PHPUnit's HTML and Clover reports show CLI coverage only. The combined metric
unions its covered executable lines with PCOV hits saved by Apache for each
request. The coverage denominator includes both database files. In each run,
the inactive database file is 0% by design. The combined figures cover
615/925 lines in `imgboard.php`, 107/126 in `inc/captcha.php`, 265/439 in
shared functions, and 769/984 in HTML rendering. The active PDO implementation
has 431/477 lines covered; mysqli has 417/443. Bundled reCAPTCHA code and
static username lists are excluded from the denominator.

The HTTP suite covers guest posting with a correct, wrong, and reused CAPTCHA
answer; invalid parent, empty or oversized message, locked thread, unsupported
and duplicate uploads; expired and blocked passcodes; a second-IP passcode
limit; and administrator approval, bans, passcode management, and staff role
controls. Media and staff action tests assert both the HTTP response and the
stored post state. Video tests cover a valid MP4 and a truncated MP4 that must
return a posting error without a PHP fatal error.

Remaining gaps before a broad refactor include premoderation with its
configuration enabled, external GeoIP and IP reputation services, less common
error paths in media processing, PostgreSQL web requests, and the bundled
Dollchan extension's browser interactions. JavaScript tests use a simulated
DOM; real browser layout and cross-browser behavior are not covered. The
combined PHP coverage metric includes the inactive database implementation,
which accounts for much of the remaining denominator.

During the admin passcode request test, an existing form mismatch surfaced:
with the default `ATOM_UNIQUEID = false`, the issue form omits `name`, while the
database rejects a null `name`. The characterization test supplies a name to
exercise the successful management path; the engine remains unchanged.
