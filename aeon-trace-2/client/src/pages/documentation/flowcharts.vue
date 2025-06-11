<script setup lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const sections = ref({
    collection: false,
    company: false,
    dpp: false,
    imageCollection: false,
    logistics: false,
    product: false,
    step: false,
    supplyChain: false,
    transportType: false,
    user: false,
    videoCollection: false,
})

const subsections = ref({
    batch: false,
    batchUnit: false,
    country: false,
    process: false,
    role: false,
    company: false,
    companySite: false,
    dppConnector: false,
    dpp: false,
    dppJsonEnaleia: false,
    dppJsonSteps: false,
    dppJsonExport: false,
    companySiteImage: false,
    dppImage: false,
    logistics: false,
    logisticsTemplate: false,
    attribute: false,
    inputCategory: false,
    input: false,
    productAttribute: false,
    product: false,
    productInput: false,
    productInputImage: false,
    productTemplate: false,
    productStep: false,
    step: false,
    stepTemplate: false,
    node: false,
    supplyChainTemplate: false,
    transportType: false,
    user: false,
    dppVideo: false,
})

const toggleSection = (sectionName: keyof typeof sections.value) => {
    sections.value[sectionName] = !sections.value[sectionName]
}

const toggleSubsection = (subsectionName: keyof typeof subsections.value) => {
    subsections.value[subsectionName] = !subsections.value[subsectionName]
}
</script>

