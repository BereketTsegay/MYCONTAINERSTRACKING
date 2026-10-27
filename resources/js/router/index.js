import { createRouter, createWebHistory } from 'vue-router';
import DashboardLayout from '../layouts/DashboardLayout.vue';

const routes = [
    // Wrap protected routes inside the structural Dashboard Layout frame wrapper
    {
      path: '/login',
      name: 'Login',
      component: () => import('../views/Login.vue'),
      meta: { guestOnly: true }
    },
    {
      path: '/register',
      name: 'Register',
      component: () => import('../views/Register.vue'),
      meta: { guestOnly: true }
    },
  {
    path: '/',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue')
      },
      {
        path: 'profile',
        name: 'Profile',
        component: () => import('../views/Profile.vue')
      },
      {
        path: 'admin',
        name: 'AdminDashboard',
        component: () => import('../views/admin/index.vue'),
        meta: { requiresRole: 'admin' }
      },
      {
        path: '/admin/permissions',
        name: 'admin.permissions',
        // Lazy-load the dashboard view we built in the previous steps
        component: () => import('../views/admin/PermissionDashboard.vue'),
        meta: {
          requiresRole: 'admin' // Only users with 'admin' in their JWT claims can enter
        }
      },
      {
        path: '/admin/roles',
        name: 'admin.roles',
        // Lazy-load the dashboard view we built in the previous steps
        component: () => import('../views/admin/RoleManager.vue'),
        meta: {
          requiresRole: 'admin' // Only users with 'admin' in their JWT claims can enter
        }
      },
      {
            path: '/cargo/parties',
            name: 'cargo.parties',
            component: () => import('../views/cargo/parties/PartyManager.vue'),
            meta: {
            requiresRole: 'admin',
            title: 'Cargo Parties Management'
            }
        },
      {
            path: '/cargo/vessels',
            name: 'cargo.vessels',
            component: () => import('../views/cargo/vessels/VesselManager.vue'),
            meta: {
            requiresRole: 'admin',
            title: 'Cargo Vessels Management'
            }
        },
      {
            path: '/cargo/batches',
            name: 'cargo.batches',
            component: () => import('../views/cargo/batchs/batchs.vue'),
            meta: {
            requiresRole: 'admin',
            title: 'Cargo Batches Management'
            }
        },
        {
            path: '/cargo/voyages',
            name: 'cargo.voyages',
            component: () => import('../views/cargo/voyage/index.vue'),
            meta: {
            requiresRole: 'admin',
            title: 'Cargo Voyages Management'
            }
        },
        {
            path: '/cargo/containers',
            name: 'cargo.containers',
            component: () => import('../views/cargo/container/index.vue'),
            meta: {
            requiresRole: 'admin',
            title: 'Cargo Containers Management'
            }
        }
    ]
  },
  {
    path: '/403',
    name: 'Forbidden',
    component: () => import('../views/errors/403.vue')
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});



export default router;
