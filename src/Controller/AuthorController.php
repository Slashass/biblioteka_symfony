<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author", name="author_index", methods={"GET"})
     */
    // INDEX -----------------------------------------------------------------
    public function index(Request $r): Response
    {
        // Take
        $authors = $this->getDoctrine()
            ->getRepository(Author::class);

        // Sort 
        if ($r->query->get('sort') == 'name_asc') {
            $authors = $authors->findBy([], ['name' => 'ASC']);
        } elseif ($r->query->get('sort') == 'name_desc') {
            $authors = $authors->findBy([], ['name' => 'DESC']);
        } elseif ($r->query->get('sort') == 'surname_asc') {
            $authors = $authors->findBy([], ['surname' => 'ASC']);
        } elseif ($r->query->get('sort') == 'surname_desc') {
            $authors = $authors->findBy([], ['surname' => 'DESC']);
        } else {
            $authors = $authors->findAll();
        }

        // Give
        return $this->render('author/index.html.twig', [
            'authors' => $authors,
            'sortBy' => $r->query->get('sort') ?? 'defaut'
        ]);
    }
    /**
     * @Route("/author/create", name="author_create", methods={"GET"})
     */
    // CREATE -----------------------------------------------------------------
    public function create(Request $r): Response
    {
        return $this->render('author/create.html.twig', [
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }
    /**
     * @Route("/author/store", name="author_store", methods={"POST"})
     */
    // naudojam request, kad gaut duomenis is POST metodo
    // STORE ------------------------------------------------------------------
    public function store(Request $r, ValidatorInterface $validator): Response
    {
        // creating new author 
        $author = new Author;

        $author
            ->setName($r->request->get('author_name'))
            ->setSurname($r->request->get('author_surname'));


        // Error validation 
        $errors = $validator->validate($author);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $r->getSession()->getFlashBag()->get('errors', $error->getMessage());
            }
            return $this->redirectToRoute('author_create');
        }

        // doctrine tarpininkas tarp db ir entity
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }

    /**
     * @Route("/author/edit/{id}", name="author_edit", methods={"GET"})
     */
    // EDIT ------------------------------------------------------------------
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
    //  UPDATE ---------------------------------------------------------------
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
    //  DELETE ---------------------------------------------------------------
    public function delete($id): Response
    {
        // imam autoriu pagal sena id
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);

        if ($author->getBooks()->count() > 0) {
            return new Response('Cannot delete, author own a book');
        }

        // doctrine tarpininkas tarp db ir entity
        // metodu remove paduoda autoriu istrinam
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }
}
