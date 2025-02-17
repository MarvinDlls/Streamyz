<?php

namespace App\Controller;

use App\Service\TmdbApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    private TmdbApiService $tmdbApiService;

    public function __construct(TmdbApiService $tmdbApiService)
    {
        $this->tmdbApiService = $tmdbApiService;
    }

    #[Route('/movies', name: 'movie_list')]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search');
        
        if ($search) {
            $movies = $this->tmdbApiService->searchMovies($search);
        } else {
            $movies = $this->tmdbApiService->fetchPopularMovies();
        }

        return $this->render('movies/index.html.twig', [
            'movies' => $movies['results'],
            'search' => $search
        ]);
    }

    #[Route('/movie/{id}', name: 'movie_detail')]
    public function detail(int $id): Response
    {
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
        ]);
    }
}
