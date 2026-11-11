<?php

namespace App\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Replaces the app's ISBNLab HTTP client with one that answers from
 * tests/fixtures, so tests run without a token or network access.
 */
final class MockIsbnLab
{
    /** @var list<array{request: \Psr\Http\Message\RequestInterface}> */
    public array $requests = [];

    public function __construct(ContainerInterface $container, Response ...$responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->requests));
        $container->set('isbnlab.http_client', new Client(['handler' => $stack]));
    }

    public static function json(string $fixture): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], (string) file_get_contents(__DIR__.'/fixtures/'.$fixture));
    }

    public static function error(int $status, string $detail): Response
    {
        return new Response($status, ['Content-Type' => 'application/problem+json'], (string) json_encode(['title' => 'An error occurred', 'detail' => $detail, 'status' => $status]));
    }

    public function lastPath(): string
    {
        return $this->requests[\count($this->requests) - 1]['request']->getUri()->getPath();
    }
}
