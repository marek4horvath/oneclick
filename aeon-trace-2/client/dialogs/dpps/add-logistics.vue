<script lang="ts" setup>
import mimedb from 'mime-db'
import VueDatePicker from '@vuepic/vue-datepicker'
import ModalLayout from '@/dialogs/modalLayout.vue'
import '@vuepic/vue-datepicker/dist/main.css'
import { calculateDistance, getTravelDistance } from '~/helpers/getAddressGoogle'
import { b64toBlob } from '@/helpers/convert'
import type { MessageBag } from '@/types/index'

const { $listen } = useNuxtApp()
const { t } = useI18n()
const isAddLogisticsModalOpen = ref(false)

const dppStore = useDppStore()
const transportTypesStore = useTransportTypesStore()
const logisticsStore = useLogisticsStore()
const companiesStore = useCompaniesStore()
const startingCoordinates = ref()

const startingCompanyName = ref()
const destinationCompanyName = ref()

const nodeData = ref()
const supplyChain = ref()

const inputs = ref([])
const inputsData = ref({})

const isLoading = ref({
    nodeTo: true,
    parentDpp: true,
})

const closeAddLogisticsModal = () => {
    isAddLogisticsModalOpen.value = false
}

const formValuesSteps = ref([])

const formValues = ref({
    number_of_steps: 0,
    parent_dpp: null,
    toNode: null,
})

const form = ref(null)
const valid = ref(false)
const companies = ref([])
const dpps = ref([])
const productStepsDpps = ref([])
const logisticsTemplate = ref({})
const typeOfTransports = ref({})
const stepsData = ref<any[]>([])
const templateInput = ref({})
const step = ref(1)

const zoom = ref<number>(10)

const addressesDestinationPoint = ref<Record<string, any>[]>([{
    lat: 50.8503460,
    lng: 4.3517210,
}])

const mapsCoordinatesDestination = ref([{
    lat: 0,
    lng: 0,
}])

const activeMap = reactive({
    0: true,
})

const markerClicked = reactive({
    0: true,
})

const showMakers = reactive({
    0: false,
})

const refreshMap = reactive({
    0: false,
})

const fetchCompanies = async () => {
    companies.value = await companiesStore.fetchCompaniesListing(undefined, undefined, undefined, true, false, undefined, undefined)
}

const fetchDpps = async () => {
    if (!supplyChain.value?.dpps) {
        return
    }

    const dppPromises = supplyChain.value.dpps.map(async (dpp: any) => {
        const id = dpp.split('/').pop()
        const dppData = await dppStore.fetchDppPromise(id)

        return dppData.state === 'NOT_ASSIGNED' ? dppData : null
    })

    const productStepsDppsPromises = nodeData.value.productSteps.map(async (productStep: any) => {
        const id = productStep.split('/').pop()
        const productSteps = await useProductsStore().fetchProductStepById(id)

        return productSteps.dpp ? null : productSteps
    })

    const filteredProductStepsDpps = (await Promise.all(productStepsDppsPromises)).filter(Boolean)

    productStepsDpps.value = filteredProductStepsDpps

    const filteredDpps = (await Promise.all(dppPromises)).filter(Boolean)

    dpps.value = await Promise.all(filteredDpps)

    isLoading.value.parentDpp = false
}

const dppsOptions = computed(() => {
    const allDpps = [...(dpps.value || []), ...(productStepsDpps.value || [])]

    return allDpps
        .filter(dpp => dpp.state === 'NOT_ASSIGNED')
        .filter(dpp => {
            const dppNodeId = typeof dpp.node === 'string'
                ? dpp.node.split('/').pop()
                : dpp.nodeId

            const currentNodeId = nodeData.value?.id

            return dppNodeId === currentNodeId
        })
        .filter(dpp => !dpp.ongoingDpp)
        .filter(dpp => !dpp.createEmptyDpp)
        .filter((value, index, self) =>
            index === self.findIndex(item => item.id === value.id),
        )
        .map(dpp => ({
            ...dpp,
            name: `${dpp.id} - ${dpp.name}`,
        }))
})

const nodesToOptions = computed(() => {
    return nodeData.value?.children || []
})

const companiseOptions = computed(() => {
    return nodeData.value.companiesFromProductTemplates
})

const sitesOptions = ref({})

const fetchLogisticsTemplates = async (id: string, index: number) => {
    const transportType = await transportTypesStore.fetchTransportTypeById(id)

    logisticsTemplate.value[index] = transportType.logisticsTemplateData.map(template => {
        return {
            value: template.id,
            title: template.name,
        }
    })
}

watch(() => formValues.value.createQr, () => {
    formValues.value.createEmptyDpp = false
    formValues.value.createOngoingDpp = false
})

