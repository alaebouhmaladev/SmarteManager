<template>
  <div class="space-y-4">
    <!-- Header -->
    <div
      class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
      <div>
        <h2 class="text-lg font-semibold text-sm-dark">
          Employees
        </h2>
        <p class="text-xs text-neutral-500">
          Manage employees, roles and hourly rates.
        </p>
      </div>

      <PrimaryButton size="sm" @click="openCreate">
        + Add employee
      </PrimaryButton>
    </div>

    <!-- Filters / search -->
    <div
      class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
    >
      <p class="text-xs text-neutral-500">
        Total employees: {{ filteredEmployees.length }}
      </p>

      <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
        <!-- Role filter -->
        <select
          v-model="filters.role"
          class="rounded-xl border border-neutral-200 px-3 py-2 text-xs
                 bg-white focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
        >
          <option value="">All roles</option>
          <option value="chef">Chef</option>
          <option value="waiter">Waiter</option>
          <option value="manager">Manager</option>
          <option value="other">Other</option>
        </select>

        <!-- Status filter -->
        <select
          v-model="filters.status"
          class="rounded-xl border border-neutral-200 px-3 py-2 text-xs
                 bg-white focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
        >
          <option value="">All status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

        <!-- Search -->
        <div
          class="flex items-center gap-2 rounded-xl border border-neutral-200 bg-white px-3 py-2"
        >
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search name or phone..."
            class="w-full text-xs outline-none bg-transparent"
          />
        </div>
      </div>
    </div>

    <!-- Table -->
    <CardBox>
      <template #default>
        <TableBase>
          <template #head>
            <th
              class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase"
            >
              Employee
            </th>
            <th
              class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase"
            >
              Phone
            </th>
            <th
              class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase"
            >
              Role
            </th>
            <th
              class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase"
            >
              Hourly rate
            </th>
            <th
              class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase"
            >
              Status
            </th>
            <th
              class="px-4 py-2 text-right text-[11px] font-medium text-neutral-500 uppercase"
            >
              Actions
            </th>
          </template>

          <template #body>
            <tr
              v-for="emp in filteredEmployees"
              :key="emp.id"
              class="hover:bg-sm-cream/50 text-sm"
            >
              <td class="px-4 py-2">
                <p class="font-medium text-sm-dark">
                  {{ fullName(emp) }}
                </p>
                <p class="text-[11px] text-neutral-500">
                  Hired:
                  {{ emp.hire_date || '—' }}
                </p>
              </td>
              <td class="px-4 py-2">
                <span class="text-xs text-neutral-700">
                  {{ emp.phone || '—' }}
                </span>
              </td>
              <td class="px-4 py-2">
                <span class="text-xs text-neutral-700">
                  {{ emp.role || '—' }}
                </span>
              </td>
              <td class="px-4 py-2">
                <span class="text-xs text-neutral-700">
                  {{ formatMoney(emp.hourly_rate) }}/h
                </span>
              </td>
              <td class="px-4 py-2">
                <span
                  class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px]"
                  :class="
                    emp.status === 'active'
                      ? 'bg-emerald-50 text-emerald-700'
                      : 'bg-neutral-100 text-neutral-600'
                  "
                >
                  <span
                    class="h-1.5 w-1.5 rounded-full"
                    :class="
                      emp.status === 'active'
                        ? 'bg-emerald-500'
                        : 'bg-neutral-400'
                    "
                  ></span>
                  {{ emp.status === 'active' ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-2 text-right">
                <div class="inline-flex items-center gap-2">
                  <button
                    class="text-xs text-neutral-600 hover:underline"
                    @click="openProfile(emp)"
                  >
                    Profile
                  </button>
                  <button
                    class="text-xs text-sm-dark hover:underline"
                    @click="openEdit(emp)"
                  >
                    Edit
                  </button>
                  <button
                    class="text-xs text-red-500 hover:underline"
                    :disabled="employeesStore.deletingId === emp.id"
                    @click="confirmDelete(emp)"
                  >
                    <span
                      v-if="employeesStore.deletingId === emp.id"
                    >
                      Deleting...
                    </span>
                    <span v-else>Delete</span>
                  </button>
                </div>
              </td>
            </tr>

            <tr
              v-if="
                !employeesStore.loading &&
                filteredEmployees.length === 0
              "
            >
              <td
                colspan="6"
                class="px-4 py-6 text-center text-xs text-neutral-500"
              >
                No employees match your filters.
              </td>
            </tr>
          </template>
        </TableBase>
      </template>
    </CardBox>

    <!-- Create / Edit Modal -->
    <ModalBase :open="isModalOpen" @close="closeModal">
      <template #title>
        {{ editingEmployee ? 'Edit employee' : 'Add employee' }}
      </template>

      <template #body>
        <form class="space-y-3" @submit.prevent="handleSubmit">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <InputField
              v-model="form.first_name"
              label="First name"
              placeholder="Ali"
              required
            />
            <InputField
              v-model="form.last_name"
              label="Last name"
              placeholder="Hassan"
              required
            />
          </div>

          <InputField
            v-model="form.phone"
            label="Phone"
            placeholder="0600000000"
          />

          <InputField
            v-model="form.role"
            label="Role"
            placeholder="Waiter, Chef, Manager..."
          />

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <InputField
              v-model="form.hourly_rate"
              label="Hourly rate (MAD)"
              type="number"
              min="0"
              step="0.1"
              required
            />

            <InputField
              v-model="form.hire_date"
              label="Hire date"
              type="date"
            />
          </div>

          <div>
            <label
              class="block text-xs font-medium text-neutral-700 mb-1"
            >
              Status
            </label>
            <select
              v-model="form.status"
              required
              class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </form>
      </template>

      <template #footer>
        <div class="flex justify-end gap-2">
          <button
            type="button"
            class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
            @click="closeModal"
          >
            Cancel
          </button>
          <PrimaryButton
            size="sm"
            :disabled="employeesStore.saving"
            @click="handleSubmit"
          >
            <span v-if="!employeesStore.saving">
              {{ editingEmployee ? 'Save changes' : 'Create employee' }}
            </span>
            <span v-else>Saving...</span>
          </PrimaryButton>
        </div>
      </template>
    </ModalBase>

    <!-- Profile Modal (read-only) -->
    <ModalBase :open="isProfileOpen" @close="closeProfile">
      <template #title>
        Employee profile
      </template>

      <template #body>
        <div v-if="selectedEmployee" class="space-y-2 text-sm">
          <p class="font-semibold text-sm-dark">
            {{ fullName(selectedEmployee) }}
          </p>
          <p class="text-xs text-neutral-500">
            Role: {{ selectedEmployee.role || '—' }}
          </p>
          <p class="text-xs text-neutral-500">
            Phone: {{ selectedEmployee.phone || '—' }}
          </p>
          <p class="text-xs text-neutral-500">
            Hourly rate: {{ formatMoney(selectedEmployee.hourly_rate) }}/h
          </p>
          <p class="text-xs text-neutral-500">
            Hire date: {{ selectedEmployee.hire_date || '—' }}
          </p>
          <p class="text-xs text-neutral-500">
            Status:
            {{ selectedEmployee.status === 'active' ? 'Active' : 'Inactive' }}
          </p>
          <!-- Later we can add last attendances here -->
        </div>
      </template>

      <template #footer>
        <div class="flex justify-end">
          <button
            type="button"
            class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
            @click="closeProfile"
          >
            Close
          </button>
        </div>
      </template>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useEmployeesStore } from '@/stores/employees'

import CardBox from '@/components/ui/CardBox.vue'
import TableBase from '@/components/ui/TableBase.vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import ModalBase from '@/components/ui/ModalBase.vue'
import InputField from '@/components/ui/InputField.vue'

const employeesStore = useEmployeesStore()

const isModalOpen = ref(false)
const editingEmployee = ref(null)

const isProfileOpen = ref(false)
const selectedEmployee = ref(null)

const form = reactive({
  first_name: '',
  last_name: '',
  phone: '',
  role: '',
  hourly_rate: '',
  hire_date: '',
  status: 'active',
})

const filters = reactive({
  role: '',
  status: '',
  search: '',
})

onMounted(() => {
  employeesStore.fetchEmployees()
})

const fullName = (emp) =>
  `${emp.first_name || ''} ${emp.last_name || ''}`.trim()

const filteredEmployees = computed(() => {
  let list = employeesStore.employees || []

  if (filters.role) {
    list = list.filter((e) => (e.role || '') === filters.role)
  }

  if (filters.status) {
    list = list.filter((e) => e.status === filters.status)
  }

  if (filters.search.trim()) {
    const q = filters.search.toLowerCase()
    list = list.filter(
      (e) =>
        fullName(e).toLowerCase().includes(q) ||
        (e.phone || '').toLowerCase().includes(q)
    )
  }

  return list
})

const resetForm = () => {
  form.first_name = ''
  form.last_name = ''
  form.phone = ''
  form.role = ''
  form.hourly_rate = ''
  form.hire_date = ''
  form.status = 'active'
}

const openCreate = () => {
  editingEmployee.value = null
  resetForm()
  isModalOpen.value = true
}

const openEdit = (emp) => {
  editingEmployee.value = emp
  form.first_name = emp.first_name || ''
  form.last_name = emp.last_name || ''
  form.phone = emp.phone || ''
  form.role = emp.role || ''
  form.hourly_rate = emp.hourly_rate ?? ''
  form.hire_date = emp.hire_date || ''
  form.status = emp.status || 'active'
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
}

const openProfile = (emp) => {
  selectedEmployee.value = emp
  isProfileOpen.value = true
}

const closeProfile = () => {
  isProfileOpen.value = false
  selectedEmployee.value = null
}

const handleSubmit = async () => {
  if (employeesStore.saving) return

  const payload = {
    first_name: form.first_name,
    last_name: form.last_name,
    phone: form.phone || null,
    role: form.role || null,
    hourly_rate: Number(form.hourly_rate),
    hire_date: form.hire_date || null,
    status: form.status,
  }

  if (!editingEmployee.value) {
    await employeesStore.createEmployee(payload)
  } else {
    await employeesStore.updateEmployee(editingEmployee.value.id, payload)
  }

  isModalOpen.value = false
  resetForm()
}

const confirmDelete = async (emp) => {
  if (employeesStore.deletingId) return
  if (!window.confirm(`Delete employee ${fullName(emp)}?`)) return

  await employeesStore.deleteEmployee(emp.id)
}

const formatMoney = (value) => {
  if (value == null) return '0'
  const num = Number(value) || 0
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(num)
}
</script>
