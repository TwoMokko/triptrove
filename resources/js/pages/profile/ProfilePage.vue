<script setup lang="ts">
import { useUsersStore } from "@/entities/user/model/store"
import { computed } from "vue"
import Loader from "@/shared/ui/Loader.vue"
import FileUploader from "@/shared/ui/FileUploader.vue"
import EditableField from "@/shared/ui/EditableField.vue"

const userStore = useUsersStore()
const currentUser = computed(() => userStore.currentUser)
const isLoading = computed(() => userStore.currentUser === null)

const avatarSrc = computed(() => {
  if (currentUser.value?.avatar) {
    return `/storage/${currentUser.value.avatar}`
  }
  return '/storage/users/avatars/default-user.svg'
})

const validateName = (name: string) => {
  if (!name.trim()) return 'Имя не может быть пустым'
  if (name.length < 2) return 'Минимум 2 символа'
  if (name.length > 50) return 'Максимум 50 символов'
  return ''
}
const saveName = async (newName: string) => {
  try {
    await userStore.updateName(newName)
  } catch (err) {
    let errorMessage = 'Ошибка при сохранении имени'
    throw new Error(errorMessage)
  }
}
const validateLogin = (login: string) => {
  if (!login.trim()) return 'Логин не может быть пустым'
  if (login.length < 3) return 'Логин должен содержать минимум 3 символа'
  if (!/^[a-zA-Z0-9_]+$/.test(login)) return 'Логин может содержать только буквы, цифры и подчеркивания'
  return ''
}
const saveLogin = async (newLogin: string) => {
  try {
    await userStore.updateLogin(newLogin)
  } catch (err) {
    let errorMessage = 'Ошибка при сохранении логина'

    if (err.response?.status === 422) {
      errorMessage = 'Такой логин уже существует'
    }

    throw new Error(errorMessage)
  }
}
</script>

<template>
  <Loader v-if="isLoading" />
  <div v-else class="px-[4%] md:px-[10%] py-10">
    <div class="flex flex-col md:flex-row gap-6 items-start">
      <FileUploader
          target="user"
          folder="avatars"
          db-field="avatar"
          :src="avatarSrc"
          :class-name="'w-40 h-40 rounded-full'"
      />
      <div class="md:w-96 flex-1 max-w-md space-y-2">


          <EditableField
              :model-value="userStore.currentUser?.name || ''"
              label="Имя"
              placeholder="Введите ваше имя"
              :validator="validateName"
              :on-save="saveName"
              @save-error="handleSaveError"
          />

          <EditableField
              :model-value="userStore.currentUser?.login || ''"
              label="Логин"
              placeholder="Введите логин"
              :validator="validateLogin"
              :on-save="saveLogin"
              @save-error="handleSaveError"
          />

          <!-- Поля только для чтения -->
          <div>
            <label class="block text-sm font-medium text-primary">
              Email
            </label>
            <div>
              {{ userStore.currentUser?.email }}
            </div>
          </div>

      </div>
    </div>
  </div>
</template>
