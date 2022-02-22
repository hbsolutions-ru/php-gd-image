<?php declare(strict_types=1);

namespace HBS\GdImage;

use Psr\Log\{
    LoggerInterface,
    NullLogger,
};
use HBS\GdImage\Exception\ClassNotFound;

class Factory
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Factory constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param string $filename
     * @param string $mediaType
     * @param bool $lazy
     * @return ImageInterface
     * @throws ClassNotFound
     */
    public function get(string $filename, string $mediaType, bool $lazy = true): ImageInterface
    {
        $className = __NAMESPACE__ . '\\Image\\' . $this->mapTypeToClassName($mediaType);

        if (!class_exists($className)) {
            throw new ClassNotFound(sprintf("Class '%s' not found", $className));
        }

        return new $className($filename, $lazy, $this->logger);
    }

    private function mapTypeToClassName(string $type): string
    {
        $parts = explode('/', $type);

        return ucfirst(strtolower($parts[1] ?? $parts[0]));
    }
}
