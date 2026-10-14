<?php

namespace App\Support;

use FPDF;

/**
 * Tambahan FPDF untuk kartu peserta: lingkaran, warna heksadesimal, pembungkus teks, dan konversi UTF-8.
 */
class CardFpdf extends FPDF
{
    public function fillHex(string $hex): void
    {
        $this->SetFillColor(...$this->rgb($hex));
    }

    public function textHex(string $hex): void
    {
        $this->SetTextColor(...$this->rgb($hex));
    }

    public function drawHex(string $hex): void
    {
        $this->SetDrawColor(...$this->rgb($hex));
    }

    /**
     * @return array{int, int, int}
     */
    public function rgb(string $hex): array
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return [$r, $g, $b];
    }

    /**
     * FPDF memakai font inti (Latin-1/cp1252), sehingga teks UTF-8 dikonversi dulu.
     */
    public function latin(string $text): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text) ?: $text;
    }

    /**
     * Lingkaran terisi memakai empat kurva Bezier.
     */
    public function circle(float $x, float $y, float $r, string $style = 'F'): void
    {
        $k = $this->k;
        $h = $this->h;
        $c = 0.5523 * $r;
        $op = ['F' => 'f', 'FD' => 'B', 'D' => 'S'][$style] ?? 'f';

        $points = [
            [$x + $r, $y, $x + $r, $y - $c, $x + $c, $y - $r, $x, $y - $r],
            [$x, $y - $r, $x - $c, $y - $r, $x - $r, $y - $c, $x - $r, $y],
            [$x - $r, $y, $x - $r, $y + $c, $x - $c, $y + $r, $x, $y + $r],
            [$x, $y + $r, $x + $c, $y + $r, $x + $r, $y + $c, $x + $r, $y],
        ];

        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($h - $y) * $k));
        foreach ($points as [, , $x1, $y1, $x2, $y2, $x3, $y3]) {
            $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', $x1 * $k, ($h - $y1) * $k, $x2 * $k, ($h - $y2) * $k, $x3 * $k, ($h - $y3) * $k));
        }
        $this->_out($op);
    }

    /**
     * Siku sudut ala HUD di empat pojok sebuah area.
     */
    public function brackets(float $x, float $y, float $w, float $h, float $len, float $thickness, string $hex): void
    {
        $this->fillHex($hex);
        foreach ([[$x, $y, 1, 1], [$x + $w, $y, -1, 1], [$x, $y + $h, 1, -1], [$x + $w, $y + $h, -1, -1]] as [$cx, $cy, $dx, $dy]) {
            $this->Rect($dx > 0 ? $cx : $cx - $len, $dy > 0 ? $cy : $cy - $thickness, $len, $thickness, 'F');
            $this->Rect($dx > 0 ? $cx : $cx - $thickness, $dy > 0 ? $cy : $cy - $len, $thickness, $len, 'F');
        }
    }

    /**
     * Membungkus teks per kata sesuai lebar (mm) pada font yang sedang aktif.
     *
     * @return array<int, string>
     */
    public function wrap(string $text, float $width): array
    {
        $lines = [];
        $line = '';

        foreach (preg_split('/\s+/', trim($text)) as $word) {
            $candidate = $line === '' ? $word : $line.' '.$word;

            if ($this->GetStringWidth($candidate) <= $width || $line === '') {
                $line = $candidate;
            } else {
                $lines[] = $line;
                $line = $word;
            }
        }

        return $line === '' ? $lines : [...$lines, $line];
    }

    /**
     * Mencari ukuran font terbesar (pt) agar teks muat dalam batas jumlah baris.
     */
    public function fitFontSize(string $text, float $width, int $maxLines, float $start, float $min, string $family = 'Helvetica', string $style = 'B'): float
    {
        for ($size = $start; $size > $min; $size -= 2) {
            $this->SetFont($family, $style, $size);
            if (count($this->wrap($text, $width)) <= $maxLines) {
                return $size;
            }
        }

        return $min;
    }
}
