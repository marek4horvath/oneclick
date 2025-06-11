<script setup lang="ts">
import mimedb from 'mime-db'
import { b64toBlob } from '@/helpers/convert'
import { getAddressGoogle } from '@/helpers/getAddressGoogle'

definePageMeta({
    title: 'companies.addCompanySite',
    name: 'add-site',
    layout: 'dashboard',
    middleware: 'auth',
    displayTitle: false,
})

const { t } = useI18n()
const route = useRoute()
const url = route.path
const parts = url.split('/').pop()
const companyId = ref(parts)
const sitesStore = useSitesStore()

const textFieldState = ref<any[]>([{
    id: 1,
    company: '',
    name: '',
    address: {
        street: '',
        postcode: null,
        country: '',
        city: '',
        houseNo: '',
    },
    mapClicked: false,
}])

const form = ref(null)
const valid = ref(false)
const countries = ref<string []>([])

const countriesStore = useCountriesStore()
const siteImagesStore = useSiteImagesStore()

const addressesSite = ref<Record<string, any>[]>([{
    lat: 50.8503460,
    lng: 4.3517210,
}])

const mapAddressesSite = ref<{ [id: number]: { fullAddress: string }[] }>({})
const zoomSite = ref<number[]>([])

const mapClickedSite = ref({
    1: true,
})

let isUpdatingAddress = false

const showMarkersSite = ref({
    1: false,
})

const addressClickMapSite = ref([])

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

const addSite = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    textFieldState.value.map(async (site: any) => {
        site.company = `/api/companies/${companyId.value}`

        const companySiteResponse = await sitesStore.createSite(site)

        if (!companySiteResponse) {
            return
        }

        if (site?.img?.url) {
            const formDataSite = new FormData()
            const image = b64toBlob(site.img.url)
            const imageType = mimedb[image.type]

            formDataSite.append('file', image, `${companySiteResponse.id}.${imageType.extensions[0]}`)
            formDataSite.append('companySite', `/api/company_sites/${companySiteResponse.id}`)

            await siteImagesStore.createSiteImage(formDataSite)
        }

        navigateTo(`/companies/detail/${companyId.value}`)
    })
}

const getZoomSite = (zoomLevel: number, id: number) => {
    zoomSite.value[id] = zoomLevel
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

onMounted(async () => {
    await countriesStore.fetchCountries()
    countries.value = [...new Set(countriesStore.countries)]
})
</script>

<template>
    <NuxtLayout has-back-button>
        <VContainer fluid>
            <div class="company">
                <h2 class="mb-5">
                    {{ $t('sites.addSite') }}
                </h2>
                <VForm
                    ref="form"
                    v-model="valid"
                >
                    <VRow
                        v-for="textField in textFieldState"
                        :key="textField.id"
                    >
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
                                    <h2>#{{ textField.id }} {{ $t('companies.companySite') }}</h2>
                                </VCol>

                                <VCol
                                    :cols="12"
                                    :md="3"
                                    :xs="12"
                                >
                                    <VBtn
                                        v-if="textField.id !== 1"
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
                                        variant="outlined"
                                    />
                                </VCol>
                            </VRow>

                            <VRow>
                                <ImageUploadComponent @image-changed="(image) => onFileChangedSite(image, textField.id)" />
                            </VRow>
                        </VCol>

                        <VCol
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
                                @click="addSite"
                            >
                                {{ $t('sites.addSite') }}
                            </VBtn>
                        </VCol>
                    </VRow>
                </VForm>
            </div>
        </VContainer>
    </NuxtLayout>
</template>
