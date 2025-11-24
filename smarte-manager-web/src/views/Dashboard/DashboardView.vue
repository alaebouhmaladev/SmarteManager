<template>
  <div class="space-y-6">
    <!-- Hero -->
    <div class="sm-card p-6 flex flex-col md:flex-row items-center justify-between gap-6">
      <div class="space-y-2">
        <h2 class="text-2xl font-bold text-sm-dark dark:text-neutral-50">
          Welcome back, Nabil 👋
        </h2>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
          Here’s a quick overview of your SmartManager system today.
        </p>
      </div>

      <PrimaryButton
        variant="primary"
        @click="onQuickAction"
      >
        Add New Employee
      </PrimaryButton>
    </div>

    <!-- Stats cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="sm-card p-5 flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <p class="text-xs text-neutral-500">Total employees</p>
          <span class="text-[10px] px-2 py-1 rounded-full bg-sm-cream text-sm-dark">
            HR
          </span>
        </div>
        <p class="text-2xl font-semibold">{{ stats.employees }}</p>
        <p class="text-xs text-emerald-600">
          ▲ {{ stats.employeesChange }} since last month
        </p>
      </div>

      <div class="sm-card p-5 flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <p class="text-xs text-neutral-500">Attendance today</p>
          <span class="text-[10px] px-2 py-1 rounded-full bg-sm-cream text-sm-dark">
            Live
          </span>
        </div>
        <p class="text-2xl font-semibold">
          {{ stats.attendanceToday.present }}/{{ stats.attendanceToday.total }}
        </p>
        <p class="text-xs text-neutral-600">
          {{ stats.attendanceToday.late }} late · {{ stats.attendanceToday.absent }} absent
        </p>
      </div>

      <div class="sm-card p-5 flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <p class="text-xs text-neutral-500">Stock valuation</p>
          <span class="text-[10px] px-2 py-1 rounded-full bg-sm-cream text-sm-dark">
            Inventory
          </span>
        </div>
        <p class="text-2xl font-semibold">
          {{ formatMoney(stats.stockValuation) }}
        </p>
        <p class="text-xs" :class="stats.stockChange >= 0 ? 'text-emerald-600' : 'text-red-500'">
          {{ stats.stockChange >= 0 ? '▲' : '▼' }}
          {{ Math.abs(stats.stockChange) }}% vs last month
        </p>
      </div>

      <div class="sm-card p-5 flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <p class="text-xs text-neutral-500">Monthly expenses</p>
          <span class="text-[10px] px-2 py-1 rounded-full bg-sm-cream text-sm-dark">
            Finance
          </span>
        </div>
        <p class="text-2xl font-semibold">
          {{ formatMoney(stats.expensesMonth.total) }}
        </p>
        <p class="text-xs text-neutral-600">
          {{ stats.expensesMonth.invoices }} invoices ·
          top: {{ stats.expensesMonth.topSupplier }}
        </p>
      </div>
    </div>

    <!-- Charts area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Attendance chart -->
      <div class="sm-card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-sm-dark dark:text-neutral-50">
            Attendance this week
          </h3>
          <p class="text-xs text-neutral-500">
            {{ weekRange }}
          </p>
        </div>

        <div class="flex items-end gap-3 h-40">
          <div
            v-for="day in weeklyAttendance"
            :key="day.label"
            class="flex-1 flex flex-col items-center gap-1"
          >
            <div
              class="w-full rounded-t-xl bg-sm-yellow/80 flex items-end justify-center"
              :style="{ height: day.percent + '%' }"
            >
              <span class="text-[10px] font-medium text-sm-dark mb-1">
                {{ day.present }}
              </span>
            </div>
            <span class="text-[11px] text-neutral-500">
              {{ day.label }}
            </span>
          </div>
        </div>

        <p class="mt-3 text-[11px] text-neutral-500">
          Based on scheduled employees vs present employees per day.
        </p>
      </div>

      <!-- Expenses distribution -->
      <div class="sm-card p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-sm-dark dark:text-neutral-50">
            Expenses by category
          </h3>
          <span class="text-[11px] text-neutral-500">
            This month
          </span>
        </div>

        <div class="space-y-3">
          <div
            v-for="cat in expensesByCategory"
            :key="cat.name"
            class="space-y-1"
          >
            <div class="flex items-center justify-between text-xs">
              <span class="text-neutral-600 dark:text-neutral-300">
                {{ cat.name }}
              </span>
              <span class="font-medium">
                {{ formatMoney(cat.amount) }}
              </span>
            </div>
            <div class="w-full h-2 rounded-full bg-sm-cream overflow-hidden">
              <div
                class="h-2 rounded-full bg-sm-dark"
                :style="{ width: cat.percent + '%' }"
              ></div>
            </div>
          </div>
        </div>

        <p class="mt-4 text-[11px] text-neutral-500">
          Total: {{ formatMoney(stats.expensesMonth.total) }} this month.
        </p>
      </div>
    </div>

    <!-- Recent activity -->
    <div class="sm-card p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-sm-dark dark:text-neutral-50">
          Recent activity
        </h3>
      </div>

      <ul class="divide-y divide-neutral-100 dark:divide-neutral-800 text-sm">
        <li
          v-for="item in recentActivity"
          :key="item.id"
          class="py-2 flex items-center justify-between"
        >
          <div>
            <p class="font-medium text-sm-dark dark:text-neutral-100">
              {{ item.title }}
            </p>
            <p class="text-[11px] text-neutral-500">
              {{ item.description }}
            </p>
          </div>
          <span class="text-[11px] text-neutral-400">
            {{ item.time }}
          </span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'

// MOCK DATA for now – later you can replace by API response
const stats = ref({
  employees: 24,
  employeesChange: '+3',
  attendanceToday: {
    present: 18,
    total: 24,
    late: 2,
    absent: 4,
  },
  stockValuation: 15200,
  stockChange: 4.3,
  expensesMonth: {
    total: 8540,
    invoices: 17,
    topSupplier: 'Main Food Supplier',
  },
})

const weeklyAttendance = ref([
  { label: 'Mon', present: 18, percent: 70 },
  { label: 'Tue', present: 20, percent: 80 },
  { label: 'Wed', present: 19, percent: 76 },
  { label: 'Thu', present: 21, percent: 84 },
  { label: 'Fri', present: 17, percent: 68 },
  { label: 'Sat', present: 14, percent: 56 },
])

const expensesByCategory = ref([
  { name: 'Food & supplies', amount: 4200, percent: 50 },
  { name: 'Salaries', amount: 2600, percent: 30 },
  { name: 'Utilities', amount: 900, percent: 11 },
  { name: 'Misc', amount: 840, percent: 9 },
])

const recentActivity = ref([
  {
    id: 1,
    title: 'New employee added',
    description: 'You added “Ahmed R.” to HR records.',
    time: '5 min ago',
  },
  {
    id: 2,
    title: 'Stock movement recorded',
    description: '15x “Pizza boxes 33cm” added to inventory.',
    time: '24 min ago',
  },
  {
    id: 3,
    title: 'Expense registered',
    description: 'Monthly electricity bill recorded.',
    time: '2 hours ago',
  },
  {
    id: 4,
    title: 'Attendance closed',
    description: 'Yesterday’s attendance report generated.',
    time: '1 day ago',
  },
])

const weekRange = computed(() => 'Mon – Sat')

function formatMoney(value) {
  if (value == null) return '-'
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value)
}

function onQuickAction() {
  // For now just console.log – later we’ll open "Create Employee" modal
  console.log('Quick action: open Add Employee modal')
}
</script>
