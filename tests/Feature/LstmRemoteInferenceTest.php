<?php

namespace Tests\Feature;

use App\Services\LstmInferenceService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class LstmRemoteInferenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'lstm.driver' => 'http', 'lstm.api_url' => 'https://lstm.example.test',
            'lstm.api_token' => str_repeat('x', 48), 'lstm.python' => 'does-not-exist',
        ]);
        Http::preventStrayRequests();
    }

    public function test_http_driver_sends_bearer_and_payload_without_running_local_python(): void
    {
        Http::fake(['https://lstm.example.test/v1/predict' => Http::response([
            'platforms' => ['instagram' => ['status' => 'ok', 'account_id' => 'ig-a']],
        ])]);
        $payload = ['accounts' => [['platform' => 'instagram', 'account_id' => 'ig-a', 'posts' => []]], 'candidates' => []];
        $result = app(LstmInferenceService::class)->predict($payload);
        $this->assertSame('ok', $result['platforms']['instagram']['status']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer '.str_repeat('x', 48))
            && $request->data() === $payload);
    }

    public function test_insecure_url_is_rejected_before_sending_the_key(): void
    {
        Http::fake();
        config(['lstm.api_url' => 'http://lstm.example.test']);
        try {
            app(LstmInferenceService::class)->predict([]);
            $this->fail('HTTP URL should be rejected');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('HTTPS', $e->getMessage());
        }
        Http::assertNothingSent();
    }

    public function test_remote_error_does_not_leak_its_body_or_key(): void
    {
        Http::fake(['*' => Http::response(['debug' => 'private-body-'.str_repeat('x', 48)], 401)]);
        try {
            app(LstmInferenceService::class)->predict([]);
            $this->fail('401 should fail');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('401', $e->getMessage());
            $this->assertStringNotContainsString('private-body', $e->getMessage());
            $this->assertStringNotContainsString(str_repeat('x', 48), $e->getMessage());
        }
    }

    public function test_remote_timeout_has_a_safe_actionable_message(): void
    {
        Http::fake(fn () => throw new ConnectionException('Untrusted request details'));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No se pudo contactar la API LSTM');
        app(LstmInferenceService::class)->predict([]);
    }

    public function test_incomplete_platform_response_is_rejected(): void
    {
        Http::fake(['*' => Http::response(['platforms' => []])]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('incompatible');
        app(LstmInferenceService::class)->predict(['accounts' => [['platform' => 'instagram']]]);
    }

    public function test_tls_failure_is_reported_without_disabling_certificate_verification(): void
    {
        Http::fake(fn () => throw new \GuzzleHttp\Exception\RequestException(
            'Sensitive TLS details', new \GuzzleHttp\Psr7\Request('POST', 'https://lstm.example.test'),
            null, null, ['errno' => 60]
        ));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('LSTM_CA_BUNDLE');
        app(LstmInferenceService::class)->predict([]);
    }
}
