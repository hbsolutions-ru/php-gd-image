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

    protected function getImageOrientation(string $filename): int
    {
        $exifData = exif_read_data($filename);
        if (!$exifData) {
            $this->logger->notice(sprintf("Failed to read EXIF data for %s", $filename));
            return self::ORIENTATION_TOP;
        }

        if (!isset($exifData['Orientation'])) {
            $this->logger->warning(sprintf("Orientation not found in the EXIF data for %s", $filename));
            return self::ORIENTATION_TOP;
        }

        $orientation = intval($exifData['Orientation']);

        if (!in_array($orientation, [
            self::ORIENTATION_TOP,
            self::ORIENTATION_BOTTOM,
            self::ORIENTATION_RIGHT,
            self::ORIENTATION_LEFT,
        ], true)) {
            $this->logger->warning(sprintf("Extraordinary EXIF Orientation %d for %s", $orientation, $filename));
            return self::ORIENTATION_TOP;
        }

        return $orientation;
    }

    protected function saveImage($image, string $filename): bool
    {
        return imagejpeg($image, $filename);
    }
}
