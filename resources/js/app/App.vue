<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { api } from "@/app/api/api";
import { layouts } from "@/shared/ui/layout";
import { useRoute } from "vue-router";
import ModalContainer from "@/widgets/modalContainer/ModalContainer.vue"

const route = useRoute();

const layout = computed(() => layouts[route.meta.layout] || layouts.default);

onMounted(async () => {
  await api.get('/sanctum/csrf-cookie', {
    baseURL: '', // Временное удаление /api
    withCredentials: true,
  });
});
</script>

<template>
  <component :is="layout">
    <RouterView />
    <ModalContainer />
  </component>
</template>