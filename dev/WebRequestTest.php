<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class WebRequestTest extends TestCase {
    private string $cookies;
    private PDO $webDb;

    protected function setUp(): void {
        $this->cookies = tempnam(sys_get_temp_dir(), 'atom-http-cookies-');
        $this->webDb = new PDO(
            'mysql:host=' . (getenv('TEST_DB_HOST') ?: 'db-test') . ';dbname=atomboard_test',
            'atomboard_test', 'atomboard_test',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    protected function tearDown(): void {
        unlink($this->cookies);
    }

    /** @return array{status:int, body:string, headers:string} */
    private function request(string $path, ?array $fields = null): array {
        $curl = curl_init((getenv('TEST_HTTP_BASE') ?: 'http://web-test/test/') . $path);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEFILE => $this->cookies,
            CURLOPT_COOKIEJAR => $this->cookies,
            CURLOPT_TIMEOUT => 15,
        ];
        if ($fields !== null) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = array_filter($fields, static fn($value): bool => $value instanceof CURLFile) ?
                $fields : http_build_query($fields);
        }
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        if ($response === false) {
            throw new RuntimeException(curl_error($curl));
        }
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        return [
            'status' => $status,
            'headers' => substr($response, 0, $headerSize),
            'body' => substr($response, $headerSize),
        ];
    }

    private function webPost(string $marker): array {
        $query = $this->webDb->prepare('SELECT * FROM test_posts WHERE message LIKE ? ORDER BY id DESC LIMIT 1');
        $query->execute(['%' . $marker . '%']);
        $post = $query->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($post, 'Expected web request to persist a post');
        return $post;
    }

    private function loginWithPasscode(): void {
        $id = insertPass(3600, 'HTTP visitor', 'test', '');
        $response = $this->request('imgboard.php?passcode', ['passcode' => $id]);
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('You have logged in', $response['body']);
        self::assertSame('OK', $this->request('imgboard.php?passcode&check')['body']);
    }

    private function guestPost(array $fields): array {
        self::assertSame(200, $this->request('inc/captcha.php')['status']);
        return $this->request('imgboard.php', array_merge([
            'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
            'message' => '', 'password' => '',
            'captcha' => getenv('TEST_CAPTCHA_TEXT') ?: '48291',
        ], $fields));
    }

    public function testGuestCaptchaPostingAndValidation(): void {
        $answer = getenv('TEST_CAPTCHA_TEXT') ?: '48291';
        $this->request('inc/captcha.php');
        $wrong = $this->request('imgboard.php', [
            'parent' => '0', 'message' => 'Wrong answer', 'captcha' => 'wrong',
        ]);
        self::assertStringContainsString('Incorrect captcha text', $wrong['body']);
        $reused = $this->request('imgboard.php', [
            'parent' => '0', 'message' => 'Reused answer', 'captcha' => $answer,
        ]);
        self::assertStringContainsString('Incorrect captcha text', $reused['body']);

        $marker = 'Guest-thread-' . bin2hex(random_bytes(5));
        $created = $this->guestPost(['message' => $marker, 'password' => 'test']);
        self::assertSame(303, $created['status'], $created['body']);
        $thread = $this->webPost($marker);
        $threadId = (int)$thread['id'];
        self::assertSame(0, (int)$thread['pass']);
        self::assertStringContainsString($marker, $this->request('res/' . $threadId . '.html')['body']);

        $replay = $this->request('imgboard.php', [
            'parent' => '0', 'message' => 'Replay should fail', 'captcha' => $answer,
        ]);
        self::assertStringContainsString('Incorrect captcha text', $replay['body']);

        $replyMarker = 'Guest-reply-' . bin2hex(random_bytes(5));
        $reply = $this->guestPost(['parent' => (string)$threadId, 'message' => $replyMarker]);
        self::assertSame(303, $reply['status'], $reply['body']);
        self::assertSame($threadId, (int)$this->webPost($replyMarker)['parent']);

        $invalidParent = $this->guestPost(['parent' => '99999999', 'message' => 'No thread']);
        self::assertStringContainsString('Invalid parent thread ID', $invalidParent['body']);
        $empty = $this->guestPost(['message' => '']);
        self::assertStringContainsString('Please enter a message', $empty['body']);
        $tooLong = $this->guestPost(['message' => str_repeat('x', ATOM_POSTING_MAXLEN + 1)]);
        self::assertStringContainsString('message is too long', $tooLong['body']);

        $this->webDb->exec('UPDATE test_posts SET locked = 1 WHERE id = ' . $threadId);
        $locked = $this->guestPost(['parent' => (string)$threadId, 'message' => 'Locked reply']);
        self::assertStringContainsString('thread is locked', $locked['body']);

        $report = $this->request('inc/captcha.php');
        self::assertSame(200, $report['status']);
        $reported = $this->request('imgboard.php?report&addreport&json=1', [
            'id' => (string)$threadId, 'reason' => 'Guest report', 'captcha' => $answer,
        ]);
        self::assertSame('ok', json_decode($reported['body'], true)['result']);

        $banId = insertBan($thread['ip'], time() + 3600, 'test ban');
        try {
            $blocked = $this->guestPost(['message' => 'Banned guest']);
            self::assertStringContainsString('has been banned', $blocked['body']);
        } finally {
            deleteBan($banId);
        }
    }

    public function testPasscodeRejectionAndIpUseLimit(): void {
        $unknown = $this->request('imgboard.php?passcode', ['passcode' => 'not-issued']);
        self::assertStringContainsString('not found in database', $unknown['body']);

        $expiredId = insertPass(-10, 'expired', 'test', '');
        $expired = $this->request('imgboard.php?passcode', ['passcode' => $expiredId]);
        self::assertStringContainsString('has expired', $expired['body']);
        self::assertSame(403, $this->request('imgboard.php?passcode&check')['status']);

        $blockedId = insertPass(3600, 'blocked', 'test', '');
        $blockedPass = passByID($blockedId);
        changePass((int)$blockedPass['number'], 'blocked', null, null, time() + 3600, 'test block');
        $blocked = $this->request('imgboard.php?passcode', ['passcode' => $blockedId]);
        self::assertStringContainsString('has been blocked', $blocked['body']);
        self::assertSame(403, $this->request('imgboard.php?passcode&check')['status']);

        $this->loginWithPasscode();
        $active = $this->webDb->query("SELECT number, id FROM pass WHERE meta = 'HTTP visitor' ORDER BY number DESC LIMIT 1")
            ->fetch(PDO::FETCH_ASSOC);
        $query = $this->webDb->prepare('UPDATE pass SET last_used = ?, last_used_ip = ? WHERE id = ?');
        $query->execute([time(), '198.51.100.9', $active['id']]);
        $denied = $this->request('imgboard.php', [
            'parent' => '0', 'message' => 'Second IP should wait',
        ]);
        self::assertStringContainsString('used recently by another IP', $denied['body']);
    }

    public function testVisitorPostingLikesReportsAndDeletes(): void {
        $initial = $this->request('imgboard.php?passcode&check');
        self::assertSame(403, $initial['status']);
        self::assertSame('INVALID', $initial['body']);
        self::assertStringContainsString('name="passcode"', $this->request('imgboard.php?passcode')['body']);

        $captcha = $this->request('inc/captcha.php');
        self::assertSame(200, $captcha['status']);
        self::assertStringContainsString('image/png', $captcha['headers']);
        self::assertStringStartsWith("\x89PNG\r\n\x1a\n", $captcha['body']);

        $missingCaptcha = $this->request('imgboard.php', ['parent' => '0', 'message' => 'Unauthenticated']);
        self::assertStringContainsString('Captcha error', $missingCaptcha['body']);

        $this->loginWithPasscode();
        $marker = 'HTTP-thread-' . bin2hex(random_bytes(5));
        $created = $this->request('imgboard.php', [
            'parent' => '0', 'name' => 'Web Tester', 'subject' => 'Request test',
            'email' => '', 'message' => $marker, 'password' => 'test',
        ]);
        self::assertSame(303, $created['status']);
        $thread = $this->webPost($marker);
        $threadId = (int)$thread['id'];
        self::assertSame(0, (int)$thread['parent']);
        self::assertStringContainsString($marker, $this->request('res/' . $threadId . '.html')['body']);

        $replyMarker = 'HTTP-reply-' . bin2hex(random_bytes(5));
        $replyResponse = $this->request('imgboard.php', [
            'parent' => (string)$threadId, 'name' => '', 'email' => '', 'subject' => '',
            'message' => $replyMarker, 'password' => 'test',
        ]);
        self::assertSame(303, $replyResponse['status']);
        $reply = $this->webPost($replyMarker);
        self::assertSame($threadId, (int)$reply['parent']);
        $replyId = (int)$reply['id'];

        $like = json_decode($this->request('imgboard.php?like=' . $replyId)['body'], true);
        self::assertSame('ok', $like['result']);
        self::assertSame(1, $like['likes']);
        $report = json_decode($this->request('imgboard.php?report&addreport&json=1', [
            'id' => (string)$replyId, 'reason' => 'Test report',
        ])['body'], true);
        self::assertSame('ok', $report['result']);
        $duplicate = json_decode($this->request('imgboard.php?report&addreport&json=1', [
            'id' => (string)$replyId, 'reason' => 'Test report',
        ])['body'], true);
        self::assertSame('alreadysent', $duplicate['result']);

        $wrongPassword = $this->request('imgboard.php?delete', [
            'delete' => (string)$replyId, 'password' => 'wrong',
        ]);
        self::assertStringContainsString('Invalid password', $wrongPassword['body']);
        self::assertStringContainsString($replyMarker, $this->request('res/' . $threadId . '.html')['body']);
        $deleted = $this->request('imgboard.php?delete', [
            'delete' => (string)$replyId, 'password' => 'test',
        ]);
        self::assertStringContainsString('has been deleted', $deleted['body']);
        self::assertStringNotContainsString($replyMarker, $this->request('res/' . $threadId . '.html')['body']);

        self::assertStringContainsString('logged out', $this->request('imgboard.php?passcode&logout')['body']);
        self::assertSame(403, $this->request('imgboard.php?passcode&check')['status']);
    }

    public function testImageUploadThroughMultipartPost(): void {
        $this->loginWithPasscode();
        $imagePath = sys_get_temp_dir() . '/atom-upload-' . bin2hex(random_bytes(5)) . '.png';
        $textPath = sys_get_temp_dir() . '/atom-upload-' . bin2hex(random_bytes(5)) . '.txt';
        $image = imagecreatetruecolor(16, 12);
        imagepng($image, $imagePath);
        imagedestroy($image);
        file_put_contents($textPath, 'Unsupported attachment');
        try {
            $marker = 'HTTP-upload-' . bin2hex(random_bytes(5));
            $response = $this->request('imgboard.php', [
                'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
                'message' => $marker, 'password' => 'test',
                'file[]' => new CURLFile($imagePath, 'image/png', 'test-image.png'),
            ]);
            self::assertSame(303, $response['status'], $response['body']);
            $post = $this->webPost($marker);
            self::assertNotSame('', $post['file0']);
            self::assertNotSame('', $post['thumb0']);
            self::assertSame(200, $this->request('src/' . $post['file0'])['status']);
            self::assertSame(200, $this->request('thumb/' . $post['thumb0'])['status']);

            $duplicate = $this->request('imgboard.php', [
                'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
                'message' => 'Duplicate upload', 'password' => '',
                'file[]' => new CURLFile($imagePath, 'image/png', 'same-image.png'),
            ]);
            self::assertStringContainsString('Duplicate File', $duplicate['body']);

            $unsupported = $this->request('imgboard.php', [
                'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
                'message' => 'Text upload', 'password' => '',
                'file[]' => new CURLFile($textPath, 'text/plain', 'note.txt'),
            ]);
            self::assertStringContainsString('Unsupported file type', $unsupported['body']);
        } finally {
            unlink($imagePath);
            unlink($textPath);
        }
    }

    public function testMultipleFilesVideoAndLocalEmbed(): void {
        $this->loginWithPasscode();
        $files = [];
        foreach ([20, 180] as $shade) {
            $path = sys_get_temp_dir() . '/atom-media-' . bin2hex(random_bytes(6)) . '.png';
            $image = imagecreatetruecolor(32, 24);
            imagefill($image, 0, 0, imagecolorallocate($image, $shade, 30, 90));
            imagepng($image, $path);
            imagedestroy($image);
            $files[] = $path;
        }
        try {
            $marker = 'Multi-file-' . bin2hex(random_bytes(5));
            $multi = $this->request('imgboard.php', [
                'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
                'message' => $marker, 'password' => '',
                'file[0]' => new CURLFile($files[0], 'image/png', 'first.png'),
                'file[1]' => new CURLFile($files[1], 'image/png', 'second.png'),
            ]);
            self::assertSame(303, $multi['status'], $multi['body']);
            $post = $this->webPost($marker);
            self::assertNotSame('', $post['file0']);
            self::assertNotSame('', $post['file1']);
            self::assertNotSame($post['file0_hex'], $post['file1_hex']);

            $videoMarker = 'Video-post-' . bin2hex(random_bytes(5));
            $video = $this->request('imgboard.php', [
                'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
                'message' => $videoMarker, 'password' => '',
                'file[]' => new CURLFile(__DIR__ . '/sample.mp4', 'video/mp4', 'sample.mp4'),
            ]);
            self::assertSame(303, $video['status'], $video['body']);
            $videoPost = $this->webPost($videoMarker);
            self::assertSame(64, (int)$videoPost['image0_width']);
            self::assertSame(48, (int)$videoPost['image0_height']);
            self::assertStringEndsWith('.jpg', $videoPost['thumb0']);
            self::assertSame(200, $this->request('thumb/' . $videoPost['thumb0'])['status']);

            $brokenVideoPath = sys_get_temp_dir() . '/atom-broken-' . bin2hex(random_bytes(5)) . '.mp4';
            file_put_contents($brokenVideoPath, substr(file_get_contents(__DIR__ . '/sample.mp4'), 0, 32));
            try {
                $brokenVideo = $this->request('imgboard.php', [
                    'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
                    'message' => 'Broken-video-' . bin2hex(random_bytes(5)), 'password' => '',
                    'file[]' => new CURLFile($brokenVideoPath, 'video/mp4', 'broken.mp4'),
                ]);
                self::assertStringContainsString('Could not inspect video', $brokenVideo['body']);
                self::assertStringNotContainsString('Fatal error', $brokenVideo['body']);
            } finally {
                unlink($brokenVideoPath);
            }

            $embedMarker = 'Embed-post-' . bin2hex(random_bytes(5));
            $embed = $this->request('imgboard.php', [
                'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
                'message' => $embedMarker, 'password' => '',
                'embed' => 'https://fixture.local/watch/1',
            ]);
            self::assertSame(303, $embed['status'], $embed['body']);
            $embedPost = $this->webPost($embedMarker);
            self::assertSame('fixture.local', $embedPost['file0_hex']);
            self::assertStringContainsString('fixture.local/watch/1', $embedPost['file0']);
            self::assertNotSame('', $embedPost['thumb0']);

            $invalid = $this->request('imgboard.php', [
                'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
                'message' => 'Bad embed', 'password' => '',
                'embed' => 'https://unknown.invalid/watch/1',
            ]);
            self::assertStringContainsString('Invalid embed URL', $invalid['body']);

            $mixed = $this->request('imgboard.php', [
                'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
                'message' => 'Mixed media', 'password' => '',
                'embed' => 'https://fixture.local/watch/1',
                'file[]' => new CURLFile($files[0], 'image/png', 'mixed.png'),
            ]);
            self::assertStringContainsString('at the same time is not supported', $mixed['body']);
        } finally {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    public function testGuestAndPasscodeUploadSizeLimits(): void {
        $path = sys_get_temp_dir() . '/atom-large-' . bin2hex(random_bytes(6)) . '.png';
        $image = imagecreatetruecolor(8, 8);
        imagepng($image, $path);
        imagedestroy($image);
        file_put_contents($path, random_bytes(ATOM_FILE_MAXKB * 1024 + 1), FILE_APPEND);
        try {
            $guest = $this->guestPost([
                'message' => 'Oversized guest upload',
                'file[]' => new CURLFile($path, 'image/png', 'large.png'),
            ]);
            self::assertStringContainsString('larger than 20 MB', $guest['body']);

            $this->loginWithPasscode();
            $marker = 'Large-passcode-file-' . bin2hex(random_bytes(5));
            $allowed = $this->request('imgboard.php', [
                'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
                'message' => $marker, 'password' => '',
                'file[]' => new CURLFile($path, 'image/png', 'large.png'),
            ]);
            self::assertSame(303, $allowed['status'], $allowed['body']);
            self::assertGreaterThan(ATOM_FILE_MAXKB * 1024,
                (int)$this->webPost($marker)['file0_size']);
        } finally {
            unlink($path);
        }
    }

    public function testFormattedPostsAndFormattingLimits(): void {
        $this->loginWithPasscode();
        $anchorMarker = 'Format-anchor-' . bin2hex(random_bytes(5));
        self::assertSame(303, $this->request('imgboard.php', [
            'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
            'message' => $anchorMarker, 'password' => '',
        ])['status']);
        $anchorId = (int)$this->webPost($anchorMarker)['id'];

        $marker = 'Formatting-' . bin2hex(random_bytes(5));
        $formatted = $this->request('imgboard.php', [
            'parent' => (string)$anchorId, 'name' => '', 'email' => '', 'subject' => '',
            'message' => $marker . ' **bold** [code]block[/code] `inline` >>' . $anchorId .
                ' https://example.invalid/path <script>alert(1)</script>',
            'password' => '',
        ]);
        self::assertSame(303, $formatted['status'], $formatted['body']);
        $message = $this->webPost($marker)['message'];
        self::assertStringContainsString('<b>bold</b>', $message);
        self::assertStringContainsString('<pre>block</pre>', $message);
        self::assertStringContainsString('<code>inline</code>', $message);
        self::assertStringContainsString('/test/res/' . $anchorId . '.html#' . $anchorId, $message);
        self::assertStringContainsString('href="https://example.invalid/path"', $message);
        self::assertStringContainsString('&lt;script&gt;', $message);

        $tooManyBlocks = $this->request('imgboard.php', [
            'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
            'message' => str_repeat('[code]x[/code]', ATOM_POSTING_MAXCODE + 2), 'password' => '',
        ]);
        self::assertStringContainsString('Too many code blocks', $tooManyBlocks['body']);
        $tooManyLinks = $this->request('imgboard.php', [
            'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
            'message' => str_repeat('https://example.invalid/a ', ATOM_POSTING_MAXURL + 1),
            'password' => '',
        ]);
        self::assertStringContainsString('Too many external links', $tooManyLinks['body']);
    }

    public function testAdministratorLoginAndCsrfProtection(): void {
        $this->request('imgboard.php?manage');
        $temporary = $this->request('imgboard.php?manage', ['manage_password' => 'test-admin-password']);
        self::assertStringContainsString('Create admin account', $temporary['body']);
        $created = $this->request('imgboard.php?manage', [
            'new_admin_user' => 'http-admin', 'new_admin_pass' => 'test',
        ]);
        self::assertStringContainsString('Admin account created', $created['body']);
        $login = $this->request('imgboard.php?manage', [
            'manage_user' => 'http-admin', 'manage_password' => 'test',
        ]);
        self::assertStringContainsString('Log Out', $login['body']);
        $rejected = $this->request('imgboard.php?manage&staff', [
            'add_user' => 'new-mod', 'add_pass' => 'test', 'add_role' => 'moderator',
        ]);
        self::assertStringContainsString('Invalid CSRF token', $rejected['body']);
        $staffPage = $this->request('imgboard.php?manage&staff');
        self::assertSame(1, preg_match('/name="token" value="([a-f0-9]{64})"/', $staffPage['body'], $matches));
        $accepted = $this->request('imgboard.php?manage&staff', [
            'token' => $matches[1], 'add_user' => 'new-mod',
            'add_pass' => 'test', 'add_role' => 'moderator',
        ]);
        self::assertStringContainsString('Staff account added', $accepted['body']);
        self::assertSame('moderator', $this->webDb->query(
            "SELECT role FROM staff WHERE username = 'new-mod'"
        )->fetchColumn());

        $marker = 'Admin-thread-' . bin2hex(random_bytes(5));
        $postResponse = $this->request('imgboard.php', [
            'parent' => '0', 'name' => 'Administrator', 'email' => '', 'subject' => '',
            'message' => $marker, 'password' => '',
        ]);
        self::assertSame(303, $postResponse['status']);
        $threadId = (int)$this->webPost($marker)['id'];
        $this->webDb->exec('UPDATE test_posts SET moderated = 0 WHERE id = ' . $threadId);
        $approved = $this->request('imgboard.php?manage', [
            'token' => $matches[1], 'approve' => (string)$threadId,
        ]);
        self::assertStringContainsString('has been approved', $approved['body']);
        self::assertSame(1, (int)$this->webDb->query(
            'SELECT moderated FROM test_posts WHERE id = ' . $threadId
        )->fetchColumn());

        $locked = $this->request('imgboard.php?manage', [
            'token' => $matches[1], 'lock' => (string)$threadId, 'setlocked' => '1',
        ]);
        self::assertStringContainsString('has been locked', $locked['body']);
        self::assertSame(1, (int)$this->webDb->query(
            'SELECT locked FROM test_posts WHERE id = ' . $threadId
        )->fetchColumn());

        $stickied = $this->request('imgboard.php?manage', [
            'token' => $matches[1], 'stick' => (string)$threadId, 'setsticky' => '1',
        ]);
        self::assertStringContainsString('has been stickied', $stickied['body']);
        self::assertSame(1, (int)$this->webDb->query(
            'SELECT stickied FROM test_posts WHERE id = ' . $threadId
        )->fetchColumn());
        $endless = $this->request('imgboard.php?manage', [
            'token' => $matches[1], 'endless' => (string)$threadId, 'setendless' => '1',
        ]);
        self::assertStringContainsString('has been made endless', $endless['body']);
        self::assertSame(1, (int)$this->webDb->query(
            'SELECT endless FROM test_posts WHERE id = ' . $threadId
        )->fetchColumn());
        self::assertStringContainsString($marker,
            $this->request('imgboard.php?manage&moderate=' . $threadId)['body']);

        $edited = $this->request('imgboard.php?manage&editpost=' . $threadId, [
            'token' => $matches[1], 'message' => 'Staff edited message',
        ]);
        self::assertStringContainsString('has been changed', $edited['body']);
        self::assertStringContainsString('Staff edited message', (string)$this->webDb->query(
            'SELECT message FROM test_posts WHERE id = ' . $threadId
        )->fetchColumn());

        $imagePath = sys_get_temp_dir() . '/atom-staff-' . bin2hex(random_bytes(5)) . '.png';
        $image = imagecreatetruecolor(19, 13);
        imagefill($image, 0, 0, imagecolorallocate($image, 14, 96, 201));
        imagepng($image, $imagePath);
        imagedestroy($image);
        try {
            $mediaMarker = 'Staff-media-' . bin2hex(random_bytes(5));
            $mediaResponse = $this->request('imgboard.php', [
                'parent' => '0', 'name' => '', 'email' => '', 'subject' => '',
                'message' => $mediaMarker, 'password' => '',
                'file[]' => new CURLFile($imagePath, 'image/png', 'staff.png'),
            ]);
            self::assertSame(303, $mediaResponse['status'], $mediaResponse['body']);
            $mediaId = (int)$this->webPost($mediaMarker)['id'];
            $hide = $this->request('imgboard.php?' . http_build_query([
                'manage' => '', 'delete-files' => $mediaId,
                'delete-file-mod' => [0], 'action' => 'hide',
            ]));
            self::assertStringContainsString('have been changed', $hide['body']);
            self::assertSame('spoiler.png', $this->webDb->query(
                'SELECT thumb0 FROM test_posts WHERE id = ' . $mediaId
            )->fetchColumn());
            $deleteImage = $this->request('imgboard.php?' . http_build_query([
                'manage' => '', 'delete-files' => $mediaId,
                'delete-file-mod' => [0], 'action' => 'delete',
            ]));
            self::assertStringContainsString('have been deleted', $deleteImage['body']);
            self::assertSame('', $this->webDb->query(
                'SELECT file0 FROM test_posts WHERE id = ' . $mediaId
            )->fetchColumn());
            $deletePost = $this->request('imgboard.php?manage', [
                'token' => $matches[1], 'delete' => (string)$mediaId,
            ]);
            self::assertStringContainsString('has been deleted', $deletePost['body']);
            self::assertSame(0, (int)$this->webDb->query(
                'SELECT COUNT(*) FROM test_posts WHERE id = ' . $mediaId
            )->fetchColumn());
        } finally {
            unlink($imagePath);
        }

        insertReport($threadId, 'test', '198.51.100.2', 'review');
        $closed = $this->request('imgboard.php?manage', [
            'token' => $matches[1], 'deletereports' => (string)$threadId,
        ]);
        self::assertStringContainsString('Related reports are closed', $closed['body']);
        self::assertSame(0, (int)$this->webDb->query(
            'SELECT COUNT(*) FROM reports WHERE board = "test" AND postnum = ' . $threadId
        )->fetchColumn());

        $issued = $this->request('imgboard.php?manage&issuepasscode', [
            'token' => $matches[1], 'expires' => '3600', 'meta' => 'admin-issued',
            'meta_admin' => 'test', 'name' => 'Test Holder',
        ]);
        self::assertStringContainsString('New passcode issued', $issued['body']);
        $passNumber = (int)$this->webDb->query(
            "SELECT number FROM pass WHERE meta = 'admin-issued' ORDER BY number DESC LIMIT 1"
        )->fetchColumn();
        self::assertGreaterThan(0, $passNumber);
        $changed = $this->request('imgboard.php?manage&managepasscode', [
            'token' => $matches[1], 'id' => (string)$passNumber, 'meta' => 'admin-issued',
            'block_till' => date('Y-m-d\TH:i', time() + 3600), 'block_reason' => 'review',
        ]);
        self::assertStringContainsString('has been changed', $changed['body']);
        self::assertSame('review', $this->webDb->query(
            'SELECT blocked_reason FROM pass WHERE number = ' . $passNumber
        )->fetchColumn());

        $ban = $this->request('imgboard.php?manage&bans', [
            'token' => $matches[1], 'ip' => '198.51.100.8',
            'expire' => '3600', 'reason' => 'test moderation',
        ]);
        self::assertStringContainsString('Ban record added', $ban['body']);
        self::assertSame(1, (int)$this->webDb->query('SELECT COUNT(*) FROM bans')->fetchColumn());

        self::assertStringContainsString('Ban an IP address',
            $this->request('imgboard.php?manage&bans')['body']);
        self::assertStringContainsString('Issue a new passcode',
            $this->request('imgboard.php?manage&passcodes=new')['body']);
        self::assertStringContainsString('Account settings',
            $this->request('imgboard.php?manage&account')['body']);
        self::assertStringContainsString('198.51.100.8',
            $this->request('imgboard.php?manage&ipinfo=198.51.100.8')['body']);
        self::assertStringContainsString('Ban record added',
            $this->request('imgboard.php?manage&modlog')['body']);
        self::assertStringContainsString('The board has been rebuilt',
            $this->request('imgboard.php?manage&rebuildall')['body']);

        $janitor = $this->request('imgboard.php?manage&staff', [
            'token' => $matches[1], 'add_user' => 'new-janitor',
            'add_pass' => 'test', 'add_role' => 'janitor',
        ]);
        self::assertStringContainsString('Staff account added', $janitor['body']);

        $adminCookies = $this->cookies;
        $roleCookies = tempnam(sys_get_temp_dir(), 'atom-role-cookies-');
        try {
            $this->cookies = $roleCookies;
            $moderator = $this->request('imgboard.php?manage', [
                'manage_user' => 'new-mod', 'manage_password' => 'test',
            ]);
            self::assertStringContainsString('Bans</a>', $moderator['body']);
            self::assertStringNotContainsString('Staff</a>', $moderator['body']);
            self::assertStringNotContainsString('Add new staff member',
                $this->request('imgboard.php?manage&staff')['body']);
            self::assertStringContainsString('Ban an IP address',
                $this->request('imgboard.php?manage&bans')['body']);

            $this->request('imgboard.php?manage&logout');
            $janitorLogin = $this->request('imgboard.php?manage', [
                'manage_user' => 'new-janitor', 'manage_password' => 'test',
            ]);
            self::assertStringNotContainsString('Bans</a>', $janitorLogin['body']);
            self::assertStringNotContainsString('Ban an IP address',
                $this->request('imgboard.php?manage&bans')['body']);
            self::assertStringNotContainsString('Issue a new passcode',
                $this->request('imgboard.php?manage&passcodes=new')['body']);
        } finally {
            $this->cookies = $adminCookies;
            unlink($roleCookies);
        }
    }
}
