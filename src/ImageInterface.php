<?php declare(strict_types=1);

namespace HBS\GdImage;

interface ImageInterface
{
    public function getAspectRatio(): float;

    public function getMediaType(): string;

    public function getOrientation(): int;

    public function getSize(): array;
}
