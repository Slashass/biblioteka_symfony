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
     * @Route("/book", name="book_index", methods={"GET"})
     */
    // INDEX --------------------------------------------------------------------
    public function index(Request $r): Response
    {
        // All authors ----------------------------------------------------------
        $authors = $this->getDoctrine()
        ->getRepository(Author::class)
        ->findAll();
        
        
        // All books ------------------------------------------------------------
        $books = $this->getDoctrine()
        ->getRepository(Book::class);

        // Filter
        if($r->query->get('author_id') == 'all' || $r->query->get('title') == 'all') {
            $books = $books->findAll();
        }
        elseif (null !== $r->query->get('title')) {
            $books = $books->findBy(['title' => $r->query->get('title')]);
        }
        elseif (null !== $r->query->get('author_id')) {
            $books = $books->findBy(['author_id' => $r->query->get('author_id')],
            ['title' => 'ASC']);
        }

        // Sort
        elseif($r->query->get('sort') == 'book_asc') {
            $books = $books->findBy([],['title' => 'ASC']);
        } 
        elseif ($r->query->get('sort') == 'book_desc'){
            $books = $books->findBy([],['title' => 'DESC']);
        }
        elseif($r->query->get('sort') == 'id_asc') {
            $books = $books->findBy([],['author_id' => 'ASC']);
        }
        else {
            $books = $books->findAll();
        }

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'authors' => $authors,
            'sortBy' =>  $r->query->get('sort') ?? 'defaut',
            'authorId' => $r->query->get('author_id') ?? 0,
            'bookTitle' => $r->query->get('title') ?? 0,
        ]);
    }
    
    /**
     * @Route("/book/create", name="book_create", methods={"GET"})
     */
    // CREATE -------------------------------------------------------------------
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
    // naudojam request, kad gaut duomenis is POST metodo ------------------------
    public function store(Request $r): Response
    {

        // id patrikrinimas
        // dd($r->request->get('book_about'));

        $author = $this->getDoctrine()
        ->getRepository(Author::class)
        ->find($r->request->get('book_author_id'));
 
        $book = new Book;
        $book
            ->setTitle($r->request->get('book_title'))
            ->setPages($r->request->get('book_pages'))
            ->setIsbn($r->request->get('book_isbn'))
            ->setAbout($r->request->get('book_about'))
            ->setAuthor($author); 

        // doctrine tarpininkas tarp db ir entity
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        return $this->redirectToRoute('book_index');
    }

    /**
     * @Route("/book/edit/{id}", name="book_edit", methods={"GET"})
     */
    // EDIT --------------------------------------------------------------------
    public function edit(int $id): Response
    {

        $book = $this->getDoctrine()
        ->getRepository(Book::class)
        ->find($id);
        
        $authors = $this->getDoctrine()
        ->getRepository(Author::class)
        ->findAll();

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'authors' => $authors,
        ]);
    }

    /**
     * @Route("/book/update/{id}", name="book_update", methods={"POST"})
     */
    //  UPDATE method-----------------------------------------------------------
    public function update(Request $r, $id): Response
    {
        $book = $this->getDoctrine()
        ->getRepository(Book::class)
        ->find($id);
        
        // imam autoriu pagal sena id
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($r->request->get('book_author'));

        // dd($author);

        $book
            ->setTitle($r->request->get('book_title'))
            ->setPages($r->request->get('book_pages'))
            ->setIsbn($r->request->get('book_isbn'))
            ->setAbout($r->request->get('book_about'))
            ->setAuthor($author);
        
        // doctrine tarpininkas tarp db ir entity
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        return $this->redirectToRoute('book_index');
    }

     /**
     * @Route("/book/delete/{id}", name="book_delete", methods={"POST"})
     */
    //  DELETE method -----------------------------------------------------------
    public function delete($id): Response
    {
        // imam knygas pagal sena id
        $book = $this->getDoctrine()
        ->getRepository(Book::class)
        ->find($id);

        // doctrine tarpininkas tarp db ir entity
        // metodu remove paduoda autoriu istrinam
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($book);
        $entityManager->flush();

        return $this->redirectToRoute('book_index');
    }
}
