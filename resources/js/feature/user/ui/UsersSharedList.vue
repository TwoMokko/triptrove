<script setup lang="ts">
import {computed, ref, watch} from "vue"
import Icon from "@/shared/ui/Icon.vue"
import { mdiDelete } from '@mdi/js'
import InputCustom from "@/shared/ui/InputCustom.vue"
import api from "@/app/api/api"
import { storeToRefs } from "pinia"
import { useUsersStore } from "@/entities/user"
import { useTravelsStore } from "@/entities/travel"

interface user {
    id: number;
    name: string;
    login: string;
}

const props = defineProps<{
    modelValue: user[]
}>()

const emit = defineEmits(['update:modelValue'])

const { currentUser } = storeToRefs(useUsersStore())
const { currentTravel } = storeToRefs(useTravelsStore())
const localUsers = ref<user[]>([...props.modelValue])
const searchString = ref<string>('')
const usersSearch = ref<user[]>([])

const filteredUsers = computed(() =>
    localUsers.value.filter(user => user.id !== currentUser.value.id)
)

// Обновляем родительский компонент
const updateModel = () => {
    emit('update:modelValue', localUsers.value)
}

// Следим за изменениями props
watch(() => props.modelValue, (newValue) => {
    localUsers.value = [...newValue]
}, { deep: true })

const delUserShared = (id: number) => {
    localUsers.value = localUsers.value.filter(itm => itm.id !== id)
    updateModel()
}

const addUser = (user: user): void => {
    if (!localUsers.value.some(u => u.id === user.id)) {
        localUsers.value = [...localUsers.value, user]
        updateModel()
    }
}

const getUsersForSearch = async (): Promise<void> => {
    if (!searchString.value?.trim()) {
        usersSearch.value = []
        return
    }

    // TODO: ВЫНЕСТИ ОТСЮДА В STORE
    try {
        const response = await api.get(`/users/search/`, {
            params: {
                user_search: searchString.value,
                user_id: currentUser.value.id,
            },
        })
        usersSearch.value = response.data
        console.log(usersSearch.value)
    } catch (error) {
        console.error('Error fetching users:', error)
    }
}
</script>

<template>
    <div>
        <div class="pb-4">Другие пользователи, которые участвовали и могут редактировать</div>
        <div v-if="filteredUsers.length" class="flex gap-2 flex-wrap pb-4">
            <div v-for="user in filteredUsers" :key="user.id" class="flex gap-2 py-3 px-8 rounded-3xl border border-secondary w-fit">
                <div>{{ user.name }}</div>
                <div @click="delUserShared(user.id)" class="cursor-pointer">
                    <Icon :iconPath="mdiDelete" class="w-6 h-6 text-secondary hover:text-dark" />
                </div>
            </div>
        </div>

        <div>
            <InputCustom
                v-model:value="searchString"
                @input="getUsersForSearch"
                :placeholder="'поиск пользователя'"
                :type="'text'"
            />
            <div v-if="usersSearch.length" class="py-3 px-8 space-y-2">
                <template
                    v-for="user in usersSearch"
                    :key="user.id"
                >
                    <div v-if="user.id == currentUser.id || (currentTravel && user.id == currentTravel.user_id)"><span class="text-primary">является создателем</span> {{ user.name }} ({{ user.login }})</div>
                    <div
                        v-else
                        @click="addUser(user)"
                        class="cursor-pointer hover:text-secondary"
                    >
                        {{ user.name }} ({{ user.login }})
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