const userId = ref(null)
let isFetching = false

$listen('openAddLogisticsModal', async data => {
    if (isFetching) {
        return
    }

    isFetching = true
    step.value = 1
    formValuesSteps.value = []

    formValues.value = {
        number_of_steps: 0,
        parent_dpp: null,
        toNode: null,
    }

    addressesDestinationPoint.value = [{
        lat: 50.8503460,
        lng: 4.3517210,
    }]

    mapsCoordinatesDestination.value = [{
        lat: 0,
        lng: 0,
    }]

    isAddLogisticsModalOpen.value = true
    nodeData.value = data.nodeData
    isLoading.value.nodeTo = false
    supplyChain.value = data.supplyChain

    inputs.value = []

    nodeData.value.steps.forEach((step: any) => {
        step.inputs.forEach((input: any) => {
            inputs.value.push(input)
        })
    })

    await Promise.all([fetchDpps(), fetchCompanies()])

    const authStore = useAuthStore()

    userId.value = await authStore.getMyId()

    isFetching = false
})

const distance = ref({})

const typeOfTransportsTravelModes = {
    car: 'DRIVING',
    truck: 'DRIVING',
    train: 'TRANSIT',
}

const updateDistance = (index: number, latOrigin: number, lngOrigin: number, latDestination: number, lngDestination: number) => {
    if ((latOrigin === 0 && lngOrigin === 0) || (latDestination === 0 && lngDestination === 0)) {
        distance.value[index] = ''

        return
    }

    const typeOfTransport = typeOfTransports.value[index].filter(type => type.id === formValuesSteps.value[index].typeOfTransport)[0]?.name
    const travelMode = typeOfTransportsTravelModes[typeOfTransport]

    if (travelMode === undefined) {
        distance.value[index] = calculateDistance(
            latOrigin,
            lngOrigin,
            latDestination,
            lngDestination,
        )
    } else {
        getTravelDistance(
            travelMode,
            latOrigin,
            lngOrigin,
            latDestination,
            lngDestination,
        ).then(calculatedDistance => {
            distance.value[index] = calculatedDistance
        })
    }
}

const confirmFilled = async (indexStep: number) => {
    if (!formValuesSteps.value[indexStep] || !formValuesSteps.value[indexStep].inputs) {
        return
    }

    const inputMap: Record<string, any> = {}

    for (const key in formValuesSteps.value[indexStep].inputs) {
        const value = formValuesSteps.value[indexStep].inputs[key]
        const baseId = key.split('_')[0]

        if (!inputMap[baseId]) {
            inputMap[baseId] = {
                id: baseId,
                name: null,
                stepId: formValuesSteps.value[indexStep].id,
                inputValue: [],
                type: null,
            }
        }

        inputMap[baseId].inputValue.push(value)
    }

    if (!templateInput.value[indexStep]) {
        templateInput.value[indexStep] = []
    }

    inputsData.value[indexStep] = templateInput.value[indexStep].map((input: any) => {
        if (inputMap[input.id]) {
            return {
                name: input.name,
                type: input.type,
                inputValue: inputMap[input.id].inputValue || [],
                stepId: formValuesSteps.value[indexStep].id,
            }
        }

        return null
    }).filter(Boolean)
}

const fillData = async (indexStep: number) => {
    await confirmFilled(indexStep)

    const message: MessageBag = {
        type: 'success',
        message: t('messages.logisticsFillInData'),
        title: 'Success',
    }

    useNuxtApp().$event('message', message)
}

const findDppsByIds = (ids: string[]) => {
    const results = ids.map(id => {
        const inProductSteps = productStepsDpps.value?.some(dpp => dpp.id === id)
        const inDpps = dpps.value?.some(dpp => dpp.id === id)

        return {
            id,
            inProductSteps,
            inDpps,
            found: inProductSteps || inDpps,
        }
    })

    return results
}

