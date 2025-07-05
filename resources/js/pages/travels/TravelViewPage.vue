<script setup lang="ts">
import { useRoute } from "vue-router"
import { onMounted, ref } from "vue"
import { useTravelsStore } from "@/entities/travel"
import Loader from "@/shared/ui/Loader.vue"

const route = useRoute()
const travelId = Number(route.params.id)
const travelsStore = useTravelsStore()

onMounted(async () => {
    await travelsStore.getTravel(travelId)
})
</script>

<template>
    <Loader v-if="!travelsStore.currentViewTravel"/>
    <section v-else class="px-[10%] py-10">
        <h1>Место: {{ travelsStore.currentViewTravel.place }}</h1>
        <p>test</p>
    </section>
</template>

