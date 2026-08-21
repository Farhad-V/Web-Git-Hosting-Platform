import axios from 'axios'

interface CsrfTokenResponse {
  csrf_token: string
}

const apiBaseUrl =
  import.meta.env.VITE_API_BASE_URL
  ?? '/api/v1'

const csrfClient = axios.create({
  baseURL: apiBaseUrl,
  headers: {
    Accept: 'application/json',
  },
  timeout: 15_000,
  withCredentials: true,
})

export const apiClient = axios.create({
  baseURL: apiBaseUrl,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 15_000,
  withCredentials: true,
})

apiClient.interceptors.request.use(
  async (config) => {
    const method =
      config.method?.toLowerCase()
      ?? 'get'

    const requiresCsrf = [
      'post',
      'put',
      'patch',
      'delete',
    ].includes(method)

    if (!requiresCsrf) {
      return config
    }

    const response =
      await csrfClient.get<CsrfTokenResponse>(
        '/auth/csrf-token',
      )

    config.headers.set(
      'X-CSRF-TOKEN',
      response.data.csrf_token,
    )

    return config
  },
)