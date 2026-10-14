<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Pembuat QR code berformat SVG (tanpa ekstensi imagick).
 */
class QrCode
{
    /**
     * @return string markup <svg> siap dicetak/ditampilkan inline
     */
    public static function svg(string $data, int $size = 200, int $margin = 1): string
    {
        $style = new RendererStyle($size, $margin, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(0, 0, 0)));
        $svg = (new Writer(new ImageRenderer($style, new SvgImageBackEnd)))->writeString($data);

        // buang deklarasi XML agar aman disisipkan langsung ke HTML
        return trim(preg_replace('/^<\?xml[^>]*\?>\s*/', '', $svg));
    }

    /**
     * Matriks modul (true = gelap) untuk keperluan render lain, mis. uji end-to-end.
     *
     * @return array<int, array<int, bool>>
     */
    public static function matrix(string $data): array
    {
        $code = Encoder::encode($data, ErrorCorrectionLevel::M(), 'UTF-8');
        $matrix = $code->getMatrix();
        $rows = [];

        for ($y = 0; $y < $matrix->getHeight(); $y++) {
            for ($x = 0; $x < $matrix->getWidth(); $x++) {
                $rows[$y][$x] = $matrix->get($x, $y) === 1;
            }
        }

        return $rows;
    }
}
