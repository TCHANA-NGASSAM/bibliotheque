<?php

namespace App\Controller\Librarian;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/librarian')]
#[IsGranted('ROLE_LIBRARIAN')]
class LibrarianDashboardController extends AbstractController
{
    #[Route('', name: 'librarian_dashboard')]
    public function index(): Response
    {
        return $this->render('librarian/dashboard.html.twig');
    }
}
