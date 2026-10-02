<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RenderingTest extends TestCase {
    public function testPostingFormsExposeThreadReplyAndPasscodeControls(): void {
        $newThread = buildPostForm(0);
        self::assertStringContainsString('name="parent" value="0"', $newThread);
        self::assertStringContainsString('value="New thread"', $newThread);
        self::assertStringContainsString('name="file[]"', $newThread);
        self::assertStringContainsString('Passcode users', $newThread);
        self::assertStringContainsString('name="captcha"', $newThread);

        $reply = buildPostForm(42);
        self::assertStringContainsString('name="parent" value="42"', $reply);
        self::assertStringContainsString('value="Reply"', $reply);

        $staff = buildPostForm(0, true);
        self::assertStringContainsString('name="staffpost"', $staff);
    }

    public function testPostMarkupForThreadReplyAndOmittedPosts(): void {
        $op = newPost(0);
        $op['id'] = 42;
        $op['subject'] = '<topic>';
        $op['message'] = '<strong>Welcome</strong>';
        $op['omitted'] = 2;
        $html = buildPost($op);
        self::assertStringContainsString('class="post op" id="post42"', $html);
        self::assertStringContainsString('&lt;topic&gt;', $html);
        self::assertStringContainsString('<strong>Welcome</strong>', $html);
        self::assertStringContainsString('2 posts', $html);
        self::assertStringContainsString('href="res/42.html"', $html);

        $reply = newPost(42);
        $reply['id'] = 43;
        $reply['message'] = 'Reply';
        $html = buildPost($reply, true);
        self::assertStringContainsString('class="post reply" id="post43"', $html);
        self::assertStringContainsString('/unit/res/42.html#43', $html);
    }

    public function testPostFileAndEditMarkup(): void {
        $post = newPost(0);
        $post['id'] = 42;
        $post['file0'] = 'picture.png';
        $post['file0_hex'] = 'imagehash';
        $post['file0_original'] = 'my-picture.png';
        $post['file0_size_formatted'] = '1 KB';
        $post['image0_width'] = 640;
        $post['image0_height'] = 480;
        $post['thumb0'] = 'picture-thumb.png';
        $post['thumb0_width'] = 230;
        $post['thumb0_height'] = 173;
        $html = buildPost($post);
        self::assertStringContainsString('/unit/src/picture.png', $html);
        self::assertStringContainsString('/unit/thumb/picture-thumb.png', $html);
        self::assertStringContainsString('640x480', $html);

        $edit = buildPost($post, true, 'edit');
        self::assertStringContainsString('name="delete-file-mod[]"', $edit);
        self::assertStringContainsString('name="message"', $edit);
    }

    public function testBoardAndThreadPagesExposeNavigationAndDeletion(): void {
        $board = buildPage('<article id="sample">Sample</article>', 0, 2, 1);
        self::assertStringContainsString('href="index.html">Previous', $board);
        self::assertStringContainsString('href="2.html">Next', $board);
        self::assertStringContainsString('name="deletepost"', $board);
        self::assertStringContainsString('id="sample"', $board);

        $thread = buildPage('Thread', 42);
        self::assertStringContainsString('Return</a>', $thread);
        self::assertStringContainsString('name="parent" value="42"', $thread);
    }

    public function testPasscodeScreensAndRelativeTimestamps(): void {
        self::assertStringContainsString('name="passcode"', makePasscodeLoginForm('login'));
        $pass = ['issued' => time() - 60, 'expires' => time() + 3600];
        self::assertStringContainsString('valid passcode', makePasscodeLoginForm('valid', $pass));
        self::assertSame('', makePasscodeLoginForm('unknown'));
        self::assertSame('Never', formatTimestamp(0));
        self::assertSame('Just now', formatTimestamp(time() - 5));
        self::assertSame('2m ago', formatTimestamp(time() - 120));
        self::assertSame('2h ago', formatTimestamp(time() - 7200));
    }

    public function testManagementFormsExposePasscodeActions(): void {
        global $loginStatus;
        $loginStatus = 'admin';
        $html = makePasscodesManager('test-token');
        self::assertStringContainsString('Issue a new passcode', $html);
        self::assertStringContainsString('name="expires"', $html);
        self::assertStringContainsString('name="form_passcode_manage"', $html);
    }
}
