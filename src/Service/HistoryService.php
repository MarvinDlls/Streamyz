<?php

namespace App\Service;

use App\Entity\History;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class HistoryService
{

    private EntityManagerInterface $em;
    private RequestStack $rs;

    //
    public function __construct(EntityManagerInterface $em, RequestStack $rs)
    {
        $this->em = $em;
        $this->rs = $rs;
    }

    // Crée ou récupèrer l'ID de l'utilisateur
    public function getUser(): string
    {
        $request = $this->rs->getCurrentRequest();
        $userId = $request->cookies->get('user_uuid');

        if (!$userId) {
            $userId = Uuid::v4();

            $history = new History();
            $history
                ->setIpAddress($request->getClientIp())
                ->setUser($userId)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
            ;

            $this->em->persist($history);
            $this->em->flush();
        }

        return $userId;
    }

    public function addHistory(string $tmdbId, string $tmdbTitle, Response $response): void
    {
        $userId = $this->getUser($response);
        $hr = $this->em->getRepository(History::class);

        $history = $hr->findOneBy(['user' => $userId]);

        if (!$history) {
            $history = new History();
            $history
                ->setUser($userId)
                ->setCreatedAt(new \DateTimeImmutable())
            ;
        }

        $history
            ->addMovie($tmdbId, $tmdbTitle)
            ->setUpdatedAt(new \DateTimeImmutable())
        ;
        $this->em->persist($history);
        $this->em->flush();
    }
}