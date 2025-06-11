<script lang="ts" setup>
import mimedb from 'mime-db'
import ModalLayout from '@/dialogs/modalLayout.vue'
import InputByType from '@/components/InputByType.vue'
import type { Step } from '~/types/api/productTemplate'
import TreeFlow from '~/components/tree/TreeFlow.vue'
import { b64toBlob } from '@/helpers/convert'
import type { MessageBag } from '@/types/index'
import type { SelectItem } from '@/types/selectItem'
import { formatPascalCaseToLabel } from '@/helpers/textFormatter'

const { $event, $listen } = useNuxtApp()
const { t } = useI18n()
const isAddDppModalOpen = ref(false)

const logisticsStore = useLogisticsStore()
const processStore = useProcessStore()
const companiesStore = useCompaniesStore()
const nodesStore = useNodesStore()
const logisticIds = ref<any[]>([])

const nodeData = ref()
const dynamicallyAddedInputs = ref<Record<number, string[]>>({})
const confirmedSteps = ref<Record<number, boolean>>({})

const inputs = ref([])

const form = ref(null)
const valid = ref(false)
const companies = ref<SelectItem[]>([])
const dppsByNode = ref()
const sites = ref([])
const stepsState = ref([])
const selectedDpps = ref<Record<string, any[]>>({})
const selectedLogistics = ref<Record<string, any[]>>({})
const selectedFromProductSteps = ref<Record<string, any[]>>({})
const dpps = ref<Record<string, any[]>>({})
const productStepsDpp = ref<Record<string, any[]>>({})
const showProductStepsInput = ref<Record<string, boolean>>({})
const prevProductStepSelections: Record<string, string[]> = {}

const MEASUREMENT_TYPE = 'MEASUREMENT_TYPE'

const isLoading = ref({
    company: true,
    site: true,
})

const formValues = ref({
    idQr: '',
    company: null,
    site: null,
    logistic: null,
    inputs: [],
    measurementValueProductSteps: [],
    measurementValue: [],
    logistics: [],
    dpp: [],
    productStepsDpp: [],
    updatableInput: [],
})

const dppCreationStatus = ref({
    createDpp: false,
    ongoingDpp: false,
    emptyDpp: true,
    btnLable: t('dpps.createEmptyDpp'),
})

const activeSteps = ref([])
const activeStepsData = ref([])
const inputData = ref([])
const isConfirmFilled = ref(false)
const isSubmited = ref(false)
const dppProductId = ref(null)
const dppCreateId = ref(null)
const productResponseData = ref(null)
const previousInputData = ref<Record<string, any>>({})
const siteNoDataAvailable = ref(t('noSite'))
const seenIds = new Set()
const fromDpps = ref()

const companyRules = [
    (v: string) => !!v || 'Company is required',
]

const closeAddDppModal = () => {
    isAddDppModalOpen.value = false
    formValues.value = {
        idQr: '',
        company: null,
        site: null,
        logistic: null,
        inputs: [],
        updatableInput: [],
    }

    seenIds.clear()

    stepsState.value = []
    Object.keys(prevProductStepSelections).forEach(key => {
        delete prevProductStepSelections[key]
    })

    nodeData.value?.steps.forEach((step, stepIndex) => {
        showProductStepsInput.value[step.id] = false
        if (dynamicallyAddedInputs.value[stepIndex]) {
            step.inputs = step.inputs.filter(
                input => !dynamicallyAddedInputs.value[stepIndex].includes(input.id),
            )
        }
    })

    dynamicallyAddedInputs.value = {}
    confirmedSteps.value = {}

    activeSteps.value = []
    activeStepsData.value = []
    inputData.value = []
    isConfirmFilled.value = false
}

const fetchCompanies = async () => {
    let companyData = []

    if (nodeData.value.productTemplates && nodeData.value.productTemplates.length > 0) {
        companyData = await Promise.all(
            nodeData.value.productTemplates.map(async (productTemplateId: any) => {
                const p = await useProductsStore().fetchCompanyProducts(productTemplateId.split('/').pop())

                return p.companies.map((company: any) => ({
                    value: company.id,
                    title: company.name,
                    sites: company.sites,
                }))
            }),
        )
    } else if (nodeData.value.nodeTemplate) {
        // The situation needs to be discussed in case the node has only a node template assigned, but the company should still be displayed.
        // Currently, the node template does not own any companies.
        const companiesRequest = await companiesStore.fetchCompaniesListing(undefined, undefined, undefined, false, true, undefined, undefined)

        companyData = companiesRequest.map((company: any) => ({
            value: company.id,
            title: company.name,
            sites: company.sites,
        }))
    }

    companies.value = companyData.flat()

    isLoading.value.company = false
    isLoading.value.site = false
}

watch(() => formValues.value.company, newCompanyId => {
    if (!newCompanyId) {
        sites.value = []
        isLoading.value.site = false

        return
    }

    const selectedCompany = companies.value?.find(company => company.value === newCompanyId)

    if (selectedCompany?.sites) {
        isLoading.value.site = true
    }

    formValues.value.site = null
    sites.value = [
        {
            title: t('noSite'),
            value: 0,
        },
        ...(selectedCompany?.sites?.map((site: any) => ({
            title: site.name,
            value: site.id,
        })) || []),
    ]

    if (!sites.value?.length) {
        siteNoDataAvailable.value = t('noDataAvailable')
    }

    isLoading.value.site = false
})

const userId = ref(null)

const refreshForm = () => {
    formValues.value = {
        idQr: '',
        company: null,
        site: null,
        logistic: null,
        inputs: [],
        measurementValueProductSteps: [],
        measurementValue: [],
        logistics: [],
        dpp: [],
        productStepsDpp: [],
        locked: false,
        updatableInput: [],
    }
}

const fetchLogistics = async (id: string) => {
    const getLogisticsResponse = await logisticsStore.fetchLogisticsById(id)

    if (!getLogisticsResponse) {
        return
    }

    if (
        nodeData.value['@id'] === getLogisticsResponse.toNode
        && !logisticIds.value.includes(getLogisticsResponse.logisticsParent.id)
    ) {
        logisticIds.value.push(getLogisticsResponse.logisticsParent.id)

        return getLogisticsResponse
    }
}

const validateSteps = (steps: any, inputsData: any, requireAllInputs = true) => {
    const validationResult = steps
        .filter((step: any) => step.inputs && step.inputs.length > 0)
        .map((step: any) => {
            const stepInputs = step?.inputs.map((input: any) => inputsData[input.id]).filter(Boolean)

            const measurement = formValues.value.measurementValueProductSteps?.[step.id]

            const hasMeasurement
                = step.batchTypeOfStep === 'BATCH'
                && step.measurementType
                && !!measurement

            const filledInputs = stepInputs.filter((input: any) => {
                // If the type is "MEASUREMENT TYPE", check the measurement Value Inputs.
                if (input.inputValue === MEASUREMENT_TYPE) {
                    return input.measurementValueInputs !== undefined
                        && input.measurementValueInputs !== null
                        && input.measurementValueInputs !== ''
                }

                return input.inputValue !== undefined
                        && input.inputValue !== null
                        && input.inputValue !== ''
                        && (!Array.isArray(input.inputValue) || input.inputValue.length > 0)
            })

            const inputsToCheck = step.inputs.filter((input: any) => {
                return !(input.type === 'textList' && (!input?.measurementType || input?.measurementType === ''))
            })

            const hasAllInputs = requireAllInputs
                ? filledInputs.length === inputsToCheck.length
                : filledInputs.length > 0

            const shouldValidateExtraFields = nodeData.value?.parents?.length > 0

            const logistics = shouldValidateExtraFields ? formValues?.value?.logistics?.[step.id] : true
            const dpp = shouldValidateExtraFields ? formValues?.value?.dpp?.[step.id] : true

            const hasLogistics = shouldValidateExtraFields ? !!logistics : true
            const hasDpp = shouldValidateExtraFields ? !!dpp : true

            let hasProductStepsDpp = true

            if (showProductStepsInput.value[step.id] === true) {
                const productStepsDppData = shouldValidateExtraFields ? formValues?.value?.productStepsDpp?.[step.id] : true

                hasProductStepsDpp = shouldValidateExtraFields
                    ? Array.isArray(productStepsDppData) && productStepsDppData.length > 0
                    : true
            }

            let isValid = hasAllInputs && hasLogistics && hasDpp && hasProductStepsDpp

            if (isValid && !hasMeasurement && step.batchTypeOfStep === 'BATCH') {
                isValid = false
            }

            const noInputsOrMeasurements = filledInputs.length === 0 && !hasMeasurement

            const isProductStepsEmpty = showProductStepsInput.value[step.id]
                ? !Array.isArray(formValues?.value?.productStepsDpp?.[step.id]) || formValues.value.productStepsDpp[step.id].length === 0
                : true

            const isEmptyAllInputs
                = noInputsOrMeasurements
                && (!shouldValidateExtraFields || (
                    (!logistics || logistics === null)
                    && (!dpp || dpp === null)
                    && isProductStepsEmpty
                ))

            return {
                stepId: step.id,
                stepName: step.name,
                isValid,
                isEmptyAllInputs,
            }
        })

    const allStepsValid = validationResult.every((result: any) => result.isValid)

    return {
        allStepsValid,
        validationResult,
    }
}

