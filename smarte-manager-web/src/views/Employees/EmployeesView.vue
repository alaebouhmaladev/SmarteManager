<template>
  <div class="space-y-4">

    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark dark:text-neutral-50">
          Employees
        </h2>
        <p class="text-xs text-neutral-500">
          Manage all your staff: add, edit or disable employee profiles.
        </p>
      </div>

      <PrimaryButton @click="openCreate">
        + New Employee
      </PrimaryButton>
    </div>

    <!-- Filters -->
    <div class="sm-card p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex-1 max-w-xs">
        <InputField
          v-model="search"
          label="Search"
          placeholder="Search name or position"
        />
      </div>

      <div class="flex items-center gap-2">
        <button
          v-for="option in statusFilters"
          :key="option.value"
          class="px-3 py-1.5 rounded-full text-xs border transition"
          :class="selectedStatus === option.value
            ? 'bg-sm-dark text-sm-cream border-sm-dark'
            : 'bg-white text-neutral-600 border-neutral-200 hover:bg-neutral-100'"
          @click="selectedStatus = option.value"
        >
          {{ option.label }}
        </button>
      </div>
    </div>

    <!-- Employees Table -->
    <TableBase>
      <template #head>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Name
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Position
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Salary (MAD/hr)
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Status
        </th>
        <th class="px-4 py-2 text-right text-[11px] font-medium text-neutral-500 uppercase">
          Actions
        </th>
      </template>

      <template #body>
        <tr
          v-for="emp in filteredEmployees"
          :key="emp.id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2 align-middle">
            <p class="font-medium text-sm-dark dark:text-neutral-100">
              {{ emp.name }}
            </p>
            <p class="text-[11px] text-neutral-500">
              ID: #{{ emp.id }}
            </p>
          </td>

          <td class="px-4 py-2 align-middle">
            <p class="text-sm text-neutral-700 dark:text-neutral-200">
              {{ emp.position }}
            </p>
          </td>

          <td class="px-4 py-2 align-middle">
            <span class="text-sm">
              {{ emp.hourly_rate }} MAD
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px]"
              :class="emp.active
                ? 'bg-emerald-50 text-emerald-700'
                : 'bg-neutral-100 text-neutral-500'"
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="emp.active ? 'bg-emerald-500' : 'bg-neutral-400'"
              ></span>
              {{ emp.active ? 'Active' : 'Inactive' }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle text-right">
            <div class="inline-flex items-center gap-2">
              <button
                class="text-xs text-sm-dark hover:underline"
                @click="openEdit(emp)"
              >
                Edit
              </button>
              <button
                class="text-xs text-red-500 hover:underline"
                @click="deleteEmployee(emp.id)"
              >
                Delete
              </button>
            </div>
          </td>
        </tr>

        <!-- Empty state -->
        <tr v-if="filteredEmployees.length === 0">
          <td colspan="5" class="px-4 py-6 text-center text-xs text-neutral-500">
            No employees found.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>
          Showing {{ filteredEmployees.length }} employee(s)
        </span>
      </template>
    </TableBase>

    <!-- Modal -->
    <ModalBase
      v-model="showModal"
      :title="isEditing ? 'Edit Employee' : 'Create Employee'"
      :subtitle="isEditing ? 'Update employee info.' : 'Add a new team member.'"
    >
      <form class="space-y-3" @submit.prevent="saveEmployee">
        <InputField
          v-model="form.name"
          label="Full name"
          placeholder="Ahmed R."
          required
        />

        <InputField
          v-model="form.position"
          label="Position"
          placeholder="Chef, Cashier, Worker..."
          required
        />

        <InputField
          v-model="form.hourly_rate"
          label="Hourly rate (MAD)"
          type="number"
          placeholder="15"
          required
        />

        <!-- Active toggle -->
        <div class="flex items-center justify-between pt-2">
          <span class="text-xs text-neutral-700">Active employee</span>
          <button
            type="button"
            class="relative inline-flex h-6 w-10 items-center rounded-full transition border border-neutral-200"
            :class="form.active ? 'bg-emerald-500' : 'bg-neutral-200'"
            @click="form.active = !form.active"
          >
            <span
              class="h-4 w-4 inline-block transform rounded-full bg-white shadow transition-transform"
              :class="form.active ? 'translate-x-4' : 'translate-x-1'"
            ></span>
          </button>
        </div>
      </form>

      <template #footer>
        <button
          class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
          @click="showModal = false"
        >
          Cancel
        </button>

        <PrimaryButton
          type="button"
          :loading="saving"
          @click="saveEmployee"
        >
          {{ isEditing ? 'Save changes' : 'Create employee' }}
        </PrimaryButton>
      </template>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import InputField from '@/components/ui/InputField.vue'
import TableBase from '@/components/ui/TableBase.vue'
import ModalBase from '@/components/ui/ModalBase.vue'

/* -------------------------------------------
   MOCK EMPLOYEES DATA
   (Replace with API later)
-------------------------------------------- */
const employees = ref([
  {
    id: 1,
    name: 'Ahmed Rami',
    position: 'Chef',
    hourly_rate: 25,
    active: true,
  },
  {
    id: 2,
    name: 'Said Nassim',
    position: 'Cashier',
    hourly_rate: 15,
    active: true,
  },
  {
    id: 3,
    name: 'Mouad Idrissi',
    position: 'Worker',
    hourly_rate: 13,
    active: false,
  },
])

/* -------------------------------------------
   Filters
-------------------------------------------- */
const search = ref('')
const selectedStatus = ref('all')

const statusFilters = [
  { value: 'all', label: 'All' },
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
]

const filteredEmployees = computed(() => {
  return employees.value.filter((e) => {
    const matchesSearch =
      e.name.toLowerCase().includes(search.value.toLowerCase()) ||
      e.position.toLowerCase().includes(search.value.toLowerCase())

    const matchesStatus =
      selectedStatus.value === 'all' ||
      (selectedStatus.value === 'active' && e.active) ||
      (selectedStatus.value === 'inactive' && !e.active)

    return matchesSearch && matchesStatus
  })
})

/* -------------------------------------------
   Modal & Form
-------------------------------------------- */
const showModal = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const editId = ref(null)

const form = reactive({
  name: '',
  position: '',
  hourly_rate: '',
  active: true,
})

function resetForm() {
  form.name = ''
  form.position = ''
  form.hourly_rate = ''
  form.active = true
  editId.value = null
  isEditing.value = false
}

function openCreate() {
  resetForm()
  showModal.value = true
}

function openEdit(emp) {
  form.name = emp.name
  form.position = emp.position
  form.hourly_rate = emp.hourly_rate
  form.active = emp.active
  editId.value = emp.id
  isEditing.value = true
  showModal.value = true
}

function saveEmployee() {
  saving.value = true

  setTimeout(() => {
    if (isEditing.value && editId.value != null) {
      const index = employees.value.findIndex((e) => e.id === editId.value)
      if (index !== -1) {
        employees.value[index] = {
          ...employees.value[index],
          name: form.name,
          position: form.position,
          hourly_rate: Number(form.hourly_rate),
          active: form.active,
        }
      }
    } else {
      const newId =
        employees.value.length > 0
          ? Math.max(...employees.value.map((e) => e.id)) + 1
          : 1

      employees.value.push({
        id: newId,
        name: form.name,
        position: form.position,
        hourly_rate: Number(form.hourly_rate),
        active: form.active,
      })
    }

    saving.value = false
    showModal.value = false
    resetForm()
  }, 400)
}

function deleteEmployee(id) {
  employees.value = employees.value.filter((e) => e.id !== id)
}
</script>
