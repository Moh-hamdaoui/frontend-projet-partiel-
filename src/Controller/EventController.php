<?php
// src/Controller/EventController.php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participant;
use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class EventController extends AbstractController
{
    #[Route('/api/events', name: 'list_events', methods: ['GET'])]
    public function listEvents(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        return $this->json($events);
    }

    #[Route('/api/events/{id}', name: 'view_event', methods: ['GET'])]
    public function viewEvent(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($event);
    }

    #[Route('/api/events', name: 'create_event', methods: ['POST'])]
    public function createEvent(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        $event = new Event();
        $event->setTitre($data['titre']);
        $event->setDescription($data['description']);
        $event->setDate(new \DateTime($data['date']));
        $event->setLieu($data['lieu']);
        $event->setNombreMaxParticipants($data['nombreMaxParticipants']);

        $entityManager->persist($event);
        $entityManager->flush();

        return $this->json($event, Response::HTTP_CREATED);
    }

    #[Route('/api/events/{id}', name: 'update_event', methods: ['PUT'])]
    public function updateEvent(int $id, Request $request, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $event->setTitre($data['titre']);
        $event->setDescription($data['description']);
        $event->setDate(new \DateTime($data['date']));
        $event->setLieu($data['lieu']);
        $event->setNombreMaxParticipants($data['nombreMaxParticipants']);

        $entityManager->flush();

        return $this->json($event);
    }

    #[Route('/api/events/{id}', name: 'delete_event', methods: ['DELETE'])]
    public function deleteEvent(int $id, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($event);
        $entityManager->flush();

        return $this->json(['message' => 'Event deleted successfully']);
    }

    // Gestion des participants
    #[Route('/api/events/{eventId}/participants', name: 'list_participants', methods: ['GET'])]
    public function listParticipants(int $eventId, ParticipantRepository $participantRepository): Response
    {
        $participants = $participantRepository->findBy(['event' => $eventId]);
        return $this->json($participants);
    }

    #[Route('/api/events/{eventId}/participants', name: 'add_participant', methods: ['POST'])]
    public function addParticipant(int $eventId, Request $request, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($eventId);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (count($event->getParticipants()) >= $event->getNombreMaxParticipants()) {
            return $this->json(['error' => 'Event is already full'], Response::HTTP_BAD_REQUEST);
        }

        $participant = new Participant();
        $participant->setNom($data['nom']);
        $participant->setEmail($data['email']);
        $participant->setEvent($event);

        $entityManager->persist($participant);
        $entityManager->flush();

        return $this->json($participant, Response::HTTP_CREATED);
    }
}
