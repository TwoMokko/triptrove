<script setup lang="ts">
import Loader from "@/shared/ui/Loader.vue";

const props = defineProps<{
    activeTab: string;
    tabs: Array<{ id: string; label: string }>;
    isLoading: boolean;
}>()

const emit = defineEmits<{
    (e: 'update:activeTab', id: string): void
}>()

const switchTab = (tabId: string) => {
    emit('update:activeTab', tabId)
}
</script>

<template>
    <div class="grid grid-cols-2 md:flex mb-6">
        <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="switchTab(tab.id)"
            class="py-2 px-4 border-b focus:outline-none"
            :class="activeTab === tab.id ? 'border-primary' : 'border-transparent'"
        >
            {{ tab.label }}
        </button>
    </div>

    <Loader v-if="isLoading" />
    <div v-else>
      <slot />
    </div>
</template>