const createInputsDpp = async (inputsFillData: any, logistic: any) => {
    for (const input in inputsFillData) {
        const type = inputsFillData[input].type.toLowerCase().replace(/\s+/g, '')
        switch (type) {
            case 'image':

                if (!inputsFillData[input]?.inputValue) {
                    break
                }

                if (Array.isArray(inputsFillData[input].inputValue)) {
                    for (let i = 0; i < inputsFillData[input].inputValue.length; i++) {
                        const requestData = {
                            logistics: `/api/logistics/${logistic.id}`,
                            name: inputsFillData[input].name,
                            type: inputsFillData[input].type,
                            createQr: false,
                        }

                        const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                        if (inputResponse) {
                            const image = b64toBlob(inputsFillData[input].inputValue[i].url)
                            const imageType = mimedb[image.type]

                            const formData = new FormData()

                            formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)
                            await useProductsInputsStore().createProductInputImage(inputResponse.id, formData)
                        }
                    }
                } else {
                    const requestData = {
                        logistics: `/api/logistics/${logistic.id}`,
                        name: inputsFillData[input].name,
                        type: inputsFillData[input].type,
                        createQr: false,
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    if (inputResponse) {
                        const image = b64toBlob(inputsFillData[input].inputValue.url)
                        const imageType = mimedb[image.type]

                        const formData = new FormData()

                        formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)
                        await useProductsInputsStore().createProductInputImage(inputResponse.id, formData)
                    }
                }

                break

            case 'images':

                if (!inputsFillData[input]?.inputValue) {
                    break
                }

                if (Array.isArray(inputsFillData[input].inputValue)) {
                    const requestData = {
                        logistics: `/api/logistics/${logistic.id}`,
                        name: inputsFillData[input].name,
                        type: inputsFillData[input].type,
                        createQr: false,
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    for (let i = 0; i < inputsFillData[input].inputValue.length; i++) {
                        if (inputResponse) {
                            const formData = new FormData()
                            const image = b64toBlob(inputsFillData[input].inputValue[i])
                            const imageType = mimedb[image.type]

                            formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)
                            formData.append('input', `/api/product_inputs/${inputResponse.id}`)

                            await useProductsInputsStore().createProductInputImages(formData)
                        }
                    }
                } else {
                    const requestData = {
                        logistics: `/api/logistics/${logistic.id}`,
                        name: inputsFillData[input].name,
                        type: inputsFillData[input].type,
                        createQr: false,
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    if (inputResponse) {
                        const formData = new FormData()
                        const image = b64toBlob(inputsFillData[input].inputValue)
                        const imageType = mimedb[image.type]

                        formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)
                        formData.append('input', `/api/product_inputs/${inputResponse.id}`)

                        await useProductsInputsStore().createProductInputImages(formData)
                    }
                }

                break

            case 'file':

                if (!inputsFillData[input]?.inputValue) {
                    break
                }

                if (Array.isArray(inputsFillData[input].inputValue)) {
                    for (let i = 0; i < inputsFillData[input].inputValue.length; i++) {
                        const requestData = {
                            logistics: `/api/logistics/${logistic.id}`,
                            name: inputsFillData[input].name,
                            type: inputsFillData[input].type,
                            createQr: false,
                        }

                        const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                        if (inputResponse) {
                            await useProductsInputsStore().createProductInputDocument(inputResponse.id, inputsFillData[input].inputValue[i])
                        }
                    }
                } else {
                    const requestData = {
                        logistics: `/api/logistics/${logistic.id}`,
                        name: inputsFillData[input].name,
                        type: inputsFillData[input].type,
                        createQr: false,
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    if (inputResponse) {
                        await useProductsInputsStore().createProductInputDocument(inputResponse.id, inputsFillData[input].inputValue)
                    }
                }

                break

            case 'text':

                if (!inputsFillData[input]?.inputValue) {
                    break
                }

                if (Array.isArray(inputsFillData[input].inputValue)) {
                    for (let i = 0; i < inputsFillData[input].inputValue.length; i++) {
                        const requestData = {
                            logistics: `/api/logistics/${logistic.id}`,
                            name: inputsFillData[input].name,
                            type: inputsFillData[input].type,
                            textValue: inputsFillData[input].inputValue[i],
                            createQr: false,
                        }

                        await useProductsInputsStore().createProductInput(requestData)
                    }
                } else {
                    const requestData = {
                        logistics: `/api/logistics/${logistic.id}`,
                        name: inputsFillData[input].name,
                        type: inputsFillData[input].type,
                        textValue: inputsFillData[input].inputValue,
                        createQr: false,
                    }

                    await useProductsInputsStore().createProductInput(requestData)
                }

                break

            case 'textarea':

                if (!inputsFillData[input]?.inputValue) {
                    break
                }

                if (Array.isArray(inputsFillData[input].inputValue)) {
                    for (let i = 0; i < inputsFillData[input].inputValue.length; i++) {
                        const requestData = {
                            logistics: `/api/logistics/${logistic.id}`,
                            name: inputsFillData[input].name,
                            type: inputsFillData[input].type,
                            textAreaValue: inputsFillData[input].inputValue[i],
                            createQr: false,
                        }

                        await useProductsInputsStore().createProductInput(requestData)
                    }
                } else {
                    const requestData = {
                        logistics: `/api/logistics/${logistic.id}`,
                        name: inputsFillData[input].name,
                        type: inputsFillData[input].type,
                        textAreaValue: inputsFillData[input].inputValue,
                        createQr: false,
                    }

                    await useProductsInputsStore().createProductInput(requestData)
                }

                break

            case 'numerical':

                if (!inputsFillData[input]?.inputValue) {
                    break
                }

                if (Array.isArray(inputsFillData[input].inputValue)) {
                    for (let i = 0; i < inputsFillData[input].inputValue.length; i++) {
                        const requestData = {
                            logistics: `/api/logistics/${logistic.id}`,
                            name: inputsFillData[input].name,
                            type: inputsFillData[input].type,
                            numericalValue: Number.parseFloat(inputsFillData[input].inputValue[i]),
                            createQr: false,
                        }

                        await useProductsInputsStore().createProductInput(requestData)
                    }
                } else {
                    const requestData = {
                        logistics: `/api/logistics/${logistic.id}`,
                        name: inputsFillData[input].name,
                        type: inputsFillData[input].type,
                        numericalValue: Number.parseFloat(inputsFillData[input].inputValue),
                        createQr: false,
                    }

                    await useProductsInputsStore().createProductInput(requestData)
                }

                break

            case 'datetime':

                if (!inputsFillData[input]?.inputValue) {
                    break
                }

                if (Array.isArray(inputsFillData[input].inputValue)) {
                    let requestData
                    for (let i = 0; i < inputsFillData[input].inputValue.length; i++) {
                        requestData = {
                            logistics: `/api/logistics/${logistic.id}`,
                            name: inputsFillData[input].name,
                            type: inputsFillData[input].type,
                            dateTimeFrom: inputsFillData[input].inputValue[i]?.length ? inputsFillData[input].inputValue[i][0] : inputsFillData[input].inputValue[0] || null,
                            dateTimeTo: inputsFillData[input].inputValue[i]?.length ? inputsFillData[input].inputValue[i][1] : inputsFillData[input].inputValue[1] || null,
                            createQr: false,
                        }

                        if (inputsFillData[input].inputValue[i][0] || inputsFillData[input].inputValue[i][1]) {
                            await useProductsInputsStore().createProductInput(requestData)
                        } else {
                            await useProductsInputsStore().createProductInput(requestData)
                            break
                        }
                    }
                } else {
                    const requestData = {
                        logistics: `/api/logistics/${logistic.id}`,
                        name: inputsFillData[input].name,
                        type: inputsFillData[input].type,
                        dateTimeFrom: inputsFillData[input].inputValue[0] || null,
                        dateTimeTo: inputsFillData[input].inputValue[1] || null,
                        createQr: false,
                    }

                    await useProductsInputsStore().createProductInput(requestData)
                }

                break

            case 'coordinates':

                if (!inputsFillData[input]?.inputValue) {
                    break
                }

                if (Array.isArray(inputsFillData[input].inputValue)) {
                    for (let i = 0; i < inputsFillData[input].inputValue.length; i++) {
                        const requestData = {
                            logistics: `/api/logistics/${logistic.id}`,
                            name: inputsFillData[input].name,
                            type: inputsFillData[input].type,
                            latitudeValue: inputsFillData[input].inputValue[i].lat || null,
                            longitudeValue: inputsFillData[input].inputValue[i].lng || null,
                            createQr: false,
                        }

                        await useProductsInputsStore().createProductInput(requestData)
                    }
                } else {
                    const requestData = {
                        logistics: `/api/logistics/${logistic.id}`,
                        name: inputsFillData[input].name,
                        type: inputsFillData[input].type,
                        latitudeValue: inputsFillData[input].inputValue.lat || null,
                        longitudeValue: inputsFillData[input].inputValue.lng || null,
                        createQr: false,
                    }

                    await useProductsInputsStore().createProductInput(requestData)
                }

                break
        }
    }
}