const isFetching = ref(false)

onMounted(() => {
    const handleOpenAddDppModal = async (data: any) => {
        if (isFetching.value) {
            return
        }

        isFetching.value = true

        isAddDppModalOpen.value = true
        nodeData.value = data.nodeData
        nodeData.value.logisticsItem = []

        dpps.value = []
        productStepsDpp.value = []

        nodeData.value.parents.forEach((parent: any) => {
            const fromNodeLogistics = Object.values(parent.fromNodeLogistics)

            if (fromNodeLogistics?.length) {
                nodeData.value.logisticsItem = []
                logisticIds.value = []
                fromNodeLogistics.forEach(fromNodeLogistic => {
                    if (nodeData.value.toNodeLogistics.includes(fromNodeLogistic)) {
                        nodeData.value.showLogistics = true
                    }
                })
            }
        })

        refreshForm()
        inputs.value = []

        fetchCompanies()

        if (nodeData.value?.countLogisticsNext) {
            logisticIds.value = []
            nodeData.value.logisticsItem = []

            const assignedIds = nodeData.value.countLogisticsNext.assignedToDppData || []
            const exportIds = nodeData.value.countLogisticsNext.exportLogisticsData || []
            const inUseIds = nodeData.value.countLogisticsNext.inUseLogisticsData || []

            const allLogisticsIds = [...assignedIds, ...exportIds, ...inUseIds]

            const fetchedLogistics = await Promise.all(
                allLogisticsIds.map(async (logisticsRef: string) => {
                    const id = logisticsRef.split('/').pop()

                    return await fetchLogistics(id)
                }),
            )

            const items = []
            const idLogistics = []

            for (const logisticItem of fetchedLogistics) {
                const parentId = logisticItem.logisticsParent.id

                if (seenIds.has(parentId)) {
                    continue
                }

                seenIds.add(parentId)

                if (logisticItem.logisticsParent.fromDpps?.length) {
                    idLogistics.push(logisticItem.logisticsParent.id)
                }

                if (logisticItem.logisticsParent.fromProductSteps?.length) {
                    selectedFromProductSteps.value[parentId] = {
                        idLogistics: parentId,
                        fromProductSteps: logisticItem.logisticsParent.fromProductSteps,

                    }

                    if (logisticItem.state === 'EXPORT_LOGISTICS') {
                        continue
                    }

                    items.push({
                        title: logisticItem.logisticsParent.name,
                        value: parentId,
                    })
                }
            }

            const filterLogistics = await logisticsStore.fetchLogisticsfilterOutExported(idLogistics)

            fromDpps.value = filterLogistics

            nodeData.value.logisticsItem = filterLogistics.logistics.map((logistic: any) => {
                return {
                    title: logistic.name,
                    value: logistic.id,
                }
            })

            if (items?.length) {
                const existingValues = new Set(nodeData.value.logisticsItem.map(item => item.value))

                const uniqueItems = items
                    .filter(Boolean)
                    .filter(item => !existingValues.has(item.value))

                nodeData.value.logisticsItem.push(...uniqueItems)
            }
        }

        if (nodeData.value.nodeTemplate) {
            const nodeTemplateId = nodeData.value.nodeTemplate.split('/').pop()
            if (nodeTemplateId) {
                const nodeDataResponse = await useProductsStore().fetchProductById(nodeTemplateId)

                if (nodeDataResponse && nodeDataResponse.stepsTemplate.steps) {
                    const newSteps = nodeDataResponse.stepsTemplate.steps.map((newStep: any) => {
                        return {
                            ...newStep,
                            parentStepNames: newStep.parentStepNames || [],
                            steps: newStep.steps || [],
                            process: newStep.process || "",
                            quantity: newStep.quantity || 0,
                            sort: newStep.sort || 0,
                            stepImage: newStep.stepImage || "",
                            unitMeasurement: newStep.unitMeasurement || "",
                            type: 'NODE_TEMPLATE',
                        }
                    })

                    const uniqueNewSteps = newSteps.filter((newStep: any) => {
                        return !nodeData.value.steps.some((existingStep: any) => existingStep.id === newStep.id)
                    })

                    nodeData.value.steps = [...nodeData.value.steps, ...uniqueNewSteps]
                }
            }
        }

        nodeData.value.steps.forEach((step: any) => {
            showProductStepsInput.value[step.id] = false
            step.inputs.forEach((input: any) => {
                inputs.value.push(input)
            })
        })

        const { allStepsValid } = validateSteps(nodeData.value.steps, inputData.value, false)
        if (!isConfirmFilled.value && !allStepsValid) {
            dppCreationStatus.value = {
                createDpp: false,
                ongoingDpp: false,
                emptyDpp: true,
                btnLable: t('dpps.createEmptyDpp'),
            }
        }

        const authStore = useAuthStore()

        userId.value = await authStore.getMyId()

        isFetching.value = false
    }

    $listen('openAddDppModal', handleOpenAddDppModal)
})

const steps = computed(() => {
    const localSteps: Step[] = []
    let logPrinted = false

    nodeData.value?.steps?.forEach(async (step: any) => {
        if (step.process.startsWith("/api/processes/") && !logPrinted) {
            const processData = await processStore.fetchProcessById(step.process.split('/').pop())

            step.processColor = processData.color
            step.process = processData.name
            localSteps.push(step)

            logPrinted = true
        } else {
            localSteps.push(step)
        }
    })

    return localSteps
})

const fetchRelevantDppsForSteps = async (fromDppsId?: any, logisticsId?: string) => {
    for (const productStep of activeStepsData.value) {
        if (!productStep.parentStepIds?.length) {
            continue
        }

        if (fromDppsId) {
            const dppsData = await useDppStore().fetchDppByIds(fromDppsId, productStep.parentStepIds)

            selectedLogistics.value[productStep.id] = dppsData.dpps.map((item: any) => {
                return {
                    ...item,
                    logisticsId,
                }
            })

            const hasProductSteps = dppsData.dpps?.some((dpp: any) => dpp.productSteps?.length)

            if (!hasProductSteps) {
                continue
            }
        }

        const parentIds = (nodeData.value.parents || []).map((parent: any) => parent.id)
        const checkLogisticsItem = nodeData.value.logisticsItem?.length > 0

        if (parentIds?.length && checkLogisticsItem) {
            dppsByNode.value = await nodesStore.getDppsByNodeIds(parentIds, productStep.parentStepIds)

            if (dppsByNode.value?.dpps) {
                dpps.value[productStep.id] = dppsByNode.value.dpps.map((dpp: any) => {
                    if (dpp.type !== 'ProductStep') {
                        const dppsItem = {
                            title: `${dpp.name} - ${dpp.id}`,
                            value: dpp.id,
                        }

                        if (dpp?.logistics.id !== logisticsId) {
                            return dppsItem
                        }

                        if (!logisticsId) {
                            return dppsItem
                        }

                        return null
                    } else {
                        const allSelectedDppEntries = Object.entries(formValues.value.dpp)

                        const usedInOtherSteps = allSelectedDppEntries
                            .filter(([stepId]) => stepId !== productStep.id)
                            .map(([, dppId]) => dppId)

                        if (!usedInOtherSteps.includes(dpp.id) || formValues.value.dpp[productStep.id] === dpp.id) {
                            return {
                                title: `${dpp.name} - ${dpp.id}`,
                                value: dpp.id,
                            }
                        }

                        return null
                    }
                }).filter(Boolean)
            }
        }
    }
}

const toggleStep = async (step: any) => {
    if (activeSteps.value.find(stepId => stepId === step.id)) {
        activeSteps.value = activeSteps.value.filter(stepId => stepId !== step.id)
        activeStepsData.value = activeStepsData.value.filter(stepId => stepId.id !== step.id)
    } else {
        activeSteps.value.push(step.id)
        activeStepsData.value.push(step)
    }

    if (nodeData.value.parents?.length !== 0) {
        await fetchRelevantDppsForSteps()
    }
}

const duplicateInput = (step, inputId: string) => {
    const input = inputs.value.find((inputItem: any) => inputItem.id === inputId)
    const newInputId = `${input.id}_${Date.now()}`

    step.inputs.push({
        ...input,
        id: newInputId,
        additional: true,
    })

    const stepIndex = nodeData.value.steps.findIndex(s => s.id === step.id)
    if (!dynamicallyAddedInputs.value[stepIndex]) {
        dynamicallyAddedInputs.value[stepIndex] = []
    }
    dynamicallyAddedInputs.value[stepIndex].push(newInputId)
}

