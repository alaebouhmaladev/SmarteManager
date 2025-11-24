<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark dark:text-neutral-50">
          Users
        </h2>
        <p class="text-xs text-neutral-500">
          Manage admins, managers and staff accounts.
        </p>
      </div>

      <PrimaryButton @click="openCreate">
        + New User
      </PrimaryButton>
    </div>

    <!-- Filters -->
    <div class="sm-card p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex-1 max-w-xs">
        <InputField
          v-model="search"
          label="Search"
          placeholder="Search by name or email"
        />
      </div>

      <div class="flex items-center gap-2">
        <button
          v-for="option in roleFilters"
          :key="option.value"
          class="px-3 py-1.5 rounded-full text-xs border transition"
          :class="selectedRole === option.value
            ? 'bg-sm-dark text-sm-cream border-sm-dark'
            : 'bg-white text-neutral-600 border-neutral-200 hover:bg-neutral-100'"
          @click="selectedRole = option.value"
        >
          {{ option.label }}
        </button>
      </div>
    </div>

    <!-- Users table -->
    <TableBase>
      <template #head>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Name
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Email
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Role
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
          v-for="user in filteredUsers"
          :key="user.id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2 align-middle">
            <p class="font-medium text-sm-dark dark:text-neutral-100">
              {{ user.name }}
            </p>
            <p class="text-[11px] text-neutral-500">
              ID: #{{ user.id }}
            </p>
          </td>

          <td class="px-4 py-2 align-middle">
            <p class="text-sm text-neutral-700 dark:text-neutral-200">
              {{ user.email }}
            </p>
          </td>

          <td class="px-4 py-2 align-middle">
            <span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px] font-medium"
              :class="roleBadgeClass(user.role)"
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="roleDotClass(user.role)"
              ></span>
              {{ user.role.toUpperCase() }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle">
            <span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px]"
              :class="user.active
                ? 'bg-emerald-50 text-emerald-700'
                : 'bg-neutral-100 text-neutral-500'"
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="user.active ? 'bg-emerald-500' : 'bg-neutral-400'"
              ></span>
              {{ user.active ? 'Active' : 'Disabled' }}
            </span>
          </td>

          <td class="px-4 py-2 align-middle text-right">
            <div class="inline-flex items-center gap-2">
              <button
                class="text-xs text-sm-dark hover:underline"
                @click="openEdit(user)"
              >
                Edit
              </button>
              <button
                class="text-xs text-red-500 hover:underline"
                @click="deleteUser(user.id)"
              >
                Delete
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="filteredUsers.length === 0">
          <td colspan="5" class="px-4 py-6 text-center text-xs text-neutral-500">
            No users found with current filters.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>
          Showing {{ filteredUsers.length }} user(s)
        </span>
      </template>
    </TableBase>

    <!-- Create / Edit Modal -->
    <ModalBase
      v-model="showModal"
      :title="isEditing ? 'Edit user' : 'Create user'"
      :subtitle="isEditing ? 'Update role or status.' : 'Add a new SmartManager user.'"
    >
      <form class="space-y-3" @submit.prevent="saveUser">
        <InputField
          v-model="form.name"
          label="Full name"
          placeholder="Nabil Example"
          required
        />
        <InputField
          v-model="form.email"
          label="Email"
          type="email"
          placeholder="user@example.com"
          required
        />

        <!-- Role select -->
        <div class="space-y-1.5">
          <label class="block text-xs font-medium text-neutral-700">
            Role
          </label>
          <select
            v-model="form.role"
            class="w-full rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
          >
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="staff">Staff</option>
          </select>
        </div>

        <!-- Active toggle -->
        <div class="flex items-center justify-between pt-2">
          <span class="text-xs text-neutral-700">
            Active account
          </span>
          <button
            type="button"
            class="relative inline-flex h-6 w-10 items-center rounded-full transition
                   border border-neutral-200"
            :class="form.active ? 'bg-emerald-500' : 'bg-neutral-200'"
            @click="form.active = !form.active"
          >
            <span
              class="inline-block h-4 w-4 transform rounded-full bg-white shadow
                     transition-transform"
              :class="form.active ? 'translate-x-4' : 'translate-x-1'"
            />
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
          @click="saveUser"
        >
          {{ isEditing ? 'Save changes' : 'Create user' }}
        </PrimaryButton>
      </template>
    </ModalBase>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import InputField from '@/components/ui/InputField.vue'
