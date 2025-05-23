<script setup lang="ts">
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
import TravelList from "@/feature/travel/TravelList.vue"
import { ref } from "vue"
import TravelForm from "@/widgets/travel/ui/TravelForm.vue"
import { travelData } from "@/app/types/types"
import { useTravelsStore } from "@/etities/travel"
import { useUsersStore } from "@/etities/user"
import { useModal } from "@/shared/lib/useModal"

const { openModal, closeModal } = useModal()
const usersStore = useUsersStore()
const travelsStore = useTravelsStore()

const newTravel = ref<Omit<travelData, 'id'>>({ users: [] })

const createTravel = (): void => {
    console.log(newTravel.value)
    travelsStore.addTravel({ ...newTravel.value, user_id: usersStore.currentUser.id })
    newTravel.value = { users: [] }
    closeModal('create-travel')
}

const openCreateTravelModal = () => {
    openModal('create-travel', TravelForm, {
        modelValue: newTravel.value,
        onHandler: createTravel,
        btnText: 'Добавить путешествие'
    })
}
</script>

<template>
    <div v-if="!travelsStore.hasTravels" class="mb-4">
        <div>Нет созданных путешествий</div>
        <div class="text-end">
            <ButtonCustom
                text="Новое путешествие"
                @handler="openCreateTravelModal"
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
                @handler="openCreateTravelModal"
            />
        </div>
    </div>
</template>
