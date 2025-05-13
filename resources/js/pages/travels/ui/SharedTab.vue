<script setup lang="ts">
import { ref, watch } from "vue"
import { debounce } from "@/shared/lib/debounce"
import Loader from "@/shared/ui/Loader.vue"
import TravelList from "@/feature/travel/TravelList.vue"
import Icon from "@/shared/ui/Icon.vue"
import { mdiMenuDown, mdiCheck } from '@mdi/js'
import { useUsersStore } from "@/etities/user"
import { useTravelsStore } from "@/etities/travel"

const usersStore = useUsersStore()
const travelsStore = useTravelsStore()

const selectedUsers = ref<number[]>([])
const isUpdatingSharedTravels = ref<boolean>(false)
const isOpenSelect = ref<boolean>(false)

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
</script>

<template>
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
