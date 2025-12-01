<script setup lang="ts">
import { ref, watch, computed, onBeforeUnmount, onMounted } from "vue"
import { debounce } from "@/shared/lib/debounce"
import Loader from "@/shared/ui/Loader.vue"
import Icon from "@/shared/ui/Icon.vue"
import { mdiMenuDown } from "@mdi/js"
import { useUsersStore } from "@/entities/user/model/store";
import { useTravelsStore } from "@/features/travels/model/store";
import { userShort } from "@/shared/types/api";
import TravelList from "@/widgets/travel/ui/TravelList.vue";

const usersStore = useUsersStore()
const travelsStore = useTravelsStore()

const selectedGroup = ref<number[]>([])
const selectedString = ref<string>('Выбрать группу')
const isUpdatingSharedTravels = ref(false)
const isOpenSelect = ref(false)

const selectContainerRef = ref<HTMLElement | null>(null)


const handleClickOutside = (event: MouseEvent) => {
  if (selectContainerRef.value && !selectContainerRef.value.contains(event.target as Node)) {
    isOpenSelect.value = false
  }
}

// Получаем уникальные группы пользователей (вообще это происходит на беке, но если нужна доп проверка)
const uniqueGroups = computed(() => {
  // const groupsMap = new Map<string, any[]>()
  //
  // travelsStore.usersFriend.forEach(group => {
  //     // Фильтруем текущего пользователя из группы
  //     const filteredGroup = group.filter(user => user.id !== usersStore.currentUser.id)
  //     if (filteredGroup.length === 0) return;
  //
  //     // Создаем ключ для группы (отсортированные ID)
  //     const groupKey = filteredGroup.map(u => u.id).sort().join(",")
  //
  //     // Сохраняем только уникальные группы
  //     if (!groupsMap.has(groupKey)) {
  //         groupsMap.set(groupKey, filteredGroup)
  //     }
  // })
  //
  // return Array.from(groupsMap.values())
  console.log('here', travelsStore.usersFriend)
  return travelsStore.usersFriend
})

// Обработчик выбора группы
const selectGroup = (group: userShort[]) => {
  selectedString.value = group.length > 0 ? group.map(u => u.login).join(', ') : 'Выбрать группу'

  const groupIds = group.map(u => u.id)

  if (selectedGroup.value.length === groupIds.length &&
      selectedGroup.value.sort().every((id, i) => id === groupIds.sort()[i])) {
    // Если выбрана та же группа - снимаем выбор
    selectedGroup.value = []
  } else {
    // Выбираем новую группу
    selectedGroup.value = groupIds
  }

  isOpenSelect.value = false
}

// Добавляем текущего пользователя при выборе группы
watch(selectedGroup, (newVal) => {
  if (newVal.length > 0 && !newVal.includes(usersStore.currentUser.id)) {
    selectedGroup.value = [...newVal, usersStore.currentUser.id]
  } else if (newVal.length === 0) {
    selectedGroup.value = []
  }
}, { deep: true })

// Запрос к API при изменении выбранной группы
watch(selectedGroup, debounce(async (newVal) => {
  if (isUpdatingSharedTravels.value) return

  isUpdatingSharedTravels.value = true;
  await travelsStore.getTravelsWithUsers(newVal)
  isUpdatingSharedTravels.value = false
}, 300), { deep: true })


onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>

<template>
  <div v-if="travelsStore.hasUsersFriend">
    <div class="flex gap-4 mb-4 items-center">
      <h3 class="text-xl">Группы:</h3>
      <div class="relative" ref="selectContainerRef">
        <div
            @click="isOpenSelect = !isOpenSelect"
            class="flex justify-between py-2 px-4 w-60 border border-primary cursor-pointer"
        >
          <span>{{ selectedString }}</span>
          <Icon
              :iconPath="mdiMenuDown"
              class="w-6 h-6 text-primary"
          />
        </div>
        <div
            v-show="isOpenSelect"
            class="absolute top-full left-0 right-0 flex flex-col gap-2 p-2 border border-gray-200 shadow-lg z-10 bg-gradient-to-br from-indigo-50 to-blue-50"
            @click.stop
        >
          <label
              v-for="(group, index) in uniqueGroups"
              :key="index"
              class="cursor-pointer hover:bg-white p-2 rounded"
          >
            <input
                type="radio"
                :checked="selectedGroup.length === group.length && selectedGroup.sort().every((id, i) => id === group.map(u => u.id).sort()[i])"
                @change="selectGroup(group)"
                class="hidden"
                name="userGroup"
            />
            <span class="flex gap-2 items-center">
                            {{ group.map(user => user.login).join(', ') }}
                        </span>
          </label>
        </div>
      </div>
    </div>

    <div v-if="isUpdatingSharedTravels">
      <Loader />
    </div>

    <template v-else>
      <div v-if="travelsStore.travelsWithUsers === null" class="text-gray-500">
        Выберите группу пользователей для просмотра совместных путешествий
      </div>

      <template v-else>
        <div v-if="travelsStore.hasFriendsTravels" class="mb-4">
          <TravelList :travels="travelsStore.travelsWithUsers" list-type="shared" />
        </div>
        <div v-else class="text-gray-500">
          Нет общих путешествий с выбранной группой пользователей
        </div>
      </template>
    </template>
  </div>

  <div v-else class="text-gray-500">
    У вас пока нет друзей для совместных путешествий
  </div>
</template>
