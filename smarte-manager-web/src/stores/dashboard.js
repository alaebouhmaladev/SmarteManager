// src/stores/dashboard.js
import { defineStore } from 'pinia'
import http from '@/api/http'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'
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

    // detailed list of today's attendance
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
      const auth = useAuthStore()
      const role = auth.user?.role || null

      this.loading = true
      this.error = null

      try {
        // =============================================================
        // 1) USER
        // =============================================================
        const meRes = await http.get('/auth/me')
        this.me = meRes.data

        // =============================================================
        // 2) OVERVIEW (common for all roles)
        // =============================================================
        const overviewRes = await http.get('/dashboard/overview')
        this.overview = overviewRes.data

        // -------------------------------------------------------------
        // ROLE GROUPS
        // -------------------------------------------------------------
        const canHR = ['admin', 'manager', 'hr'].includes(role)
        const canInventory = ['admin', 'manager', 'stock_manager'].includes(role)

        // =============================================================
        // 3) TODAY ATTENDANCE + EMPLOYEES (HR ROLES ONLY)
        // =============================================================
        if (canHR) {
          const [allAttRes, empRes] = await Promise.all([
            http.get('/attendances'),
            http.get('/employees'),
          ])

          const allAttendance = allAttRes.data || []
          const todayStr = dayjs().format('YYYY-MM-DD')

          this.todayAttendance = allAttendance.filter(
            (a) => a.work_date === todayStr,
          )

          this.employees = empRes.data || []
          this.latestEmployees = this.employees.slice(0, 3)
        } else {
          this.todayAttendance = []
          this.employees = []
          this.latestEmployees = []
        }

        // =============================================================
        // 4) EXPENSES + SUMMARY (INVENTORY ROLES ONLY)
        // =============================================================
        if (canInventory) {
          const [expRes, expSum] = await Promise.all([
            http.get('/expenses'),
            http.get('/expenses/monthly-summary'),
          ])

          this.expenses = expRes.data || []
          this.invoicesCount = this.expenses.length

          this.expensesSummary = expSum.data || null
          this.expenseCategories = this.expensesSummary?.by_category || []
        } else {
          this.expenses = []
          this.expensesSummary = null
          this.expenseCategories = []
          this.invoicesCount = 0
        }

        // =============================================================
        // 5) PAYROLL (HR ROLES ONLY)
        // =============================================================
        if (canHR) {
          const payRes = await http.get('/payroll/monthly')
          this.payroll = payRes.data || null
        } else {
          this.payroll = null
        }

        // =============================================================
        // 6) INVENTORY VALUATION + LOW STOCK (INVENTORY ROLES ONLY)
        // =============================================================
        if (canInventory) {
          const [valRes, lowRes] = await Promise.all([
            http.get('/inventory/valuation'),
            http.get('/inventory/low-stock'),
          ])

          this.inventoryValuation = valRes.data?.total_value || 0
          this.lowStockCount = (lowRes.data || []).length
        } else {
          this.inventoryValuation = 0
          this.lowStockCount = 0
        }

        // =============================================================
        // 7) STOCK MOVEMENTS (INVENTORY ROLES ONLY)
        // =============================================================
        if (canInventory) {
          const movRes = await http.get('/stock-movements')
          this.stockMovements = movRes.data || []
          this.latestStockMovements = this.stockMovements.slice(0, 3)
        } else {
          this.stockMovements = []
          this.latestStockMovements = []
        }

        // =============================================================
        // 8) SUPPLIERS + OVERVIEWS (INVENTORY ROLES ONLY)
        // =============================================================
        if (canInventory) {
          const supRes = await http.get('/suppliers')
          this.suppliers = supRes.data || []

          const supplierOverviews = []
          for (const s of this.suppliers) {
            try {
              const ov = await http.get(`/suppliers/${s.id}/overview`)
              supplierOverviews.push({
                supplier: s,
                overview: ov.data,
              })
            } catch {
              // ignore single supplier errors
            }
          }
          this.supplierOverviews = supplierOverviews
        } else {
          this.suppliers = []
          this.supplierOverviews = []
        }

        // =============================================================
        // 9) ACTIVITY FEED (MERGED)
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
          // prefer created_at (full datetime), fallback to hire_date
          date: e.created_at ?? e.hire_date,
        })
      })

      // Expenses
      this.expenses.slice(0, 3).forEach((ex) => {
        feed.push({
          type: 'expense',
          title: 'Expense registered',
          message: `${ex.category} expense of ${ex.amount} MAD recorded.`,
          // prefer created_at (full datetime), fallback to expense_date
          date: ex.created_at ?? ex.expense_date,
        })
      })

      // Stock Movements
      this.latestStockMovements.forEach((m) => {
        feed.push({
          type: 'inventory',
          title: 'Stock movement recorded',
          message: `${m.quantity} units of "${m.product?.name}" (${m.type})`,
          // prefer created_at (full datetime), fallback to movement_date
          date: m.created_at ?? m.movement_date,
        })
      })

      // Supplier purchases from overview
      this.supplierOverviews.forEach((s) => {
        const pur = s.overview?.purchases?.[0]
        if (pur) {
          feed.push({
            type: 'supplier',
            title: 'Supplier purchase recorded',
            message: `${pur.quantity} units purchased from “${s.supplier.name}”.`,
            // prefer created_at, fallback to movement_date
            date: pur.created_at ?? pur.movement_date,
          })
        }
      })

      // sort by date desc, keep last 5 events
      return feed
        .filter((item) => !!item.date)
        .sort((a, b) => new Date(b.date) - new Date(a.date))
        .slice(0, 5)
    },

    // === HELPERS ===
    timeAgo(date) {
      if (!date) return '-'
      const d = dayjs(date)
      if (!d.isValid()) return '-'
      return d.fromNow()
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
