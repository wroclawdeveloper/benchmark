<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class EmailTest extends TestCase
{
    public function testValidEmailAddress(): void
    {
        $mail = new PHPMailer(true);
        $this->assertInstanceOf(
            PHPMailer::class,
            $mail
        );
    }

    public function testCannotBeCreatedFromInvalidEmailAddress(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Email::fromString('invalid');
    }

    public function testCanBeUsedAsString(): void
    {
        $this->assertEquals(
            'user@example.com',
            Email::fromString('user@example.com')
        );
    }
}