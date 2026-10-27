<template>
  <div class="min-h-screen bg-[var(--brand-bg)] text-[var(--brand-text)]">
    <div v-if="mobileMenuOpen" class="fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-sm lg:hidden" @click="mobileMenuOpen = false"></div>

    <aside :class="[
      mobileMenuOpen ? 'translate-x-0' : '-translate-x-full',
      'fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-[var(--brand-primary)] text-white shadow-2xl transition-transform duration-300 lg:translate-x-0'
    ]">
      <div class="flex items-center justify-between border-b border-white/10 px-7 py-6">
        <router-link to="/dashboard" class="flex items-center gap-3" @click="mobileMenuOpen = false">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-[var(--brand-accent)] text-lg font-bold text-[var(--brand-primary)]">CF</span>
          <span class="text-lg font-semibold tracking-tight">Cargo FLW</span>
        </router-link>
        <button class="grid h-9 w-9 place-items-center rounded-lg text-white/60 hover:bg-white/10 hover:text-white lg:hidden" aria-label="Close navigation" @click="mobileMenuOpen = false">&times;</button>
      </div>

      <div class="px-7 pb-3 pt-6"><p class="text-[10px] font-semibold tracking-[0.2em] text-white/40 uppercase">Workspace</p></div>

      <!-- SCROLLABLE INTERACTIVE GROUP NAVIGATION -->
      <nav class="flex-1 space-y-2 overflow-y-auto px-4">
        <div v-for="menu in structuredNav" :key="menu.id" class="space-y-1">

          <!-- TYPE A: INDEPENDENT ROOT OPTION LINK -->
          <router-link v-if="!menu.isGroup" :to="menu.path" class="block" @click="mobileMenuOpen = false">
            <div :class="[
              isActive(menu.path) ? 'bg-[var(--brand-accent)] font-semibold text-[var(--brand-primary)] shadow-lg shadow-black/10' : 'text-white/65 hover:bg-white/10 hover:text-white',
              'flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition'
            ]">
              <span :class="[isActive(menu.path) ? 'bg-[var(--brand-primary)]/10' : 'bg-white/10', 'grid h-8 w-8 place-items-center rounded-lg text-[10px] font-bold tracking-wide']">{{ menu.short }}</span>
              <span class="flex-1">{{ menu.name }}</span>
            </div>
          </router-link>

          <!-- TYPE B: COLLAPSIBLE ACCORDION DROPDOWN GROUP -->
          <div v-else class="space-y-1">
            <button
              @click="toggleGroup(menu.id)"
              :class="[
                isGroupChildActive(menu) ? 'text-[var(--brand-accent)] font-medium' : 'text-white/65 hover:text-white hover:bg-white/5',
                'flex w-full items-center justify-between rounded-xl px-4 py-3 text-sm transition text-left'
              ]"
            >
              <div class="flex items-center gap-3">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-white/10 text-[10px] font-bold tracking-wide text-white">{{ menu.short }}</span>
                <span>{{ menu.name }}</span>
              </div>
              <span :class="[expandedGroups[menu.id] ? 'rotate-180' : 'rotate-0', 'text-[10px] transition-transform duration-200 text-white/40']">▼</span>
            </button>

            <!-- NESTED SUB-NAVIGATION DRAWER -->
            <div v-show="expandedGroups[menu.id]" class="ml-6 border-l border-white/10 pl-3 space-y-1 transition-all">
              <router-link v-for="subItem in menu.children" :key="subItem.path" :to="subItem.path" class="block" @click="mobileMenuOpen = false">
                <div :class="[
                  isActive(subItem.path) ? 'text-[var(--brand-accent)] font-semibold' : 'text-white/50 hover:text-white',
                  'flex items-center gap-3 rounded-lg px-3 py-2 text-xs transition'
                ]">
                  <span class="h-1.5 w-1.5 rounded-full" :class="isActive(subItem.path) ? 'bg-[var(--brand-accent)]' : 'bg-white/20'"></span>
                  <span>{{ subItem.name }}</span>
                </div>
              </router-link>
            </div>
          </div>

        </div>
      </nav>

      <div class="m-4 rounded-2xl border border-white/10 bg-white/5 p-4">
        <div class="flex items-center gap-3">
          <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[var(--brand-accent)] font-semibold text-[var(--brand-primary)]">{{ userInitial }}</div>
          <div class="min-w-0"><p class="truncate text-sm font-semibold">{{ authStore.user?.name || 'Workspace member' }}</p><p class="mt-0.5 truncate text-[10px] font-semibold tracking-[0.15em] text-white/45 uppercase">{{ authStore.roleName }}</p></div>
        </div>
        <button class="mt-4 flex w-full items-center justify-between rounded-lg border border-white/10 px-3 py-2 text-xs font-medium text-white/60 transition hover:bg-white/10 hover:text-white" @click="handleLogout"><span>Sign out</span><span aria-hidden="true">&rarr;</span></button>
      </div>
    </aside>

    <div class="min-h-screen lg:pl-72">
      <header class="sticky top-0 z-30 flex h-[76px] items-center justify-between border-b border-slate-200/80 bg-[var(--brand-bg)]/90 px-5 backdrop-blur-md sm:px-8 lg:px-10">
        <div class="flex items-center gap-4">
          <button class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm lg:hidden" aria-label="Open navigation" @click="mobileMenuOpen = true">☰</button>
          <div><p class="hidden text-[10px] font-semibold tracking-[0.18em] text-slate-400 uppercase sm:block">Bereket workspace</p><h1 class="text-lg font-semibold tracking-tight text-slate-950">{{ pageTitle }}</h1></div>
        </div>
        <div class="flex items-center gap-3">
          <span class="hidden items-center gap-2 rounded-full border border-[var(--brand-accent)]/40 bg-[var(--brand-accent)]/10 px-3 py-1.5 text-xs font-medium text-[var(--brand-primary)] sm:flex"><span class="h-1.5 w-1.5 rounded-full bg-[var(--brand-success)]"></span>All systems normal</span>
          <router-link to="/profile" class="grid h-10 w-10 place-items-center rounded-full bg-[var(--brand-primary)] text-sm font-semibold text-[var(--brand-accent)]" :aria-label="`Open profile for ${authStore.user?.name || 'user'}`">{{ userInitial }}</router-link>
        </div>
      </header>

      <main class="mx-auto max-w-[1440px] px-5 py-7 sm:px-8 sm:py-9 lg:px-10"><router-view /></main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const mobileMenuOpen = ref(false);

