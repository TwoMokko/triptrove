import { defineStore } from 'pinia'
import { ref } from 'vue'

interface Modal {
    id: string
    component: any
    props?: Record<string, any>
    isCollapsed?: boolean
}

export const useModalStore = defineStore('modal', () => {
    const modals = ref<Modal[]>([])

    const openModal = (id: string, component: any, props?: Record<string, any>) => {
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
        }
    }

    return { modals, openModal, closeModal, toggleCollapse }
})
