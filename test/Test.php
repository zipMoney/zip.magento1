<?php

use PHPUnit\Framework\TestCase;

final class Test extends TestCase
{

    public function testSample(): void
    {
        $this->assertEquals(
            'user@example.com',
            'user@example.com'
        );
    }
}

