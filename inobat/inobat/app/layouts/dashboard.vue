<template>
    <v-layout class="rounded rounded-md">
        <v-navigation-drawer permanent>
            <v-list>
                <v-list-item class="mb-12">
                    <v-img src="/assets/images/logo.png" class="mx-auto w-50 my-4"></v-img>
                </v-list-item>

                <NuxtLink to="/dashboard">
                    <v-list-item class="pa-0 text-decoration-none" :title="$t('menu.dashboard')"></v-list-item>
                </NuxtLink>
                <NuxtLink to="/dpps">
                    <v-list-item class="pa-0 text-decoration-none" :title="$t('menu.dpps')"></v-list-item>
                </NuxtLink>
                <NuxtLink to="/supply-chains">
                    <v-list-item class="pa-0 text-decoration-none" :title="$t('menu.supplyChain')"></v-list-item>
                </NuxtLink>
                <NuxtLink to="/companies">
                    <v-list-item class="pa-0 text-decoration-none" :title="$t('menu.companies')"></v-list-item>
                </NuxtLink>
                <NuxtLink to="/products">
                    <v-list-item class="pa-0 text-decoration-none" :title="$t('menu.products')"></v-list-item>
                </NuxtLink>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar :title="route?.meta?.title">
            <template v-slot:append>
                <v-spacer></v-spacer>
                <v-menu transition="slide-y-transition">
                    <template v-slot:activator="{ props }">
                        <v-btn color="primary" v-bind="props" icon="mdi-account"></v-btn>
                    </template>
                    <v-list class="pa-3 mt-3">
                        <v-list-item>
                            <v-list-item-title>
                                {{ email }}
                            </v-list-item-title>
                        </v-list-item>
                        <v-list-item class="text-center">
                            <v-list-item-title>
                                 <v-btn  @click="logout()" color="primary">
                                    {{ $t('menu.logout') }}
                                </v-btn>
                            </v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>
            </template>
        </v-app-bar>

        <v-main class="d-flex align-center justify-center" style="min-height: 300px;">
            <slot />
        </v-main>
    </v-layout>
</template>

<script setup lang="ts">
const route = useRoute()

const authStore = useAuthStore()
const email = computed(() => authStore.email)

const logout = () => {
    authStore.logout()
    navigateTo('/login')
};
</script>
