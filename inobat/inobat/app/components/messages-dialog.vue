<template>
    <div
        v-if="activeMessages.length"
        class="message-container"
        :class="[
            `position-${position}`,
        ]"
    >
        <div
            v-for="(message, index) in activeMessages"
            :key="index"
            class="message"
            :class="[
                `animation-${animation}`,
                message.type,
            ]"
            @click="handleClick(message)"
        >
            {{ message.text }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { nextTick, onUnmounted, ref, watch } from 'vue'

interface Message {
    text: string
    type: string
}

const props = defineProps({
    messages: {
        type: Array as () => Message[],
        required: true,
    },
    position: {
        type: String,
        default: 'center', // left, center, right
    },
    duration: {
        type: Number,
        default: 3000, // Time in ms
    },
    animation: {
        type: String,
        default: 'bottom-to-top', // Available: bottom-to-top, top-to-bottom, left-to-right, right-to-left
    },
})

const emit = defineEmits(['removeMessage'])

const activeMessages = ref<Message[]>([])

const removeMessage = (message: Message) => {
    const index = activeMessages.value.indexOf(message)
    if (index !== -1) {
        activeMessages.value.splice(index, 1)
        emit('removeMessage', message)
    }
}

const addMessage = async (message: Message) => {
    activeMessages.value.push(message)
    await nextTick()

    setTimeout(() => {
        removeMessage(message)
    }, props.duration)
}

watch(
    () => props.messages,
    newMessages => {
        if (newMessages.length) {
            newMessages.forEach(msg => {
                addMessage(msg)
            })
        }
    },
    { deep: true },
)

onUnmounted(() => {
    activeMessages.value = []
})

const handleClick = (message: Message) => {
    removeMessage(message)
}
</script>

<style scoped lang="scss">
$message-bg-color: rgb(var(--v-theme-primary));
$message-text-color: white;
$message-padding: 30px;
$message-border-radius: 5px;
$message-opacity: 0.9;
$animation-duration: 0.5s;
$position-top: 10%;
$position-left: 20px;
$position-right: 20px;
$gap: 10px;
$fontWeight: 600;

.message-container {
  position: fixed;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: $gap;
  justify-content: center;
  align-items: center;

  &.position-left {
    left: $position-left;
    top: $position-top;
    transform: translateY(-$position-top);
  }

  &.position-center {
    left: 50%;
    top: $position-top;
    transform: translate(-50%, -$position-top);
  }

  &.position-right {
    right: $position-right;
    top: $position-top;
    transform: translateY(-$position-top);
  }
}

.message {
  background-color: $message-bg-color;
  color: $message-text-color;
  padding: $message-padding;
  border-radius: $message-border-radius;
  opacity: $message-opacity;
  transition: opacity $animation-duration, transform $animation-duration;
  width: 300px;
  font-weight: $fontWeight;
  box-shadow: 0px 1px 4px 0px rgba(76, 78, 100, 0.2196078431);
  cursor: pointer;

  &.animation-bottom-to-top {
    transform: translateY(100%);
    animation: slide-up $animation-duration forwards;
  }

  &.animation-top-to-bottom {
    transform: translateY(-100%);
    animation: slide-down $animation-duration forwards;
  }

  &.animation-left-to-right {
    transform: translateX(-100%);
    animation: slide-right $animation-duration forwards;
  }

  &.animation-right-to-left {
    transform: translateX(100%);
    animation: slide-left $animation-duration forwards;
  }
}

@keyframes slide-up {
  0% {
    opacity: 0;
    transform: translateY(100%);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slide-down {
  0% {
    opacity: 0;
    transform: translateY(-100%);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slide-left {
  0% {
    opacity: 0;
    transform: translateX(100%);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slide-right {
  0% {
    opacity: 0;
    transform: translateX(-100%);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>
