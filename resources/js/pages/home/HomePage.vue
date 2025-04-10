<script setup lang="ts">
import { onMounted, ref } from "vue"
import { useTravelsStore } from "@/etities/travel"
import Loader from "@/shared/ui/Loader.vue"
import TravelListItem from "@/shared/ui/travel/TravelListItem.vue";

const travelsStore = useTravelsStore()
const page = ref(1)

onMounted(async () => {
    await travelsStore.getPublishedTravels(page.value)
})
</script>

<template>
    <div class="px-[10%] py-10">
        <Loader v-if="travelsStore.isLoading"/>
        <template v-else>
            <div class="test rounded-3xl mb-4 py-20 text-white">
                <h1 class="text-2xl">ALL PUBLIC TRAVELS</h1>
            </div>
            <div v-for="creator in travelsStore.publishedTravels.data">
                <h3 class="text-xl mb-4">name: {{ creator.name }}, login: {{ creator.login }}</h3>
                <div>
                    <TravelListItem
                        v-for="item in creator.travels"
                        :key="item.id"
                        :item="item"
                    />
                </div>
            </div>
        </template>
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
