import { useState } from 'react'
import { zodResolver } from '@hookform/resolvers/zod'
import { Eye, EyeOff, LoaderCircle, LockKeyhole, Mail } from 'lucide-react'
import { useForm } from 'react-hook-form'
import { Link, useNavigate } from 'react-router-dom'
import { z } from 'zod'
import {
  getApiErrorMessage,
  getValidationErrors,
} from '../../lib/api-errors'
import { AuthLayout } from './AuthLayout'
import { useAuth } from './auth-context'

const loginSchema = z.object({
  email: z
    .string()
    .trim()
    .min(1, 'Email address is required.')
    .email('Enter a valid email address.'),
  password: z.string().min(1, 'Password is required.'),
})

type LoginFormValues = z.infer<typeof loginSchema>

export function LoginPage() {
  const navigate = useNavigate()
  const { login } = useAuth()
  const [showPassword, setShowPassword] = useState(false)
  const [submissionError, setSubmissionError] = useState<string | null>(null)

  const {
    formState: { errors, isSubmitting },
    handleSubmit,
    register,
    setError,
  } = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: '',
      password: '',
    },
  })

  async function submit(values: LoginFormValues): Promise<void> {
    setSubmissionError(null)

    try {
      await login({
        ...values,
        device_name: 'web',
      })

      navigate('/dashboard', { replace: true })
    } catch (error) {
      const validationErrors = getValidationErrors(error)

      if (validationErrors.email?.[0] !== undefined) {
        setError('email', {
          type: 'server',
          message: validationErrors.email[0],
        })
      }

      if (validationErrors.password?.[0] !== undefined) {
        setError('password', {
          type: 'server',
          message: validationErrors.password[0],
        })
      }

      if (Object.keys(validationErrors).length === 0) {
        setSubmissionError(
          getApiErrorMessage(
            error,
            'Unable to sign in. Check your connection and try again.',
          ),
        )
      }
    }
  }

  return (
    <AuthLayout
      description="Use your account credentials to access repositories and settings."
      footer={
        <>
          New to GitForge?{' '}
          <Link
            className="font-semibold text-blue-400 transition hover:text-blue-300"
            to="/register"
          >
            Create an account
          </Link>
        </>
      }
      title="Welcome back"
    >
      <form
        className="space-y-5"
        noValidate
        onSubmit={handleSubmit(submit)}
      >
        {submissionError !== null && (
          <div
            className="rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300"
            role="alert"
          >
            {submissionError}
          </div>
        )}

        <div>
          <label
            className="mb-2 block text-sm font-medium text-slate-200"
            htmlFor="email"
          >
            Email address
          </label>
          <div className="relative">
            <Mail
              aria-hidden="true"
              className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"
            />
            <input
              aria-describedby={errors.email ? 'email-error' : undefined}
              aria-invalid={errors.email ? 'true' : 'false'}
              autoComplete="email"
              className="h-11 w-full rounded-lg border border-white/10 bg-[#0d1117] pl-10 pr-3 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
              id="email"
              placeholder="you@example.com"
              type="email"
              {...register('email')}
            />
          </div>
          {errors.email?.message !== undefined && (
            <p className="mt-2 text-sm text-red-400" id="email-error">
              {errors.email.message}
            </p>
          )}
        </div>

        <div>
          <div className="mb-2 flex items-center justify-between">
            <label
              className="block text-sm font-medium text-slate-200"
              htmlFor="password"
            >
              Password
            </label>
          </div>
          <div className="relative">
            <LockKeyhole
              aria-hidden="true"
              className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"
            />
            <input
              aria-describedby={errors.password ? 'password-error' : undefined}
              aria-invalid={errors.password ? 'true' : 'false'}
              autoComplete="current-password"
              className="h-11 w-full rounded-lg border border-white/10 bg-[#0d1117] pl-10 pr-11 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
              id="password"
              placeholder="Enter your password"
              type={showPassword ? 'text' : 'password'}
              {...register('password')}
            />
            <button
              aria-label={showPassword ? 'Hide password' : 'Show password'}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 transition hover:text-slate-300"
              onClick={() => setShowPassword((visible) => !visible)}
              type="button"
            >
              {showPassword ? (
                <EyeOff aria-hidden="true" className="size-4" />
              ) : (
                <Eye aria-hidden="true" className="size-4" />
              )}
            </button>
          </div>
          {errors.password?.message !== undefined && (
            <p className="mt-2 text-sm text-red-400" id="password-error">
              {errors.password.message}
            </p>
          )}
        </div>

        <button
          className="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-lg shadow-blue-950/30 transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
          disabled={isSubmitting}
          type="submit"
        >
          {isSubmitting && (
            <LoaderCircle
              aria-hidden="true"
              className="size-4 animate-spin"
            />
          )}
          {isSubmitting ? 'Signing in...' : 'Sign in'}
        </button>
      </form>
    </AuthLayout>
  )
}