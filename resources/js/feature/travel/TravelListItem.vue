<script setup lang="ts">
import { mdiPencil, mdiDelete, mdiLock, mdiLockOpenVariant } from '@mdi/js'
import Icon from "@/shared/ui/Icon.vue"
import TravelForm from "@/widgets/travel/ui/TravelForm.vue"
import type { travelData } from "@/app/types/types"
import { useTravelsStore } from "@/entities/travel"
import { useUsersStore } from "@/entities/user"
import { storeToRefs } from "pinia"
import { computed, markRaw } from "vue"
import { useModal } from '@/shared/lib/useModal'
import { useConfirm } from "@/shared/lib/useConfirm"

const props = defineProps<{
    item: travelData
}>()

const travelsStore = useTravelsStore()
const { currentUser } = storeToRefs(useUsersStore())
const { openModal, closeModal } = useModal()
const { confirm } = useConfirm()

const isDragging = computed(() => false)

const handleMouseDown = (e: MouseEvent) => {
    e.stopPropagation()
}

const handleEdit = (e: Event) => {
    e.stopPropagation()
    travelsStore.setCurrentTravel({ ...props.item })
    openModal(`edit-travel-${props.item.id}`, markRaw(TravelForm), {
        modelValue: travelsStore.currentTravel,
        onHandler: handleSave,
        btnText: 'Сохранить',
        isCollapsible: false,
        previewText: travelsStore.currentTravel.place,
        title: '',
    })
}

const handleDelete = async () => {
    const isConfirmed = await confirm({
        title: 'Вы уверены, что хотите удалить это путешествие?',
        // message: 'Вы уверены, что хотите удалить это путешествие?',
    })

    if (isConfirmed) {
        try {
            await travelsStore.removeTravel(props.item.id, currentUser.value.id)
        } catch (error) {
            alert(error.response?.data?.message || 'Ошибка при удалении')
        }
    }
}

const handleSave = async (travel: travelData) => {
    console.log('aaa: ', travelsStore.currentTravel)

    travelsStore.currentTravel = travel

    if (travelsStore.currentTravel) {
        try {
            await travelsStore.editTravel(
                travelsStore.currentTravel.id,
                travelsStore.currentTravel,
                currentUser.value.id
            )
            travelsStore.setCurrentTravel(null)
            closeModal(`edit-travel-${props.item.id}`)
        } catch (error) {
            console.error('Ошибка при сохранении:', error)
        }
    }
}
</script>

<template>
    <div
        :class="{ 'dragging': isDragging }"
        class="overflow-hidden py-10 px-14 bg-[#ffffff15] rounded-2xl shadow-[0_0_20px_rgba(0,0,0,0.06)] mb-2.5 hover:bg-[#ffffff50] transition-all ease-in duration-200"
        @mousedown="handleMouseDown"
    >
        <div class="grid gap-2 grid-cols-10">
            <div>
                <Icon
                    :iconPath="item.published ? mdiLockOpenVariant : mdiLock"
                    class="w-6 h-6 text-secondary hover:text-dark"
                />
            </div>
            <div>{{ item.place }}</div>
            <div>{{ item.when }}</div>
            <div>{{ item.amount }}</div>
            <div>{{ item.mode_of_transport }}</div>
            <div>{{ item.accommodation }}</div>
            <div>{{ item.advice }}</div>
            <div>{{ item.entertainment }}</div>
            <div>{{ item.general_impression }}</div>
            <div class="flex gap-2 justify-end">
                <button @click="handleEdit" class="cursor-pointer" title="редактировать">
                    <Icon
                        :iconPath="mdiPencil"
                        class="w-6 h-6 text-secondary hover:text-dark"
                    />
                </button>
                <button
                    @click="item.user_id === currentUser.id && handleDelete()"
                    :class="[
                        item.user_id === currentUser.id
                            ? 'cursor-pointer group'
                            : 'opacity-30 cursor-not-allowed'
                    ]"
                    :title="item.user_id === currentUser.id ? 'удалить' : 'нет прав для удаления'"
                >
                    <Icon
                        :iconPath="mdiDelete"
                        class="w-6 h-6 text-secondary group-hover:text-dark"
                    />
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.dragging {
    @apply opacity-75;
}
</style>
