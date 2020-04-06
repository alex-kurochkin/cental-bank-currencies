<?php

namespace common\domain\utils;

use InvalidArgumentException;

class Files
{

    public static function readAsText(string $path): string
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("File does not exist: $path");
        }

        $handle = fopen($path, 'rt');
        if ($handle === false) {
            throw new InvalidArgumentException("File is not readable: $path");
        }

        flock($handle, LOCK_SH);
        $content = file_get_contents($path);
        flock($handle, LOCK_UN);
        fclose($handle);

        if ($content === false) {
            throw new InvalidArgumentException("File is not readable: $path");
        }

        return $content;
    }

    public static function writeString(string $path, string $content, int $mode = 0644): bool
    {
        $result = file_put_contents($path, $content, LOCK_EX);
        if ($result === false) {
            return false;
        }

        chmod($path, $mode);
        return true;
    }
}
