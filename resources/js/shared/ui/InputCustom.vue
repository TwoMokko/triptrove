<script setup lang="ts">
const model = defineModel('value', { required: true })

withDefaults(defineProps<{
    placeholder?: string,
    type?: string,
    name?: string,
    required?: boolean
}>(), {
    type: 'text',
    required: false
})
</script>

<template>
    <input
        class="focus-visible:outline-none py-3 px-8 rounded-3xl border border-secondary bg-transparent w-full"
        v-model="model"
        :placeholder="placeholder"
        :type="type"
        :name="name"
        :required="required"
    >
</template>
