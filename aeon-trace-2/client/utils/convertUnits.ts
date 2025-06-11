import convert from 'convert-units'

/**
 * Documentation for the convert-units library
 * https://www.npmjs.com/package/convert-units?activeTab=readme
 */

/**
 * Returns all available measure types
 * (e.g., length, mass, temperature, etc.)
 */
export function listMeasures(): string[] {
    return convert().measures()
}

/**
 * Returns all available units grouped by their measure type
 * (e.g., length, mass, temperature, etc.)
 */
export function listAllUnits(): Record<string, string[]> {
    const measures = convert().measures()
    const unitsByMeasure: Record<string, string[]> = {}

    for (const measure of measures) {
        unitsByMeasure[measure] = convert().possibilities(measure)
    }

    return unitsByMeasure
}

/**
 * Returns detailed unit descriptions for a specific measure type
 * (e.g., 'length', 'mass', 'reactivePower', etc.)
 * If no measure is provided, returns all units.
 */
export function listDetailedUnits(measure?: string): convert.UnitDescription[] {
    return convert().list(measure)
}

/**
 * Converts a value from one unit to another.
 * @param value - The numeric value to convert
 * @param from - The source unit (e.g., 'm', 'kg', 's')
 * @param to - The target unit (e.g., 'ft', 'lb', 'min')
 * @returns The converted numeric value
 */
export function convertUnits(value: number, from: string, to: string): number {
    try {
        return convert(value).from(from).to(to)
    } catch (error) {
        console.error(`❌ Error converting ${value} from "${from}" to "${to}":`, error)
        throw error
    }
}

/**
 * Returns the unit symbol (e.g., 'kg', 'ms') for a given full unit name
 * (e.g., 'Kilogram', 'Microseconds', 'Meters', etc.)
 * @param name - Full name of the unit (case-insensitive)
 * @returns The unit abbreviation (e.g., 'kg') or null if not found
 */
export function findUnitSymbolByName(name: string): string | null {
    if (!name) {
        return null
    }

    const allUnits = convert().list()

    const normalizedInput = name.trim().toLowerCase()

    const found = allUnits.find(unit => {
        return (
            unit.singular.toLowerCase() === normalizedInput
            || unit.plural.toLowerCase() === normalizedInput
        )
    })

    return found?.abbr ?? null
}
