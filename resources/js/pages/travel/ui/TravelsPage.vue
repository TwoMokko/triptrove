<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router';
import { useTravelsStore } from '@/features/travels/model/store';
import { reactive, ref, watch } from "vue";
import { useUsersStore } from "@/entities/user/model/store";
import TravelTabs from "@/widgets/travel/ui/TravelTabs.vue";
import PersonalTab from "@/pages/travel/ui/tabs/PersonalTab.vue";
import SharedTab from "@/pages/travel/ui/tabs/SharedTab.vue";
import WishlistTab from "@/pages/travel/ui/tabs/WishlistTab.vue";
import PlannedTab from "@/pages/travel/ui/tabs/PlannedTab.vue";

const route = useRoute();
const router = useRouter();
const userStore = useUsersStore();
const travelsStore = useTravelsStore();

const isLoading = ref(true);

enum TravelTab {
  PERSONAL = 'personal',
  SHARED = 'shared',
  WISHLIST = 'wishlist',
  PLANNED = 'planned',
  // ARCHIVED = 'archived'
}
type TabId = TravelTab

const tabs = [
  { id: TravelTab.PERSONAL, label: 'Мои' },
  { id: TravelTab.SHARED, label: 'Совместные' },
  { id: TravelTab.WISHLIST, label: 'Хочу посетить' },
  { id: TravelTab.PLANNED, label: 'Планы' },
  // { id: TravelTab.ARCHIVED, label: 'Архив' }
] as const

const getInitialTab = (): TabId => {
  const queryTab = route.query.tab as TabId

  if (queryTab && queryTab !== TravelTab.PERSONAL && tabs.some(tab => tab.id === queryTab)) {
    return queryTab
  }
  return TravelTab.PERSONAL
}

const activeTab = ref<TabId>(getInitialTab())

const dataLoaded = reactive({
  [TravelTab.PERSONAL]: false,
  [TravelTab.SHARED]: false,
  [TravelTab.WISHLIST]: false,
  [TravelTab.PLANNED]: false
})

const loadTabData = async (tabId: TabId) => {
  if (!userStore.currentUser || dataLoaded[tabId]) return

  isLoading.value = true

  switch (tabId) {
    case TravelTab.PERSONAL:
      await travelsStore.fetchMyTravels(userStore.currentUser.id)
      console.log('personal')
      break
    case TravelTab.SHARED:
      await Promise.all([
        travelsStore.getSharedTravels(userStore.currentUser.id),
        travelsStore.getFriendUsers(userStore.currentUser.id)
      ])
      console.log('shared')
      break
    case TravelTab.WISHLIST:
      // await travelsStore.getWishlistTravels(usersStore.currentUser.id)
      console.log('wish')
      break
    case TravelTab.PLANNED:
      // await travelsStore.getPlannedTravels(usersStore.currentUser.id)
      console.log('planned')
      break
  }

  dataLoaded[tabId] = true
  isLoading.value = false
}

watch(activeTab, (newTab) => {
  if (newTab === TravelTab.PERSONAL) {
    const { tab, ...queryWithoutTab } = route.query

    if (Object.keys(queryWithoutTab).length > 0) {
      router.replace({ query: queryWithoutTab })
    } else {
      router.replace({ query: undefined })
    }
  } else {
    router.replace({ query: { ...route.query, tab: newTab } })
  }

  if (!dataLoaded[newTab]) {
    loadTabData(newTab)
  }
}, { immediate: true })

</script>

<template>
    <div class="px-[4%] md:px-[10%] py-10">
        <TravelTabs v-model:active-tab="activeTab" :tabs="tabs" :is-loading="isLoading">
            <PersonalTab v-show="activeTab === TravelTab.PERSONAL" />
            <SharedTab v-show="activeTab === TravelTab.SHARED" />
            <WishlistTab v-show="activeTab === TravelTab.WISHLIST" />
            <PlannedTab v-show="activeTab === TravelTab.PLANNED" />
        </TravelTabs>
    </div>
</template>
