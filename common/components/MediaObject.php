<?php

namespace common\components;

use common\components\exceptions\Exception;
use Yii;
use yii\base\BaseObject;

/**
 * Class MediaObject represents an entity comprised of a record in the database and one or more files associated with it
 */
class MediaObject extends BaseObject
{

    /** @var ImageFile Image content */
    protected $_file;
    /** @var string Path to file */
    protected $_name;
    /** @var string Images directory */
    protected $_mediaPath;
    /** @var string Images host */
    protected $_mediaHost;

    /**
     * @param string $base64OrPath Path or source of a file
     *
     * @throws Exception
     */
    function __construct(string $base64OrPath)
    {
        parent::__construct();

        $this->_mediaPath = rtrim(self::getMediaPath(), '/');
        $this->_mediaHost = rtrim(Yii::$app->params['media']['url'], '/');

        if (strlen($base64OrPath) < 100 && file_exists($base64OrPath)) { // is file path
            $this->_file = new ImageFile($base64OrPath);
        } else { // is string in base64
            $base64 = base64_decode($base64OrPath, true);
            if ($base64 === false) {
                throw new Exception('Bad file');
            }
            $this->_file = new ImageFile($base64);
        }

        do {
            $this->_name = self::genName();
        } while (file_exists($this->genFileLocation()));
    }

    /**
     * Save file to a disk
     *
     * @return string
     * @throws Exception
     */
    public function save(): string
    {
        $path = $this->genFileLocation();
        $this->_file->format('jpg', [], true)
            ->save($path);

        return $this->genFileLink();
    }

    /**
     * Generates a random name for the media. File
     *
     * @return string A 30 char long string
     */
    public static function genName(): string
    {
        return Tools::genRandomStr(30);
    }

    /**
     * Generates a file location based on name
     *
     * @return string Absolute path to the file
     */
    public function genFileLocation(): string
    {
        return $this->_mediaPath . '/' . static::genRelativeFileLocation($this->_name);
    }

    /**
     * Generate a hyperlink to the file based name
     *
     * @return string link to the file
     */
    public function genFileLink(): string
    {
        return $this->_mediaHost . '/' . static::genRelativeFileLocation($this->_name);
    }

    /**
     * Generate file location
     *
     * @param string $name Name of a media
     * @param string $extension Extension of a media
     *
     * @return string
     */
    private function genRelativeFileLocation(string $name, string $extension = 'jpg'): string
    {
        return substr($name, 0, 2) . '/' . substr($name, 2, 3) . '/' . substr($name, 2 + 3, 4) . '/' . substr($name, 2 + 3 + 4) . '.' . $extension;
    }

    /**
     * @param string $url
     * @throws Exception
     */
    public static function deleteByUrl(string $url)
    {
        $path = realpath(self::getMediaPath() . '/' . parse_url($url, PHP_URL_PATH));

        if ($path !== false) {
            unlink($path);
        }
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public static function getMediaPath(): string
    {
        $path = realpath(Yii::getAlias(Yii::$app->params['media']['path']));
        if ($path === false) {
            throw new Exception('Media directory is not valid path');
        }

        return $path;
    }
}
