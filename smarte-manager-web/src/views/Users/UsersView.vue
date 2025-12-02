<template>
  <div class="space-y-4">
    <!-- Header -->
    <div
      class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
      <div>
        <h2 class="text-lg font-semibold text-sm-dark">
          Users
        </h2>
        <p class="text-xs text-neutral-500">
          Manage admin, manager, HR, stock manager and staff accounts.
        </p>
      </div>

      <PrimaryButton size="sm" @click="openCreate">
        + Add user
      </PrimaryButton>
    </div>

    <!-- Filters / search -->
    <div
      class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
    >
      <p class="text-xs text-neutral-500">
        Total users: {{ filteredUsers.length }}
      </p>

      <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
        <!-- Role filter -->
        <select
          v-model="filters.role"
          class="rounded-xl border border-neutral-200 px-3 py-2 text-xs
                 bg-white focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
        >
          <option
            v-for="opt in roleFilterOptions"
            :key="opt.value"
            :value="opt.value"
          >
            {{ opt.label }}
          </option>
        </select>

        <!-- Search -->
        <div
          class="flex items-center gap-2 rounded-xl border border-neutral-200 bg-white px-3 py-2"
        >
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search name or email..."
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
              Name
            </th>
            <th
              class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase"
            >
              Email
            </th>
            <th
              class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase"
            >
              Role
            </th>
            <th
              class="px-4 py-2 text-right text-[11px] font-medium text-neutral-500 uppercase"
            >
              Actions
            </th>
          </template>

          <template #body>
            <tr
              v-for="user in filteredUsers"
              :key="user.id"
              class="hover:bg-sm-cream/50 text-sm"
            >
              <td class="px-4 py-2">
                <p class="font-medium text-sm-dark">
                  {{ user.name }}
                </p>
              </td>
              <td class="px-4 py-2">
                <span class="text-xs text-neutral-500">
                  {{ user.email }}
                </span>
              </td>
              <td class="px-4 py-2">
                <span class="sm-badge">
                  {{ roleLabel(user.role) }}
                </span>
              </td>
              <td class="px-4 py-2 text-right">
                <div class="inline-flex items-center gap-2">
                  <button
                    class="text-xs text-sm-dark hover:underline"
                    @click="openEdit(user)"
                  >
                    Edit
                  </button>
                  <button
                    class="text-xs text-red-500 hover:underline"
                    :disabled="usersStore.deletingId === user.id"
                    @click="confirmDelete(user)"
                  >
                    <span v-if="usersStore.deletingId === user.id">
                      Deleting...
                    </span>
                    <span v-else>Delete</span>
                  </button>
                </div>
              </td>
            </tr>

            <tr
              v-if="!usersStore.loading && filteredUsers.length === 0"
            >
              <td
                colspan="4"
                class="px-4 py-6 text-center text-xs text-neutral-500"
              >
                No users match your filters.
              </td>
            </tr>
          </template>
        </TableBase>
      </template>
    </CardBox>

    <!-- Create / Edit Modal -->
    <ModalBase :open="isModalOpen" @close="closeModal">
      <template #title>
        {{ editingUser ? 'Edit user' : 'Add user' }}
      </template>

      <template #body>
        <form class="space-y-3" @submit.prevent="handleSubmit">
          <InputField
            v-model="form.name"
            label="Full name"
            placeholder="Full Name"
            required
          />

          <InputField
            v-model="form.email"
            label="Email"
            type="email"
            placeholder="user@example.com"
            required
          />

          <div>
            <label
              class="block text-xs font-medium text-neutral-700 mb-1"
            >
              Role
            </label>
            <select
              v-model="form.role"
              required
              class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
            >
              <option
                v-for="opt in roleOptions"
                :key="opt.value"
                :value="opt.value"
              >
                {{ opt.label }}
              </option>
            </select>
          </div>

          <div v-if="!editingUser">
            <InputField
              v-model="form.password"
              label="Password"
              type="password"
              placeholder="••••••••"
              required
            />
          </div>

          <div v-else>
            <p class="text-[11px] text-neutral-500 mb-1">
              Leave password empty if you don't want to change it.
            </p>
            <InputField
              v-model="form.password"
              label="New password (optional)"
              type="password"
              placeholder="••••••••"
            />
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
            :disabled="usersStore.saving"
            @click="handleSubmit"
          >
            <span v-if="!usersStore.saving">
              {{ editingUser ? 'Save changes' : 'Create user' }}
            </span>
            <span v-else>Saving...</span>
          </PrimaryButton>
        </div>
      </template>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useUsersStore } from '@/stores/users'
