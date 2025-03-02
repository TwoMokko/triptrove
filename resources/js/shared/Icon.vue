<template>
    <svg :width="size" :height="size" viewBox="0 0 24 24" fill="currentColor">
        <path :d="iconPath" />
    </svg>
</template>

<script setup lang="ts">
defineProps<{
    iconPath: string;
    size?: number | string;
}>();
</script>
