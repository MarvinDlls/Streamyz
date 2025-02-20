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
use Symfony\Component\HttpFoundation\JsonResponse;

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
            $user = $this->historyService->getUser();
            $favorites = $this->favoriteRepository->findBy(['user' => $user]);
            
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
    public function toggle(int $tmdbId): JsonResponse
    {
        try {
            $user = $this->historyService->getUser();
            
            $existingFavorite = $this->favoriteRepository->findOneBy([
                'user' => $user,
                'tmdb' => $tmdbId
            ]);

            if ($existingFavorite) {
                $this->entityManager->remove($existingFavorite);
                $this->entityManager->flush();
                
                return $this->json([
                    'status' => 'removed',
                    'message' => 'Film retiré des favoris'
                ]);
            }

            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setTmdb($tmdbId);
            $favorite->setCreatedAt(new \DateTimeImmutable());
            $favorite->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($favorite);
            $this->entityManager->flush();

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
    public function check(int $tmdbId): JsonResponse
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

    #[Route('/check-multiple', name: 'check_multiple_favorites', methods: ['POST'])]
    public function checkMultipleFavorites(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $movieIds = $data['movieIds'] ?? [];

            $user = $this->historyService->getUser();
            if (!$user) {
                return $this->json(['favorites' => []]);
            }

            $favorites = $this->favoriteRepository->createQueryBuilder('f')
                ->select('f.tmdb')
                ->where('f.user = :user')
                ->andWhere('f.tmdb IN (:movieIds)')
                ->setParameter('user', $user)
                ->setParameter('movieIds', $movieIds)
                ->getQuery()
                ->getResult();

            $favoriteIds = array_column($favorites, 'tmdb');

            return $this->json(['favorites' => $favoriteIds]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }

    #[Route('/remove/{id}', name: 'favorite_remove', methods: ['DELETE'])]
    public function remove(Favorite $favorite): JsonResponse
    {
        try {
            $this->entityManager->remove($favorite);
            $this->entityManager->flush();

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