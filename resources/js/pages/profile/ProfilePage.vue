<script setup lang="ts">
import { ref, onMounted, computed, ComputedRef } from 'vue'
import api from "../../app/api/api.js"
import { mdiPencil, mdiDelete } from '@mdi/js'
import { travelData } from "@/app/types/types"
import Icon from "../../shared/ui/Icon.vue"
import Loader from "@/shared/ui/Loader.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
import Modal from '@/shared/ui/Modal.vue'
import TravelForm from "@/widgets/travel/ui/TravelForm.vue"

const travels = ref<travelData[]>([])
const userId: number = 1
const newTravel = ref<travelData>({ user_id: userId })
const changeTravel = ref<travelData>({ user_id: userId })
const changeId = ref<number | null>(null)
const isModalOpenForCreateTravel = ref(false)

const isLoading: ComputedRef<boolean> = computed(() => {
    return travels.value.length <= 0
})

const getTravels = async (): Promise<void> => {
    try {
        const response = await api.get('/travels')
        travels.value = response.data
    } catch (error) {
        console.error('Error fetching travels:', error)
    }
}

const createTravel = async (): Promise<void> => {
    try {
        const response = await api.post('/travels', newTravel.value)
        console.log('Travel created:', response.data)
        getTravels().then(() => console.log({response}))
        newTravel.value = { user_id: userId }

        // показать загрузку на кнопке, потом закрыть окно
        isModalOpenForCreateTravel.value = false
    } catch (error) {
        console.error('Error creating travel:', error)
    }
}

const updateTravel = (item: travelData): void => {
    changeId.value = item.id
    changeTravel.value = item
}

const saveTravel = async (): Promise<void> => {
    try {
        const response = await api.put(`/travels/${changeId.value}`, changeTravel.value)
        console.log('Travel updated:', response.data)
        getTravels().then(() => console.log({response}))

        // показать загрузку на кнопке, потом закрыть окно
        changeId.value = null
    } catch (error) {
        console.error('Error updating travel:', error)
        if (error.response) {
            console.log(error.response.data.message); // Ошибка от сервера
        } else {
            console.log('Network error'); // Ошибка сети
        }
    }
}

const deleteTravel = async (id: number): Promise<void> => {
    try {
        const response = await api.delete(`/travels/${id}`)
        console.log('Travel deleted:', response.data)
        getTravels().then(() => console.log({response}))

        // спросить, удалять ли, потом показать, что идет удаление и после окно, что удалено
    } catch (error) {
        console.error('Error deleting travel:', error)
    }
}

onMounted(() => {
    getTravels()
})
</script>

<template>
    <div v-if="isLoading" class="px-[10%] py-10">
        <Loader />
    </div>
    <div v-else class="px-[10%] py-10">
        <h1 class="text-2xl mb-4">Путешествия пользователя с ID: {{ newTravel.user_id }}</h1>
        <div class="mb-4">
            <div v-if="travels.length">
                <div v-for="item in travels" :key="item.id" class="card">
                    <div v-if="changeId !== item.id" class="grid gap-2 grid-cols-7">
                        <div>{{ item.place }}</div>
                        <div>{{ item.date }}</div>
                        <div>{{ item.mode_of_transport }}</div>
                        <div>{{ item.good_impression }}</div>
                        <div>{{ item.bad_impression }}</div>
                        <div>{{ item.general_impression }}</div>
                        <div class="flex gap-2 justify-end">
                            <div @click="updateTravel(item)" class="cursor-pointer">
                                <Icon :iconPath="mdiPencil" class="w-6 h-6 text-secondary hover:text-dark cursor-pointer" />
                            </div>
                            <div @click="deleteTravel(item.id)" class="cursor-pointer">
                                <Icon :iconPath="mdiDelete" class="w-6 h-6 text-secondary hover:text-dark cursor-pointer" />
                            </div>
                        </div>
                    </div>
                    <Modal :isOpen="changeId === item.id" @close="() => changeId = null">
                        <TravelForm v-model="changeTravel" @handler="saveTravel" :btn-text="'сохранить'" />
                    </Modal>
                </div>
            </div>
            <p v-else>Loading...</p>
        </div>
        <div class="text-end">
            <ButtonCustom text="Новое путешествие" @handler="() => isModalOpenForCreateTravel = true" />
        </div>
        <Modal :isOpen="isModalOpenForCreateTravel" @close="() => isModalOpenForCreateTravel = false">
            <TravelForm v-model="newTravel" @handler="createTravel" :btn-text="'добавть путешествие'" />
        </Modal>
    </div>
</template>

