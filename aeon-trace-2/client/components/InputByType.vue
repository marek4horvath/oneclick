<script setup lang="ts">
import VueDatePicker from '@vuepic/vue-datepicker'
import Map from '~/components/Map.vue'
import '@vuepic/vue-datepicker/dist/main.css'
import {
    PhosphorIconClockCounterClockwise,
} from "#components"

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
    name: {
        type: String,
        required: true,
    },
    data: {
        type: Array as () => any[],
        required: false,
        default: () => [],
    },
    input: {
        type: Array as () => any[],
        required: false,
        default: () => [],
    },
    isDisabled: {
        type: Boolean,
        default: false,
        required: false,
    },
    measurementModelValue: {
        type: [String, Number, null],
        default: null,
    },

    updatableInput: {
        type: Boolean,
        default: false,
    },
    showHistory: {
        type: Boolean,
        default: false,
    },
    showUpdatableInput: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(['update:modelValue', 'update:measurementModelValue', 'update:updatableInput', 'update:historyClick'])

const showMarkers = ref(false)
const mapClicked = ref(true)
const zoom = ref<number>(10)
const model = ref(props.data ? props.data : '')
const measurementValue = ref(props.measurementModelValue)
const updatableInput = ref(props.updatableInput || false)
const MEASUREMENT_TYPE = 'MEASUREMENT_TYPE'

const image = ref()

if (props.type === 'image') {
    image.value = props.data
}

if (props.type === 'images') {
    model.value = []
    image.value = props.data
}

if (props.type === 'coordinates') {
    model.value = {
        lat: props.data ? props.data?.lat : '',
        lng: props.data ? props.data?.lng : '',
    }
    showMarkers.value = false
    if (props.data?.lat && props.data?.lng) {
        setTimeout(() => {
            showMarkers.value = true
        }, 10)
    }
}

if (props.type === 'checkboxList') {
    model.value = props.data ? props.data : []
}

if (props.type === 'radioList') {
    model.value = props.data ? props.data : ''
}

if (props.input?.measurementType) {
    model.value = MEASUREMENT_TYPE

    emit('update:modelValue', model.value)
}

watch(() => model.value, () => {
    emit('update:modelValue', model.value)
})

watch(measurementValue, val => {
    emit('update:measurementModelValue', val)
})

watch(updatableInput, val => {
    emit('update:updatableInput', val)
})

const imageChanged = (imageData: string | string[]) => {
    model.value = imageData
}

const getCoords = params => {
    model.value.lat = params.lat
    model.value.lng = params.lng
    emit('update:modelValue', model.value)
}

const history = (data: any) => {
    emit('update:historyClick', data.id)
}

const clearFile = () => {
    model.value = ''
}

const addresses = ref<Record<string, any>[]>([{
    lat: 50.8503460,
    lng: 4.3517210,
}])
</script>

