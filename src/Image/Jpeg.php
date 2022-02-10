<?php declare(strict_types=1);

namespace HBS\GdImage\Image;

use HBS\GdImage\BaseImage;

class Jpeg extends BaseImage
{
    private const MEDIA_TYPE = 'image/jpeg';

    public function getMediaType(): string
    {
        return self::MEDIA_TYPE;
    }

    protected function createImage(string $filename)
    {
        return imagecreatefromjpeg($filename);
    }
}
