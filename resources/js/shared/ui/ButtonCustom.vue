<script setup lang="ts">
defineProps<{
  text: string,
  className?: string,
  type?: 'button' | 'reset' | 'submit',
  disabled?: boolean,
}>()
</script>

<template>
  <button
      class="btn"
      :class="className"
      @click="$emit('handler')"
      :type="type"
      :disabled="disabled"
  >
    {{ text }}
  </button>
</template>

<style>
.btn {
  position: relative;
  background: linear-gradient(135deg, #a1f4ff 0%, #abc8ff 100%);
  color: white;
  padding: 0.75rem 40px;
  border: none;
  border-radius: 60px;
  cursor: pointer;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  min-width: 300px;
  width: 100%;
  overflow: hidden;
  z-index: 1;
}

.btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #a1f4ff 0%, #abc8ff 40%, #a1f4ff 100%);
  opacity: 0;
  transition: opacity 0.4s ease-out;
  z-index: -1;
  border-radius: 60px;
}

.btn:not(:disabled):hover::before {
  opacity: 1;
}
.btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
  background: linear-gradient(135deg, #cccccc 0%, #999999 100%);
}
</style>
