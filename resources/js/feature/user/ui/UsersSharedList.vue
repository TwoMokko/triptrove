<script setup lang="ts">
import { ref } from "vue"
import UsersSearch from "@/feature/user/ui/UsersSearch.vue"
import Icon from "@/shared/ui/Icon.vue"
import { mdiDelete } from '@mdi/js'

const usersShared = ref([
    {name: 'test', id: 1},
    {name: 'abc', id: 2},
    {name: 'ter', id: 3},
    {name: 'rew', id: 4},
    {name: 'anp', id: 5}
])

// вынести в store
const getUsersShared = () => {

}

const delUserShared = (id: number) => {

}
</script>

<template>
    <div>
        <div class="pb-4">
            Другие пользователи, которые участвовали и могут редактировать
        </div>
        <div v-if="usersShared" class="flex gap-2 flex-wrap pb-4">
            <div v-for="user in usersShared">
                <div class="flex gap-2 py-3 px-8 rounded-3xl border border-secondary w-fit">
                    <div>{{ user.name }}</div>
                    <button @click="delUserShared(user.id)" class="cursor-pointer">
                        <Icon
                            :iconPath="mdiDelete"
                            class="w-6 h-6 text-secondary hover:text-dark"
                        />
                    </button>
                </div>
            </div>
        </div>
        <UsersSearch />
    </div>
</template>