const submit = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        useNuxtApp().$event('message', {
            type: 'error',
            message: t('messages.logisticsInputReq'),
            title: 'Error',
        })

        return
    }

    const typeDpps = findDppsByIds(formValues.value.parent_dpp)

    const fromDppsId = typeDpps
        .filter(parentDpp => parentDpp.inDpps)
        .map(parentDpp => `/api/dpps/${parentDpp.id}`)

    const fromProductStepsDppsId = typeDpps
        .filter(parentDpp => parentDpp.inProductSteps)
        .map(parentDpp => `/api/product_steps/${parentDpp.id}`)

    const nodeToName = nodeData.value.children.find((node: any) => node.id === formValues.value.toNode)

    const parentLogistics = await logisticsStore.createLogistics({
        fromDpps: fromDppsId,
        fromProductSteps: fromProductStepsDppsId,
        fromNode: `/api/nodes/${nodeData.value.id}`,
        toNode: `/api/nodes/${formValues.value.toNode}`,
        supplyChainTemplate: `/api/supply_chain_templates/${supplyChain.value.id}`,
        numberOfSteps: Number.parseInt(formValues.value.number_of_steps),
        name: `${nodeData.value.name} -> ${nodeToName?.name}`,
        startingCompanyName: startingCompanyName.value || null,
        destinationCompanyName: destinationCompanyName.value || null,
        user: `/api/users/${userId.value}`,
    })

    for (let index = 0; index < formValuesSteps.value.length; index++) {
        const step = formValuesSteps.value[index]
        const typeOfTransportName = typeOfTransports.value[index].filter(type => type.id === step.typeOfTransport)[0]?.name

        const logistic = await logisticsStore.createLogistics({
            company: `/api/companies/${step.company}`,
            arrivalTime: step.arrivalTime,
            departureTime: step.departureTime,
            typeOfTransport: typeOfTransportName || '',
            destinationPointLat: step.destination.lat,
            destinationPointLng: step.destination.lng,
            totalDistance: distance.value[index],
            fromDpps: fromDppsId,
            fromProductSteps: fromProductStepsDppsId,
            fromNode: `/api/nodes/${nodeData.value.id}`,
            toNode: `/api/nodes/${formValues.value.toNode}`,
            supplyChainTemplate: `/api/supply_chain_templates/${supplyChain.value.id}`,
            numberOfSteps: Number.parseInt(formValues.value.number_of_steps),
            logisticsTemplate: step?.template ? `/api/logistics_templates/${step?.template}` : null,
            logisticsParent: parentLogistics['@id'],
            name: `${parentLogistics?.id} - ${parentLogistics?.name} - ${typeOfTransportName}`,
            startingCompanyName: startingCompanyName.value || null,
            destinationCompanyName: destinationCompanyName.value || null,
            user: `/api/users/${userId.value}`,
            startingPointCoordinates: [
                {
                    latitude: startingCoordinates.value.lat,
                    longitude: startingCoordinates.value.lng,
                    logistics: [],
                },
            ],
        })

        if (!inputsData.value[index]?.length && logistic) {
            continue
        }

        await createInputsDpp(inputsData.value[index], logistic)
    }

    const message: MessageBag = {
        type: 'success',
        message: t('messages.logisticsCreate'),
        title: 'Success',
    }

    useNuxtApp().$event('message', message)

    useNuxtApp().$event('logisticsAdded', true)
    closeAddLogisticsModal()
}

