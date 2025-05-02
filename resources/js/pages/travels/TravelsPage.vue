<script setup lang="ts">
import { useUsersStore } from "@/etities/user"
import { useTravelsStore } from "@/etities/travel"
import {ref, onMounted, watch} from 'vue'
import { travelData } from "@/app/types/types"
import Loader from "@/shared/ui/Loader.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
// import TravelListItem from "@/feature/travel/TravelListItem.vue"
import TravelList from "@/feature/travel/TravelList.vue"
import TravelForm from "@/widgets/travel/ui/TravelForm.vue"
import Modal from "@/shared/ui/Modal.vue"
import {debounce} from "@/shared/lib/debounce";

const usersStore = useUsersStore()
const travelsStore = useTravelsStore()

const isModalOpenForCreateTravel = ref<boolean>(false)
const newTravel = ref<Omit<travelData, 'id'>>({ users: [] })

const selectedUsers = ref<number[]>([])
const isUpdatingSharedTravels = ref<boolean>(false)
const isOpenSelect = ref<boolean>(false)

const createTravel = (): void => {
    isModalOpenForCreateTravel.value = false
    console.log(newTravel.value)
    travelsStore.addTravel({ ...newTravel.value, user_id: usersStore.currentUser.id })
    newTravel.value = { users: [] }
}

watch(selectedUsers, (newVal) => {
    const hasOthers = newVal.some(id => id !== usersStore.currentUser.id)
    if (hasOthers && !newVal.includes(usersStore.currentUser.id)) {
        selectedUsers.value = [...newVal, usersStore.currentUser.id]
    }
    else if (!hasOthers && newVal.includes(usersStore.currentUser.id)) {
        selectedUsers.value = []
    }
}, { deep: true })

watch(selectedUsers, debounce(async (newVal) => {
    if (isUpdatingSharedTravels.value) return
    isUpdatingSharedTravels.value = true
    await travelsStore.getTravelsWithUsers(newVal)
    isUpdatingSharedTravels.value = false
}, 300), { deep: true })

onMounted(async () => {
    if (usersStore.currentUser) {
        await travelsStore.getTravels(usersStore.currentUser.id)
        await travelsStore.getFriendUsers(usersStore.currentUser.id)
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

<!--        <div v-if="travelsStore.hasSharedTravels" class="mt-4">-->
<!--            <h2 class="text-2xl mb-4">Путешествия с другими пользователями</h2>-->
<!--            <div v-for="creator in travelsStore.sharedTravels">-->
<!--                <h3 class="text-xl mb-4">name: {{ creator.name }}, login: {{ creator.login }}</h3>-->
<!--                <div>-->
<!--                    <TravelList :travels="creator.travels" list-type="shared" :creator-id="creator.id" />-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

        <div v-if="travelsStore.hasUsersFriend">
            <div class="flex gap-4 mb-4">
                <h2 class="text-2xl">Путешествия с пользователями:</h2>
                <div class="relative">
                    <div
                        @click="isOpenSelect = !isOpenSelect"
                        class="p-2 w-40"
                    >
                        select
                    </div>
                    <div
                        v-show="isOpenSelect"
                        class="absolute top-full left-0 right-0 bg-gray-400 flex flex-col gap-2 p-2"
                    >
                        <input
                            v-show="false"
                            type="checkbox"
                            :value="usersStore.currentUser.id"
                            v-model="selectedUsers"
                        >
                        <label
                            v-for="user in travelsStore.usersFriend"
                            v-show="user.id !== usersStore.currentUser.id"
                            :key="user.id"
                        >
                            <input
                                type="checkbox"
                                :value="user.id"
                                v-model="selectedUsers"
                            >
                            <span>{{ user.name }}</span>
                        </label>
                    </div>
                </div>
            </div>
            <div v-if="isUpdatingSharedTravels">
                <Loader />
            </div>
            <template v-else>
                <div v-if="travelsStore.hasFriendsTravels" class="mb-4">
                    <TravelList :travels="travelsStore.travelsWithUsers" list-type="shared" />
                </div>
                <div v-else>
                    Нет общих путешествий с выбранными пользователями
                </div>
            </template>
        </div>
    </div>
</template>
