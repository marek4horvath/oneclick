<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $event, $listen } = useNuxtApp()
const isAddProcessModalOpen = ref(false)
const processStore = useProcessStore()

const form = ref(null)
const valid = ref(false)

const formProcess = ref({
    id: '',
    name: '',
    color: '#fffff',
})

const swatches = ref([
    ['#FF0000', '#AA0000', '#550000'],
    ['#FFFF00', '#AAAA00', '#555500'],
    ['#00FF00', '#00AA00', '#005500'],
    ['#00FFFF', '#00AAAA', '#005555'],
    ['#0000FF', '#0000AA', '#000055'],
])

const typeProcess_ = ref('step')

$listen('openAddProcessModal', (typeProcess: string) => {
    isAddProcessModalOpen.value = true
    typeProcess_.value = typeProcess

    formProcess.value = {
        id: '',
        name: '',
        color: '#fffff',
    }
})

const closeAddProcessModal = () => {
    isAddProcessModalOpen.value = false
}

const handleProcessAddSubmitted = () => {
    $event('handleProcessAddSubmitted')
}

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const createProcess = await processStore.createProcess({
        name: formProcess.value.name,
        color: formProcess.value.color,
        processType: typeProcess_.value || 'step',
    })

    if (!createProcess) {
        return
    }

    handleProcessAddSubmitted()

    isAddProcessModalOpen.value = false
}

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
]

const colorRules = [
    (v: string) => !!v || 'Color is required',
]
</script>

<template>
    <ModalLayout
        :is-open="isAddProcessModalOpen"
        name="add-process-modal"
        :title="$t('process.createNew')"
        button-submit-text="Save"
        class="add-process"
        @modal-close="closeAddProcessModal"
        @submit="submitHandler"
    >
        <template #content>
            <div class="form-wrapper">
                <VForm
                    ref="form"
                    v-model="valid"
                >
                    <VTextField
                        v-model="formProcess.name"
                        :label="$t('process.processName')"
                        variant="outlined"
                        density="compact"
                        type="text"
                        :rules="nameRules"
                    />

                    <div class="d-flex justify-center pa-2">
                        <div class="d-flex justify-center flex-column">
                            <VColorPicker
                                v-model="formProcess.color"
                                :swatches="swatches"
                                class=""
                                mode="hex"
                                show-swatches
                                :rules="colorRules"
                            />
                        </div>
                    </div>
                </VForm>
            </div>
        </template>

        <template #footer>
            <VBtn
                variant="text"
                class="submit-btn"
                @click="submitHandler"
            >
                {{ $t('process.createNew') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style lang="scss">
.add-process.modal-mask {
    .modal-container {
        width: 30vw !important;

        .modal-body {
            height: auto;
            padding-top: 1rem;
            .form-wrapper {
                height: 400px;
                padding-top: 1rem;
                overflow-y: scroll;
            }
        }

        .modal-footer {
            margin-top: 2rem;
            .v-btn {
                padding-inline: 1rem;
                padding-block: 0.5rem;
                display: inline-block;
                border-radius: unset;
                flex: 1;
                transition: 0.5s all;

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
        }
    }
}

.custom-swatch {
  width: 24px; /* Veľkosť swatcha */
  height: 24px;
  border-radius: 50%; /* Kruh */
  border: 2px solid #fff; /* Biela obruba pre lepšiu viditeľnosť */
  cursor: pointer;
}
</style>
