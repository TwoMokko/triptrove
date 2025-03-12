<script setup lang="ts">
import {defineProps, defineEmits, ref} from 'vue';
import { mdiWindowClose, mdiMinus } from '@mdi/js'
import Icon from "@/shared/ui/Icon.vue";

// Принимаем пропс `isOpen` для управления видимостью модального окна
// const props = defineProps({
//     isOpen: {
//         type: Boolean,
//         required: true,
//     },
// })

defineProps<{
    isOpen: boolean,
}>()

// Определяем событие для закрытия модального окна
const emit = defineEmits(['close'])

const closeModal = () => {
    emit('close')
}

const beforeCloseModal = () => {
    isCollapsed.value = false
    closeModal()
}

const isCollapsed = ref<boolean>(false)
</script>

<template>
    <div v-if="isOpen" class="modal-overlay" :class="{ collapsed: isCollapsed }" @click="() => console.log('можно вызвать closeModal, чтобы закрывать, нажимая вне окна, но тогда не получится при сворачивании пользоваться')">
        <div class="modal-wrap" @click.stop>
            <div class="flex gap-4 justify-end">
<!--                Доработать, чтобы свернутые окна выстраивались в линию, еще чтобы при редактировании значения не исчезали из карточки путешествия, еще добавить заголовок (чтобы отличать свернутые окна друг от друга)-->
<!--                <Icon-->
<!--                    :iconPath="mdiMinus"-->
<!--                    class="w-6 h-6 text-secondary hover:text-dark cursor-pointer"-->
<!--                    @click="() => isCollapsed = !isCollapsed" />-->
                <Icon
                    :iconPath="mdiWindowClose"
                    class="w-6 h-6 text-secondary hover:text-dark cursor-pointer"
                    @click="beforeCloseModal" />
            </div>
            <div class="mt-4 modal-content">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    display: flex;
    justify-content: center;
    align-items: center;

    &.collapsed {
        bottom: 2rem;
        right: 2rem;
        width: fit-content;
        height: fit-content;
        background-color: transparent;

        .modal-wrap {
            padding: 10px 30px;

            .modal-content {
                display: none;
            }
        }
    }

    &:not(.collapsed) {
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #00000015;

        .modal-wrap {
            padding: 40px 60px;
        }
    }
}

.modal-wrap {
    border-radius: 20px;
    background: linear-gradient(135deg, #e0e7ff 0%, #f0f4ff 100%);
    filter: blur(80);
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.06);
    min-width: 50%;
}
</style>
