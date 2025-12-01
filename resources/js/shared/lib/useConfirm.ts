import { useModal } from './useModal'
import { markRaw } from 'vue'
import ConfirmModal from "../ui/modal/ui/ConfirmModal.vue";

export const useConfirm = () => {
    const { openModal, closeModal } = useModal()

    const confirm = (options: {
        title?: string
        message?: string,
        previewText?: string,
        allowCollapse?: boolean
    }): Promise<boolean> => {
        return new Promise((resolve) => {
            openModal('global-confirm', markRaw(ConfirmModal), {
                message: options.message || 'не указано',
                title: options.title || 'подтвердите действие',
                previewText: options.previewText || 'не указано',
                isCollapsible: options.allowCollapse || false,
                onConfirm: () => {
                    closeModal('global-confirm')
                    resolve(true)
                },
                onClose: () => {
                    closeModal('global-confirm')
                    resolve(false)
                }
            })
        })
    }

    return { confirm }
}
