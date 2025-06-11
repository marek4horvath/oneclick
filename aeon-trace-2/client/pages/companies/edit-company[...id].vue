<script setup lang="ts">
import mimedb from 'mime-db'
import { uniqBy } from 'lodash'
import { b64toBlob } from '@/helpers/convert'
import { getAddressGoogle } from '@/helpers/getAddressGoogle'
import type { SelectItem } from '@/types/selectItem'

definePageMeta({
    title: 'page.companies.edit.title',
    name: 'edit-companies',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const logisticsQuery = route.query.logistics
const form = ref(null)
const valid = ref(false)
const countries = ref<string []>([])
const filePreview = ref<string>('')
const countriesStore = useCountriesStore()
const companiesStore = useCompaniesStore()
const transportTypesStore = useTransportTypesStore()
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)

const formCompany = ref({
    id: 0,
    name: '',
    street: '',
    zip: null,
    country: '',
    city: '',
    email: '',
    contact: '',
    houseNo: '',
    description: '',
    typeTransports: [],
    isLogistics: logisticsQuery === 'true',
    isProductCompany: logisticsQuery === 'false',
    sites: [],
    image: [],
    products: [],
    users: [],
    mapClicked: false,
})

const newImageFile = ref<string>()

const addresses = ref<Record<string, any>[]>([{
    lat: 50.8503460,
    lng: 4.3517210,
}])

const showMarkers = ref(false)
const mapClicked = ref(true)
const zoom = ref<number>(10)
let isUpdatingAddress = false

const addressClickMap = ref({
    street: '',
    zip: null,
    country: '',
    city: '',
    houseNo: '',
})

const transportItem = ref<SelectItem[]>([{
    value: '',
    title: '',
}])

const url = route.path
const companyId = url.split('/').pop()

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

const onFileChanged = (image: string) => {
    newImageFile.value = image
}

const fetchCompanyData = async () => {
    await companiesStore.fetchCompanyById(companyId)

    const company = companiesStore.getCompanyById(companyId)

    formCompany.value = {
        id: company.id,
        name: company.name,
        street: company.address.street,
        zip: company.address.postcode,
        country: company.address.country,
        city: company.address.city,
        email: company.email,
        contact: company.phone,
        houseNo: company.address.houseNo,
        description: company.description,
        typeTransports: company.typeOfTransport.map((transport: any) => transport.split('/').pop()),
        isLogistics: company.logisticsCompany,
        isProductCompany: company.productCompany,
        sites: [],
        image: company.companyLogo,
    }

    if (company.companyLogo !== '') {
        filePreview.value = `${backendUrl.value}/media/company_logos/${company.companyLogo}`
    }
}

const editCompany = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const formData = new FormData()

    const selectedTransportIds = (formCompany.value.typeTransports || []).map((typeTransportId: string) => {
        return `/api/transport_types/${typeTransportId}`
    })

    const editCompanyResponse = await companiesStore.updateCompany(companyId, {
        name: formCompany.value.name,
        email: formCompany.value.email,
        phone: formCompany.value.contact,
        description: formCompany.value.description,
        logisticsCompany: formCompany.value.isLogistics,
        productCompany: formCompany.value.isProductCompany,
        typeOfTransport: selectedTransportIds || [],
        address: {
            street: formCompany.value.street,
            city: formCompany.value.city,
            postcode: formCompany.value.postcode,
            houseNo: formCompany.value.houseNo,
            country: formCompany.value.country,
        },
    })

    if (!editCompanyResponse) {
        return
    }

    if (newImageFile.value?.url) {
        const image = b64toBlob(newImageFile.value.url)

        const imageType = mimedb[image.type]

        formData.append('file', image, `${companyId}.${imageType.extensions[0]}`)

        await companiesStore.uploadCompanyLogo(companyId, formData)
    }

    if (formCompany.value.isLogistics && !formCompany.value.isProductCompany) {
        await router.push('/logistics')
    } else {
        await router.push('/companies')
    }
}

const getZoom = (zoomLevel: number) => {
    zoom.value = zoomLevel
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

watch(
    () => ({
        street: formCompany.value.street,
        zip: formCompany.value.zip,
        country: formCompany.value.country,
        city: formCompany.value.city,
        houseNo: formCompany.value.houseNo,
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
            formCompany.value = {
                street: addressData.street,
                zip: addressData.zip,
                country: addressData.country,
                city: addressData.city,
                houseNo: addressData.houseNo,
                mapClicked: true,
            }
        }
    }
}, { immediate: true, deep: true })

onMounted(async () => {
    await countriesStore.fetchCountries()
    await transportTypesStore.fetchTransportTypes()
    await fetchCompanyData()
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
                <h2>{{ $t('page.companies.edit.title') }}</h2>
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
                                        v-model="formCompany.street"
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
                                        v-model="formCompany.zip"
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
                                        v-model="formCompany.houseNo"
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
                                        v-model="formCompany.city"
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
                                        v-model="formCompany.country"
                                        :label="t('address.country')"
                                        :placeholder="t('address.country')"
                                        :items="uniqBy(countries, 'value')"
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
                                        class="type-transports"
                                        :label="t('logisticsTemplate.typeOfTransport')"
                                        :placeholder="t('logisticsTemplate.typeOfTransport')"
                                        variant="outlined"
                                    />
                                </VCol>
                            </VRow>

                            <VRow>
                                <ImageUploadComponent
                                    :image="filePreview"
                                    @image-changed="onFileChanged"
                                />
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
                                @click="editCompany"
                            >
                                {{ $t('companies.editCompany') }}
                            </VBtn>
                        </VCol>
                    </VRow>
                </VForm>
            </div>
        </VContainer>
    </NuxtLayout>
</template>
