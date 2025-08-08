import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/Dashboard.vue')
  },
  {
    path: '/surveys',
    name: 'Surveys',
    component: () => import('@/views/Surveys.vue')
  },
  {
    path: '/surveys/create',
    name: 'CreateSurvey',
    component: () => import('@/views/CreateSurvey.vue')
  },
  {
    path: '/surveys/:id',
    name: 'SurveyDetail',
    component: () => import('@/views/SurveyDetail.vue')
  },
  {
    path: '/public/:accessCode',
    name: 'PublicSurvey',
    component: () => import('@/views/PublicSurvey.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router