const isSuccess = (response: any) => {
    return !!response
}

const getStateByStep = (stepId: string) => {
    let infoState: any = {
        title: t('dpps.state.emptyDpp'),
        class: 'empty',
    }

    if (!stepId && !stepsState.value) {
        return infoState
    }

    const stepState = stepsState.value.find((stepStateItem: any) => stepStateItem.stepId === stepId)

    if (!stepState) {
        return infoState
    }

    if (!stepState.isEmptyAllInputs && !stepState.isValid) {
        infoState = {
            title: t('dpps.state.ongoingDpp'),
            class: 'ongoing',
            ongoingDpp: true,
            createEmptyDpp: false,
        }
    }

    if (stepState.isEmptyAllInputs && !stepState.isValid) {
        infoState = {
            title: t('dpps.state.emptyDpp'),
            class: 'empty',
            ongoingDpp: false,
            createEmptyDpp: true,
        }
    }

    if (!stepState.isEmptyAllInputs && stepState.isValid) {
        infoState = {
            title: t('dpps.state.finishDpp'),
            class: 'finish',
            ongoingDpp: false,
            createEmptyDpp: false,
        }
    }

    return infoState
}

const createInputsDpp = async (inputsData: any, dppId: string, dppProduct: any) => {
    const productSteps = new Set()
    const inputSteps = new Set()

    for (const input in inputsData) {
        const productStep = dppProduct.productSteps.find(step => step.stepTemplateReference.split('/').pop() === inputsData[input].stepId)
        let stepConfirmedInput = false
        const type = inputsData[input]?.type?.toLowerCase().replace(/\s+/g, '')
        const stepFind = nodeData.value?.steps.find((step: any) => step.id === inputsData[input].stepId)
        const inputUnit = stepFind?.inputs.find((inputItem: any) => inputItem.id === inputsData[input].id)

        switch (type) {
            case 'image':

                if (Array.isArray(inputsData[input]?.inputValue)) {
                    for (let i = 0; i < inputsData[input]?.inputValue.length; i++) {
                        const requestData = {
                            dpp: dppId ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep.id}`,
                            name: inputsData[input]?.name,
                            type: inputsData[input]?.type,
                            inputCategories: inputsData[input]?.inputCategories,
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            updatable: inputsData[input].updatableInput[i] || false,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            additional: i !== 0,
                            inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                        }

                        const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                        stepConfirmedInput = isSuccess(inputResponse)

                        if (inputResponse && inputsData[input].inputValue[i] !== MEASUREMENT_TYPE) {
                            const image = b64toBlob(inputsData[input].inputValue[i].url ? inputsData[input].inputValue[i].url : inputsData[input].inputValue[i])
                            const imageType = mimedb[image.type]

                            const formData = new FormData()

                            formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)
                            await useProductsInputsStore().createProductInputImage(inputResponse.id, formData)
                        }
                    }
                } else {
                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        updatable: inputsData[input].updatableInput || false,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        additional: inputsData[input].additional,
                        inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)

                    if (inputResponse && inputsData[input].inputValue !== MEASUREMENT_TYPE) {
                        const image = b64toBlob(inputsData[input].inputValue.url ? inputsData[input].inputValue.url : inputsData[input].inputValue)
                        const imageType = mimedb[image.type]

                        const formData = new FormData()

                        formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)
                        await useProductsInputsStore().createProductInputImage(inputResponse.id, formData)
                    }
                }

                break

            case 'images':

                if (Array.isArray(inputsData[input].inputValue)) {
                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        updatable: inputsData[input].updatableInput || false,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        additional: inputsData[input].additional,
                        inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        if (inputResponse && inputsData[input].inputValue[i] !== MEASUREMENT_TYPE) {
                            const formData = new FormData()
                            const image = b64toBlob(inputsData[input].inputValue[i])
                            const imageType = mimedb[image.type]

                            formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)
                            formData.append('input', `/api/product_inputs/${inputResponse.id}`)

                            await useProductsInputsStore().createProductInputImages(formData)
                        }
                    }
                } else {
                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        updatable: inputsData[input].updatableInput || false,
                        additional: inputsData[input].additional,
                        inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)

                    if (inputResponse && inputsData[input].inputValue !== MEASUREMENT_TYPE) {
                        const formData = new FormData()
                        const image = b64toBlob(inputsData[input].inputValue)
                        const imageType = mimedb[image.type]

                        formData.append('file', image, `${inputResponse.id}.${imageType.extensions[0]}`)
                        formData.append('input', `/api/product_inputs/${inputResponse.id}`)

                        await useProductsInputsStore().createProductInputImages(formData)
                    }
                }

                break

            case 'file':

                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const requestData = {
                            dpp: dppId ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep.id}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            updatable: inputsData[input].updatableInput || false,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            additional: i !== 0,
                            inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                        }

                        const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                        stepConfirmedInput = isSuccess(inputResponse)

                        if (inputResponse && inputsData[input].inputValue[i] !== MEASUREMENT_TYPE) {
                            await useProductsInputsStore().createProductInputDocument(inputResponse.id, inputsData[input].inputValue[i])
                        }
                    }
                } else {
                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        updatable: inputsData[input].updatableInput || false,
                        additional: inputsData[input].additional,
                        inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)

                    if (inputResponse && inputsData[input].inputValue !== MEASUREMENT_TYPE) {
                        await useProductsInputsStore().createProductInputDocument(inputResponse.id, inputsData[input].inputValue)
                    }
                }

                break

            case 'text':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const requestData = {
                            dpp: dppId ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep.id}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            textValue: inputsData[input].inputValue[i],
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            updatable: inputsData[input].updatableInput[i] || false,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            additional: i !== 0,
                            inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                        }

                        const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        textValue: inputsData[input].inputValue,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        updatable: inputsData[input].updatableInput || false,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        additional: inputsData[input].additional,
                        inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)
                }

                break

            case 'textarea':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const requestData = {
                            dpp: dppId ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep.id}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            textAreaValue: inputsData[input].inputValue[i],
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            updatable: inputsData[input].updatableInput || false,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            additional: i !== 0,
                            inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                        }

                        const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        textAreaValue: inputsData[input].inputValue,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        updatable: inputsData[input].updatableInput || false,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        additional: inputsData[input].additional,
                        inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)
                }

                break

            case 'numerical':

                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const isMeasurementType = inputsData[input]?.inputValue?.[i] === MEASUREMENT_TYPE

                        const requestData = {
                            dpp: dppId ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep.id}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            numericalValue: isMeasurementType ? 0 : Number.parseFloat(inputsData[input].inputValue[i]),
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            updatable: inputsData[input].updatableInput || false,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            additional: i !== 0,
                            inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                        }

                        const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const isMeasurementType = inputsData[input]?.inputValue === MEASUREMENT_TYPE

                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        numericalValue: isMeasurementType ? 0 : Number.parseFloat(inputsData[input].inputValue),
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        updatable: inputsData[input].updatableInput || false,
                        additional: inputsData[input].additional,
                        inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)
                }

                break

            case 'datetimerange':
                if (Array.isArray(inputsData[input].inputValue) && inputsData[input].inputValue.length > 2) {
                    let requestData
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const isMeasurementType = inputsData[input]?.inputValue?.[i] === MEASUREMENT_TYPE

                        const dateTimeFrom = isMeasurementType
                            ? null
                            : inputsData[input]?.inputValue?.[i]?.length < 1
                                ? inputsData[input]?.inputValue?.[i]?.[0]
                                : inputsData[input]?.inputValue?.[0] || null

                        const dateTimeTo = isMeasurementType
                            ? null
                            : inputsData[input]?.inputValue?.[i]?.length < 1
                                ? inputsData[input]?.inputValue?.[i]?.[1]
                                : inputsData[input]?.inputValue?.[1] || null

                        requestData = {
                            dpp: dppId ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep.id}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            dateTimeFrom,
                            dateTimeTo,
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                            updatable: inputsData[input].updatableInput || false,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            additional: i !== 0,
                            inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                        }

                        if (inputsData[input].inputValue[i][0] || inputsData[input].inputValue[i][1]) {
                            const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                            stepConfirmedInput = isSuccess(inputResponse)
                        } else {
                            const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                            stepConfirmedInput = isSuccess(inputResponse)
                            break
                        }
                    }
                } else {
                    const isMeasurementType = inputsData[input]?.inputValue === MEASUREMENT_TYPE

                    const dateTimeFrom = isMeasurementType
                        ? null
                        : inputsData[input].inputValue[0] || null

                    const dateTimeTo = isMeasurementType
                        ? null
                        : inputsData[input].inputValue[1] || null

                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        dateTimeFrom,
                        dateTimeTo,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        updatable: inputsData[input].updatableInput || false,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        additional: inputsData[input].additional,
                        inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)
                }

                break

            case 'datetime':
                if (Array.isArray(inputsData[input].inputValue)) {
                    let requestData
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const isMeasurementType = inputsData[input]?.inputValue?.[i] === MEASUREMENT_TYPE

                        const dateTimeTo = isMeasurementType
                            ? null
                            : inputsData[input].inputValue[i]?.length
                                ? inputsData[input].inputValue[i]
                                : inputsData[input].inputValue || null

                        requestData = {
                            dpp: dppId ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep.id}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            dateTimeTo,
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            updatable: inputsData[input].updatableInput[i] || false,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            additional: i !== 0,
                            inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                        }

                        if (inputsData[input].inputValue[i][0] || inputsData[input].inputValue[i][1]) {
                            const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                            stepConfirmedInput = isSuccess(inputResponse)
                        } else {
                            const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                            stepConfirmedInput = isSuccess(inputResponse)
                            break
                        }
                    }
                } else {
                    const isMeasurementType = inputsData[input]?.inputValue === MEASUREMENT_TYPE

                    const dateTimeTo = isMeasurementType
                        ? null
                        : inputsData[input].inputValue || null

                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        dateTimeTo,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        updatable: inputsData[input].updatableInput || false,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        additional: inputsData[input].additional,
                        inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)
                }

                break

            case 'coordinates':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const isMeasurementType = inputsData[input]?.inputValue?.[i] === MEASUREMENT_TYPE

                        const latitudeValue = isMeasurementType
                            ? 0
                            : inputsData[input].inputValue[i].lat || 0

                        const longitudeValue = isMeasurementType
                            ? 0
                            : inputsData[input].inputValue[i].lng || 0

                        const requestData = {
                            dpp: dppId ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep.id}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            latitudeValue,
                            longitudeValue,
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input].measurementValueInputs[i]) || 0,
                            updatable: inputsData[input].updatableInput[i] || false,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                            additional: i !== 0,
                            inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                        }

                        const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const isMeasurementType = inputsData[input]?.inputValue === MEASUREMENT_TYPE

                    const latitudeValue = isMeasurementType
                        ? 0
                        : inputsData[input].inputValue.lat || 0

                    const longitudeValue = isMeasurementType
                        ? 0
                        : inputsData[input].inputValue.lng || 0

                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        latitudeValue,
                        longitudeValue,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        updatable: inputsData[input].updatableInput || false,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                        additional: inputsData[input].additional,
                        inputReference: inputsData[input].additional ? null : `/api/product_steps/${productStep.id}`,
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)
                }

                break

            case 'checkboxlist':
                if (Array.isArray(inputsData[input].inputValue)
                    && inputsData[input].inputValue.every(val => Array.isArray(val))
                ) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const requestData = {
                            dpp: dppId ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep.id}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            checkboxValue: Array.isArray(inputsData[input]?.inputValue?.[i])
                                ? inputsData[input].inputValue[i]
                                : [],
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            measurementValue: Number(inputsData[input][i].measurementValueInputs) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                        }

                        const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        checkboxValue: Array.isArray(inputsData[input].inputValue) ? inputsData[input].inputValue : [],
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        updatable: inputsData[input].updatableInput || false,
                        additional: inputsData[input].additional,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)
                }
                break

            case 'radiolist':
                if (Array.isArray(inputsData[input].inputValue)) {
                    for (let i = 0; i < inputsData[input].inputValue.length; i++) {
                        const requestData = {
                            dpp: dppId ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep.id}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            radioValue: inputsData[input].inputValue[i],
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            updatable: inputsData[input].updatableInput[i] || false,
                            additional: i !== 0,
                            measurementValue: Number(inputsData[input][i].measurementValueInputs) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                        }

                        const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        radioValue: inputsData[input].inputValue,
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        updatable: inputsData[input].updatableInput || false,
                        additional: inputsData[input].additional,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)
                }
                break

            case 'textlist':
                if (Array.isArray(inputsData[input].textList)
                    && inputsData[input].textList.every(val => Array.isArray(val))
                ) {
                    for (let i = 0; i < inputsData[input].textList.length; i++) {
                        const requestData = {
                            dpp: dppId ? `/api/dpps/${dppId}` : null,
                            productStep: `/api/product_steps/${productStep.id}`,
                            name: inputsData[input].name,
                            type: inputsData[input].type,
                            inputCategories: inputsData[input].inputCategories,
                            textValue: inputsData[input].textList[i].join(', '),
                            createQr: false,
                            locked: !!inputsData[input].locked,
                            additional: i !== 0,
                            measurementValue: Number(inputsData[input][i].measurementValueInputs) || 0,
                            measurementType: inputUnit?.measurementType || '',
                            unitMeasurement: inputUnit?.unitMeasurement || '',
                            unitSymbol: inputUnit?.unitSymbol || '',
                        }

                        const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                        stepConfirmedInput = isSuccess(inputResponse)
                    }
                } else {
                    const requestData = {
                        dpp: dppId ? `/api/dpps/${dppId}` : null,
                        productStep: `/api/product_steps/${productStep.id}`,
                        name: inputsData[input].name,
                        type: inputsData[input].type,
                        inputCategories: inputsData[input].inputCategories,
                        textValue: inputsData[input].textList.join(', '),
                        createQr: false,
                        locked: !!inputsData[input].locked,
                        additional: inputsData[input].additional,
                        measurementValue: Number(inputsData[input].measurementValueInputs) || 0,
                        measurementType: inputUnit?.measurementType || '',
                        unitMeasurement: inputUnit?.unitMeasurement || '',
                        unitSymbol: inputUnit?.unitSymbol || '',
                    }

                    const inputResponse = await useProductsInputsStore().createProductInput(requestData)

                    stepConfirmedInput = isSuccess(inputResponse)
                }

                break
        }

        if (stepConfirmedInput
            && !productSteps.has(productStep.id)
            && !inputSteps.has(inputsData[input].stepId)
            && !isSubmited.value
        ) {
            const { ongoingDpp: stateOngoingDpp, createEmptyDpp: stateCreateEmptyDpp } = getStateByStep(inputsData[input].stepId)
            let state: string | null = null

            if (stateOngoingDpp) {
                state = 'ONGOING_DPP'
            } else if (stateCreateEmptyDpp) {
                state = 'EMPTY_DPP'
            } else if (formValues.value.logistics[inputsData[input].stepId]) {
                state = 'NOT_ASSIGNED'
            } else {
                state = 'NOT_ASSIGNED'
            }

            const logistics = formValues.value.logistics[inputsData[input].stepId]
            const productStepsDppData = formValues.value.productStepsDpp[inputsData[input].stepId]
            const productStepsDppPreviousData = formValues.value.dpp[inputsData[input].stepId]

            const apiUrlProcessedMaterials = showProductStepsInput.value[inputsData[input].stepId]
                ? Array.isArray(productStepsDppData)
                    ? productStepsDppData.map((productStepDpp: string) => `/api/product_steps/${productStepDpp}`)
                    : []
                : productStepsDppPreviousData
                    ? [`/api/product_steps/${productStepsDppPreviousData}`]
                    : []

            const processedMaterials = apiUrlProcessedMaterials
            const unitSymbol = nodeData.value?.steps.find(step => step.id === inputsData[input].stepId).unitSymbol

            // This request takes an awfully long time on locale 17.48s (PATCH)
            // You should probably contact BE
            await useProductsStore().updateProductStepById(productStep.id, {
                createQr: true,
                locked: true,
                unitMeasurement: inputsData[input].unitMeasurement || null,
                measurementType: inputsData[input].measurementType || null,
                measurementValue: Number.parseFloat(inputsData[input].measurementValueProductSteps) || null,
                unitSymbol: unitSymbol || '',
                ongoingDpp: stateOngoingDpp,
                createEmptyDpp: stateCreateEmptyDpp,
                state,
                processedMaterials,
                materialsReceivedFrom: logistics ? [`/api/logistics/${logistics}`] : [],
            })

            productSteps.add(productStep.id)
            inputSteps.add(inputsData[input].stepId)
        }
    }
}

const getStateBigDpp = () => {
    const classes = activeSteps.value.map((idStep: string) => getStateByStep(idStep).class)

    const total = classes.length
    const ongoingCount = classes.filter((c: string) => c === 'ongoing').length
    const emptyCount = classes.filter((c: string) => c === 'empty').length
    const finishCount = classes.filter((c: string) => c === 'finish').length

    if (ongoingCount === total) {
        return 'ONGOING_DPP'
    }

    if (emptyCount === total) {
        return 'EMPTY_DPP'
    }

    if (finishCount >= 1 && finishCount < total) {
        return 'ONGOING_DPP'
    }

    return 'NOT_ASSIGNED'
}

const addDPP = async (dppStatus: any) => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        useNuxtApp().$event('message', {
            type: 'error',
            message: t('messages.companyReq'),
            title: 'Error',
        })

        return
    }

    try {
        const productTemplateId = nodeData.value?.productTemplates?.[0]
        const nodeTemplateId = nodeData.value?.nodeTemplate
        const stepsId = activeSteps.value.map((id: string) => `/api/steps/${id}`)
        const productSteps: string[] = []
        let nodeTemplateData
        let products
        let getNodeTemplateResponse
        let productResponse
        let dppResponse: any = null

        const [nodeTemplateSteps, productTemplateSteps] = activeStepsData.value.reduce(
            (result: any[], active: any) => {
                if (active?.type === 'NODE_TEMPLATE') {
                    result[0].push(active.id)
                } else {
                    result[1].push(active.id)
                }

                return result
            },
            [[], []],
        )

        if (productTemplateId) {
            if (productTemplateSteps?.length) {
                productResponse = await useProductsStore().createDppProduct({
                    productTemplate: productTemplateId,
                    steps: productTemplateSteps,
                    node: nodeData.value['@id'],
                    company: `/api/companies/${formValues.value.company}`,
                    user: `/api/users/${userId.value}`,
                    companySite: formValues.value.site && formValues.value.site !== 0 ? `/api/company_sites/${formValues.value.site}` : null,
                    materialsReceivedFrom: null,
                })

                dppProductId.value = productResponse.id

                const getProductResponse = await useProductsStore().fetchDppProductById(productResponse.id)

                for (const productStep of getProductResponse.productSteps) {
                    if (productSteps.includes(`/api/product_steps/${productStep.id}`)) {
                        continue
                    }

                    productSteps.push(`/api/product_steps/${productStep.id}`)

                    const updatePromises = productTemplateSteps.map(async (productTemplateStep: string) => {
                        let { ongoingDpp: stateOngoingDpp, createEmptyDpp: stateCreateEmptyDpp } = getStateByStep(productTemplateStep)
                        let state: string | null = null
                        const logistics = formValues.value.logistics[productTemplateStep]
                        if (activeSteps.value?.length === 1 || stateCreateEmptyDpp) {
                            if (stateOngoingDpp) {
                                state = 'ONGOING_DPP'
                            } else if (stateCreateEmptyDpp) {
                                state = 'EMPTY_DPP'
                            } else if (logistics) {
                                state = 'NOT_ASSIGNED'
                            } else {
                                state = 'NOT_ASSIGNED'
                            }

                            if (stateOngoingDpp === undefined && stateCreateEmptyDpp === undefined) {
                                state = 'EMPTY_DPP'
                                stateCreateEmptyDpp = true
                            }

                            const productStepsDppData = formValues.value.productStepsDpp[productTemplateStep]
                            const productStepsDppPreviousData = formValues.value.dpp[productTemplateStep]

                            const apiUrlProcessedMaterials = showProductStepsInput.value[productTemplateStep]
                                ? Array.isArray(productStepsDppData)
                                    ? productStepsDppData.map((productStepDpp: string) => `/api/product_steps/${productStepDpp}`)
                                    : []
                                : productStepsDppPreviousData
                                    ? [`/api/product_steps/${productStepsDppPreviousData}`]
                                    : []

                            const processedMaterials = apiUrlProcessedMaterials
                            const stepData = nodeData.value?.steps.find(step => step.id === productTemplateStep)

                            await useProductsStore().updateProductStepById(productStep.id, {
                                ongoingDpp: stateOngoingDpp,
                                createEmptyDpp: stateCreateEmptyDpp,
                                processedMaterials,
                                state,
                                materialsReceivedFrom: logistics ? [`/api/logistics/${logistics}`] : [],
                                unitMeasurement: stepData?.unitMeasurement || null,
                                measurementType: stepData?.measurementType || null,
                                measurementValue: Number.parseFloat(formValues.value.measurementValueProductSteps[productTemplateStep]) || null,
                                unitSymbol: stepData.unitSymbol || '',
                            })
                        }
                    })

                    await Promise.all(updatePromises)
                }

                productResponseData.value = getProductResponse
            }
        }

        if (nodeTemplateId) {
            if (nodeTemplateSteps?.length) {
                nodeTemplateData = await useProductsStore().createDppProduct({
                    productTemplate: nodeTemplateId,
                    steps: nodeTemplateSteps,
                    node: nodeData.value['@id'],
                    company: `/api/companies/${formValues.value.company}`,
                    user: `/api/users/${userId.value}`,
                    companySite: formValues.value.site && formValues.value.site !== 0 ? `/api/company_sites/${formValues.value.site}` : null,
                    materialsReceivedFrom: null,
                })

                getNodeTemplateResponse = await useProductsStore().fetchDppProductById(nodeTemplateData.id)

                for (const productStep of getNodeTemplateResponse.productSteps) {
                    productSteps.push(`/api/product_steps/${productStep.id}`)

                    const updatePromises = nodeTemplateSteps.map(async (nodeTemplateStep: string) => {
                        let { ongoingDpp: stateOngoingDpp, createEmptyDpp: stateCreateEmptyDpp } = getStateByStep(nodeTemplateStep)
                        let state: string | null = null
                        const logistics = formValues.value.logistics[nodeTemplateStep]
                        if (activeSteps.value?.length === 1 || stateCreateEmptyDpp) {
                            if (stateOngoingDpp) {
                                state = 'ONGOING_DPP'
                            } else if (stateCreateEmptyDpp) {
                                state = 'EMPTY_DPP'
                            } else if (logistics) {
                                state = 'NOT_ASSIGNED'
                            } else {
                                state = 'NOT_ASSIGNED'
                            }

                            if (stateOngoingDpp === undefined && stateCreateEmptyDpp === undefined) {
                                state = 'EMPTY_DPP'
                                stateCreateEmptyDpp = true
                            }

                            const productStepsDppData = formValues.value.productStepsDpp[nodeTemplateStep]
                            const productStepsDppPreviousData = formValues.value.dpp[nodeTemplateStep]

                            const apiUrlProcessedMaterials = showProductStepsInput.value[nodeTemplateStep]
                                ? Array.isArray(productStepsDppData)
                                    ? productStepsDppData.map((productStepDpp: string) => `/api/product_steps/${productStepDpp}`)
                                    : []
                                : productStepsDppPreviousData
                                    ? [`/api/product_steps/${productStepsDppPreviousData}`]
                                    : []

                            const processedMaterials = apiUrlProcessedMaterials
                            const stepData = nodeData.value?.steps.find(step => step.id === nodeTemplateStep)

                            await useProductsStore().updateProductStepById(productStep.id, {
                                ongoingDpp: stateOngoingDpp,
                                createEmptyDpp: stateCreateEmptyDpp,
                                processedMaterials,
                                state,
                                materialsReceivedFrom: logistics ? [`/api/logistics/${logistics}`] : [],
                                unitMeasurement: stepData?.unitMeasurement || null,
                                measurementType: stepData?.measurementType || null,
                                measurementValue: Number.parseFloat(formValues.value.measurementValueProductSteps[nodeTemplateStep]) || null,
                                unitSymbol: stepData.unitSymbol || '',
                            })
                        }
                    })

                    await Promise.all(updatePromises)
                }
            }
        }

        if (nodeTemplateData && productResponse) {
            products = [productResponse['@id'], nodeTemplateData['@id']]
            productResponseData.value.productSteps = productResponseData.value.productSteps.concat(getNodeTemplateResponse.productSteps)
        }

        if (nodeTemplateData && !productResponse) {
            products = [nodeTemplateData['@id']]
            productResponseData.value = { productSteps: getNodeTemplateResponse.productSteps }
        }

        if (!nodeTemplateData && productResponse) {
            products = [productResponse['@id']]
        }

        if (activeSteps.value?.length > 1) {
            const logisticsArray = Object.values(formValues.value.logistics || {}).filter(id => id)
            const uniqueLogistics = [...new Set(logisticsArray)].map(id => `/api/logistics/${id}`)

            if (formValues.value.logistic) {
                uniqueLogistics.push(`/api/logistics/${formValues.value.logistic}`)
            }

            dppResponse = await useDppStore().createDpp({
                company: `/api/companies/${formValues.value.company}`,
                companySite: formValues.value.site && formValues.value.site !== 0 ? `/api/company_sites/${formValues.value.site}` : null,
                materialsReceivedFrom: uniqueLogistics || [],
                createEmptyDpp: getStateBigDpp() === 'EMPTY_DPP',
                ongoingDpp: getStateBigDpp() === 'ONGOING_DPP',
                createQr: true,
                products,
                node: nodeData.value['@id'],
                productSteps,
                qrId: formValues.value.qrId,
                steps: stepsId || [],
                locked: dppStatus.createDpp,
                supplyChainTemplate: nodeData.value.supplyChainTemplate,
                user: `/api/users/${userId.value}`,
                state: getStateBigDpp(),
            })

            dppCreateId.value = dppResponse?.id
        }

        if (dppStatus.ongoingDpp || dppStatus.createDpp) {
            await createInputsDpp(inputData.value, dppResponse?.id || null, productResponseData.value)
        }

        isConfirmFilled.value = false

        useNuxtApp().$event('message', {
            type: 'success',
            message: t('messages.createDpps'),
            title: 'Success',
        })

        useNuxtApp().$event('dppAdded', true)

        closeAddDppModal()
    } catch (error) {
        console.error('Error creating DPP:', error)
        useNuxtApp().$event('message', {
            type: 'error',
            message: t('messages.errorDppCreation'),
            title: 'Error',
        })
    }
}

const confirmFilled = async (stepIndex: number, stepId: string) => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        const message: MessageBag = {
            type: 'error',
            message: t('messages.companyReq'),
            title: 'Error',
        }

        useNuxtApp().$event('message', message)

        return
    }

    const inputMap: Record<string, any> = {}
    const step = nodeData.value.steps?.find((s: any) => s.id === stepId)
    const hasInputs = step.inputs && step.inputs.length > 0

    if (!hasInputs) {
        const message: MessageBag = {
            type: 'info',
            message: t('messages.confirmInput'),
            title: 'Info',
        }

        useNuxtApp().$event('message', message)

        return
    }

    for (const key in formValues.value.inputs) {
        const value = formValues.value.inputs[key]
        const baseId = key.split('_')[0]

        if (!inputMap[baseId]) {
            inputMap[baseId] = {
                id: baseId,
                name: null,
                stepId: null,
                stepName: null,
                inputValue: [],
                measurementValueInputs: [],
                updatableInput: [],
                type: null,
                inputCategories: null,
                isConfirmFilled: false,
                unitMeasurement: null,
                measurementType: null,
                logistics: null,
                dpp: null,
                productStepsDpp: null,
                additional: false,
            }
        }

        inputMap[baseId].inputValue.push(value)
        inputMap[baseId].measurementValueInputs.push(formValues.value.measurementValue[key])
    }

    activeStepsData.value.forEach((step: any) => {
        if (!step?.inputs || step?.inputs.length === 0) {
            return
        }

        step.inputs?.forEach((input: any) => {
            if (inputMap[input.id]) {
                inputMap[input.id].stepId = step.id
                inputMap[input.id].stepName = step.name
                inputMap[input.id].unitMeasurement = step.unitMeasurement
                inputMap[input.id].measurementType = step.measurementType
                inputMap[input.id].measurementValueProductSteps = formValues.value.measurementValueProductSteps[step.id]
                inputMap[input.id].name = input.name
                inputMap[input.id].type = input.type
                inputMap[input.id].inputCategories = input.categories || []
                inputMap[input.id].additional = input.additional || false
                inputMap[input.id].updatableInput = input.updatable
            }

            if (input.type === 'textList') {
                if (!inputMap[input.id]) {
                    inputMap[input.id] = {}
                }

                inputMap[input.id].stepId = step.id
                inputMap[input.id].stepName = step.name
                inputMap[input.id].unitMeasurement = step.unitMeasurement
                inputMap[input.id].measurementType = step.measurementType
                inputMap[input.id].measurementValueProductSteps = formValues.value.measurementValueProductSteps[step.id]
                inputMap[input.id].name = input.name
                inputMap[input.id].type = input.type
                inputMap[input.id].inputCategories = input.categories || []
                inputMap[input.id].textList = input.options || []
                inputMap[input.id].additional = input.additional || false
                inputMap[input.id].updatableInput = input.updatable
            }
        })
    })

    for (const key in inputMap) {
        if (inputMap[key].inputValue?.length === 1) {
            inputMap[key].inputValue = inputMap[key].inputValue[0]
        }

        if (inputMap[key].measurementValueInputs?.length === 1) {
            inputMap[key].measurementValueInputs = inputMap[key].measurementValueInputs[0]
        }

        if (inputMap[key].updatableInput?.length === 1) {
            inputMap[key].updatableInput = inputMap[key].updatableInput[0]
        }

        // Validate and lock the input on confirmation of the step if it has a value
        if (isInputFilled(inputMap[key].inputValue)) {
            inputMap[key].locked = true
        }

        const previousValue = previousInputData.value[key]
        if (!previousValue || JSON.stringify(previousValue.inputValue) !== JSON.stringify(inputMap[key].inputValue)) {
            inputMap[key].isConfirmFilled = true
        }
    }

    confirmedSteps.value[stepIndex] = true

    previousInputData.value = { ...inputMap }
    inputData.value = inputMap

    if (formValues.value.logistics[stepId] && !formValues.value.dpp[stepId]) {
        formValues.value.logistics[stepId] = null
    }

    if (formValues.value.logistics[stepId] && formValues.value.dpp[stepId] && !formValues.value.productStepsDpp[step.id] && showProductStepsInput.value[step.id]) {
        formValues.value.logistics[stepId] = null
        formValues.value.dpp[stepId] = null
    }

    const { validationResult } = validateSteps(nodeData.value.steps, inputData.value)
    const hasValidStep = validationResult.some((result: any) => result.isValid)
    const allValid = validationResult.every((result: any) => result.isEmptyAllInputs)

    stepsState.value = validationResult

    if (hasValidStep) {
        dppCreationStatus.value = {
            createDpp: true,
            ongoingDpp: false,
            emptyDpp: false,
            btnLable: t('dpps.save'),
        }
    } else {
        dppCreationStatus.value = {
            createDpp: false,
            ongoingDpp: false,
            emptyDpp: true,
            btnLable: t('dpps.createEmptyDpp'),
        }
    }

    if (!allValid) {
        dppCreationStatus.value = {
            createDpp: true,
            ongoingDpp: false,
            emptyDpp: false,
            btnLable: t('dpps.save'),
        }
    }

    isConfirmFilled.value = true

    const message: MessageBag = {
        type: 'success',
        message: t('messages.logisticsFillInData'),
        title: 'Success',
    }

    useNuxtApp().$event('message', message)
}

const runValidation = () => {
    const { validationResult, allStepsValid } = validateSteps(nodeData.value.steps, inputData.value, false)
    const hasValidStep = validationResult.some((result: any) => result.isValid)

    dppCreationStatus.value = hasValidStep
        ? {
            createDpp: false,
            ongoingDpp: true,
            emptyDpp: false,
            btnLable: t('dpps.createOngoingDpp'),
        }
        : {
            createDpp: false,
            ongoingDpp: false,
            emptyDpp: true,
            btnLable: t('dpps.createEmptyDpp'),
        }

    dppCreationStatus.value = allStepsValid
        ? {
            createDpp: true,
            ongoingDpp: false,
            emptyDpp: false,
            btnLable: t('dpps.save'),
        }
        : {
            createDpp: false,
            ongoingDpp: true,
            emptyDpp: false,
            btnLable: t('dpps.createOngoingDpp'),
        }
}

const addInput = (index: number) => {
    $event('openDppAddInputModal', { indexStep: index, type: 'add-dpp' })
}

const updateAllProductStepsAvailability = () => {
    const allSelected: Record<string, boolean> = {}

    Object.values(formValues.value.productStepsDpp || {}).forEach((selected: any) => {
        (selected || []).forEach((id: string) => {
            allSelected[id] = true
        })
    })

    for (const key in selectedDpps.value) {
        if (!Array.isArray(selectedDpps.value[key])) {
            delete selectedDpps.value[key]
        }
    }

    Object.keys(formValues.value.dpp || {}).forEach((stepId: string) => {
        const dppId = formValues.value.dpp[stepId]

        const dppsList = selectedDpps.value[stepId] || selectedLogistics.value[stepId]

        if (!Array.isArray(dppsList)) {
            productStepsDpp.value[stepId] = []

            return
        }
        const dpp = dppsList.find(d => d.id === dppId)

        if (!dpp || !dpp.productSteps?.length) {
            productStepsDpp.value[stepId] = []

            return
        }

        const currentSelection = formValues.value.productStepsDpp?.[stepId] || []

        const available = dpp.productSteps
            .filter(ps => !allSelected[ps.id] || currentSelection.includes(ps.id))
            .map(ps => ({
                title: ps.name,
                value: ps.id,
            }))

        productStepsDpp.value[stepId] = available

        formValues.value.productStepsDpp[stepId] = currentSelection.filter((id: string) =>
            available.some(opt => opt.value === id),
        )

        if (!formValues.value.productStepsDpp[stepId]?.length) {
            formValues.value.productStepsDpp[stepId] = null
        }
    })
}

const updateDppOptionsForOtherSteps = () => {
    const selectedDppIds = Object.values(formValues.value.dpp)

    activeStepsData.value.forEach(productStep => {
        const currentStepId = productStep.id

        dpps.value[currentStepId] = (dppsByNode.value?.dpps || [])
            .filter(dpp => {
                const isProductStep = dpp.type === 'ProductStep'

                const isSelectedInCurrentStep = formValues.value.dpp[currentStepId] === dpp.id

                const isUsedElsewhere = selectedDppIds.some(id => id !== currentStepId && id === dpp.id)

                if (isProductStep) {
                    return isProductStep && (isSelectedInCurrentStep || !isUsedElsewhere)
                } else {
                    return {
                        title: `${dpp.name} - ${dpp.id}`,
                        value: dpp.id,
                    }
                }
            })
            .map(dpp => ({
                title: `${dpp.name} - ${dpp.id}`,
                value: dpp.id,
            }))
    })
}

/* eslint-disable @typescript-eslint/no-use-before-define */
const handleLogisticsChange = async (stepId: string, newValue: any, isDppsChange: boolean = false) => {
    if ((!selectedLogistics.value || !selectedLogistics.value[stepId]) && selectedFromProductSteps.value[newValue]?.fromProductSteps) {
        const matchedTitles = selectedFromProductSteps.value[newValue]?.fromProductSteps.map((url: string) => {
            const id = url.split('/').pop()
            if (!dpps.value[stepId]?.length) {
                return null
            }

            return dpps.value[stepId].find((item: any) => item.value === id)?.value || null
        }).filter(Boolean)

        formValues.value.dpp[stepId] = matchedTitles[0] || null

        return
    }

    const fromDppsByLogistics = fromDpps.value.logistics?.filter(logistic => logistic.id === newValue)

    if (fromDppsByLogistics?.length) {
        await fetchRelevantDppsForSteps(fromDppsByLogistics[0].fromDpps, fromDppsByLogistics[0].id)
    }

    selectedDpps.value[stepId] = selectedLogistics.value[stepId]

    dpps.value[stepId] = []
    formValues.value.dpp[stepId] = null
    formValues.value.productStepsDpp[stepId] = null
    productStepsDpp.value[stepId] = []
    showProductStepsInput.value[stepId] = false

    if (isDppsChange) {
        await handleDppsChange(stepId, selectedDpps.value[stepId]?.[0]?.id)
    }

    const filteredDpps = selectedDpps.value[stepId]
        ?.filter((dpp: any) => dpp.productSteps?.length)
        ?.filter((dpp: any) => dpp?.logisticsId === newValue)
        .map((dpp: any) => ({
            title: `${dpp.name} - ${dpp.id}`,
            value: dpp.id,
        }))

    dpps.value[stepId] = filteredDpps

    if (!filteredDpps?.length) {
        await fetchRelevantDppsForSteps()
        formValues.value.dpp[stepId] = null
        formValues.value.productStepsDpp[stepId] = null
        productStepsDpp.value[stepId] = []
        showProductStepsInput.value[stepId] = false
    }
}

const handleDppsChange = async (stepId: string, newValue: any) => {
    const dpp = dppsByNode.value.dpps.find(dppItem => dppItem.id === newValue)
    if (!dpp) {
        return
    }

    formValues.value.logistics[stepId] = dpp.logistics?.id ?? null

    const selectedDpp = selectedDpps.value[stepId]?.find(d => d.id === newValue)

    formValues.value.productStepsDpp[stepId] = null
    productStepsDpp.value[stepId] = []
    showProductStepsInput.value[stepId] = false

    if (!selectedDpp) {
        await handleLogisticsChange(stepId, dpp.logistics?.id, true)

        if (selectedLogistics.value[stepId]) {
            formValues.value.dpp[stepId] = selectedLogistics.value[stepId][0].id
        }
        formValues.value.dpp[stepId] = dpp.id

        showProductStepsInput.value[stepId] = productStepsDpp.value[stepId]?.length > 0
    }

    if (selectedDpp?.productSteps?.length) {
        showProductStepsInput.value[stepId] = true
        productStepsDpp.value[stepId] = selectedDpp.productSteps.map((ps: any) => ({
            title: ps.name,
            value: ps.id,
        }))

        const selectedProductStepIds = Object.values(prevProductStepSelections).flat()

        productStepsDpp.value[stepId] = productStepsDpp.value[stepId].filter(
            (item: any) => !selectedProductStepIds.includes(item.value),
        )
    }

    if (dpp.type === 'ProductStep') {
        updateDppOptionsForOtherSteps()
    } else {
        formValues.value.dpp[stepId] = dpp.id

        const dppsItemsData = dpps.value[stepId]

        const fromDppsByLogistics = fromDpps.value.logistics?.filter(logistic => logistic.id === dpp?.logistics.id)

        if (fromDppsByLogistics?.length) {
            await fetchRelevantDppsForSteps(fromDppsByLogistics[0].fromDpps, fromDppsByLogistics[0].id)

            if (selectedLogistics.value[stepId][0].productSteps?.length) {
                showProductStepsInput.value[stepId] = true
                productStepsDpp.value[stepId] = selectedLogistics.value[stepId][0].productSteps.map((ps: any) => ({
                    title: ps.name,
                    value: ps.id,
                }))

                const selectedProductStepIds = Object.values(prevProductStepSelections).flat()

                productStepsDpp.value[stepId] = productStepsDpp.value[stepId].filter(
                    (item: any) => !selectedProductStepIds.includes(item.value),
                )
            }
        }

        dpps.value[stepId] = dppsItemsData

        return {
            title: `${dpp.name} - ${dpp.id}`,
            value: dpp.id,
        }
    }

    showProductStepsInput.value[stepId] = false
}

/* eslint-enable @typescript-eslint/no-use-before-define */

const handleProductStepsDpp = (stepId: string, newValue: string[]) => {
    prevProductStepSelections[stepId] = [...newValue]
    updateAllProductStepsAvailability()
}

$listen('handleDppInputSubmitted', input => {
    const stepIndex = input.indexStep

    if (!dynamicallyAddedInputs.value[stepIndex]) {
        dynamicallyAddedInputs.value[stepIndex] = []
    }

    const existingInput = nodeData.value.steps[stepIndex].inputs.find(i => i.id === input.id)
    if (existingInput) {
        return
    }

    nodeData.value.steps[stepIndex].inputs.push({
        id: input.id,
        type: input.inputType,
        name: input.label,
        options: input.options,
        additional: true,
        updatable: input.updatableInput,
        measurementType: input.measurementType,
        unitMeasurement: input.unitMeasurement,
        unitSymbol: input.unitSymbol,
    })

    dynamicallyAddedInputs.value[stepIndex].push(input.id)

    runValidation()
})
</script>

<template>
    <ModalLayout
        :is-open="isAddDppModalOpen"
        name="simple-modal"
        :title="`Add DPP: ${nodeData?.name}`"
        no-buttons
        width="70vw"
        style="overflow-y: scroll;"
        @modal-close="closeAddDppModal"
    >
        <template #content>
            <VContainer class="w-100">
                <p class="num_of_inputs mb-5">
                    Number of inputs: {{ inputs.length }}
                </p>

                <VForm
                    ref="form"
                    v-model="valid"
                >
                    <VRow>
                        <VCol
                            cols="12"
                            sm="6"
                        >
                            <VTextField
                                v-model="formValues.idQr"
                                :label="t('dpps.idQr')"
                                variant="outlined"
                                required
                            />
                        </VCol>
                        <VCol
                            cols="12"
                            sm="6"
                        >
                            <VSelect
                                v-model="formValues.company"
                                :label="t('dpps.company')"
                                variant="outlined"
                                :items="companies"
                                :rules="companyRules"
                                :loading="isLoading.company"
                                :no-data-text="isLoading.company ? $t('dpps.loadingCompanies') : $t('noDataAvailable')"
                            />
                        </VCol>
                        <VCol
                            cols="12"
                            sm="6"
                        >
                            <VSelect
                                v-model="formValues.site"
                                :label="t('dpps.site')"
                                variant="outlined"
                                :items="sites"
                                :loading="isLoading.site"
                                :no-data-text="isLoading.site ? $t('dpps.loadingSites') : siteNoDataAvailable"
                            />
                        </VCol>
                        <VCol
                            cols="12"
                            sm="6"
                            class="hidden"
                        >
                            <VSelect
                                v-model="formValues.logistic"
                                :label="t('dpps.logistic')"
                                :placeholder="t('dpps.selectLogistic')"
                                variant="outlined"
                                :items="nodeData.logisticsItem"
                            />
                        </VCol>
                    </VRow>

                    <div
                        v-for="(step, index) in nodeData.steps"
                        :key="step.id"
                    >
                        <VRow>
                            <VCol
                                cols="12"
                                class="step-checkbox"
                                :class="{ 'no-border': index === 0 }"
                            >
                                <VCheckbox
                                    :model-value="activeSteps.find(id => id === step.id)"
                                    :value="step.id"
                                    :disabled="confirmedSteps[index]"
                                    @click="toggleStep(step)"
                                >
                                    <template #label>
                                        <span>{{ step.name }} <span v-if="activeSteps.includes(step.id)">({{ $t('state') }}:  <strong :class="getStateByStep(step.id).class">{{ `${getStateByStep(step.id).title}` }}</strong> )</span></span>
                                    </template>
                                </VCheckbox>
                            </VCol>

                            <template v-if="activeSteps.includes(step.id)">
                                <VCol
                                    :key="step.id"
                                    cols="12"
                                    sm="6"
                                    class="my-0 py-0 pb-4"
                                >
                                    <VSelect
                                        v-model="formValues.logistics[step.id]"
                                        :label="t('dpps.logistic')"
                                        :placeholder="t('dpps.selectLogistic')"
                                        variant="outlined"
                                        :items="nodeData.logisticsItem"
                                        :disabled="confirmedSteps[index]"
                                        clearable
                                        @update:model-value="(val) => handleLogisticsChange(step.id, val)"
                                    />
                                </VCol>

                                <VCol
                                    :key="step.id"
                                    cols="12"
                                    sm="6"
                                    class="my-0 py-0 pb-4"
                                >
                                    <VSelect
                                        v-model="formValues.dpp[step.id]"
                                        :label="t('dpps.previousDpp')"
                                        :placeholder="t('dpps.previousDpp')"
                                        variant="outlined"
                                        :items="dpps[step.id]"
                                        :disabled="confirmedSteps[index]"
                                        clearable
                                        @update:model-value="(val) => handleDppsChange(step.id, val)"
                                    />
                                </VCol>

                                <VCol
                                    v-if="showProductStepsInput[step.id]"
                                    :key="step.id"
                                    cols="12"
                                    sm="6"
                                    class="my-0 py-0 pb-4"
                                >
                                    <VSelect
                                        v-model="formValues.productStepsDpp[step.id]"
                                        :label="t('dpps.productStepDpp')"
                                        :placeholder="t('dpps.productStepDpp')"
                                        variant="outlined"
                                        :items="productStepsDpp[step.id]"
                                        multiple
                                        :disabled="confirmedSteps[index]"
                                        clearable
                                        @update:model-value="(val) => handleProductStepsDpp(step.id, val)"
                                    />
                                </VCol>
                                <VCol
                                    v-if="step.batchTypeOfStep === 'BATCH' && step.measurementType"
                                    :key="step.id"
                                    cols="12"
                                    sm="6"
                                    class="my-0 py-0 pb-4"
                                >
                                    <VTextField
                                        v-model="formValues.measurementValueProductSteps[step.id]"
                                        :label="`${step.measurementType === 'batchQuantity' ? $t('dpps.batchQuantity') : formatPascalCaseToLabel(step.measurementType)}${step.unitMeasurement ? ` (${step.unitMeasurement})` : ''}`"
                                        variant="outlined"
                                        type="number"
                                        required
                                        :disabled="confirmedSteps[index]"
                                    >
                                        <template #append-inner>
                                            {{ step.unitSymbol }}
                                        </template>
                                    </VTextField>
                                </VCol>

                                <VCol
                                    v-for="input in step.inputs.filter(i => !i.additional)"
                                    :key="input.id"
                                    cols="12"
                                    sm="6"
                                    class="my-0 py-0 pb-4"
                                >
                                    <InputByType
                                        v-model="formValues.inputs[input.id]"
                                        :measurement-model-value="formValues.measurementValue[input.id]"
                                        :updatable-input="formValues.updatableInput[input.id]"
                                        :type="input.type"
                                        :name="input.name"
                                        :input="input"
                                        :is-disabled="confirmedSteps[index]"
                                        :show-updatable-input="false"
                                        @update:measurement-model-value="val => formValues.measurementValue[input.id] = val"
                                        @update:updatable-input="val => formValues.updatableInput[input.id] = val"
                                    />
                                    <VBtn
                                        v-if="!['textList', 'textlist'].includes(input.type)"
                                        class="duplicate-btn"
                                        variant="plain"
                                        :disabled="confirmedSteps[index]"
                                        @click="duplicateInput(step, input.id)"
                                    >
                                        <PhosphorIconPlusCircle
                                            :size="16"
                                            class="me-2 ms-0 ps-0"
                                        />
                                        {{ t('dpps.duplicateInput') }}
                                    </VBtn>
                                </VCol>

                                <VCol
                                    v-if="step.inputs.some(i => i.additional)"
                                    cols="12"
                                    class="mt-4"
                                >
                                    <VRow class="pa-4">
                                        <VRow style="width: 100%">
                                            <span class="additional-input-title">{{ t('dpps.additionalInputs') }}:</span>
                                        </VRow>
                                        <VRow>
                                            <VCol
                                                v-for="input in step.inputs.filter(i => i.additional)"
                                                :key="input.id"
                                                cols="12"
                                                sm="6"
                                                class="my-0 py-0 pb-4"
                                            >
                                                <InputByType
                                                    v-model="formValues.inputs[input.id]"
                                                    :measurement-model-value="formValues.measurementValue[input.id]"
                                                    :type="input.type"
                                                    :name="input.name"
                                                    :input="input"
                                                    :is-disabled="confirmedSteps[index]"
                                                    :updatable-input="formValues.updatableInput[input.id]"
                                                    :show-updatable-input="false"
                                                    @update:measurement-model-value="val => formValues.measurementValue[input.id] = val"
                                                    @update:updatable-input="val => formValues.updatableInput[input.id] = val"
                                                />
                                                <VBtn
                                                    v-if="!['textList', 'textlist'].includes(input.type)"
                                                    class="duplicate-btn"
                                                    variant="plain"
                                                    :disabled="confirmedSteps[index]"
                                                    @click="duplicateInput(step, input.id)"
                                                >
                                                    <PhosphorIconPlusCircle
                                                        :size="16"
                                                        class="me-2 ms-0 ps-0"
                                                    />
                                                    {{ t('dpps.duplicateInput') }}
                                                </VBtn>
                                            </VCol>
                                        </VRow>
                                    </VRow>
                                </VCol>
                            </template>
                        </VRow>

                        <template v-if="activeSteps.includes(step.id)">
                            <VRow>
                                <VCol
                                    cols="6"
                                    class="my-5 py-0 pb-4"
                                >
                                    <VBtn
                                        class="text-uppercase ma-auto d-block"
                                        color="#26A69A"
                                        variant="flat"
                                        size="large"
                                        height="45"
                                        width="90%"
                                        :disabled="confirmedSteps[index]"
                                        @click="addInput(index)"
                                    >
                                        {{ t('dpps.addInput') }}
                                    </VBtn>
                                </VCol>

                                <VCol
                                    cols="6"
                                    class="my-5 py-0 pb-4"
                                >
                                    <VBtn
                                        class="text-uppercase ma-auto d-block"
                                        color="#26A69A"
                                        variant="flat"
                                        size="large"
                                        height="45"
                                        width="90%"
                                        :disabled="confirmedSteps[index]"
                                        @click="confirmFilled(index, step.id)"
                                    >
                                        {{ t('dpps.confirmFilled') }}
                                    </VBtn>
                                </VCol>
                            </VRow>
                        </template>
                    </div>
                </VForm>

                <div class="d-flex align-center justify-end mt-4 mb-4">
                    <VBtn
                        class="text-uppercase mx-3"
                        color="#26A69A"
                        variant="flat"
                        size="large"
                        height="45"
                        :disabled="activeSteps?.length === 0"
                        @click="addDPP(dppCreationStatus)"
                    >
                        {{ dppCreationStatus.btnLable }}
                    </VBtn>

                    <VBtn
                        class="text-uppercase"
                        color="#FF474A"
                        variant="flat"
                        size="large"
                        height="45"
                        @click="closeAddDppModal"
                    >
                        {{ t('dpps.cancel') }}
                    </VBtn>
                </div>

                <TreeFlow
                    :data="steps"
                    connection-key="none"
                    traversal="forward"
                    disable-options
                />
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

    .v-label{
        .empty {
            color: #a020f0;
        }

        .finish {
            color: #66ff07;
        }

        .ongoing {
            color: #ffa500;
        }
    }
}

.duplicate-btn {
    margin: 0;
    color: #26A69A;
    font-size: 10px;
    text-transform: none;
    padding: 0;
}

.additional-input-title {
    font-size: 1.2rem;
    color: #26A69A;
    padding-left: 10px;
    padding-bottom: 10px;
}
</style>
