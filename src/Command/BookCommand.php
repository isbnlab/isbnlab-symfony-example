<?php

namespace App\Command;

use App\IsbnLab\ApiError;
use ISBNLab\Api\BooksApi;
use ISBNLab\ApiException;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Prints one book: bin/console isbnlab:book 9780765302304
 */
#[AsCommand(name: 'isbnlab:book', description: 'Look up a book by ISBN with the ISBNLab API')]
final class BookCommand
{
    public function __construct(private readonly BooksApi $books)
    {
    }

    public function __invoke(SymfonyStyle $io, #[Argument('ISBN-10 or ISBN-13')] string $isbn): int
    {
        try {
            $book = $this->books->getBookByIsbn($isbn);
        } catch (ApiException $e) {
            $io->error(ApiError::from($e)->message);

            return Command::FAILURE;
        }

        $authors = array_map(static fn ($author) => $author->getName(), $book->getAuthors() ?? []);
        $publishers = array_map(static fn ($publisher) => $publisher->getName(), $book->getPublishers() ?? []);
        $series = $book->getSeries();

        $io->title((string) $book->getTitle());
        $io->definitionList(
            ['Subtitle' => $book->getSubtitle() ?? '-'],
            ['Authors' => implode(', ', $authors) ?: '-'],
            ['Publisher' => implode(', ', $publishers) ?: '-'],
            ['Published' => $book->getPublishDate()?->format('Y') ?? '-'],
            ['Format' => $book->getPhysicalFormat() ?? '-'],
            ['Pages' => $book->getNumberOfPages() ?? '-'],
            ['Series' => null !== $series?->getName() ? $series->getName().(null !== $series->getNumber() ? ' #'.$series->getNumber() : '') : '-'],
            ['ISBN-10' => $book->getIsbn10() ?? '-'],
            ['ISBN-13' => $book->getIsbn13() ?? '-'],
            ['Other editions' => \count($book->getOtherEditions() ?? [])],
            ['On ISBNLab' => $book->getLink() ?? '-'],
        );

        return Command::SUCCESS;
    }
}
