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
    public function getUuid(): string
    {
        $request = $this->rs->getCurrentRequest();
        $uuid = $request->cookies->get('user_uuid');

        if (!$uuid) {
            $uuid = Uuid::v4();

            $history = new History();
            $history
                ->setIpAdress($request->getClientIp())
                ->setUuid($uuid)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
            ;

            $this->em->persist($history);
            $this->em->flush();
        }

        return $uuid;
    }

    public function addHistory(int $movieId, Response $response): void
    {
        $uuid = $this->getUuid($response);
        $hr = $this->em->getRepository(History::class);

        $history = $hr->findOneBy(['uuid' => $uuid]);

        if (!$history) {
            $history = new History();
            $history->setUuid($uuid);
        }

        $history
            ->addMovie($movieId)
            ->setUpdatedAt(new \DateTimeImmutable())
        ;
        $this->em->persist($history);
        $this->em->flush();
    }
}
