<script setup lang="ts">
import mimedb from 'mime-db'
import { uniqBy } from 'lodash'
import { PhosphorIconMagicWand } from '#components'
import { b64toBlob } from '@/helpers/convert'
import { getAddressGoogle } from '@/helpers/getAddressGoogle'
import type { SelectItem } from '@/types/selectItem'

definePageMeta({
    title: 'companies.addCompany',
    name: 'add-companies',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { $alert } = useNuxtApp()
const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const logisticsQuery = route.query.logistics
const textFieldState = ref<any[]>([])
const form = ref(null)
const valid = ref(false)
const countries = ref<string []>([])

const countriesStore = useCountriesStore()
const companiesStore = useCompaniesStore()
const siteImagesStore = useSiteImagesStore()
const transportTypesStore = useTransportTypesStore()

const formCompany = ref({
    id: 0,
    name: '',
    email: '',
    contact: '',
    description: '',
    isSite: false,
    typeTransports: [],
    isLogistics: logisticsQuery === 'true',
    isProductCompany: logisticsQuery === 'false',
    sites: [],
    image: [],
    products: [],
    users: [],
    fullAddress: '',
    mapClicked: false,
})

const formAddress = ref({
    street: '',
    zip: null,
    country: '',
    city: '',
    houseNo: '',
})

const newImageFile = ref<string>()

const addresses = ref<Record<string, any>[]>([{
    lat: 50.8503460,
    lng: 4.3517210,
}])

const addressesSite = ref<Record<string, any>[]>([{
    lat: 50.8503460,
    lng: 4.3517210,
}])

const showMarkers = ref(false)
const mapClicked = ref(true)
const mapAddressesSite = ref<{ [id: number]: { fullAddress: string }[] }>({})
const zoom = ref<number>(10)
const zoomSite = ref<number[]>([])

const mapClickedSite = ref({
    1: true,
})

let isUpdatingAddress = false

const showMarkersSite = ref({
    1: false,
})

const addressClickMap = ref({
    street: '',
    zip: null,
    country: '',
    city: '',
    houseNo: '',
})

const addressClickMapSite = ref([])

const transportItem = ref<SelectItem[]>([{
    value: '',
    title: '',
}])

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

const streetRules = [
    (v: string) => !!v || 'Street is required',
    (v: string) => v.trim().length > 0 || 'Street cannot be empty',
]

const zipRules = [
    (v: string) => !!v || 'Zip is required',
    (v: string) => v.trim().length > 0 || 'Zip cannot be empty',
]

const houseNumberRules = [
    (v: string) => !!v || 'House number is required',
    (v: string) => v.trim().length > 0 || 'House number cannot be empty',
]

const cityRules = [
    (v: string) => !!v || 'City is required',
    (v: string) => v.trim().length > 0 || 'Zip cannot be empty',
]

const countryRules = [
    (v: string) => !!v || 'Country is required',
    (v: string) => v.trim().length > 0 || 'Country cannot be empty',
]

const emailRules = [
    (v: string) => !!v || 'E-mail is required',
    (v: string) => /.+@.+/.test(v) || 'E-mail must be valid',
]

const contactRules = [
    (v: string) => !!v || 'Phone number is required',
    (v: string) => /^\d+$/.test(v) || 'Phone number must contain only numbers',
    (v: string) => v.length >= 8 || 'Phone number must be at least 8 digits long',
    (v: string) => v.length <= 15 || 'Phone number must be at most 15 digits long',
]

const imageChanged = (image: string) => {
    newImageFile.value = image
}

const onFileChangedSite = (image: string, id: number) => {
    for (let i = 0; i < textFieldState.value.length; i++) {
        if (textFieldState.value[i].id === id) {
            textFieldState.value[i].img = image
        }
    }
}

const addTextField = () => {
    const newId = textFieldState.value.length > 0 ? textFieldState.value[textFieldState.value.length - 1].id + 1 : 1

    showMarkersSite.value[newId] = false
    mapClickedSite.value[newId] = true
    zoomSite.value[newId] = 10

    textFieldState.value.push({
        id: newId,
        name: '',
        address: {
            street: '',
            postcode: null,
            country: '',
            city: '',
            houseNo: '',
        },
        img: '',
        mapClicked: false,
    })
}

const removeTextField = (id: number) => {
    const index = textFieldState.value.findIndex(textField => textField.id === id)
    if (index !== -1) {
        textFieldState.value.splice(index, 1)
    }
}

const addCompany = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const formData = new FormData()

    const selectedTransportIds = (formCompany.value.typeTransports || []).map((typeTransportId: string) => {
        return `/api/transport_types/${typeTransportId}`
    })

    const addCompanyResponse = await companiesStore.createCompany({
        name: formCompany.value.name,
        email: formCompany.value.email,
        phone: formCompany.value.contact,
        description: formCompany.value.description,
        logisticsCompany: formCompany.value.isLogistics,
        productCompany: formCompany.value.isProductCompany,
        typeOfTransport: selectedTransportIds || [],
        address: {
            street: formAddress.value.street,
            city: formAddress.value.city,
            postcode: formAddress.value.zip,
            houseNo: formAddress.value.houseNo,
            country: formAddress.value.country,
        },
        sites: [
            ...textFieldState.value,
        ],

    })

    if (!addCompanyResponse) {
        $alert('Failed to add company', 'Error', 'error')

        return
    }

    textFieldState.value.forEach(async (site: any) => {
        const matchedSite = addCompanyResponse.sites.find((s: any) => s.name === site.name)
        if (matchedSite) {
            const formDataSite = new FormData()

            const image = b64toBlob(site.img.url)
            const imageType = mimedb[image.type]

            formDataSite.append('file', image, `${matchedSite.id}.${imageType.extensions[0]}`)
            formDataSite.append('companySite', `/api/company_sites/${matchedSite.id}`)

            await siteImagesStore.createSiteImage(formDataSite)
        }
    })

    if (newImageFile.value) {
        const image = b64toBlob(newImageFile.value.url)

        const imageType = mimedb[image.type]

        formData.append('file', image, `${addCompanyResponse.id}.${imageType.extensions[0]}`)

        await companiesStore.uploadCompanyLogo(addCompanyResponse.id, formData)
    }

    if (formCompany.value.isLogistics && !formCompany.value.isProductCompany) {
        await router.push('/logistics/')
    } else {
        await router.push(`/companies/detail/${addCompanyResponse.id}`)
    }
}

const getZoom = (zoomLevel: number) => {
    zoom.value = zoomLevel
}

const getZoomSite = (zoomLevel: number, id: number) => {
    zoomSite.value[id] = zoomLevel
}

const getCoordination = (coord: any) => {
    if (isUpdatingAddress) {
        return
    }

    isUpdatingAddress = true
    showMarkers.value = false
    mapClicked.value = true

    getAddressGoogle(coord.lat, coord.lng).then(address => {
        addressClickMap.value = {
            street: address.street,
            zip: address.zip,
            country: address.country,
            city: address.city,
            houseNo: address.houseNo,
        }
    }).finally(() => {
        isUpdatingAddress = false
    })
}

const getCoordinationSite = (coord: any, id: number) => {
    if (isUpdatingAddress) {
        return
    }

    showMarkersSite.value[id] = false
    mapClickedSite.value[id] = true

    isUpdatingAddress = true

    getAddressGoogle(coord.lat, coord.lng).then(address => {
        const existingAddressIndex = addressClickMapSite.value.findIndex(item => item.id === id)

        if (existingAddressIndex !== -1) {
            addressClickMapSite.value[existingAddressIndex] = {
                id,
                street: address.street,
                zip: address.zip,
                country: address.country,
                city: address.city,
                houseNo: address.houseNo,
            }
        } else {
            addressClickMapSite.value.push({
                id,
                street: address.street,
                zip: address.zip,
                country: address.country,
                city: address.city,
                houseNo: address.houseNo,
            })
        }

        isUpdatingAddress = false
    }).catch(() => {
        isUpdatingAddress = false
    })
}

watch(
    () => ({
        street: formAddress.value.street,
        zip: formAddress.value.zip,
        country: formAddress.value.country,
        city: formAddress.value.city,
        houseNo: formAddress.value.houseNo,
    }),
    (newValue, oldVal) => {
        const addressData: any = {
            street: '',
            zip: null,
            country: '',
            city: '',
            houseNo: '',
        }

        const { street, zip, country, city, houseNo } = newValue

        showMarkers.value = false
        mapClicked.value = true

        if (street && zip && country && city && houseNo && !newValue.mapClicked) {
            addressData.street = street
            addressData.zip = zip
            addressData.country = country
            addressData.city = city
            addressData.houseNo = houseNo

            const isAllFilled = Object.entries(addressData).every(([__, value]) => value !== '' && value !== null && value !== undefined)

            if (isAllFilled) {
                const fullAddress: string | any = `${addressData.street} ${addressData.houseNo}, ${addressData.zip} ${addressData.city} - ${addressData.country}`

                addresses.value = [fullAddress]
                formCompany.value.fullAddress = fullAddress

                showMarkers.value = true
                mapClicked.value = false
            }
        } else {
            addressData.street = oldVal?.street || ''
            addressData.zip = oldVal?.zip || null
            addressData.country = oldVal?.country || ''
            addressData.city = oldVal?.city || ''
            addressData.houseNo = oldVal?.houseNo || ''

            const isAllFilled = Object.entries(addressData).every(([__, value]) => value !== '' && value !== null && value !== undefined)

            if (isAllFilled) {
                const fullAddress: string | any = `${addressData.street} ${addressData.houseNo}, ${addressData.zip} ${addressData.city} - ${addressData.country}`

                addresses.value = [fullAddress]
                formCompany.value.fullAddress = fullAddress

                showMarkers.value = true
            }

            newValue.mapClicked = false
        }
    },
    { immediate: true, deep: true },
)

watch(() => addressClickMap.value, newValue => {
    const addressData: any = {
        street: '',
        zip: null,
        country: '',
        city: '',
        houseNo: '',
    }

    showMarkers.value = false

    if (newValue) {
        addressData.street = newValue.street || ''
        addressData.zip = newValue.zip || null
        addressData.country = newValue.country || ''
        addressData.city = newValue.city || ''
        addressData.houseNo = newValue.houseNo || ''

        const isAllFilled = Object.entries(addressData).every(([__, value]) => {
            return value !== '' && value !== null && value !== undefined
        })

        if (isAllFilled) {
            showMarkers.value = false
            formAddress.value = {
                street: addressData.street,
                zip: addressData.zip,
                country: addressData.country,
                city: addressData.city,
                houseNo: addressData.houseNo,
                mapClicked: true,
            }

            formCompany.value.fullAddress = `${addressData.street} ${addressData.houseNo}, ${addressData.zip} ${addressData.city} - ${addressData.country}`
        }
    }
}, { immediate: true, deep: true })

watch(() => textFieldState.value, newValue => {
    newValue.forEach(item => {
        const addressData = item.address || {
            street: '',
            zip: null,
            country: '',
            city: '',
            houseNo: '',
        }

        const isAllFilled = Object.entries(addressData).every(([_, value]) => value !== '' && value !== null && value !== undefined)

        if (isAllFilled) {
            const fullAddress = `${addressData.street} ${addressData.houseNo}, ${addressData.postcode} ${addressData.city} - ${addressData.country}`

            if (!mapAddressesSite.value[item.id]) {
                mapAddressesSite.value[item.id] = []
            }

            mapAddressesSite.value[item.id] = [{
                fullAddress,
            }]
            showMarkersSite.value[item.id] = true
            mapClickedSite.value[item.id] = false
        }

        if (item.mapClicked) {
            item.mapClicked = false
        }
    })
}, { immediate: true, deep: true })

watch(() => addressClickMapSite.value, newValue => {
    if (newValue && newValue.length) {
        newValue.forEach((addressData: any) => {
            const textField = textFieldState.value.find(item => item.id === addressData.id)

            showMarkersSite.value[addressData.id] = false
            if (textField) {
                textField.address = {
                    street: addressData.street || '',
                    postcode: addressData.zip || null,
                    country: addressData.country || '',
                    city: addressData.city || '',
                    houseNo: addressData.houseNo || '',
                }

                textField.mapClicked = true
            }
        })
    }
}, { immediate: true, deep: true })

// Watch and sync the Company data with First Site data
watch(() => ({
    name: formCompany.value.name,
    street: formAddress.value.street,
    zip: formAddress.value.zip,
    houseNo: formAddress.value.houseNo,
    city: formAddress.value.city,
    country: formAddress.value.country,
}), newVal => {
    if (!formCompany.value.isSite) {
        return
    }

    const site = textFieldState.value.find(site_ => site_.id === 0)
    if (!site) {
        return
    }

    site.name = newVal.name
    site.address = {
        street: newVal.street,
        postcode: newVal.zip,
        houseNo: newVal.houseNo,
        city: newVal.city,
        country: newVal.country,
    }
}, { deep: true })

watch(() => formCompany.value.isSite, newValue => {
    if (newValue) {
        textFieldState.value.push({
            id: 0,
            name: formCompany.value.name,
            address: {
                street: formAddress.value.street,
                postcode: formAddress.value.zip,
                country: formAddress.value.country,
                city: formAddress.value.city,
                houseNo: formAddress.value.houseNo,
            },
        })
    } else {
        textFieldState.value = []
    }
}, { immediate: true })

onMounted(async () => {
    await countriesStore.fetchCountries()
    await transportTypesStore.fetchTransportTypes()
    countries.value = countriesStore.countries

    transportItem.value = transportTypesStore.transportTypes.map((type: any) => {
        return {
            value: type.id,
            title: type.name,
        }
    })
})
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer fluid>
            <div class="company">
                <h2>{{ $t('companies.addCompany') }}</h2>
                <p>{{ $t('companies.title') }}</p>
                <VForm
                    ref="form"
                    v-model="valid"
                >
                    <VRow class="mt-3">
                        <VCol
                            :cols="12"
                            :md="6"
                            :xs="12"
                        >
                            <VRow>
                                <VCol
                                    :cols="12"
                                    :md="4"
                                    :xs="12"
                                >
                                    <VCheckbox
                                        v-model="formCompany.isSite"
                                        :label="$t('companies.siteCompany')"
                                        color="#24a69a"
                                        class="check-box"
                                    />
                                </VCol>

                                <VCol
                                    :cols="12"
                                    :md="4"
                                    :xs="12"
                                >
                                    <VCheckbox
                                        v-model="formCompany.isLogistics"
                                        :label="$t('companies.logisticsCompany')"
                                        color="#24a69a"
                                        class="check-box"
                                    />
                                </VCol>

                                <VCol
                                    :cols="12"
                                    :md="4"
                                    :xs="12"
                                >
                                    <VCheckbox
                                        v-model="formCompany.isProductCompany"
                                        :label="$t('companies.productCompany')"
                                        color="#24a69a"
                                        class="check-box"
                                    />
                                </VCol>
                            </VRow>

                            <VRow>
                                <VCol
                                    :cols="12"
                                    :md="6"
                                    :xs="12"
                                    class="pb-0"
                                >
                                    <VTextField
                                        v-model="formCompany.name"
                                        :label="$t('companies.companyName')"
                                        variant="outlined"
                                        density="compact"
                                        type="text"
                                        :rules="nameRules"
                                    />
                                </VCol>

                                <VCol
                                    :cols="12"
                                    :md="6"
                                    :xs="12"
                                    class="pb-0"
                                >
                                    <VTextField
                                        v-model="formAddress.street"
                                        :label="$t('address.street')"
                                        variant="outlined"
                                        density="compact"
                                        type="text"
                                        :rules="streetRules"
                                    />
                                </VCol>
                            </VRow>

                            <VRow class="mt-0">
                                <VCol
                                    :cols="12"
                                    :md="6"
                                    :xs="12"
                                    class="pb-0"
                                >
                                    <VTextField
                                        v-model="formAddress.zip"
                                        :label="$t('address.zip')"
                                        variant="outlined"
                                        density="compact"
                                        type="text"
                                        :rules="zipRules"
                                    />
                                </VCol>

                                <VCol
                                    :cols="12"
                                    :md="6"
                                    :xs="12"
                                    class="pb-0"
                                >
                                    <VTextField
                                        v-model="formAddress.houseNo"
                                        :label="$t('address.houseNumber')"
                                        variant="outlined"
                                        density="compact"
                                        type="text"
                                        :rules="houseNumberRules"
                                    />
                                </VCol>
                            </VRow>

                            <VRow class="mt-0">
                                <VCol
                                    :cols="12"
                                    :md="6"
                                    :xs="12"
                                    class="pb-0"
                                >
                                    <VTextField
                                        v-model="formAddress.city"
                                        :label="$t('address.city')"
                                        variant="outlined"
                                        density="compact"
                                        type="text"
                                        :rules="cityRules"
                                    />
                                </VCol>

                                <VCol
                                    :cols="12"
                                    :md="6"
                                    :xs="12"
                                    class="pb-0"
                                >
                                    <VSelect
                                        v-model="formAddress.country"
                                        :label="t('address.country')"
                                        :placeholder="t('address.country')"
                                        :items="countries"
                                        :rules="countryRules"
                                        variant="outlined"
                                    />
                                </VCol>
                            </VRow>

                            <VRow class="mt-0">
                                <VCol
                                    :cols="12"
                                    :md="6"
                                    :xs="12"
                                    class="pb-0"
                                >
                                    <VTextField
                                        v-model="formCompany.contact"
                                        :label="$t('companies.contact')"
                                        variant="outlined"
                                        density="compact"
                                        type="text"
                                        :rules="contactRules"
                                    />
                                </VCol>

                                <VCol
                                    :cols="12"
                                    :md="6"
                                    :xs="12"
                                    class="pb-0"
                                >
                                    <VTextField
                                        v-model="formCompany.email"
                                        :label="$t('companies.email')"
                                        variant="outlined"
                                        density="compact"
                                        type="text"
                                        :rules="emailRules"
                                    />
                                </VCol>
                            </VRow>

                            <VRow class="mt-0">
                                <VCol
                                    :cols="12"
                                    :md="12"
                                    :xs="12"
                                    class="pb-0"
                                >
                                    <VTextField
                                        v-model="formCompany.fullAddress"
                                        :label="$t('address.fullGoogleAddress')"
                                        variant="outlined"
                                        density="compact"
                                        type="text"
                                    >
                                        <template #append-inner>
                                            <span>{{ $t('autofill') }}</span><PhosphorIconMagicWand :size="24" />
                                        </template>
                                    </VTextField>
                                </VCol>
                            </VRow>

                            <VRow class="mt-0">
                                <VCol
                                    :cols="12"
                                    :md="12"
                                    :xs="12"
                                    class="pb-0"
                                >
                                    <VTextarea
                                        v-model="formCompany.description"
                                        :label="$t('companies.description')"
                                        variant="outlined"
                                    />
                                </VCol>
                            </VRow>

                            <VRow
                                v-if="formCompany.isLogistics"
                                class="mt-0"
                            >
                                <VCol
                                    :cols="12"
                                    :md="12"
                                    :xs="12"
                                    class="pb-0"
                                >
                                    <VSelect
                                        v-model="formCompany.typeTransports"
                                        :items="uniqBy(transportItem, 'value')"
                                        multiple
                                        variant="outlined"
                                        class="type-transports"
                                        :label="t('companies.typeOfTransport')"
                                        :placeholder="t('companies.selectTypeOfTransport')"
                                    />
                                </VCol>
                            </VRow>

                            <VRow>
                                <ImageUploadComponent @image-changed="imageChanged" />
                            </VRow>
                        </VCol>

                        <VCol
                            :cols="12"
                            :md="6"
                            :xs="12"
                            class="mt-4"
                        >
                            <Map
                                :address-groups="[{ addresses, color: 'blue', connectLine: false }]"
                                :is-marker-clicked="mapClicked"
                                :show-markers="showMarkers"
                                :zoom="zoom"
                                @point-clicked="getCoordination"
                                @get-zoom="getZoom"
                            />
                        </VCol>
                    </VRow>

                    <div
                        v-if="textFieldState.length > 0"
                        class="mt-3"
                    >
                        <VRow
                            v-for="textField in textFieldState"
                            :key="textField.id"
                        >
                            <VCol
                                v-if="textField.id !== 0"
                                :cols="12"
                                :md="6"
                                :xs="12"
                            >
                                <VRow>
                                    <VCol
                                        :cols="12"
                                        :md="9"
                                        :xs="12"
                                    >
                                        <h2>#{{ textField.id }} {{ $t('companies.companySite') }}</h2>
                                    </VCol>

                                    <VCol
                                        :cols="12"
                                        :md="3"
                                        :xs="12"
                                    >
                                        <VBtn
                                            variant="text"
                                            class="remove-site"
                                            @click="removeTextField(textField.id)"
                                        >
                                            {{ $t('companies.removeCompanySite') }}
                                        </VBtn>
                                    </VCol>
                                </VRow>

                                <VRow class="mt-0">
                                    <VCol
                                        :cols="12"
                                        :md="6"
                                        :xs="12"
                                        class="pb-0"
                                    >
                                        <VTextField
                                            v-model="textField.name"
                                            :label="$t('companies.siteName')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="nameRules"
                                        />
                                    </VCol>

                                    <VCol
                                        :cols="12"
                                        :md="6"
                                        :xs="12"
                                        class="pb-0"
                                    >
                                        <VTextField
                                            v-model="textField.address.street"
                                            :label="$t('address.street')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="streetRules"
                                        />
                                    </VCol>
                                </VRow>

                                <VRow class="mt-0">
                                    <VCol
                                        :cols="12"
                                        :md="6"
                                        :xs="12"
                                        class="pb-0"
                                    >
                                        <VTextField
                                            v-model="textField.address.postcode"
                                            :label="$t('address.zip')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="zipRules"
                                        />
                                    </VCol>

                                    <VCol
                                        :cols="12"
                                        :md="6"
                                        :xs="12"
                                        class="pb-0"
                                    >
                                        <VTextField
                                            v-model="textField.address.houseNo"
                                            :label="$t('address.houseNumber')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="houseNumberRules"
                                        />
                                    </VCol>
                                </VRow>

                                <VRow class="mt-0">
                                    <VCol
                                        :cols="12"
                                        :md="6"
                                        :xs="12"
                                        class="pb-0"
                                    >
                                        <VTextField
                                            v-model="textField.address.city"
                                            :label="$t('address.city')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="cityRules"
                                        />
                                    </VCol>

                                    <VCol
                                        :cols="12"
                                        :md="6"
                                        :xs="12"
                                        class="pb-0"
                                    >
                                        <VSelect
                                            v-model="textField.address.country"
                                            :label="t('address.country')"
                                            :placeholder="t('address.country')"
                                            :items="countries"
                                            :rules="countryRules"
                                        />
                                    </VCol>
                                </VRow>

                                <VRow>
                                    <ImageUploadComponent @image-changed="(image) => onFileChangedSite(image, textField.id)" />
                                </VRow>
                            </VCol>

                            <VCol
                                v-if="textField.id !== 0"
                                :cols="12"
                                :md="6"
                                :xs="12"
                            >
                                <div class="map">
                                    <Map
                                        :address-groups="[{ addresses: mapAddressesSite[textField.id]?.[0]?.fullAddress ? [mapAddressesSite[textField.id]?.[0]?.fullAddress] : addressesSite, color: 'blue', connectLine: false }]"
                                        :is-marker-clicked="mapClickedSite[textField.id]"
                                        :show-markers="showMarkersSite[textField.id]"
                                        :zoom="zoomSite[textField.id]"
                                        @point-clicked="(coord) => getCoordinationSite(coord, textField.id)"
                                        @get-zoom="((zoomMap: number) => getZoomSite(zoomMap, textField.id))"
                                    />
                                </div>
                            </VCol>
                        </VRow>
                    </div>

                    <VRow>
                        <VCol
                            :cols="12"
                            :md="6"
                            :xs="12"
                        >
                            <VBtn
                                variant="text"
                                class="add-site"
                                @click="addTextField"
                            >
                                {{ $t('companies.addCompanySite') }}
                            </VBtn>
                        </VCol>
                    </VRow>

                    <VRow>
                        <VCol
                            :cols="12"
                            :md="6"
                            :xs="12"
                        >
                            <VBtn
                                variant="text"
                                class="submit-btn"
                                height="45"
                                @click="addCompany"
                            >
                                {{ $t('companies.addCompany') }}
                            </VBtn>
                        </VCol>
                    </VRow>
                </VForm>
            </div>
        </VContainer>
    </NuxtLayout>
</template>
