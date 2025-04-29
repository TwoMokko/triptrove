<script setup lang="ts">
import { useUsersStore } from "@/etities/user"
import { useTravelsStore } from "@/etities/travel"
import { ref, onMounted } from 'vue'
import { travelData } from "@/app/types/types"
import Loader from "@/shared/ui/Loader.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
// import TravelListItem from "@/feature/travel/TravelListItem.vue"
import TravelList from "@/feature/travel/TravelList.vue"
import TravelForm from "@/widgets/travel/ui/TravelForm.vue"
import Modal from "@/shared/ui/Modal.vue"

const usersStore = useUsersStore()
const travelsStore = useTravelsStore()

const isModalOpenForCreateTravel = ref<boolean>(false)
const newTravel = ref<Omit<travelData, 'id'>>({ users: [] })

const createTravel = (): void => {
    isModalOpenForCreateTravel.value = false
    console.log(newTravel.value)
    travelsStore.addTravel({ ...newTravel.value, user_id: usersStore.currentUser.id })
    newTravel.value = { users: [] }
}

// const createTravel = async (): void =>  {
//     newTravel.value = await travelsStore.addTravel(usersStore.currentUser.id)
//     travelsStore.setCurrentTravel(newTravel.value)
// }

onMounted(async () => {
    if (usersStore.currentUser) {
        await travelsStore.getTravels(usersStore.currentUser.id)
        await travelsStore.getTravelsWithUsers([1, 2])
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
                    @handler="() => isModalOpenForCreateTravel = true"
                />
            </div>
            <Modal
                :isOpen="isModalOpenForCreateTravel"
                @close="() => isModalOpenForCreateTravel = false"
            >
                <TravelForm
                    v-model="newTravel"
                    @handler="createTravel"
                    :btn-text="'Добавить путешествие'"
                />
            </Modal>
        </div>
        <div v-else>
            <h2 class="text-2xl mb-4">Путешествия пользователя: {{ usersStore.currentUser.name }}</h2>
            <div class="mb-4">
                <div>
                    <div class="grid gap-2 grid-cols-10 py-4 px-[60px] font-medium">
                        <div >публичность</div>
                        <div >место</div>
                        <div >когда</div>
                        <div >сумма</div>
                        <div >на чем добирались</div>
                        <div >где жили</div>
                        <div >совет</div>
                        <div >экскурсии и развлечения</div>
                        <div >общие впечатления</div>
                        <div class="flex gap-2 justify-end">изменить/удалить</div>
                    </div>


                    <TravelList :travels="travelsStore.travels" list-type="personal" />
<!--                    <TravelListItem-->
<!--                        v-for="item in travelsStore.travels"-->
<!--                        :key="item.id"-->
<!--                        :item="item"-->
<!--                    />-->

                </div>
            </div>
            <div class="text-end">
                <ButtonCustom
                    text="Новое путешествие"
                    @handler="() => isModalOpenForCreateTravel = true"
                />
            </div>
            <Modal
                :isOpen="isModalOpenForCreateTravel"
                @close="() => isModalOpenForCreateTravel = false"
            >
                <TravelForm
                    v-model="newTravel"
                    @handler="createTravel"
                    :btn-text="'Добавить путешествие'"
                />
            </Modal>
        </div>

        <div v-if="travelsStore.hasSharedTravels" class="mt-4">
            <h2 class="text-2xl mb-4">Путешествия с другими пользователями</h2>
            <div v-for="creator in travelsStore.sharedTravels">
                <h3 class="text-xl mb-4">name: {{ creator.name }}, login: {{ creator.login }}</h3>
                <div>
                    <TravelList :travels="creator.travels" list-type="shared" :creator-id="creator.id" />
<!--                    <TravelListItem-->
<!--                        v-for="item in creator.travels"-->
<!--                        :key="item.id"-->
<!--                        :item="item"-->
<!--                    />-->
                </div>
            </div>

        </div>

        <div>
            <h2 class="text-2xl mb-4">Путешествия с пользователями: ВЫБИРАТЬ</h2>
            <div class="mb-4">
                <TravelList :travels="travelsStore.travelsWithUsers" list-type="shared" />
            </div>
        </div>
    </div>
</template>
