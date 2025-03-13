<script setup lang="ts">
import { useAuthStore } from "@/etities/auth/model"
import { ref, onMounted, computed, ComputedRef } from 'vue'
import api from "../../app/api/api.js"
import { userData } from "@/app/types/types"
import Loader from "@/shared/ui/Loader.vue"


const user = ref<userData>()

const authStore = useAuthStore()

const isLoading: ComputedRef<boolean> = computed(() => {
    return user.value === undefined
})


const getUser = async () => {
    try {
        const response = await api.get('/usersByToken', {
            headers: {
                Authorization: `Bearer ${authStore.token}`, // Передаем токен в заголовке
            },
        })
        console.log('User get:', response.data)
        user.value = response.data.user

    } catch (error) {
        console.error('Error creating travel:', error)
    }
}


onMounted(() => {
    getUser()
})
</script>

<template>
    <div  class="px-[10%] py-10">
        <Loader v-if="isLoading"/>
        <div v-else>
            <h1 class="text-2xl mb-4">Имя: {{ user.name }}</h1>
            <div>id: {{ user.id }}</div>
            <div>email: {{ user.email }}</div>
        </div>
    </div>
</template>