// Reactive state dictionary to monitor expanded menu folders
const expandedGroups = ref({
  security: false,
  cargo: false
});

const toggleGroup = (groupId) => {
  expandedGroups.value[groupId] = !expandedGroups.value[groupId];
};

// Hierarchical Navigation Tree Blueprint structure
const structuredNav = computed(() => {
  const list = [
    { id: 'overview', name: 'Overview', path: '/dashboard', short: 'OV', isGroup: false },
    { id: 'profile', name: 'Profile settings', path: '/profile', short: 'PR', isGroup: false }
  ];

  if (authStore.isAdmin) {
    list.push(
      {
        id: 'security',
        name: 'Security Admin',
        short: 'SE',
        isGroup: true,
        children: [
          { name: 'Admin center', path: '/admin' },
          { name: 'Role & Permissions', path: '/admin/permissions' },
          { name: 'Role Management', path: '/admin/roles' }
        ]
      },
      {
        id: 'cargo',
        name: 'Cargo Fleet',
        short: 'CR',
        isGroup: true,
        children: [
          { name: 'Parties Register', path: '/cargo/parties' },
          { name: 'Vessels Fleet', path: '/cargo/vessels' },
          { name: 'Inventory Batches', path: '/cargo/batches' },
          { name: 'Transit Voyages', path: '/cargo/voyages' },
          { name: 'Containers Log', path: '/cargo/containers' }
        ]
      }
    );
  }
  return list;
});

// Helper testing if an active path matches a group drawer sub-child
const isGroupChildActive = (group) => {
  return group.children?.some(child => route.path === child.path);
};

// Automatically keep dropdowns open if page is directly reloaded on a sub-item route
const autoExpandActiveMenus = () => {
  structuredNav.value.forEach(item => {
    if (item.isGroup && isGroupChildActive(item)) {
      expandedGroups.value[item.id] = true;
    }
  });
};

watch(() => route.path, () => {
  autoExpandActiveMenus();
});

onMounted(() => {
  autoExpandActiveMenus();
});

const pageTitle = computed(() => {
  if (route.name === 'AdminDashboard' || route.path === '/admin') return 'Admin center';
  if (route.name === 'Profile' || route.path === '/profile') return 'Profile settings';
  if (route.path === '/admin/permissions') return 'Role and Permissions';
  if (route.path === '/admin/roles') return 'Role Management';
  if (route.path === '/cargo/parties') return 'Party Management';
  if (route.path === '/cargo/vessels') return 'Vessel Management';
  if (route.path === '/cargo/batches') return 'Batch Management';
  if (route.path === '/cargo/voyages') return 'Voyage Management';
  if (route.path === '/cargo/containers') return 'Container Management';
  return 'Overview';
});

const userInitial = computed(() => authStore.user?.name?.charAt(0)?.toUpperCase() || 'B');
const isActive = (path) => route.path === path;

const handleLogout = async () => {
  await authStore.logout();
  router.push({ name: 'Login' });
};
</script>
