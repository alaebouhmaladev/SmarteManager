// src/stores/dashboard.js
import { defineStore } from 'pinia'
import http from '@/api/http'
import { useUiStore } from '@/stores/ui'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
dayjs.extend(relativeTime)

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    me: null,

    // main overview from /dashboard/overview
    overview: null,

    // OLD weekly chart (kept in case you need it later)
    weekAttendance: [],

    // NEW: detailed list of today's attendance
    todayAttendance: [],

    employees: [],
    latestEmployees: [],

    expensesSummary: null,
    expenses: [],
    expenseCategories: [],
    invoicesCount: 0,

    payroll: null,

    inventoryValuation: 0,
    lowStockCount: 0,

    stockMovements: [],
    latestStockMovements: [],

    suppliers: [],
    supplierOverviews: [],

    activityFeed: [],

    loading: false,
    error: null,
  }),

  actions: {
    async loadDashboard() {
      const ui = useUiStore()
      this.loading = true
      this.error = null

      try {
        // =============================================================
        // 1) USER
        // =============================================================
        const meRes = await http.get('/auth/me')
        this.me = meRes.data

        // =============================================================
        // 2) OVERVIEW (employees, inventory, attendance, payroll)
        // =============================================================
        const overviewRes = await http.get('/dashboard/overview')
        this.overview = overviewRes.data

        // =============================================================
        // 3) TODAY ATTENDANCE (detailed list, from /attendances)
        // =============================================================
        const allAttRes = await http.get('/attendances')
        const allAttendance = allAttRes.data || []
        const todayStr = dayjs().format('YYYY-MM-DD')

        this.todayAttendance = allAttendance.filter(
          (a) => a.work_date === todayStr
        )

        // (Optional) keep weekAttendance empty or compute later if you want

        // =============================================================
        // 4) EMPLOYEES (for recent activity)
        // =============================================================
        const empRes = await http.get('/employees')
        this.employees = empRes.data
        this.latestEmployees = this.employees.slice(0, 3)

        // =============================================================
        // 5) EXPENSES + SUMMARY
        // =============================================================
        const expRes = await http.get('/expenses')
        this.expenses = expRes.data
        this.invoicesCount = expRes.data.length

        const expSum = await http.get('/expenses/monthly-summary')
        this.expensesSummary = expSum.data
        this.expenseCategories = expSum.data.by_category

        // =============================================================
        // 6) PAYROLL
        // =============================================================
        const payRes = await http.get('/payroll/monthly')
        this.payroll = payRes.data

        // =============================================================
        // 7) INVENTORY (valuation + low-stock)
        // =============================================================
        const valRes = await http.get('/inventory/valuation')
        this.inventoryValuation = valRes.data.total_value

        const lowRes = await http.get('/inventory/low-stock')
        this.lowStockCount = lowRes.data.length

        // =============================================================
        // 8) STOCK MOVEMENTS (for recent activity)
        // =============================================================
        const movRes = await http.get('/stock-movements')
        this.stockMovements = movRes.data
        this.latestStockMovements = this.stockMovements.slice(0, 3)

        // =============================================================
        // 9) SUPPLIERS + overviews
        // =============================================================
        const supRes = await http.get('/suppliers')
        this.suppliers = supRes.data

        const supplierOverviews = []
        for (const s of this.suppliers) {
          const ov = await http.get(`/suppliers/${s.id}/overview`)
          supplierOverviews.push({
            supplier: s,
            overview: ov.data,
          })
        }
        this.supplierOverviews = supplierOverviews

        // =============================================================
        // 10) ACTIVITY FEED (MERGED)
        // =============================================================
        this.activityFeed = this.buildActivityFeed()
      } catch (err) {
        console.error(err)
        this.error = 'Error loading dashboard.'
        ui.pushToast({
          type: 'error',
          title: 'Dashboard',
          message: 'Failed to load dashboard data.',
        })
      } finally {
        this.loading = false
      }
    },

    buildActivityFeed() {
      const feed = []

      // Employees
      this.latestEmployees.forEach((e) => {
        feed.push({
          type: 'employee',
          title: 'New employee added',
          message: `You added “${e.first_name} ${e.last_name}” to HR records.`,
          date: e.created_at || e.hire_date,
        })
      })

      // Expenses
      this.expenses.slice(0, 3).forEach((ex) => {
        feed.push({
          type: 'expense',
          title: 'Expense registered',
          message: `${ex.category} expense of ${ex.amount} MAD recorded.`,
          date: ex.expense_date,
        })
      })

      // Stock Movements
      this.latestStockMovements.forEach((m) => {
        feed.push({
          type: 'inventory',
          title: 'Stock movement recorded',
          message: `${m.quantity} units of "${m.product?.name}" (${m.type})`,
          date: m.movement_date,
        })
      })

      // Supplier purchases
      this.supplierOverviews.forEach((s) => {
        const pur = s.overview.purchases[0]
        if (pur) {
          feed.push({
            type: 'supplier',
            title: 'Supplier purchase recorded',
            message: `${pur.quantity} units purchased from “${s.supplier.name}”.`,
            date: pur.movement_date,
          })
        }
      })

      // sort by date desc, keep last 5 events
      return feed
        .sort((a, b) => new Date(b.date) - new Date(a.date))
        .slice(0, 5)
    },

    // === HELPERS ===
    timeAgo(date) {
      return dayjs(date).fromNow()
    },

    percentChange(current, previous) {
      if (previous === 0) return 0
      return (((current - previous) / previous) * 100).toFixed(0)
    },

    isLate(checkInTime) {
      return dayjs(checkInTime).isAfter(dayjs().hour(9).minute(0))
    },

    formatMAD(value) {
      return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD',
      }).format(value)
    },
  },
})
