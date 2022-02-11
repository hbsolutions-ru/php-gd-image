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
     * @var \GdImage|null
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
}
