<script setup lang="ts">
import { computed, ComputedRef } from 'vue'
import Loader from "@/shared/ui/Loader.vue"
import { useUsersStore } from "@/etities/user"
import FileUploader from "@/shared/ui/FileUploader.vue"

const usersStore = useUsersStore()

const currentUser = computed(() => usersStore.currentUser)
const isLoading: ComputedRef<boolean> = computed(() => {
    return usersStore.currentUser === null
})
</script>

<template>
    <Loader v-if="isLoading" />
    <div v-else class="px-[10%] py-10">
        <div class="flex gap-6 items-center">
            <FileUploader
                target="user"
                folder="avatars"
                db-field="avatar"
                :src="currentUser.avatar ? `storage/${currentUser.avatar}` : '/storage/users/avatars/default-user.svg'"
                :class-name="'w-40 h-40 rounded-full'"
            />
            <div>
                <h1 class="text-2xl mb-4">Имя: {{ currentUser.name }}</h1>
                <div>id: {{ currentUser.id }}</div>
                <div>login: {{ currentUser.login }}</div>
                <div>email: {{ currentUser.email }}</div>
            </div>
        </div>
    </div>
</template>

