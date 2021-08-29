<?php declare(strict_types=1);

use A2Dborm\Prueba;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        $this->assertInstanceOf(
            Prueba::class,
            Prueba::fromString('user@example.com')
        );
    }

    public function testCanBeUsedAsString(): void
    {
        $this->assertEquals(
            'user@example.com',
            Prueba::fromString('user@example.com')
        );
    }
}