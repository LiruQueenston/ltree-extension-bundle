<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use function explode;
use function implode;
use function is_array;
use function is_string;

class LTreeType extends Type
{
    public const TYPE_NAME = 'ltree';

    /**
     * { @inheritDoc }
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'ltree';
    }

    /**
     * Converts a value from its database representation to its PHP representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @return mixed[]|null The PHP representation of the value.
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        return is_string($value) ? explode('.', $value) : null;
    }

    /**
     * Converts a value from its PHP representation to its database representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @return string|null The database representation of the value.
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?array
    {
        return is_array($value) ? implode('.', $value) : null;
    }

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
