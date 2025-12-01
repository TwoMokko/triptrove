<script setup lang="ts">
import { useRoute } from "vue-router"
import { computed, onMounted } from "vue"
import Loader from "@/shared/ui/Loader.vue"
import { useTravelsStore } from "@/features/travels/model/store";

const route = useRoute()
const travelId = Number(route.params.id)
const travelsStore = useTravelsStore()

const currentTravel = computed(() => travelsStore.currentViewTravel)

onMounted(async () => {
    // может не срабатывать при обновлении
    await travelsStore.fetchPublishedById(travelId)
    console.log(currentTravel.value)
})
</script>

<template>
    <Loader v-if="!travelsStore.currentViewTravel"/>
    <section v-else class="px-[4%] md:px-[10%] py-10">
        <h1>Место: {{ currentTravel.place }}</h1>
        <p>тест</p>
    </section>
</template>

