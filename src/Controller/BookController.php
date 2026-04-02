<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\BookReview;
use App\Entity\Reservation;
use App\Entity\User;
use App\Form\BookReviewFormType;
use App\Form\ReservationRequestType;
use App\Repository\BookRepository;
use App\Repository\BookReviewRepository;
use App\Repository\CategoryRepository;
use App\Repository\FavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(BookRepository $bookRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'latestBooks' => $bookRepository->findBy([], ['id' => 'DESC'], 6),
        ]);
    }

    #[Route('/books', name: 'app_books')]
    public function index(
        Request $request,
        BookRepository $bookRepository,
        CategoryRepository $categoryRepository,
    ): Response {
        $title = $request->query->getString('title');
        $author = $request->query->getString('author');
        $categoryRaw = $request->query->get('category');
        $categoryId = null;
        if ($categoryRaw !== null && $categoryRaw !== '') {
            $categoryId = (int) $categoryRaw;
            if ($categoryId <= 0) {
                $categoryId = null;
            }
        }

        $books = $bookRepository->search(
            $title !== '' ? $title : null,
            $author !== '' ? $author : null,
            $categoryId,
        );

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'categories' => $categoryRepository->findBy([], ['name' => 'ASC']),
            'search_title' => $title,
            'search_author' => $author,
            'search_category' => $categoryId,
        ]);
    }

    #[Route('/books/{id<\d+>}', name: 'app_book_show')]
    public function show(
        Book $book,
        BookReviewRepository $bookReviewRepository,
        FavoriteRepository $favoriteRepository,
    ): Response {
        $user = $this->getUser();

        $reviewFormView = null;
        $reservationFormView = null;
        $existingReview = null;
        $isFavorite = false;

        if ($user instanceof User) {
            $existingReview = $bookReviewRepository->findOneByUserAndBook($user, $book);
            if ($existingReview === null) {
                $draft = (new BookReview())->setUser($user)->setBook($book);
                $reviewFormView = $this->createForm(BookReviewFormType::class, $draft)->createView();
            }

            $reservationDraft = (new Reservation())->setUser($user)->setBook($book);
            $reservationFormView = $this->createForm(ReservationRequestType::class, $reservationDraft)->createView();

            $isFavorite = null !== $favoriteRepository->findOneByUserAndBook($user, $book);
        }

        $reviews = $bookReviewRepository->findDisplayedForBook($book, $user instanceof User ? $user : null);

        return $this->render('book/show.html.twig', [
            'book' => $book,
            'reviews' => $reviews,
            'reviewForm' => $reviewFormView,
            'reservationForm' => $reservationFormView,
            'existingReview' => $existingReview,
            'isFavorite' => $isFavorite,
        ]);
    }
}
