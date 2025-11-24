<template>
  <div class="space-y-4">

    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark dark:text-neutral-50">
          Payroll
        </h2>
        <p class="text-xs text-neutral-500">
          Monthly payroll calculation for all employees.
        </p>
      </div>

      <PrimaryButton @click="exportCSV">
        Export CSV
      </PrimaryButton>
    </div>

    <!-- Filters -->
    <div class="sm-card p-4 space-y-3 md:space-y-0 md:flex md:items-end md:justify-between md:gap-4">

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
            v-for="emp in employees"
            :key="emp.id"
            :value="emp.id"
          >
            {{ emp.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <TableBase>
      <template #head>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Employee
        </th>

        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Hours Worked
        </th>

        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Hourly Rate (MAD)
        </th>

        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Total Salary
        </th>

        <th class="px-4 py-2 text-right text-[11px] font-medium text-neutral-500 uppercase">
          Payslip
        </th>
      </template>

      <template #body>
        <tr
          v-for="row in payrollComputed"
          :key="row.employee_id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2">
            <p class="font-medium text-sm-dark dark:text-neutral-100">
              {{ getEmployee(row.employee_id).name }}
            </p>
            <p class="text-[11px] text-neutral-500">
              ID: #{{ row.employee_id }}
            </p>
          </td>

          <td class="px-4 py-2">
            {{ row.total_hours.toFixed(2) }}h
          </td>

          <td class="px-4 py-2">
            {{ getEmployee(row.employee_id).hourly_rate }} MAD
          </td>

          <td class="px-4 py-2 font-semibold">
            {{ formatMoney(row.total_salary) }}
          </td>

          <td class="px-4 py-2 text-right">
            <button
              class="text-xs text-sm-dark hover:underline"
              @click="openPayslip(row)"
            >
              View
            </button>
          </td>
        </tr>

        <tr v-if="payrollComputed.length === 0">
          <td colspan="5" class="px-4 py-6 text-center text-xs text-neutral-500">
            No payroll data for this period.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>
          {{ payrollComputed.length }} payroll(s)
        </span>
      </template>
    </TableBase>

    <!-- Payslip Modal -->
    <ModalBase
      v-model="showModal"
      title="Employee Payslip"
      :subtitle="'Payroll details for ' + (selectedEmployee?.name || '')"
    >
      <div class="space-y-3 text-sm">

        <div class="flex justify-between">
          <span class="text-neutral-600">Employee</span>
          <span class="font-medium">{{ selectedEmployee?.name }}</span>
        </div>

        <div class="flex justify-between">
          <span class="text-neutral-600">Position</span>
          <span class="font-medium">{{ selectedEmployee?.position }}</span>
        </div>

        <div class="flex justify-between">
          <span class="text-neutral-600">Hourly Rate</span>
          <span class="font-medium">{{ selectedEmployee?.hourly_rate }} MAD</span>
        </div>

        <div class="flex justify-between">
          <span class="text-neutral-600">Hours Worked</span>
          <span class="font-medium">{{ selectedPayroll?.total_hours }} h</span>
        </div>

        <div class="flex justify-between border-t pt-3 mt-2">
          <span class="font-semibold">Total Salary</span>
          <span class="font-semibold text-sm-dark">
            {{ formatMoney(selectedPayroll?.total_salary) }}
          </span>
        </div>
      </div>

      <template #footer>
        <button
          class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
          @click="showModal = false"
        >
          Close
        </button>
      </template>
    </ModalBase>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import TableBase from '@/components/ui/TableBase.vue'
import ModalBase from '@/components/ui/ModalBase.vue'

/* ---------------------------------------------------
   MOCK EMPLOYEES (hr rate included)
----------------------------------------------------- */
const employees = [
  { id: 1, name: 'Ahmed Rami', position: 'Chef', hourly_rate: 25 },
  { id: 2, name: 'Said Nassim', position: 'Cashier', hourly_rate: 15 },
  { id: 3, name: 'Mouad Idrissi', position: 'Worker', hourly_rate: 13 },
]

/* ---------------------------------------------------
   MOCK ATTENDANCE HOURS (for payroll calculation)
----------------------------------------------------- */
const attendanceHours = [
  { employee_id: 1, total_hours: 167.2 },
  { employee_id: 2, total_hours: 152.8 },
  { employee_id: 3, total_hours: 134.5 },
]

/* ---------------------------------------------------
   Filters
----------------------------------------------------- */
const filters = reactive({
  month: '2025-11',
  employeeId: '',
})

/* ---------------------------------------------------
   Computed payroll array
----------------------------------------------------- */
const payrollComputed = computed(() => {
  return attendanceHours
    .filter((row) =>
      filters.employeeId
        ? row.employee_id === Number(filters.employeeId)
        : true
    )
    .map((row) => {
      const emp = employees.find((e) => e.id === row.employee_id)
      return {
        ...row,
        total_salary: row.total_hours * emp.hourly_rate,
      }
    })
})

/* ---------------------------------------------------
   Helper functions
----------------------------------------------------- */
function getEmployee(id) {
  return employees.find((e) => e.id === id)
}

function formatMoney(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value)
}

/* ---------------------------------------------------
   CSV export (mock)
----------------------------------------------------- */
function exportCSV() {
  alert('🚀 CSV export will be implemented with backend!')
}

/* ---------------------------------------------------
   Payslip modal
----------------------------------------------------- */
const showModal = ref(false)
const selectedEmployee = ref(null)
const selectedPayroll = ref(null)

function openPayslip(row) {
  selectedEmployee.value = getEmployee(row.employee_id)
  selectedPayroll.value = row
  showModal.value = true
}
</script>
