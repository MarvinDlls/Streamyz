<?php

namespace App\Controller;

use App\Entity\Report;
use App\Service\HistoryService;
use App\Service\TmdbApiService;
use App\Repository\HistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MovieController extends AbstractController
{
    public function __construct(
        private TmdbApiService $tmdbApiService,
        private HistoryRepository $historyRepository,
        private HistoryService $historyService,
        private EntityManagerInterface $entityManager
    ) {}

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
            $user = $this->historyService->getUser();
            $history = $this->historyRepository->findOneBy(['user' => $user]);

            return [
                'movies' => $paginator->paginate($results, $page, 14),
                'search' => $search,
                'genre' => $genre,
                'history' => $history,
                'error' => null
            ];
        } catch (\Exception $e) {
            return [
                'movies' => [],
                'search' => $search,
                'genre' => $genre,
                'history' => [],
                'error' => 'Une erreur est survenue lors de la récupération des films.'
            ];
        }
    }

    #[Route('/', name: 'movie_list')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $genres = [
            "28" => "Action",
            "12" => "Aventure",
            "10752" => "Guerre",
            "99" => "Documentaire",
            "18" => "Drame",
            "10751" => "Famille",
            "14" => "Fantasy",
            "36" => "Histoire",
            "27" => "Horreur",
            "10402" => "Musique",
            "9648" => "Mystère",
            "10749" => "Romance",
            "878" => "Science-Fiction",
            "53" => "Thriller",
        ];

        $genreId = $request->query->get('genre');
        $selectedGenre = $genres[$genreId] ?? null;

        $moviesData = $this->getMovies($request, $paginator);

        $moviesData['selectedGenre'] = $selectedGenre;
        $moviesData['genres'] = $genres;

        $response = $this->render('movies/index.html.twig', $moviesData);

        // Gestion du cookie utilisateur
        if (!$request->cookies->has('user_uuid')) {
            $user = $this->historyService->getUser();
            $cookie = Cookie::create('user_uuid', $user)
                ->withExpires(new \DateTime('+1 year'))
                ->withPath('/')
                ->withSecure($request->isSecure())
                ->withHttpOnly(true);
            $response->headers->setCookie($cookie);
        }

        return $response;
    }

    #[Route('/movies/all', name: 'movie_all')]
    public function tous(Request $request, PaginatorInterface $paginator): Response
    {
        try {
            $maxPages = 10;
            $movies = $this->tmdbApiService->fetchAllMovies($maxPages);
            $pagination = $paginator->paginate(
                $movies,
                $request->query->getInt('page', 1),
                14
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

    #[Route('/movie/{id}', name: 'movie_detail', methods: ['GET', 'POST'])]
    public function detail(int $id, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleMovieReport($id);
        }

        try {
            $details = $this->tmdbApiService->fetchDetailMovie($id);
            $videos = $this->tmdbApiService->videoMovie($id);
            $reviews = $this->tmdbApiService->reviewMovie($id);

            // Ajout à l'historique
            $response = new Response();
            $this->historyService->addHistory($details['id'], $details['title'], $response);

            $trailer = $this->findTrailer($videos['results'] ?? []);

            return $this->render('movies/detail.html.twig', [
                'details' => $details,
                'trailer' => $trailer,
                'reviews' => $reviews['results'] ?? [],
                'hasError' => false
            ], $response);
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

    private function handleMovieReport(int $id): Response
    {
        $report = new Report();
        $report->setTmdb($id);
        $report->setUser($this->historyService->getUser());
        $report->setCreatedAt(new \DateTimeImmutable());
        
        $this->entityManager->persist($report);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Votre signalement a été pris en compte');
        return $this->redirectToRoute('movie_list');
    }

    private function findTrailer(array $videos): ?array
    {
        foreach ($videos as $video) {
            if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
                return $video;
            }
        }
        return null;
    }
}