import TableBase from '@/components/ui/TableBase.vue'
import ModalBase from '@/components/ui/ModalBase.vue'

// MOCK USERS DATA (replace with API later)
const users = ref([
  {
    id: 1,
    name: 'Admin Master',
    email: 'admin@smartmanager.test',
    role: 'admin',
    active: true,
  },
  {
    id: 2,
    name: 'Manager One',
    email: 'manager1@smartmanager.test',
    role: 'manager',
    active: true,
  },
  {
    id: 3,
    name: 'Manager Two',
    email: 'manager2@smartmanager.test',
    role: 'manager',
    active: true,
  },
  {
    id: 4,
    name: 'Staff Employee',
    email: 'staff1@smartmanager.test',
    role: 'staff',
    active: true,
  },
  {
    id: 5,
    name: 'Old Staff',
    email: 'staff2@smartmanager.test',
    role: 'staff',
    active: false,
  },
])

const search = ref('')
const selectedRole = ref('all')

const roleFilters = [
  { value: 'all', label: 'All roles' },
  { value: 'admin', label: 'Admins' },
  { value: 'manager', label: 'Managers' },
  { value: 'staff', label: 'Staff' },
]

const filteredUsers = computed(() => {
  return users.value.filter((u) => {
    const matchesSearch =
      u.name.toLowerCase().includes(search.value.toLowerCase()) ||
      u.email.toLowerCase().includes(search.value.toLowerCase())

    const matchesRole =
      selectedRole.value === 'all' || u.role === selectedRole.value

    return matchesSearch && matchesRole
  })
})

// Modal + form
const showModal = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const editId = ref(null)

const form = reactive({
  name: '',
  email: '',
  role: 'staff',
  active: true,
})

function resetForm() {
  form.name = ''
  form.email = ''
  form.role = 'staff'
  form.active = true
  editId.value = null
  isEditing.value = false
}

function openCreate() {
  resetForm()
  showModal.value = true
}

function openEdit(user) {
  form.name = user.name
  form.email = user.email
  form.role = user.role
  form.active = user.active
  editId.value = user.id
  isEditing.value = true
  showModal.value = true
}

function saveUser() {
  saving.value = true

  setTimeout(() => {
    if (isEditing.value && editId.value != null) {
      const index = users.value.findIndex((u) => u.id === editId.value)
      if (index !== -1) {
        users.value[index] = {
          ...users.value[index],
          name: form.name,
          email: form.email,
          role: form.role,
          active: form.active,
        }
      }
    } else {
      const newId =
        users.value.length > 0
          ? Math.max(...users.value.map((u) => u.id)) + 1
          : 1
      users.value.push({
        id: newId,
        name: form.name,
        email: form.email,
        role: form.role,
        active: form.active,
      })
    }

    saving.value = false
    showModal.value = false
    resetForm()
  }, 400) // small fake delay for UX
}

function deleteUser(id) {
  users.value = users.value.filter((u) => u.id !== id)
}

function roleBadgeClass(role) {
  switch (role) {
    case 'admin':
      return 'bg-sm-dark text-sm-cream'
    case 'manager':
      return 'bg-sm-yellow/90 text-sm-dark'
    default:
      return 'bg-neutral-100 text-neutral-700'
  }
}

function roleDotClass(role) {
  switch (role) {
    case 'admin':
      return 'bg-sm-cream'
    case 'manager':
      return 'bg-sm-dark'
    default:
      return 'bg-neutral-500'
  }
}
</script>
