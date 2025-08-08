<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="px-4 py-6 sm:px-0 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Encuestas</h1>
          <p class="mt-2 text-gray-600">Gestiona las encuestas de tu equipo</p>
        </div>
        <router-link 
          v-if="authStore.isAdmin || authStore.isTeamLead"
          to="/surveys/create" 
          class="btn btn-primary"
        >
          Crear Encuesta
        </router-link>
      </div>

      <!-- Surveys List -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
          <p class="mt-4 text-gray-500">Cargando encuestas...</p>
        </div>
        
        <div v-else-if="surveys.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No hay encuestas</h3>
          <p class="mt-1 text-sm text-gray-500">Comienza creando tu primera encuesta.</p>
        </div>
        
        <ul v-else class="divide-y divide-gray-200">
          <li v-for="survey in surveys" :key="survey.id" class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-3">
                  <h3 class="text-lg font-medium text-gray-900 truncate">
                    {{ survey.title }}
                  </h3>
                  <span :class="getStatusClass(survey.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ getStatusText(survey.status) }}
                  </span>
                </div>
                <p v-if="survey.description" class="mt-1 text-sm text-gray-500 truncate">
                  {{ survey.description }}
                </p>
                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                  <span>Creada: {{ formatDate(survey.created_at) }}</span>
                  <span v-if="survey.team">Equipo: {{ survey.team.name }}</span>
                  <span v-if="survey.access_code">Código: {{ survey.access_code }}</span>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <router-link 
                  :to="`/surveys/${survey.id}`" 
                  class="btn btn-secondary"
                >
                  Ver
                </router-link>
                <button 
                  v-if="survey.status === 'draft' && (authStore.isAdmin || survey.created_by === authStore.user?.id)"
                  @click="activateSurvey(survey.id)"
                  class="btn btn-primary"
                >
                  Activar
                </button>
                <button 
                  v-if="survey.status === 'active' && (authStore.isAdmin || survey.created_by === authStore.user?.id)"
                  @click="pauseSurvey(survey.id)"
                  class="btn btn-secondary"
                >
                  Pausar
                </button>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { surveyAPI } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const surveys = ref([])
const loading = ref(true)

const fetchSurveys = async () => {
  try {
    loading.value = true
    const response = await surveyAPI.index()
    surveys.value = response.data.surveys
  } catch (error) {
    console.error('Error fetching surveys:', error)
  } finally {
    loading.value = false
  }
}

const activateSurvey = async (surveyId) => {
  try {
    await surveyAPI.activate(surveyId)
    await fetchSurveys()
  } catch (error) {
    console.error('Error activating survey:', error)
  }
}

const pauseSurvey = async (surveyId) => {
  try {
    await surveyAPI.pause(surveyId)
    await fetchSurveys()
  } catch (error) {
    console.error('Error pausing survey:', error)
  }
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    active: 'bg-green-100 text-green-800',
    paused: 'bg-yellow-100 text-yellow-800',
    closed: 'bg-red-100 text-red-800'
  }
  return classes[status] || classes.draft
}

const getStatusText = (status) => {
  const texts = {
    draft: 'Borrador',
    active: 'Activa',
    paused: 'Pausada',
    closed: 'Cerrada'
  }
  return texts[status] || 'Borrador'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

onMounted(() => {
  fetchSurveys()
})
</script> 