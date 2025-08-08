import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useDashboardStore = defineStore('dashboard', () => {
  const stats = ref({
    total_surveys: 0,
    active_surveys: 0,
    total_responses: 0,
    recent_activity: []
  })
  const loading = ref(false)
  const error = ref(null)

  const fetchDashboardStats = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get('/dashboard')
      stats.value = response.data.stats
    } catch (err) {
      error.value = err.response?.data?.message || 'Error fetching dashboard stats'
      console.error('Error fetching dashboard stats:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchRecentActivity = async () => {
    try {
      const response = await axios.get('/dashboard/recent-activity')
      stats.value.recent_activity = response.data.activity
    } catch (err) {
      console.error('Error fetching recent activity:', err)
    }
  }

  return {
    stats,
    loading,
    error,
    fetchDashboardStats,
    fetchRecentActivity
  }
})
