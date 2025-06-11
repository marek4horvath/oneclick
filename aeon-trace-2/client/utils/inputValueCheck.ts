export const isInputFilled = (value: any): boolean => {
    return !!((typeof value === 'string' && value.trim() !== '')
        || (typeof value === 'number' && !Number.isNaN(value))
        || (value instanceof File)

        // Array of anything
        || (Array.isArray(value) && value.length > 0)

        // Single image object
        || (typeof value === 'object'
            && value !== null
            && typeof value.url === 'string'
            && value.url.startsWith('data:image'))

        // Array of image objects
        || (Array.isArray(value)
            && value.length > 0
            && value.every(img =>
                typeof img === 'object'
                && typeof img.url === 'string'
                && img.url.startsWith('data:image')))

        // Coordinates
        || (typeof value === 'object'
            && value !== null
            && 'lat' in value
            && 'lng' in value
            && value.lat !== null
            && value.lng !== null)

        // Date
        || (value instanceof Date || !Number.isNaN(Date.parse(value))))
}