const numberOfStepsRules = [
    (value: number) => (value > 0 && value <= 10) || t('page.dppsAddLogistics.numberOfStepsRules'),
]

const parentDppRules = [
    (value: string) => !!value || t('page.dppsAddLogistics.parentDppRules'),
]

const nodeToRules = [
    (value: string) => !!value || t('page.dppsAddLogistics.nodeToRules'),
]

const companyLogisticsRules = [
    (value: string) => !!value || t('page.dppsAddLogistics.companyLogisticsRules'),
]

const typeOfTransportRules = [
    (value: string) => !!value || t('page.dppsAddLogistics.typeOfTransportRules'),
]

const templateRules = [
    (value: string) => !!value || t('page.dppsAddLogistics.templateRules'),
]

const generateSteps = async (n: number) => {
    stepsData.value = Array.from({ length: n }, (_, i) => {
        showMakers[i] = false

        return {
            name: `Step ${i + 1}`,
            id: `${i + 1}`,
            inputs: [],
        }
    })
}

const goToStep2 = async () => {
    if (formValues.value.number_of_steps === 0 || !formValues.value.parent_dpp?.length || !formValues.value.toNode) {
        const formValidation: any = form.value

        formValidation.validate()

        return
    }

    const typeDpps = findDppsByIds(formValues.value.parent_dpp)

    if (typeDpps[0].inProductSteps) {
        startingCoordinates.value = await useProductsStore().fetchProductStepsDppGeo(typeDpps[0].id)
    } else {
        startingCoordinates.value = await dppStore.fetchDppGeo(typeDpps[0].id)
    }

    startingCompanyName.value = startingCoordinates.value.name

    await generateSteps(formValues.value.number_of_steps)
    step.value = 2
}

const getCoords = (coords: any, index: number) => {
    formValuesSteps.value[index].destination.lat = coords.lat
    formValuesSteps.value[index].destination.lng = coords.lng

    mapsCoordinatesDestination.value[index] = {
        lat: Number(coords.lat.toFixed(6)),
        lng: Number(coords.lng.toFixed(6)),
    }

    addressesDestinationPoint.value[index] = {}

    formValuesSteps.value[index].companyMap = null
    formValuesSteps.value[index].sites = null

    updateDistance(
        index,
        startingCoordinates.value.lat,
        startingCoordinates.value.lng,
        formValuesSteps.value[index].destination.lat,
        formValuesSteps.value[index].destination.lng,

    )
}

