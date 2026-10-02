<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase {
    protected function setUp(): void {
        global $dbh, $mysqli;
        foreach ([ATOM_DBLIKES, ATOM_DBREPORTS, ATOM_DBPASS, ATOM_DBPOSTS,
            ATOM_DBBANS, ATOM_DBSTAFF, ATOM_DBMODLOG, ATOM_DBIPLOOKUPS] as $table) {
            if (ATOM_DBMODE === 'pdo') {
                $dbh->exec('DELETE FROM ' . $table);
            } else {
                $mysqli->query('DELETE FROM ' . $table);
            }
        }
        $_SERVER['REMOTE_ADDR'] = '192.0.2.10';
    }

    private function post(int $parent = 0, string $message = 'body'): int {
        $post = newPost($parent);
        $post['message'] = $message;
        $post['name'] = 'Tester';
        $post['pass'] = 0;
        return insertPost($post);
    }

    public function testThreadAndRepliesCanBeRead(): void {
        $thread = $this->post(0, 'first');
        $reply = $this->post($thread, 'second');
        self::assertSame('first', getPost($thread)['message']);
        self::assertSame($thread, (int)getPost($reply)['parent']);
        self::assertCount(1, getThreads());
        self::assertSame(1, getThreadsCount());
        self::assertCount(2, getThreadPosts($thread));
        self::assertSame(1, getThreadPostsCount($thread));
        self::assertNull(getPost(-1));
    }

    public function testModerationHidesPendingPostUntilApproval(): void {
        $thread = $this->post();
        $reply = $this->post($thread);
        $this->query('UPDATE ' . ATOM_DBPOSTS . ' SET moderated = 0 WHERE id = ' . $reply);
        self::assertCount(1, getThreadPosts($thread));
        self::assertCount(2, getThreadPosts($thread, false));
        approvePost($reply);
        self::assertCount(2, getThreadPosts($thread));
    }

    public function testSearchPostsByIpAndImageHash(): void {
        $post = newPost(0);
        $post['file0_hex'] = 'known-image';
        $post['pass'] = 0;
        $id = insertPost($post);
        self::assertSame($id, (int)getPostsByImageHex('known-image')['id']);
        self::assertNull(getPostsByImageHex('missing'));
        self::assertCount(1, getPostsByIP('192.0.2.0/24'));
        self::assertCount(0, getPostsByIP('198.51.100.0/24'));
    }

    public function testLikesTogglePerIpAndTrackCount(): void {
        $id = $this->post();
        self::assertSame([true, 1], toggleLike($id, '192.0.2.10'));
        self::assertSame([true, 2], toggleLike($id, '192.0.2.11'));
        self::assertSame(2, (int)getPost($id)['likes']);
        self::assertSame([false, 1], toggleLike($id, '192.0.2.10'));
        self::assertCount(1, likesByPostID($id));
    }

    public function testReportsAreUniquePerReporterAndPost(): void {
        $id = $this->post();
        $report = insertReport($id, ATOM_BOARD, '192.0.2.20', 'spam');
        self::assertIsInt($report);
        self::assertSame('exists', insertReport($id, ATOM_BOARD, '192.0.2.20', 'spam'));
        self::assertIsInt(insertReport($id, ATOM_BOARD, '192.0.2.21', 'spam'));
        deleteReports($id);
        self::assertIsInt(insertReport($id, ATOM_BOARD, '192.0.2.20', 'spam'));
    }

    public function testPasscodeIssueUseBlockAndDelete(): void {
        $id = insertPass(3600, 'user note', 'admin note', 'Tester');
        self::assertSame(64, strlen($id));
        $pass = passByID($id);
        self::assertSame('Tester', $pass['name']);
        self::assertFalse(isPassExpired($pass));
        $number = (int)$pass['number'];
        usePass($id, '192.0.2.30');
        self::assertSame('192.0.2.30', passByNum($number)['last_used_ip']);
        changePass($number, 'changed', null, null, time() + 3600, 'abuse');
        self::assertSame('abuse', isPassBlocked(passByID($id)));
        self::assertCount(1, getAllPasscodes());
        deletePass($id);
        self::assertNull(passByID($id));
    }

    public function testThreadStateAndDeletion(): void {
        $thread = $this->post();
        $reply = $this->post($thread);
        toggleStickyThread($thread, 1);
        toggleLockThread($thread, 1);
        toggleEndlessThread($thread, 1);
        $op = getPost($thread);
        self::assertSame(1, (int)$op['stickied']);
        self::assertSame(1, (int)$op['locked']);
        self::assertSame(1, (int)$op['endless']);
        deletePost($thread);
        self::assertNull(getPost($thread));
        self::assertNull(getPost($reply));
    }

    public function testEditingAndBumpingAThread(): void {
        $thread = $this->post();
        self::assertTrue(isThreadExists($thread));
        self::assertFalse(isThreadExists(-1));
        editPostMessage($thread, '<b>Edited</b>');
        self::assertSame('<b>Edited</b>', getPost($thread)['message']);
        $this->query('UPDATE ' . ATOM_DBPOSTS . ' SET bumped = 1 WHERE id = ' . $thread);
        $reply = newPost($thread);
        $reply['email'] = 'sage';
        updateThreadPosts($thread, $reply);
        self::assertSame(1, (int)getPost($thread)['bumped']);
        $reply['email'] = '';
        updateThreadPosts($thread, $reply);
        self::assertGreaterThan(1, (int)getPost($thread)['bumped']);
    }

    public function testBanLifecycle(): void {
        $id = insertBan('192.0.2.0/24', time() + 3600, 'abuse');
        self::assertSame('abuse', banByIP('192.0.2.10')['reason']);
        self::assertNull(banByIP('198.51.100.10'));
        self::assertCount(1, getAllBans());
        self::assertNotNull(banByID($id));
        deleteBan($id);
        self::assertNull(banByID($id));
        insertBan('198.51.100.1', time() - 10, 'expired');
        clearExpiredBans();
        self::assertSame([], getAllBans());
    }

    public function testStaffCredentialsCanBeChanged(): void {
        self::assertTrue(addStaffMember(' Moderator ', 'first-secret', 'moderator'));
        $staff = getStaffMember('Moderator');
        self::assertSame('moderator', $staff['role']);
        self::assertTrue(password_verify('first-secret', $staff['password_hash']));
        changeStaffMember('Moderator', 'second-secret');
        self::assertTrue(password_verify('second-secret', getStaffMember('Moderator')['password_hash']));
        updateStaffLogin('Moderator');
        $members = getAllStaffMembers();
        self::assertCount(1, $members);
        self::assertGreaterThan(0, (int)$members[0]['last_login']);
        deleteStaffMember((int)$members[0]['id']);
        self::assertNull(getStaffMember('Moderator'));
    }

    public function testIpLookupCacheUpdatesAndExpires(): void {
        self::assertNull(lookupByIP('192.0.2.77'));
        storeLookupResult('192.0.2.77', 0, 0, 1, 0, 0, 'isp', 'Example ISP');
        self::assertSame(1, (int)lookupByIP('192.0.2.77')['proxy']);
        self::assertFalse(isDirtyIP('192.0.2.77'));
        storeLookupResult('192.0.2.77', 1, 0, 1, 0, 0, 'isp', 'Example ISP');
        self::assertTrue(isDirtyIP('192.0.2.77'));
        $this->query('UPDATE ' . ATOM_DBIPLOOKUPS . ' SET last_updated = 1 WHERE ip = \'192.0.2.77\'');
        deleteOldLookups();
        self::assertNull(lookupByIP('192.0.2.77'));
        self::assertSame('ANON', getCountryCode('192.0.2.77', null));
    }

    public function testModerationLogPrivacyAndDateFiltering(): void {
        $_SESSION['atom_user'] = 'test-moderator';
        try {
            modLog('public action', '0', 'Green');
            modLog('private action', '1', 'Red');
        } finally {
            unset($_SESSION['atom_user']);
        }
        self::assertCount(2, getModLogRecords());
        $public = getModLogRecords(0, 0, true);
        self::assertCount(1, $public);
        self::assertSame('public action', $public[0]['action']);
        self::assertSame('test-moderator', $public[0]['username']);
        self::assertCount(2, getModLogRecords(time() - 5, time() + 5));
    }

    public function testGeneratedBoardAndThreadContainPersistedPosts(): void {
        $thread = $this->post(0, 'Thread marker');
        $this->post($thread, 'Reply marker');
        rebuildThread($thread);
        $threadPage = file_get_contents('res/' . $thread . '.html');
        self::assertStringContainsString('Thread marker', $threadPage);
        self::assertStringContainsString('Reply marker', $threadPage);
        self::assertStringContainsString('Thread marker', file_get_contents(ATOM_INDEX));
        self::assertFileExists('catalog.html');
        unlink('res/' . $thread . '.html');
        unlink(ATOM_INDEX);
        unlink('catalog.html');
    }

    private function query(string $sql): void {
        global $dbh, $mysqli;
        if (ATOM_DBMODE === 'pdo') {
            $dbh->exec($sql);
        } else {
            $mysqli->query($sql);
        }
    }
}
