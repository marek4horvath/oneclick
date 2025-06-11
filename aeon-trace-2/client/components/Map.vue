<script setup lang="ts">
import { useI18n } from 'vue-i18n'

const props = defineProps({
    addressGroups: {
        type: Array as () => {
            addresses: (string | { lat: number; lng: number })[]
            color: string
            connectLine?: boolean
        }[],
        required: true,
    },

    isMarkerClicked: {
        type: Boolean,
        default: false,
    },

    isActiveMap: {
        type: Boolean,
        default: true,
    },

    showMarkers: {
        type: Boolean,
        default: true,
    },

    zoom: {
        type: Number,
        default: null,
    },

    isRefreshMaps: {
        type: Boolean,
        default: false,
    },
    isTrace: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['pointClicked', 'pointClickedAddress', 'getZoom'])

const map = ref<google.maps.Map | null>(null)
const markers = ref<google.maps.Marker[]>([])
const infoWindows = ref<google.maps.InfoWindow[]>([])
const msg = ref<string>('')
const coordinates = ref<{ coords: google.maps.LatLngLiteral; color: string; address: string }[]>([])
const previousCoords = ref<{ lat: number; lng: number } | null>(null)
const showMarkers_ = ref(props.showMarkers)
const uniqueId = ref<string>('')
const makers = new Set()
const isMapMarkerActive = ref<boolean>(props.isMarkerClicked)
const isGoogleMapsLoaded = ref(false)
const clickCoordination = ref([])

const { t } = useI18n()

const waitForGoogleMaps = () => {
    return new Promise<void>(resolve => {
        if (window.google && window.google.maps) {
            isGoogleMapsLoaded.value = true
            resolve()

            return
        }

        const checkInterval = setInterval(() => {
            if (window.google && window.google.maps) {
                clearInterval(checkInterval)
                isGoogleMapsLoaded.value = true
                resolve()
            }
        }, 100)
    })
}

async function geocodeAddress(address: string): Promise<google.maps.LatLngLiteral | null> {
    return new Promise(resolve => {
        const geocoder = new google.maps.Geocoder()

        geocoder.geocode({ address }, (results, status) => {
            if (status === 'OK' && results && results.length > 0) {
                const location = results[0].geometry.location

                const coords = {
                    lat: location.lat(),
                    lng: location.lng(),
                }

                resolve(coords)
            } else {
                resolve(null)
            }
        })
    })
}

async function reverseGeocode(
    coords: google.maps.LatLngLiteral,
    detailed = false,
): Promise<string | { zip: string; houseNo: string; city: string; country: string; street: string }> {
    return new Promise(resolve => {
        const geocoder = new google.maps.Geocoder()

        geocoder.geocode({ location: coords }, (results, status) => {
            if (status === 'OK' && results && results.length > 0) {
                const addressComponents = results[0].address_components

                if (detailed) {
                    const address = {
                        zip: '',
                        houseNo: '',
                        city: '',
                        country: '',
                        street: '',
                    }

                    addressComponents.forEach(component => {
                        const types = component.types

                        if (types.includes('postal_code')) {
                            address.zip = component.long_name
                        } else if (types.includes('street_number')) {
                            address.houseNo = component.long_name
                        } else if (types.includes('route')) {
                            address.street = component.long_name
                        } else if (types.includes('locality') || types.includes('postal_town')
                                || types.includes('sublocality') || types.includes('administrative_area_level_2')) {
                            address.city = component.long_name
                        } else if (types.includes('country')) {
                            address.country = component.long_name
                        }
                    })
                    resolve(address)
                } else {
                    resolve(results[0].formatted_address)
                }
            } else {
                resolve(detailed ? { zip: '', houseNo: '', city: '', country: '', street: '' } : '')
            }
        })
    })
}

function initMap() {
    if (coordinates.value.length > 0) {
        let totalLat = 0
        let totalLng = 0
        let minLat = Number.POSITIVE_INFINITY
        let maxLat = Number.NEGATIVE_INFINITY
        let minLng = Number.POSITIVE_INFINITY
        let maxLng = Number.NEGATIVE_INFINITY

        coordinates.value.forEach(coord => {
            totalLat += coord.coords.lat
            totalLng += coord.coords.lng
            minLat = Math.min(minLat, coord.coords.lat)
            maxLat = Math.max(maxLat, coord.coords.lat)
            minLng = Math.min(minLng, coord.coords.lng)
            maxLng = Math.max(maxLng, coord.coords.lng)
        })

        const centerLat = totalLat / coordinates.value.length
        const centerLng = totalLng / coordinates.value.length
        const mapCenter = { lat: centerLat, lng: centerLng }

        const latDiff = maxLat - minLat
        const lngDiff = maxLng - minLng
        const maxDiff = Math.max(latDiff, lngDiff)

        let zoomLevel
        if (maxDiff < 0.1) {
            zoomLevel = 10
        } else if (maxDiff < 0.5) {
            zoomLevel = 12
        } else if (maxDiff < 1) {
            zoomLevel = 10
        } else {
            zoomLevel = 8
        }

        if (map.value) {
            if (!isMapMarkerActive.value) {
                map.value.setCenter(mapCenter)
            }
            map.value.setZoom(props.zoom ? props.zoom : zoomLevel)
        } else {
            map.value = new google.maps.Map(
                document.getElementById(uniqueId.value) as HTMLElement,
                {
                    center: mapCenter,
                    zoom: props.zoom ? props.zoom : zoomLevel,
                },
            )
        }
    }

    if (props.isActiveMap) {
        map.value?.addListener('click', (event: any) => {
            isMapMarkerActive.value = true
            if (isMapMarkerActive.value) {
                const clickedCoords = {
                    lat: event.latLng.lat(),
                    lng: event.latLng.lng(),
                }

                const exists = clickCoordination.value.some(coord =>
                    coord.lat === clickedCoords.lat && coord.lng === clickedCoords.lng,
                )

                if (exists) {
                    return
                }

                makers.forEach(marker => marker.setMap(null))
                makers.clear()
                markers.value = []

                emit('getZoom', getZoomLevel())
                emit('pointClicked', clickedCoords)

                previousCoords.value = clickedCoords

                const marker = new google.maps.Marker({
                    position: clickedCoords,
                    map: map.value,
                    icon: {
                        url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                    },
                })

                map.value.panTo(clickedCoords)

                markers.value.push(marker)
                makers.add(marker)

                clickCoordination.value.push(clickedCoords)
            } else {
                emit('pointClicked', [])
            }
        })
    }
}

function updateMarkersAndPolylines() {
    if (map.value) {
        if (showMarkers_.value && !isMapMarkerActive.value) {
            makers.forEach(marker => marker.setMap(null))
            makers.clear()
            markers.value = []

            props.addressGroups.forEach(group => {
                const groupCoords = coordinates.value
                    .filter(item => item.color === group.color)
                    .map(item => ({
                        coords: item.coords,
                        color: item.color,
                        address: item.address,
                    }))

                groupCoords.forEach((item, index) => {
                    const isLastInGroup = props.isTrace && index === groupCoords.length - 1
                    const markerColor = isLastInGroup ? 'red' : item.color

                    const marker = new google.maps.Marker({
                        position: item.coords,
                        map: map.value,
                        title: `Address ${index + 1}`,
                        icon: {
                            url: `http://maps.google.com/mapfiles/ms/icons/${markerColor}-dot.png`,
                        },
                    })

                    const contentString = `<div><p>${item.address}</p></div>`

                    const infoWindow = new google.maps.InfoWindow({
                        content: contentString,
                    })

                    marker.addListener('click', () => {
                        infoWindow.open(map.value, marker)
                    })

                    markers.value.push(marker)
                    makers.add(marker)
                    infoWindows.value.push(infoWindow)
                })

                if (group.connectLine && groupCoords.length > 1) {
                    const lineSymbol = {
                        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                    }

                    const existingPolylines = groupCoords.map((_, i) => {
                        if (i < groupCoords.length - 1) {
                            return new google.maps.Polyline({
                                path: [groupCoords[i].coords, groupCoords[i + 1].coords],
                                geodesic: true,
                                strokeColor: group.color,
                                strokeOpacity: 1.0,
                                strokeWeight: 2,
                                icons: [{
                                    icon: lineSymbol,
                                    offset: '100%',
                                }],
                            })
                        } else {
                            return undefined
                        }
                    }).filter((polyline: any) => polyline !== undefined)

                    existingPolylines.forEach(polyline => {
                        polyline.setMap(map.value)
                    })
                }
            })
        }
    }
}

async function getCoordinates() {
    try {
        if (props.addressGroups.length === 0) {
            return
        }

        const coords = await Promise.all(props.addressGroups.flatMap(group =>
            group.addresses.map(async address => {
                if (typeof address === 'string') {
                    const coord = await geocodeAddress(address)

                    return {
                        coords: coord,
                        color: group.color,
                        address: coord ? await reverseGeocode(coord) : '',
                    }
                } else {
                    return {
                        coords: { lat: address.lat, lng: address.lng },
                        color: group.color,
                        address: typeof address === 'string' ? address : await reverseGeocode({ lat: address.lat, lng: address.lng }),
                    }
                }
            }),
        ))

        coordinates.value = coords.filter(c => c.coords !== null) as { coords: google.maps.LatLngLiteral; color: string; address: string }[]

        initMap()
        updateMarkersAndPolylines()
        msg.value = ''
    } catch (error) {
        msg.value = t('mss.errorFetching', { error })
    }
}

function getZoomLevel() {
    if (map.value) {
        return map.value.getZoom()
    } else {
        return null
    }
}

function refreshMap() {
    getCoordinates()
    updateMarkersAndPolylines()
    makers.forEach(marker => marker.setMap(null))
    makers.clear()
    markers.value = []
}

watch(() => props.showMarkers,
    newValue => {
        showMarkers_.value = newValue
    },
)

watch(() => props.isMarkerClicked,
    newValue => {
        isMapMarkerActive.value = newValue
    },
)

watch(() => props.isRefreshMaps,
    newValue => {
        if (newValue) {
            refreshMap()
        }
    }, { immediate: true },
)

watch(previousCoords, (newVal, oldVal) => {
    if (oldVal && isMapMarkerActive.value) {
        markers.value = markers.value.filter((marker: any) => {
            const position = marker.getPosition()
            if (position) {
                const latLng = {
                    lat: position.lat(),
                    lng: position.lng(),
                }

                const roundOldLat = Number.parseFloat(oldVal.lat.toFixed(6))
                const roundOldLng = Number.parseFloat(oldVal.lng.toFixed(6))
                const roundActualLat = Number.parseFloat(latLng.lat.toFixed(6))
                const roundActualLng = Number.parseFloat(latLng.lng.toFixed(6))

                if (roundActualLat === roundOldLat || roundActualLng === roundOldLng) {
                    marker.setMap(null)
                    marker.setVisible(false)

                    return false
                }
            }

            return true
        })

        previousCoords.value = newVal
    }
}, { immediate: true })

watch(() => props.addressGroups, getCoordinates, { deep: true })

onMounted(async () => {
    uniqueId.value = `map-${Math.random().toString(36).substr(2, 9)}`
    await waitForGoogleMaps()
    await getCoordinates()
})
</script>

<template>
    <div
        v-if="!isGoogleMapsLoaded"
        class="loading"
    >
        {{ $t('lodingMap') }}
    </div>
    <div
        v-if="uniqueId && isGoogleMapsLoaded"
        :id="uniqueId"
        class="map-init"
        style="height: 400px; width: 100%;"
    />
</template>
