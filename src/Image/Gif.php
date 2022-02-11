<?php declare(strict_types=1);

namespace HBS\GdImage\Image;

use HBS\GdImage\BaseImage;

class Gif extends BaseImage
{
    private const MEDIA_TYPE = 'image/gif';

    public function getMediaType(): string
    {
        return self::MEDIA_TYPE;
    }

    protected function createImage(string $filename)
    {
        return imagecreatefromgif($filename);
    }

    protected function getImageOrientation(string $filename): int
    {
        return self::ORIENTATION_TOP;
    }

    protected function saveImage($image, string $filename): bool
    {
        return imagegif($image, $filename);
    }
}
