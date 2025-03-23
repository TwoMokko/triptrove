<script setup lang="ts">
import { useUsersStore } from "@/etities/user"
import { useTravelsStore } from "@/etities/travel"
import { ref, onMounted, computed, ComputedRef } from 'vue'
import api from "../../app/api/api.js"
import { mdiPencil, mdiDelete } from '@mdi/js'
import {travelData, userData} from "@/app/types/types"
import Icon from "../../shared/ui/Icon.vue"
import Loader from "@/shared/ui/Loader.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
import Modal from '@/shared/ui/Modal.vue'
import TravelForm from "@/widgets/travel/ui/TravelForm.vue"
import { useAuthStore } from "@/etities/auth"

const travels = ref<travelData[] | undefined>()
const newTravel = ref<travelData>()
const changeTravel = ref<travelData>()
const changeId = ref<number | null>(null)
const isModalOpenForCreateTravel = ref(false)
const user = ref<userData>()

const usersForTravel = ref()

const authStore = useAuthStore()
const usersStore = useUsersStore()
const travelsStore = useTravelsStore()

const isLoading: ComputedRef<boolean> = computed(() => {
    return travels.value === undefined
})

const isTravels: ComputedRef<boolean> = computed(() => {
    return travels.value ? travels.value.length <= 0 : false
})

const getTravels = async (): Promise<void> => {
    try {
        const response = await api.get('/travelsFromUser', {
            params: {
                user_id: user.value.id,
            },
        })
        travels.value = response.data
    } catch (error) {
        console.error('Error fetching travels:', error)
    }
}

const createTravel = async (): Promise<void> => {
    try {
        // переписать!!!
        newTravel.value.user_id = user.value.id
        const response = await api.post('/travels', newTravel.value)
        console.log('Travel created:', response.data)
        getTravels().then(() => console.log({response}))
        newTravel.value = { user_id: user.value.id }

        // показать загрузку на кнопке, потом закрыть окно
        isModalOpenForCreateTravel.value = false
    } catch (error) {
        console.error('Error creating travel:', error)
    }

    // newTravel.value
    // getTravels().then(() => console.log({response}))
    // newTravel.value = { user_id: user.value.id }
    // показать загрузку на кнопке, потом закрыть окно
    // isModalOpenForCreateTravel.value = false
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

    // getTravels().then(() => console.log({response}))

    // показать загрузку на кнопке, потом закрыть окно
    // changeId.value = null
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

    // getTravels().then(() => console.log({response}))

    // спросить, удалять ли, потом показать, что идет удаление и после окно, что удалено
}

const getUsersForTravels = async (): Promise<void> => {
    try {
        const response = await api.get(`/travels/${1}/users`, {
            // params: {
            //     travel_id: changeId.value,
            // },
        })
        usersForTravel.value = response.data
        console.log(usersForTravel.value)
    } catch (error) {
        console.error('Error fetching travels:', error)
    }
}

const fetchUser = async () => {
    await usersStore.getUserByToken(authStore.token)
}

onMounted(() => {
    fetchUser().then(() => {
        user.value = usersStore.currentUser
        getTravels()
    })
})
</script>

<template>
    <div  class="px-[10%] py-10">
        <Loader v-if="isLoading"/>
        <div v-else>
            <div v-if="isTravels">
                <div class="mb-4">no travels</div>
                <div class="text-end">
                    <ButtonCustom text="Новое путешествие" @handler="() => isModalOpenForCreateTravel = true" />
                </div>
                <Modal :isOpen="isModalOpenForCreateTravel" @close="() => isModalOpenForCreateTravel = false">
                    <TravelForm v-model="newTravel" @handler="createTravel" :btn-text="'добавть путешествие'" />
                </Modal>
            </div>
            <div v-else>
                <h1 class="text-2xl mb-4">Путешествия пользователя: {{ user.name }}</h1>
                <div class="mb-4">
                    <div v-if="!isLoading">
                        <div class="grid gap-2 grid-cols-7 py-4 px-[60px] font-medium">
                            <div >место</div>
                            <div >когда</div>
                            <div >на чем добирались</div>
                            <div >хорошее</div>
                            <div >плохое</div>
                            <div >общие впечатления</div>
                            <div class="flex gap-2 justify-end">изменить/удалить</div>
                        </div>


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
        </div>
    </div>
</template>
