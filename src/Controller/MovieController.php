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

    
    #[Route('/player', name: 'player')]
    public function id(): Response
    {
        return $this->render('components/player.html.twig', [
            'video_url' => 'chemin/vers/video.mp4',
            'width' => '640',
            'height' => '360',
            'controls' => true,
            'autoplay' => false,
            'loop' => false,
            'mime_type' => 'video/mp4'
        ]);
    }
}
