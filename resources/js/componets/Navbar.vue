<template>
  <nav class="border-b border-[var(--brand-border)] bg-[var(--brand-surface)] px-6 py-4 flex justify-between items-center shadow-sm">
    <div class="font-bold text-lg text-[var(--brand-primary)]">SPA Portal</div>

    <div class="flex items-center gap-4">
      <template v-if="authStore.isAuthenticated">
        <span class="text-sm text-[var(--brand-muted)]">Hello, {{ authStore.user?.name }}</span>
        <button @click="handleLogout" class="text-sm font-medium text-[var(--brand-primary)] hover:text-[var(--brand-primary-strong)]">
          Sign Out
        </button>
      </template>
      <template v-else>
        <router-link to="/login" class="text-sm font-medium text-[var(--brand-primary)] hover:text-[var(--brand-primary-strong)]">Sign In</router-link>
      </template>
    </div>
  </nav>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = async () => {
  await authStore.logout();
  router.push({ name: 'Login' });
};
</script>
