import { useState } from 'react'
import { zodResolver } from '@hookform/resolvers/zod'
import {
  AtSign,
  Eye,
  EyeOff,
  LoaderCircle,
  LockKeyhole,
  Mail,
  UserRound,
} from 'lucide-react'
import { useForm } from 'react-hook-form'
import { Link, useNavigate } from 'react-router-dom'
import { z } from 'zod'
import {
  getApiErrorMessage,
  getValidationErrors,
} from '../../lib/api-errors'
import { AuthLayout } from './AuthLayout'
import { useAuth } from './auth-context'

const reservedUsernames = [
  'admin',
  'api',
  'assets',
  'auth',
  'login',
  'register',
  'repositories',
  'settings',
]

const registerSchema = z
  .object({
    name: z
      .string()
      .trim()
      .min(2, 'Name must contain at least 2 characters.')
      .max(100, 'Name cannot exceed 100 characters.'),
    username: z
      .string()
      .trim()
      .min(3, 'Username must contain at least 3 characters.')
      .max(39, 'Username cannot exceed 39 characters.')
      .regex(
        /^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/,
        'Use lowercase letters, numbers, and single hyphens between characters.',
      )
      .refine(
        (username) => !reservedUsernames.includes(username),
        'This username is reserved.',
      ),
    email: z
      .string()
      .trim()
      .min(1, 'Email address is required.')
      .email('Enter a valid email address.'),
    password: z
      .string()
      .min(12, 'Password must contain at least 12 characters.')
      .regex(/[a-z]/, 'Password must contain a lowercase letter.')
      .regex(/[A-Z]/, 'Password must contain an uppercase letter.')
      .regex(/[0-9]/, 'Password must contain a number.')
      .regex(
        /[^A-Za-z0-9]/,
        'Password must contain a symbol.',
      ),
    password_confirmation: z.string().min(
      1,
      'Confirm your password.',
    ),
  })
  .refine(
    (values) => values.password === values.password_confirmation,
    {
      message: 'Password confirmation does not match.',
      path: ['password_confirmation'],
    },
  )

type RegisterFormValues = z.infer<typeof registerSchema>

const inputClassName =
  'h-11 w-full rounded-lg border border-white/10 bg-[#0d1117] pl-10 pr-3 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20'

