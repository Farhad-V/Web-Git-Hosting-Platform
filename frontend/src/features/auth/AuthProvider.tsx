import type { ReactNode } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import axios from 'axios'
import {
  getCurrentUser,
  login as loginRequest,
  logout as logoutRequest,
  register as registerRequest,
} from '../../services/auth-service'
import type {
  LoginCredentials,
  RegisterCredentials,
  User,
} from '../../types/auth'
import {
  AuthContext,
  type AuthContextValue,
} from './auth-context'

const currentUserQueryKey =
  ['auth', 'user'] as const

interface AuthProviderProps {
  children: ReactNode
}

export function AuthProvider({
  children,
}: AuthProviderProps) {
  const queryClient = useQueryClient()

  const currentUserQuery = useQuery({
    queryKey: currentUserQueryKey,

    queryFn: async (): Promise<User | null> => {
      try {
        const response =
          await getCurrentUser()

        return response.user
      } catch (error) {
        if (
          axios.isAxiosError(error)
          && error.response?.status === 401
        ) {
          return null
        }

        throw error
      }
    },

    retry: false,
  })

  async function login(
    credentials: LoginCredentials,
  ): Promise<void> {
    const response =
      await loginRequest(credentials)

    queryClient.setQueryData(
      currentUserQueryKey,
      response.user,
    )
  }

  async function register(
    credentials: RegisterCredentials,
  ): Promise<void> {
    const response =
      await registerRequest(credentials)

    queryClient.setQueryData(
      currentUserQueryKey,
      response.user,
    )
  }

  async function logout(): Promise<void> {
    try {
      await logoutRequest()
    } finally {
      queryClient.setQueryData(
        currentUserQueryKey,
        null,
      )

      queryClient.removeQueries({
        predicate: (query) =>
          query.queryKey[0] !== 'auth',
      })
    }
  }

  const user =
    currentUserQuery.data
    ?? null

  const value: AuthContextValue = {
    user,

    isAuthenticated:
      user !== null,

    isLoading:
      currentUserQuery.isPending,

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