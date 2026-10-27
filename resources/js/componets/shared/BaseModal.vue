<!-- src/components/shared/BaseModal.vue -->
<template>
  <Transition name="fade">
    <div v-if="show" class="modal-overlay" @click.self="emit('close')">
      <div class="modal-container" :class="sizeClass">
        <header class="modal-header">
          <h3>{{ title }}</h3>
          <button @click="emit('close')" class="close-btn">&times;</button>
        </header>
        <section class="modal-body">
          <slot></slot>
        </section>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({
  show: Boolean,
  title: String,
  size: { type: String, default: 'md' }
});
const emit = defineEmits(['close']);
const sizeClass = computed(() => `size-${props.size}`);
</script>

<style scoped>
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 999; display: flex; align-items: center; justify-content: center; }
.modal-container { background: white; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); width: 90%; max-height: 90vh; overflow-y: auto; display: flex; flex-direction: column; }
.size-md { max-width: 550px; }
.size-lg { max-width: 800px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; }
.modal-header h3 { font-size: 1.25rem; font-weight: 600; color: #1e293b; margin: 0; }
.close-btn { background: none; border: none; font-size: 1.5rem; color: #64748b; cursor: pointer; }
.modal-body { padding: 1.5rem; overflow-y: auto; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
