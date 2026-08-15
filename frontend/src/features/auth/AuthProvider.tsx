import { useEffect, type ReactNode } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import {
  getCurrentUser,
  login as loginRequest,
  logout as logoutRequest,
  register as registerRequest,
} from '../../services/auth-service'
import {
  getAccessToken,
  removeAccessToken,
  setAccessToken,
} from '../../lib/auth-storage'
import type {
  LoginCredentials,
  RegisterCredentials,
  User,
} from '../../types/auth'
import { AuthContext, type AuthContextValue } from './auth-context'

const currentUserQueryKey = ['auth', 'user'] as const

interface AuthProviderProps {
  children: ReactNode
}

export function AuthProvider({ children }: AuthProviderProps) {
  const queryClient = useQueryClient()
  const hasAccessToken = getAccessToken() !== null

  const currentUserQuery = useQuery({
    queryKey: currentUserQueryKey,
    queryFn: async (): Promise<User> => {
      const response = await getCurrentUser()

      return response.user
    },
    enabled: hasAccessToken,
    retry: false,
  })

  useEffect(() => {
    if (currentUserQuery.isError) {
      removeAccessToken()
      queryClient.setQueryData(currentUserQueryKey, null)
    }
  }, [currentUserQuery.isError, queryClient])

  async function login(credentials: LoginCredentials): Promise<void> {
    const response = await loginRequest(credentials)

    setAccessToken(response.access_token)
    queryClient.setQueryData(currentUserQueryKey, response.user)
  }

  async function register(credentials: RegisterCredentials): Promise<void> {
    const response = await registerRequest(credentials)

    setAccessToken(response.access_token)
    queryClient.setQueryData(currentUserQueryKey, response.user)
  }

  async function logout(): Promise<void> {
    try {
      if (getAccessToken() !== null) {
        await logoutRequest()
      }
    } finally {
      removeAccessToken()
      queryClient.setQueryData(currentUserQueryKey, null)
      queryClient.removeQueries({
        predicate: (query) => query.queryKey[0] !== 'auth',
      })
    }
  }

  const value: AuthContextValue = {
    user: currentUserQuery.data ?? null,
    isAuthenticated: currentUserQuery.data !== undefined
      && currentUserQuery.data !== null,
    isLoading: hasAccessToken && currentUserQuery.isPending,
    login,
    register,
    logout,
  }

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  )
}