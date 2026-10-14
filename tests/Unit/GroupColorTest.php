<?php

namespace Tests\Unit;

use App\Support\GroupColor;
use Tests\TestCase;

class GroupColorTest extends TestCase
{
    public function test_every_group_up_to_fifty_has_a_distinct_color(): void
    {
        $colors = collect(range(1, 50))->map(fn (int $n) => GroupColor::for($n)['background']);

        $this->assertCount(50, $colors->unique(), 'Setiap nomor grup harus memiliki warna berbeda');
        $this->assertTrue($colors->every(fn ($hex) => (bool) preg_match('/^#[0-9a-f]{6}$/', $hex)));
    }

    public function test_color_is_stable_and_text_is_readable(): void
    {
        $this->assertSame(GroupColor::for(4), GroupColor::for(4));

        // kuning terang butuh teks gelap, biru tua butuh teks putih
        $this->assertSame('#111827', GroupColor::for(4)['text']);
        $this->assertSame('#ffffff', GroupColor::for(2)['text']);
    }

    public function test_unassigned_group_is_neutral_and_has_no_name(): void
    {
        $this->assertNotSame(GroupColor::for(1)['background'], GroupColor::for(null)['background']);
        $this->assertSame(['background', 'text'], array_keys(GroupColor::for(1)));
    }
}
