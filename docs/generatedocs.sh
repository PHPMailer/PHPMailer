#!/bin/sh
# Regenerate PHPMailer documentation
rm -rf phpdocs/*
phpdoc --directory .. --target ./phpdoc --ignore test/,examples/,extras/,test_script/ --sourcecode --force --title PHPMailer
