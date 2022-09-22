#!/usr/bin/env php
<?php

/**
 * It asks a question, and returns the answer
 *
 * @param  string $question The question to ask the user.
 * @param  string $default The default value to return if the user doesn't enter anything.
 * @return string The answer to the question.
 */
function ask(string $question, string $default = ''): string
{
    $answer = readline($question . ($default ? " ({$default})" : null) . ': ');

    if (!$answer) {
        return $default;
    }

    return $answer;
}

/**
 * It asks the user a question, and returns true if the user answers "y" or "yes", and false otherwise
 *
 * @param  string $question The question to ask the user.
 * @param  bool   $default  The default value to return if the user just presses enter.
 * @return bool A boolean value.
 */
function confirm(string $question, bool $default = false): bool
{
    $answer = ask($question . ' (' . ($default ? 'Y/n' : 'y/N') . ')');

    if (!$answer) {
        return $default;
    }

    return strtolower($answer) === 'y';
}

/**
 * Prints it to the screen with a newline character at the end.
 *
 * @param string $line The line to write to the console.
 */
function writeln(string $line): void
{
    echo $line . PHP_EOL;
}

/**
 * It runs a command and returns the output
 *
 * @param  string $command The command to run.
 * @return string The output of the command.
 */
function run(string $command): string
{
    $out = shell_exec($command);
    return trim((string) $out);
}

/**
 * Return the string after the last position of the search string.
 *
 * @param  string $subject The string to search in
 * @param  string $search The string to search for.
 * @return string
 */
function str_after(string $subject, string $search): string
{
    $pos = strrpos($subject, $search);

    if ($pos === false) {
        return $subject;
    }

    return substr($subject, $pos + strlen($search));
}

/**
 * It takes a slugify string
 *
 * @param  string $subject The string to slugify.
 * @return string
 */
function slugify(string $subject): string
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $subject), '-'));
}

/**
 * It takes a title cased string
 *
 * @param  string $subject The string to be converted to title case.
 * @return string
 */
function title_case(string $subject): string
{
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $subject)));
}

/**
 * It replaces all occurrences of the keys in the file with the values
 *
 * @param  string $file         The file to replace the contents in.
 * @param  array  $replacements An array of key/value pairs.
 * @return void
 */
