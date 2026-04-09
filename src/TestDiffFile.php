<?php

namespace PHPMailer\PHPMailer;

class TestDiffFile
{
    // PHP-W1075: unused private property
    private string $unused = "never read";

    // PHP-A1004: insecure hash
    public function hashPassword(string $password): string
    {
        return md5($password);
    }

    // PHP-W1078: error suppression
    public function readFile(string $path): string|false
    {
        return @file_get_contents($path);
    }

    // PHP-A1009: exec
    public function runCommand(string $cmd): void
    {
        exec($cmd);
    }

    // PHP-W1085: empty else
    public function check(bool $flag): void
    {
        if ($flag) {
            echo "yes";
        } else {
        }
    }
}
