<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Type;

use App\Common\ValueObject\AbstractUuid;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

abstract class AbstractUuidType extends Type
{
    /**
     * @return class-string<AbstractUuid>
     */
    abstract protected function getValueObjectClass(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL([
            'length' => 36,
            'fixed' => true,
        ]);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        $expectedClass = $this->getValueObjectClass();
        if (!$value instanceof $expectedClass) {
            throw new ConversionException(sprintf(
                'Expected instance of %s, got %s',
                $expectedClass,
                is_object($value) ? $value::class : gettype($value)
            ));
        }

        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?AbstractUuid
    {
        if ($value === null) {
            return null;
        }

        $class = $this->getValueObjectClass();
        return new $class((string) $value);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
