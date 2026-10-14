<?php

namespace Tests\Unit;

use App\Support\TroopNumber;
use PHPUnit\Framework\TestCase;

class TroopNumberTest extends TestCase
{
    public function test_input_is_normalized(): void
    {
        $this->assertSame('01-083', TroopNumber::normalize('01083'));
        $this->assertSame('01-083', TroopNumber::normalize(' 01.083 '));
        $this->assertSame('01-083', TroopNumber::normalize('01-083'));
        $this->assertSame('1083', TroopNumber::normalize('1083'));
    }

    public function test_pair_is_first_plus_one(): void
    {
        $this->assertSame('01-084', TroopNumber::pair('01-083'));
        $this->assertSame('12-100', TroopNumber::pair('12-099'));
        $this->assertSame('01-002', TroopNumber::pair('01-001'));
        $this->assertSame('01-083 & 01-084', TroopNumber::label('01-083'));
    }

    public function test_invalid_numbers_have_no_pair(): void
    {
        $this->assertFalse(TroopNumber::isValid('01-999'));
        $this->assertFalse(TroopNumber::isValid('01-084'));
        $this->assertNull(TroopNumber::pair('01-084'));
        $this->assertFalse(TroopNumber::isValid('1-083'));
        $this->assertNull(TroopNumber::pair('01-999'));
    }
}
