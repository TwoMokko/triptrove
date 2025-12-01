<script setup lang="ts">
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
import { markRaw, ref, watch } from "vue"
import TravelForm from "@/widgets/travel/ui/TravelForm.vue"
import { Travel } from "@/shared/types/api"
import { useTravelsStore } from "@/features/travels/model/store"
import { useUsersStore } from "@/entities/user/model/store"
import { useModal } from "@/shared/lib/useModal"
import TravelList from "@/widgets/travel/ui/TravelList.vue"

const { openModal, closeModal, updateModalProps } = useModal()
const usersStore = useUsersStore()
const travelsStore = useTravelsStore()

const newTravel = ref<Omit<Travel, 'id'>>({ users: [] })

watch(newTravel, (value) => {
  updateModalProps('create-travel', { modelValue: value })
}, { deep: true })

const createTravel = (travel: Travel): void => {
  newTravel.value = travel
  travelsStore.addTravel({ ...newTravel.value, user_id: usersStore.currentUser.id })
  newTravel.value = { users: [] }

  console.log('add: ', travelsStore.myTravels)
  closeModal('create-travel')
}

const openCreateTravelModal = () => {
  openModal('create-travel', markRaw(TravelForm), {
    modelValue: newTravel.value,
    onHandler: createTravel,
    btnText: 'Добавить путешествие',
    isCollapsible: false,
    previewText: travelsStore.currentTravel?.place ?? 'Новое путешествие',
    title: travelsStore.currentTravel?.place ?? 'Новое путешествие',
  })
}
</script>

<template>
  <div v-if="!travelsStore.hasTravels" class="mb-4">
    <div>Нет созданных путешествий</div>
    <div class="text-end md:w-[300px] md:ml-auto">
      <ButtonCustom
          text="Новое путешествие"
          @handler="openCreateTravelModal"
      />
    </div>
  </div>

  <div v-else>
    <div class="mb-4">
      <TravelList :travels="travelsStore.myTravels" list-type="personal" />
    </div>
    <div class="text-end md:w-[300px] md:ml-auto">
      <ButtonCustom
          text="Новое путешествие"
          @handler="openCreateTravelModal"
      />
    </div>
  </div>
</template>
