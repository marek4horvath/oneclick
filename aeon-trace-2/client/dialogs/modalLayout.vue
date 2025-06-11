<script setup lang="ts">
const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    buttonCancelText: {
        type: String,
        default: "Cancel",
    },
    buttonSubmitText: {
        type: String,
        default: "Submit",
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disableCancelButton: {
        type: Boolean,
        default: false,
    },
    noButtons: {
        type: Boolean,
        default: false,
    },
    width: {
        type: String,
        default: '35vw',
    },
    height: {
        type: String,
        default: 'auto',
    },
})

const emit = defineEmits(["modalClose", "submit"])

const target = ref(null)

const closeOnEscape = event => {
    if (event.key === "Escape") {
        emit("modalClose")
    }
}

onMounted(() => {
    window.addEventListener("keydown", closeOnEscape)
})

onUnmounted(() => {
    window.removeEventListener("keydown", closeOnEscape)
})
</script>

<template>
    <div
        v-if="props.isOpen"
        class="modal-mask"
    >
        <div class="modal-wrapper">
            <div
                ref="target"
                class="modal-container"
                :style="{ width: props.width, height: props.height }"
            >
                <div
                    class="close-box top-wrapper"
                    @click="emit('modalClose')"
                >
                    <span class="close">&times;</span>
                </div>

                <div class="modal-header">
                    {{ props.title }}
                </div>

                <div class="modal-description">
                    <slot name="description" />
                </div>
                <div class="modal-body">
                    <slot name="content">
                        default content
                    </slot>
                </div>
                <div
                    v-if="!noButtons"
                    class="modal-footer"
                >
                    <slot name="footer">
                        <VBtn
                            v-if="!disableCancelButton"
                            variant="text"
                            rounded="0"
                            size="large"
                            class="cancel-btn"
                            @click.stop="emit('modalClose')"
                        >
                            {{ props.buttonCancelText }}
                        </VBtn>

                        <VBtn
                            variant="flat"
                            rounded="0"
                            size="large"
                            class="submit-btn"
                            :loading="loading"
                            @click.stop="emit('submit')"
                        >
                            {{ props.buttonSubmitText }}
                        </VBtn>
                    </slot>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.modal-mask {
    position: fixed;
    z-index: 1004;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    overflow-y: auto;

    .modal-container {
        min-width: 300px;
        margin: 150px auto;
        padding: 1.5rem;
        background-color: #FFFFFF;
        border-radius: 10px;
        box-shadow: 0 4px 34px 0 rgba(0, 0, 0, 0.25);
        position: relative;

        .close-box {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            height: 25px;
            width: 25px;
            text-align: center;
            line-height: 25px;
            background-color: rgba(38, 166, 154, 1);
            border-radius: 5px;
            color: #FFFFFF;
            cursor: pointer;

            &:hover {
                background-color: rgba(38, 166, 154, 0.8);
            }
        }

        .modal-header {
           color: rgba(38, 166, 154, 1);
           font-size: 30px;
           text-align: center;
           margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .modal-description {
            font-size: 16px;
            text-align: center;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            align-items: stretch;
            align-content: stretch;
            gap: 1rem;

            .v-btn {
                padding-inline: 1rem;
                padding-block: 0.5rem;
                display: inline-block;
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

                &.cancel-btn {
                    background-color: rgba(38, 166, 154, 0);
                    color: #000000;
                }
            }
        }
    }
}
</style>
