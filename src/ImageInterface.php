<?php declare(strict_types=1);

namespace HBS\GdImage;

interface ImageInterface
{
    public const ORIENTATION_TOP = 1;
    public const ORIENTATION_BOTTOM = 3;
    public const ORIENTATION_RIGHT = 6;
    public const ORIENTATION_LEFT = 8;

    public const SIZE_HEIGHT = 'height';
    public const SIZE_WIDTH = 'width';

    public function fitToSizeKeepingAspectRatio(string $filename, int $maxWidth, int $maxHeight): bool;

    public function getAspectRatio(): float;

    public function getMediaType(): string;

    public function getOrientation(): int;

    public function getSize(): array;
}