watch(() => formValuesSteps.value.map(item_ => item_.companyMap), (newVals: any[]) => {
    for (let index = 0; index < newVals.length; index++) {
        const companyId = newVals[index]

        if (!companyId) {
            continue
        }

        const company = nodeData.value.companiesFromProductTemplates.find(
            (companyItem: any) => companyItem.id === companyId,
        )

        if (!company) {
            return
        }

        showMakers[index] = false
        refreshMap[index] = false
        activeMap[index] = true
        markerClicked[index] = true

        destinationCompanyName.value = company.name

        formValuesSteps.value[index].sites = null

        addressesDestinationPoint.value[index] = {
            lat: company.latitude,
            lng: company.longitude,
        }

        sitesOptions.value[index] = company.sites || []

        formValuesSteps.value[index].destination.lat = company.latitude
        formValuesSteps.value[index].destination.lng = company.longitude
        mapsCoordinatesDestination.value[index] = {}

        updateDistance(
            index,
            startingCoordinates.value.lat || 0,
            startingCoordinates.value.lng || 0,
            company.latitude,
            company.longitude,
        )

        setTimeout(() => {
            showMakers[index] = true
            refreshMap[index] = true
            activeMap[index] = false
            markerClicked[index] = false
        }, 10)
    }
}, { immediate: true })

watch(() => formValuesSteps.value.map(item_ => item_.sites), (newVals: any[]) => {
    for (let index = 0; index < newVals.length; index++) {
        const siteId = newVals[index]

        if (!siteId) {
            continue
        }

        const company = nodeData.value.companiesFromProductTemplates.find(
            (companyItem: any) => companyItem.id === formValuesSteps.value[index].companyMap,
        )

        if (!company) {
            return
        }

        const site = company.sites?.find((siteItem: any) => siteItem.id === siteId)

        if (!site) {
            formValuesSteps.value[index].sites = null

            return
        }

        showMakers[index] = false
        refreshMap[index] = false
        activeMap[index] = true
        markerClicked[index] = true

        destinationCompanyName.value = site.name

        addressesDestinationPoint.value[index] = {
            lat: site.latitude,
            lng: site.longitude,
        }

        formValuesSteps.value[index].destination.lat = site.latitude
        formValuesSteps.value[index].destination.lng = site.longitude

        updateDistance(
            index,
            startingCoordinates.value.lat || 0,
            startingCoordinates.value.lng || 0,
            site.latitude,
            site.longitude,
        )

        mapsCoordinatesDestination.value[index] = {}

        setTimeout(() => {
            showMakers[index] = true
            refreshMap[index] = true
            activeMap[index] = false
            markerClicked[index] = false
        }, 10)
    }
}, { immediate: true })

watch(() => formValuesSteps.value.map(item_ => item_.company), async (newVals: any, oldVals: any) => {
    for (let index = 0; index < newVals.length; index++) {
        const companyId = newVals[index]

        if (!companyId) {
            typeOfTransports.value[index] = []
            continue
        }

        if (newVals[index] !== oldVals[index]) {
            formValuesSteps.value[index].typeOfTransport = null
            formValuesSteps.value[index].template = null
        }

        typeOfTransports.value[index] = companies.value
            .filter(company => company.id === companyId)
            .map(company => company.typeOfTransportsData)
            .flat()
    }
}, { immediate: true })

watch(() => formValuesSteps.value.map(item_ => item_.typeOfTransport), (newVals: any[], oldVals: any[]) => {
    for (let index = 0; index < newVals.length; index++) {
        const typeOfTransportId = newVals[index]

        if (!typeOfTransportId) {
            continue
        }

        if (newVals[index] !== oldVals[index]) {
            formValuesSteps.value[index].template = null
        }

        updateDistance(
            index,
            startingCoordinates.value.lat || 0,
            startingCoordinates.value.lng || 0,
            formValuesSteps.value[index].destination.lat,
            formValuesSteps.value[index].destination.lng,
        )

        fetchLogisticsTemplates(typeOfTransportId, index)
    }
}, { immediate: true })

watch(() => formValuesSteps.value.map(item_ => item_.template), async (newVals: any[]) => {
    for (let index = 0; index < newVals.length; index++) {
        const logisticTemplateId = newVals[index]

        if (!logisticTemplateId) {
            continue
        }

        const logisticTemplate = await logisticsStore.fetchLogisticsTemplateById(logisticTemplateId)

        templateInput.value[index] = logisticTemplate.inputDetails
    }
}, { immediate: true })

watch(
    () => stepsData.value,
    newSteps => {
        formValuesSteps.value = newSteps.map(step => ({
            id: step.id,
            template: null,
            company: null,
            site: null,
            logistic: null,
            inputs: [],
            typeOfTransport: null,
            destination: {
                lat: 0,
                lng: 0,
            },
            companyMap: null,
            sites: null,
        }))
    },
    { deep: true, immediate: true },
)
</script>

