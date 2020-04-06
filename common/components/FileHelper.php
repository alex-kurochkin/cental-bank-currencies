<?php

namespace common\components;

use Exception;

/** File Helper */
class FileHelper extends \yii\helpers\FileHelper
{

    /**
     * Delete file
     *
     * @param string $file File path
     *
     * @return bool
     */
    public static function deleteFile(string $file)
    {
        if (!is_file($file)) {
            return false;
        }

        return unlink($file);
    }

    /**
     * Loads MIME types from the specified file.
     *
     * @return array the mapping from file extensions to MIME types
     */
    public static function getMimeTypes()
    {
        return self::loadMimeTypes(null);
    }

    /**
     * Determines the MIME type based on the extension name of the specified file.
     * This method will use a local map between extension names and MIME types.
     *
     * @param string $ext extension of the file.
     *
     * @return string|null the MIME type. Null is returned if the MIME type cannot be determined.
     */
    public static function getMimeTypeByExt($ext)
    {
        $mimeTypes = static::loadMimeTypes(null);
        $ext = strtolower(trim($ext, '.'));

        if (!isset($mimeTypes[$ext])) {
            return null;
        }

        return $mimeTypes[$ext];
    }

    /**
     * Copy of a file to the temporary directory of system
     *
     * @param string $filePath
     * @param string|null $extension
     *
     * @return string
     */
    public static function copyToTempDir($filePath, $extension = null)
    {
        $tmpPath = tempnam(ini_get('upload_tmp_dir'), 'tmp_');
        if (!is_null($extension)) {
            $tmpPath .= $extension;
        }
        copy($filePath, $tmpPath);

        return $tmpPath;
    }

    /**
     * Tells whether the filename is a regular file
     *
     * @param string $filePath
     *
     * @return bool
     */
    public static function isFile(string $filePath)
    {
        $f = pathinfo($filePath, PATHINFO_EXTENSION);

        if (strlen($f) === 0) {
            return false;
        }

        try {
            return file_exists($filePath);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns filename component of path.
     *
     * Bug fix with the backslashes on *nix
     *
     * For example, on *nix,
     * ```php
     * basename('/path/to/Foo'); // return 'Foo'
     * basename('\path\to\Foo'); // return '\path\to\Foo'
     * ```
     * and
     * ```php
     * FileHelper::baseName('/path/to/Foo'); // return 'Foo'
     * FileHelper::baseName('\path\to\Foo'); // return 'Foo'
     * ```
     *
     * @param string $path A path
     * @param string $suffix [optional] If the filename ends in suffix this will also be cut off.
     *
     * @return string the base name of the given path.
     */
    public static function baseName($path, $suffix = null)
    {
        return basename(str_replace("\\", "/", $path), $suffix);
    }
}
