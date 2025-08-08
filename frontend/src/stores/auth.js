import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  // Datos simulados para evitar errores sin sistema de auth
  const user = ref({
    id: 1,
    name: 'Usuario Demo',
    email: 'demo@teamPulse.com',
    role: 'admin'
  })
  const isAuthenticated = ref(true)
  const loading = ref(false)
  const error = ref(null)

  // Getters simulados
  const isAdmin = ref(true)
  const isTeamLead = ref(true)

  // Funciones vacías para compatibilidad
  const login = async (credentials) => {
    // Sin autenticación real
    return Promise.resolve()
  }

  const register = async (userData) => {
    // Sin registro real
    return Promise.resolve()
  }

  const logout = async () => {
    // Sin logout real
    return Promise.resolve()
  }

  return {
    user,
    isAuthenticated,
    loading,
    error,
    isAdmin,
    isTeamLead,
    login,
    register,
    logout
  }
})
