<script setup lang="ts">
import {ref, onMounted, computed} from 'vue'
import api from "./app/api/api.js"
import { travelData } from "./app/types/types";

const travels = ref<travelData[]>([])
const userId: number = 1
const newTravel = ref<travelData>({ user_id: userId })
const changeTravel = ref<travelData>({ user_id: userId })
const changeId = ref<number | null>(null)


const isChangeTravelOnId = computed(() => {
    return changeId.value == null
})

const getTravels = async () => {
    try {
        const response = await api.get('/travels')
        travels.value = response.data
    } catch (error) {
        console.error('Error fetching travels:', error)
    }
}

const createTravel = async (data: travelData) => {
    console.log({data})
    console.log({currentTravel: newTravel})

    try {
        const response = await api.post('/travels', data)
        console.log('Travel created:', response.data)
        getTravels().then(() => console.log({response}))
        newTravel.value = { user_id: userId }
    } catch (error) {
        console.error('Error creating travel:', error)
    }
}

const updateTravel = (item: travelData) => {
    changeId.value = item.id
    changeTravel.value = item

    console.log(isChangeTravelOnId.value)
}

const saveTravel = () => {
    doUpdateTravel()
    changeId.value = null
}

const doUpdateTravel = async () => {
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

const deleteTravel = async (id: number) => {
    try {
        const response = await api.delete(`/travels/${id}`)
        console.log('Travel deleted:', response.data)
        getTravels().then(() => console.log({response}))
    } catch (error) {
        console.error('Error deleting travel:', error)
    }
}

onMounted(() => {
    getTravels()
})
</script>

<template>
    <div class="px-[10%] py-10 bg-main">
        <h1 class="text-2xl mb-4 text-primary">Путешествия пользователя с ID: {{ newTravel.user_id }}</h1>
        <div class="mb-4">
            <div v-if="travels.length">
                <div v-for="item in travels" :key="item.id">
                    <div v-if="isChangeTravelOnId" class="grid gap-2 grid-cols-8">
                        <div>{{ item.place }}</div>
                        <div>{{ item.date }}</div>
                        <div>{{ item.mode_of_transport }}</div>
                        <div>{{ item.good_impression }}</div>
                        <div>{{ item.bad_impression }}</div>
                        <div>{{ item.general_impression }}</div>
                        <div @click="updateTravel(item)" class="cursor-pointer text-primary">change</div>
                        <div @click="deleteTravel(item.id)" class="cursor-pointer text-red-900">del</div>
                    </div>
                    <div v-else>
                        <input class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary" v-model="changeTravel.place" placeholder="место">
                        <input class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary" v-model="changeTravel.date" placeholder="время когда">
                        <input class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary" v-model="changeTravel.mode_of_transport" placeholder="на чем добирались">
                        <textarea class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary resize-none" v-model="changeTravel.good_impression" placeholder="хорошее" />
                        <textarea class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary resize-none" v-model="changeTravel.bad_impression" placeholder="плохое" />
                        <textarea class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary resize-none" v-model="changeTravel.general_impression" placeholder="общие впечатления" />
                        <button @click="saveTravel(item.id)">save</button>
                    </div>
                </div>
            </div>
            <p v-else>Loading...</p>
        </div>

        <div class="flex flex-col gap-2 mb-4">
            <input class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary" v-model="newTravel.place" placeholder="место">
            <input class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary" v-model="newTravel.date" placeholder="время когда">
            <input class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary" v-model="newTravel.mode_of_transport" placeholder="на чем добирались">
            <textarea class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary resize-none" v-model="newTravel.good_impression" placeholder="хорошее" />
            <textarea class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary resize-none" v-model="newTravel.bad_impression" placeholder="плохое" />
            <textarea class="p-2 border-[1px] rounded-md focus:border-[1px] focus-visible:outline-none focus:border-primary focus-visible:border-[1px] focus-visible:border-primary resize-none" v-model="newTravel.general_impression" placeholder="общие впечатления" />
        </div>

        <div>
            <button @click="createTravel(newTravel)" class="p-2 bg-primary text-white rounded-md">create travel</button>
        </div>
    </div>
</template>
