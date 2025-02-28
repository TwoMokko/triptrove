<script setup>
    import { ref } from "vue"
    const title = ref('Laravel')
</script>

<template>
    <h1
        class="text-blue-800 text-3xl"
        @mouseenter="title = 'Vue'"
        @mouseleave="title = 'Laravel'"
        v-text="title"
    />
</template>
