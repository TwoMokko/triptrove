<script setup lang="ts">
import { computed, ref } from "vue"
import { Travel } from "@/shared/types/api"
import TravelListItem from './TravelListItem.vue'
import { useTravelsStore } from "@/features/travels/model/store"
import { useUsersStore } from "@/entities/user/model/store"

interface Props {
    travels: Travel[],
    listType?: 'personal' | 'shared',
    creatorId?: number
}
const props = defineProps<Props>()

const travelsStore = useTravelsStore()
const usersStore = useUsersStore()

const isDnD = computed(() => props.listType === 'personal')

const dropTarget = ref<Travel | null>(null)
const draggedItem = ref<{
    data: Travel,
    sourceList: 'personal' | 'shared'
} | null>(null)


// const touchStartY = ref(0)
// const isDragging = ref(false)
// const dragElement = ref<HTMLElement | null>(null)

const handleDragStart = (e: DragEvent | PointerEvent, item: Travel) => {
    draggedItem.value = {
        data: item,
        sourceList: props.listType || 'personal'
    }

    if (e instanceof DragEvent) {
      e.dataTransfer?.setData('text/plain', item.id.toString());
    }
    (e.target as HTMLElement).classList.add('dragging')

    // isDragging.value = true
    // dragElement.value = target
}

const handleDragOver = (e: DragEvent | PointerEvent, item: Travel) => {
    e.preventDefault()
    dropTarget.value = item
}

const handleDragEnter = (e: DragEvent | PointerEvent, item: Travel) => {
    e.preventDefault();
    (e.currentTarget as HTMLElement).classList.add('drag-over')
}

const handleDragEnd = (e: DragEvent | PointerEvent) => {
    (e.target as HTMLElement).classList.remove('dragging')
    document.querySelectorAll('.drag-over').forEach(el => {
        el.classList.remove('drag-over')
    })
    draggedItem.value = null
    dropTarget.value = null

    // isDragging.value = false
    // dragElement.value = null
}

const handleDrop = async (e: DragEvent | PointerEvent, targetItem: Travel) => {
  e.preventDefault();
  (e.currentTarget as HTMLElement).classList.remove('drag-over')

  if (!draggedItem.value || !targetItem) return

  if (draggedItem.value.sourceList === props.listType) {
    const items = [...props.travels]
    const draggedIndex = items.findIndex(i => i.id === draggedItem.value?.data.id)
    const targetIndex = items.findIndex(i => i.id === targetItem.id)

    if (draggedIndex === -1 || targetIndex === -1) return

    // Проверяем, изменился ли порядок
    if (draggedIndex === targetIndex) {
      console.log('Порядок не изменился, запрос не отправляется')
      return // ← Выходим если порядок не изменился
    }

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


// Специфичные функции для touch-событий
// const handleTouchStart = (e: TouchEvent, item: Travel) => {
//   e.preventDefault()
//   const touch = e.touches[0]
//   touchStartY.value = touch.clientY
//   handleDragStart(e as any, item)
// }
//
// const handleTouchMove = (e: TouchEvent, item: Travel) => {
//   if (!draggedItem.value) return
//
//   e.preventDefault()
//   const touch = e.touches[0]
//   const currentY = touch.clientY
//
//   // Эмуляция dragOver для touch
//   handleDragOver(e as any, item)
//
//   // Визуальное перемещение элемента (опционально)
//   if (dragElement.value) {
//     const deltaY = currentY - touchStartY.value
//     dragElement.value.style.transform = `translateY(${deltaY}px)`
//   }
// }
//
// const handleTouchEnd = (e: TouchEvent) => {
//   if (!draggedItem.value) return
//
//   e.preventDefault()
//
//   // Сброс трансформации
//   if (dragElement.value) {
//     dragElement.value.style.transform = ''
//   }
//
//   // Завершение перетаскивания
//   if (dropTarget.value && draggedItem.value) {
//     handleDrop(e as any, dropTarget.value)
//   }
//
//   handleDragEnd(e as any)
// }


// Универсальный обработчик pointer down (работает и для мыши и для touch)
// const handlePointerDown = (e: PointerEvent, item: Travel) => {
//   if (e.pointerType === 'touch' || e.pointerType === 'mouse') {
//     // Общая логика для начала перетаскивания
//     draggedItem.value = {
//       data: item,
//       sourceList: props.listType || 'personal'
//     }
//
//     touchStartY.value = e.clientY
//     isDragging.value = true
//     dragElement.value = e.currentTarget as HTMLElement
//     ;(e.currentTarget as HTMLElement).classList.add('dragging')
//
//     // Для мыши также устанавливаем drag data
//     if (e.pointerType === 'mouse' && e instanceof DragEvent) {
//       e.dataTransfer?.setData('text/plain', item.id.toString())
//     }
//   }
// }
</script>

<template>
    <TravelListItem
        v-for="item in props.travels"
        :key="item.id"
        :item="item"
        :draggable="isDnD"
        :class="{ 'cursor-grab': isDnD }"
        @dragover.prevent="handleDragOver($event, item)"
        @dragstart="handleDragStart($event, item)"
        @dragenter.prevent="handleDragEnter($event, item)"
        @dragend="handleDragEnd"
        @drop="handleDrop($event, item)"
    />
</template>
