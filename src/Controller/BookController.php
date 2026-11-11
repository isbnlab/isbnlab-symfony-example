<?php

namespace App\Controller;

use App\IsbnLab\ApiError;
use ISBNLab\Api\AuthorsApi;
use ISBNLab\Api\BooksApi;
use ISBNLab\Api\IsbnApi;
use ISBNLab\ApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookController extends AbstractController
{
    public function __construct(
        private readonly BooksApi $books,
        private readonly IsbnApi $isbn,
        private readonly AuthorsApi $authors,
    ) {
    }

    /** The search form. Submitting it goes to the book page for that ISBN. */
    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(Request $request): Response
    {
        $isbn = preg_replace('/[^0-9Xx]/', '', (string) $request->query->get('isbn', ''));
        if ('' !== $isbn) {
            return $this->redirectToRoute('book', ['isbn' => $isbn]);
        }

        return $this->render('book/home.html.twig');
    }

    /** One book: Free plan. */
    #[Route('/book/{isbn}', name: 'book', methods: ['GET'])]
    public function book(string $isbn): Response
    {
        try {
            $book = $this->books->getBookByIsbn($isbn);
        } catch (ApiException $e) {
            return $this->error(ApiError::from($e), $isbn);
        }

        return $this->render('book/show.html.twig', ['book' => $book]);
    }

    /** ISBN-10, ISBN-13, EAN and ASIN for any valid ISBN: Free plan. */
    #[Route('/isbn/{isbn}', name: 'isbn', methods: ['GET'])]
    public function isbn(string $isbn): Response
    {
        try {
            $details = $this->isbn->parseIsbn($isbn);
        } catch (ApiException $e) {
            return $this->error(ApiError::from($e), $isbn);
        }

        return $this->render('book/isbn.html.twig', ['details' => $details]);
    }

    /** An author, by the id a book response gives: Standard or Pro plan. */
    #[Route('/author/{id}', name: 'author', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function author(string $id): Response
    {
        try {
            $author = $this->authors->getAuthor($id);
        } catch (ApiException $e) {
            return $this->error(ApiError::from($e));
        }

        return $this->render('book/author.html.twig', ['author' => $author]);
    }

    private function error(ApiError $error, ?string $isbn = null): Response
    {
        return $this->render('book/error.html.twig', ['error' => $error, 'isbn' => $isbn], new Response(status: $error->status));
    }
}
