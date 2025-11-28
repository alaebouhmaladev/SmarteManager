<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark">
          My Attendance
        </h2>
        <p class="text-xs text-neutral-500">
          Track your check-in and check-out times.
        </p>
      </div>
    </div>

    <!-- Check-in widget -->
    <div class="sm-card p-5 flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="space-y-1">
        <p class="text-xs text-neutral-500">
          Today – {{ today }}
        </p>
        <p class="text-sm text-neutral-700">
          Status:
          <span
            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px]"
            :class="isCheckedIn ? 'bg-emerald-50 text-emerald-700' : 'bg-neutral-100 text-neutral-600'"
          >
            <span
              class="h-1.5 w-1.5 rounded-full"
              :class="isCheckedIn ? 'bg-emerald-500' : 'bg-neutral-400'"
            ></span>
            {{ isCheckedIn ? 'Checked in' : 'Not checked in' }}
          </span>
        </p>

        <p class="text-xs text-neutral-500">
          Check-in:
          <span class="font-medium text-sm-dark">
            {{ todayRecord?.check_in || '—' }}
          </span>
          · Check-out:
          <span class="font-medium text-sm-dark">
            {{ todayRecord?.check_out || '—' }}
          </span>
        </p>
      </div>

      <div class="flex items-center gap-3">
        <button
          v-if="!isCheckedIn"
          class="inline-flex items-center justify-center px-4 py-2 rounded-xl
                 text-xs font-medium bg-sm-dark text-sm-cream hover:bg-black
                 disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="store.loading"
          @click="handleCheckIn"
        >
          {{ store.loading ? 'Working...' : 'Check in' }}
        </button>

        <button
          v-else
          class="inline-flex items-center justify-center px-4 py-2 rounded-xl
                 text-xs font-medium bg-white border border-neutral-300 text-sm-dark hover:bg-neutral-100
                 disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="store.loading"
          @click="handleCheckOut"
        >
          {{ store.loading ? 'Working...' : 'Check out' }}
        </button>
      </div>
    </div>

    <!-- History -->
    <TableBase>
      <template #head>
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
        <!-- Loading -->
        <tr v-if="store.loading && history.length === 0">
          <td colspan="5" class="px-4 py-6 text-center text-xs text-neutral-500">
            Loading your attendance...
          </td>
        </tr>

        <!-- Rows -->
        <tr
          v-for="row in history"
          :key="row.id"
          class="hover:bg-sm-cream/50 text-sm"
        >
          <td class="px-4 py-2 align-middle">
            {{ row.work_date }}
          </td>
          <td class="px-4 py-2 align-middle">
            {{ row.check_in || '—' }}
          </td>
          <td class="px-4 py-2 align-middle">
            {{ row.check_out || '—' }}
          </td>
          <td class="px-4 py-2 align-middle">
            {{ row.total_hours != null ? Number(row.total_hours).toFixed(2) : '—' }}
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

        <!-- Empty -->
        <tr v-if="!store.loading && history.length === 0">
          <td colspan="5" class="px-4 py-6 text-center text-xs text-neutral-500">
            No attendance records yet.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>{{ history.length }} day(s) tracked</span>
      </template>
    </TableBase>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import TableBase from '@/components/ui/TableBase.vue'
import { useMyAttendanceStore } from '@/stores/myAttendance'

const store = useMyAttendanceStore()

// today as YYYY-MM-DD
const today = new Date().toISOString().slice(0, 10)

// load month history on mount
onMounted(() => {
  store.fetchMyAttendance()
})

const history = computed(() => store.history)
const todayRecord = computed(() => store.todayRecord)
const isCheckedIn = computed(
  () => !!todayRecord.value?.check_in && !todayRecord.value?.check_out
)

// actions
async function handleCheckIn() {
  if (store.loading) return
  await store.checkIn()
}

async function handleCheckOut() {
  if (store.loading) return
  await store.checkOut()
}

// compute status for each row similar to AttendanceView
function computedStatus(row) {
  if (row.status) {
    const s = String(row.status).toLowerCase()
    if (s === 'present') return 'Present'
    if (s === 'late') return 'Late'
    if (s === 'absent') return 'Absent'
  }

  if (row.check_in && row.check_out) return 'Present'
  if (!row.check_in && !row.check_out) return 'Absent'
  return 'Present'
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
