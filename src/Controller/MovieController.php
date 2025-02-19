<?php

namespace App\Controller;

use App\Entity\History;
use App\Repository\HistoryRepository;
use App\Service\HistoryService;
use App\Service\TmdbApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    private TmdbApiService $tmdbApiService;
    private HistoryRepository $hr;

    public function __construct(TmdbApiService $tmdbApiService, HistoryService $hs)
    {
        $this->tmdbApiService = $tmdbApiService;
        $this->hr = $hs;
    }

    #[Route('/movies', name: 'movie_list', methods: ['GET'])]
    public function index(Request $request, Response $response): Response
    {
        $search = $request->query->get('search');

        if ($search) {
            $movies = $this->tmdbApiService->searchMovies($search);
        } else {
            $movies = $this->tmdbApiService->fetchPopularMovies();
        }

        $uuid = $this->hr->getUuid($response);
        $history = $this->hr->findBy(['uuid' => $uuid]);

        return $this->render('movies/index.html.twig', [
            'movies' => $movies['results'],
            'search' => $search,
            'history' => $history
        ]);
    }

    #[Route('/movie/{id}', name: 'movie_detail', methods: ['GET'])]
    public function detail(int $id): Response
    {
        $response = new Response();

        $this->hr->addHistory($id, $response);
        $details = $this->tmdbApiService->fetchDetailMovie($id);
        $videos = $this->tmdbApiService->videoMovie($id);

        $trailer = null;
        foreach ($videos['results'] ?? [] as $video) {
            if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
                $trailer = $video;
                break;
            }
        }

        return $this->render('movies/detail.html.twig', [
            'details' => $details,
            'trailer' => $trailer,
        ], $response);
    }
}
