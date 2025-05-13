<script setup lang="ts">
import { useUsersStore } from "@/etities/user"
import { useTravelsStore } from "@/etities/travel"
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from "vue-router"
import Loader from "@/shared/ui/Loader.vue"
import TravelTabs from "@/widgets/travelTabs/ui/TravelTabs.vue"
import PersonalTab from "@/pages/travels/ui/PersonalTab.vue"
import SharedTab from "@/pages/travels/ui/SharedTab.vue"
import WishlistTab from "@/pages/travels/ui/WishlistTab.vue"
import PlannedTab from "@/pages/travels/ui/PlannedTab.vue"

const usersStore = useUsersStore()
const travelsStore = useTravelsStore()

const tabs = [
    { id: 'personal', label: 'Мои путешествия' },
    { id: 'shared', label: 'Совместные' },
    { id: 'wishlist', label: 'Хочу посетить' },
    { id: 'planned', label: 'Планы' }
    // { id: 'favorites', label: 'Избранное' },
    // { id: 'archived', label: 'Архив' }
] as const

type TabId = typeof tabs[number]['id']
const activeTab = ref<TabId>('personal')

const route = useRoute()
const router = useRouter()

if (route.query.tab && tabs.some(t => t.id === route.query.tab)) {
    activeTab.value = route.query.tab as TabId
}

watch(activeTab, (tab) => {
    router.replace({ query: { ...route.query, tab } })
})

watch(activeTab, (newTab) => {
    if (newTab === 'shared' && !travelsStore.usersFriend.length) {
        travelsStore.getFriendUsers(usersStore.currentUser.id)
    }
})

onMounted(async () => {
    if (usersStore.currentUser) {
        await travelsStore.getTravels(usersStore.currentUser.id)
        await travelsStore.getFriendUsers(usersStore.currentUser.id)
        await travelsStore.getSharedTravels(usersStore.currentUser.id)
    }
})
</script>

<template>
    <Loader v-if="travelsStore.isLoading"/>
    <div v-else class="px-[10%] py-10">
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
    </div>
</template>

