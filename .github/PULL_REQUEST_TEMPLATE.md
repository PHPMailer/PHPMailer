Before submitting your pull request, check whether your code adheres to PHPMailer coding standards (which is mostly [PSR-12](https://www.php-fig.org/psr/psr-12/)) by running [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer):

    ./vendor/bin/phpcs

Any problems reported can probably be fixed automatically by using its partner tool, PHP code beautifier:

    ./vendor/bin/phpcbf
