<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author", name="author_index", methods={"GET"})
     */
    public function index(): Response
    {
        // pasiemam visus autorius
        $authors = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findAll();
        
        // atiduodam visus autorius
        return $this->render('author/index.html.twig', [
            'authors' => $authors,
        ]);
    }
    /**
     * @Route("/author/create", name="author_create", methods={"GET"})
     */
    public function create(): Response
    {
        return $this->render('author/create.html.twig', [
        ]);
    }
    /**
     * @Route("/author/store", name="author_store", methods={"POST"})
     */
    // naudojam request, kad gaut duomenis is POST metodo
    public function store(Request $r): Response
    {
        // creating new author 
        $author = new Author;
        $author
            ->setName($r->request->get('author_name'))
            ->setSurname($r->request->get('author_surname'));
        
        // doctrine tarpininkas tarp db ir entity
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }

    /**
     * @Route("/author/edit/{id}", name="author_edit", methods={"GET"})
     */
    public function edit(int $id): Response
    {
        // ieskom to autoriaus kurio id yra perduotas
        $author = $this->getDoctrine()
        ->getRepository(Author::class)
        ->find($id);

        // atiduodam viena authoriu
        return $this->render('author/edit.html.twig', [
            'author' => $author,
        ]);
    }
    /**
     * @Route("/author/update/{id}", name="author_update", methods={"POST"})
     */
    //  update method
    public function update(Request $r, $id): Response
    {
        // imam autoriu pagal sena id
        $author = $this->getDoctrine()
        ->getRepository(Author::class)
        ->find($id);

        $author
            ->setName($r->request->get('author_name'))
            ->setSurname($r->request->get('author_surname'));
        
        // doctrine tarpininkas tarp db ir entity
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }

     /**
     * @Route("/author/delete/{id}", name="author_delete", methods={"POST"})
     */
    //  DELETE method
    public function delete($id): Response
    {
        // imam autoriu pagal sena id
        $author = $this->getDoctrine()
        ->getRepository(Author::class)
        ->find($id);

        if ($author->getBooks()->count() > 0) {
            return new Response('Trinti negalima nes turi knygu');
        }

        // doctrine tarpininkas tarp db ir entity
        // metodu remove paduoda autoriu istrinam
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }
}
