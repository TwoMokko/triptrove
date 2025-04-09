<script setup lang="ts">
import { onMounted } from "vue"
import UsersSearch from "@/feature/user/ui/UsersSearch.vue"
import Icon from "@/shared/ui/Icon.vue"
import { mdiDelete } from '@mdi/js'
import { useTravelsStore } from "@/etities/travel"


const travelsStore = useTravelsStore()

const delUserShared = (id: number) => {
    travelsStore.detachUser(id)
}

onMounted(() => {
    travelsStore.getSharedUsers()
})
</script>

<template>
    <div>
        <div class="pb-4">
            Другие пользователи, которые участвовали и могут редактировать
        </div>
        <div v-if="travelsStore.sharedUsers" class="flex gap-2 flex-wrap pb-4">
            <div v-for="user in travelsStore.sharedUsers">
                <div class="flex gap-2 py-3 px-8 rounded-3xl border border-secondary w-fit">
                    <div>{{ user.name }}</div>
                    <div @click="delUserShared(user.user_id)" class="cursor-pointer">
                        <Icon
                            :iconPath="mdiDelete"
                            class="w-6 h-6 text-secondary hover:text-dark"
                        />
                    </div>
                </div>
            </div>
        </div>
        <UsersSearch />
    </div>
</template>
