<?php

namespace App\Controller\Librarian;

use App\Entity\Book;
use App\Form\BookFormType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/librarian/books')]
#[IsGranted('ROLE_LIBRARIAN')]
class LibrarianBookController extends AbstractController
{
    #[Route('', name: 'librarian_book_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('librarian/book/index.html.twig', [
            'books' => $bookRepository->findBy([], ['title' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'librarian_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $book = new Book();
        $form = $this->createForm(BookFormType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($book);
            $em->flush();
            $this->addFlash('success', 'Ouvrage ajouté au catalogue.');

            return $this->redirectToRoute('librarian_book_index');
        }

        return $this->render('librarian/book/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'librarian_book_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(BookFormType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Fiche ouvrage mise à jour.');

            return $this->redirectToRoute('librarian_book_index');
        }

        return $this->render('librarian/book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}/delete', name: 'librarian_book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_book'.$book->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
        } else {
            $em->remove($book);
            $em->flush();
            $this->addFlash('success', 'Ouvrage supprimé.');
        }

        return $this->redirectToRoute('librarian_book_index');
    }
}
