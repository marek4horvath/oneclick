<?php

declare(strict_types=1);

namespace App\Enums\Steps;

enum BatchTypeUnits: string
{
    // Weight
    case Weight_Milligrams = 'mg';
    case Weight_Grams = 'g';
    case Weight_Kilos = 'kg';
    case Weight_Tons = 't';

    // Volume
    case Volume_Milliliters = 'ml';
    case Volume_Litres = 'l';
    case Volume_CubicMetres = 'm3';

    // Length
    case Length_Millimeters = 'mm';
    case Length_Centimeters = 'cm';
    case Length_Metres = 'm';

    /**
     * Returns the type (Weight, Volume, Length) for a given enum case.
     */
    public function getTypeUnit(): string
    {
        return match (true) {
            str_starts_with($this->name, 'Weight_') => 'Weight',
            str_starts_with($this->name, 'Volume_') => 'Volume',
            str_starts_with($this->name, 'Length_') => 'Length',
            default => throw new \LogicException('Unknown BatchTypeUnits case: ' . $this->name),
        };
    }

    /**
     * Returns the display name for the enum case.
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::Weight_Milligrams => 'Milligrams',
            self::Weight_Grams => 'Grams',
            self::Weight_Kilos => 'Kilos',
            self::Weight_Tons => 'Tons',
            self::Volume_Milliliters => 'Milliliters',
            self::Volume_Litres => 'Litres',
            self::Volume_CubicMetres => 'Cubic Metres',
            self::Length_Millimeters => 'Millimeters',
            self::Length_Centimeters => 'Centimeters',
            self::Length_Metres => 'Metres',
        };
    }

    /**
     * Groups all enum cases by their type unit.
     *
     * @return array<string, array<int, array{name: string, value: string}>> Array where keys are type units and values are arrays of name-value pairs.
     */
    public static function groupedByType(): array
    {
        $groups = [];

        foreach (self::cases() as $case) {
            $type = $case->getTypeUnit();
            $groups[$type][] = [
                'name' => $case->getDisplayName(),
                'value' => $case->value,
            ];
        }

        return $groups;
    }
}
