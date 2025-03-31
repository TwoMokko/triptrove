<script setup lang="ts">
import { ref, watch } from "vue"
import { travelData } from "@/app/types/types"
import InputCustom from "@/shared/ui/InputCustom.vue"
import TextareaCustom from "@/shared/ui/TextareaCustom.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    btnText: {
        type: String,
        required: false
    }
})

// Определяем событие для обновления объекта
const emit = defineEmits(['update:modelValue', 'handler'])

// Создаем локальную копию объекта для редактирования
const localTravel = ref<travelData>({ ...props.modelValue })

// Обновляем родительский объект при изменении локального
const updateModel = () => {
    console.log('values: ', localTravel.value)
    emit('update:modelValue', localTravel.value)
}

// Следим за изменениями пропса `modelValue` (на случай, если он изменится извне)
watch(
    () => props.modelValue,
    (newValue) => {
        localTravel.value = { ...newValue }
    },
    { deep: true }
)

</script>

<template>
    <div class="flex flex-col gap-3 mb-4">
        <InputCustom v-model:value="localTravel.place" :placeholder="'место'" :type="'text'" @input="updateModel" />
        <InputCustom v-model:value="localTravel.date" :placeholder="'время когда'" :type="'text'" @input="updateModel" />
        <InputCustom v-model:value="localTravel.mode_of_transport" :placeholder="'на чем добирались'" :type="'text'" @input="updateModel" />
        <TextareaCustom v-model:text="localTravel.good_impression" :placeholder="'хорошее'" @change="updateModel" />
        <TextareaCustom v-model:text="localTravel.bad_impression" :placeholder="'плохое'" @change="updateModel" />
        <TextareaCustom v-model:text="localTravel.general_impression" :placeholder="'общие впечатления'" @change="updateModel" />
    </div>
    <div class="text-end mt-4">
        <ButtonCustom :text="btnText" @click="$emit('handler')" class="w-full" />
    </div>
</template>
