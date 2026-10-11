<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class BotHttpTest extends TestCase {
    private function call(string $method, string $query = '', ?array $command = null, bool $authorized = true, bool $internal = true): array {
        $base = $internal ? (getenv('TEST_BOT_HTTP_BASE') ?: 'http://web-test:8081/test/') : (getenv('TEST_HTTP_BASE') ?: 'http://web-test/test/');
        $tokenFile = getenv('TEST_BOT_API_TOKEN_FILE');
        self::assertNotFalse($tokenFile, 'Test API token file must be configured');
        $token = trim((string)file_get_contents($tokenFile));
        self::assertSame(64, strlen($token), 'Test API token must be generated at runtime');
        $curl = curl_init($base . 'inc/bot-api.php' . $query);
        curl_setopt_array($curl, [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15, CURLOPT_HTTPHEADER => array_filter([
                'Content-Type: application/json', $authorized ? 'Authorization: Bearer ' . $token : null,
                'Bot-Internal-Request: 1', 'X-Forwarded-For: 127.0.0.1'
            ])]);
        if ($command !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($command));
        }
        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        return [$status, json_decode($body, true)];
    }

    public function testPublicListenerRejectsEvenAuthenticatedRequestsAndSpoofedHeaders(): void {
        self::assertSame(404, $this->call('GET', '?op=reports', null, true, false)[0]);
        self::assertSame(404, $this->call('POST', '', ['action' => 'ban'], true, false)[0]);
    }

    public function testServiceRejectsUnauthenticatedReads(): void {
        [$status, $body] = $this->call('GET', '?op=reports', null, false);
        self::assertSame(401, $status);
        self::assertSame('Unauthorized', $body['error']);
    }

    public function testAuthenticatedReadAndInvalidIp(): void {
        [$status, $body] = $this->call('GET', '?op=reports');
        self::assertSame(200, $status);
        self::assertIsArray($body['reports']);
        self::assertSame(400, $this->call('GET', '?op=case&ip=invalid')[0]);
    }

    public function testGetCannotPerformAnAction(): void {
        self::assertSame(405, $this->call('GET', '?action=ban&ip=192.0.2.42')[0]);
    }

    public function testServiceChecksPostAuthenticationAndCommand(): void {
        self::assertSame(401, $this->call('POST', '', ['action' => 'ban'], false)[0]);
        self::assertSame(400, $this->call('POST', '', ['action' => 'invalid'])[0]);
    }

    public function testCaseReadReturnsExplicitUnknownMetadata(): void {
        [$status, $body] = $this->call('GET', '?op=case&ip=192.0.2.99');
        self::assertSame(200, $status);
        self::assertSame('ANON', $body['country']);
        self::assertNull($body['lookup']);
        self::assertArrayHasKey('context', $body);
    }

    public function testAuthorizedPostDismissesReportsAndReplayIsSafe(): void {
        $db = new PDO('mysql:host=' . (getenv('TEST_DB_HOST') ?: 'db-test') . ';dbname=atomboard_test',
            'atomboard_test', 'atomboard_test', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $post = newPost(0);
        $post['ip'] = '192.0.2.250';
        $post['message'] = 'Authenticated bot HTTP fixture';
        $columns = implode(',', array_keys($post));
        $query = $db->prepare('INSERT INTO test_posts (' . $columns . ') VALUES (' . implode(',', array_fill(0, count($post), '?')) . ')');
        $query->execute(array_values($post));
        $id = (int)$db->lastInsertId();
        $query = $db->prepare('INSERT INTO reports (ip,board,postnum,reason,timestamp) VALUES (?,?,?,?,?)');
        $query->execute(['203.0.113.250', 'test', $id, 'HTTP moderation fixture', time()]);
        $report = (int)$db->lastInsertId();
        $command = ['ip' => $post['ip'], 'action' => 'dismiss',
            'moderator' => '@http_moderator',
            'operation_id' => 'http-' . bin2hex(random_bytes(8)), 'through_report' => $report];
        try {
            [$status, $body] = $this->call('POST', '', $command);
            self::assertSame(200, $status, json_encode($body));
            self::assertSame('dismissed', $body['status']);
            self::assertSame('@http_moderator', $body['moderator']);
            self::assertArrayNotHasKey('actor', $body);
            self::assertSame([$status, $body], $this->call('POST', '', $command));
            self::assertSame([], $this->call('GET', '?op=case&ip=' . $post['ip'])[1]['reports']);
            self::assertSame(1, (int)$db->query('SELECT COUNT(*) FROM test_posts WHERE id=' . $id)->fetchColumn());
        } finally {
            $db->exec('DELETE FROM test_posts WHERE id=' . $id);
            $db->exec('DELETE FROM reports WHERE id=' . $report);
        }
    }
}
