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
            'sortBy' => $r->query->get('sort') ?? 'defaut',
            'errors' => $r->getSession()->getFlashBag()->get('success', [])
        ]);
    }
    /**
     * @Route("/author/create", name="author_create", methods={"GET"})
     */
    // CREATE -----------------------------------------------------------------
    public function create(Request $r): Response
    {
        $author_name = $r->getSession()->getFlashBag()->get('author_name', []);
        $author_surname = $r->getSession()->getFlashBag()->get('author_surname', []);

        return $this->render('author/create.html.twig', [
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'author_name' => $author_name[0] ?? '',
            'author_surname' => $author_surname[0] ?? '',
        ]);
    }
    /**
     * @Route("/author/store", name="author_store", methods={"POST"})
     */
    // naudojam request, kad gaut duomenis is POST metodo
    // STORE ------------------------------------------------------------------
    public function store(Request $r, ValidatorInterface $validator): Response
    {
        $submittedToken = $r->request->get('token');

        // TEST 
        // $submittedToken = $submittedToken. 'hhh';

        // CSRF TOKEN
        if (!$this->isCsrfTokenValid('create_author', $submittedToken)) {
            $r->getSession()->getFlashBag()->add('errors', 'Wrong CSRF token');
            return $this->redirectToRoute('author_create');
        }

        // creating new author 
        $author = new Author;

        $author
            ->setName($r->request->get('author_name'))
            ->setSurname($r->request->get('author_surname'));


        // Error validation 
        $errors = $validator->validate($author);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
                http_response_code(400);
            }
            $r->getSession()->getFlashBag()->add('author_name', $r->request->get('author_name'));
            $r->getSession()->getFlashBag()->add('author_surname', $r->request->get('author_surname'));

            return $this->redirectToRoute('author_create');
        }

        // doctrine tarpininkas tarp db ir entity
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();
        http_response_code(200);

        $r->getSession()->getFlashBag()->add('success', 'Author successfuly added!');

        return $this->redirectToRoute('author_index');
    }

    /**
     * @Route("/author/edit/{id}", name="author_edit", methods={"GET"})
     */
    // EDIT ------------------------------------------------------------------
    public function edit(Request $r, int $id): Response
    {
        // ieskom to autoriaus kurio id yra perduotas
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);

        $author_name = $r->getSession()->getFlashBag()->get('author_name', []);
        $author_surname = $r->getSession()->getFlashBag()->get('author_surname', []);

        // atiduodam viena authoriu
        return $this->render('author/edit.html.twig', [
            'author' => $author,
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'author_name' => $author_name[0] ?? '',
            'author_surname' => $author_surname[0] ?? '',
        ]);
    }
    /**
     * @Route("/author/update/{id}", name="author_update", methods={"POST"})
     */
    //  UPDATE ---------------------------------------------------------------
    public function update(Request $r, $id, ValidatorInterface $validator): Response
    {
        // imam autoriu pagal sena id
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);

        $author
            ->setName($r->request->get('author_name'))
            ->setSurname($r->request->get('author_surname'));
        
        // Error validation 
        $errors = $validator->validate($author);    

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
                http_response_code(400);
            }
            $r->getSession()->getFlashBag()->add('author_name', $r->request->get('author_name'));
            $r->getSession()->getFlashBag()->add('author_surname', $r->request->get('author_surname'));

            return $this->redirectToRoute('author_edit', ['id'=>$author->getId()]);
        }


        // doctrine tarpininkas tarp db ir entity
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();
        http_response_code(200);

        $r->getSession()->getFlashBag()->add('success', 'Author successfuly updated!');

        return $this->redirectToRoute('author_index');
    }

    /**
     * @Route("/author/delete/{id}", name="author_delete", methods={"POST"})
     */
    //  DELETE ---------------------------------------------------------------
    public function delete(Request $r, $id): Response
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
        http_response_code(200);

        $r->getSession()->getFlashBag()->add('success', 'Author successfuly deleted!');

        return $this->redirectToRoute('author_index');
    }
}
