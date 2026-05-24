<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\JwtHandler;
use Exception;
use PHPUnit\Framework\TestCase;

class JwtHandlerTest extends TestCase
{
    private const SECRET = 'unit-test-secret';

    public function testGenerateTokenReturnsThreeSegments(): void
    {
        $jwt = new JwtHandler(self::SECRET);
        $token = $jwt->generateToken(['user_id' => 123]);

        $this->assertCount(3, explode('.', $token));
    }

    public function testValidateTokenReturnsPayloadForValidToken(): void
    {
        $jwt = new JwtHandler(self::SECRET, 'HS256', 3600);
        $token = $jwt->generateToken(['user_id' => 123]);

        $payload = $jwt->validateToken($token);

        $this->assertSame(123, $payload['user_id']);
        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('exp', $payload);
        $this->assertArrayHasKey('nbf', $payload);
    }

    public function testValidateTokenThrowsWhenTokenMissing(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token is required');

        $jwt = new JwtHandler(self::SECRET);
        $jwt->validateToken('');
    }

    public function testValidateTokenThrowsForInvalidFormat(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid token format');

        $jwt = new JwtHandler(self::SECRET);
        $jwt->validateToken('invalid-token');
    }

    public function testValidateTokenThrowsForInvalidSignature(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid token signature');

        $jwt = new JwtHandler(self::SECRET);
        $token = $jwt->generateToken(['user_id' => 123]);
        $parts = explode('.', $token);
        $parts[2] = 'tampered-signature';

        $jwt->validateToken(implode('.', $parts));
    }

    public function testValidateTokenThrowsWhenExpired(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token has expired');

        $jwt = new JwtHandler(self::SECRET);
        $token = $this->buildTokenWithPayload([
            'iat' => time() - 120,
            'exp' => time() - 60,
            'nbf' => time() - 120,
            'user_id' => 123,
        ]);

        $jwt->validateToken($token);
    }

    public function testValidateTokenThrowsWhenNbfInFuture(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token not valid yet');

        $jwt = new JwtHandler(self::SECRET);
        $token = $this->buildTokenWithPayload([
            'iat' => time(),
            'exp' => time() + 300,
            'nbf' => time() + 120,
            'user_id' => 123,
        ]);

        $jwt->validateToken($token);
    }

    public function testDecodeTokenReturnsNullForInvalidFormat(): void
    {
        $jwt = new JwtHandler(self::SECRET);
        $this->assertNull($jwt->decodeToken('invalid-token'));
    }

    public function testGetTokenExpirationReturnsNullForInvalidToken(): void
    {
        $jwt = new JwtHandler(self::SECRET);
        $this->assertNull($jwt->getTokenExpiration('invalid-token'));
    }

    public function testIsTokenExpiredReturnsTrueForExpiredToken(): void
    {
        $jwt = new JwtHandler(self::SECRET);
        $token = $this->buildTokenWithPayload([
            'iat' => time() - 120,
            'exp' => time() - 60,
            'nbf' => time() - 120,
            'user_id' => 123,
        ]);

        $this->assertTrue($jwt->isTokenExpired($token));
    }

    public function testGetTokenRemainingTimeReturnsZeroWhenExpired(): void
    {
        $jwt = new JwtHandler(self::SECRET);
        $token = $this->buildTokenWithPayload([
            'iat' => time() - 120,
            'exp' => time() - 60,
            'nbf' => time() - 120,
            'user_id' => 123,
        ]);

        $this->assertSame(0, $jwt->getTokenRemainingTime($token));
    }

    public function testGetTokenRemainingTimeReturnsPositiveForValidToken(): void
    {
        $jwt = new JwtHandler(self::SECRET);
        $token = $jwt->generateToken(['user_id' => 123]);

        $this->assertGreaterThan(0, $jwt->getTokenRemainingTime($token));
    }

    private function buildTokenWithPayload(array $payload): string
    {
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $headerEncoded = $this->base64UrlEncode((string) json_encode($header));
        $payloadEncoded = $this->base64UrlEncode((string) json_encode($payload));

        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, self::SECRET, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
