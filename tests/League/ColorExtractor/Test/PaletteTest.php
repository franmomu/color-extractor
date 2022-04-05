<?php

namespace League\ColorExtractor\Test;

use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;
use PHPUnit\Framework\TestCase;

final class PaletteTest extends TestCase
{
    private string $jpegPath = './tests/assets/test.jpeg';
    private string $gifPath = './tests/assets/test.gif';
    private string $pngPath = './tests/assets/test.png';
    private string $transparentPngPath = './tests/assets/red-transparent-50.png';

    public function testJpegExtractSingleColor(): void
    {
        $extractor = new ColorExtractor(Palette::fromFilename($this->jpegPath));
        $colors = $extractor->extract(1);

        $this->assertIsArray($colors);
        $this->assertCount(1, $colors);
        $this->assertEquals(15985688, $colors[0]);
    }

    public function testGifExtractSingleColor(): void
    {
        $extractor = new ColorExtractor(Palette::fromFilename($this->gifPath));
        $colors = $extractor->extract(1);

        $this->assertIsArray($colors);
        $this->assertCount(1, $colors);
        $this->assertEquals(12022491, $colors[0]);
    }

    public function testPngExtractSingleColor(): void
    {
        $extractor = new ColorExtractor(Palette::fromFilename($this->pngPath));
        $colors = $extractor->extract(1);

        $this->assertIsArray($colors);
        $this->assertCount(1, $colors);
        $this->assertEquals(14024704, $colors[0]);
    }

    public function testJpegExtractMultipleColors(): void
    {
        $extractor = new ColorExtractor(Palette::fromFilename($this->pngPath));
        $numColors = 3;
        $colors = $extractor->extract($numColors);

        $this->assertIsArray($colors);
        $this->assertCount($numColors, $colors);
        $this->assertEquals($colors, [14024704, 3407872, 7111569]);
    }

    public function testTransparencyHandling(): void
    {
        $this->assertCount(0, Palette::fromFilename($this->transparentPngPath));

        $whiteBackgroundPalette = Palette::fromFilename($this->transparentPngPath, Color::fromHexToInt('#FFFFFF'));
        $this->assertEquals(iterator_to_array($whiteBackgroundPalette), [Color::fromHexToInt('#FF8080') => 1]);

        $blackBackgroundPalette = Palette::fromFilename($this->transparentPngPath, Color::fromHexToInt('#000000'));
        $this->assertEquals(iterator_to_array($blackBackgroundPalette), [Color::fromHexToInt('#7E0000') => 1]);
    }
}
