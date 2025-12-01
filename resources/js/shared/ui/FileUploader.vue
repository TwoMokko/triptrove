<script setup lang="ts">
import { ref } from "vue"
import { useUsersStore } from "@/entities/user/model/store"
import { useTravelsStore } from "@/features/travels/model/store"

const props = defineProps<{
    target: 'user' | 'travel';          // Для какой сущности загрузка
    folder: string;                     // Папка на сервере (avatars/travels)
    className: string;                  // class
    src: string;                        // Путь до картинки
    dbField?: string;                   // Поле в БД (avatar/cover_photo)
    entityId?: number;                  // ID путешествия/пользователя
}>()

const userStore = useUsersStore()
const travelStore = useTravelsStore()

const fileInput = ref(null)
const previewUrl = ref(props.src)

const triggerFileInput = () => {
    fileInput.value.click()
}

const handleUpload = async (e) => {
    const file = e.target.files[0]
    if (!file) return



    if (props.target === 'user') {
        try {
            await userStore.fetchUpdateAvatar(file)
            previewUrl.value = URL.createObjectURL(file)
            // previewUrl.value = userStore.currentUser.avatar
        }
        catch (err) {
            alert(err)
        }
    } else {
        if (!props.entityId) throw new Error('Travel ID is required')
        await travelStore.fetchUpdateTravelCover(file, props.entityId)
    }
}
</script>

<template>
    <div
        :class="className"
        class="relative flex flex-col items-center justify-end overflow-hidden group"
    >
        <input
            type="file"
            ref="fileInput"
            @change="handleUpload"
            accept="image/*"
            class="hidden"
        >

        <img
            v-if="previewUrl"
            :src="previewUrl"
            alt="img"
            class="absolute top-0 bottom-0 object-cover text-center w-full h-full"
        >
        <div
            @click="triggerFileInput"
            class="w-full bg-stone-700/50 text-white p-2 text-center z-10 cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity duration-200"
        >
            edit
        </div>
    </div>
</template>

