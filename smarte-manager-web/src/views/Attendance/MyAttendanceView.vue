<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark dark:text-neutral-50">
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
        <p class="text-sm text-neutral-700 dark:text-neutral-200">
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
          Check-in: <span class="font-medium text-sm-dark">{{ todayRecord?.check_in || '—' }}</span>
          · Check-out:
          <span class="font-medium text-sm-dark">{{ todayRecord?.check_out || '—' }}</span>
        </p>
      </div>

      <div class="flex items-center gap-3">
        <PrimaryButton
          v-if="!isCheckedIn"
          @click="handleCheckIn"
        >
          Check in
        </PrimaryButton>

        <PrimaryButton
          v-else
          variant="secondary"
          @click="handleCheckOut"
        >
          Check out
        </PrimaryButton>
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
        <tr
          v-for="row in history"
          :key="row.date"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2 align-middle">
            {{ row.date }}
          </td>
          <td class="px-4 py-2 align-middle">
            {{ row.check_in || '—' }}
          </td>
          <td class="px-4 py-2 align-middle">
            {{ row.check_out || '—' }}
          </td>
          <td class="px-4 py-2 align-middle">
            {{ row.total_hours != null ? row.total_hours.toFixed(2) : '—' }}
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

        <tr v-if="history.length === 0">
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
import { computed, ref } from 'vue'
import TableBase from '@/components/ui/TableBase.vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'

// Helper to get today's date as YYYY-MM-DD
const today = new Date().toISOString().slice(0, 10)

// MOCK history – last few days
const history = ref([
  {
    date: '2025-11-20',
    check_in: '09:10',
    check_out: '17:00',
    total_hours: 7.8,
    status: 'Present',
  },
  {
    date: '2025-11-19',
    check_in: '09:25',
    check_out: '16:55',
    total_hours: 7.5,
    status: 'Late',
  },
  {
    date: '2025-11-18',
    check_in: null,
    check_out: null,
    total_hours: null,
    status: 'Absent',
  },
])

// Today record in history or null
const todayRecord = computed(() =>
  history.value.find((h) => h.date === today) || null
)

const isCheckedIn = computed(() => !!todayRecord.value?.check_in && !todayRecord.value?.check_out)

// For now we only simulate in-memory; later we’ll call API
function handleCheckIn() {
  if (todayRecord.value && todayRecord.value.check_in) return

  const now = getTimeString()

  if (todayRecord.value) {
    todayRecord.value.check_in = now
    todayRecord.value.status = 'Present'
  } else {
    history.value.unshift({
      date: today,
      check_in: now,
      check_out: null,
      total_hours: null,
      status: 'Present',
    })
  }
}

function handleCheckOut() {
  if (!todayRecord.value || todayRecord.value.check_out) return

  const now = getTimeString()
  todayRecord.value.check_out = now

  // Fake calculation: 8h if both times exist
  todayRecord.value.total_hours = 8
}

function getTimeString() {
  const d = new Date()
  const hh = String(d.getHours()).padStart(2, '0')
  const mm = String(d.getMinutes()).padStart(2, '0')
  return `${hh}:${mm}`
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
