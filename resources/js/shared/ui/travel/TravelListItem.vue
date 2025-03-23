
<script setup lang="ts">
// TODO: использование в компонентах этот компонент:
// <div v-for="item in travels" :key="item.id">
//     <TravelListItem
//          :item="item"
//          :change-id="changeId"
//          :change-travel="changeTravel"
//          @update="updateTravel"
//          @delete="deleteTravel"
//          @save="saveTravel"
//          @update:changeTravel="val => changeTravel = val"
//     />
// </div>

// TODO: Что-то тут не работает, написать заново.
// import { computed, ref } from 'vue'
// import { mdiPencil, mdiDelete } from '@mdi/js'
// import Icon from "@/shared/ui/Icon.vue"
// import Modal from '@/shared/ui/Modal.vue'
// import TravelForm from "@/widgets/travel/ui/TravelForm.vue"
// import type { travelData } from "@/app/types/types"
//
// const props = defineProps({
//     item: {
//         type: Object as () => travelData,
//         required: true
//     },
//     changeId: {
//         type: Number,
//         default: null
//     },
//     changeTravel: {
//         type: Object as () => travelData,
//         required: true
//     }
// })
//
// const emit = defineEmits([
//     'update:changeTravel',
//     'update',
//     'delete',
//     'save'
// ])
//
// const localTravel = ref({...props.changeTravel})
//
// const isEditing = computed(() => props.changeId === props.item.id)
//
// const handleEdit = () => {
//     emit('update', props.item)
// }
//
// const handleDelete = () => {
//     emit('delete', props.item.id)
// }
//
// const handleSave = () => {
//     emit('save', localTravel.value)
// }
//
// const closeModal = () => {
//     emit('update:changeTravel', null)
// }
</script>

<template>
<!--    <div class="card">-->
<!--        <div v-if="!isEditing" class="grid gap-2 grid-cols-7">-->
<!--            <div>{{ item.place }}</div>-->
<!--            <div>{{ item.date }}</div>-->
<!--            <div>{{ item.mode_of_transport }}</div>-->
<!--            <div>{{ item.good_impression }}</div>-->
<!--            <div>{{ item.bad_impression }}</div>-->
<!--            <div>{{ item.general_impression }}</div>-->
<!--            <div class="flex gap-2 justify-end">-->
<!--                <div @click="handleEdit" class="cursor-pointer">-->
<!--                    <Icon-->
<!--                        :iconPath="mdiPencil"-->
<!--                        class="w-6 h-6 text-secondary hover:text-dark cursor-pointer"-->
<!--                    />-->
<!--                </div>-->
<!--                <div @click="handleDelete" class="cursor-pointer">-->
<!--                    <Icon-->
<!--                        :iconPath="mdiDelete"-->
<!--                        class="w-6 h-6 text-secondary hover:text-dark cursor-pointer"-->
<!--                    />-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

<!--        <Modal :isOpen="isEditing" @close="closeModal">-->
<!--            <TravelForm-->
<!--                v-model="localTravel"-->
<!--                @handler="handleSave"-->
<!--                :btn-text="'сохранить'"-->
<!--            />-->
<!--        </Modal>-->
<!--    </div>-->
</template>


