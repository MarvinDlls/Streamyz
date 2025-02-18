<?php

namespace App\Controller;

use App\Service\TmdbApiService;
use Knp\Component\Pager\PaginatorInterface;
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
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
    $search = $request->query->get('search');
    $genre = $request->query->get('genre');
    $page = $request->query->getInt('page', 1);
    
    try {
        if ($search) {
            $movies = $this->tmdbApiService->searchMovies($search);
        } elseif ($genre) {
            $movies = $this->tmdbApiService->fetchGenreMovies($genre);
        } else {
            $movies = $this->tmdbApiService->fetchPopularMovies();
        }
        
        $results = isset($movies['results']) ? $movies['results'] : [];
        
        $pagination = $paginator->paginate(
            $results, // Les données à paginer
            $page,    // Le numéro de page actuel
            12       // Nombre d'éléments par page
        );

        return $this->render('movies/index.html.twig', [
            'movies' => $pagination,
            'search' => $search,
            'genre' => $genre
        ]);
    } catch (\Exception $e) {
        return $this->render('movies/index.html.twig', [
            'movies' => [],
            'search' => $search,
            'genre' => $genre,
            'error' => 'Une erreur est survenue lors de la récupération des films.'
        ]);
    }
}

    #[Route('/movie/{id}', name: 'movie_detail')]
    public function detail(int $id): Response
    {
        try {
            $details = $this->tmdbApiService->fetchDetailMovie($id);
            $videos = $this->tmdbApiService->videoMovie($id);
            $reviews = $this->tmdbApiService->reviewMovie($id);

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
                'reviews' => $reviews['results'] ?? [],
                'hasError' => false
            ]);
        } catch (\Exception $e) {

            return $this->render('movies/detail.html.twig', [
                'details' => null,
                'trailer' => null,
                'reviews' => [],
                'hasError' => true,
                'errorMessage' => 'Une erreur est survenue lors de la récupération des informations du film.'
            ]);
        }
    }
}
