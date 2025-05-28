<script setup lang="ts">
import { computed, ComputedRef, onMounted, onUnmounted, ref } from 'vue'
import Loader from "@/shared/ui/Loader.vue"
import { useUsersStore } from "@/etities/user"
import FileUploader from "@/shared/ui/FileUploader.vue"

interface EditMode {
    name: boolean,
    email: boolean,
    login: boolean
}

const usersStore = useUsersStore()
const editMode = ref<EditMode>({
    name: false,
    email: false,
    login: false
})
const inputRefs = ref<Record<string, HTMLInputElement | null>>({
    name: null,
    email: null,
    login: null,
})

const currentUser = computed(() => usersStore.currentUser)
const isLoading: ComputedRef<boolean> = computed(() => {
    return usersStore.currentUser === null
})


const handleClickOutside = (event: MouseEvent) => {
    const isOutsideAllInputs = Object.values(inputRefs.value).every(
        (input) => !input?.contains(event.target as Node)
    )

    if (isOutsideAllInputs) {
        Object.keys(editMode.value).forEach((key) => {
            if (editMode.value[key]) {
                onSave(key)
            }
            editMode.value[key] = false
        })
    }
}

const onSave = (field: keyof EditMode) => {
    console.log(`Сохранено поле ${field}:`, currentUser.value[field])
    editMode.value[field] = false
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
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
                <h1 class="text-2xl mb-4 flex gap-2 items-center">
                    <span>Имя:</span>

                    <input
                        v-if="editMode.name"
                        v-model="currentUser.name"
                        :placeholder="currentUser.name"
                        :type="'text'"
                        :ref="(el) => (inputRefs.name = el as HTMLInputElement)"
                        @keyup.enter="editMode.name = false; onSave('name')"
                    />
                    <span
                        v-else
                        @click.stop="editMode.name = !editMode.name"
                    >
                        {{ currentUser.name }}
                    </span>
                </h1>
                <div>id: {{ currentUser.id }}</div>
                <div class="flex gap-2 items-center">
                    login:

                    <input
                        v-if="editMode.login"
                        v-model="currentUser.login"
                        :placeholder="currentUser.login"
                        :type="'text'"
                        :ref="(el) => (inputRefs.login = el as HTMLInputElement)"
                        @keyup.enter="editMode.login = false; onSave('login')"
                    />
                    <span
                        v-else
                        @click.stop="editMode.login = !editMode.login"
                    >
                        {{ currentUser.login }}
                    </span>
                </div>
                <div class="flex gap-2 items-center">
                    email:

                    <input
                        v-if="editMode.email"
                        v-model="currentUser.email"
                        :placeholder="currentUser.email"
                        :type="'text'"
                        :ref="(el) => (inputRefs.email = el as HTMLInputElement)"
                        @keyup.enter="editMode.login = false; onSave('email')"
                    />
                    <span
                        v-else
                        @click.stop="editMode.email = !editMode.email"
                    >
                        {{ currentUser.email }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

