<?php

declare(strict_types=1);

namespace DDL\Tests\Types;

use DDL\LtreeExtensionBundle\Types\LTreeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LtreeTest extends TestCase
{
    /** @var AbstractPlatform|MockObject */
    private $platform;

    /** @var LTreeType */
    private $type;

    protected function setUp(): void
    {
        $this->platform = $this->createMock(AbstractPlatform::class);
        $this->type = new LTreeType();
    }

    public function testEmptyArrayConvertsToDatabaseValue(): void
    {
        self::assertEquals('', $this->type->convertToDatabaseValue([], $this->platform));
    }

    public function testNullConvertsToDatabaseValue(): void
    {
        self::assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    public function testSingleItemArrayConvertsToDatabaseValue(): void
    {
        self::assertEquals('123', $this->type->convertToDatabaseValue(['123'], $this->platform));
    }

    public function testMultipleItemArrayConvertsToDatabaseValue(): void
    {
        self::assertRegExp(/** @lang PhpRegExp */ '/^(?>\d+\.?)+$/m', $this->type->convertToDatabaseValue([123, 321, 456, 789], $this->platform));
    }

    public function testEmptyLtreeConvertsToPhpValue(): void
    {
        self::assertIsArray($this->type->convertToPHPValue('', $this->platform));
    }

    public function testSingleNodeLtreeConvertsToPhpValue(): void
    {
        self::assertEquals(['123'], $this->type->convertToPHPValue('123', $this->platform));
    }

    public function testMultiNodeLtreeConvertsToPhpValue(): void
    {
        self::assertEquals(['123', '321', '213'], $this->type->convertToPHPValue('123.321.213', $this->platform));
    }

    public function testNullConvertsToPhpValue(): void
    {
        self::assertNull($this->type->convertToPHPValue(null, $this->platform));
    }
}
