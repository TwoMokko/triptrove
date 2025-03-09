<template>
    <div v-if="isOpen" class="modal-overlay" @click="closeModal">
        <div class="modal-content" @click.stop>
            <div class="mb-4">
                <Icon
                    :iconPath="mdiWindowClose"
                    class="w-6 h-6 text-secondary hover:text-dark cursor-pointer ml-auto"
                    @click="closeModal" />
            </div>
            <slot></slot>
        </div>
    </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';
import { mdiWindowClose } from '@mdi/js'
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
const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #00000015;
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    padding: 40px 60px;
    border-radius: 20px;
    background: linear-gradient(135deg, #e0e7ff 0%, #f0f4ff 100%);
    filter: blur(80);
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.06);
    min-width: 50%;
}
</style>
