<?php

namespace League\ColorExtractor;

use ArrayIterator;
use Countable;
use GdImage;
use InvalidArgumentException;
use IteratorAggregate;

final class Palette implements Countable, IteratorAggregate
{
    private array $colors;

    public function __construct()
    {
        $this->colors = [];
    }

    public function count(): int
    {
        return \count($this->colors);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->colors);
    }

    public function getColorCount(int $color): int
    {
        return $this->colors[$color];
    }

    public function getMostUsedColors(int $limit = null): array
    {
        return \array_slice($this->colors, 0, $limit, true);
    }

    public static function fromFilename(string $filename, ?int $backgroundColor = null): Palette
    {
        $image = imagecreatefromstring(file_get_contents($filename));
        $palette = self::fromGD($image, $backgroundColor);
        imagedestroy($image);

        return $palette;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromGD(GdImage $image, ?int $backgroundColor = null): Palette
    {
        if (null !== $backgroundColor && (!is_numeric($backgroundColor) || $backgroundColor < 0 || $backgroundColor > 16777215)) {
            throw new InvalidArgumentException(sprintf('"%s" does not represent a valid color', $backgroundColor));
        }

        $palette = new self();

        $areColorsIndexed = !imageistruecolor($image);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $palette->colors = [];

        $backgroundColorRed = ($backgroundColor >> 16) & 0xFF;
        $backgroundColorGreen = ($backgroundColor >> 8) & 0xFF;
        $backgroundColorBlue = $backgroundColor & 0xFF;

        for ($x = 0; $x < $imageWidth; ++$x) {
            for ($y = 0; $y < $imageHeight; ++$y) {
                $color = imagecolorat($image, $x, $y);
                if ($areColorsIndexed) {
                    $colorComponents = imagecolorsforindex($image, $color);
                    $color = ($colorComponents['alpha'] * 16777216) +
                             ($colorComponents['red'] * 65536) +
                             ($colorComponents['green'] * 256) +
                             ($colorComponents['blue']);
                }

                if ($alpha = $color >> 24) {
                    if (null === $backgroundColor) {
                        continue;
                    }

                    $alpha /= 127;
                    $color = (int) (($color >> 16 & 0xFF) * (1 - $alpha) + $backgroundColorRed * $alpha) * 65536 +
                             (int) (($color >> 8 & 0xFF) * (1 - $alpha) + $backgroundColorGreen * $alpha) * 256 +
                             (int) (($color & 0xFF) * (1 - $alpha) + $backgroundColorBlue * $alpha);
                }

                isset($palette->colors[$color]) ?
                    ++$palette->colors[$color] :
                    $palette->colors[$color] = 1;
            }
        }

        arsort($palette->colors);

        return $palette;
    }
}
