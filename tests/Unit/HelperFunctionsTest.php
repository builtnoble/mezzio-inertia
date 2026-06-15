<?php

declare(strict_types=1);

it('normalizes path string', function (string $input, string $output) {
    expect(normalizePath($input))->toBe($output);
})->with([
    'duplicates slashes' => ['///path//with///duplicate/slashes//', 'path/with/duplicate/slashes'],
    'outer left slash' => ['/dir/subdir/filename.ext', 'dir/subdir/filename.ext'],
    'outer right slash' => ['dir/subdir/filename.ext/', 'dir/subdir/filename.ext'],
    'outer slashes' => ['/dir/subdir/filename.ext/', 'dir/subdir/filename.ext'],
    'only a slash' => ['/', ''],
]);
