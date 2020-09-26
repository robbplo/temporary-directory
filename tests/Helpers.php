<?php

function deleteDirectory($path)
{
    if (is_link($path)) {
        return unlink($path);
    }

    if (! file_exists($path)) {
        return true;
    }

    if (! is_dir($path)) {
        return unlink($path);
    }

    foreach (new FilesystemIterator($path) as $item) {
        if (! deleteDirectory($item)) {
            return false;
        }
    }

    return rmdir($path);
}
