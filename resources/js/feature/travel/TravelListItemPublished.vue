<script setup lang="ts">
import type { travelData } from "@/app/types/types"

const props = defineProps<{
    item: travelData
}>()

</script>

<template>
    <div class="card">
        <div class="grid gap-2 grid-cols-8">
            <div>{{ item.place }}</div>
            <div>{{ item.when }}</div>
            <div>{{ item.amount }}</div>
            <div>{{ item.mode_of_transport }}</div>
            <div>{{ item.accommodation }}</div>
            <div>{{ item.advice }}</div>
            <div>{{ item.entertainment }}</div>
            <div>{{ item.general_impression }}</div>
        </div>
    </div>
</template>

<style scoped>
.card {
    @apply py-10 px-14 bg-[#ffffff15] rounded-2xl shadow-[0_0_20px_rgba(0,0,0,0.06)] mb-2.5;
}
</style>
