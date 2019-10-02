FROM phpdoc/phpdoc

LABEL "repository"="https://github.com/PHPMailer/PHPMailer"

LABEL "com.github.actions.name"="Build Docs"
LABEL "com.github.actions.description"="Build Docs with phpDocumentor"
LABEL "com.github.actions.icon"="file-text"
LABEL "com.github.actions.color"="blue"

# don't show errors
RUN echo "display_errors = Off" > $PHP_INI_DIR/conf.d/errors.ini

COPY entrypoint.sh /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
