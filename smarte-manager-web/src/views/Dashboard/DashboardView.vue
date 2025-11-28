<template>
  <div class="space-y-6">
    <!-- Hero -->
    <div class="sm-card p-6 flex flex-col md:flex-row items-center justify-between gap-6">
      <div class="space-y-2">
        <h2 class="text-2xl font-bold text-sm-dark dark:text-neutral-50">
          Welcome back, {{ userName }} 👋
        </h2>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
          Here’s a quick overview of your SmartManager system today.
        </p>
      </div>

      <PrimaryButton variant="primary" @click="onQuickAction">
        Add New Employee
      </PrimaryButton>
    </div>

    <!-- Stats cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total employees -->
      <div class="sm-card p-5 flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <p class="text-xs text-neutral-500">Total employees</p>
          <span class="text-[10px] px-2 py-1 rounded-full bg-sm-cream text-sm-dark">
            HR
          </span>
        </div>
        <p class="text-2xl font-semibold">
          {{ stats.employees }}
        </p>
      </div>

      <!-- Attendance today (numbers) -->
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
          {{ stats.attendanceToday.late }} late ·
          {{ stats.attendanceToday.absent }} absent
        </p>
      </div>

      <!-- Stock valuation -->
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
      </div>

      <!-- Monthly expenses -->
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
      </div>
    </div>

    <!-- Charts area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Attendance today (DETAIL TABLE) – unchanged -->
      <div class="sm-card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-sm-dark dark:text-neutral-50">
            Attendance today
          </h3>
          <p class="text-xs text-neutral-500">
            {{ todayDateFormatted }}
          </p>
        </div>

        <div v-if="dashboardStore.loading" class="text-sm text-neutral-500">
          Loading attendance…
        </div>

        <div v-else-if="todayAttendanceRows.length === 0" class="text-sm text-neutral-500">
          No attendance records for today yet.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-xs md:text-sm">
            <thead>
              <tr class="text-left border-b border-neutral-200 dark:border-neutral-800">
                <th class="py-2 pr-4">Employee</th>
                <th class="py-2 pr-4">Check-in</th>
                <th class="py-2 pr-4">Check-out</th>
                <th class="py-2 pr-4">Hours</th>
                <th class="py-2 pr-4">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in todayAttendanceRows"
                :key="row.id"
                class="border-b border-neutral-100 dark:border-neutral-800"
              >
                <td class="py-2 pr-4">
                  {{ row.employeeName }}
                </td>
                <td class="py-2 pr-4">
                  {{ row.checkIn }}
                </td>
                <td class="py-2 pr-4">
                  {{ row.checkOut || '—' }}
                </td>
                <td class="py-2 pr-4">
                  {{ row.totalHours }}
                </td>
                <td class="py-2 pr-4">
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium"
                    :class="row.status === 'Present'
                      ? 'bg-emerald-50 text-emerald-700'
                      : row.status === 'Completed'
                      ? 'bg-neutral-100 text-neutral-700'
                      : 'bg-red-50 text-red-600'"
                  >
                    ● {{ row.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p class="mt-3 text-[11px] text-neutral-500">
          Based on today’s check-ins and check-outs.
        </p>
      </div>

      <!-- Expenses by supplier -->
      <div class="sm-card p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-sm-dark dark:text-neutral-50">
            Expenses by supplier
          </h3>
          <span class="text-[11px] text-neutral-500">
            This month
          </span>
        </div>

        <div v-if="dashboardStore.loading" class="text-sm text-neutral-500">
          Loading expenses…
        </div>

        <div v-else-if="expensesBySupplier.length === 0" class="text-sm text-neutral-500">
          No expenses recorded for this month yet.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="sup in expensesBySupplier"
            :key="sup.id"
            class="space-y-1"
          >
            <div class="flex items-center justify-between text-xs">
              <span class="text-neutral-600 dark:text-neutral-300">
                {{ sup.name }}
              </span>
              <span class="font-medium">
                {{ formatMoney(sup.amount) }}
              </span>
            </div>
            <div class="w-full h-2 rounded-full bg-sm-cream overflow-hidden">
              <div
                class="h-2 rounded-full bg-sm-dark"
                :style="{ width: sup.percent + '%' }"
              ></div>
            </div>
          </div>

          <p class="mt-4 text-[11px] text-neutral-500">
            Total: {{ formatMoney(stats.expensesMonth.total) }} this month.
          </p>
        </div>
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
          v-for="item in activityRows"
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
            {{ item.timeAgo }}
          </span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import { useDashboardStore } from '@/stores/dashboard'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const dashboardStore = useDashboardStore()
const authStore = useAuthStore()

/* ---------- Top stats ---------- */

const userName = computed(() => {
  // Prefer auth store, then dashboard.me, fallback to "Nabil"
  const full =
    authStore.user?.name ||
    dashboardStore.me?.name ||
    'Nabil'

  return full.split(' ')[0]
})

const expensesBySupplier = computed(() => {
  // Support different naming in store (expenses or expensesList, suppliers or suppliersList)
  const expenses =
    dashboardStore.expenses ||
    dashboardStore.expensesList ||
    []

  const suppliers =
    dashboardStore.suppliers ||
    dashboardStore.suppliersList ||
    []

  // Use current month from summary or today
  const monthStr =
    dashboardStore.expensesSummary?.month ||
    new Date().toISOString().slice(0, 7) // YYYY-MM

  const map = new Map()

  for (const e of expenses) {
    if (!e.expense_date || !e.supplier_id) continue
    if (!String(e.expense_date).startsWith(monthStr)) continue

    const current = map.get(e.supplier_id) || 0
    map.set(e.supplier_id, current + Number(e.amount || 0))
  }

  const total = Array.from(map.values()).reduce((a, b) => a + b, 0)

  return Array.from(map.entries())
    .map(([supplierId, amount]) => {
      const sup = suppliers.find((s) => s.id === supplierId)
      return {
        id: supplierId,
        name: sup?.name || `Supplier #${supplierId}`,
        amount,
        percent: total ? Math.round((amount / total) * 100) : 0,
      }
    })
    .sort((a, b) => b.amount - a.amount)
})

const stats = computed(() => {
  const data = dashboardStore.overview

  if (!data) {
    return {
      employees: 0,
      employeesChange: '0',
      attendanceToday: {
        present: 0,
        total: 0,
        late: 0,
        absent: 0,
      },
      stockValuation: 0,
      stockChange: 0,
      expensesMonth: {
        total: 0,
        invoices: 0,
        topSupplier: '—',
      },
    }
  }

  const totalEmployees = data.employees?.total_active ?? 0
  const todayCheckins = data.attendance?.today_checkins ?? 0
  const currentlyPresent = data.attendance?.currently_present ?? 0

  const topSupName = expensesBySupplier.value[0]?.name || '—'

  return {
    employees: totalEmployees,
    employeesChange: '0',
    attendanceToday: {
      present: currentlyPresent,
      total: totalEmployees,
      late: 0,
      absent: Math.max(totalEmployees - todayCheckins, 0),
    },
    stockValuation: data.inventory?.total_value ?? 0,
    stockChange: 0,
    expensesMonth: {
      total: data.expenses?.total_this_month ?? 0,
      invoices: dashboardStore.invoicesCount ?? 0,
      topSupplier: topSupName,
    },
  }
})

/* ---------- Today attendance detailed table (unchanged) ---------- */

const todayDateFormatted = computed(() =>
  new Date().toLocaleDateString('en-GB', {
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }),
)

const todayAttendanceRows = computed(() => {
  const list = dashboardStore.todayAttendance || []
  return list.map((a) => {
    const employeeName = a.employee
      ? `${a.employee.first_name} ${a.employee.last_name}`
      : `Employee #${a.employee_id}`

    const checkIn = a.check_in
      ? new Date(a.check_in).toLocaleTimeString([], {
          hour: '2-digit',
          minute: '2-digit',
        })
      : ''

    const checkOut = a.check_out
      ? new Date(a.check_out).toLocaleTimeString([], {
          hour: '2-digit',
          minute: '2-digit',
        })
      : ''

    const totalHours =
      a.total_hours != null ? a.total_hours.toFixed(2) + 'h' : '0.00h'

    let status = 'Absent'
    if (a.check_in && !a.check_out) status = 'Present'
    if (a.check_in && a.check_out) status = 'Completed'

    return {
      id: a.id,
      employeeName,
      checkIn,
      checkOut,
      totalHours,
      status,
    }
  })
})

/* ---------- Recent activity ---------- */

const activityRows = computed(() =>
  (dashboardStore.activityFeed || []).map((item, idx) => ({
    id: idx,
    title: item.title,
    description: item.message,
    timeAgo: dashboardStore.timeAgo(item.date),
  })),
)

/* ---------- Helpers ---------- */

function formatMoney(value) {
  if (value == null) return '-'
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value)
}

function onQuickAction() {
  router.push({ name: 'employees' })
}

/* ---------- Init ---------- */
onMounted(() => {
  dashboardStore.loadDashboard()
})
</script>
