<?php

namespace App\Controller\Admin;

use App\Repository\BookRepository;
use App\Repository\BookReviewRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(
        ReservationRepository $reservationRepository,
        BookRepository $bookRepository,
        BookReviewRepository $bookReviewRepository,
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'pendingReservations' => $reservationRepository->countPending(),
            'hiddenReviews' => $bookReviewRepository->countHidden(),
            'lowStockBooks' => $bookRepository->findWhereStockAtOrBelow(2),
        ]);
    }
}
