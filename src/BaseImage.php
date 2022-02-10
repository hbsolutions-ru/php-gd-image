<?php declare(strict_types=1);

namespace HBS\GdImage;

use Psr\Log\{
    LoggerInterface,
    NullLogger,
};
use HBS\GdImage\Exception\UnexpectedValueException;

abstract class BaseImage implements ImageInterface
{
    public const HEIGHT = 'height';
    public const WIDTH = 'width';

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

    public function __construct(string $filename, bool $lazy = true, ?LoggerInterface $logger = null)
    {
        $this->filename = $filename;
        $this->lazy = $lazy;
        $this->logger = $logger ?? new NullLogger();

        if ($this->lazy) {
            return;
        }

        $this->getImage();
    }

    abstract protected function createImage(string $filename);

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
}
