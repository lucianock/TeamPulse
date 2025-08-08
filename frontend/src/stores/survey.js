import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

// Configure axios
axios.defaults.baseURL = API_URL
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

export const useSurveyStore = defineStore('survey', () => {
  const surveys = ref([])
  const currentSurvey = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // Actions
  const fetchSurveys = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get('/surveys')
      surveys.value = response.data.surveys
    } catch (err) {
      error.value = err.response?.data?.message || 'Error fetching surveys'
      console.error('Error fetching surveys:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchSurvey = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get(`/surveys/${id}`)
      currentSurvey.value = response.data.survey
      return response.data.survey
    } catch (err) {
      error.value = err.response?.data?.message || 'Error fetching survey'
      console.error('Error fetching survey:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const createSurvey = async (surveyData) => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.post('/surveys', surveyData)
      surveys.value.unshift(response.data.survey)
      return response.data.survey
    } catch (err) {
      error.value = err.response?.data?.message || 'Error creating survey'
      console.error('Error creating survey:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateSurvey = async (id, surveyData) => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.put(`/surveys/${id}`, surveyData)
      const index = surveys.value.findIndex(s => s.id === id)
      if (index !== -1) {
        surveys.value[index] = response.data.survey
      }
      currentSurvey.value = response.data.survey
      return response.data.survey
    } catch (err) {
      error.value = err.response?.data?.message || 'Error updating survey'
      console.error('Error updating survey:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteSurvey = async (id) => {
    loading.value = true
    error.value = null
    try {
      await axios.delete(`/surveys/${id}`)
      surveys.value = surveys.value.filter(s => s.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Error deleting survey'
      console.error('Error deleting survey:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const activateSurvey = async (id) => {
    try {
      const response = await axios.post(`/surveys/${id}/activate`)
      const index = surveys.value.findIndex(s => s.id === id)
      if (index !== -1) {
        surveys.value[index].status = 'active'
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error activating survey'
      throw err
    }
  }

  const pauseSurvey = async (id) => {
    try {
      const response = await axios.post(`/surveys/${id}/pause`)
      const index = surveys.value.findIndex(s => s.id === id)
      if (index !== -1) {
        surveys.value[index].status = 'paused'
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error pausing survey'
      throw err
    }
  }

  const closeSurvey = async (id) => {
    try {
      const response = await axios.post(`/surveys/${id}/close`)
      const index = surveys.value.findIndex(s => s.id === id)
      if (index !== -1) {
        surveys.value[index].status = 'closed'
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error closing survey'
      throw err
    }
  }

  const fetchPublicSurvey = async (accessCode) => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get(`/public/surveys/${accessCode}`)
      return response.data.survey
    } catch (err) {
      error.value = err.response?.data?.message || 'Survey not found'
      console.error('Error fetching public survey:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const submitPublicResponse = async (accessCode, responseData) => {
    try {
      const response = await axios.post(`/public/surveys/${accessCode}/responses`, responseData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error submitting response'
      console.error('Error submitting response:', err)
      throw err
    }
  }

  const getSurveyStatistics = async (id) => {
    try {
      const response = await axios.get(`/surveys/${id}/statistics`)
      return response.data.statistics
    } catch (err) {
      error.value = err.response?.data?.message || 'Error fetching statistics'
      console.error('Error fetching statistics:', err)
      throw err
    }
  }

  return {
    // State
    surveys,
    currentSurvey,
    loading,
    error,
    
    // Actions
    fetchSurveys,
    fetchSurvey,
    createSurvey,
    updateSurvey,
    deleteSurvey,
    activateSurvey,
    pauseSurvey,
    closeSurvey,
    fetchPublicSurvey,
    submitPublicResponse,
    getSurveyStatistics
  }
})
