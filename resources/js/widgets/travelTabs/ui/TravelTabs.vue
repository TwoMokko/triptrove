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
    <div class="flex mb-6">
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
    <transition
        mode="out-in"
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div :key="activeTab" class="will-change-[opacity]">
            <KeepAlive>
                <slot :name="activeTab" />
            </KeepAlive>
        </div>
    </transition>
</template>
