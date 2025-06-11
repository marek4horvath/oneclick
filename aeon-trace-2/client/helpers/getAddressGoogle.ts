/**
 * Return the address according to lat and lng
 *
 * @param {number} lat - The latitude coordinate
 * @param {number} lng - The longitude coordinate
 * @returns {object} The address information object
 */
export async function getAddressGoogle(lat: number, lng: number) {
    const apiKey = import.meta.env.VITE_APP_GOOGLE_API_KEY
    const url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${apiKey}`

    try {
        const response = await fetch(url)
        const data = await response.json()

        if (data.status === 'OK') {
            const addressComponents = data.results[0]?.address_components

            let street = ''
            let zip = ''
            let country = ''
            let city = ''
            let houseNo = ''

            addressComponents.forEach(component => {
                const types = component.types

                if (types.includes('route')) {
                    street = component.long_name
                }
                if (types.includes('postal_code')) {
                    zip = component.long_name
                }
                if (types.includes('country')) {
                    country = component.long_name
                }
                if (
                    types.includes('locality')
                    || types.includes('postal_town')
                    || types.includes('administrative_area_level_2')
                    || types.includes('sublocality')
                ) {
                    city = component.long_name
                }
                if (types.includes('street_number')) {
                    houseNo = component.long_name
                }
            })

            return {
                street,
                zip,
                country,
                city,
                houseNo,
            }
        } else {
            console.error('Error in geocoding:', data.status)

            return null
        }
    } catch (error) {
        console.error('Error getting address:', error)

        return null
    }
}

/**
 * Calculate the haversine distance (air distance) between two points
 *
 * @param {number} latOrigin - Latitude of the first point
 * @param {number} lngOrigin - Longitude of the first point
 * @param {number} latDestination - Latitude of the second point
 * @param {number} lngDestination - Longitude of the second point
 * @returns {number} The distance in kilometers
 */
export function calculateDistance(latOrigin: number, lngOrigin: number, latDestination: number, lngDestination: number): number {
    // Helper function to convert degrees to radians
    const toRadians = (value: number): number => (value * Math.PI) / 180

    const R = 6371 // Earth's radius in kilometers
    const dLat = toRadians(latDestination - latOrigin) // Difference in latitude
    const dLng = toRadians(lngDestination - lngOrigin) // Difference in longitude

    // Haversine formula
    const a
        = Math.sin(dLat / 2) * Math.sin(dLat / 2)
        + Math.cos(toRadians(latOrigin))
        * Math.cos(toRadians(latDestination))
        * Math.sin(dLng / 2)
        * Math.sin(dLng / 2)

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
    const distance = R * c // Distance in kilometers

    return Number.parseFloat(distance.toFixed(2)) // Return with two decimal places
}

/**
 * Calculate transport distance between two points using Google Maps Directions API.
 *
 * @param {string} travelMode - Travel mode for distance calculation.
 * @param {number} latOrigin - Latitude of the starting point.
 * @param {number} lngOrigin - Longitude of the starting point.
 * @param {number} latDestination - Latitude of the destination point.
 * @param {number} lngDestination - Longitude of the destination point.
 * @returns {Promise<number | null>} - The driving distance in kilometers, rounded up to the nearest integer.
 */
export async function getTravelDistance(travelMode: string, latOrigin: number, lngOrigin: number, latDestination: number, lngDestination: number): Promise<number | null> {
    const directionsService = new google.maps.DirectionsService()

    return new Promise((resolve, reject) => {
        const request = {
            origin: { lat: latOrigin, lng: lngOrigin }, // Starting point coordinates
            destination: { lat: latDestination, lng: lngDestination }, // Destination point coordinates
            travelMode, // Travel mode
        }

        directionsService.route(request, (result: any, status: any) => {
            if (status === google.maps.DirectionsStatus.OK) {
                // Calculate the distance in kilometers and round it up to the nearest integer
                const distance = Math.ceil(result.routes[0].legs[0].distance.value / 1000)

                resolve(distance)
            } else {
                // Log an error and reject the promise if the API request fails
                console.error('Error calculating route:', status)
                reject(new Error(`Error calculating route: ${status}`))
            }
        })
    })
}
