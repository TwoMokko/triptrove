import { defineStore } from 'pinia'
import { Component, ref } from 'vue'

interface Modal {
    id: string
    component: Component
    props?: Record<string, any>
    isCollapsed?: boolean
}

export const useModalStore = defineStore('modal', () => {
    const modals = ref<Modal[]>([])

    const openModal = (id: string, component: Component, props?: Record<string, any>) => {
        if (!modals.value.some(m => m.id === id)) {
            modals.value.push({ id, component, props, isCollapsed: false })
        }
    }

    const closeModal = (id: string) => {
        modals.value = modals.value.filter(m => m.id !== id)
    }

    const toggleCollapse = (id: string) => {
        const modal = modals.value.find(m => m.id === id)
        if (modal) {
            modal.isCollapsed = !modal.isCollapsed

            // Сохраняем данные при сворачивании
            if (modal.isCollapsed && modal.component.props?.modelValue) {
                modal.tempData = {...modal.component.props.modelValue}
            }
        }
    }

    const updateModalProps = (id: string, newProps: Record<string, any>) => {
        const modal = modals.value.find(m => m.id === id)
        // if (modal) {
        //     modal.props = { ...modal.props, ...newProps }
        // }
        if (modal) {
            modal.props.modelValue = { ...newProps }
        }
    }

    return { modals, openModal, closeModal, toggleCollapse, updateModalProps }
})
