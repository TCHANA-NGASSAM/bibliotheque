<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\BookReview;
use App\Entity\Favorite;
use App\Entity\Reservation;
use App\Entity\ReservationStatus;
use App\Entity\User;
use App\Form\BookReviewFormType;
use App\Form\ReservationRequestType;
use App\Repository\BookReviewRepository;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BookInteractionController extends AbstractController
{
    #[Route('/books/{id<\d+>}/reserve', name: 'app_book_reserve', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reserve(
        Request $request,
        Book $book,
        EntityManagerInterface $em,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $reservation = (new Reservation())
            ->setUser($user)
            ->setBook($book)
            ->setStatus(ReservationStatus::Pending);

        $form = $this->createForm(ReservationRequestType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reservation);
            $em->flush();
            $this->addFlash('success', 'Demande de réservation enregistrée. Un bibliothécaire la traitera.');

            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $this->addFlash('danger', 'Créneau invalide. Vérifiez les dates.');

        return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
    }

    #[Route('/books/{id<\d+>}/favorite', name: 'app_book_favorite_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleFavorite(
        Request $request,
        Book $book,
        FavoriteRepository $favoriteRepository,
        EntityManagerInterface $em,
    ): Response {
        if (!$this->isCsrfTokenValid('favorite'.$book->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $existing = $favoriteRepository->findOneByUserAndBook($user, $book);
        if ($existing !== null) {
            $em->remove($existing);
            $this->addFlash('success', 'Retiré des favoris.');
        } else {
            $fav = (new Favorite())->setUser($user)->setBook($book);
            $em->persist($fav);
            $this->addFlash('success', 'Ajouté aux favoris.');
        }
        $em->flush();

        return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
    }

    #[Route('/books/{id<\d+>}/review', name: 'app_book_review', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function review(
        Request $request,
        Book $book,
        BookReviewRepository $bookReviewRepository,
        EntityManagerInterface $em,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($bookReviewRepository->findOneByUserAndBook($user, $book) !== null) {
            $this->addFlash('warning', 'Vous avez déjà publié un avis pour ce livre.');

            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $review = (new BookReview())
            ->setUser($user)
            ->setBook($book)
            ->setVisible(true);

        $form = $this->createForm(BookReviewFormType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($review);
            $em->flush();
            $this->addFlash('success', 'Votre avis a été publié.');

            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $this->addFlash('danger', 'Impossible d’enregistrer l’avis.');

        return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
    }
}
