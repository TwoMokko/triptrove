<script setup lang="ts">
import { useRoute } from "vue-router"
import { computed } from "vue"
import { layouts } from '@/shared/ui/layout'

const route = useRoute()
const layout = computed(() => layouts[route.meta.layout] || layouts.default)


</script>

<template>
    <component :is="layout">
        <router-view />
    </component>
</template>
