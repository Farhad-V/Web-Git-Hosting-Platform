import axios from 'axios'
import type { ValidationErrorResponse } from '../types/auth'

export function getApiErrorMessage(
  error: unknown,
  fallbackMessage: string,
): string {
  if (!axios.isAxiosError<ValidationErrorResponse>(error)) {
    return fallbackMessage
  }

  const responseData = error.response?.data

  if (typeof responseData?.message === 'string') {
    return responseData.message
  }

  return fallbackMessage
}

export function getValidationErrors(
  error: unknown,
): Record<string, string[]> {
  if (!axios.isAxiosError<ValidationErrorResponse>(error)) {
    return {}
  }

  return error.response?.data.errors ?? {}
}