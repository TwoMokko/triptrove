<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { travelData } from "@/app/types/types"
import InputCustom from "@/shared/ui/InputCustom.vue"
import TextareaCustom from "@/shared/ui/TextareaCustom.vue"
import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
import UsersSharedList from "@/feature/user/ui/UsersSharedList.vue"
import Icon from "@/shared/ui/Icon.vue"
import { mdiLock, mdiLockOpenVariant } from '@mdi/js'

// TODO: оптимизировать html разметку

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    btnText: {
        type: String,
        required: false,
        default: 'Сохранить'
    }
})

const emit = defineEmits(['update:modelValue', 'handler'])

const localTravel = ref<travelData>({ ...props.modelValue })

watch(
    () => props.modelValue,
    (newValue) => {
        localTravel.value = { ...newValue }
    },
    { deep: true }
)

const handleUsersUpdate = (updatedUsers) => {
    localTravel.value = {
        ...localTravel.value,
        users: updatedUsers
    }
}

const handleSubmit = () => {
    emit('handler', localTravel.value)
}
</script>

<template>
    <form class="flex flex-col gap-3 mb-4 overflow-y-scroll h-[calc(80vh-24px-80px-1rem-48px)]">
        <div class="flex gap-2">
            <label class="flex items-end gap-2 cursor-pointer">
                <input type="checkbox" v-model="localTravel.published" hidden="hidden" @change="updateModel">
                <Icon
                    :iconPath="localTravel.published ? mdiLockOpenVariant : mdiLock"
                    class="w-6 h-6 text-secondary hover:text-dark"
                />
                <span v-if="localTravel.published">Публичное</span>
                <span v-else>Приватное</span>
            </label>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <h3 class="text-secondary text-sm">место</h3>
                <InputCustom v-model:value="localTravel.place" :placeholder="'место'" :type="'text'" />
            </div>
            <div>
                <h3 class="text-secondary text-sm">время когда</h3>
                <InputCustom v-model:value="localTravel.when" :placeholder="'время когда'" :type="'text'" />
            </div>
            <div>
                <h3 class="text-secondary text-sm">сумма</h3>
                <InputCustom v-model:value="localTravel.amount" :placeholder="'сумма'" :type="'text'" />
            </div>
            <div>
                <h3 class="text-secondary text-sm">на чем добирались</h3>
                <InputCustom v-model:value="localTravel.mode_of_transport" :placeholder="'на чем добирались'" :type="'text'" />
            </div>
            <div>
                <h3 class="text-secondary text-sm">где жили</h3>
                <TextareaCustom v-model:text="localTravel.accommodation" :placeholder="'где жили'" />
            </div>
            <div>
                <h3 class="text-secondary text-sm">совет</h3>
                <TextareaCustom v-model:text="localTravel.advice" :placeholder="'совет'" />
            </div>
            <div>
                <h3 class="text-secondary text-sm">экскурсии и развлечения</h3>
                <TextareaCustom v-model:text="localTravel.entertainment" :placeholder="'экскурсии и развлечения'" />
            </div>
            <div>
                <h3 class="text-secondary text-sm">общие впечатления</h3>
                <TextareaCustom v-model:text="localTravel.general_impression" :placeholder="'общие впечатления'" />
            </div>
        </div>


        <UsersSharedList
            v-model="localTravel.users"
            @update:modelValue="handleUsersUpdate"
        />
    </form>
    <div class="text-end mt-4">
        <ButtonCustom :text="btnText" @click="handleSubmit" class="w-full" />
    </div>
</template>
