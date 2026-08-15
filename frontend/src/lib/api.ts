import axios from 'axios'
import { getAccessToken } from './auth-storage'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? '/api/v1'

export const apiClient = axios.create({
  baseURL: apiBaseUrl,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 15_000,
})

apiClient.interceptors.request.use((config) => {
  const accessToken = getAccessToken()

  if (accessToken !== null) {
    config.headers.set('Authorization', `Bearer ${accessToken}`)
  }

  return config
})