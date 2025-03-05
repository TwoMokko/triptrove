<script setup lang="ts">
const model = defineModel('model')
defineProps<{
    value?: string,
    placeholder?: string
    type?: string,
}>()

</script>

<template>
    <input
        class="focus-visible:outline-none py-4 px-8 rounded-3xl border border-secondary bg-transparent"
        v-model="model"
        :value="value"
        :placeholder="placeholder"
        :type="type"
    >
</template>
