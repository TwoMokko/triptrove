<script setup lang="ts">
import { Travel } from "@/shared/types/api";

const props = defineProps<{
    item: Travel
}>()

</script>

<template>
    <div class="card">
        <div class="grid gap-2 md:grid-cols-8">
            <router-link
                :to="{ name: 'travelView', params: { id: item.id } }"
                class="hover:text-primary cursor-pointer"
            >
                {{ item.place }}
            </router-link>
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
    @apply py-2 md:py-10 px-3 md:px-14 bg-[#ffffff15] rounded-2xl shadow-[0_0_20px_rgba(0,0,0,0.06)] mb-2.5;
}
</style>
