<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use App\Entity\Author;
use App\Entity\Book;

class BookController extends AbstractController
{
    /**
     * @Route("/book", name="book_index")
     */
    // INDEX --------------------------------------------------------------------
    public function index(): Response
    {
        // imam knygas
        $books = $this->getDoctrine()
        ->getRepository(Book::class)
        ->findAll();

        

        return $this->render('book/index.html.twig', [
            'books' => $books,
            
        ]);
    }
    /**
     * @Route("ckeditor", name="ckeditor")
     */
    // CKEDITOR -------------------------------------------------------------------
    public function ckeditor(): Response
    {
        $form = $this->createFormBuilder()
        ->add('content', CKEditorType::class, [
            'config' => [
                'config' => 'main_configs',
            ],]
            )
        ->getForm();

        return $this->render('ckeditor.html.twig', [
            'form' => $form->createView()
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

        $form = $this->createFormBuilder()
        ->add('About', CKEditorType::class, [
            'config' => [
                'config' => 'main_configs',
                'cloudServices_tokenUrl' => 'https://example.com/cs-token-endpoint',
                'cloudServices_uploadUrl' => 'https://your-organization-id.cke-cs.com/easyimage/upload/',
            ],]
            )
        ->getForm();

        return $this->render('book/create.html.twig', [
            'authors' => $authors,
            'form' => $form->createView(),
            'autoload' => true,
            'async'=> true,
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
        // ieskom to autoriaus kurio id yra perduotas
        $book = $this->getDoctrine()
        ->getRepository(Book::class)
        ->find($id);

        $authors = $this->getDoctrine()
        ->getRepository(Author::class)
        ->findAll();

        $form = $this->createFormBuilder()
        ->add('About', CKEditorType::class, [
            'config' => [
                'config' => 'main_configs',
            ],]
            )
        ->getForm();

        // atiduodam viena knyga
        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'authors' => $authors,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/book/update/{id}", name="book_update", methods={"POST"})
     */
    //  UPDATE method-----------------------------------------------------------
    public function update(Request $r, $id): Response
    {
        // imam autoriu pagal sena id
        $book = $this->getDoctrine()
            ->getRepository(Book::class)
            ->find($id);

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
