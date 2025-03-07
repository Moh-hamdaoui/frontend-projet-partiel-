<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class EventController extends AbstractController
{
    #[Route('/api/events', methods: ['GET'])]
    public function listEvents(): Response
    {
        return $this->json(["message" => "Liste des événements"]);
    }
}
