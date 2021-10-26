#!/usr/bin/env php
<?php

function ask(string $question, string $default = ''): string {
    $answer = readline($question . ($default ? " ({$default})" : null) . ': ');

    if (! $answer) {
        return $default;
    }

    return $answer;
}

function confirm(string $question, bool $default = false): bool {
    $answer = ask($question . ' (' . ($default ? 'Y/n' : 'y/N') . ')');

    if (! $answer) {
        return $default;
    }

    return strtolower($answer) === 'y';
}

function writeln(string $line): void {
    echo $line . PHP_EOL;
}

function run(string $command): string {
    return trim(shell_exec($command));
}

function str_after(string $subject, string $search): string {
    $pos = strrpos($subject, $search);

    if ($pos === false) {
        return $subject;
    }

    return substr($subject, $pos + strlen($search));
}

function slugify(string $subject, string $separator = '-'): string {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', $separator, $subject), $separator));
}

function pascal_case(string $subject): string {
    return str_replace(' ', '', title_case($subject));
}

function title_case(string $subject): string {
    return ucwords(str_replace(['-', '_'], ' ', $subject));
}

function replace_in_file(string $file, array $replacements): void {
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contents
        )
    );
}

$gitName = run('git config user.name');
$authorName = ask('Author name', $gitName);

$gitEmail = run('git config user.email');
$authorEmail = ask('Author email', $gitEmail);

$usernameGuess = explode(':', run('git config remote.origin.url'))[1];
$usernameGuess = dirname($usernameGuess);
$usernameGuess = basename($usernameGuess);
$authorUsername = ask('Author username', $usernameGuess);

$vendorName = ask('Vendor name', $authorName);
$vendorSlug = ask('Vendor slug', $authorUsername);
$vendorNamespace = ask('Vendor namespace', pascal_case($vendorSlug));

$currentDirectory = getcwd();
$folderName = basename($currentDirectory);

$packageName = ask('Package name', $folderName);
$packageSlug = ask('Package slug', slugify($packageName));
$packageNamespace = pascal_case($packageName);
$className = ask('Class name', $packageNamespace);
$description = ask('Package description', "This is my package {$packageName}");

writeln('------');
writeln("Author     : {$authorName} ({$authorUsername}, {$authorEmail})");
writeln("Vendor     : {$vendorName} ({$vendorSlug})");
writeln("Package    : {$packageName} <{$description}>");
writeln("Namespace  : {$vendorNamespace}\\{$packageNamespace}");
writeln("Packagist  : {$vendorSlug}/{$packageName}");
writeln("Github     : https://github.com/{$vendorSlug}/{$packageSlug}");
writeln("Class name : {$className}");
writeln('------');

writeln('This script will replace the above values in all relevant files in the project directory.');

if (! confirm('Modify files?', true)) {
    exit(1);
}

$replacements = [
    'author-name' => $authorName,
    'author-username' => $authorUsername,
    'author-email@domain.com' => $authorEmail,
    'vendor-name' => $vendorName,
    'vendor-slug' => $vendorSlug,
    'package-name' => $packageName,
    'package-slug' => $packageSlug,
    'package-description' => $description,
    'VendorName' => $vendorName,
    'VendorNamespace' => $vendorNamespace,
    'PackageNamespace' => $packageName,
    'SkeletonClass' => $className,
];
$files = explode(PHP_EOL, run('grep -E -r -l -i "' . implode('|', array_keys($replacements)) . '" --exclude-dir=vendor ./* ./.github/* | grep -v ' . basename(__FILE__)));

foreach ($files as $file) {
    replace_in_file($file, $replacements);

    match (true) {
        str_contains($file, 'src/SkeletonClass.php') => rename($file, './src/' . $className . '.php'),
        default => [],
    };
}

confirm('Execute `composer install` and run tests?') && run('composer install && composer test');
confirm('Let this script delete itself?', true) && unlink(__FILE__);
