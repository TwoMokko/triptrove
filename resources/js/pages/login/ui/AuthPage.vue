<script setup lang="ts">
    const user = null
</script>

<template>
    <div v-if="!user">register</div>
    <div v-else>login</div>
</template>
