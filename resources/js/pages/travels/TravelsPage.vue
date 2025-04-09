<script setup lang="ts">
import { useUsersStore } from "@/etities/user"
import { useTravelsStore } from "@/etities/travel"
import { ref, onMounted } from 'vue'
import { travelData } from "@/app/types/types"
import Loader from "@/shared/ui/Loader.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
import Modal from '@/shared/ui/Modal.vue'
import TravelForm from "@/widgets/travel/ui/TravelForm.vue"
import { useAuthStore } from "@/etities/auth"
import TravelListItem from "@/shared/ui/travel/TravelListItem.vue"

const usersForTravel = ref()

const authStore = useAuthStore()
const usersStore = useUsersStore()
const travelsStore = useTravelsStore()

const isModalOpenForCreateTravel = ref<boolean>(false)
const newTravel = ref<Omit<travelData, 'id'>>()


const createTravel = async (): void => {
    isModalOpenForCreateTravel.value = false
    // travelsStore.addTravel({ ...newTravel.value, user_id: usersStore.currentUser.id })
    if (travelsStore.currentTravel) {

        console.log('save new travel: ', travelsStore.currentTravel)

        await travelsStore.editTravel(
            travelsStore.currentTravel.id,
            travelsStore.currentTravel
        )
        travelsStore.setCurrentTravel(null)
    }
}


const openCreateTravel = async (): void =>  {
    newTravel.value = await travelsStore.addTravel(usersStore.currentUser.id)
    isModalOpenForCreateTravel.value = true
}

onMounted(async () => {
    await usersStore.getUserByToken(authStore.token)
    if (usersStore.currentUser) {
        await travelsStore.getTravels(usersStore.currentUser.id)
    }
})

</script>

<template>
    <div  class="px-[10%] py-10">
        <Loader v-if="travelsStore.isLoading"/>
        <div v-else>
            <div v-if="!travelsStore.hasTravels">
                <div class="mb-4">no travels</div>
                <div class="text-end">
                    <ButtonCustom
                        text="Новое путешествие"
                        @handler="openCreateTravel"
                    />
                </div>
                <Modal
                    :isOpen="isModalOpenForCreateTravel"
                    @close="() => isModalOpenForCreateTravel = false"
                >
                    <TravelForm
                        v-model="travelsStore.currentTravel"
                        @handler="createTravel"
                        :btn-text="'Добавить путешествие'"
                    />
                </Modal>
            </div>
            <div v-else>
                <h1 class="text-2xl mb-4">Путешествия пользователя: {{ usersStore.currentUser.name }}</h1>
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
                        @handler="openCreateTravel"
                    />
                </div>
                <Modal
                    :isOpen="isModalOpenForCreateTravel"
                    @close="() => isModalOpenForCreateTravel = false"
                >
                    <TravelForm
                        v-model="travelsStore.currentTravel"
                        @handler="createTravel"
                        :btn-text="'Добавить путешествие'"
                    />
                </Modal>
            </div>
        </div>
    </div>
</template>
