<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbApiService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function fetchPopularMovies(): array
    {
        $response = $this->client->request('GET', 'https://api.themoviedb.org/3/search/movie?&query=dragon', [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR'
            ]
        ]);

        return $response->toArray();
    }
}