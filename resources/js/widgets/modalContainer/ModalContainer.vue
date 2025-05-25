<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useModalStore } from '@/app/store/modal'
import Modal from '@/shared/ui/modal/Modal.vue'

const modalStore = useModalStore()
const { modals } = storeToRefs(modalStore)

const handleClose = (modalId: string) => {
    modalStore.closeModal(modalId)
}

const handleToggleCollapse = (modalId: string) => {
    modalStore.toggleCollapse(modalId)
}

</script>

<template>
    <div class="">
        <template v-for="modal in modals" :key="modal.id">
            <Modal
                :modalId="modal.id"
                :isOpen="true"
                :isCollapsed="modal.isCollapsed || false"
                @close="() => handleClose(modal.id)"
                @toggle-collapse="() => handleToggleCollapse(modal.id)"
                :previewText="modal.props.previewText"
                :title="modal.props.title"
                :isCollapsible="modal.props.isCollapsible"
            >
                <component
                    :is="modal.component"
                    v-bind="modal.props"
                    @handler="modal.props.onHandler"
                />
            </Modal>
        </template>
    </div>
</template>
