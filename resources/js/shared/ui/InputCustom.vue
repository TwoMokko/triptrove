<script setup lang="ts">
const value = defineModel('value', { required: true })
defineProps<{
    placeholder?: string,
    type?: string,
    name?: string,
    required?: boolean
}>()
</script>

<template>
    <input
        class="focus-visible:outline-none py-3 px-8 rounded-3xl border border-secondary bg-transparent w-full"
        v-model="value"
        :placeholder="placeholder"
        :type="type"
        @input="$emit('input')"
        :name="name"
        :required="required"
    >
</template>
