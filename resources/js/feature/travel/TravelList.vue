<script setup lang="ts">
import { computed, ref } from "vue"
import { useTravelsStore } from "@/etities/travel"
import { useUsersStore } from "@/etities/user"
import { travelData } from "@/app/types/types"
import TravelListItem from './TravelListItem.vue'

interface Props {
    travels: travelData[],
    listType?: 'personal' | 'shared',
    creatorId?: number
}
const props = defineProps<Props>()

const travelsStore = useTravelsStore()
const usersStore = useUsersStore()

const dropTarget = ref<travelData | null>(null)
const draggedItem = ref<{
    data: travelData,
    sourceList: 'personal' | 'shared'
} | null>(null)

const handleDragStart = (e: DragEvent, item: travelData) => {
    draggedItem.value = {
        data: item,
        sourceList: props.listType || 'personal'
    }
    e.dataTransfer?.setData('text/plain', item.id.toString());
    (e.target as HTMLElement).classList.add('dragging')
}

const handleDragOver = (e: DragEvent, item: travelData) => {
    e.preventDefault()
    dropTarget.value = item
}

const handleDragEnter = (e: DragEvent, item: travelData) => {
    e.preventDefault();
    (e.currentTarget as HTMLElement).classList.add('drag-over')
}

const handleDragEnd = (e: DragEvent) => {
    (e.target as HTMLElement).classList.remove('dragging')
    document.querySelectorAll('.drag-over').forEach(el => {
        el.classList.remove('drag-over')
    })
    draggedItem.value = null
    dropTarget.value = null
}

const handleDrop = async (e: DragEvent, targetItem: travelData) => {
    e.preventDefault();
    (e.currentTarget as HTMLElement).classList.remove('drag-over')

    if (!draggedItem.value || !targetItem) return

    if (draggedItem.value.sourceList === props.listType) {
        const items = [...props.travels]
        const draggedIndex = items.findIndex(i => i.id === draggedItem.value?.data.id)
        const targetIndex = items.findIndex(i => i.id === targetItem.id)

        if (draggedIndex === -1 || targetIndex === -1) return

        const [removed] = items.splice(draggedIndex, 1)
        items.splice(targetIndex, 0, removed)

        const updates = items.map((item, index) => ({
            id: item.id,
            order: index + 1
        }))

        try {
            await travelsStore.updateTravelsOrder(updates, props.listType === 'shared', props.creatorId ? props.creatorId : usersStore.currentUser.id)
        } catch (err) {
            console.error('Order update failed:', err)
            alert('Не удалось обновить порядок')
        }
    }
}
</script>

<template>
    <TravelListItem
        v-for="item in props.travels"
        :key="item.id"
        :item="item"
        draggable="true"
        @dragstart="handleDragStart($event, item)"
        @dragover.prevent="handleDragOver($event, item)"
        @dragenter.prevent="handleDragEnter($event, item)"
        @dragend="handleDragEnd"
        @drop="handleDrop($event, item)"
    />
</template>
