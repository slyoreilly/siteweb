<?php

declare(strict_types=1);

final class SyncAckClient
{
    private string $url;
    private string $token;
    private string $headerName;
    private int $timeoutSeconds;
    private int $maxAttempts;

    public function __construct(string $url, string $token, string $headerName, int $timeoutSeconds, int $maxAttempts)
    {
        if ($url === '' || $token === '') {
            throw new RuntimeException('SYNC_ACK_URL and SYNC_ACK_TOKEN are required');
        }

        $this->url = $url;
        $this->token = $token;
        $this->headerName = $headerName !== '' ? $headerName : 'X-Sync-Token';
        $this->timeoutSeconds = $timeoutSeconds > 0 ? $timeoutSeconds : 10;
        $this->maxAttempts = $maxAttempts > 0 ? $maxAttempts : 6;
    }

    public static function fromEnv(): self
    {
        $url = trim((string)(getenv('SYNC_ACK_URL') ?: ''));
        $token = trim((string)(getenv('SYNC_ACK_TOKEN') ?: ''));
        $header = trim((string)(getenv('SYNC_ACK_HEADER') ?: 'X-Sync-Token'));
        $timeout = (int)(getenv('SYNC_ACK_TIMEOUT_SECONDS') ?: 10);
        $maxAttempts = (int)(getenv('SYNC_ACK_MAX_ATTEMPTS') ?: 6);

        return new self($url, $token, $header, $timeout, $maxAttempts);
    }

    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function backoffSecondsForAttempt(int $attempt): int
    {
        $schedule = [1, 2, 5, 10, 30, 60];
        $index = max(0, min($attempt - 1, count($schedule) - 1));
        return $schedule[$index];
    }

    public function sendOnce(array $payload): array
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return [
                'result' => 'technical',
                'http_code' => 0,
                'error' => 'ack payload serialization failed',
                'latency_ms' => 0,
            ];
        }

        $start = microtime(true);

        $ch = curl_init($this->url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                $this->headerName . ': ' . $this->token,
            ],
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min($this->timeoutSeconds, 5),
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $latencyMs = (int)round((microtime(true) - $start) * 1000);

        if ($response === false) {
            return [
                'result' => 'technical',
                'http_code' => 0,
                'error' => 'curl_error: ' . $curlErr,
                'latency_ms' => $latencyMs,
            ];
        }

        if ($httpCode === 200) {
            return ['result' => 'success', 'http_code' => 200, 'error' => null, 'latency_ms' => $latencyMs];
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return ['result' => 'failed_auth', 'http_code' => $httpCode, 'error' => 'ack auth failed', 'latency_ms' => $latencyMs];
        }

        if ($httpCode === 400 || $httpCode === 404 || $httpCode === 409) {
            return ['result' => 'rejected', 'http_code' => $httpCode, 'error' => 'ack functional rejection', 'latency_ms' => $latencyMs];
        }

        if ($httpCode >= 500 || $httpCode === 0) {
            return ['result' => 'technical', 'http_code' => $httpCode, 'error' => 'ack technical http=' . $httpCode, 'latency_ms' => $latencyMs];
        }

        if ($httpCode >= 400 && $httpCode < 500) {
            return ['result' => 'rejected', 'http_code' => $httpCode, 'error' => 'ack functional rejection', 'latency_ms' => $latencyMs];
        }

        return ['result' => 'technical', 'http_code' => $httpCode, 'error' => 'ack unexpected status=' . $httpCode, 'latency_ms' => $latencyMs];
    }
}
?>
