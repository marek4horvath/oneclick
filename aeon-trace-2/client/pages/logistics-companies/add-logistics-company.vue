<script setup lang="ts">
import mimedb from 'mime-db'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { uniqBy } from 'lodash'
import { PhosphorIconMagicWand } from '#components'
import { b64toBlob } from '~/helpers/convert.ts'
import { useLogisticsStore } from '~/stores/logistics.ts'
import { useTransportationTemplateStore } from '~/stores/transportationTemplates.ts'
import type { SelectItem } from '~/types/selectItem.ts'
import { getAddressGoogle } from '~/utils/getAddressGoogle'

definePageMeta({
    title: 'page.logistics.addCompany.title',
    name: 'add-logistics-company',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { t } = useI18n()
const router = useRouter()
const { $event } = useNuxtApp()
const newImageFile = ref<string>()
const transportationTemplateStore = useTransportationTemplateStore()
const logisticsStore = useLogisticsStore()
const companiesStore = useCompaniesStore()
const formValidation = ref(null)

const form = ref<Company>({
    id: 0,
    name: '',
    address: {
        street: '',
        postcode: null,
        country: '',
        city: '',
        houseNo: '',
    },
    email: '',
    contact: '',
    description: '',
    isLogistics: true,
    isProductCompany: false,
    typeTransports: [],
})

const addresses = ref<Record<string, any>[]>([{
    lat: 50.8503460,
    lng: 4.3517210,
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

let isUpdatingAddress = false
const zoom = ref<number>(10)
const showMarkers = ref(false)
const mapClicked = ref(true)

const addressClickMap = ref({
    street: '',
    postcode: null,
    country: '',
    city: '',
    houseNo: '',
})

const getCoordination = (coord: any) => {
    if (isUpdatingAddress) {
        return
    }

    isUpdatingAddress = true
    showMarkers.value = false
    mapClicked.value = true

    getAddressGoogle(coord.lat, coord.lng).then(address => {
        addressClickMap.value = {
            street: address?.street,
            postcode: address?.zip,
            country: address?.country,
            city: address?.city,
            houseNo: address?.houseNo,
        }

        form.value.address.street = address?.street
        form.value.address.postcode = address?.zip
        form.value.address.country = address?.country
        form.value.address.city = address?.city
        form.value.address.houseNo = address?.houseNo
    }).finally(() => {
        isUpdatingAddress = false
    })
}

const getZoom = (zoomLevel: number) => {
    zoom.value = zoomLevel
}

const imageChanged = (image: string) => {
    newImageFile.value = image
}

interface Company {
    id: number
    name: string
    street: string
    postcode: number | null
    country: string
    city: string
    email: string
    contact: string
    description: string
    houseNo: string
    isLogistics: boolean
    isProductCompany: boolean
    typeTransports: string[]
}

const isFormValid = ref(false)

const transportTypesItems = ref<SelectItem[]>([])
const countriesItems = ref<SelectItem[]>([])

onMounted(async () => {
    await transportationTemplateStore.fetchTransportTypes()
    await logisticsStore.fetchCountries()
    countriesItems.value = logisticsStore.getCountries
    transportTypesItems.value = transportationTemplateStore.transportTypes.map((type: any) => {
        return {
            value: type.id,
            title: type.name,
        }
    })
})

const addressInput = ref<HTMLInputElement | null>(null)

onMounted(() => {
    if (addressInput.value) {
        const autocomplete = new google.maps.places.Autocomplete(addressInput.value, {
            types: ['geocode'],
        })

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace()
            if (!place.geometry || !place.geometry.location) {
                console.error(`No details available for input: ${place.name}`)

                return
            }

            const addressComponents = place.address_components
            if (addressComponents) {
                const street = addressComponents.find((component: any) => component.types.includes('route'))?.long_name || ''
                const houseNo = addressComponents.find((component: any) => component.types.includes('street_number'))?.long_name || ''

                const city = addressComponents.find((component: any) =>
                    component.types.includes('sublocality') || component.types.includes('locality'),
                )?.long_name || ''

                const postcode = addressComponents.find((component: any) => component.types.includes('postal_code'))?.long_name || ''
                const country = addressComponents.find((component: any) => component.types.includes('country'))?.long_name || ''

                form.value.address.street = street
                form.value.address.houseNo = houseNo
                form.value.address.city = city
                form.value.address.postcode = postcode
                form.value.address.country = country

                const lat = place.geometry.location.lat()
                const lng = place.geometry.location.lng()

                addresses.value = [{ lat, lng }]
                showMarkers.value = true
                mapClicked.value = false
            }
        })
    }
})

const fullAddress = computed(() => {
    if (form.value.address.street) {
        const { street, houseNo, postcode, city, country } = form.value.address

        return `${street} ${houseNo}, ${postcode}, ${city}, ${country}`
    } else {
        return ' '
    }
})

const updateAddress = (newAddress: string) => {
    const parts = newAddress?.split(', ')
    if (parts.length === 2) {
        const [streetHouseNo, postcodeCityCountry] = parts
        const [street, houseNo] = streetHouseNo.split(' ')
        const [postcode, cityCountry] = postcodeCityCountry.split(', ')
        const [city, country] = cityCountry.split(',  ')

        form.value.address.street = street
        form.value.address.houseNo = houseNo
        form.value.address.postcode = postcode
        form.value.address.city = city
        form.value.address.country = country
    }
}

watch(
    () => ({
        street: form.value.address.street,
        postcode: form.value.address.postcode,
        country: form.value.address.country,
        city: form.value.address.city,
        houseNo: form.value.address.houseNo,
    }),
    (newValue: any, oldVal: any) => {
        const addressData: any = {
            street: '',
            postcode: null,
            country: '',
            city: '',
            houseNo: '',
        }

        const { street, postcode, country, city, houseNo } = newValue

        showMarkers.value = false
        mapClicked.value = true

        if (street && postcode && country && city && houseNo && !newValue.mapClicked) {
            addressData.street = street
            addressData.postcode = postcode
            addressData.country = country
            addressData.city = city
            addressData.houseNo = houseNo

            const isAllFilled = Object.entries(addressData).every(([__, value]) => value !== '' && value !== null && value !== undefined)

            if (isAllFilled) {
                const fullCompanyAddress: string | any = `${addressData.street} ${addressData.houseNo}, ${addressData.postcode} ${addressData.city} - ${addressData.country}`

                addresses.value = [fullCompanyAddress]

                showMarkers.value = true
                mapClicked.value = false
            }
        } else {
            addressData.street = oldVal?.street || ''
            addressData.postcode = oldVal?.postcode || null
            addressData.country = oldVal?.country || ''
            addressData.city = oldVal?.city || ''
            addressData.houseNo = oldVal?.houseNo || ''

            const isAllFilled = Object.entries(addressData).every(([__, value]) => value !== '' && value !== null && value !== undefined)

            if (isAllFilled) {
                const fullCompanyAddress: string | any = `${addressData.street} ${addressData.houseNo}, ${addressData.postcode} ${addressData.city} - ${addressData.country}`

                addresses.value = [fullCompanyAddress]

                showMarkers.value = true
            }

            newValue.mapClicked = false
        }
    },
    { immediate: true, deep: true },
)

watch(() => addressClickMap.value, (newValue: any) => {
    const addressData: any = {
        street: '',
        postcode: null,
        country: '',
        city: '',
        houseNo: '',
    }

    showMarkers.value = false

    if (newValue) {
        addressData.street = newValue.street || ''
        addressData.postcode = newValue.postcode || null
        addressData.country = newValue.country || ''
        addressData.city = newValue.city || ''
        addressData.houseNo = newValue.houseNo || ''

        const isAllFilled = Object.entries(addressData).every(([__, value]) => {
            return value !== '' && value !== null && value !== undefined
        })

        if (isAllFilled) {
            showMarkers.value = false
            form.value.address = {
                street: addressData.street,
                postcode: addressData.postcode,
                country: addressData.country,
                city: addressData.city,
                houseNo: addressData.houseNo,
                mapClicked: true,
            }
        }
    }
}, { immediate: true, deep: true })

const submitHandler = async () => {
    formValidation.value.validate()

    if (!isFormValid.value) {
        return
    }

    try {
        const response = await companiesStore.createCompany({
            name: form.value.name,
            email: form.value.email,
            description: form.value.description,
            address: form.value.address,
            logisticsCompany: form.value.isLogistics,
            phone: form.value.contact,
            productCompany: form.value.isProductCompany,
            typeOfTransport: form.value.typeTransports.map((id: string) => `/api/transport_types/${id}`),
        })

        if (!response) {
            return
        }

        if (newImageFile.value) {
            const image = b64toBlob(newImageFile.value.url)
            const formData = new FormData()

            const imageType = mimedb[image.type]

            formData.append('file', image, `${response.id}.${imageType.extensions[0]}`)

            const companyLogo = await companiesStore.uploadCompanyLogo(response.id, formData)

            if (!companyLogo) {
                return
            }
        }

        await router.push(`/logistics-companies/detail/${response.id}`)
        $event('saveNewLogisticsCompany')
    } catch (error) {
        console.error('Failed to add logistics company:', error)
    }
}
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer fluid>
            <div
                class="add-company"
                style="margin: 0 40px 0 0; background-color: white"
            >
                <div class="company-header">
                    <h1 style="color: #26A69A">
                        {{ t('logistics.addLogisticsCompany') }}
                    </h1>
                </div>
                <div class="form-add-company">
                    <VForm
                        ref="formValidation"
                        v-model="isFormValid"
                    >
                        <VRow>
                            <VCol cols="6">
                                <VRow style="margin-bottom: -55px; align-items: center; justify-content: center; display: flex">
                                    <VCol
                                        cols="6"
                                        style="display: flex; justify-content: center; align-items: center"
                                    >
                                        <VCheckbox
                                            v-model="form.isLogistics"
                                            :label="t('logistics.logisticsCompany')"
                                            color="#26A69A"
                                        />
                                    </VCol>
                                    <VCol
                                        cols="6"
                                        style="display: flex; justify-content: center; align-items: center"
                                    >
                                        <VCheckbox
                                            v-model="form.isProductCompany"
                                            :label="t('logistics.productCompany')"
                                            color="#26A69A"
                                        />
                                    </VCol>
                                </VRow>
                                <VRow style="margin-bottom: -35px">
                                    <VCol cols="6">
                                        <VTextField
                                            v-model="form.name"
                                            :label="t('logistics.formName')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="nameRules"
                                        />
                                    </VCol>
                                    <VCol cols="6">
                                        <VTextField
                                            v-model="form.address.street"
                                            :label="t('logistics.formStreet')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="streetRules"
                                        />
                                    </VCol>
                                </VRow>
                                <VRow style="margin-bottom: -35px">
                                    <VCol cols="6">
                                        <VTextField
                                            v-model="form.address.postcode"
                                            :label="t('logistics.formZip')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="zipRules"
                                        />
                                    </VCol>
                                    <VCol cols="6">
                                        <VTextField
                                            v-model="form.address.houseNo"
                                            :label="t('logistics.formHouseNo')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="houseNumberRules"
                                        />
                                    </VCol>
                                </VRow>
                                <VRow style="margin-bottom: -35px">
                                    <VCol cols="6">
                                        <VTextField
                                            v-model="form.address.city"
                                            :label="t('logistics.formCity')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="cityRules"
                                        />
                                    </VCol>
                                    <VCol cols="6">
                                        <VSelect
                                            v-model="form.address.country"
                                            :label="t('logistics.formCountry')"
                                            :items="countriesItems"
                                            :rules="countryRules"
                                            variant="outlined"
                                        />
                                    </VCol>
                                </VRow>
                                <VRow style="margin-bottom: -35px">
                                    <VCol cols="6">
                                        <VTextField
                                            v-model="form.contact"
                                            :label="t('logistics.formContact')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="contactRules"
                                        />
                                    </VCol>
                                    <VCol cols="6">
                                        <VTextField
                                            v-model="form.email"
                                            :label="t('logistics.formEmail')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :rules="emailRules"
                                        />
                                    </VCol>
                                </VRow>
                                <VRow>
                                    <VCol cols="12">
                                        <VTextField
                                            ref="addressInput"
                                            :label="t('logistics.formFullAddress')"
                                            variant="outlined"
                                            density="compact"
                                            type="text"
                                            :model-value="fullAddress"
                                            @update:model-value="updateAddress"
                                        >
                                            <template #append-inner>
                                                <span>{{ t('autofill') }}</span><PhosphorIconMagicWand :size="24" />
                                            </template>
                                        </VTextField>
                                    </VCol>
                                </VRow>
                                <VTextarea
                                    v-model="form.description"
                                    :label="t('logistics.formDescription')"
                                    variant="outlined"
                                />
                                <VSelect
                                    v-if="form.isLogistics"
                                    v-model="form.typeTransports"
                                    :items="uniqBy(transportTypesItems, 'value')"
                                    multiple
                                    variant="outlined"
                                    :label="t('logistics.formTypeTransports')"
                                />
                                <hr style="margin-bottom: 25px">
                                <ImageUploadComponent @image-changed="imageChanged" />
                            </VCol>
                            <VCol cols="6">
                                <div class="map">
                                    <Map
                                        :address-groups="[{ addresses, color: 'blue', connectLine: false }]"
                                        :is-marker-clicked="mapClicked"
                                        :show-markers="showMarkers"
                                        :zoom="zoom"
                                        @point-clicked="getCoordination"
                                        @get-zoom="getZoom"
                                    />
                                </div>
                            </VCol>
                        </VRow>
                        <VRow style="margin-top: -60px">
                            <VCol
                                cols="6"
                                class="modal-footer"
                            >
                                <VBtn
                                    class="submit-btn add-company-button"
                                    @click="submitHandler"
                                >
                                    {{ t("logistics.addLogisticsCompanyCaps") }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </div>
            </div>
        </VContainer>
    </NuxtLayout>
</template>

<style scoped>
.add-company {
    width: 100%;
    padding: 20px;
}

.company-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.form-add-company {
    margin-top: 20px;
}

.add-company-button {
    padding-inline: 1rem;
    padding-block: 0.5rem;
    display: inline-block;
    border-radius: unset;
    flex: 1;
    transition: 0.5s all;
    width: 100%;
    height: 45px !important;

    &:hover {
        background-color: rgba(167, 217, 212, 1) !important;
        color: #000000 !important;
    }

    &.submit-btn {
        background-color: rgba(38, 166, 154, 1);
        color: #FFFFFF;
        transition: 0.5s all;

        &:hover {
            background-color: rgba(167, 217, 212, 1);
        }
    }
}
</style>
