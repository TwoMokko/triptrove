<script setup lang="ts">
import { mdiPencil, mdiDelete, mdiLock, mdiLockOpenVariant } from '@mdi/js'
import Icon from "@/shared/ui/Icon.vue"
import Modal from '@/shared/ui/Modal.vue'
import TravelForm from "@/widgets/travel/ui/TravelForm.vue"
import type { travelData } from "@/app/types/types"
import { useTravelsStore } from "@/etities/travel"
import { useUsersStore } from "@/etities/user"
import { storeToRefs } from "pinia"
import { computed } from "vue"

const props = defineProps<{
    item: travelData
}>()

const travelsStore = useTravelsStore()
const { currentUser } = storeToRefs(useUsersStore())

const isDragging = computed(() => false)

const handleMouseDown = (e: MouseEvent) => {
    e.stopPropagation()
}
const handleEdit = (e: Event) => {
    e.stopPropagation()
    travelsStore.setCurrentTravel({ ...props.item })
}

const handleDelete = async () => {
    if (confirm('Вы уверены, что хотите удалить это путешествие?')) {
        try {
            await travelsStore.removeTravel(props.item.id, currentUser.value.id)
        }
        catch (error) {
            alert(error.response.data.message)
        }
    }
}

const handleSave = async () => {
    if (travelsStore.currentTravel) {

        console.log('save: ', travelsStore.currentTravel)

        await travelsStore.editTravel(
            travelsStore.currentTravel.id,
            travelsStore.currentTravel,
            currentUser.value.id
        )
        travelsStore.setCurrentTravel(null)
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
                <button @click="handleDelete" class="cursor-pointer" title="удалить">
                    <Icon
                        :iconPath="mdiDelete"
                        class="w-6 h-6 text-secondary hover:text-dark"
                    />
                </button>
            </div>
        </div>

        <Modal
            :isOpen="travelsStore.currentTravel?.id === item.id"
            @close="travelsStore.setCurrentTravel(null)"
        >
            <TravelForm
                v-model="travelsStore.currentTravel"
                @handler="handleSave"
                :btn-text="'Сохранить'"
            />
        </Modal>
    </div>
</template>

<style scoped>
.dragging {
    @apply opacity-75;
}
</style>
