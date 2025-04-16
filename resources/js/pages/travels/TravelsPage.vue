<script setup lang="ts">
import { useUsersStore } from "@/etities/user"
import { useTravelsStore } from "@/etities/travel"
import { ref, onMounted } from 'vue'
import { travelData } from "@/app/types/types"
import Loader from "@/shared/ui/Loader.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
import TravelListItem from "@/feature/travel/TravelListItem.vue"

const usersStore = useUsersStore()
const travelsStore = useTravelsStore()

// const isModalOpenForCreateTravel = ref<boolean>(false)
const newTravel = ref<Omit<travelData, 'id'>>()


const createTravel = async (): void =>  {
    newTravel.value = await travelsStore.addTravel(usersStore.currentUser.id)
    travelsStore.setCurrentTravel(newTravel.value)
}

onMounted(async () => {
    if (usersStore.currentUser) {
        await travelsStore.getTravels(usersStore.currentUser.id)
        await travelsStore.getSharedTravels(usersStore.currentUser.id)
    }
})

</script>

<template>
    <Loader v-if="travelsStore.isLoading"/>
    <div v-else class="px-[10%] py-10">
        <div v-if="!travelsStore.hasTravels">
            <div class="mb-4">no travels</div>
            <div class="text-end">
                <ButtonCustom
                    text="Новое путешествие"
                    @handler="createTravel"
                />
            </div>
        </div>
        <div v-else>
            <h2 class="text-2xl mb-4">Путешествия пользователя: {{ usersStore.currentUser.name }}</h2>
            <div class="mb-4">
                <div>
                    <div class="grid gap-2 grid-cols-7 py-4 px-[60px] font-medium">
                        <div >место</div>
                        <div >когда</div>
                        <div >на чем добирались</div>
                        <div >хорошее</div>
                        <div >плохое</div>
                        <div >общие впечатления</div>
                        <div class="flex gap-2 justify-end">изменить/удалить</div>
                    </div>


                    <TravelListItem
                        v-for="item in travelsStore.travels"
                        :key="item.id"
                        :item="item"
                    />

                </div>
            </div>
            <div class="text-end">
                <ButtonCustom
                    text="Новое путешествие"
                    @handler="createTravel"
                />
            </div>
        </div>

        <div v-if="travelsStore.hasSharedTravels" class="mt-4">
            <h2 class="text-2xl mb-4">Путешествия с другими пользователями</h2>
            <div v-for="creator in travelsStore.sharedTravels">
                <h3 class="text-xl mb-4">name: {{ creator.name }}, login: {{ creator.login }}</h3>
                <div>
                    <TravelListItem
                        v-for="item in creator.travels"
                        :key="item.id"
                        :item="item"
                    />
                </div>
            </div>

        </div>
    </div>
</template>
