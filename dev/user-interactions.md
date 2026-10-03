# User interactions to cover with tests

The local `/test/` board represents one board. The [Dollchan `/ukr/` board](https://dollchan.net/ukr/) shows the same core experience with real threads, media, navigation, and passcode users. Test the PHP pages without assuming the optional Dollchan browser extension is installed; cover extension behavior separately if it is in scope.

## Visitor

| Journey | User actions | Observable result to cover |
| --- | --- | --- |
| Discover content | Open the board index, move between index pages, open the catalog, choose a thread | Correct thread and reply previews; thread page and post anchors open |
| Read a thread | Follow a post reference, move to top or bottom, return to the board | Navigation stays on the intended board and post |
| Create a thread | Enter a message, optional name, email, subject, file, embed, and deletion password; complete CAPTCHA; submit | New thread appears on the index, catalog, and its own page |
| Reply | Open a thread, enter text or media, complete CAPTCHA, submit | Reply appears in the thread and index preview; bump behavior follows the email and thread limits |
| Compose rich content | Use quotes, post references, formatting, links, and supported file types | Stored message renders safely; valid references and thumbnails work |
| React | Like a post, then unlike it | Count and selected state change, including after page reload |
| Report | Open the report form for a post, give a reason, complete CAPTCHA | Report is recorded once and visible to moderators |
| Delete own post | Select the post and enter its deletion password | Post disappears; generated pages and files are updated |
| Preferences | Switch between Dark and Light themes | Choice persists on subsequent pages |

For posting, cover invalid CAPTCHA, missing required content, oversized or unsupported files, invalid parent thread, a locked thread, and posting limits. For deletion and reporting, cover invalid credentials or repeated submissions.

## Passcode holder

| Journey | User actions | Observable result to cover |
| --- | --- | --- |
| Sign in | Open **Passcode**, enter an active code | Success and expiry are shown; the session is recognized on board pages |
| Post with code | Create a thread or reply | CAPTCHA is skipped; the passcode file size limit applies; post stores passcode association |
| Return visit | Reload or open another board page during the valid session | Passcode status remains active |
| Sign out | Use **Log Out** | Session and passcode cookie are cleared; CAPTCHA returns |
| Rejected code | Enter unknown, expired, or blocked code | Clear reason is shown; posting receives no passcode privileges |
| IP use limit | Use the same code from a second IP within the configured interval | Second IP is rejected until the limit expires |

The local test code is `atomboard-local-test-passcode` by default. Change it with `TEST_PASSCODE` in `.env` before the first start. Tests for expired, blocked, and second-IP states should create separate passcodes so the active fixture remains usable.

## Staff

| Role | User actions to cover |
| --- | --- |
| Administrator | Sign in, create an account, issue a passcode, change or block a passcode, create staff accounts, rebuild board pages |
| Moderator | Review reports, approve or delete posts, edit content, lock/sticky/endless a thread, manage bans and passcodes within role permissions |
| Janitor | Review reports and posts, approve or delete within role permissions |

Test role boundaries: unauthenticated users cannot reach staff actions, and each staff role sees only its allowed controls. Include the post and report state after moderation, not only the success message.

## Fixture coverage

The current seed contains one text thread and eight replies. For interaction tests, add isolated fixtures with an image, video or embed, formatted text and a `>>post` reference, a deletion password, a report, multiple poster IPs, and separate active/expired/blocked passcodes. Keep those fixtures independent so one test does not change another test's starting state.
