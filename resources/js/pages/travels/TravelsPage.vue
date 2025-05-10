<script setup lang="ts">
import { useUsersStore } from "@/etities/user"
import { useTravelsStore } from "@/etities/travel"
import { ref, onMounted, watch } from 'vue'
import { travelData } from "@/app/types/types"
import Loader from "@/shared/ui/Loader.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
// import TravelListItem from "@/feature/travel/TravelListItem.vue"
import TravelList from "@/feature/travel/TravelList.vue"
import TravelForm from "@/widgets/travel/ui/TravelForm.vue"
import Modal from "@/shared/ui/Modal.vue"
import { debounce } from "@/shared/lib/debounce"
import { useRoute, useRouter } from "vue-router"
import Icon from "@/shared/ui/Icon.vue"
import { mdiMenuDown, mdiCheck } from '@mdi/js'

const usersStore = useUsersStore()
const travelsStore = useTravelsStore()

const isModalOpenForCreateTravel = ref<boolean>(false)
const newTravel = ref<Omit<travelData, 'id'>>({ users: [] })

const selectedUsers = ref<number[]>([])
const isUpdatingSharedTravels = ref<boolean>(false)
const isOpenSelect = ref<boolean>(false)


const tabs = [
    { id: 'personal', label: 'Мои путешествия' },
    { id: 'shared', label: 'Совместные' },
    // { id: 'favorites', label: 'Избранное' },
    // { id: 'archived', label: 'Архив' }
] as const

type TabId = typeof tabs[number]['id']
const activeTab = ref<TabId>('personal')

const createTravel = (): void => {
    isModalOpenForCreateTravel.value = false
    console.log(newTravel.value)
    travelsStore.addTravel({ ...newTravel.value, user_id: usersStore.currentUser.id })
    newTravel.value = { users: [] }
}

const route = useRoute()
const router = useRouter()

if (route.query.tab && tabs.some(t => t.id === route.query.tab)) {
    activeTab.value = route.query.tab as TabId
}

watch(activeTab, (tab) => {
    router.replace({ query: { ...route.query, tab } })
})

watch(activeTab, (newTab) => {
    if (newTab === 'shared' && !travelsStore.usersFriend.length) {
        travelsStore.getFriendUsers(usersStore.currentUser.id)
    }
})

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
        <div class="flex mb-6">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                class="py-2 px-4 border-b border-transparent focus:outline-none"
                :class="{'border-primary': activeTab === tab.id}"
            >
                {{ tab.label }}
            </button>
        </div>

        <transition
            mode="out-in"
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div :key="activeTab" class="will-change-[opacity]">
                <!-- Контент для личных путешествий вынести -->
                <template v-if="activeTab === 'personal'">
                    <div v-if="!travelsStore.hasTravels" class="mb-4">
                        <div>Нет созданных путешествий</div>
                        <div class="text-end">
                            <ButtonCustom
                                text="Новое путешествие"
                                @handler="() => isModalOpenForCreateTravel = true"
                            />
                        </div>
                    </div>

                    <div v-else>
                        <div class="mb-4">
                            <!--                        <div class="grid gap-2 grid-cols-10 py-4 px-[60px] font-medium">-->
                            <!--                            &lt;!&ndash; Заголовки колонок &ndash;&gt;-->
                            <!--                        </div>-->
                            <TravelList :travels="travelsStore.travels" list-type="personal" />
                        </div>
                        <div class="text-end">
                            <ButtonCustom
                                text="Новое путешествие"
                                @handler="() => isModalOpenForCreateTravel = true"
                            />
                        </div>
                    </div>
                </template>

                <!-- Контент для совместных путешествий вынести -->
                <template v-else-if="activeTab === 'shared'">
                    <div v-if="travelsStore.hasUsersFriend">
                        <div class="flex gap-4 mb-4 items-center">
                            <h3 class="text-xl">Фильтр по пользователям:</h3>
                            <!-- Вынести фильтр селект -->
                            <div class="relative">
                                <div
                                    @click="isOpenSelect = !isOpenSelect"
                                    class="flex justify-between py-2 px-4 w-40 border border-primary cursor-pointer"
                                >
                                    <span>select</span>
                                    <Icon
                                        :iconPath="mdiMenuDown"
                                        class="w-6 h-6 text-primary"
                                    />
                                </div>
                                <div
                                    v-show="isOpenSelect"
                                    class="absolute top-full left-0 right-0 bg-white flex flex-col gap-2 p-2"
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
                                            class="hidden"
                                        >
                                        <div class="flex gap-2 items-center">
                                        <span
                                            class="flex justify-center items-center w-5 h-5 border border-primary rounded transition-all"
                                        >
                                            <Icon
                                                v-if="selectedUsers.includes(user.id)"
                                                :iconPath="mdiCheck"
                                                class="w-3 h-3 text-primary"
                                            />
                                        </span>
                                            <span>{{ user.name }}</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div v-if="isUpdatingSharedTravels">
                            <Loader />
                        </div>

                        <template v-else>
                            <div v-if="travelsStore.travelsWithUsers === null">
                                Выберите пользователей для просмотра совместных путешествий
                            </div>

                            <template v-else>
                                <div v-if="travelsStore.hasFriendsTravels" class="mb-4">
                                    <!--                                <h2 class="text-2xl mb-4">Совместные путешествия</h2>-->
                                    <TravelList :travels="travelsStore.travelsWithUsers" list-type="shared" />
                                </div>
                                <div v-else>
                                    Нет общих путешествий с выбранными пользователями
                                </div>
                            </template>
                        </template>
                    </div>

                    <div v-else>
                        У вас пока нет друзей для совместных путешествий
                    </div>
                </template>
            </div>

        </transition>

        <!-- Модальное окно (остается общим для обоих вкладок) -->
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
</template>

