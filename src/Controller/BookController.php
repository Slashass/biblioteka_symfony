<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Author;
use App\Entity\Book;

class BookController extends AbstractController
{
    /**
     * @Route("/book", name="book_index")
     */
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }
    /**
     * @Route("/book/create", name="book_create", methods={"GET"})
     */
    public function create(): Response
    {
        // pasiemam visus autorius
        $authors = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findAll();

        return $this->render('book/create.html.twig', [
            'authors' => $authors,
        ]);
    }
     /**
     * @Route("/book/store", name="book_store", methods={"POST"})
     */
    // naudojam request, kad gaut duomenis is POST metodo
    public function store(Request $r): Response
    {
        // creating new author 
        $book = new Book;
        $book
            ->setTitle($r->request->get('book_title'))
            ->setPages($r->request->get('book_pages'))
            ->setIsbn($r->request->get('book_isbn'))
            ->setAbout($r->request->get('book_about'))
            ->setAuthorId($r->request->get('book_author_id'));
        
        // doctrine tarpininkas tarp db ir entity
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        return $this->redirectToRoute('book_index');
    }
}
