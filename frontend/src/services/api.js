import axios from 'axios';

// Create axios instance
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8002/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    console.error('API Error:', error);
    return Promise.reject(error);
  }
);

// Auth API - Removed for public access

// Organization API
export const organizationAPI = {
  index: () => api.get('/organizations'),
  show: (id) => api.get(`/organizations/${id}`),
  store: (data) => api.post('/organizations', data),
  update: (id, data) => api.put(`/organizations/${id}`, data),
  destroy: (id) => api.delete(`/organizations/${id}`),
};

// Team API
export const teamAPI = {
  index: () => api.get('/teams'),
  show: (id) => api.get(`/teams/${id}`),
  store: (data) => api.post('/teams', data),
  update: (id, data) => api.put(`/teams/${id}`, data),
  destroy: (id) => api.delete(`/teams/${id}`),
  addMember: (teamId, data) => api.post(`/teams/${teamId}/members`, data),
  removeMember: (teamId, userId) => api.delete(`/teams/${teamId}/members/${userId}`),
};

// Survey API
export const surveyAPI = {
  index: () => api.get('/surveys'),
  show: (id) => api.get(`/surveys/${id}`),
  store: (data) => api.post('/surveys', data),
  update: (id, data) => api.put(`/surveys/${id}`, data),
  destroy: (id) => api.delete(`/surveys/${id}`),
  addQuestion: (surveyId, data) => api.post(`/surveys/${surveyId}/questions`, data),
  updateQuestion: (surveyId, questionId, data) => api.put(`/surveys/${surveyId}/questions/${questionId}`, data),
  deleteQuestion: (surveyId, questionId) => api.delete(`/surveys/${surveyId}/questions/${questionId}`),
  activate: (id) => api.post(`/surveys/${id}/activate`),
  pause: (id) => api.post(`/surveys/${id}/pause`),
  close: (id) => api.post(`/surveys/${id}/close`),
  statistics: (id) => api.get(`/surveys/${id}/statistics`),
  publicShow: (accessCode) => api.get(`/public/surveys/${accessCode}`),
};

// Survey Response API
export const surveyResponseAPI = {
  index: () => api.get('/survey-responses'),
  show: (id) => api.get(`/survey-responses/${id}`),
  store: (data) => api.post('/survey-responses', data),
  bySurvey: (surveyId) => api.get(`/survey-responses/by-survey/${surveyId}`),
  publicStore: (accessCode, data) => api.post(`/public/surveys/${accessCode}/responses`, data),
};

// Dashboard API
export const dashboardAPI = {
  index: () => api.get('/dashboard'),
  organizationStats: () => api.get('/dashboard/organization-stats'),
  teamStats: (teamId) => api.get(`/dashboard/team-stats/${teamId}`),
  surveyStats: (surveyId) => api.get(`/dashboard/survey-stats/${surveyId}`),
  recentActivity: () => api.get('/dashboard/recent-activity'),
};

export default api; 