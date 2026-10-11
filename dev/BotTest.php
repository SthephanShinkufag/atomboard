<?php
declare(strict_types=1);
require_once __DIR__ . '/../inc/bot.php';
use PHPUnit\Framework\TestCase;

final class BotTest extends TestCase {
    protected function setUp(): void {
        foreach ([ATOM_DBREPORTS, ATOM_DBPOSTS, ATOM_DBBANS, ATOM_DBMODLOG] as $table) {
            botQuery('DELETE FROM ' . $table);
        }
        botQuery('CREATE TABLE IF NOT EXISTS bot_actions (operation_id varchar(64) PRIMARY KEY, request_hash varchar(64) NOT NULL, result text NOT NULL)');
        botQuery('DELETE FROM bot_actions');
        $_SERVER['REMOTE_ADDR'] = '192.0.2.42';
    }

    private function post(string $ip, int $parent = 0, string $message = 'post'): int {
        $_SERVER['REMOTE_ADDR'] = $ip;
        $post = newPost($parent);
        $post['message'] = $message;
        $post['pass'] = 0;
        $post['password'] = 'private-password';
        return insertPost($post);
    }

    private function command(string $action, int $report): array {
        return ['ip' => '192.0.2.42', 'action' => $action, 'operation_id' => 'test-operation',
            'actor' => '7', 'through_report' => $report, 'expires' => time() + 1209600];
    }

    public function testSnapshotGroupsByPosterAndIncludesContextWithoutPasswords(): void {
        $parent = $this->post('198.51.100.1');
        $reply = $this->post('192.0.2.42', $parent,
            '<a class="refreply" href="/unit/res/' . $parent . '.html#' . $parent . '">&gt;&gt;' . $parent . '</a>');
        insertReport($reply, ATOM_BOARD, '203.0.113.8', 'spam');
        $case = botCaseSnapshot('192.0.2.42');
        self::assertCount(1, $case['reports']);
        self::assertCount(1, $case['posts']);
        self::assertSame($parent, (int)$case['context'][(string)$reply][(string)$parent]['id']);
        self::assertStringNotContainsString('private-password', json_encode($case));
        self::assertSame([], botCaseSnapshot('203.0.113.8')['reports']);
    }

    public function testBoardDoesNotRetainDeletedPostSnapshots(): void {
        $id = $this->post('192.0.2.42');
        insertReport($id, ATOM_BOARD, '203.0.113.8', 'spam');
        self::assertArrayNotHasKey('poster_ip', getAllReports()[0]);
        self::assertArrayNotHasKey('post_snapshot', getAllReports()[0]);
        botQuery('DELETE FROM ' . ATOM_DBPOSTS . ' WHERE id = ?', [$id]);
        $case = botCaseSnapshot('192.0.2.42');
        self::assertSame([], $case['reports']);
        self::assertSame([], $case['posts']);
    }

    public function testDismissClosesOnlyKnownReportsAndIsIdempotent(): void {
        $id = $this->post('192.0.2.42');
        $first = (int)insertReport($id, ATOM_BOARD, '203.0.113.8', 'spam');
        insertReport($id, ATOM_BOARD, '203.0.113.9', 'new');
        $command = $this->command('dismiss', $first);
        $result = botModerate($command);
        self::assertSame('dismissed', $result['status']);
        self::assertSame($result, botModerate($command));
        self::assertCount(1, botCaseSnapshot('192.0.2.42')['reports']);
        self::assertNotNull(getPost($id));
    }

    public function testSubnetBanDeletesExactIpOnlyInReportedThread(): void {
        $thread = $this->post('198.51.100.1');
        $target = $this->post('192.0.2.42', $thread);
        $sameThread = $this->post('192.0.2.42', $thread);
        $neighbor = $this->post('192.0.2.43', $thread);
        $otherThread = $this->post('198.51.100.1');
        $elsewhere = $this->post('192.0.2.42', $otherThread);
        $report = (int)insertReport($target, ATOM_BOARD, '203.0.113.8', 'spam');
        $command = $this->command('ban', $report);
        $result = botModerate($command);
        self::assertSame('192.0.2.0/24', $result['subnet']);
        self::assertEqualsCanonicalizing([$target, $sameThread], $result['deleted']);
        self::assertNull(getPost($target));
        self::assertNotNull(getPost($neighbor));
        self::assertNotNull(getPost($elsewhere));
        self::assertSame($command['expires'], (int)banByIP('192.0.2.99')['expire']);
        self::assertSame($result, botModerate($command));
        self::assertCount(1, getAllBans());
        self::assertArrayNotHasKey('actor', $result);
        self::assertSame('Telegram moderation', botQuery('SELECT username FROM ' . ATOM_DBMODLOG . ' ORDER BY id DESC LIMIT 1')[0]['username']);
        $reordered = array_reverse($command, true);
        $reordered['actor'] = 'another-authorized-moderator';
        self::assertSame($result, botModerate($reordered));
    }

    public function testOperationCannotBeReusedWithDifferentPayload(): void {
        $id = $this->post('192.0.2.42');
        $report = (int)insertReport($id, ATOM_BOARD, '203.0.113.8', 'spam');
        $command = $this->command('dismiss', $report);
        botModerate($command);
        $command['action'] = 'ban';
        $this->expectException(InvalidArgumentException::class);
        botModerate($command);
    }

    public function testModeratorNicknameIsRecordedWithoutNumericIdentity(): void {
        $id = $this->post('192.0.2.42');
        $report = (int)insertReport($id, ATOM_BOARD, '203.0.113.8', 'spam');
        $command = $this->command('dismiss', $report);
        $command['moderator'] = '@test_moderator';
        $result = botModerate($command);
        self::assertSame('@test_moderator', $result['moderator']);
        self::assertArrayNotHasKey('actor', $result);
        self::assertSame('@test_moderator', botQuery('SELECT username FROM ' . ATOM_DBMODLOG . ' ORDER BY id DESC LIMIT 1')[0]['username']);
        self::assertSame($result, botModerate($command));
    }

    public function testOpeningPostDeletionRetainsEngineCascadeAndCountsReplies(): void {
        $thread = $this->post('192.0.2.42');
        $reply = $this->post('198.51.100.1', $thread);
        $report = (int)insertReport($thread, ATOM_BOARD, '203.0.113.8', 'spam');
        $result = botModerate($this->command('ban', $report));
        self::assertEqualsCanonicalizing([$thread, $reply], $result['deleted']);
        self::assertNull(getPost($reply));
    }

    public function testIpv6CannotUseIpv4SubnetAction(): void {
        $command = $this->command('ban', 1);
        $command['ip'] = '2001:db8::1';
        $this->expectException(InvalidArgumentException::class);
        botModerate($command);
    }

    public function testExpiredActionDoesNotCreateBanOrDeletePosts(): void {
        $id = $this->post('192.0.2.42');
        $report = (int)insertReport($id, ATOM_BOARD, '203.0.113.8', 'spam');
        $command = $this->command('ban', $report);
        $command['expires'] = time() - 1;
        try {
            botModerate($command);
            self::fail('Expected invalid expiry');
        } catch (InvalidArgumentException $e) {
            self::assertNotNull(getPost($id));
            self::assertSame([], getAllBans());
        }
    }
}
