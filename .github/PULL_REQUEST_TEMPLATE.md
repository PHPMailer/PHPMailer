Before submitting your pull request, check whether your code adheres to PHPMailer
coding standards by running the following command:

`./vendor/bin/php-cs-fixer --diff --dry-run --verbose fix `

And committing eventual changes. It's important that this command uses the specific version of php-cs-fixer configured for PHPMailer, so run `composer install` within the PHPMailer folder to use the exact version it needs.
