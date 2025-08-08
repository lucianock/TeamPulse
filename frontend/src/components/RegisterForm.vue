<template>
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <h2 class="register-title">
          Crear cuenta en TeamPulse
        </h2>
        <p class="register-subtitle">
          Plataforma de feedback anónimo para equipos
        </p>
      </div>
      
      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label for="name">Nombre completo</label>
          <input
            id="name"
            v-model="form.name"
            name="name"
            type="text"
            required
            class="form-input"
            placeholder="Tu nombre completo"
          />
        </div>
        
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            name="email"
            type="email"
            required
            class="form-input"
            placeholder="tu@email.com"
          />
        </div>
        
        <div class="form-group">
          <label for="organization_name">Nombre de la organización</label>
          <input
            id="organization_name"
            v-model="form.organization_name"
            name="organization_name"
            type="text"
            required
            class="form-input"
            placeholder="Nombre de tu empresa o organización"
          />
        </div>
        
        <div class="form-group">
          <label for="organization_description">Descripción de la organización (opcional)</label>
          <textarea
            id="organization_description"
            v-model="form.organization_description"
            name="organization_description"
            rows="3"
            class="form-textarea"
            placeholder="Breve descripción de tu organización"
          ></textarea>
        </div>
        
        <div class="form-group">
          <label for="job_title">Cargo (opcional)</label>
          <input
            id="job_title"
            v-model="form.job_title"
            name="job_title"
            type="text"
            class="form-input"
            placeholder="Tu cargo o posición"
          />
        </div>
        
        <div class="form-group">
          <label for="phone">Teléfono (opcional)</label>
          <input
            id="phone"
            v-model="form.phone"
            name="phone"
            type="tel"
            class="form-input"
            placeholder="Tu número de teléfono"
          />
        </div>
        
        <div class="form-group">
          <label for="password">Contraseña</label>
          <input
            id="password"
            v-model="form.password"
            name="password"
            type="password"
            required
            class="form-input"
            placeholder="Mínimo 8 caracteres"
          />
        </div>
        
        <div class="form-group">
          <label for="password_confirmation">Confirmar contraseña</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            name="password_confirmation"
            type="password"
            required
            class="form-input"
            placeholder="Repite tu contraseña"
          />
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="register-button"
        >
          <span v-if="loading" class="loading-spinner">
            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>
          {{ loading ? 'Creando cuenta...' : 'Crear cuenta' }}
        </button>

        <div class="login-link">
          <router-link to="/login">
            ¿Ya tienes cuenta? Inicia sesión
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  organization_name: '',
  organization_description: '',
  job_title: '',
  phone: ''
})

const error = ref('')
const loading = ref(false)

const handleRegister = async () => {
  try {
    loading.value = true
    error.value = ''
    
    // Validate password confirmation
    if (form.value.password !== form.value.password_confirmation) {
      error.value = 'Las contraseñas no coinciden'
      return
    }
    
    await authStore.register(form.value)
    router.push('/dashboard')
  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      const errorMessages = Object.values(errors).flat()
      error.value = errorMessages.join(', ')
    } else {
      error.value = err.response?.data?.message || 'Error al crear la cuenta'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f9fafb;
  padding: 2rem 1rem;
}

.register-card {
  max-width: 500px;
  width: 100%;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  padding: 2rem;
}

.register-header {
  text-align: center;
  margin-bottom: 2rem;
}

.register-title {
  font-size: 1.875rem;
  font-weight: 800;
  color: #111827;
  margin-bottom: 0.5rem;
}

.register-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.form-input,
.form-textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  transition: all 0.2s;
  box-sizing: border-box;
  font-family: inherit;
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input::placeholder,
.form-textarea::placeholder {
  color: #9ca3af;
}

.register-button {
  width: 100%;
  padding: 0.75rem 1rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
  position: relative;
}

.register-button:hover {
  background-color: #2563eb;
}

.register-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-message {
  color: #dc2626;
  font-size: 0.875rem;
  text-align: center;
  margin-bottom: 1rem;
}

.login-link {
  text-align: center;
  margin-top: 1rem;
}

.login-link a {
  color: #3b82f6;
  text-decoration: none;
  font-size: 0.875rem;
}

.login-link a:hover {
  color: #2563eb;
  text-decoration: underline;
}

.loading-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style> 