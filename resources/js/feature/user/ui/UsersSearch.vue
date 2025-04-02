<script setup lang="ts">
import {computed, ref} from "vue"
import api from "@/app/api/api.js"
import InputCustom from "@/shared/ui/InputCustom.vue"

const searchUsers = ref<string>()
const usersSearch = ref([])

const isUsersSearch = computed(() => usersSearch.value.length > 0)

// вынести это в store
const getUsersForSearch = async (): Promise<void> => {
    if (!searchUsers.value) {
        usersSearch.value = []
        return
    }

    try {
        const response = await api.get(`/usersSearch/`, {
            params: {
                user_search: searchUsers.value,
            },
        })
        usersSearch.value = response.data
        console.log(usersSearch.value)
    } catch (error) {
        console.error('Error fetching users for search:', error)
    }
}
</script>

<template>
    <div>
        <InputCustom v-model:value="searchUsers" @input="getUsersForSearch" :placeholder="'поиск пользователя'" :type="'text'" />
        <div class="py-3 px-8">
            <div v-for="userItm in usersSearch">
                {{ userItm.name }} ({{ userItm.login }})
            </div>
        </div>
    </div>
</template>
