<script setup lang="ts">
import {ref, onMounted, computed, ComputedRef} from 'vue'
import api from "../../app/api/api.js"
import { travelData } from "@/app/types/types"
import Icon from "../../shared/ui/Icon.vue"
import { mdiPencil, mdiDelete, mdiContentSave, mdiClose } from '@mdi/js'
import Loader from "@/shared/ui/Loader.vue";
import InputCustom from "@/shared/ui/InputCustom.vue";
import Textarea from "@/shared/ui/Textarea.vue";

const travels = ref<travelData[]>([])
const userId: number = 1
const newTravel = ref<travelData>({ user_id: userId })
const changeTravel = ref<travelData>({ user_id: userId })
const changeId = ref<number | null>(null)

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
    } catch (error) {
        console.error('Error creating travel:', error)
    }
}

const updateTravel = (item: travelData): void => {
    changeId.value = item.id
    changeTravel.value = item
}

const saveTravel = (): void => {
    doUpdateTravel()
    changeId.value = null
}

const cancelUpdate = (): void => {
    // как-то сохранить предыдущие данные
    changeId.value = null
}

const doUpdateTravel = async (): Promise<void> => {
    try {
        const response = await api.put(`/travels/${changeId.value}`, changeTravel.value)
        console.log('Travel updated:', response.data)
        getTravels().then(() => console.log({response}))
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
    } catch (error) {
        console.error('Error deleting travel:', error)
    }
}

const resizeTextarea = (event: Event): void => {
    const textarea: HTMLTextAreaElement = event.target as HTMLTextAreaElement

    textarea.style.height = 'auto'
    if (!textarea.textContent) textarea.style.height = `${textarea.scrollHeight}px`

    // return textarea.scrollHeight
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
                                <Icon :iconPath="mdiPencil" class="w-6 h-6 text-secondary hover:text-dark" />
                            </div>
                            <div @click="deleteTravel(item.id)" class="cursor-pointer">
                                <Icon :iconPath="mdiDelete" class="w-6 h-6 text-secondary hover:text-dark" />
                            </div>
                        </div>
                    </div>
                    <div v-else class="grid gap-2 grid-cols-7">
                        <input class="focus-visible:outline-none py-4 px-8 rounded-3xl border border-secondary bg-transparent" v-model="changeTravel.place" placeholder="место">
                        <input class="focus-visible:outline-none py-4 px-8 rounded-3xl border border-secondary bg-transparent" v-model="changeTravel.date" placeholder="время когда">
                        <input class="focus-visible:outline-none py-4 px-8 rounded-3xl border border-secondary bg-transparent" v-model="changeTravel.mode_of_transport" placeholder="на чем добирались">
                        <textarea @input="resizeTextarea" class="focus-visible:outline-none resize-none py-4 px-8 rounded-3xl border border-secondary bg-transparent" v-model="changeTravel.good_impression" placeholder="хорошее" />
                        <textarea @input="resizeTextarea" class="focus-visible:outline-none resize-none py-4 px-8 rounded-3xl border border-secondary bg-transparent" v-model="changeTravel.bad_impression" placeholder="плохое" />
                        <textarea @input="resizeTextarea" class="focus-visible:outline-none resize-none py-4 px-8 rounded-3xl border border-secondary bg-transparent" v-model="changeTravel.general_impression" placeholder="общие впечатления" />
                        <div class="flex gap-2 justify-end">
                            <Icon @click="saveTravel" :iconPath="mdiContentSave" class="w-6 h-6 text-secondary hover:text-dark" />
                            <Icon @click="cancelUpdate" :iconPath="mdiClose" class="w-6 h-6 text-secondary hover:text-dark" />
                        </div>
                    </div>
                </div>
            </div>
            <p v-else>Loading...</p>
        </div>

       <div class="card">
           <div class="flex flex-col gap-3 mb-4">
               <InputCustom v-model="newTravel.place" :placeholder="'место'" :type="'text'" :value="newTravel.place" />
               <InputCustom v-model="newTravel.date" :placeholder="'время когда'" :type="'text'" :value="newTravel.date" />
               <InputCustom v-model="newTravel.mode_of_transport" :placeholder="'на чем добирались'" :type="'text'" :value="newTravel.mode_of_transport" />

               <Textarea v-model="newTravel.good_impression" :placeholder="'хорошее'" :value="newTravel.good_impression" />

<!--               <textarea @input="resizeTextarea" class="focus-visible:outline-none resize-none py-4 px-8 rounded-3xl border border-secondary bg-transparent" v-model="newTravel.good_impression" placeholder="хорошее" />-->
               <textarea @input="resizeTextarea" class="focus-visible:outline-none resize-none py-4 px-8 rounded-3xl border border-secondary bg-transparent" v-model="newTravel.bad_impression" placeholder="плохое" />
               <textarea @input="resizeTextarea" class="focus-visible:outline-none resize-none py-4 px-8 rounded-3xl border border-secondary bg-transparent" v-model="newTravel.general_impression" placeholder="общие впечатления" />


           </div>
           <div class="text-end mt-4">
               <!--            <button @click="createTravel(newTravel)" class="py-3 px-10 text-white rounded-[60px] cursor-pointer text-center bg-linear-135 from-cyan-500 to-blue-500">create travel</button>-->
               <a @click="createTravel" class="btn">create travel</a>
           </div>
       </div>
    </div>
</template>

