<?php declare(strict_types=1);

namespace HBS\GdImage;

use Psr\Log\{
    LoggerInterface,
    NullLogger,
};
use HBS\GdImage\Exception\UnexpectedValueException;

abstract class BaseImage implements ImageInterface
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var bool
     */
    protected $lazy = true;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \GdImage|resource|null
     */
    protected $gdImage = null;

    /**
     * @var int|null
     */
    protected $orientation = null;

    /**
     * @var array|null
     */
    protected $size = null;

    public function __construct(string $filename, bool $lazy = true, ?LoggerInterface $logger = null)
    {
        $this->filename = $filename;
        $this->lazy = $lazy;
        $this->logger = $logger ?? new NullLogger();

        if ($this->lazy) {
            return;
        }

        $this->getImage();
        $this->getSize();
    }

    abstract protected function createImage(string $filename);

    abstract protected function getImageOrientation(string $filename): int;

    abstract protected function saveImage($image, string $filename): bool;

    public function fitToSizeKeepingAspectRatio(string $filename, int $maxWidth, int $maxHeight): bool
    {
        [
            self::SIZE_WIDTH => $fittedWidth,
            self::SIZE_HEIGHT => $fittedHeight,
        ] = $this->getFittedSize($maxWidth, $maxHeight);

        $orientation = $this->getOrientation();
        $image = $this->getImage();

        switch($orientation) {
            case self::ORIENTATION_BOTTOM:
                $image = imagerotate($image,180,0);
                break;
            case self::ORIENTATION_RIGHT:
                $image = imagerotate($image,-90,0);
                break;
            case self::ORIENTATION_LEFT:
                $image = imagerotate($image,90,0);
                break;
        }

        $image = imagescale($image, $fittedWidth, $fittedHeight);

        if (!$image) {
            $this->logger->error(sprintf("Failed to scale image %s", $this->filename));
            return false;
        }

        if (!$this->saveImage($image, $filename)) {
            $this->logger->error(sprintf("Failed to save image to %s", $filename));
            return false;
        }

        return true;
    }

    public function getAspectRatio(): float
    {
        $size = $this->getSize();

        return $size[self::SIZE_WIDTH] / $size[self::SIZE_HEIGHT];
    }

    public function getImage()
    {
        if ($this->gdImage === null) {
            $this->gdImage = $this->createImage($this->filename);
        }

        if (!$this->gdImage) {
            $errorMessage = sprintf("Failed to create image from filename: %s", $this->filename);
            $this->logger->error($errorMessage);
            throw new UnexpectedValueException($errorMessage);
        }

        return $this->gdImage;
    }

    public function getOrientation(): int
    {
        if ($this->orientation === null) {
            $this->orientation = $this->getImageOrientation($this->filename);
        }

        return $this->orientation;
    }

    public function getSize(): array
    {
        if ($this->size === null) {

            $imageSize = getimagesize($this->filename);

            if (!$imageSize) {
                $errorMessage = sprintf("Failed to get image size for %s", $this->filename);
                $this->logger->error($errorMessage);
                throw new UnexpectedValueException($errorMessage);
            }

            $width = intval($imageSize[0]);
            $height = intval($imageSize[1]);

            if (in_array(
                $this->getOrientation(),
                [
                    self::ORIENTATION_RIGHT,
                    self::ORIENTATION_LEFT,
                ],
                true
            )) {
                [$width, $height] = [$height, $width];
            }

            $this->size = [
                self::SIZE_WIDTH => $width,
                self::SIZE_HEIGHT => $height,
            ];
        }

        return $this->size;
    }

    protected function getFittedSize(int $maxWidth, int $maxHeight): array
    {
        [
            self::SIZE_WIDTH => $width,
            self::SIZE_HEIGHT => $height,
        ] = $this->getSize();

        $frameRatio = $maxWidth / $maxHeight;
        $ratio = $width / $height;

        if ($ratio > $frameRatio) {

            if ($width > $maxWidth) {
                return [
                    self::SIZE_WIDTH => $maxWidth,
                    self::SIZE_HEIGHT => (int)($maxWidth / $ratio),
                ];
            } else {
                return [
                    self::SIZE_WIDTH => $width,
                    self::SIZE_HEIGHT => $height,
                ];
            }

        } else {

            if ($height > $maxHeight) {
                return [
                    self::SIZE_WIDTH => (int)($maxHeight * $ratio),
                    self::SIZE_HEIGHT => $maxHeight
                ];
            } else {
                return [
                    self::SIZE_WIDTH => $width,
                    self::SIZE_HEIGHT => $height
                ];
            }

        }
    }
}
