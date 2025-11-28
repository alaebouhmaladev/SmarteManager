// src/stores/payroll.js
import { defineStore } from 'pinia'
import http from '@/api/http'
import { useUiStore } from '@/stores/ui'

export const usePayrollStore = defineStore('payroll', {
  state: () => ({
    // current selected month (YYYY-MM)
    month: null,

    // main monthly payroll summary
    // from PayrollController::monthly() -> employees[]
    list: [], // [{ employee_id, employee_name, total_hours, hourly_rate, salary }]

    // total payroll amount for that month
    totalPayroll: 0,

    // detailed payroll for one employee
    // from PayrollController::employeeMonthly()
    // {
    //   month,
    //   employee: { id, first_name, last_name, hourly_rate },
    //   total_hours,
    //   salary,
    //   attendances: [...]
    // }
    employeeDetails: null,

    loading: false,
    loadingEmployee: false,
    exporting: false,
    error: null,
  }),

  actions: {
    /* --------------------------------------------------
     * Monthly payroll list
     * GET /payroll/monthly?month=YYYY-MM
     * Response:
     * {
     *   month: "2025-11",
     *   total_payroll: 999.99,
     *   employees: [...]
     * }
     * -------------------------------------------------- */
    async fetchMonthly(month = null) {
      this.loading = true
      this.error = null
      const ui = useUiStore()

      const params = {}
      if (month) params.month = month

      try {
        const { data } = await http.get('/payroll/monthly', { params })

        this.list = data?.employees || []
        this.totalPayroll = data?.total_payroll ?? 0
        this.month = data?.month || month || this.month
      } catch (err) {
        console.warn('PAYROLL MONTHLY ERROR', err.response?.status, err.response?.data)
        this.error = 'Failed to load monthly payroll.'
        ui.pushToast({
          type: 'error',
          title: 'Payroll',
          message: 'Failed to load monthly payroll from server.',
        })
      } finally {
        this.loading = false
      }
    },

    /* --------------------------------------------------
     * Employee monthly payroll details
     * GET /payroll/employee/{employee}?month=YYYY-MM
     * Response:
     * {
     *   month,
     *   employee: { id, first_name, last_name, hourly_rate },
     *   total_hours,
     *   salary,
     *   attendances: [...]
     * }
     * -------------------------------------------------- */
    async fetchEmployeeMonthly(employeeId, month = null) {
      if (!employeeId) return

      this.loadingEmployee = true
      this.error = null
      const ui = useUiStore()

      const params = {}
      if (month) params.month = month
      else if (this.month) params.month = this.month

      try {
        const { data } = await http.get(`/payroll/employee/${employeeId}`, {
          params,
        })

        this.employeeDetails = data
      } catch (err) {
        console.error('PAYROLL EMPLOYEE ERROR', err)
        this.error = 'Failed to load employee payroll.'
        ui.pushToast({
          type: 'error',
          title: 'Payroll',
          message: 'Failed to load this employee payroll details.',
        })
      } finally {
        this.loadingEmployee = false
      }
    },

    /* --------------------------------------------------
     * Export monthly payroll as CSV
     * GET /payroll/export-csv?month=YYYY-MM
     * -------------------------------------------------- */
    async exportMonthlyCsv(month = null) {
      this.exporting = true
      const ui = useUiStore()

      try {
        const params = new URLSearchParams()
        const effectiveMonth = month || this.month
        if (effectiveMonth) params.set('month', effectiveMonth)

        const base = http.defaults.baseURL?.replace(/\/$/, '') || ''
        const url = `${base}/payroll/export-csv?${params.toString()}`

        window.open(url, '_blank')
      } catch (err) {
        console.error('PAYROLL EXPORT CSV ERROR', err)
        ui.pushToast({
          type: 'error',
          title: 'Export failed',
          message: 'Could not export payroll CSV.',
        })
      } finally {
        this.exporting = false
      }
    },
  },
})
