import axios from 'axios'
import { api } from './http'

const LEGACY_TOKEN_KEY = 'amar-assist-token'
const LEGACY_USER_KEY = 'amar-assist-user'

export interface AuthUser {
  id: number
  name: string
  email: string
}

interface SessionResponse {
  data: {
    user: AuthUser
  }
}

async function getCsrfCookie() {
  const apiUrl = new URL(import.meta.env.VITE_API_URL || '/api', window.location.origin)
  const csrfUrl = new URL('/sanctum/csrf-cookie', apiUrl)

  await axios.get(csrfUrl.toString(), {
    headers: { Accept: 'application/json' },
    withCredentials: true,
  })
}

export function revokeLegacyBrowserToken() {
  const token = localStorage.getItem(LEGACY_TOKEN_KEY)
  localStorage.removeItem(LEGACY_TOKEN_KEY)
  localStorage.removeItem(LEGACY_USER_KEY)

  if (!token) return

  const apiUrl = new URL(import.meta.env.VITE_API_URL || '/api', window.location.origin)
  const apiPath = `${apiUrl.pathname.replace(/\/$/, '')}/logout`
  const logoutUrl = new URL(apiPath, apiUrl.origin)

  void getCsrfCookie()
    .then(() =>
      axios.post(logoutUrl.toString(), {}, {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        withCredentials: true,
        withXSRFToken: true,
      }),
    )
    .catch((error: unknown) => {
      const status = axios.isAxiosError(error) ? error.response?.status : undefined
      console.warn('Legacy browser token revocation failed.', status ? `HTTP ${status}` : 'Network error')
    })
}

export async function login(email: string, password: string) {
  await getCsrfCookie()
  const { data } = await api.post<SessionResponse>('/session/login', { email, password })

  return data.data.user
}

export async function register(name: string, email: string, password: string, passwordConfirmation: string) {
  await getCsrfCookie()
  const { data } = await api.post<SessionResponse>('/session/register', {
    name,
    email,
    password,
    password_confirmation: passwordConfirmation,
  })

  return data.data.user
}

export async function logout() {
  await getCsrfCookie()
  await api.post('/session/logout')
}

export async function getCurrentUser() {
  const { data } = await api.get<SessionResponse>('/session/user')

  return data.data.user
}
