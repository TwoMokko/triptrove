<script setup lang="ts">
import { mdiWindowClose, mdiMinus } from '@mdi/js'
import Icon from "@/shared/ui/Icon.vue"

interface Props {
  modalId: string
  isOpen: boolean
  isCollapsed: boolean
  title?: string
  previewText?: string
  isCollapsible?: boolean
}

const props = defineProps<Props>()


const emit = defineEmits(['close', 'toggle-collapse'])

const closeModal = () => {
    emit('close')
}

const toggleCollapse = () => {
    emit('toggle-collapse')
}
</script>

<template>
    <div v-if="isOpen" class="modal-overlay" :class="{ collapsed: isCollapsed }" @click.self="closeModal">
        <div class="modal-wrap" @click.stop>
            <div class="flex justify-between">
                <h3>
                   {{ title }}
                </h3>
                <div class="flex gap-4 justify-end">
                    <Icon
                        v-if="isCollapsible"
                        :iconPath="mdiMinus"
                        class="w-6 h-6 text-secondary hover:text-dark cursor-pointer"
                        @click="toggleCollapse"
                    />
                    <Icon
                        :iconPath="mdiWindowClose"
                        class="w-6 h-6 text-secondary hover:text-dark cursor-pointer"
                        @click="closeModal"
                    />
                </div>
            </div>
            <div class="mt-4 modal-content">
                <slot></slot>
            </div>


        </div>

        <div v-if="isCollapsed" class="flex gap-2">
            {{ previewText }}
        </div>
    </div>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10;

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

            .modal-content {
                /*overflow-y: scroll;
                height: calc(80vh - 24px - 80px - 1rem);*/
            }
        }
    }
}

.modal-wrap {
    border-radius: 20px;
    background: linear-gradient(135deg, #e0e7ff 0%, #f0f4ff 100%);
    filter: blur(80);
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.06);
    min-width: 50%;
    overflow: hidden;
    max-height: 80%;
    max-width: 60%;
}

@media (max-width: 768px) {
    .modal-overlay:not(.collapsed) .modal-wrap {
        padding: 20px;
        max-width: 90%;
    }
}
</style>
