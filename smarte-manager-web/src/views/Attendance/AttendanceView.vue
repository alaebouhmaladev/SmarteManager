<template>
  <div class="space-y-4">
    <!-- Header + manual admin controls -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark">
          Attendance
        </h2>
        <p class="text-xs text-neutral-500">
          View and filter attendance records for all employees.
        </p>
      </div>

      <!-- Manual check-in / check-out for selected employee -->
      <div class="sm-card px-3 py-2 flex items-center gap-3">
        <select
          v-model="manual.employeeId"
          class="rounded-xl border border-neutral-200 bg-white px-3 py-1.5 text-xs
                 focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
        >
          <option value="">Select employee…</option>
          <option
            v-for="emp in employeesStore.employees"
            :key="emp.id"
            :value="emp.id"
          >
            {{ fullName(emp) }}
          </option>
        </select>

        <button
          class="text-xs px-3 py-1.5 rounded-xl bg-sm-dark text-sm-cream disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="!manual.employeeId"
          @click="handleManualCheckIn"
        >
          Check in
        </button>

        <button
          class="text-xs px-3 py-1.5 rounded-xl bg-white border border-neutral-300 text-sm-dark disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="!manual.employeeId"
          @click="handleManualCheckOut"
        >
          Check out
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div
      class="sm-card p-4 space-y-3 md:space-y-0 md:flex md:items-end md:justify-between md:gap-4"
    >
      <div class="flex flex-col sm:flex-row gap-3 flex-1">
        <div class="flex-1">
          <label class="block text-xs font-medium text-neutral-700 mb-1">
            From date
          </label>
          <input
            v-model="filters.from"
            type="date"
            class="w-full rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
          />
        </div>

        <div class="flex-1">
          <label class="block text-xs font-medium text-neutral-700 mb-1">
            To date
          </label>
          <input
            v-model="filters.to"
            type="date"
            class="w-full rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
          />
        </div>

        <div class="flex-1">
          <label class="block text-xs font-medium text-neutral-700 mb-1">
            Employee
          </label>
          <select
            v-model="filters.employeeId"
            class="w-full rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
          >
            <option value="">All employees</option>
            <option
              v-for="emp in employeesStore.employees"
              :key="emp.id"
              :value="emp.id"
            >
              {{ fullName(emp) }}
            </option>
          </select>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          v-for="opt in statusFilters"
          :key="opt.value"
          class="px-3 py-1.5 rounded-full text-xs border transition"
          :class="filters.status === opt.value
            ? 'bg-sm-dark text-sm-cream border-sm-dark'
            : 'bg-white text-neutral-600 border-neutral-200 hover:bg-neutral-100'"
          @click="filters.status = opt.value"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>

    <!-- Table -->
    <TableBase>
      <template #head>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Employee
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Date
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Check-in
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Check-out
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Total hours
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Status
        </th>
      </template>

      <template #body>
        <!-- Loading state -->
        <tr v-if="attendanceStore.loading">
          <td colspan="6" class="px-4 py-6 text-center text-xs text-neutral-500">
            Loading attendance...
          </td>
        </tr>

        <!-- Data rows -->
        <tr
          v-for="row in filteredAttendance"
          :key="row.id"
          class="hover:bg-sm-cream/50 text-sm"
        >
          <td class="px-4 py-2 align-middle">
            <p class="font-medium text-sm-dark">
              {{ getEmployeeName(row.employee_id) }}
            </p>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm text-neutral-700">
              {{ formatDate(row.work_date) }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm">
              {{ formatTime(row.check_in) }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm">
              {{ formatTime(row.check_out) }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm">
              {{ row.total_hours != null ? Number(row.total_hours).toFixed(2) : '—' }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px]"
              :class="statusBadgeClass(computedStatus(row))"
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="statusDotClass(computedStatus(row))"
              ></span>
              {{ computedStatus(row) }}
            </span>
          </td>
        </tr>

        <!-- Empty state -->
        <tr
          v-if="!attendanceStore.loading && filteredAttendance.length === 0"
        >
          <td colspan="6" class="px-4 py-6 text-center text-xs text-neutral-500">
            No attendance records with current filters.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>Showing {{ filteredAttendance.length }} record(s)</span>
      </template>
    </TableBase>
  </div>
</template>

<script setup>
import { computed, reactive, onMounted } from 'vue'
import TableBase from '@/components/ui/TableBase.vue'
import { useAttendanceStore } from '@/stores/attendance'
import { useEmployeesStore } from '@/stores/employees'

const attendanceStore = useAttendanceStore()
const employeesStore = useEmployeesStore()

// Load employees + attendance on mount
onMounted(async () => {
  if (!employeesStore.employees.length) {
    await employeesStore.fetchEmployees()
  }
  await attendanceStore.fetchAll()
})

// filters for table
const filters = reactive({
  from: '',
  to: '',
  employeeId: '',
  status: 'all', // all | Active | Not Active
})

// manual admin controls
const manual = reactive({
  employeeId: '',
})

// All / Active / Not Active
const statusFilters = [
  { value: 'all', label: 'All' },
  { value: 'Active', label: 'Active' },
  { value: 'Not Active', label: 'Not active' },
]

// full name from employees store
const fullName = (emp) =>
  `${emp.first_name || ''} ${emp.last_name || ''}`.trim()

function getEmployeeName(id) {
  const emp = employeesStore.employees.find((e) => e.id === id)
  return emp ? fullName(emp) : 'Unknown'
}

/**
 * STATUS:
 *  - Active     = has check_in AND NO check_out (currently working)
 *  - Not Active = everything else
 */
function computedStatus(row) {
  const hasIn = !!row.check_in
  const hasOut = !!row.check_out

  if (hasIn && !hasOut) {
    return 'Active'
  }
  return 'Not Active'
}

// Format helpers
function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  if (Number.isNaN(d.getTime())) return dateStr
  return d.toLocaleDateString('fr-MA', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

function formatTime(dateTimeStr) {
  if (!dateTimeStr) return '—'
  const d = new Date(dateTimeStr)
  if (Number.isNaN(d.getTime())) return dateTimeStr
  return d.toLocaleTimeString('fr-MA', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

const filteredAttendance = computed(() => {
  return (attendanceStore.records || []).filter((row) => {
    const date = row.work_date

    if (filters.from && date < filters.from) return false
    if (filters.to && date > filters.to) return false

    if (filters.employeeId && row.employee_id !== Number(filters.employeeId)) {
      return false
    }

    const s = computedStatus(row)
    if (filters.status !== 'all' && s !== filters.status) {
      return false
    }

    return true
  })
})

function statusBadgeClass(status) {
  switch (status) {
    case 'Active':
      return 'bg-emerald-50 text-emerald-700'
    case 'Not Active':
      return 'bg-red-50 text-red-600'
    default:
      return 'bg-neutral-100 text-neutral-700'
  }
}

function statusDotClass(status) {
  switch (status) {
    case 'Active':
      return 'bg-emerald-500'
    case 'Not Active':
      return 'bg-red-500'
    default:
      return 'bg-neutral-500'
  }
}

/* ===== Manual check-in / check-out actions ===== */

async function handleManualCheckIn() {
  if (!manual.employeeId) return
  await attendanceStore.checkInForEmployee(Number(manual.employeeId))
}

async function handleManualCheckOut() {
  if (!manual.employeeId) return
  await attendanceStore.checkOutForEmployee(Number(manual.employeeId))
}
</script>
