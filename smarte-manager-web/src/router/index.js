// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/Auth/LoginView.vue'),
    meta: { guestOnly: true },
  },

  {
    path: '/',
    component: () => import('../components/layout/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: { name: 'dashboard' } },

      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('../views/Dashboard/DashboardView.vue'),
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('../views/Users/UsersView.vue'),
        meta: { roles: ['admin', 'manager'] },
      },
      {
        path: 'employees',
        name: 'employees',
        component: () => import('../views/Employees/EmployeesView.vue'),
      },
      {
        path: 'attendance',
        name: 'attendance',
        component: () => import('../views/Attendance/AttendanceView.vue'),
      },
      {
        path: 'attendance/my',
        name: 'my-attendance',
        component: () => import('../views/Attendance/MyAttendanceView.vue'),
      },
      {
        path: 'payroll',
        name: 'payroll',
        component: () => import('../views/Payroll/PayrollView.vue'),
      },
      {
        path: 'inventory',
        name: 'inventory',
        component: () => import('../views/Inventory/InventoryOverview.vue'),
      },
      {
        path: 'inventory/product/:id',
        name: 'product-history',
        component: () => import('../views/Inventory/ProductHistoryView.vue'),
      },
      {
        path: 'suppliers',
        name: 'suppliers',
        component: () => import('../views/Suppliers/SuppliersView.vue'),
      },
      {
        path: 'suppliers/:id',
        name: 'supplier-overview',
        component: () => import('../views/Suppliers/SupplierOverview.vue'),
      },
      {
        path: 'products',
        name: 'products',
        component: () => import('../views/Inventory/ProductsView.vue'),
      },
      {
        path: 'expenses',
        name: 'expenses',
        component: () => import('../views/Expenses/ExpensesView.vue'),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const DEV_BYPASS =
    import.meta.env.VITE_DEV_BYPASS_AUTH === 'true';

  // 🔥 DEV MODE: skip all auth/role checks
  if (DEV_BYPASS) {
    console.warn('⚠️ AUTH BYPASSED — DEV MODE ACTIVE');
    return next();
  }

  const auth = useAuthStore();

  const requiresAuth = to.matched.some((r) => r.meta.requiresAuth);
  const guestOnly = to.matched.some((r) => r.meta.guestOnly);
  const requiredRoles = to.meta.roles || null;

  // If we have a token but no user loaded (page refresh) → fetch current user
  if (auth.token && !auth.user) {
    try {
      await auth.fetchMe();
    } catch (e) {
      // if token invalid, fetchMe will usually logout
    }
  }

  // 1) Need auth but not logged in → go login
  if (requiresAuth && !auth.isAuthenticated) {
    return next({
      name: 'login',
      query: { redirect: to.fullPath },
    });
  }

  // 2) Guest-only route (login) while already logged in → go dashboard
  if (guestOnly && auth.isAuthenticated) {
    return next({ name: 'dashboard' });
  }

  // 3) Role-based access (admin/manager)
  if (requiredRoles && auth.isAuthenticated) {
    const role = auth.userRole || auth.user?.role || null;

    if (!requiredRoles.includes(role)) {
      return next({ name: 'dashboard' });
    }
  }

  return next();
});

export default router;
