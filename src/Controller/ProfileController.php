<?php

namespace App\Controller;

use App\Repository\FavoriteRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile')]
    public function index(
        ReservationRepository $reservationRepository,
        FavoriteRepository $favoriteRepository,
    ): Response {
        $user = $this->getUser();
        if ($user === null) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('profile/index.html.twig', [
            'reservations' => $reservationRepository->findByUserOrdered($user),
            'favorites' => $favoriteRepository->findByUserOrdered($user),
        ]);
    }
}
