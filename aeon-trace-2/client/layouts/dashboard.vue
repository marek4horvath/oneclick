<script setup lang="ts">
import ContextMenu from '@imengyu/vue3-context-menu'
import Profile from '@/dialogs/profile.vue'
import { displayTitle, getPageBreadcrumbs, getPageHeader } from '@/utils/meta'
import {
    PhosphorIconBuilding,
    PhosphorIconBuildings,
    PhosphorIconCube,
    PhosphorIconHouseLine,
    PhosphorIconLink,
    PhosphorIconLockSimple,
    PhosphorIconTruckTrailer,
    PhosphorIconWaveform,
} from '#components'

defineProps({
    hasBackButton: {
        type: Boolean,
        default: false,
        required: false,
    },
})

const { hasAtLeastRole } = useRoleAccess()

const route = useRoute()
const router = useRouter()

const routeLinks = shallowRef([
    {
        title: 'dashboard',
        to: '/dashboard',
        icon: PhosphorIconHouseLine,
        isActive: false,
    },
    {
        title: 'dpps',
        to: '/dpps',
        icon: PhosphorIconWaveform,
        isActive: false,
    },
    {
        title: 'supplyChain',
        to: '/supply-chains',
        icon: PhosphorIconLink,
        activePath: ['/supply-chains/detail:id(.*)*'],
        isActive: false,
    },
    {
        title: 'companies',
        icon: PhosphorIconBuildings,
        isExpanded: false,
        activePath: ['/companies', '/companies/add-company', '/logistics-companies', '/logistics-companies/add-logistics-company'],
        isActive: false,
        children: [
            {
                title: 'mainCompanies',
                to: '/companies',
                icon: PhosphorIconBuilding,
                activePath: ['/companies', '/companies/add-company'],
            },
            {
                title: 'logisticsCompanies',
                to: '/logistics-companies',
                icon: PhosphorIconTruckTrailer,
                activePath: ['/logistics-companies', '/logistics-companies/add-logistics-company'],
            },
        ],
    },
    {
        title: 'products',
        to: '/products',
        icon: PhosphorIconCube,
        activePath: ['/products/detail:id(.*)*'],
        isActive: false,
        requiredRole: Roles.ADMIN,
    },
    {
        title: 'dataSharing',
        to: '/data-sharing-policy',
        icon: PhosphorIconLockSimple,
        activePath: ['/data-sharing-policy/detail:id(.*)*'],
        isActive: false,
    },
])

const filteredRouteLinks = computed(() => {
    return routeLinks.value.filter(link => {
        const showParent = !link.requiredRole || hasAtLeastRole(link.requiredRole)

        if (link.children) {
            link.children = link.children.filter(child =>
                !child.requiredRole || hasAtLeastRole(child.requiredRole),
            )

            return link.children.length > 0 && showParent
        }

        return showParent
    })
})

const updateActiveLink = () => {
    routeLinks.value.forEach((link: any) => {
        link.isActive = false
        link.isExpanded = false

        if (link.children) {
            let anyChildActive = false

            link.children.forEach((child: any) => {
                const childActive = route.path.startsWith(child.to) || (child.activePath && child.activePath.some((path: any) => {
                    const regex = new RegExp(path)

                    return regex.test(route.path)
                }))

                if (childActive) {
                    anyChildActive = true
                    link.isExpanded = true
                    child.isActive = true
                } else {
                    child.isActive = false
                }
            })

            if (anyChildActive) {
                link.isActive = true
            }
        }

        if (!link.children) {
            const isActiveFromActivePath = link.activePath && link.activePath.some((path: any) => {
                const regex = new RegExp(path)

                return regex.test(route.path)
            })

            if (isActiveFromActivePath) {
                link.isActive = true
                link.isExpanded = true
            } else {
                link.isActive = route.path.startsWith(link.to)
            }
        }
    })
    routeLinks.value = [...routeLinks.value]
}

watch(() => route.path, updateActiveLink, { immediate: true })

const getLinkClass = (link: any) => {
    if (link.isActive) {
        return 'active'
    }

    return ''
}

const getIconClass = (link: any) => {
    if (link.title === 'companies' && link.isActive) {
        return 'icon-active'
    }

    return ''
}

const openNotifications = () => {
    console.log('openNotifications')
}

const toggleSubmenu = (link: any) => {
    if (link?.children) {
        link.isExpanded = !link.isExpanded
        routeLinks.value = [...routeLinks.value]
    }
}

const openSettings = () => {
    console.log('openSettings')
}

const toggleTheme = () => {
    console.log('toggleTheme')
}

const openProfileModal = () => {
    // $event('openProfileModal')
}

const logout = () => {
    useAuthStore().logout()
    navigateTo('/login')
}

const openProfileSubmenu = (ev: MouseEvent) => {
    ContextMenu.showContextMenu({
        items: [
            { label: 'Profile', onClick: openProfileModal },
            { label: 'Logout', onClick: logout },
        ],
        x: ev.clientX,
        y: ev.clientY,
    })
}

const goBack = () => {
    router.go(-1)
}
</script>

