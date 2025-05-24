import { useModalStore } from "../../app/store/modal"
import { Component } from "vue"

export const useModal = () => {
    const modalStore = useModalStore()

    const openModal = (id: string, component: Component, props: Record<string, any> = {}) => {
        modalStore.openModal(id, component, props)
    }

    const updateModalProps = (id: string, newProps: Record<string, any>) => {
        modalStore.updateModalProps(id, newProps)
    }

    const closeModal = (id: string) => {
        modalStore.closeModal(id)
    }

    return { openModal, updateModalProps, closeModal }
}
