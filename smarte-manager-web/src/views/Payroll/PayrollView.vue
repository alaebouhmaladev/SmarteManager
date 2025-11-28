<template>
  <div class="space-y-4">
    <!-- Header + Export -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark">
          Payroll
        </h2>
        <p class="text-xs text-neutral-500">
          Monthly payroll calculation for all employees.
        </p>
      </div>

      <button
        class="px-4 py-2 rounded-full bg-sm-dark text-sm-cream text-xs font-medium
               disabled:opacity-60 disabled:cursor-not-allowed"
        :disabled="payrollStore.exporting"
        @click="handleExportCsv"
      >
        {{ payrollStore.exporting ? 'Exporting…' : 'Export CSV' }}
      </button>
    </div>

    <!-- Filters (Month + Employee) -->
    <div
      class="sm-card p-4 space-y-3 md:space-y-0 md:flex md:items-end md:justify-between md:gap-4"
    >
      <div class="flex flex-col sm:flex-row gap-3 flex-1">
        <!-- Month -->
        <div class="flex-1">
          <label class="block text-xs font-medium text-neutral-700 mb-1">
            Month
          </label>
          <input
            v-model="filters.month"
            type="month"
            class="w-full rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
          />
        </div>

        <!-- Employee -->
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
          class="px-3 py-1.5 rounded-full text-xs border transition
                 bg-sm-dark text-sm-cream border-sm-dark"
          @click="handleLoadMonthly"
        >
          Load payroll
        </button>
      </div>
    </div>

    <!-- Error message -->
    <p v-if="payrollStore.error" class="text-xs text-red-600">
      {{ payrollStore.error }}
    </p>

    <!-- Table -->
    <TableBase>
      <template #head>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Employee
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Hours worked
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Hourly rate (MAD)
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Total salary
        </th>
        <th class="px-4 py-2 text-right text-[11px] font-medium text-neutral-500 uppercase">
          Payslip
        </th>
      </template>

      <template #body>
        <!-- Loading -->
        <tr v-if="payrollStore.loading">
          <td colspan="5" class="px-4 py-6 text-center text-xs text-neutral-500">
            Loading payroll...
          </td>
        </tr>

        <!-- Data rows -->
        <tr
          v-for="row in filteredPayroll"
          :key="row.employee_id"
          class="hover:bg-sm-cream/50 text-sm"
        >
          <td class="px-4 py-2 align-middle">
            <p class="font-medium text-sm-dark">
              {{ row.employee_name }}
            </p>
            <p class="text-[11px] text-neutral-500">
              ID: #{{ row.employee_id }}
            </p>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm">
              {{ formatHours(row.total_hours) }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm">
              {{ formatRate(row.hourly_rate) }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm font-semibold text-sm-dark">
              {{ formatMoney(row.salary) }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle text-right">
            <button
              class="text-xs text-neutral-800 underline underline-offset-2"
              @click="handleViewPayslip(row)"
            >
              View
            </button>
          </td>
        </tr>

        <!-- Empty -->
        <tr
          v-if="!payrollStore.loading && filteredPayroll.length === 0"
        >
          <td colspan="5" class="px-4 py-6 text-center text-xs text-neutral-500">
            No payroll records for this month.
          </td>
        </tr>
      </template>

      <template #footer>
        <span class="text-xs text-neutral-600">
          {{ filteredPayroll.length }} payroll(s)
        </span>
      </template>
    </TableBase>

    <!-- Payslip MODAL -->
    <div
      v-if="showPayslipModal"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/40"
    >
      <div class="sm-card w-full max-w-2xl mx-4 p-4 relative bg-white">
        <!-- Close button -->
        <button
          class="absolute top-3 right-3 text-neutral-400 hover:text-neutral-700 text-sm"
          @click="closePayslip"
        >
          ✕
        </button>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
          <div>
            <h3 class="text-sm font-semibold text-sm-dark">
              Payslip
              <span v-if="employeeFullName">
                – {{ employeeFullName }}
              </span>
            </h3>
            <p class="text-[11px] text-neutral-500">
              Month:
              <span v-if="payrollStore.employeeDetails">
                {{ payrollStore.employeeDetails.month }}
              </span>
            </p>
          </div>

          <div class="flex gap-4 text-xs" v-if="payrollStore.employeeDetails">
            <div>
              <p class="text-neutral-500 text-[11px]">Total hours</p>
              <p class="font-medium text-sm-dark">
                {{ formatHours(payrollStore.employeeDetails.total_hours) }}
              </p>
            </div>
            <div>
              <p class="text-neutral-500 text-[11px]">Total salary</p>
              <p class="font-medium text-sm-dark">
                {{ formatMoney(payrollStore.employeeDetails.salary) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Body -->
        <div v-if="payrollStore.loadingEmployee" class="py-6 text-center text-xs text-neutral-500">
          Loading payslip...
        </div>

        <div
          v-else-if="payrollStore.employeeDetails"
          class="border-t border-neutral-200 pt-3 mt-2"
        >
          <p class="text-[11px] font-medium text-neutral-600 mb-2">
            Attendance breakdown
          </p>
          <div class="max-h-64 overflow-y-auto text-xs">
            <table class="w-full">
              <thead>
                <tr class="text-[11px] text-neutral-500 uppercase">
                  <th class="py-1 text-left">Date</th>
                  <th class="py-1 text-left">Check-in</th>
                  <th class="py-1 text-left">Check-out</th>
                  <th class="py-1 text-left">Hours</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="att in payrollStore.employeeDetails.attendances"
                  :key="att.id"
                  class="border-t border-neutral-100"
                >
                  <td class="py-1">
                    {{ formatDate(att.work_date) }}
                  </td>
                  <td class="py-1">
                    {{ formatTime(att.check_in) }}
                  </td>
                  <td class="py-1">
                    {{ formatTime(att.check_out) }}
                  </td>
                  <td class="py-1">
                    {{ formatHours(att.total_hours) }}
                  </td>
                </tr>
                <tr
                  v-if="!payrollStore.employeeDetails.attendances ||
                        payrollStore.employeeDetails.attendances.length === 0"
                >
                  <td colspan="4" class="py-2 text-neutral-500 text-[11px]">
                    No attendance records found for this employee in this month.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div
          v-else
          class="py-6 text-center text-xs text-neutral-500"
        >
          No payslip data to display.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted } from 'vue'
import TableBase from '@/components/ui/TableBase.vue'
import { usePayrollStore } from '@/stores/payroll'
import { useEmployeesStore } from '@/stores/employees'

const payrollStore = usePayrollStore()
const employeesStore = useEmployeesStore()

const showPayslipModal = ref(false)

function getDefaultMonth() {
  const now = new Date()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  return `${now.getFullYear()}-${month}`
}

const filters = reactive({
  month: payrollStore.month || getDefaultMonth(),
  employeeId: '',
})

const fullName = (emp) =>
  `${emp.first_name || ''} ${emp.last_name || ''}`.trim()

onMounted(async () => {
  if (!employeesStore.employees.length) {
    await employeesStore.fetchEmployees()
  }
  await payrollStore.fetchMonthly(filters.month)
})

const filteredPayroll = computed(() =>
  (payrollStore.list || []).filter((row) => {
    if (filters.employeeId && row.employee_id !== Number(filters.employeeId)) {
      return false
    }
    return true
  }),
)

const employeeFullName = computed(() => {
  const d = payrollStore.employeeDetails
  if (!d?.employee) return ''
  return `${d.employee.first_name} ${d.employee.last_name}`
})

function formatHours(hours) {
  if (hours == null) return '—'

  const value = Number(hours)
  if (Number.isNaN(value)) return '—'

  let h = Math.floor(value)
  let m = Math.round((value - h) * 60)

  // Handle rounding (for example 1.999 → 2h 0m)
  if (m === 60) {
    h += 1
    m = 0
  }

  if (h === 0 && m === 0) return '0h'
  if (h === 0) return `${m}m`
  if (m === 0) return `${h}h`

  return `${h}h ${m}m`
}


function formatRate(rate) {
  if (rate == null) return '—'
  return `${Number(rate).toLocaleString('fr-MA')} MAD`
}

function formatMoney(value) {
  if (value == null) return '—'
  return `${Number(value).toLocaleString('fr-MA')} MAD`
}

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

async function handleLoadMonthly() {
  await payrollStore.fetchMonthly(filters.month)
}

function handleExportCsv() {
  payrollStore.exportMonthlyCsv(filters.month)
}

async function handleViewPayslip(row) {
  showPayslipModal.value = true
  await payrollStore.fetchEmployeeMonthly(row.employee_id, filters.month)
}

function closePayslip() {
  showPayslipModal.value = false
}
</script>
