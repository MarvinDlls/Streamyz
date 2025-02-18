<?php

namespace App\Service;

use App\Entity\History;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class HistoryService
{

    private $em;
    private $rs;

    public function __construct(EntityManagerInterface $em, RequestStack $rs)
    {
        $this->em = $em;
        $this->rs = $rs;
    }


    // Crée ou récupèrer l'ID de l'utilisateur
    public function getUuid(Response $response): string
    {
        $request = $this->rs->getCurrentRequest();
        $uuid = $request->cookies->get('user_uuid');

        if (!$uuid) {
            $uuid = Uuid::v4()->toRfc4122();
            $cookie = Cookie::create('user_uuid', $uuid, strtotime('+1 year'));
            $response->headers->setCookie($cookie);
        }

        return $uuid;
    }

}