export function RegisterPage() {
  const navigate = useNavigate()
  const { register: createAccount } = useAuth()
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirmation, setShowConfirmation] = useState(false)
  const [submissionError, setSubmissionError] = useState<string | null>(null)

  const {
    formState: { errors, isSubmitting },
    handleSubmit,
    register,
    setError,
  } = useForm<RegisterFormValues>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: '',
      username: '',
      email: '',
      password: '',
      password_confirmation: '',
    },
  })

  async function submit(values: RegisterFormValues): Promise<void> {
    setSubmissionError(null)

    try {
      await createAccount({
        ...values,
        device_name: 'web',
      })

      navigate('/dashboard', { replace: true })
    } catch (error) {
      const validationErrors = getValidationErrors(error)
      const fields: Array<keyof RegisterFormValues> = [
        'name',
        'username',
        'email',
        'password',
        'password_confirmation',
      ]

      fields.forEach((field) => {
        const message = validationErrors[field]?.[0]

        if (message !== undefined) {
          setError(field, {
            type: 'server',
            message,
          })
        }
      })

      if (Object.keys(validationErrors).length === 0) {
        setSubmissionError(
          getApiErrorMessage(
            error,
            'Unable to create your account. Check your connection and try again.',
          ),
        )
      }
    }
  }

  return (
    <AuthLayout
      description="Create your identity and start hosting repositories on your server."
      footer={
        <>
          Already have an account?{' '}
          <Link
            className="font-semibold text-blue-400 transition hover:text-blue-300"
            to="/login"
          >
            Sign in
          </Link>
        </>
      }
      title="Create your account"
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
            htmlFor="name"
          >
            Full name
          </label>
          <div className="relative">
            <UserRound
              aria-hidden="true"
              className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"
            />
            <input
              aria-describedby={errors.name ? 'name-error' : undefined}
              aria-invalid={errors.name ? 'true' : 'false'}
              autoComplete="name"
              className={inputClassName}
              id="name"
              placeholder="Farhad Valizadegan"
              type="text"
              {...register('name')}
            />
          </div>
          {errors.name?.message !== undefined && (
            <p className="mt-2 text-sm text-red-400" id="name-error">
              {errors.name.message}
            </p>
          )}
        </div>

        <div>
          <label
            className="mb-2 block text-sm font-medium text-slate-200"
            htmlFor="username"
          >
            Username
          </label>
          <div className="relative">
            <AtSign
              aria-hidden="true"
              className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"
            />
            <input
              aria-describedby={
                errors.username ? 'username-error' : 'username-help'
              }
              aria-invalid={errors.username ? 'true' : 'false'}
              autoCapitalize="none"
              autoComplete="username"
              className={inputClassName}
              id="username"
              placeholder="farhad"
              spellCheck="false"
              type="text"
              {...register('username', {
                setValueAs: (value: string) => value.trim().toLowerCase(),
              })}
            />
          </div>
          {errors.username?.message !== undefined ? (
            <p className="mt-2 text-sm text-red-400" id="username-error">
              {errors.username.message}
            </p>
          ) : (
            <p className="mt-2 text-xs text-slate-500" id="username-help">
              This becomes your repository namespace.
            </p>
          )}
        </div>

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
              className={inputClassName}
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
          <label
            className="mb-2 block text-sm font-medium text-slate-200"
            htmlFor="password"
          >
            Password
          </label>
          <div className="relative">
            <LockKeyhole
              aria-hidden="true"
              className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"
            />
            <input
              aria-describedby={
                errors.password ? 'password-error' : 'password-help'
              }
              aria-invalid={errors.password ? 'true' : 'false'}
              autoComplete="new-password"
              className={`${inputClassName} pr-11`}
              id="password"
              placeholder="Create a strong password"
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
          {errors.password?.message !== undefined ? (
            <p className="mt-2 text-sm text-red-400" id="password-error">
              {errors.password.message}
            </p>
          ) : (
            <p className="mt-2 text-xs leading-5 text-slate-500" id="password-help">
              At least 12 characters with uppercase, lowercase, number, and symbol.
            </p>
          )}
        </div>

        <div>
          <label
            className="mb-2 block text-sm font-medium text-slate-200"
            htmlFor="password-confirmation"
          >
            Confirm password
          </label>
          <div className="relative">
            <LockKeyhole
              aria-hidden="true"
              className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"
            />
            <input
              aria-describedby={
                errors.password_confirmation
                  ? 'password-confirmation-error'
                  : undefined
              }
              aria-invalid={
                errors.password_confirmation ? 'true' : 'false'
              }
              autoComplete="new-password"
              className={`${inputClassName} pr-11`}
              id="password-confirmation"
              placeholder="Repeat your password"
              type={showConfirmation ? 'text' : 'password'}
              {...register('password_confirmation')}
            />
            <button
              aria-label={
                showConfirmation
                  ? 'Hide password confirmation'
                  : 'Show password confirmation'
              }
              className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 transition hover:text-slate-300"
              onClick={() => setShowConfirmation((visible) => !visible)}
              type="button"
            >
              {showConfirmation ? (
                <EyeOff aria-hidden="true" className="size-4" />
              ) : (
                <Eye aria-hidden="true" className="size-4" />
              )}
            </button>
          </div>
          {errors.password_confirmation?.message !== undefined && (
            <p
              className="mt-2 text-sm text-red-400"
              id="password-confirmation-error"
            >
              {errors.password_confirmation.message}
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
          {isSubmitting ? 'Creating account...' : 'Create account'}
        </button>
      </form>
    </AuthLayout>
  )
}