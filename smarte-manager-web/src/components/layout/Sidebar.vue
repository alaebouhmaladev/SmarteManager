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

// Icons from heroicons libary
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

const navItems = computed(() => {
  const base = [
    {
      name: 'dashboard',
      label: 'Dashboard',
      icon: HomeIcon,
      to: { name: 'dashboard' },
    },
    {
      name: 'users',
      label: 'Users',
      icon: UsersIcon,
      to: { name: 'users' },
      roles: ['admin', 'manager'],
    },
    {
      name: 'employees',
      label: 'Employees',
      icon: UserGroupIcon,
      to: { name: 'employees' },
    },
    {
      name: 'attendance',
      label: 'Attendance',
      icon: ClockIcon,
      to: { name: 'attendance' },
      children: ['my-attendance'],
    },
    {
      name: 'payroll',                         
      label: 'Payroll',
      icon: CurrencyDollarIcon,
      to: { name: 'payroll' },
    },
    {
      name: 'inventory',
      label: 'Inventory',
      icon: ArchiveBoxIcon,
      to: { name: 'inventory' },
      children: ['products', 'product-history'],
    },
    {
      name: 'suppliers',
      label: 'Suppliers',
      icon: BuildingStorefrontIcon,
      to: { name: 'suppliers' },
      children: ['supplier-overview'],
    },
    {
      name: 'expenses',
      label: 'Expenses',
      icon: BanknotesIcon,
      to: { name: 'expenses' },
    },
  ]

  // In dev-bypass mode (no user) show everything
  if (!currentRole.value) return base

  // Filter by roles when user exists
  return base.filter(
    (item) => !item.roles || item.roles.includes(currentRole.value)
  )
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
