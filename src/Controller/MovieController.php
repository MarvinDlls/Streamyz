<?php

namespace App\Controller;

use App\Entity\History;
use App\Service\HistoryService;
use App\Service\TmdbApiService;
use App\Repository\HistoryRepository;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MovieController extends AbstractController
{
    private TmdbApiService $tmdbApiService;
    private HistoryRepository $hr;
    private HistoryService $hs;

    public function __construct(TmdbApiService $tmdbApiService, HistoryRepository $hr, HistoryService $hs)
    {
        $this->tmdbApiService = $tmdbApiService;
        $this->hr = $hr;
        $this->hs = $hs;
    }

    #[Route('/movies', name: 'movie_list', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search');

        if ($search) {
            $movies = $this->tmdbApiService->searchMovies($search);
        } else {
            $movies = $this->tmdbApiService->fetchPopularMovies();
        }

        $uuid = $this->hs->getUuid();
        $history = $this->hr->findOneBy(['uuid' => $uuid]);

        $response = $this->render('movies/index.html.twig', [
            'movies' => $movies['results'],
            'search' => $search,
            'history' => $history ? $history->getTmdb() : [],
        ]);

        // Si l'UUID n'existait pas, ajoute un cookie
        if (!$request->cookies->has('user_uuid')) {
            $cookie = Cookie::create('user_uuid', $uuid, strtotime('+1 year'));
            $response->headers->setCookie($cookie);
        }

        return $response;
    }


    #[Route('/movie/{id}', name: 'movie_detail', methods: ['GET'])]
    public function detail(int $id): Response
    {
        $response = new Response();

        $details = $this->tmdbApiService->fetchDetailMovie($id);
        $videos = $this->tmdbApiService->videoMovie($id);

        $movieTitle = $details['title'];
        $this->hs->addHistory($movieTitle, $response);

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
