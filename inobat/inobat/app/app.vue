<template>
    <NuxtPage />
</template>

<script setup lang="ts">
const authStore = useAuthStore();
const route = useRoute();

onMounted(() => {
    if (authStore.isLoggedIn) {
        if (route.path === '/login' || route.path === '/') {
            navigateTo('/dashboard')
        }
    } else {
        navigateTo('/login')
    }
});

useHead({
    titleTemplate: `Inobat`,
});
</script>
