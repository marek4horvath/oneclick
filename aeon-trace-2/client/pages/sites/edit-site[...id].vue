<script setup lang="ts">
import mimedb from 'mime-db'
import { uniqBy } from 'lodash'
import { b64toBlob } from '@/helpers/convert'
import { getAddressGoogle } from '@/helpers/getAddressGoogle'

definePageMeta({
    title: 'sites.editSite',
    name: 'edit-site',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const url = route.path
const parts = url.split('/').pop()
const siteId = ref(parts)
const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)

const sitesStore = useSitesStore()
const countriesStore = useCountriesStore()
const siteImagesStore = useSiteImagesStore()

const textFieldState = ref<any[]>({
    id: 1,
    name: '',
    address: {
        street: '',
        postcode: null,
        country: '',
        city: '',
        houseNo: '',
    },
    mapClicked: false,
})

const newImageFile = ref<string>()
const filePreview = ref<string>('')

const form = ref(null)
const valid = ref(false)
const countries = ref<string []>([])

const addresses = ref<Record<string, any>[]>([{
    lat: 50.8503460,
    lng: 4.3517210,
}])

const addressClickMap = ref({
    street: '',
    zip: null,
    country: '',
    city: '',
    houseNo: '',
})

const mapClicked = ref(false)
const showMarkers = ref(false)
const imageWasChanged = ref(false)

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

const onFileChanged = (image: string) => {
    imageWasChanged.value = true
    newImageFile.value = image
}

const fetchSite = async () => {
    const site = await sitesStore.fetchSiteById(siteId.value)

    textFieldState.value = {
        id: site.id,
        name: site.name,
        siteImages: site.siteImages,
        address: {
            street: site.address.street,
            postcode: site.address.postcode,
            country: site.address.country,
            city: site.address.city,
            houseNo: site.address.houseNo,
        },
    }

    filePreview.value = site.siteImages?.length ? `${backendUrl.value}/media/company_sites_images/${site.siteImages[0].image}` : ''
}

const goBack = () => {
    router.go(-1)
}

const editSite = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const editSiteResponse = await sitesStore.updateSite(textFieldState.value.id, {
        name: textFieldState.value.name,
        address: {
            street: textFieldState.value.address.street,
            postcode: textFieldState.value.address.postcode,
            country: textFieldState.value.address.country,
            city: textFieldState.value.address.city,
            houseNo: textFieldState.value.address.houseNo,
        },
    })

    if (!editSiteResponse) {
        return
    }

    if (!imageWasChanged.value || !newImageFile.value) {
        if (imageWasChanged.value && !newImageFile.value) {
            siteImagesStore.deleteSiteImage(textFieldState.value?.siteImages[0]?.id)
        }
    }

    if (newImageFile.value) {
        const siteImage: Blob = b64toBlob(newImageFile.value.url)
        const imageType = mimedb[siteImage.type]
        const formData = new FormData()

        formData.append('file', siteImage, `${siteId.value}.${imageType.extensions[0]}`)
        formData.append('companySite', `/api/company_sites/${siteId.value}`)
        if (textFieldState.value?.siteImages[0]?.id) {
            siteImagesStore.deleteSiteImage(textFieldState.value?.siteImages[0]?.id)
        }

        const addSiteImage = await siteImagesStore.createSiteImage(formData)

        if (!addSiteImage) {
            return
        }
    }

    goBack()
}

const getCoordination = (coord: any) => {
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
    })
}

watch(() => textFieldState.value, (newValue, oldVal) => {
    const addressData: any = {
        street: '',
        zip: null,
        country: '',
        city: '',
        houseNo: '',
    }

    showMarkers.value = false
    if (newValue && !newValue.mapClicked) {
        addressData.street = newValue.address.street || ''
        addressData.zip = newValue.address.postcode || null
        addressData.country = newValue.address.country || ''
        addressData.city = newValue.address.city || ''
        addressData.houseNo = newValue.address.houseNo || ''

        const isAllFilled = Object.entries(addressData).every(([__, value]) => {
            return value !== '' && value !== null && value !== undefined
        })

        if (isAllFilled) {
            const fullAddress: string | any = `${addressData.street} ${addressData.houseNo}, ${addressData.zip} ${addressData.city} - ${addressData.country}`

            addresses.value = [fullAddress]

            showMarkers.value = true
        }
    } else {
        addressData.street = oldVal.address.street || ''
        addressData.zip = oldVal.address.postcode || null
        addressData.country = oldVal.address.country || ''
        addressData.city = oldVal.address.city || ''
        addressData.houseNo = oldVal.address.houseNo || ''

        const isAllFilled = Object.entries(addressData).every(([__, value]) => {
            return value !== '' && value !== null && value !== undefined
        })

        if (isAllFilled) {
            const fullAddress: string | any = `${addressData.street} ${addressData.houseNo}, ${addressData.zip} ${addressData.city} - ${addressData.country}`

            addresses.value = [fullAddress]

            showMarkers.value = true
        }
        newValue.mapClicked = false
    }
}, { immediate: true, deep: true })

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

            textFieldState.value.address = {
                street: addressData.street,
                postcode: addressData.zip,
                country: addressData.country,
                city: addressData.city,
                houseNo: addressData.houseNo,
            }

            textFieldState.value.mapClicked = true
        }
    }
}, { immediate: true, deep: true })

onMounted(async () => {
    await countriesStore.fetchCountries()
    await fetchSite()
    countries.value = countriesStore.countries
})
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer fluid>
            <div class="company">
                <h2 class="mb-5">
                    {{ $t('sites.editSite') }}
                </h2>
                <VForm
                    ref="form"
                    v-model="valid"
                >
                    <VRow>
                        <VCol
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
                                    <h2>{{ $t('companies.companySite') }}</h2>
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
                                        v-model="textFieldState.name"
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
                                        v-model="textFieldState.address.street"
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
                                        v-model="textFieldState.address.postcode"
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
                                        v-model="textFieldState.address.houseNo"
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
                                        v-model="textFieldState.address.city"
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
                                        v-model="textFieldState.address.country"
                                        :label="t('address.country')"
                                        :placeholder="t('address.country')"
                                        :items="uniqBy(countries, 'value')"
                                        :rules="countryRules"
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
                        >
                            <div class="map">
                                <Map
                                    :address-groups="[{ addresses, color: 'blue', connectLine: false }]"
                                    :is-marker-clicked="mapClicked"
                                    :show-markers="showMarkers"
                                    :zoom="10"
                                    @point-clicked="getCoordination"
                                />
                            </div>
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
                                @click="editSite"
                            >
                                {{ $t('sites.editSite') }}
                            </VBtn>
                        </VCol>
                    </VRow>
                </VForm>
            </div>
        </VContainer>
    </NuxtLayout>
</template>
