<script setup lang="ts">
import {computed, ComputedRef, onMounted, onUnmounted, ref, watch} from 'vue'
import Loader from "@/shared/ui/Loader.vue"
import { useUsersStore } from "@/etities/user"
import FileUploader from "@/shared/ui/FileUploader.vue"
import Icon from "@/shared/ui/Icon.vue"
import { mdiPencil } from '@mdi/js'

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

const originalValues = ref<Record<string, string>>({
    name: '',
    email: '',
    login: ''
})

const currentUser = computed(() => usersStore.currentUser)
const isLoading: ComputedRef<boolean> = computed(() =>  usersStore.currentUser === null)

watch(currentUser, (newVal: typeof currentUser.value) => {
    if (newVal) {
        originalValues.value = {
            name: newVal.name,
            email: newVal.email,
            login: newVal.login
        }
    }
}, { immediate: true })

const handleClickOutside = (event: MouseEvent) => {
    const isOutsideAllInputs = Object.values(inputRefs.value).every(
        (input) => !input?.contains(event.target as Node)
    )

    if (isOutsideAllInputs) {
        Object.keys(editMode.value).forEach((key) => {
            if (editMode.value[key]) {
                const field = key as keyof EditMode

                currentUser.value[field] !== originalValues.value[field]
                    ? onSave(field)
                    : editMode.value[field] = false

            }
            editMode.value[key] = false
        })
    }
}

const onSave = (field: keyof EditMode) => {
    try {
        // Здесь логика сохранения в API
        // Например:
        // await usersStore.updateUser({
        //   [field]: currentUser.value[field]
        // })

        console.log(`Сохранено поле ${field}:`, currentUser.value[field])
        originalValues.value[field] = currentUser.value[field]
    } catch (error) {
        console.error('Ошибка при сохранении:', error)
        // Откат значения, если сохранение не удалось
        currentUser.value[field] = originalValues.value[field]
    } finally {
        editMode.value[field] = false
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

//TODO: оптимизировать (создать компонент для строк с данными)
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
            <div class="w-96">
                <h1 class="text-2xl mb-4 flex gap-2 items-center group">
                    <span>Имя:</span>

                    <input
                        v-if="editMode.name"
                        v-model="currentUser.name"
                        :placeholder="currentUser.name"
                        :type="'text'"
                        :ref="(el) => (inputRefs.name = el as HTMLInputElement)"
                        @keyup.enter="editMode.name = false; onSave('name')"
                        class="w-full"
                    />
                    <div
                        v-else
                        class="w-full flex gap-2 justify-between"
                    >
                        <span>{{ currentUser.name }}</span>
                        <span
                            @click.stop="editMode.name = !editMode.name"
                            class="hidden group-hover:inline-block cursor-pointer"
                        >
                            <Icon
                                :iconPath="mdiPencil"
                                class="w-4 h-4 text-secondary hover:text-dark"
                            />
                        </span>
                    </div>
                </h1>
                <div>id: {{ currentUser.id }}</div>
                <div class="flex gap-2 items-center group">
                    login:

                    <input
                        v-if="editMode.login"
                        v-model="currentUser.login"
                        :placeholder="currentUser.login"
                        :type="'text'"
                        :ref="(el) => (inputRefs.login = el as HTMLInputElement)"
                        @keyup.enter="editMode.login = false; onSave('login')"
                        class="w-full"
                    />
                    <div
                        v-else
                        class="w-full flex gap-2 justify-between"
                    >
                        <span>{{ currentUser.login }}</span>
                        <span
                            @click.stop="editMode.login = !editMode.login"
                            class="hidden group-hover:inline-block cursor-pointer"
                        >
                            <Icon
                                :iconPath="mdiPencil"
                                class="w-4 h-4 text-secondary hover:text-dark"
                            />
                        </span>
                    </div>
                </div>
                <div class="flex gap-2 items-center group">
                    email:

                    <input
                        v-if="editMode.email"
                        v-model="currentUser.email"
                        :placeholder="currentUser.email"
                        :type="'text'"
                        :ref="(el) => (inputRefs.email = el as HTMLInputElement)"
                        @keyup.enter="editMode.login = false; onSave('email')"
                        class="w-full"
                    />
                    <div
                        v-else
                        class="w-full flex gap-2 justify-between"
                    >
                        <span>{{ currentUser.email }}</span>
                        <span
                            @click.stop="editMode.email = !editMode.email"
                            class="hidden group-hover:inline-block cursor-pointer"
                        >
                            <Icon
                                :iconPath="mdiPencil"
                                class="w-4 h-4 text-secondary hover:text-dark"
                            />
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

