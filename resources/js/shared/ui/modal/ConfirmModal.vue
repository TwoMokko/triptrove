<script setup lang="ts">
    defineProps({
        title: { type: String, default: 'Подтвердите двействие' },
        message: { type: String, required: true },
    })

    const emit = defineEmits(['confirm, close'])

    const handleConfirm = () => {
        emit('confirm')
        emit('close')
    }
</script>

<template>
    <div class="">
        <h3>{{ title }}</h3>
        <p>{{ message }}</p>
        <div>
            <button @click="$emit('close')">Отмена</button>
            <button @click="$emit('handleConfirm')">Подтвердить</button>
        </div>
    </div>
</template>
