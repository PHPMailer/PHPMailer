#!/bin/sh

set -eu

/opt/phpdoc/bin/phpdoc
chmod -R a+rX /home/runner/work/PHPMailer/PHPMailer
