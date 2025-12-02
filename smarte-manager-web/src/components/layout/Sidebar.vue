<template>
  <!-- Mobile sidebar -->
  <transition name="fade">
    <div v-if="open" class="fixed inset-0 z-40 flex md:hidden">
      <!-- Close SideBar -->
      <div class="fixed inset-0 bg-black/40" @click="closeSidebar" />

      <!-- Panel -->
      <aside
        class="relative z-50 w-64 h-full bg-sm-dark text-sm-cream flex flex-col"
      >
        <div class="flex flex-col h-full">
          <!-- Brand -->
          <div class="h-14 flex items-center px-4 border-b border-black/20">
            <span class="text-sm font-semibold tracking-tight">
              SmartManager
            </span>
          </div>

          <!-- Nav -->
          <nav class="flex-1 px-3 py-4 space-y-1 text-sm overflow-y-auto">
            <RouterLink
              v-for="item in navItems"
              :key="item.name"
              :to="item.to"
              class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors"
              :class="isActive(item)
                ? 'bg-black/40 text-sm-cream'
                : 'text-sm-grey hover:bg-black/30 hover:text-sm-cream'"
              @click="handleNavigate"
            >
              <component :is="item.icon" class="w-5 h-5" />
              <span class="truncate">{{ item.label }}</span>
            </RouterLink>
          </nav>

          <div class="px-4 py-3 border-t border-black/20 text-[11px] text-sm-grey">
            SmartManager © {{ year }}
          </div>
        </div>
      </aside>
    </div>
  </transition>

  <!-- Web sidebar -->
  <aside
    class="hidden md:flex md:flex-col w-60 h-screen bg-sm-dark text-sm-cream"
  >
    <div class="flex flex-col h-full">
      <!-- Brand -->
      <div class="h-14 flex items-center px-4 border-b border-black/20">
        <span class="text-sm font-semibold tracking-tight">
          SmartManager
        </span>
      </div>

      <!-- Nav -->
      <nav class="flex-1 px-3 py-4 space-y-1 text-sm overflow-y-auto">
        <RouterLink
          v-for="item in navItems"
          :key="item.name"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors"
          :class="isActive(item)
            ? 'bg-black/40 text-sm-cream'
            : 'text-sm-grey hover:bg-black/30 hover:text-sm-cream'"
        >
          <component :is="item.icon" class="w-5 h-5" />
          <span class="truncate">{{ item.label }}</span>
        </RouterLink>
      </nav>

      <div class="px-4 py-3 border-t border-black/20 text-[11px] text-sm-grey">
        SmartManager © {{ year }}
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Icons from heroicons library
import {
  HomeIcon,
  UsersIcon,
  UserGroupIcon,
  ClockIcon,
  ArchiveBoxIcon,
  BuildingStorefrontIcon,
  BanknotesIcon,
  CurrencyDollarIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close'])

const route = useRoute()
const auth = useAuthStore()

const year = new Date().getFullYear()

const currentRole = computed(() => auth.user?.role || null)

/**
 * Build nav depending on role
 */
const navItems = computed(() => {
  const role = currentRole.value

  // Base items we re-use for different roles
  const dashboard = {
    name: 'dashboard',
    label: 'Dashboard',
    icon: HomeIcon,
    to: { name: 'dashboard' },
  }

  const users = {
    name: 'users',
    label: 'Users',
    icon: UsersIcon,
    to: { name: 'users' },
  }

  const employees = {
    name: 'employees',
    label: 'Employees',
    icon: UserGroupIcon,
    to: { name: 'employees' },
  }

  const attendance = {
    name: 'attendance',
    label: 'Attendance',
    icon: ClockIcon,
    to: { name: 'attendance' },
    children: ['my-attendance'],
  }

  const myAttendance = {
    name: 'my-attendance',
    label: 'My attendance',
    icon: ClockIcon,
    to: { name: 'my-attendance' },
  }

  const payroll = {
    name: 'payroll',
    label: 'Payroll',
    icon: CurrencyDollarIcon,
    to: { name: 'payroll' },
  }

  const inventory = {
    name: 'inventory',
    label: 'Inventory',
    icon: ArchiveBoxIcon,
    to: { name: 'inventory' },
    children: ['products', 'product-history'],
  }

  const suppliers = {
    name: 'suppliers',
    label: 'Suppliers',
    icon: BuildingStorefrontIcon,
    to: { name: 'suppliers' },
    children: ['supplier-overview'],
  }

  const expenses = {
    name: 'expenses',
    label: 'Expenses',
    icon: BanknotesIcon,
    to: { name: 'expenses' },
  }

  // When dev-bypass is active and we don't know the role yet → show everything
  if (!role) {
    return [
      dashboard,
      users,
      employees,
      attendance,
      payroll,
      inventory,
      suppliers,
      expenses,
    ]
  }

  // ADMIN: full access
  if (role === 'admin') {
    return [
      dashboard,
      users,
      employees,
      attendance,
      payroll,
      inventory,
      suppliers,
      expenses,
    ]
  }

  // MANAGER: everything except maybe some future admin-only pages
  if (role === 'manager') {
    return [
      dashboard,
      users,
      employees,
      attendance,
      payroll,
      inventory,
      suppliers,
      expenses,
    ]
  }

  // HR: HR-only pages
  if (role === 'hr') {
    return [dashboard, employees, attendance, payroll]
  }

  // STOCK MANAGER: inventory & purchasing only
  if (role === 'stock_manager') {
    return [dashboard, inventory, suppliers, expenses]
  }

  // STAFF: only dashboard + personal attendance
  if (role === 'staff') {
    return [dashboard, myAttendance]
  }

  // Fallback: just dashboard
  return [dashboard]
})

const isActive = (item) => {
  if (route.name === item.name) return true
  if (item.children && item.children.includes(route.name)) return true
  return false
}

const closeSidebar = () => emit('close')
const handleNavigate = () => emit('close')
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
