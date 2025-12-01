<script setup lang="ts">
const props = defineProps<{
    activeTab: string;
    tabs: Array<{ id: string; label: string }>
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

    <div>
      <slot />
    </div>
</template>
