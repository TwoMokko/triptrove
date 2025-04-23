<script setup lang="ts">
import { computed, ref } from "vue"
import { useTravelsStore } from "@/etities/travel"
import { travelData } from "@/app/types/types"
import TravelListItem from './TravelListItem.vue'

const travelsStore = useTravelsStore()

const draggedItem = ref<travelData | null>(null)
const dropTarget = ref<travelData | null>(null)

const sortedTravels = computed(() => {
    return [...travelsStore.travels].sort((a, b) => a.order - b.order)
})

const handleDragStart = (e: DragEvent, item: travelData) => {
    draggedItem.value = item
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

    if (!draggedItem.value || draggedItem.value.id === targetItem.id) return

    const items = [...travelsStore.travels];
    const draggedIndex = items.findIndex(i => i.id === draggedItem.value?.id)
    const targetIndex = items.findIndex(i => i.id === targetItem.id)

    if (draggedIndex === - 1 || targetIndex === - 1) return

    const [removed] = items.splice(draggedIndex, 1)
    items.splice(targetIndex, 0, removed)

    const updates = items.map((item, index) => ({
        id: item.id,
        order: index + 1
    }))

   try {
       await travelsStore.updateTravelsOrder(updates)
   }
   catch (err) {
       alert(err)
   }
}
</script>

<template>
    <TravelListItem
        v-for="item in sortedTravels"
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