<template>
    <ModalLayout
        :is-open="isAddLogisticsModalOpen"
        name="simple-modal"
        title="Logistics"
        no-buttons
        width="70vw"
        @modal-close="closeAddLogisticsModal"
    >
        <template #content>
            <VContainer class="w-100">
                <VForm
                    ref="form"
                    v-model="valid"
                >
                    <p class="num_of_inputs mb-5">
                        {{ nodeData?.name }} {{
                            formValues.toNode ? ` -> ${nodeData.children?.filter(ch => ch.id === formValues.toNode)[0]?.name}` : ''
                        }}
                    </p>

                    <template v-if="step === 1">
                        <VRow>
                            <VCol
                                cols="12"
                                sm="6"
                            >
                                <VTextField
                                    v-model="formValues.number_of_steps"
                                    label="Number of steps"
                                    variant="outlined"
                                    type="number"
                                    :rules="numberOfStepsRules"
                                    required
                                />
                            </VCol>
                            <VCol
                                cols="12"
                                sm="6"
                            >
                                <VSelect
                                    v-model="formValues.parent_dpp"
                                    label="Parent DPP"
                                    variant="outlined"
                                    :items="dppsOptions"
                                    item-title="name"
                                    item-value="id"
                                    :rules="parentDppRules"
                                    :loading="isLoading.parentDpp"
                                    :no-data-text="isLoading.parentDpp ? $t('logistics.loadingDpps') : $t('noDataAvailable')"
                                    multiple
                                    required
                                />
                            </VCol>

                            <VCol
                                cols="12"
                                sm="6"
                            >
                                <VSelect
                                    v-model="formValues.toNode"
                                    label="Node to"
                                    variant="outlined"
                                    :items="nodesToOptions"
                                    item-title="name"
                                    item-value="id"
                                    :rules="nodeToRules"
                                    :loading="isLoading.nodeTo"
                                    :no-data-text="isLoading.nodeTo ? $t('logistics.loadingNodes') : $t('noDataAvailable')"
                                    required
                                />
                            </VCol>

                            <VCol
                                cols="12"
                                class="text-right"
                            >
                                <VBtn
                                    color="#65c09e"
                                    variant="flat"
                                    class="text-uppercase"
                                    height="45"
                                    @click="goToStep2"
                                >
                                    {{ $t('products.next') }}
                                </VBtn>
                            </VCol>
                        </VRow>
                    </template>

                    <template v-if="step === 2">
                        <div
                            v-for="(step, index) in stepsData"
                            :key="step.id"
                            class=" border-b-thin py-5"
                        >
                            <p class="my-5">
                                Step {{ step.id }}
                            </p>
                            <VRow>
                                <VCol
                                    cols="12"
                                    sm="6"
                                >
                                    <VSelect
                                        v-model="formValuesSteps[index].company"
                                        :label="$t('supply.dpp.logisticsCompany')"
                                        variant="outlined"
                                        :rules="companyLogisticsRules"
                                        :items="
                                            companies.map(company => ({
                                                title: company.name,
                                                value: company.id,
                                            }))
                                        "
                                    />
                                </VCol>
                                <VCol
                                    cols="12"
                                    sm="6"
                                >
                                    <VSelect
                                        v-if="typeOfTransports[index]"
                                        v-model="formValuesSteps[index].typeOfTransport"
                                        :label="$t('page.dpps.typeOfTransport')"
                                        variant="outlined"
                                        :rules="typeOfTransportRules"
                                        :items="(typeOfTransports[index] || []).map(type => ({
                                            title: type.name,
                                            value: type.id,
                                        }))"
                                    />
                                </VCol>
                                <VCol
                                    cols="12"
                                    sm="6"
                                >
                                    <VSelect
                                        v-model="formValuesSteps[index].template"
                                        :rules="templateRules"
                                        :label="$t('page.dpps.template')"
                                        variant="outlined"
                                        :items="logisticsTemplate[index]"
                                    />
                                </VCol>
                                <VCol
                                    cols="12"
                                    sm="6"
                                >
                                    <VueDatePicker
                                        v-model="formValuesSteps[index].departureTime"
                                        :label="$t('page.dpps.departureTime')"
                                        :placeholder="$t('page.dpps.departureTime')"
                                        variant="outlined"
                                    />
                                </VCol>
                                <VCol
                                    cols="12"
                                    sm="6"
                                >
                                    <VueDatePicker
                                        v-model="formValuesSteps[index].arrivalTime"
                                        :rules="arrivalTimeRules"
                                        :label="$t('page.dpps.arrivalTime')"
                                        :placeholder="$t('page.dpps.arrivalTime')"
                                        variant="outlined"
                                    />
                                </VCol>
                            </VRow>

                            <VRow>
                                <VCol
                                    cols="12"
                                    sm="6"
                                >
                                    <p class="num_of_inputs mb-15 text-left font-weight-bold">
                                        {{ $t('page.dpps.starting_point') }}
                                    </p>
                                    <div class="map starting-point">
                                        <Map
                                            :address-groups="[{
                                                addresses: [
                                                    {
                                                        lat: startingCoordinates.lat,
                                                        lng: startingCoordinates.lng,
                                                    },
                                                ],
                                                color: 'blue',
                                                connectLine: false,
                                            }]"
                                            :zoom="zoom"
                                            :is-active-map="false"
                                            :is-marker-clicked="false"
                                        />
                                    </div>
                                </VCol>

                                <VCol
                                    cols="12"
                                    sm="6"
                                >
                                    <p class="num_of_inputs mb-5 text-left font-weight-bold">
                                        {{ $t('page.dpps.destination_point') }}
                                    </p>

                                    <VRow>
                                        <VCol
                                            cols="12"
                                            sm="6"
                                        >
                                            <VSelect
                                                v-model="formValuesSteps[index].companyMap"
                                                label="Company"
                                                variant="outlined"
                                                :items="companiseOptions"
                                                item-title="name"
                                                item-value="id"
                                                required
                                            />
                                        </VCol>
                                        <VCol
                                            cols="12"
                                            sm="6"
                                        >
                                            <VSelect
                                                v-model="formValuesSteps[index].sites"
                                                label="Site"
                                                variant="outlined"
                                                :items="sitesOptions[index]"
                                                item-title="name"
                                                item-value="id"
                                                required
                                            />
                                        </VCol>
                                    </VRow>
                                    <div class="map">
                                        <Map
                                            :address-groups="[{
                                                addresses: [
                                                    (addressesDestinationPoint[index] && Object.keys(addressesDestinationPoint[index]).length > 0)
                                                        ? addressesDestinationPoint[index]
                                                        : (mapsCoordinatesDestination[index] || { lat: 50.8503460, lng: 4.3517210 }),
                                                ],
                                                color: 'blue',
                                                connectLine: false,
                                            }]"

                                            :zoom="zoom"
                                            :show-markers="showMakers[index]"
                                            :is-active-map="activeMap[index]"
                                            :is-marker-clicked="markerClicked[index]"
                                            :is-refresh-maps="refreshMap[index]"
                                            @point-clicked="(coord) => getCoords(coord, index)"
                                        />
                                    </div>

                                    <VAlert
                                        :text="$t('page.dpps.total_distance', { distance: distance[index] })"
                                        color="#65c09e"
                                        variant="tonal"
                                        class="mt-4"
                                    />
                                </VCol>
                            </VRow>

                            <template v-if="templateInput[index]">
                                <VRow>
                                    <VCol
                                        v-for="input in templateInput[index]"
                                        :key="input.id"
                                        cols="12"
                                        sm="6"
                                        class="my-0 py-0 pb-4"
                                    >
                                        <InputByType
                                            v-model="formValuesSteps[index].inputs[input.id]"
                                            :type="input.type"
                                            :name="input.name"
                                            :show-updatable-input="false"
                                        />
                                    </VCol>
                                </VRow>
                            </template>

                            <VRow v-if="templateInput[index]?.length > 0">
                                <VCol>
                                    <VBtn
                                        class="mt-2 w-100"
                                        variant="flat"
                                        color="#65c09e"
                                        height="45"
                                        @click.prevent="fillData(index)"
                                    >
                                        {{ $t('page.dpps.fill_in_data') }}
                                    </VBtn>
                                </VCol>
                            </VRow>
                        </div>

                        <div class="d-flex align-center justify-end mt-4 flex-column">
                            <VBtn
                                class="mt-2 w-100"
                                variant="flat"
                                color="#65c09e"
                                height="45"
                                @click="submit"
                            >
                                {{ $t('page.dpps.submit') }}
                            </VBtn>
                        </div>
                    </template>
                </VForm>
            </VContainer>
        </template>
    </ModalLayout>
</template>

<style scoped lang="scss">
.num_of_inputs {
    font-size: 1.2rem;
    color: #26A69A;
    text-align: center;
}

.step-checkbox {
    padding-bottom: 0;
    border-top: 2px solid #26A69A;

    &.no-border {
        border-top: none;
    }
}

.duplicate-btn {
    margin: 0;
    color: #26A69A;
    font-size: 10px;
    text-transform: none;
    padding: 0;
}

.map.starting-point {
    margin-top: 6.7rem;
}
</style>
