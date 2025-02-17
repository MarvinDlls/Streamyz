<?php

namespace App\Controller;

use App\Service\TmdbApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index(): Response
    {
        $movies = $this->tmdbApiService->fetchPopularMovies();

        return $this->render('movies/index.html.twig', [
            'movies' => $movies['results'],
        ]);
    }
}
