<script setup lang="ts">
definePageMeta({
    middleware: 'auth',
})
</script>

<template>
    <NuxtLayout>
        <div>Welcome to the dashboard</div>
    </NuxtLayout>
</template>
