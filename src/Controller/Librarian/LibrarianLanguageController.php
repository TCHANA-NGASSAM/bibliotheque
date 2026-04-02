<?php

namespace App\Controller\Librarian;

use App\Entity\Language;
use App\Form\LanguageFormType;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/librarian/languages')]
#[IsGranted('ROLE_LIBRARIAN')]
class LibrarianLanguageController extends AbstractController
{
    #[Route('', name: 'librarian_language_index', methods: ['GET'])]
    public function index(LanguageRepository $languageRepository): Response
    {
        return $this->render('librarian/language/index.html.twig', [
            'languages' => $languageRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'librarian_language_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $language = new Language();
        $form = $this->createForm(LanguageFormType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($language);
            $em->flush();
            $this->addFlash('success', 'Langue créée.');

            return $this->redirectToRoute('librarian_language_index');
        }

        return $this->render('librarian/language/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id<\d+>}/edit', name: 'librarian_language_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Language $language, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LanguageFormType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Langue mise à jour.');

            return $this->redirectToRoute('librarian_language_index');
        }

        return $this->render('librarian/language/edit.html.twig', [
            'language' => $language,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}/delete', name: 'librarian_language_delete', methods: ['POST'])]
    public function delete(Request $request, Language $language, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_language'.$language->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
        } elseif (!$language->getBooks()->isEmpty()) {
            $this->addFlash('danger', 'Impossible de supprimer : des ouvrages utilisent cette langue.');
        } else {
            $em->remove($language);
            $em->flush();
            $this->addFlash('success', 'Langue supprimée.');
        }

        return $this->redirectToRoute('librarian_language_index');
    }
}
