export interface User {
  id: number
  name: string
  username: string
  email: string
  email_verified_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface AuthResponse {
  message: string
  user: User
  access_token: string
  token_type: 'Bearer'
}

export interface CurrentUserResponse {
  user: User
}

export interface LoginCredentials {
  email: string
  password: string
  device_name?: string
}

export interface RegisterCredentials {
  name: string
  username: string
  email: string
  password: string
  password_confirmation: string
  device_name?: string
}

export interface MessageResponse {
  message: string
}

export interface ValidationErrorResponse {
  message: string
  errors: Record<string, string[]>
}