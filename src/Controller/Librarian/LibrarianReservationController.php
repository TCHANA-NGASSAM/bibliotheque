<?php

namespace App\Controller\Librarian;

use App\Entity\Reservation;
use App\Entity\ReservationStatus;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/librarian/reservations')]
#[IsGranted('ROLE_LIBRARIAN')]
class LibrarianReservationController extends AbstractController
{
    #[Route('', name: 'librarian_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('librarian/reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAllForStaffOrdered(),
        ]);
    }

    #[Route('/{id<\d+>}/confirm', name: 'librarian_reservation_confirm', methods: ['POST'])]
    public function confirm(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('reservation_action'.$reservation->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('librarian_reservation_index');
        }

        if ($reservation->getStatus() !== ReservationStatus::Pending) {
            $this->addFlash('warning', 'Cette réservation n’est plus en attente.');

            return $this->redirectToRoute('librarian_reservation_index');
        }

        $book = $reservation->getBook();
        if ($book === null || $book->getStock() < 1) {
            $this->addFlash('danger', 'Stock insuffisant pour confirmer.');

            return $this->redirectToRoute('librarian_reservation_index');
        }

        $book->setStock($book->getStock() - 1);
        $reservation->setStatus(ReservationStatus::Confirmed);
        $em->flush();
        $this->addFlash('success', 'Réservation confirmée (stock mis à jour).');

        return $this->redirectToRoute('librarian_reservation_index');
    }

    #[Route('/{id<\d+>}/reject', name: 'librarian_reservation_reject', methods: ['POST'])]
    public function reject(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('reservation_action'.$reservation->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('librarian_reservation_index');
        }

        if ($reservation->getStatus() !== ReservationStatus::Pending) {
            $this->addFlash('warning', 'Action impossible pour ce statut.');

            return $this->redirectToRoute('librarian_reservation_index');
        }

        $reservation->setStatus(ReservationStatus::Cancelled);
        $em->flush();
        $this->addFlash('success', 'Réservation refusée.');

        return $this->redirectToRoute('librarian_reservation_index');
    }

    #[Route('/{id<\d+>}/complete', name: 'librarian_reservation_complete', methods: ['POST'])]
    public function complete(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('reservation_action'.$reservation->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('librarian_reservation_index');
        }

        if ($reservation->getStatus() !== ReservationStatus::Confirmed) {
            $this->addFlash('warning', 'Seules les réservations confirmées peuvent être clôturées (retour du livre).');

            return $this->redirectToRoute('librarian_reservation_index');
        }

        $book = $reservation->getBook();
        if ($book !== null) {
            $book->setStock($book->getStock() + 1);
        }
        $reservation->setStatus(ReservationStatus::Completed);
        $em->flush();
        $this->addFlash('success', 'Retour enregistré : exemplaire remis en stock.');

        return $this->redirectToRoute('librarian_reservation_index');
    }
}
