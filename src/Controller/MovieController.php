<?php

namespace App\Controller;

use App\Entity\Report;
use App\Service\HistoryService;
use App\Service\TmdbApiService;
use App\Repository\HistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    private EntityManagerInterface $em;

    public function __construct(TmdbApiService $tmdbApiService, HistoryRepository $hr, HistoryService $hs, EntityManagerInterface $em)
    {
        $this->tmdbApiService = $tmdbApiService;
        $this->hr = $hr;
        $this->hs = $hs;
        $this->em = $em;
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

        $user = $this->hs->getUser();
        $history = $this->hr->findOneBy(['user' => $user]);

        $response = $this->render('movies/index.html.twig', [
            'movies' => $movies['results'],
            'search' => $search,
            'history' => $history ? $history->getTmdb() : [],
        ]);

        // Si l'userId n'existait pas, ajoute un cookie
        if (!$request->cookies->has('user_uuid')) {
            $cookie = Cookie::create('user_uuid', $user)
                ->withExpires(new \DateTime('+1 year'))
                ->withPath('/')
                ->withSecure($request->isSecure())
                ->withHttpOnly(true);
            $response->headers->setCookie($cookie);
        }

        return $response;
    }

    #[Route('/movie/{id}', name: 'movie_detail', methods: ['GET'])]
    public function detail(int $id, Request $request): Response
    {
        $response = new Response();

        if ($request->isMethod('GET')) {

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
        } else {
            $uuidString = $this->hs->getUser($response);

            $report = new Report();
            $report->setTmdb($id);
            $report->setUser($uuidString);
            $report->setCreatedAt(new \DateTimeImmutable());

            $this->em->persist($report);
            $this->em->flush();

            $this->addFlash('success', 'Votre signalement a été pris en compte');
            return $this->redirectToRoute('movie_list', []);
            //@TODO change redirect to somthing better
        }
    }
}
