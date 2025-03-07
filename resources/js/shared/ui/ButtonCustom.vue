<script setup lang="ts">
defineProps<{
    text: string,
    className?: string,
    type?: string,
}>()
</script>

<template>
    <button
        class="btn"
        :class="className"
        @click="$emit('handler')"
    >
        {{ text }}
    </button>
</template>
