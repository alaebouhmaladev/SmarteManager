<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark dark:text-neutral-50">
          Attendance
        </h2>
        <p class="text-xs text-neutral-500">
          View and filter attendance records for all employees.
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div class="sm-card p-4 space-y-3 md:space-y-0 md:flex md:items-end md:justify-between md:gap-4">
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
              v-for="emp in employees"
              :key="emp.id"
              :value="emp.id"
            >
              {{ emp.name }}
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
        <tr
          v-for="row in filteredAttendance"
          :key="row.id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2 align-middle">
            <p class="font-medium text-sm-dark dark:text-neutral-100">
              {{ getEmployeeName(row.employee_id) }}
            </p>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm text-neutral-700 dark:text-neutral-200">
              {{ row.date }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm">
              {{ row.check_in || '—' }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm">
              {{ row.check_out || '—' }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm">
              {{ row.total_hours != null ? row.total_hours.toFixed(2) : '—' }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px]"
              :class="statusBadgeClass(row.status)"
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="statusDotClass(row.status)"
              ></span>
              {{ row.status }}
            </span>
          </td>
        </tr>

        <tr v-if="filteredAttendance.length === 0">
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
import { computed, reactive, ref } from 'vue'
import TableBase from '@/components/ui/TableBase.vue'

/**
 * MOCK employees
 */
const employees = [
  { id: 1, name: 'Ahmed Rami' },
  { id: 2, name: 'Said Nassim' },
  { id: 3, name: 'Mouad Idrissi' },
]

/**
 * MOCK attendance data
 */
const attendance = ref([
  {
    id: 1,
    employee_id: 1,
    date: '2025-11-20',
    check_in: '09:02',
    check_out: '17:15',
    total_hours: 8.2,
    status: 'Present',
  },
  {
    id: 2,
    employee_id: 2,
    date: '2025-11-20',
    check_in: '09:40',
    check_out: '17:05',
    total_hours: 7.3,
    status: 'Late',
  },
  {
    id: 3,
    employee_id: 3,
    date: '2025-11-20',
    check_in: null,
    check_out: null,
    total_hours: null,
    status: 'Absent',
  },
  {
    id: 4,
    employee_id: 1,
    date: '2025-11-19',
    check_in: '09:05',
    check_out: '17:00',
    total_hours: 7.9,
    status: 'Present',
  },
  {
    id: 5,
    employee_id: 2,
    date: '2025-11-19',
    check_in: '09:10',
    check_out: '16:50',
    total_hours: 7.6,
    status: 'Present',
  },
])

/**
 * Filters
 */
const filters = reactive({
  from: '',
  to: '',
  employeeId: '',
  status: 'all',
})

const statusFilters = [
  { value: 'all', label: 'All' },
  { value: 'Present', label: 'Present' },
  { value: 'Late', label: 'Late' },
  { value: 'Absent', label: 'Absent' },
]

const filteredAttendance = computed(() => {
  return attendance.value.filter((row) => {
    // date filter
    if (filters.from && row.date < filters.from) return false
    if (filters.to && row.date > filters.to) return false

    // employee filter
    if (filters.employeeId && row.employee_id !== Number(filters.employeeId)) {
      return false
    }

    // status filter
    if (filters.status !== 'all' && row.status !== filters.status) {
      return false
    }

    return true
  })
})

function getEmployeeName(id) {
  const emp = employees.find((e) => e.id === id)
  return emp ? emp.name : 'Unknown'
}

function statusBadgeClass(status) {
  switch (status) {
    case 'Present':
      return 'bg-emerald-50 text-emerald-700'
    case 'Late':
      return 'bg-amber-50 text-amber-700'
    case 'Absent':
      return 'bg-red-50 text-red-600'
    default:
      return 'bg-neutral-100 text-neutral-700'
  }
}

function statusDotClass(status) {
  switch (status) {
    case 'Present':
      return 'bg-emerald-500'
    case 'Late':
      return 'bg-amber-500'
    case 'Absent':
      return 'bg-red-500'
    default:
      return 'bg-neutral-500'
  }
}
</script>