<template>
    <template v-if="props.type.toLowerCase() === 'text'">
        <div
            v-if="!props.input.measurementType"
            class="d-flex ga-3"
        >
            <VTextField
                v-model="model"
                :label="props.name"
                variant="outlined"
                required
                :disabled="isDisabled"
            />
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    class="cursor-pointer my-3"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>

        <VCheckbox
            v-if="props.showUpdatableInput"
            v-model="updatableInput"
            :label="$t('updatable')"
            :disabled="isDisabled"
        />
        <div
            v-if="props.input && props.input?.measurementType"
            class="d-flex ga-3"
        >
            <VTextField
                v-model="measurementValue"
                :label="`${props.name} (${props.input.unitMeasurement})`"
                variant="outlined"
                type="number"
                required
                :disabled="isDisabled"
            >
                <template #append-inner>
                    {{ props.input.unitSymbol }}
                </template>
            </VTextField>

            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    class="cursor-pointer my-3"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>
    </template>

    <template v-else-if="props.type.toLowerCase() === 'textarea' || props.type.toLowerCase() === 'text area'">
        <div
            v-if="!props.input.measurementType"
            class="d-flex align-center ga-3"
        >
            <VTextarea
                v-model="model"
                :label="props.name"
                variant="outlined"
                required
                :disabled="isDisabled"
            />
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    class="cursor-pointer"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>

        <VCheckbox
            v-if="props.showUpdatableInput"
            v-model="updatableInput"
            :label="$t('updatable')"
            :disabled="isDisabled"
        />

        <div
            v-if="props.input && props.input?.measurementType"
            class="d-flex ga-3"
        >
            <VTextField
                v-model="measurementValue"
                :label="`${props.name} (${props.input.unitMeasurement})`"
                variant="outlined"
                type="number"
                required
                :disabled="isDisabled"
            >
                <template #append-inner>
                    {{ props.input.unitSymbol }}
                </template>
            </VTextField>
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    class="cursor-pointer my-3"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>
    </template>

    <template v-else-if="props.type.toLowerCase() === 'file'">
        <div
            v-if="!props.input.measurementType"
            class="d-flex align-baseline ga-3"
        >
            <VFileInput
                v-model="model"
                :label="props.name"
                variant="outlined"
                required
                :disabled="isDisabled"
            />
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    class="cursor-pointer"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>

        <div
            v-if="model && model !== MEASUREMENT_TYPE"
            class="d-flex align-center ga-3"
        >
            <span>
                {{ typeof model !== 'string' ? model.name : model }}
            </span>

            <PhosphorIconXCircle
                :size="24"
                color="#888888"
                class="cursor-pointer"
                @click="clearFile"
            />
        </div>

        <VCheckbox
            v-if="props.showUpdatableInput"
            v-model="updatableInput"
            :label="$t('updatable')"
            :disabled="isDisabled"
        />

        <div
            v-if="props.input && props.input?.measurementType"
            class="d-flex ga-3"
        >
            <VTextField
                v-model="measurementValue"
                :label="`${props.name} (${props.input.unitMeasurement})`"
                variant="outlined"
                type="number"
                required
                :disabled="isDisabled"
            >
                <template #append-inner>
                    {{ props.input.unitSymbol }}
                </template>
            </VTextField>
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    class="cursor-pointer my-3"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>
    </template>

    <template v-else-if="props.type.toLowerCase() === 'numerical'">
        <div
            v-if="!props.input.measurementType"
            class="d-flex align-center ga-3"
        >
            <VTextField
                v-model="model"
                :label="props.name"
                variant="outlined"
                type="number"
                required
                :disabled="isDisabled"
            />
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    class="cursor-pointer mb-5"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>

        <VCheckbox
            v-if="props.showUpdatableInput"
            v-model="updatableInput"
            :label="$t('updatable')"
            :disabled="isDisabled"
        />

        <div
            v-if="props.input && props.input?.measurementType"
            class="d-flex ga-3"
        >
            <VTextField
                v-model="measurementValue"
                :label="`${props.name} (${props.input.unitMeasurement})`"
                variant="outlined"
                type="number"
                required
                :disabled="isDisabled"
            >
                <template #append-inner>
                    {{ props.input.unitSymbol }}
                </template>
            </VTextField>

            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    class="cursor-pointer my-3"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>
    </template>

    <template v-else-if="props.type.toLowerCase() === 'coordinates'">
        <VRow>
            <VCol
                v-if="!props.input.measurementType"
                cols="5"
            >
                <VTextField
                    v-model="model.lat"
                    :label="`${props.name} (lat)`"
                    variant="outlined"
                    required
                    readonly
                    :disabled="isDisabled"
                />
            </VCol>
            <VCol
                v-if="!props.input.measurementType"
                cols="5"
            >
                <VTextField
                    v-model="model.lng"
                    :label="`${props.name} (lng)`"
                    variant="outlined"
                    required
                    readonly
                    :disabled="isDisabled"
                />
            </VCol>
            <VCol
                v-if="!props.input.measurementType"
                cols="2"
                class="my-4"
            >
                <span
                    v-if="props.showHistory"
                    :title="$t('actionsTablesTitle.history')"
                >
                    <PhosphorIconClockCounterClockwise
                        class="cursor-pointer"
                        :size="24"
                        color="#888888"
                        @click="history(props.input)"
                    />
                </span>
            </VCol>
            <VCheckbox
                v-if="props.showUpdatableInput"
                v-model="updatableInput"
                :label="$t('updatable')"
                :disabled="isDisabled"
            />

            <VCol
                v-if="props.input && props.input?.measurementType"
                cols="10"
            >
                <VTextField
                    v-model="measurementValue"
                    :label="`${props.name} (${props.input.unitMeasurement})`"
                    variant="outlined"
                    type="number"
                    required
                    :disabled="isDisabled"
                >
                    <template #append-inner>
                        {{ props.input.unitSymbol }}
                    </template>
                </VTextField>
            </VCol>

            <VCol
                v-if="props.input && props.input?.measurementType"
                cols="2"
                class="my-4"
            >
                <span
                    v-if="props.showHistory"
                    :title="$t('actionsTablesTitle.history')"
                >
                    <PhosphorIconClockCounterClockwise
                        class="cursor-pointer"
                        :size="24"
                        color="#888888"
                        @click="history(props.input)"
                    />
                </span>
            </VCol>
        </VRow>

        <div
            v-if="!props.input.measurementType"
            class="map"
        >
            <Map
                v-if="!isDisabled"
                :address-groups="[{ addresses, color: 'blue', connectLine: false }]"
                :is-marker-clicked="mapClicked"
                :show-markers="showMarkers"
                :zoom="zoom"
                @point-clicked="getCoords"
            />
            <Map
                v-else
                :address-groups="[{
                    addresses: [{
                        lat: model.lat,
                        lng: model.lng,
                    }],
                    color: 'blue',
                    connectLine: false,
                }]
                "
                :is-marker-clicked="false"
                show-markers
                :zoom="zoom"
                :is-active-map="false"
            />
        </div>
    </template>

    <template v-else-if="props.type.toLowerCase() === 'datetime'">
        <div
            v-if="!props.input.measurementType"
            class="d-flex align-center ga-3"
        >
            <VueDatePicker
                v-model="model"
                :label="props.name"
                format="yyyy-MM-dd HH:mm"
                :disabled="isDisabled"
                :placeholder="props.name"
            >
                <template #menu-header>
                    <div class="text-center py-2">
                        {{ props.name }}
                    </div>
                </template>
            </VueDatePicker>
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    class="cursor-pointer"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>

        <VCheckbox
            v-if="props.showUpdatableInput"
            v-model="updatableInput"
            :label="$t('updatable')"
            :disabled="isDisabled"
        />

        <div
            v-if="props.input && props.input?.measurementType"
            class="d-flex ga-3"
        >
            <VTextField
                v-model="measurementValue"
                :label="`${props.name} (${props.input.unitMeasurement})`"
                variant="outlined"
                type="number"
                required
                :disabled="isDisabled"
            >
                <template #append-inner>
                    {{ props.input.unitSymbol }}
                </template>
            </VTextField>
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    v-if="props.showHistory"
                    class="cursor-pointer my-3"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>
    </template>

    <template v-else-if="props.type.toLowerCase() === 'datetimerange'">
        <div
            v-if="!props.input.measurementType"
            class="d-flex align-center ga-3"
        >
            <VueDatePicker
                v-model="model"
                :label="props.name"
                format="yyyy-MM-dd HH:mm"
                :disabled="isDisabled"
                :placeholder="props.name"
                range
            >
                <template #menu-header>
                    <div class="text-center py-2">
                        {{ props.name }}
                    </div>
                </template>
            </VueDatePicker>
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    v-if="props.showHistory"
                    class="cursor-pointer"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>

        <VCheckbox
            v-if="props.showUpdatableInput"
            v-model="updatableInput"
            :label="$t('updatable')"
            :disabled="isDisabled"
        />

        <div
            v-if="props.input && props.input?.measurementType"
            class="d-flex ga-3"
        >
            <VTextField
                v-model="measurementValue"
                :label="`${props.name} (${props.input.unitMeasurement})`"
                variant="outlined"
                type="number"
                required
                :disabled="isDisabled"
            >
                <template #append-inner>
                    {{ props.input.unitSymbol }}
                </template>
            </VTextField>
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    v-if="props.showHistory"
                    class="cursor-pointer my-3"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>
    </template>

    <template v-else-if="props.type.toLowerCase() === 'image' || props.type.toLowerCase() === 'images'">
        <ImageUploadComponent
            v-if="!props.input.measurementType"
            :image="image || undefined"
            :single="props.type === 'image'"
            :is-disabled="isDisabled"
            @image-changed="imageChanged"
        >
            <template #history-btn>
                <span
                    v-if="props.showHistory"
                    :title="$t('actionsTablesTitle.history')"
                >
                    <PhosphorIconClockCounterClockwise
                        :size="24"
                        class="my-4 cursor-pointer"
                        color="#888888"
                        @click="history(props.input)"
                    />
                </span>
            </template>
        </ImageUploadComponent>

        <VCheckbox
            v-if="props.showUpdatableInput"
            v-model="updatableInput"
            :label="$t('updatable')"
            :disabled="isDisabled"
        />

        <div
            v-if="props.input && props.input?.measurementType"
            class="d-flex ga-3"
        >
            <VTextField
                v-model="measurementValue"
                :label="`${props.name} (${props.input.unitMeasurement})`"
                variant="outlined"
                type="number"
                required
                :disabled="isDisabled"
            >
                <template #append-inner>
                    {{ props.input.unitSymbol }}
                </template>
            </VTextField>
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    v-if="props.showHistory"
                    class="cursor-pointer my-3"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>
    </template>

    <template v-else-if="props.type.toLowerCase() === 'textlist'">
        <VList
            v-if="!props.input.measurementType"
            disabled
        >
            <VListSubheader>{{ props.name }}</VListSubheader>

            <VListItem
                v-for="(item, i) in input.options"
                :key="i"
                class="mx-5"
            >
                <VListItemTitle>{{ item }}</VListItemTitle>
            </VListItem>
        </VList>

        <VCheckbox
            v-if="props.showUpdatableInput && props.input.measurementType"
            v-model="updatableInput"
            :label="$t('updatable')"
            :disabled="isDisabled"
        />

        <div
            v-if="props.input.measurementType"
            class="d-flex ga-3"
        >
            <VTextField
                v-if="props.input && props.input?.measurementType"
                v-model="measurementValue"
                :label="`${props.name} (${props.input.unitMeasurement})`"
                variant="outlined"
                type="number"
                required
                :disabled="isDisabled"
            >
                <template #append-inner>
                    {{ props.input.unitSymbol }}
                </template>
            </VTextField>
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    v-if="props.showHistory"
                    class="cursor-pointer my-3"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>
    </template>

    <template v-else-if="props.type.toLowerCase() === 'radiolist'">
        <VRow
            v-if="!props.input.measurementType"
            class="align-center ga-3"
        >
            {{ props.name }}
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    :size="24"
                    class="my-4 cursor-pointer"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </VRow>

        <VRadioGroup
            v-if="!props.input.measurementType"
            v-model="model"
        >
            <VRadio
                v-for="(option, i) in input.options"
                :key="i"
                :label="option"
                :value="option"
                :disabled="isDisabled"
            />
        </VRadioGroup>

        <VCheckbox
            v-if="props.showUpdatableInput"
            v-model="updatableInput"
            :label="$t('updatable')"
            :disabled="isDisabled"
        />

        <div
            v-if="props.input && props.input?.measurementType"
            class="d-flex ga-3"
        >
            <VTextField
                v-model="measurementValue"
                :label="`${props.name} (${props.input.unitMeasurement})`"
                variant="outlined"
                type="number"
                required
                :disabled="isDisabled"
            >
                <template #append-inner>
                    {{ props.input.unitSymbol }}
                </template>
            </VTextField>
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    class="cursor-pointer my-3"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>
    </template>

    <template v-else-if="props.type.toLowerCase() === 'checkboxlist'">
        <VRow
            v-if="!props.input.measurementType"
            class="align-center ga-3"
        >
            {{ props.name }}
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    :size="24"
                    class="my-4 cursor-pointer"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </VRow>
        <div v-if="!props.input.measurementType">
            <VCheckbox
                v-for="(option, i) in input.options"
                :key="i"
                v-model="model"
                :label="option"
                :value="option"
                :disabled="isDisabled"
            />
        </div>

        <VCheckbox
            v-if="props.showUpdatableInput"
            v-model="updatableInput"
            :label="$t('updatable')"
            :disabled="isDisabled"
        />

        <div
            v-if="props.input && props.input?.measurementType"
            class="d-flex ga-3"
        >
            <VTextField
                v-model="measurementValue"
                :label="`${props.name} (${props.input.unitMeasurement})`"
                variant="outlined"
                type="number"
                required
                :disabled="isDisabled"
            >
                <template #append-inner>
                    {{ props.input.unitSymbol }}
                </template>
            </VTextField>
            <span
                v-if="props.showHistory"
                :title="$t('actionsTablesTitle.history')"
            >
                <PhosphorIconClockCounterClockwise
                    class="cursor-pointer my-3"
                    :size="24"
                    color="#888888"
                    @click="history(props.input)"
                />
            </span>
        </div>
    </template>
</template>

<style lang="scss">
.dp__input {
    height: 56px;
    border-radius: 0;
    border-color: #9d9d9d;

    &:focus {
        border-color: #000000;
    }

    &::placeholder {
        color: rgba(0, 0, 0, 0.9) !important;
    }
}
</style>
