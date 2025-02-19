<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Service\TmdbApiService;
use App\Service\HistoryService;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/favorites')]
class FavoriteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FavoriteRepository $favoriteRepository,
        private TmdbApiService $tmdbApiService,
        private HistoryService $historyService
    ) {}

    #[Route('/', name: 'favorite_list', methods: ['GET'])]
    public function index(): Response
    {
        try {
            // Récupérer l'utilisateur actuel via le service
            $user = $this->historyService->getUser();
            
            // Récupérer tous les favoris de l'utilisateur
            $favorites = $this->favoriteRepository->findBy(['user' => $user]);
            
            // Récupérer les détails des films depuis TMDB
            $movies = [];
            foreach ($favorites as $favorite) {
                $movieDetails = $this->tmdbApiService->fetchDetailMovie($favorite->getTmdb());
                if ($movieDetails) {
                    $movies[] = [
                        'details' => $movieDetails,
                        'favorite_id' => $favorite->getId(),
                        'created_at' => $favorite->getCreatedAt()
                    ];
                }
            }

            return $this->render('favorite/favorite.html.twig', [
                'movies' => $movies
            ]);
        } catch (\Exception $e) {
            return $this->render('favorite/favorite.html.twig', [
                'movies' => [],
                'error' => 'Une erreur est survenue lors de la récupération des favoris.'
            ]);
        }
    }

    #[Route('/toggle/{tmdbId}', name: 'favorite_toggle', methods: ['POST'])]
    public function toggle(int $tmdbId): Response
    {
        try {
            $user = $this->historyService->getUser();
            
            // Vérifier si le film est déjà en favoris
            $existingFavorite = $this->favoriteRepository->findOneBy([
                'user' => $user,
                'tmdb' => $tmdbId
            ]);

            if ($existingFavorite) {
                // Si existe déjà, on supprime
                $this->entityManager->remove($existingFavorite);
                $this->entityManager->flush();
                
                $this->addFlash('success', 'Film retiré des favoris');
                return $this->json([
                    'status' => 'removed',
                    'message' => 'Film retiré des favoris'
                ]);
            }

            // Si n'existe pas, on ajoute
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setTmdb($tmdbId);
            $favorite->setCreatedAt(new \DateTimeImmutable());
            $favorite->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($favorite);
            $this->entityManager->flush();

            $this->addFlash('success', 'Film ajouté aux favoris');
            return $this->json([
                'status' => 'added',
                'message' => 'Film ajouté aux favoris'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }

    #[Route('/check/{tmdbId}', name: 'favorite_check', methods: ['GET'])]
    public function check(int $tmdbId): Response
    {
        try {
            $user = $this->historyService->getUser();
            
            $favorite = $this->favoriteRepository->findOneBy([
                'user' => $user,
                'tmdb' => $tmdbId
            ]);

            return $this->json([
                'isFavorite' => $favorite !== null
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }

    #[Route('/remove/{id}', name: 'favorite_remove', methods: ['DELETE'])]
    public function remove(Favorite $favorite): Response
    {
        try {
            $this->entityManager->remove($favorite);
            $this->entityManager->flush();

            $this->addFlash('success', 'Film retiré des favoris');
            return $this->json([
                'status' => 'success',
                'message' => 'Favori supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la suppression'
            ], 500);
        }
    }
}