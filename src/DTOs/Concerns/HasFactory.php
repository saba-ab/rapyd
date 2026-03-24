<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\DTOs\Concerns;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

trait HasFactory
{
    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }

    public function toArray(): array
    {
        $result = [];
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            $snakeName = self::toSnakeCase($name);
            $value = $this->{$name};

            $result[$snakeName] = self::serializeValue($value);
        }

        return $result;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private static function toSnakeCase(string $input): string
    {
        return strtolower((string) preg_replace('/[A-Z]/', '_$0', lcfirst($input)));
    }

    private static function serializeValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof Carbon) {
            return $value->getTimestamp();
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map(fn ($item) => self::serializeValue($item), $value);
        }

        return $value;
    }
}