<template>
    <div>
        <DashBoard>
            <template #content>
                <div
                    class="add-company"
                    style="padding-bottom: 100px;"
                >
                    <div class="company-header">
                        <h1 class="mb-3">
                            {{ t('documentation.flowcharts.title') }}
                        </h1>
                    </div>

                    <div class="form-add-company">
                        <VRow>
                            <VCol cols="11">
                                <VForm>
                                    <!-- Collection -->
                                    <h2
                                        style="cursor: pointer;"
                                        @click="toggleSection('collection')"
                                    >
                                        {{ t('documentation.flowcharts.collection.title') }}
                                        <span v-if="sections.collection">▲</span>
                                        <span v-else>▼</span>
                                    </h2>

                                    <!-- Content of Collection -->
                                    <div v-show="sections.collection">
                                        <p>
                                            It includes a service for retrieving a list of code lists for Batch, BatchUnit, Country,
                                            Process, and Role, as well as services for adding and updating Process.
                                        </p>

                                        <!-- Batch -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('batch')"
                                        >
                                            {{ t('documentation.flowcharts.collection.batch') }}
                                            <span v-if="subsections.batch">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.batch">
                                            <a
                                                href="/images/documentation/flowcharts/collection/Collection!Batch%20flowchart!BatchFlowchartDiagram_24.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/collection/Collection!Batch%20flowchart!BatchFlowchartDiagram_24.jpg"
                                                    alt="Collection Batch flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Batch Unit -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('batchUnit')"
                                        >
                                            {{ t('documentation.flowcharts.collection.batchUnit') }}
                                            <span v-if="subsections.batchUnit">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.batchUnit">
                                            <a
                                                href="/images/documentation/flowcharts/collection/Collection!Batch%20unit%20flowchart!BatchUnitFlowchartDiagram_21.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/collection/Collection!Batch%20unit%20flowchart!BatchUnitFlowchartDiagram_21.jpg"
                                                    alt="Collection Batch unit flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Country -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('country')"
                                        >
                                            {{ t('documentation.flowcharts.collection.country') }}
                                            <span v-if="subsections.country">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.country">
                                            <a
                                                href="/images/documentation/flowcharts/collection/Collection!Country%20flowchart!CountryFlowchartDiagram_22.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/collection/Collection!Country%20flowchart!CountryFlowchartDiagram_22.jpg"
                                                    alt="Collection Country flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Process -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('process')"
                                        >
                                            {{ t('documentation.flowcharts.collection.process') }}
                                            <span v-if="subsections.process">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.process">
                                            <a
                                                href="/images/documentation/flowcharts/collection/Collection!Process%20flowchart!ProcessFlowchartDiagram_23.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/collection/Collection!Process%20flowchart!ProcessFlowchartDiagram_23.jpg"
                                                    alt="Collection Process flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Role -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('role')"
                                        >
                                            {{ t('documentation.flowcharts.collection.role') }}
                                            <span v-if="subsections.role">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.role">
                                            <a
                                                href="/images/documentation/flowcharts/collection/Collection!Role%20flowchart!RoleFlowchartDiagram_20.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/collection/Collection!Role%20flowchart!RoleFlowchartDiagram_20.jpg"
                                                    alt="Collection Role flowchart"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Company -->
                                    <h2
                                        style="cursor: pointer;"
                                        @click="toggleSection('company')"
                                    >
                                        {{ t('documentation.flowcharts.company.title') }}
                                        <span v-if="sections.company">▲</span>
                                        <span v-else>▼</span>
                                    </h2>

                                    <!-- Content of Company -->
                                    <div v-show="sections.company">
                                        <p>
                                            Contains description of services which are used to manage company and company sites.
                                        </p>

                                        <!-- Company -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('company')"
                                        >
                                            {{ t('documentation.flowcharts.company.company') }}
                                            <span v-if="subsections.company">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.company">
                                            <ul>
                                                <li>Requires authentication</li>
                                                <li>
                                                    To register a company you don’t need to have a role to do so but a admin needs to invite you
                                                </li>
                                                <li>Companies and detail companies</li>
                                                <li>
                                                    Role of manager is required to manage services for existing company such as managing logos, updating company information, deleting -for adding and inviting company admin role is required
                                                </li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/company/Company!Company%20flowchart!CompanyFlowchartDiagram_0.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/company/Company!Company%20flowchart!CompanyFlowchartDiagram_0.jpg"
                                                    alt="Company flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Company site -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('companySite')"
                                        >
                                            {{ t('documentation.flowcharts.company.companySite') }}
                                            <span v-if="subsections.companySite">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.companySite">
                                            <ul>
                                                <li>Requires authentication</li>
                                                <li>
                                                    Contains api endpoints such as adding, editing, deleting or viewing details
                                                </li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/company/Company!Company%20site%20flowchart!CompanySiteFlowchartDiagram_1.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/company/Company!Company%20site%20flowchart!CompanySiteFlowchartDiagram_1.jpg"
                                                    alt="Company site flowchart"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                    <!-- DPP -->
                                    <h2
                                        style="cursor: pointer;"
                                        @click="toggleSection('dpp')"
                                    >
                                        {{ t('documentation.flowcharts.dpp.title') }}
                                        <span v-if="sections.dpp">▲</span>
                                        <span v-else>▼</span>
                                    </h2>

                                    <!-- Content of DPP -->
                                    <div v-show="sections.dpp">
                                        <ul>
                                            <li>Requires authentication</li>
                                            <li>
                                                Contains services for work with dpp and dppConnector, which connect dpp
                                            </li>
                                        </ul>

                                        <!-- DPP connector -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('dppConnector')"
                                        >
                                            {{ t('documentation.flowcharts.dpp.dppConnector') }}
                                            <span v-if="subsections.dppConnector">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.dppConnector">
                                            <ul>
                                                <li>Requires authentication</li>
                                                <li>Contains services for working with dpp and dppConnector</li>
                                                <li>Used for storing information about connecting dpps</li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/dpp/Dpp!Dpp%20connector%20flowchart!DppConnectorFlowchartDiagram_28.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/dpp/Dpp!Dpp%20connector%20flowchart!DppConnectorFlowchartDiagram_28.jpg"
                                                    alt="Dpp connector flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Dpp -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('dpp')"
                                        >
                                            {{ t('documentation.flowcharts.dpp.dpp') }}
                                            <span v-if="subsections.dpp">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.dpp">
                                            <ul>
                                                <li>Requires authentication</li>
                                                <li>
                                                    Includes basic services for working with DPP, as well as additional services for retrieving steps in DPP, getting details by qrId, uploading and deleting cover images, TSA (timestamp) verification, uploading aneleia JSON, uploading steps JSON, and exporting JSON
                                                </li>
                                                <li>
                                                    Stores information about the used SupplyChainTemplate, productInputs, productSteps, QR code, references to sourceDppConnectors, reference to targetDppConnector, products, and more
                                                </li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/dpp/Dpp!Dpp%20flowchart!DppFlowchartDiagram_27.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/dpp/Dpp!Dpp%20flowchart!DppFlowchartDiagram_27.jpg"
                                                    alt="Dpp flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Dpp JSON enaleia -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('dppJsonEnaleia')"
                                        >
                                            {{ t('documentation.flowcharts.dpp.dppJsonEnaleia') }}
                                            <span v-if="subsections.dppJsonEnaleia">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.dppJsonEnaleia">
                                            <a
                                                href="/images/documentation/flowcharts/dpp/Dpp!Dpp%20JSON%20enaleia%20flowchart!DppJsonEnaleiaFlowchartDiagram_29.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/dpp/Dpp!Dpp%20JSON%20enaleia%20flowchart!DppJsonEnaleiaFlowchartDiagram_29.jpg"
                                                    alt="Dpp JSON enaleia flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Dpp JSON steps -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('dppJsonSteps')"
                                        >
                                            {{ t('documentation.flowcharts.dpp.dppJsonSteps') }}
                                            <span v-if="subsections.dppJsonSteps">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.dppJsonSteps">
                                            <a
                                                href="/images/documentation/flowcharts/dpp/Dpp!Dpp%20JSON%20steps%20flowchart!DppJsonStepsFlowchartDiagram_30.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/dpp/Dpp!Dpp%20JSON%20steps%20flowchart!DppJsonStepsFlowchartDiagram_30.jpg"
                                                    alt="Dpp JSON steps flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Dpp export JSON -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('dppJsonExport')"
                                        >
                                            {{ t('documentation.flowcharts.dpp.dppJsonExport') }}
                                            <span v-if="subsections.dppJsonExport">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.dppJsonExport">
                                            <a
                                                href="/images/documentation/flowcharts/dpp/Dpp!JSON%20export%20dpp%20flowchart!JsonExportDppFlowchartDiagram_31.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/dpp/Dpp!JSON%20export%20dpp%20flowchart!JsonExportDppFlowchartDiagram_31.jpg"
                                                    alt="Dpp export JSON flowchart"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Image Collection -->
                                    <h2
                                        style="cursor: pointer;"
                                        @click="toggleSection('imageCollection')"
                                    >
                                        {{ t('documentation.flowcharts.imageCollection.title') }}
                                        <span v-if="sections.imageCollection">▲</span>
                                        <span v-else>▼</span>
                                    </h2>

                                    <!-- Content of Image Collection -->
                                    <div v-show="sections.imageCollection">
                                        <ul>
                                            <li>Requires authentication</li>
                                            <li>Contains the options to manage DPP and company sites images</li>
                                        </ul>

                                        <!-- Company site image -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('companySiteImage')"
                                        >
                                            {{ t('documentation.flowcharts.imageCollection.companySiteImage') }}
                                            <span v-if="subsections.companySiteImage">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.companySiteImage">
                                            <a
                                                href="/images/documentation/flowcharts/imageCollection/ImageCollection!Company%20site%20image%20flowchart!CompanySiteImageFlowchartDiagram_3.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/imageCollection/ImageCollection!Company%20site%20image%20flowchart!CompanySiteImageFlowchartDiagram_3.jpg"
                                                    alt="Company site image flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Dpp image -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('dppImage')"
                                        >
                                            {{ t('documentation.flowcharts.imageCollection.dppImage') }}
                                            <span v-if="subsections.dppImage">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.dppImage">
                                            <a
                                                href="/images/documentation/flowcharts/imageCollection/ImageCollection!DPP%20image%20flowchart!DppImageFlowchartDiagram_4.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/imageCollection/ImageCollection!DPP%20image%20flowchart!DppImageFlowchartDiagram_4.jpg"
                                                    alt="Dpp image flowchart"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Logistics -->
                                    <h2
                                        style="cursor: pointer;"
                                        @click="toggleSection('logistics')"
                                    >
                                        {{ t('documentation.flowcharts.logistics.title') }}
                                        <span v-if="sections.logistics">▲</span>
                                        <span v-else>▼</span>
                                    </h2>

                                    <!-- Content of Logistics -->
                                    <div v-show="sections.logistics">
                                        <ul>
                                            <li>Requires authentication</li>
                                        </ul>

                                        <!-- Logistics -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('logistics')"
                                        >
                                            {{ t('documentation.flowcharts.logistics.logistics') }}
                                            <span v-if="subsections.logistics">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.logistics">
                                            <ul>
                                                <li>Is made up from logistics templates and can be used in DPPs.</li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/logistics/Logistics!Logistics%20flowchart!LogisticsFlowchartDiagram_15.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/logistics/Logistics!Logistics%20flowchart!LogisticsFlowchartDiagram_15.jpg"
                                                    alt="Logistics flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Logistics Template -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('logisticsTemplate')"
                                        >
                                            {{ t('documentation.flowcharts.logistics.logisticsTemplate') }}
                                            <span v-if="subsections.logisticsTemplate">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.logisticsTemplate">
                                            <ul>
                                                <li>
                                                    Contains the options to create, edit, view or delete logistics templates.
                                                </li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/logistics/Logistics!Logistics%20template%20flowchart!LogisticsTemplateFlowchartDiagram_14.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/logistics/Logistics!Logistics%20template%20flowchart!LogisticsTemplateFlowchartDiagram_14.jpg"
                                                    alt="Logistics template flowchart"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Product -->
                                    <h2
                                        style="cursor: pointer;"
                                        @click="toggleSection('product')"
                                    >
                                        {{ t('documentation.flowcharts.product.title') }}
                                        <span v-if="sections.product">▲</span>
                                        <span v-else>▼</span>
                                    </h2>

                                    <!-- Content of Product -->
                                    <div v-show="sections.product">
                                        <ul>
                                            <li>Uses product templates</li>
                                            <li>
                                                To create a product template, attributes, inputs, and productSteps are used to define it, and these are used when creating a product
                                            </li>
                                            <li>
                                                Anyone authenticated can get product details, product templates, and a list of companies that are using those product templates
                                            </li>
                                            <li>
                                                Only users with the Company Manager role can create, edit, delete, upload, or delete photos of a product template
                                            </li>
                                        </ul>

                                        <!-- Attribute -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('attribute')"
                                        >
                                            {{ t('documentation.flowcharts.product.attribute') }}
                                            <span v-if="subsections.attribute">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.attribute">
                                            <a
                                                href="/images/documentation/flowcharts/product/Product!Attribute%20flowchart!AttributeFlowchartDiagram_6.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/product/Product!Attribute%20flowchart!AttributeFlowchartDiagram_6.jpg"
                                                    alt="Attribute flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Input Category -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('inputCategory')"
                                        >
                                            {{ t('documentation.flowcharts.product.inputCategory') }}
                                            <span v-if="subsections.inputCategory">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.inputCategory">
                                            <a
                                                href="/images/documentation/flowcharts/product/Product!Input%20category%20flowchart!InputCategoryFlowchartDiagram_9.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/product/Product!Input%20category%20flowchart!InputCategoryFlowchartDiagram_9.jpg"
                                                    alt="Logistics template flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Input -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('input')"
                                        >
                                            {{ t('documentation.flowcharts.product.input') }}
                                            <span v-if="subsections.input">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.input">
                                            <a
                                                href="/images/documentation/flowcharts/product/Product!Input%20flowchart!InputFlowchartDiagram_8.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/product/Product!Input%20flowchart!InputFlowchartDiagram_8.jpg"
                                                    alt="Input flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Product Attribute -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('productAttribute')"
                                        >
                                            {{ t('documentation.flowcharts.product.productAttribute') }}
                                            <span v-if="subsections.productAttribute">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.productAttribute">
                                            <a
                                                href="/images/documentation/flowcharts/product/Product!Product%20attribute%20flowchart!ProductAttributeFlowchartDiagram_7.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/product/Product!Product%20attribute%20flowchart!ProductAttributeFlowchartDiagram_7.jpg"
                                                    alt="Product attribute flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Product -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('product')"
                                        >
                                            {{ t('documentation.flowcharts.product.product') }}
                                            <span v-if="subsections.product">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.product">
                                            <ul>
                                                <li>Requires authentication</li>
                                                <li>
                                                    Includes basic services for working with the product, such as getting details, collection, adding, updating, deleting a product, and uploading or deleting a product image
                                                </li>
                                                <li>
                                                    Contains information from the ProductTemplate extended with additional product specific details
                                                </li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/product/Product!Product%20flowchart!ProductFlowchartDiagram_13.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/product/Product!Product%20flowchart!ProductFlowchartDiagram_13.jpg"
                                                    alt="Product flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Product Input -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('productInput')"
                                        >
                                            {{ t('documentation.flowcharts.product.productInput') }}
                                            <span v-if="subsections.productInput">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.productInput">
                                            <a
                                                href="/images/documentation/flowcharts/product/Product!Product%20input%20flowchart!ProductinputFlowchartDiagram_10.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/product/Product!Product%20input%20flowchart!ProductinputFlowchartDiagram_10.jpg"
                                                    alt="Product input flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Product Input Image -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('productInputImage')"
                                        >
                                            {{ t('documentation.flowcharts.product.productInputImage') }}
                                            <span v-if="subsections.productInputImage">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.productInputImage">
                                            <a
                                                href="/images/documentation/flowcharts/product/Product!Product%20input%20image%20flowchart!ProductInputImageFlowchartDiagram_12.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/product/Product!Product%20input%20image%20flowchart!ProductInputImageFlowchartDiagram_12.jpg"
                                                    alt="Product input image flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Product Template -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('productTemplate')"
                                        >
                                            {{ t('documentation.flowcharts.product.productTemplate') }}
                                            <span v-if="subsections.productTemplate">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.productTemplate">
                                            <ul>
                                                <li>
                                                    Contains information about the product and basic services for working with ProductTemplate, such as getting details, listing, and retrieving companies, where authentication is required
                                                </li>
                                                <li>
                                                    Additional services like adding, updating, deleting a product template, and uploading or deleting a product template image require authentication and the Company Manager role
                                                </li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/product/Product!Product%20template%20flowchart!ProductTemplateFlowchartDiagram_11.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/product/Product!Product%20template%20flowchart!ProductTemplateFlowchartDiagram_11.jpg"
                                                    alt="Product template flowchart"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Step -->
                                    <h2
                                        style="cursor: pointer;"
                                        @click="toggleSection('step')"
                                    >
                                        {{ t('documentation.flowcharts.step.title') }}
                                        <span v-if="sections.step">▲</span>
                                        <span v-else>▼</span>
                                    </h2>

                                    <!-- Content of Step -->
                                    <div v-show="sections.step">
                                        <ul>
                                            <li>Requires authentication</li>
                                            <li>Contains multiple steps</li>
                                            <li>Basic work with step templates</li>
                                            <li>Managing step, uploading or deleting images for steps</li>
                                            <li>Returns inputs for step</li>
                                        </ul>

                                        <!-- Product Step -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('productStep')"
                                        >
                                            {{ t('documentation.flowcharts.step.productStep') }}
                                            <span v-if="subsections.productStep">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.productStep">
                                            <ul>
                                                <li>
                                                    Basic services for working with ProductStep, such as list, detail, add, update, and delete, as well as upload and delete product step image, and also getConfirm.
                                                </li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/step/Step!Product%20step%20flowchart!ProductStepFlowchartDiagram_17.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/step/Step!Product%20step%20flowchart!ProductStepFlowchartDiagram_17.jpg"
                                                    alt="Product step flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Step -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('step')"
                                        >
                                            {{ t('documentation.flowcharts.step.step') }}
                                            <span v-if="subsections.step">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.step">
                                            <ul>
                                                <li>
                                                    Basic services for working with Step, such as list, detail, add, update, and delete, as well as upload and delete step image, and also get inputs by step ID and get by qrId. It refers to the entities StepTemplate, StepPosition (which contains coordinates x and y), Process, ProductTemplate, and Company. Entities that refer to Step are Node, DPP, Input, and product site.
                                                </li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/step/Step!Step%20flowchart!StepFlowchartDiagram_18.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/step/Step!Step%20flowchart!StepFlowchartDiagram_18.jpg"
                                                    alt="Step flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Step Template -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('stepTemplate')"
                                        >
                                            {{ t('documentation.flowcharts.step.stepTemplate') }}
                                            <span v-if="subsections.stepTemplate">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.stepTemplate">
                                            <ul>
                                                <li>
                                                    Includes services for basic work with an entity and contains name and steps.
                                                </li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/step/Step!Steps%20template%20flowchart!StepsTemplateFlowchartDiagram_16.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/step/Step!Steps%20template%20flowchart!StepsTemplateFlowchartDiagram_16.jpg"
                                                    alt="Step template flowchart"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Supply Chain -->
                                    <h2
                                        style="cursor: pointer;"
                                        @click="toggleSection('supplyChain')"
                                    >
                                        {{ t('documentation.flowcharts.supplyChain.title') }}
                                        <span v-if="sections.supplyChain">▲</span>
                                        <span v-else>▼</span>
                                    </h2>

                                    <!-- Content of Supply Chain -->
                                    <div v-show="sections.supplyChain">
                                        <ul>
                                            <li>Requires authentication</li>
                                            <li>Contains nodes and supply chain templates</li>
                                            <li>
                                                Nodes include information such as fromNodeLogistics, toNodeLogistics, productTemplates, as well as details about their ancestors and descendants
                                            </li>
                                            <li>
                                                SupplyChainTemplate contains information about which nodeTemplates and productTemplates it is composed of, along with details about each node used
                                            </li>
                                        </ul>

                                        <!-- Node -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('node')"
                                        >
                                            {{ t('documentation.flowcharts.supplyChain.node') }}
                                            <span v-if="subsections.node">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.node">
                                            <ul>
                                                <li>
                                                    Includes basic services for working with nodes, such as get detail, collection, add, update, delete and get logistics from app and get by qrId.
                                                </li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/supplyChain/SupplyChain!Node%20flowchart!NodeFlowchartDiagram_25.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/supplyChain/SupplyChain!Node%20flowchart!NodeFlowchartDiagram_25.jpg"
                                                    alt="Node flowchart"
                                                >
                                            </a>
                                        </div>

                                        <!-- Supply Chain Template -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('supplyChainTemplate')"
                                        >
                                            {{ t('documentation.flowcharts.supplyChain.supplyChainTemplate') }}
                                            <span v-if="subsections.supplyChainTemplate">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.supplyChainTemplate">
                                            <ul>
                                                <li>
                                                    Authentication is required for services to retrieve details and records
                                                </li>
                                                <li>
                                                    For services to add, update, and delete, authentication and the Company Manager role are required
                                                </li>
                                            </ul>
                                            <a
                                                href="/images/documentation/flowcharts/supplyChain/SupplyChain!Supply%20chain%20template%20flowchart!SupplyChainTemplateFlowchartDiagram_26.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/supplyChain/SupplyChain!Supply%20chain%20template%20flowchart!SupplyChainTemplateFlowchartDiagram_26.jpg"
                                                    alt="Supply chain template flowchart"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Transport Type -->
                                    <h2
                                        style="cursor: pointer;"
                                        @click="toggleSection('transportType')"
                                    >
                                        {{ t('documentation.flowcharts.transportType.title') }}
                                        <span v-if="sections.transportType">▲</span>
                                        <span v-else>▼</span>
                                    </h2>

                                    <!-- Content of Transport Type -->
                                    <div v-show="sections.transportType">
                                        <!-- Transport Type -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('transportType')"
                                        >
                                            {{ t('documentation.flowcharts.transportType.transportType') }}
                                            <span v-if="subsections.transportType">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.transportType">
                                            <a
                                                href="/images/documentation/flowcharts/transportType/TransportType!Transport%20type%20flowchart!TransportTypeFlowchartDiagram_19.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/transportType/TransportType!Transport%20type%20flowchart!TransportTypeFlowchartDiagram_19.jpg"
                                                    alt="Transport type flowchart"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                    <!-- User -->
                                    <h2
                                        style="cursor: pointer;"
                                        @click="toggleSection('user')"
                                    >
                                        {{ t('documentation.flowcharts.user.title') }}
                                        <span v-if="sections.user">▲</span>
                                        <span v-else>▼</span>
                                    </h2>

                                    <!-- Content of User -->
                                    <div v-show="sections.user">
                                        <ul>
                                            <li>
                                                Contains, admin registration, first login, checks existing email and the ability to reset password
                                            </li>
                                            <li>Rest api endpoints are available without authentication</li>
                                        </ul>

                                        <!-- User -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('user')"
                                        >
                                            {{ t('documentation.flowcharts.user.user') }}
                                            <span v-if="subsections.user">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.user">
                                            <a
                                                href="/images/documentation/flowcharts/user/User%20flowchart!UserFlowchartDiagram_2.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/user/User%20flowchart!UserFlowchartDiagram_2.jpg"
                                                    alt="User flowchart"
                                                >
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Video Collection -->
                                    <h2
                                        style="cursor: pointer;"
                                        @click="toggleSection('videoCollection')"
                                    >
                                        {{ t('documentation.flowcharts.videoCollection.title') }}
                                        <span v-if="sections.videoCollection">▲</span>
                                        <span v-else>▼</span>
                                    </h2>

                                    <!-- Content of Video Collection -->
                                    <div v-show="sections.videoCollection">
                                        <ul>
                                            <li>Requires authentication</li>
                                            <li>Contains the options to manage DPP videos</li>
                                        </ul>

                                        <!-- Dpp Video -->
                                        <h3
                                            style="cursor: pointer;"
                                            @click="toggleSubsection('dppVideo')"
                                        >
                                            {{ t('documentation.flowcharts.videoCollection.dppVideo') }}
                                            <span v-if="subsections.dppVideo">▲</span>
                                            <span v-else>▼</span>
                                        </h3>
                                        <div v-show="subsections.dppVideo">
                                            <a
                                                href="/images/documentation/flowcharts/videoCollection/VideoCollection!DPP%20Video%20flowchart!DppVideoFlowchartDiagram_5.jpg"
                                                target="_blank"
                                            >
                                                <img
                                                    style="max-width: 100%"
                                                    src="/images/documentation/flowcharts/videoCollection/VideoCollection!DPP%20Video%20flowchart!DppVideoFlowchartDiagram_5.jpg"
                                                    alt="Dpp Video flowchart"
                                                >
                                            </a>
                                        </div>
                                    </div>
                                </VForm>
                            </VCol>
                        </VRow>
                    </div>
                </div>
            </template>
        </DashBoard>
    </div>
</template>
