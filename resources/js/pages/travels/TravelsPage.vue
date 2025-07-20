<script setup lang="ts">
import { useUsersStore } from "@/entities/user"
import { useTravelsStore } from "@/entities/travel"
import { ref, watch } from 'vue'
import { useRoute, useRouter } from "vue-router"
import Loader from "@/shared/ui/Loader.vue"
import TravelTabs from "@/widgets/travelTabs/ui/TravelTabs.vue"
import PersonalTab from "@/pages/travels/ui/tabs/PersonalTab.vue"
import SharedTab from "@/pages/travels/ui/tabs/SharedTab.vue"
import WishlistTab from "@/pages/travels/ui/tabs/WishlistTab.vue"
import PlannedTab from "@/pages/travels/ui/tabs/PlannedTab.vue"

const usersStore = useUsersStore()
const travelsStore = useTravelsStore()
const route = useRoute()
const router = useRouter()

const tabs = [
    { id: 'personal', label: 'Мои путешествия' },
    { id: 'shared', label: 'Совместные' },
    { id: 'wishlist', label: 'Хочу посетить' },
    { id: 'planned', label: 'Планы' }
    // { id: 'favorites', label: 'Избранное' },
    // { id: 'archived', label: 'Архив' }
] as const

type TabId = typeof tabs[number]['id']
const activeTab = ref<TabId>(
    (route.query.tab as TabId) || 'personal'
)

const isInitialLoad = ref(false)

/**
 * Загружает основные данные (личные и общие путешествия)
 * @param force - принудительное обновление, игнорируя кеш
 */
const loadMainData = async (force = false) => {
    if (!usersStore.currentUser || (isInitialLoad.value && !force)) return

    isInitialLoad.value = true
    await Promise.all([
        travelsStore.getTravels(usersStore.currentUser.id),
        travelsStore.getSharedTravels(usersStore.currentUser.id)
    ])
}

/**
 * Загружает друзей только при необходимости
 */
const loadFriendsIfNeeded = async () => {
    if (!usersStore.currentUser || activeTab.value !== 'shared') return

    // Обновляем если данных нет или кеш устарел (>5 минут)
    if (!travelsStore.usersFriend.length ||
        Date.now() - travelsStore.lastFriendsUpdate > 300000) {
        await travelsStore.getFriendUsers(usersStore.currentUser.id)
    }
}

// Следим за изменением пользователя
watch(
    () => usersStore.currentUser,
    (user) => user && loadMainData(),
    { immediate: true }
)

// Следим за сменой вкладки
watch(activeTab, (newTab) => {
    router.replace({ query: { ...route.query, tab: newTab } })

    // Загружаем специфичные данные для вкладки
    switch (newTab) {
        case 'shared':
            loadFriendsIfNeeded()
            break
        case 'planned':
            // Дополнительная загрузка для вкладки "Планы"
            break
    }
})


watch(
    () => activeTab.value,
    loadFriendsIfNeeded,
    { immediate: true }
)
</script>

<template>
    <Loader v-if="travelsStore.isLoading"/>
    <section v-else class="px-[10%] py-10">
        <TravelTabs v-model:activeTab="activeTab" :tabs="tabs">
            <template #personal>
                <PersonalTab />
            </template>
            <template #shared>
                <SharedTab />
            </template>
            <template #wishlist>
                <WishlistTab />
            </template>
            <template #planned>
                <PlannedTab />
            </template>
        </TravelTabs>
    </section>
</template>

