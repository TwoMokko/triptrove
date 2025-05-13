<script setup lang="ts">
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
import TravelList from "@/feature/travel/TravelList.vue"
import { ref } from "vue"
import Modal from "@/shared/ui/Modal.vue"
import TravelForm from "@/widgets/travel/ui/TravelForm.vue"
import { travelData } from "@/app/types/types"
import { useTravelsStore } from "@/etities/travel"
import { useUsersStore } from "@/etities/user"

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
</script>

<template>
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
            <TravelList :travels="travelsStore.travels" list-type="personal" />
        </div>
        <div class="text-end">
            <ButtonCustom
                text="Новое путешествие"
                @handler="() => isModalOpenForCreateTravel = true"
            />
        </div>
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
</template>
