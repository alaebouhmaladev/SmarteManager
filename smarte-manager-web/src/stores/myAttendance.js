// src/stores/myAttendance.js
import { defineStore } from 'pinia'
import http from '@/api/http'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'

export const useMyAttendanceStore = defineStore('myAttendance', {
  state: () => ({
    todayRecord: null,
    history: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchMyAttendance(month = null) {
      this.loading = true
      this.error = null

      const ui = useUiStore()
      const auth = useAuthStore()
      const m = month || new Date().toISOString().slice(0, 7)

      try {
        // You haven’t implemented /attendances/my?month=… yet,
        // so for now just use all attendances for this employee
        const { data } = await http.get('/attendances/employee/' + auth.user.employee_id, {
          params: {
            from: `${m}-01`,
            to: `${m}-31`,
          },
        })

        // API returns { employee_id, from, to, attendances: [] }
        this.history = data.attendances || []

        const today = new Date().toISOString().slice(0, 10)
        this.todayRecord =
          this.history.find(r => r.work_date === today) || null
      } catch (err) {
        console.error('FETCH MY ATTENDANCE ERROR', err)
        ui.pushToast({
          type: 'error',
          title: 'Attendance',
          message: 'Failed to load your attendance records.',
        })
      } finally {
        this.loading = false
      }
    },

    async checkIn() {
      const ui = useUiStore()
      const auth = useAuthStore()

      const employeeId = auth.user?.employee_id
      if (!employeeId) {
        ui.pushToast({
          type: 'error',
          title: 'Check-in failed',
          message: 'No employee is linked to your user account.',
        })
        return
      }

      try {
        const { data } = await http.post('/attendance/check-in', {
          employee_id: employeeId,
        })

        this.todayRecord = data
        this.history.unshift(data)

        ui.pushToast({
          type: 'success',
          title: 'Checked In',
          message: `Time: ${data.check_in}`,
        })
      } catch (err) {
        ui.pushToast({
          type: 'error',
          title: 'Check-in failed',
          message: err.response?.data?.message || 'Unable to check in.',
        })
      }
    },

    async checkOut() {
      const ui = useUiStore()
      const auth = useAuthStore()

      const employeeId = auth.user?.employee_id
      if (!employeeId) {
        ui.pushToast({
          type: 'error',
          title: 'Check-out failed',
          message: 'No employee is linked to your user account.',
        })
        return
      }

      try {
        const { data } = await http.post('/attendance/check-out', {
          employee_id: employeeId,
        })

        this.todayRecord = data

        const idx = this.history.findIndex(h => h.id === data.id)
        if (idx !== -1) this.history[idx] = data

        ui.pushToast({
          type: 'success',
          title: 'Checked Out',
          message: `Time: ${data.check_out}`,
        })
      } catch (err) {
        ui.pushToast({
          type: 'error',
          title: 'Check-out failed',
          message: err.response?.data?.message || 'Unable to check out.',
        })
      }
    },
  },
})
