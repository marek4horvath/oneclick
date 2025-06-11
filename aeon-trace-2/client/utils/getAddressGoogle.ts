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
