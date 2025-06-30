<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue"
import { useTravelsStore } from "@/entities/travel"
import Loader from "@/shared/ui/Loader.vue"
import TravelListItemPublished from "@/feature/travel/TravelListItemPublished.vue"
import { useUsersStore } from "@/entities/user"

const usersStore = useUsersStore()
const travelsStore = useTravelsStore()
const page = ref(1)

const currentUserId = computed(() => usersStore.currentUser?.id)

watch(currentUserId, (newId) => {
    console.log('Current user ID changed:', newId)
}, { immediate: true })

const isYoursTravels = computed(() => {
    const id = currentUserId.value
    return (creatorId: number) => id === creatorId
})

onMounted(async () => {
    await travelsStore.getPublishedTravels(page.value)
})
</script>

<template>
    <Loader v-if="travelsStore.isLoading"/>
    <div v-else class="px-[10%] py-10">
        <div class="test rounded-3xl mb-4 py-20 text-white">
            <h1 class="text-2xl">ALL PUBLIC TRAVELS</h1>
        </div>
        <div v-for="creator in travelsStore.publishedTravels.data" :key="creator.id">
            <div class="flex gap-2 items-end mb-4">
                <h3 class="text-xl">{{ creator.login }} ({{ creator.name }})</h3>
                <div v-if="isYoursTravels(creator.id)" class="text-primary">ваши</div>
            </div>
            <div>
                <TravelListItemPublished
                    v-for="item in creator.travels"
                    :key="item.id"
                    :item="item"
                />
            </div>
        </div>
    </div>
</template>
<style>
.test {
    background: linear-gradient(135deg, #abc8ff 0%, #a1f4ff 100%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
</style>
