import { useModal } from "./useModal"
import ConfirmModal from "../ui/modal/ConfirmModal.vue"


export const useConfirm = () => {
    const { open } = useModal()

    const confirm = (options: {
        title?: string
        message: string
        onConfirm: () => void
    }) => {
        open(ConfirmModal, {
            title: options.title,
            message: options.message,
            onConfirm: options.onConfirm
        })
    }

    return { confirm }
}
