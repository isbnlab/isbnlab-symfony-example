# ISBNLab Symfony example

A small Symfony 7.4 app that uses the official [ISBNLab PHP client](https://github.com/isbnlab/isbnlab-php) to look up books by ISBN with the [ISBNLab book metadata API](https://www.isbnlab.com/api-information). Use it as a starting point, or read it to see how the client fits into a Symfony project.

It shows how to:

- register the client classes (`BooksApi`, `IsbnApi`, `AuthorsApi`) as services, with the token from an environment variable ([config/services.yaml](config/services.yaml));
- show a book: title, authors, series, publish date, publisher, cover and every other edition ([templates/book/show.html.twig](templates/book/show.html.twig));
- convert an ISBN to ISBN-10, ISBN-13, EAN and ASIN;
- follow an id from a book to its author;
- turn API errors (invalid ISBN, not found, plan or daily limit) into clear messages ([src/IsbnLab/ApiError.php](src/IsbnLab/ApiError.php));
- look a book up from the command line ([src/Command/BookCommand.php](src/Command/BookCommand.php));
- test all of it without a token or network access, by swapping the HTTP client for a mock ([tests/](tests/)).

## Run it

You need PHP 8.2 or later and Composer.

1. Clone the repository and install:

   ```bash
   git clone https://github.com/isbnlab/isbnlab-symfony-example.git
   cd isbnlab-symfony-example
   composer install
   ```

2. [Create a free ISBNLab account](https://www.isbnlab.com/register) and copy your API token from your profile. Put it in `.env.local`, which is never committed:

   ```bash
   echo 'ISBNLAB_API_TOKEN=your-token-here' > .env.local
   ```

3. Start a web server and open http://127.0.0.1:8000:

   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

   Or, with the [Symfony CLI](https://symfony.com/download), `symfony serve`.

Or from the command line:

```bash
bin/console isbnlab:book 9780765302304
```

## Pages

| Page | API call | Plan |
|---|---|---|
| `/book/{isbn}` | `BooksApi::getBookByIsbn()` | Free |
| `/isbn/{isbn}` | `IsbnApi::parseIsbn()` | Free |
| `/author/{id}` | `AuthorsApi::getAuthor()` | Standard or Pro |

The Free plan includes 1,000 calls a day. With a Free token the author page shows the API's "needs a Standard or Pro plan" message, which is the error handling at work. See [pricing](https://www.isbnlab.com/pricing).

## Tests

```bash
vendor/bin/phpunit
```

The tests answer every API call from the example responses in `tests/fixtures`, so they need no token.

## License

MIT. Maintained by [ISBNLab](https://www.isbnlab.com/), the book metadata API.
