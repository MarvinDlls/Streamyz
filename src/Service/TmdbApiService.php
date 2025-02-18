<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbApiService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(
        HttpClientInterface $client,
        string $apiKey
    ) {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function fetchPopularMovies(): array
    {
        $response = $this->client->request('GET', 'https://api.themoviedb.org/3/movie/popular', [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR'
            ]
        ]);

        return $response->toArray();
    }

    public function fetchGenreMovies(string $genre): array
    {
    $response = $this->client->request('GET', 'https://api.themoviedb.org/3/discover/movie', [
        'query' => [
            'api_key' => $this->apiKey,
            'language' => 'fr-FR',
            'with_genres' => $genre
        ]
    ]);

    return $response->toArray();
    }

    public function fetchDetailMovie(int $id): array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/movie/{$id}", [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR'
            ]
        ]);

        return $response->toArray();
    }   
    
    public function videoMovie(int $id): array
    {
    $response = $this->client->request('GET', "https://api.themoviedb.org/3/movie/{$id}/videos", [
        'query' => [
            'api_key' => $this->apiKey,
            'language' => 'fr-FR'
        ]
    ]);

    return $response->toArray();
    }

    public function reviewMovie(int $id): array
    {
    $response = $this->client->request('GET', "https://api.themoviedb.org/3/movie/{$id}/reviews", [
        'query' => [
            'api_key' => $this->apiKey,
            'language' => 'fr-FR'
        ]
    ]);

    return $response->toArray();
    }

    public function searchMovies(string $query): array
    {
        $response = $this->client->request('GET', 'https://api.themoviedb.org/3/search/movie', [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR',
                'query' => $query
            ]
        ]);

        return $response->toArray();
    }
}