<?php

namespace common\components;

use common\components\exceptions\Exception;
use Imagick;

/**
 * Class ImageFile
 */
class ImageFile
{

    /** @var Imagick Instance of the imagick class with image file loaded */
    protected $_imagick;

    /** @var string Path to file */
    protected $path;

    /** @inheritdoc */
    function __construct(string $path)
    {
        if (empty($path)) {
            throw new Exception('Bad image path: ' . $path);
        }
        $this->path = $path;
        $this->load($path);
    }

    /** When cloning ImageFile, also clone an instance of Imagick */
    function __clone()
    {
        if (!is_null($this->_imagick)) {
            $this->_imagick = clone $this->_imagick;
        }
    }

    /**
     * Load image file or blob
     *
     * @param string $pathOrBlob
     *
     * @return $this
     */
    public function load(string $pathOrBlob): self
    {
        $imagick = $this->getImagick();
        $imagick->clear();
        if (FileHelper::isFile($pathOrBlob)) {
            $imagick->readImage($pathOrBlob);
        } else {
            $imagick->readImageBlob($pathOrBlob);
        }
        $this->setWhiteBackground();
        $this->path = $pathOrBlob;

        return $this;
    }

    /**
     * Returns current MIME type.
     *
     * @return string Image mime type
     */
    public function getMimeType(): string
    {
        return $this->getImagick()
            ->getImageMimeType();
    }

    /**
     * Processes itself according to supplied format
     *
     * @param string $extension Extension of a file
     * @param array $config Sizes and need to stretch
     * @param bool $force Force change format of a file
     *
     * @return $this
     * @throws Exception
     */
    public function format(string $extension, array $config = [], bool $force = false): self
    {
        $im = $this->getImagick();

        try {
            if (!empty($config)) {
                if (!$config['stretch'] || $im->getImageWidth() > $config['width'] || $im->getImageHeight() > $config['height']) {
                    $im->resizeImage($config['width'], $config['height'], imagick::FILTER_CATROM, 1, $config['stretch']);
                }
            }

            if ($force || $this->getMimeType() !== FileHelper::getMimeTypeByExt($extension)) {
                $im->setImageFormat($extension);
            }
        } catch (\Exception $ie) {
            throw new Exception('Failed to change image format');
        }

        return $this;
    }

    /**
     * Returns image width (with all changes, even if not yet saved)
     *
     * @return int Width in pixels
     */
    public function getWidth()
    {
        return $this->getImagick()
            ->getImageWidth();
    }

    /**
     * Returns image width (with all changes, even if not yet saved)
     *
     * @return int Height in pixels
     */
    public function getHeight()
    {
        return $this->getImagick()
            ->getImageHeight();
    }

    /**
     * Save file
     *
     * @param string|null $path
     *
     * @throws Exception
     */
    public function save($path = null): void
    {
        if (empty($this->path)) {
            throw new Exception('Nothing to save');
        }

        $path = $path ?? $this->path;

        if (!FileHelper::createDirectory(dirname($path))) {
            throw new Exception('Failed to create directory "' . dirname($path) . '"');
        }

        try {
            $saved = $this->getImagick()
                ->writeImage($path);
            if (!$saved) {
                throw new Exception('Unknown');
            }
        } catch (\Exception $e) {
            throw new Exception('Failed to write image to disk using Imagick: "' . $path . '". Because: ' . $e->getMessage());
        }
    }

    /**
     * Retrieves or creates Imagick instance
     *
     * @return Imagick
     */
    protected function getImagick(): Imagick
    {
        if (is_null($this->_imagick)) {
            $this->_imagick = new Imagick();
        }

        return $this->_imagick;
    }

    /**
     * Set a white background for the transparent png/gif files
     */
    protected function setWhiteBackground(): void
    {
        $imagick = $this->getImagick();

        $imagick->setImageBackgroundColor('white');
        $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
    }
}