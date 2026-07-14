<?php

declare(strict_types=1);

namespace Src;

use RuntimeException;

/**
 * FreeToGameClient
 *
 * Single responsibility: talk to the FreeToGame external API.
 * Knows nothing about the database or HTTP responses to the browser.
 */
final class FreeToGameClient
{
    public function __construct(private readonly string $baseUrl = API_BASE_URL)
    {
    }

    /**
     * Fetch the full list of games from the external API.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws RuntimeException on network or parsing failure.
     */
    public function fetchAllGames(): array
    {
        $data = $this->request('/games');

        if (!is_array($data)) {
            throw new RuntimeException('Unexpected response format from FreeToGame API.');
        }

        return $data;
    }

    /**
     * Fetch a single game's full detail by its external id.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException on network or parsing failure.
     */
    public function fetchGameById(int $externalId): array
    {
        $data = $this->request('/game?id=' . $externalId);

        if (!is_array($data) || empty($data)) {
            throw new RuntimeException('Game not found on FreeToGame API.');
        }

        return $data;
    }

    /**
     * Perform the raw HTTP GET request and decode JSON.
     *
     * @return mixed
     */
    private function request(string $path): mixed
    {
        $url = rtrim($this->baseUrl, '/') . $path;

        $ch = curl_init($url);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'GameVault/1.0 (+backend sync)',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        // Windows/XAMPP/Laragon PHP builds frequently ship without a
        // configured CA bundle, which makes cURL fail with
        // "SSL certificate problem: unable to get local issuer certificate".
        // We ship our own CA bundle so the sync works out of the box,
        // without requiring the developer to edit php.ini manually.
        $caBundle = __DIR__ . '/../config/cacert.pem';

        if (is_file($caBundle)) {
            $options[CURLOPT_CAINFO] = $caBundle;
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            throw new RuntimeException(sprintf(
                'FreeToGame API request failed (curl errno %d): %s',
                $curlErrno,
                $curlError
            ));
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException('FreeToGame API returned HTTP status ' . $httpCode);
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to decode FreeToGame API JSON response.');
        }

        return $decoded;
    }
}
