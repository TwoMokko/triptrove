<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useModalStore } from "@/shared/ui/modal"
import Modal from "@/shared/ui/modal/ui/Modal.vue"

const modalStore = useModalStore()
const { modals } = storeToRefs(modalStore)

const handleClose = (modalId: string) => {
    modalStore.closeModal(modalId)
}

const handleToggleCollapse = (modalId: string) => {
    modalStore.toggleCollapse(modalId)
}

const getComponentProps = (props: any) => {
  if (!props) return {}

  const { title, previewText, isCollapsible, ...componentProps } = props
  return componentProps
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
                    v-bind="getComponentProps(modal.props)"
                    @handler="modal.props.onHandler"
                />
            </Modal>
        </template>
    </div>
</template>
