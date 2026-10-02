<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FunctionsTest extends TestCase {
    public function testEscapingUntrustedText(): void {
        self::assertSame('&lt;script&gt;&quot;&#039;&amp;', escapeHTML('<script>"\'&'));
    }

    public function testPluralForms(): void {
        self::assertSame('reply', plural('reply', 1, 'replies'));
        self::assertSame('replies', plural('reply', 0, 'replies'));
        self::assertSame('posts', plural('post', 2));
    }

    public function testAllSubstringOffsets(): void {
        self::assertSame([0, 3, 6], strallpos('abcabcabc', 'abc'));
        self::assertSame([3, 6], strallpos('abcabcabc', 'abc', 2));
        self::assertSame([], strallpos('abc', 'z'));
    }

    public function testKnownHslColors(): void {
        self::assertSame('#ff0000', hslToHex(0, 1, 0.5));
        self::assertSame('#00ff00', hslToHex(120, 1, 0.5));
        self::assertSame('#0000ff', hslToHex(240, 1, 0.5));
    }

    public function testNewPostAndThreadIdentity(): void {
        $op = newPost(0);
        $op['id'] = 42;
        $reply = newPost(42);
        $reply['id'] = 43;
        self::assertSame('', $op['message']);
        self::assertSame('1', $op['moderated']);
        self::assertTrue(isOp($op));
        self::assertFalse(isOp($reply));
        self::assertSame(42, getThreadId($op));
        self::assertSame(42, getThreadId($reply));
    }

    public function testThumbnailSizing(): void {
        $post = newPost(0);
        $post['image0_width'] = '100';
        $post['image0_height'] = '80';
        self::assertSame(['100', '80'], getThumbnailDimensions($post));
        $post['image0_width'] = '1000';
        self::assertSame([ATOM_FILE_MAXWOP, ATOM_FILE_MAXHOP], getThumbnailDimensions($post));
        $post['parent'] = 1;
        self::assertSame([ATOM_FILE_MAXW, ATOM_FILE_MAXH], getThumbnailDimensions($post));
    }

    public function testPassTimeRules(): void {
        self::assertTrue(isPassExpired(['expires' => time() - 10]));
        self::assertFalse(isPassExpired(['expires' => time() + 3600]));
        self::assertSame('spam', isPassBlocked(['blocked_till' => time() + 3600, 'blocked_reason' => 'spam']));
        self::assertFalse(isPassBlocked(['blocked_till' => time() - 10, 'blocked_reason' => 'spam']));
    }

    public function testIpv4RangeConversions(): void {
        self::assertSame([ip2long('192.0.2.0'), ip2long('192.0.2.255')], cidr2ip('192.0.2.0/24'));
        self::assertSame([ip2long('192.0.2.7'), ip2long('192.0.2.7')], cidr2ip('192.0.2.7'));
        self::assertSame([0, 0], cidr2ip('not-an-ip'));
        self::assertSame('192.0.2.0/24', ip2cidr(ip2long('192.0.2.0'), ip2long('192.0.2.255')));
        self::assertSame('192.0.2.7', ip2cidr(ip2long('192.0.2.7'), ip2long('192.0.2.7')));
    }

    public function testPageHeaderIncludesBoardIdentity(): void {
        $html = pageHeader();
        self::assertStringContainsString('<title>Test board</title>', $html);
        self::assertStringContainsString('/unit/css/atomboard.css', $html);
    }

    public function testGdThumbnailPreservesImageAspectRatio(): void {
        $original = sys_get_temp_dir() . '/atom-image-' . bin2hex(random_bytes(8)) . '.png';
        $thumbnail = sys_get_temp_dir() . '/atom-thumb-' . bin2hex(random_bytes(8)) . '.png';
        $image = imagecreatetruecolor(100, 50);
        imagepng($image, $original);
        imagedestroy($image);
        try {
            self::assertTrue(createThumbnail($original, $thumbnail, 20, 20));
            self::assertSame([20, 10], array_slice(getimagesize($thumbnail), 0, 2));
        } finally {
            unlink($original);
            unlink($thumbnail);
        }
    }
}
