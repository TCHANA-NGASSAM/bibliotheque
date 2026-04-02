<?php

namespace App\Controller\Admin;

use App\Entity\BookReview;
use App\Repository\BookReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reviews')]
#[IsGranted('ROLE_ADMIN')]
class AdminReviewController extends AbstractController
{
    #[Route('', name: 'admin_review_index', methods: ['GET'])]
    public function index(BookReviewRepository $bookReviewRepository): Response
    {
        return $this->render('admin/review/index.html.twig', [
            'reviews' => $bookReviewRepository->findAllOrdered(),
        ]);
    }

    #[Route('/{id<\d+>}/visibility', name: 'admin_review_visibility', methods: ['POST'])]
    public function toggleVisibility(Request $request, BookReview $review, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('review_visibility'.$review->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('admin_review_index');
        }

        $review->setVisible(!$review->isVisible());
        $em->flush();
        $this->addFlash('success', $review->isVisible() ? 'Avis visible sur le site.' : 'Avis masqué (modération).');

        return $this->redirectToRoute('admin_review_index');
    }
}
