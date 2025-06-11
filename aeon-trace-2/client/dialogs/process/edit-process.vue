<script setup lang="ts">
import ModalLayout from '@/dialogs/modalLayout.vue'

const { $event, $listen } = useNuxtApp()

const isEditProcessModalOpen = ref(false)
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
],
)

$listen('openEditProcessModal', (process: any) => {
    isEditProcessModalOpen.value = true

    formProcess.value = {
        id: process.id,
        name: process.name,
        color: process.color,
    }
})

const closeEditProcessModal = () => {
    isEditProcessModalOpen.value = false
}

const handleProcessSubmitted = () => {
    $event('handleProcessSubmitted')
}

const handleProcessEditSubmitted = () => {
    $event('handleProcessEditSubmitted')
}

const submitHandler = async () => {
    const formValidation: any = form.value

    formValidation.validate()

    if (!valid.value) {
        return
    }

    const editProcess = await processStore.updateProcess(formProcess.value.id, {
        name: formProcess.value.name,
        color: formProcess.value.color,

    })

    if (!editProcess) {
        return
    }
    handleProcessSubmitted()
    handleProcessEditSubmitted()
    isEditProcessModalOpen.value = false
}
</script>

<template>
    <ModalLayout
        :is-open="isEditProcessModalOpen"
        name="edit-process-modal"
        :title="$t('process.editProcess')"
        button-submit-text="Save"
        class="edit-process"
        @modal-close="closeEditProcessModal"
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
                    />

                    <div class="d-flex justify-center pa-2">
                        <div class="d-flex justify-center flex-column">
                            <VColorPicker
                                v-model="formProcess.color"
                                :swatches="swatches"
                                class=""
                                mode="hex"
                                show-swatches
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
                {{ $t('process.edit') }}
            </VBtn>
        </template>
    </ModalLayout>
</template>

<style lang="scss">
.edit-process.modal-mask {
    .modal-container {
        width: 30vw !important;

        .modal-body {
            height: auto;
            padding-top: 1rem;
            .form-wrapper {
                height: 500px;
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