<template>
    <VLayout class="rounded rounded-md">
        <VNavigationDrawer
            :width="260"
            permanent
            floating
        >
            <VList>
                <VListItem class="mb-12">
                    <VImg
                        src="/assets/images/logo.png"
                        class="mx-auto w-90 my-4 mb-0"
                    />
                </VListItem>

                <VDivider class="mb-4" />

                <template
                    v-for="link in filteredRouteLinks"
                    :key="link.to || link.title"
                >
                    <div
                        v-if="link.children"
                        class="has-children"
                    >
                        <VListItem
                            class="pa-0 text-decoration-none"
                            @click="toggleSubmenu(link)"
                        >
                            <template #prepend>
                                <component
                                    :is="link.icon"
                                    v-if="link.icon"
                                    :size="23"
                                    weight="bold"
                                    :class="getIconClass(link)"
                                />
                            </template>
                            {{ $t(`menu.${link.title}`) }}
                            <PhosphorIconDotsThree
                                class="more"
                                :size="24"
                                weight="bold"
                            />
                        </VListItem>

                        <VList
                            v-if="link.isExpanded"
                            class="nested-menu"
                        >
                            <NuxtLink
                                v-for="child in link.children"
                                :key="child.to"
                                :to="child.to"
                                class="nested-link"
                                :class="getLinkClass(child)"
                            >
                                <VListItem class="pa-0 text-decoration-none">
                                    <template #prepend>
                                        <component
                                            :is="child.icon"
                                            v-if="child.icon"
                                            :size="20"
                                            weight="bold"
                                        />
                                    </template>
                                    <span style="padding-left: 3px">{{ $t(`menu.${child.title}`) }}</span>
                                </VListItem>
                            </NuxtLink>
                        </VList>
                    </div>

                    <NuxtLink
                        v-else
                        :to="link.to"
                        :class="getLinkClass(link)"
                    >
                        <VListItem class="pa-0 text-decoration-none">
                            <template #prepend>
                                <component
                                    :is="link.icon"
                                    v-if="link.icon"
                                    :size="23"
                                    weight="bold"
                                    :class="getIconClass(link)"
                                />
                            </template>
                            {{ $t(`menu.${link.title}`) }}
                        </VListItem>
                    </NuxtLink>
                </template>
            </VList>
        </VNavigationDrawer>

        <VMain>
            <div v-if="hasBackButton">
                <p class="back">
                    <PhosphorIconArrowLeft
                        :size="16"
                        @click="goBack"
                    />
                    <span @click="goBack">
                        {{ $t('back') }}
                    </span>
                </p>
            </div>
            <div class="layout-header">
                <div v-if="displayTitle()">
                    <VBreadcrumbs :items="getPageBreadcrumbs()" />
                    <h1 :class="hasBackButton ? 'header-relative' : ''">
                        {{ getPageHeader() }}
                    </h1>
                </div>

                <VSpacer />

                <VChip>
                    <VTextField
                        density="compact"
                        placeholder="Search..."
                        prepend-inner-icon="ri-search-line"
                        variant="solo"
                    />

                    <PhosphorIconBell
                        :size="32"
                        @click="openNotifications"
                    />
                    <PhosphorIconMoon
                        :size="32"
                        @click="toggleTheme"
                    />
                    <PhosphorIconInfo
                        :size="32"
                        @click="openSettings"
                    />

                    <VAvatar
                        color="surface-variant"
                        @click="openProfileSubmenu"
                    >
                        <VImg
                            src="https://randomuser.me/api/portraits/men/85.jpg"
                            cover
                        />
                    </VAvatar>
                </VChip>
            </div>

            <div class="main-content d-flex align-center justify-center">
                <slot />
            </div>
        </VMain>

        <Profile />
    </VLayout>
</template>

<style lang="scss">
.mx-context-menu-item {
    cursor: pointer;
}

.back {
    position: absolute;
    top: 60px;
}

.layout-header {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 2rem;

    .v-breadcrumbs {
        padding-left: 0;
        padding-bottom: 0.5rem;

        .v-breadcrumbs-item {
            color: #707EAE;
        }

        .v-breadcrumbs-divider {
            color: #64719C;
        }
    }

    .header-relative {
        position: relative;
        top: 40px;
    }

    .v-chip {
        background-color: #fff;
        height: 62px !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0;

        .v-chip__underlay {
            background-color: #fff;
        }

        .v-text-field {
            display: inline-flex;
            background-color: #F4F7FE;
            border-radius: 50px;
            padding: 6px;
            width: 200px;
            height: 40px;

            .v-input__details {
                display: none;
            }

            .v-input__control {
                width: 100%;
            }

            .v-field {
                background-color: #F4F7FE;
                box-shadow: none;
            }

            .v-field__input {
                min-height: 28px;
                height: 28px;
                padding: 0 5px;
            }
        }

        & > * {
            margin: 0 15px;
        }

        svg {
            color: #A3AED0;
            height: 22px;
            cursor: pointer;

            &:hover {
                color: #268FC6;
            }
        }

        .v-avatar {
            cursor: pointer;
            margin-left: 5px;
        }
    }
}

.has-children {
    cursor: pointer;
}

.nested-menu {
    margin-left: 20px;
    border-left: 2px solid #24a69a !important;
    padding-left: 10px;
}

.nested-link {
    font-size: 16px;
    color: #A3AED0;
}

.rotate-180 {
    transform: rotate(180deg);
}

.icon-active {
    color: #24a69a !important;
}
</style>
