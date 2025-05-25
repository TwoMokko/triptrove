<script setup lang="ts">
import ButtonCustom from "@/shared/ui/ButtonCustom.vue";

defineProps({
    message: {
        type: String,
        required: true
    },
})

const emit = defineEmits(['confirm', 'close'])
</script>

<template>
    <div>
<!--        <p>{{ message }}</p>-->
        <div class="flex justify-end gap-2 pt-4">
            <button @click="emit('close')">Отмена</button>
            <ButtonCustom @handler="emit('confirm')" text="Подтвердить" />
        </div>
    </div>
</template>
