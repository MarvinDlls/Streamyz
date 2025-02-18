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

    private function getMovies(Request $request, PaginatorInterface $paginator): array
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

            $results = $movies['results'] ?? [];

            return [
                'movies' => $paginator->paginate($results, $page, 12),
                'search' => $search,
                'genre' => $genre,
                'error' => null
            ];
        } catch (\Exception $e) {
            return [
                'movies' => [],
                'search' => $search,
                'genre' => $genre,
                'error' => 'Une erreur est survenue lors de la récupération des films.'
            ];
        }
    }

    #[Route('/movies', name: 'movie_list')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        return $this->render('movies/index.html.twig', $this->getMovies($request, $paginator));
    }

    #[Route('/movies/all', name: 'movie_all')]
    public function tous(Request $request, PaginatorInterface $paginator): Response
    {
        try {
            $maxPages = 10; // Limiter à 10 pages pour éviter un temps de chargement trop long
            $movies = $this->tmdbApiService->fetchAllMovies($maxPages); // Récupérer tous les films
            $pagination = $paginator->paginate(
                $movies, // Les données à paginer
                $request->query->getInt('page', 1), // Page actuelle
                14 // Nombre d'éléments par page
            );

            return $this->render('movies/tous.html.twig', [
                'movies' => $pagination,
                'search' => null,
                'genre' => null,
                'error' => null
            ]);
        } catch (\Exception $e) {
            return $this->render('movies/tous.html.twig', [
                'movies' => [],
                'search' => null,
                'genre' => null,
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