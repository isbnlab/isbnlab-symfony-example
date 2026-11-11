<?php

namespace App\IsbnLab;

use ISBNLab\ApiException;

/**
 * What an ISBNLab API error means for the person using this app.
 *
 * The client throws ApiException for any response other than 2xx, with the HTTP
 * status as its code. The API's own explanation is in the response body's
 * `detail`, so that is shown when there is one.
 */
final readonly class ApiError
{
    private function __construct(
        public int $status,
        public string $message,
    ) {
    }

    public static function from(ApiException $e): self
    {
        $status = $e->getCode();
        $detail = self::detail($e);

        $message = match ($status) {
            400 => 'That is not a valid ISBN.',
            401 => 'The API token is missing or wrong. Set ISBNLAB_API_TOKEN in .env.local.',
            403 => $detail ?? 'Your ISBNLab plan does not include this.',
            404 => 'That book is not in the ISBNLab catalog.',
            429 => $detail ?? 'The API limit was reached. Try again later.',
            0 => 'Could not reach the ISBNLab API.',
            default => $detail ?? 'The ISBNLab API returned an error.',
        };

        // 0 means the request never got an answer (network error).
        return new self(0 === $status ? 502 : $status, $message);
    }

    private static function detail(ApiException $e): ?string
    {
        $body = $e->getResponseBody();
        if (!\is_string($body)) {
            return null;
        }
        $data = json_decode($body, true);

        return \is_array($data) && \is_string($data['detail'] ?? null) ? $data['detail'] : null;
    }
}