import { useAuthStore } from '@/stores/auth'

import CardBox from '@/components/ui/CardBox.vue'
import TableBase from '@/components/ui/TableBase.vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import ModalBase from '@/components/ui/ModalBase.vue'
import InputField from '@/components/ui/InputField.vue'

const usersStore = useUsersStore()
const auth = useAuthStore()

const isModalOpen = ref(false)
const editingUser = ref(null)

const form = reactive({
  name: '',
  email: '',
  role: 'staff',
  password: '',
})

const filters = reactive({
  role: '',
  search: '',
})

/* ---------------- ROLE OPTIONS ---------------- */

// master list (matches backend: admin, manager, hr, stock_manager, staff)
const ALL_ROLE_OPTIONS = [
  { value: 'admin',         label: 'Admin' },
  { value: 'manager',       label: 'Manager' },
  { value: 'hr',            label: 'HR' },
  { value: 'stock_manager', label: 'Stock manager' },
  { value: 'staff',         label: 'Staff' },
]

// dropdown in the modal – depends on current user role
const roleOptions = computed(() => {
  const currentRole = auth.userRole || auth.user?.role

  // Managers can only create staff accounts (same rule as backend)
  if (currentRole === 'manager') {
    return ALL_ROLE_OPTIONS.filter((r) => r.value === 'staff')
  }

  // Admin (and others) see everything
  return ALL_ROLE_OPTIONS
})

// dropdown for filter (always show all roles)
const roleFilterOptions = [
  { value: '', label: 'All roles' },
  ...ALL_ROLE_OPTIONS,
]

// pretty label for table badge
const roleLabel = (role) => {
  const found = ALL_ROLE_OPTIONS.find((r) => r.value === role)
  return found ? found.label : (role || '').toString().toUpperCase()
}

/* ---------------- LIFECYCLE ---------------- */

onMounted(() => {
  usersStore.fetchUsers()
})

/* ---------------- COMPUTED LIST ---------------- */

const filteredUsers = computed(() => {
  let list = usersStore.users || []

  if (filters.role) {
    list = list.filter((u) => u.role === filters.role)
  }

  if (filters.search.trim()) {
    const q = filters.search.toLowerCase()
    list = list.filter(
      (u) =>
        u.name.toLowerCase().includes(q) ||
        u.email.toLowerCase().includes(q),
    )
  }

  return list
})

/* ---------------- MODAL HANDLERS ---------------- */

const resetForm = () => {
  form.name = ''
  form.email = ''
  form.role = 'staff'
  form.password = ''
}

const openCreate = () => {
  editingUser.value = null
  resetForm()
  isModalOpen.value = true
}

const openEdit = (user) => {
  editingUser.value = user
  form.name = user.name
  form.email = user.email
  form.role = user.role
  form.password = ''
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
}

/* ---------------- SUBMIT / DELETE ---------------- */

const handleSubmit = async () => {
  if (usersStore.saving) return

  const payload = {
    name: form.name,
    email: form.email,
    role: form.role,
  }

  if (!editingUser.value || form.password) {
    payload.password = form.password
  }

  if (!editingUser.value) {
    await usersStore.createUser(payload)
  } else {
    await usersStore.updateUser(editingUser.value.id, payload)
  }

  isModalOpen.value = false
  resetForm()
}

const confirmDelete = async (user) => {
  if (usersStore.deletingId) return
  if (!window.confirm(`Delete user ${user.name}?`)) return

  await usersStore.deleteUser(user.id)
}
</script>
