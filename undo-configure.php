#!/usr/bin/env php
<?php

/**
 * It runs a command and prints the output
 *
 * @param string command The command to run.
 */
function run(string $command): void
{
    $out = shell_exec($command);

    echo ">>> " . $command . PHP_EOL;
    echo trim((string) $out);
}

run('git reset HEAD --hard');
run('git clean -f -d');
run('rm -rf vendor');
run('rm -f composer.lock');
