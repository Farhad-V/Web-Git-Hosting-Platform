import { apiClient } from '../lib/api'
import type {
  AuthResponse,
  CurrentUserResponse,
  LoginCredentials,
  MessageResponse,
  RegisterCredentials,
} from '../types/auth'

export async function login(
  credentials: LoginCredentials,
): Promise<AuthResponse> {
  const response =
    await apiClient.post<AuthResponse>(
      '/auth/login',
      credentials,
    )

  return response.data
}

export async function register(
  credentials: RegisterCredentials,
): Promise<AuthResponse> {
  const response =
    await apiClient.post<AuthResponse>(
      '/auth/register',
      credentials,
    )

  return response.data
}

export async function getCurrentUser(): Promise<CurrentUserResponse> {
  const response =
    await apiClient.get<CurrentUserResponse>(
      '/auth/me',
    )

  return response.data
}

export async function logout(): Promise<MessageResponse> {
  const response =
    await apiClient.post<MessageResponse>(
      '/auth/logout',
    )

  return response.data
}