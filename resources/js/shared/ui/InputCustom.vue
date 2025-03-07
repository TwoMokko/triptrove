<script setup lang="ts">
const value = defineModel('value', { required: true })
defineProps<{
    placeholder?: string,
    type?: string,
}>()
</script>

<template>
    <input
        class="focus-visible:outline-none py-4 px-8 rounded-3xl border border-secondary bg-transparent"
        v-model="value"
        :placeholder="placeholder"
        :type="type"
    >
</template>
