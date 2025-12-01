<script setup lang="ts">
import { nextTick, ref, watch } from "vue";
import Icon from "@/shared/ui/Icon.vue";
import { mdiPencil, mdiCheck, mdiClose } from '@mdi/js'

interface Props {
  modelValue: string
  label: string
  type?: string
  placeholder?: string
  disabled?: boolean
  validator?: (value: string) => string
  required?: boolean
  onSave?: (value: string) => Promise<void>
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: '',
  type: 'text',
  disabled: false,
  required: false
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const isEditing = ref<boolean>(false)
const localValue = ref<string>(props.modelValue)
const errorMessage = ref<string>('')
const isSaving = ref<boolean>(false)
const inputRef = ref<HTMLInputElement>()


const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Enter') {
    event.preventDefault()
    save()
  } else if (event.key === 'Escape') {
    event.preventDefault()
    cancel()
  }
}
const save = async () => {
  if (props.validator) {
    const error = props.validator(localValue.value)
    if (error) {
      errorMessage.value = error
      return
    }
  }

  if (props.required && !localValue.value.trim()) {
    errorMessage.value = 'Это поле обязательно'
    return
  }

  if (localValue.value === props.modelValue) {
    isEditing.value = false
    return
  }

  isSaving.value = true
  errorMessage.value = ''
  if (props.onSave) {
    try {
      await props.onSave(localValue.value)
      isEditing.value = false
    } catch (err: any) {
      errorMessage.value = err.message || 'Ошибка при сохранении'
    } finally {
      isSaving.value = false
    }
  }

}
const cancel = () => {
  localValue.value = props.modelValue
  isEditing.value = false
  errorMessage.value = ''
}

const startEditing = () => {
  if (props.disabled) return

  isEditing.value = true
  errorMessage.value = ''

  nextTick(() => {
    inputRef.value?.focus()
    inputRef.value?.select()
  })
}

watch(() => props.modelValue, (newValue) => {
  localValue.value = newValue
})

watch(isEditing, (editing) => {
  if (isEditing) {
    nextTick(() => inputRef.value?.focus())
  }
})
</script>

<template>
  <div class="transition-all">
    <label
        class="block text-sm font-medium text-primary"
        :class="{ 'text-red-600': errorMessage }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    <div>
      <!--      редактирование-->
      <div v-if="isEditing" class="flex-1 flex items-center gap-2">
        <input
            ref="inputRef"
            v-model="localValue"
            @keydown="handleKeydown"
            :type="type"
            :placeholder="placeholder"
            :disabled="disabled || isSaving"
            class="flex-1 focus:outline-none transition-all bg-transparent"
            :class="[
              errorMessage
                ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
                : '',
              isSaving ? 'opacity-70' : ''
          ]"
        >
        <button
            @click="save"
            :disabled="disabled || isSaving"
            class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors disabled:opacity-50"
            title="Сохранить"
        >
          <Icon
              :iconPath="mdiCheck"
              class="w-4 h-4"
          />
        </button>
        <button
            @click="cancel"
            :disabled="disabled || isSaving"
            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-50"
            title="Отменить"
        >
          <Icon :iconPath="mdiClose" class="w-4 h-4" />
        </button>
        <span v-if="isSaving">...</span>
      </div>
      <!--      просмотр-->
      <div v-else class="flex-1 flex items-center gap-2 group">
        <span
            @dblclick="startEditing"
        >
          {{ modelValue || placeholder }}
        </span>
        <button
            v-if="!disabled"
            @click="startEditing"
            class="opacity-0 group-hover:opacity-100 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all"
            title="Редактировать"
        >
          <Icon :iconPath="mdiPencil" class="w-4 h-4" />
        </button>
      </div>

      <!--      ошибка-->
      <div
          v-if="errorMessage"
          class="text-red-600 text-sm mt-1 flex items-center gap-1 animate-fade-in"
      >
        <span>⚠</span>
        <span>{{ errorMessage }}</span>
      </div>
    </div>
  </div>
</template>