function replace_in_file(string $file, array $replacements): void
{
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

/**
 * If the content starts with the prefix, remove the prefix from the content.
 *
 * @param  string $prefix The prefix to remove from the content.
 * @param  string $content The content to remove the prefix from.
 * @return string The string without the prefix.
 */
function remove_prefix(string $prefix, string $content): string
{
    if (str_starts_with($content, $prefix)) {
        return substr($content, strlen($prefix));
    }

    return $content;
}

/**
 * It removes the given dependencies from the `require-dev` section of the `composer.json` file
 *
 * @param  array $names An array of package names to remove from the composer.json file.
 * @return void
 */
function remove_composer_deps(array $names): void
{
    $data = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);

    foreach ($data['require-dev'] as $name => $version) {
        if (in_array($name, $names, true)) {
            unset($data['require-dev'][$name]);
        }
    }

    file_put_contents(__DIR__ . '/composer.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

/**
 * It removes a script from the composer.json file
 *
 * @param  string $script The name of the script to remove.
 * @return void
 */
function remove_composer_script(string $script): void
{
    $data = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);

    foreach ($data['scripts'] as $name => $command) {
        if ($script === $name) {
            unset($data['scripts'][$name]);
            break;
        }
    }

    file_put_contents(__DIR__ . '/composer.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

/**
 * It removes the paragraphs between the `<!--delete-->` and `<!--/delete-->` comments in the given file
 *
 * @param string $file The path to the file to be modified.
 */
function remove_readme_paragraphs(string $file): void
{
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        preg_replace('/<!--delete-->.*<!--\/delete-->/s', '', $contents) ?: $contents
    );
}

/**
 * If the file exists and is a file, delete it.
 *
 * @param string $filename The name of the file to delete.
 */
function safe_unlink(string $filename)
{
    if (file_exists($filename) && is_file($filename)) {
        unlink($filename);
    }
}

/**
 * It returns the directory separator
 *
 * @param  string $path The path to the file or directory.
 * @return string the path with the correct separator for the operating system.
 */
function replace_separator(string $path): string
{
    return str_replace('/', DIRECTORY_SEPARATOR, $path);
}

/**
 * It returns an array of all files in the project that contain the strings
 * `:author`, `:vendor`, `:package`, `VendorName`, `skeleton`, `vendor_name`, `vendor_slug`, or `author@domain.com`
 *
 * @return array An array of files to replace.
 */
function find_files_to_replace(): array
{
    if (str_starts_with(strtoupper(PHP_OS), 'WIN')) {
        // Windows
        return preg_split('/\\r\\n|\\r|\\n/', run('dir /S /B * | findstr /v /i .git\ | findstr /v /i vendor | findstr /v /i ' . basename(__FILE__) . ' | findstr /r /i /M /F:/ ":author :vendor :package VendorName skeleton vendor_name vendor_slug author@domain.com"'));
    } else {
        // Unix
        return explode(PHP_EOL, run('grep -E -r -l -i ":author|:vendor|:package|VendorName|skeleton|vendor_name|vendor_slug|author@domain.com" --exclude-dir=vendor ./* ./.github/* | grep -v ' . basename(__FILE__)));
    }
}

// ===============
// Main
// ===============

$gitName = run('git config user.name');
$authorName = ask('Author name', $gitName);

$gitEmail = run('git config user.email');
$authorEmail = ask('Author email', $gitEmail);

$usernameGuess = explode(':', run('git config remote.origin.url'))[1];
$usernameGuess = dirname($usernameGuess);
$usernameGuess = basename($usernameGuess);
$authorUsername = ask('Author username', $usernameGuess);

$vendorName = ask('Vendor name', $authorUsername);
$vendorEmail = ask('Vendor email', $authorEmail);
$vendorSlug = slugify($vendorName);
$vendorNamespace = ucwords($vendorName);
$vendorNamespace = ask('Vendor namespace', $vendorNamespace);

$currentDirectory = getcwd();
$folderName = basename($currentDirectory);

$packageName = ask('Package name', $folderName);
$packageSlug = slugify($packageName);

$className = title_case($packageName);
$className = ask('Class name', $className);
$description = ask('Package description', "This is my package {$packageSlug}");

$usePsalm = confirm('Enable Psalm?', true);
$useDependabot = confirm('Enable Dependabot?', true);
$useUpdateChangelogWorkflow = confirm('Use automatic changelog updater workflow?', true);

writeln('------');
writeln("Author     : {$authorName} ({$authorUsername}, {$authorEmail})");
writeln("Vendor     : {$vendorName} ({$vendorSlug}, {$vendorEmail})");
writeln("Package    : {$packageSlug} <{$description}>");
writeln("Namespace  : {$vendorNamespace}\\{$className}");
writeln("Class name : {$className}");
writeln('---');
writeln('Packages & Utilities');
writeln('Use Psalm            : ' . ($usePsalm ? 'yes' : 'no'));
writeln('Use Dependabot       : ' . ($useDependabot ? 'yes' : 'no'));
writeln('Use Auto-Changelog   : ' . ($useUpdateChangelogWorkflow ? 'yes' : 'no'));
writeln('------');

writeln('This script will replace the above values in all relevant files in the project directory.');

if (!confirm('Modify files?', true)) {
    exit(1);
}

$files = find_files_to_replace();

foreach ($files as $file) {
    replace_in_file($file, [
        ':author_name' => $authorName,
        ':author_username' => $authorUsername,
        'author@domain.com' => $authorEmail,
        ':vendor_name' => $vendorName,
        ':vendor_slug' => $vendorSlug,
        'vendor@domain.com' => $vendorEmail,
        'VendorName' => $vendorNamespace,
        ':package_name' => $packageName,
        ':package_slug' => $packageSlug,
        'Skeleton' => $className,
        'skeleton' => $packageSlug,
        ':package_description' => $description,
    ]);

    match (true) {
        str_contains($file, replace_separator('src/Skeleton.php')) => rename($file, replace_separator('./src/' . $className . '.php')),
        str_contains($file, 'README.md') => remove_readme_paragraphs($file),
        default => [],
    };
}

if (!$usePsalm) {
    safe_unlink(__DIR__ . '/psalm.xml.dist');
    safe_unlink(__DIR__ . '/.github/workflows/psalm.yml');

    remove_composer_deps([
        'vimeo/psalm',
    ]);

    remove_composer_script('analyse');
}

if (!$useDependabot) {
    safe_unlink(__DIR__ . '/.github/dependabot.yml');
    safe_unlink(__DIR__ . '/.github/workflows/dependabot-auto-merge.yml');
}

if (!$useUpdateChangelogWorkflow) {
    safe_unlink(__DIR__ . '/.github/workflows/update-changelog.yml');
}

confirm('Execute `composer install` and run tests?') && run('composer install && composer test');

confirm('Let this script delete itself?', true) && unlink(__FILE__) && unlink(__DIR__ . DIRECTORY_SEPARATOR . 'undo-configure.php');
