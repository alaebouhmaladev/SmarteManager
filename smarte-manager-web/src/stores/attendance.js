// src/stores/attendance.js
import { defineStore } from 'pinia'
import http from '@/api/http'
import { useUiStore } from '@/stores/ui'

export const useAttendanceStore = defineStore('attendance', {
  state: () => ({
    // main list (index)
    records: [],

    // extras for other screens
    daily: null,            // { date, attendances: [...] }
    employeeHistory: null,  // { employee_id, from, to, attendances: [...] }
    monthlySummary: null,   // { month, summary: [...] }

    loading: false,
    exporting: false,
    error: null,
  }),

  actions: {
    /* --------------------------------------------------
     * List all attendance (GET /attendances)
     * -------------------------------------------------- */
    async fetchAll() {
      this.loading = true
      this.error = null
      const ui = useUiStore()

      try {
        const { data } = await http.get('/attendances')
        this.records = data
      } catch (err) {
        console.warn('ATTENDANCE LIST ERROR', err.response?.status, err.response?.data)
        this.error = 'Failed to load attendance.'
        ui.pushToast({
          type: 'error',
          title: 'Attendance',
          message: 'Failed to load attendance records from server.',
        })
      } finally {
        this.loading = false
      }
    },

    /* --------------------------------------------------
     * Manual check-in for an employee
     * POST /attendance/check-in { employee_id }
     * -------------------------------------------------- */
    async checkInForEmployee(employeeId) {
      const ui = useUiStore()

      if (!employeeId) {
        ui.pushToast({
          type: 'error',
          title: 'Check-in failed',
          message: 'Please select an employee first.',
        })
        return
      }

      try {
        const { data } = await http.post('/attendance/check-in', {
          employee_id: employeeId,
        })

        // add new row to main list (top)
        this.records.unshift(data)

        ui.pushToast({
          type: 'success',
          title: 'Check-in recorded',
          message: `Employee #${employeeId} checked in.`,
        })

        return data
      } catch (err) {
        console.error('CHECK-IN ERROR', err)
        const msg =
          err.response?.data?.message ||
          err.response?.data?.errors?.employee_id?.[0] ||
          'Unable to check in.'
        ui.pushToast({
          type: 'error',
          title: 'Check-in failed',
          message: msg,
        })
      }
    },

    /* --------------------------------------------------
     * Manual check-out for an employee
     * POST /attendance/check-out { employee_id }
     * -------------------------------------------------- */
    async checkOutForEmployee(employeeId) {
      const ui = useUiStore()

      if (!employeeId) {
        ui.pushToast({
          type: 'error',
          title: 'Check-out failed',
          message: 'Please select an employee first.',
        })
        return
      }

      try {
        const { data } = await http.post('/attendance/check-out', {
          employee_id: employeeId,
        })

        // update the row in main list
        const idx = this.records.findIndex((r) => r.id === data.id)
        if (idx !== -1) {
          this.records[idx] = data
        } else {
          this.records.unshift(data)
        }

        ui.pushToast({
          type: 'success',
          title: 'Check-out recorded',
          message: `Employee #${employeeId} checked out.`,
        })

        return data
      } catch (err) {
        console.error('CHECK-OUT ERROR', err)
        const msg =
          err.response?.data?.message ||
          err.response?.data?.errors?.employee_id?.[0] ||
          'Unable to check out.'
        ui.pushToast({
          type: 'error',
          title: 'Check-out failed',
          message: msg,
        })
      }
    },

    /* --------------------------------------------------
     * Daily view (GET /attendances/daily?date=YYYY-MM-DD)
     * -------------------------------------------------- */
    async fetchDaily(date = null) {
      this.loading = true
      this.error = null
      const ui = useUiStore()

      const params = {}
      if (date) params.date = date

      try {
        const { data } = await http.get('/attendances/daily', { params })
        this.daily = data
      } catch (err) {
        console.error('DAILY ATTENDANCE ERROR', err)
        this.error = 'Failed to load daily attendance.'
        ui.pushToast({
          type: 'error',
          title: 'Attendance',
          message: 'Failed to load daily attendance.',
        })
      } finally {
        this.loading = false
      }
    },

    /* --------------------------------------------------
     * History for one employee
     * GET /attendances/employee/{id}?from=&to=
     * -------------------------------------------------- */
    async fetchByEmployee(employeeId, { from = null, to = null } = {}) {
      if (!employeeId) return

      this.loading = true
      this.error = null
      const ui = useUiStore()

      const params = {}
      if (from) params.from = from
      if (to) params.to = to

      try {
        const { data } = await http.get(`/attendances/employee/${employeeId}`, {
          params,
        })
        this.employeeHistory = data
      } catch (err) {
        console.error('EMPLOYEE ATTENDANCE ERROR', err)
        this.error = 'Failed to load employee attendance.'
        ui.pushToast({
          type: 'error',
          title: 'Attendance',
          message: 'Failed to load this employee attendance.',
        })
      } finally {
        this.loading = false
      }
    },

    /* --------------------------------------------------
     * Monthly summary
     * GET /attendances/monthly-summary?month=YYYY-MM
     * -------------------------------------------------- */
    async fetchMonthlySummary(month = null) {
      this.loading = true
      this.error = null
      const ui = useUiStore()

      const params = {}
      if (month) params.month = month

      try {
        const { data } = await http.get('/attendances/monthly-summary', {
          params,
        })
        this.monthlySummary = data
      } catch (err) {
        console.error('MONTHLY SUMMARY ERROR', err)
        this.error = 'Failed to load monthly summary.'
        ui.pushToast({
          type: 'error',
          title: 'Attendance',
          message: 'Failed to load monthly summary.',
        })
      } finally {
        this.loading = false
      }
    },

    /* --------------------------------------------------
     * Export CSV
     * GET /attendances/export-csv?month=YYYY-MM
     * -------------------------------------------------- */
    async exportMonthlyCsv(month = null) {
      this.exporting = true
      const ui = useUiStore()

      try {
        const params = new URLSearchParams()
        if (month) params.set('month', month)

        // use baseURL from axios to build full URL
        const base = http.defaults.baseURL?.replace(/\/$/, '') || ''
        const url = `${base}/attendances/export-csv?${params.toString()}`

        window.open(url, '_blank')
      } catch (err) {
        console.error('EXPORT CSV ERROR', err)
        ui.pushToast({
          type: 'error',
          title: 'Export failed',
          message: 'Could not export CSV.',
        })
      } finally {
        this.exporting = false
      }
    },
  },
})
