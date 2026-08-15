import { useEffect, useState } from 'react'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import {
  Check,
  Copy,
  Eye,
  EyeOff,
  KeyRound,
  LoaderCircle,
  LockKeyhole,
  ShieldAlert,
  X,
} from 'lucide-react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import {
  getApiErrorMessage,
  getValidationErrors,
} from '../../lib/api-errors'
import { createPersonalAccessToken } from '../../services/personal-access-token-service'
import type {
  CreatePersonalAccessTokenInput,
  CreatePersonalAccessTokenResponse,
  PersonalAccessToken,
} from '../../types/personal-access-token'
import { personalAccessTokenQueryKeys } from './personal-access-token-query-keys'

const createTokenSchema = z.object({
  name: z
    .string()
    .trim()
    .min(1, 'Token name is required.')
    .max(100, 'Token name cannot exceed 100 characters.'),
  currentPassword: z
    .string()
    .min(1, 'Your current password is required.'),
  permission: z.enum(['read', 'write']),
  expirationDays: z.enum(['7', '30', '90', '365']),
})

type CreateTokenFormValues = z.infer<
  typeof createTokenSchema
>

interface CreatePersonalAccessTokenDialogProps {
  isOpen: boolean
  onClose: () => void
  onCreated: (token: PersonalAccessToken) => void
}

const inputClassName =
  'h-11 w-full rounded-lg border border-white/10 bg-[#0d1117] px-3 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20'

export function CreatePersonalAccessTokenDialog({
  isOpen,
  onClose,
  onCreated,
}: CreatePersonalAccessTokenDialogProps) {
  const queryClient = useQueryClient()

  const [createdToken, setCreatedToken] =
    useState<CreatePersonalAccessTokenResponse | null>(null)
  const [showPassword, setShowPassword] = useState(false)
  const [copyStatus, setCopyStatus] = useState<
    'idle' | 'copied' | 'failed'
  >('idle')

  const {
    clearErrors,
    formState: { errors },
    handleSubmit,
    register,
    reset,
    setError,
  } = useForm<CreateTokenFormValues>({
    resolver: zodResolver(createTokenSchema),
    defaultValues: {
      name: '',
      currentPassword: '',
      permission: 'write',
      expirationDays: '30',
    },
  })

  const createMutation = useMutation({
    mutationFn: (input: CreatePersonalAccessTokenInput) =>
      createPersonalAccessToken(input),
  })

  useEffect(() => {
    if (!isOpen) {
      return
    }

    function closeOnEscape(event: KeyboardEvent): void {
      if (
        event.key === 'Escape'
        && !createMutation.isPending
      ) {
        closeDialog()
      }
    }

    window.addEventListener('keydown', closeOnEscape)

    return () => {
      window.removeEventListener('keydown', closeOnEscape)
    }
  })

  if (!isOpen) {
    return null
  }

  function resetDialogState(): void {
    createMutation.reset()
    clearErrors()
    reset()
    setCreatedToken(null)
    setShowPassword(false)
    setCopyStatus('idle')
  }

  function closeDialog(): void {
    if (createMutation.isPending) {
      return
    }

    resetDialogState()
    onClose()
  }

  async function submit(
    values: CreateTokenFormValues,
  ): Promise<void> {
    createMutation.reset()
    setCopyStatus('idle')

    const expirationDays = Number(values.expirationDays)

    const expiresAt = new Date(
      Date.now() + expirationDays * 24 * 60 * 60 * 1000,
    ).toISOString()

    const input: CreatePersonalAccessTokenInput = {
      name: values.name.trim(),
      current_password: values.currentPassword,
      abilities:
        values.permission === 'write'
          ? ['repository:read', 'repository:write']
          : ['repository:read'],
      expires_at: expiresAt,
    }

    try {
      const response = await createMutation.mutateAsync(input)

      await queryClient.invalidateQueries({
        queryKey: personalAccessTokenQueryKeys.all,
      })

      setCreatedToken(response)
      reset()
      onCreated(response.token)
    } catch (error) {
      const validationErrors = getValidationErrors(error)

      const nameError = validationErrors.name?.[0]
      const passwordError =
        validationErrors.current_password?.[0]
      const abilityError =
        validationErrors.abilities?.[0]
        ?? validationErrors['abilities.0']?.[0]
      const expirationError =
        validationErrors.expires_at?.[0]

      if (nameError !== undefined) {
        setError('name', {
          type: 'server',
          message: nameError,
        })
      }

      if (passwordError !== undefined) {
        setError('currentPassword', {
          type: 'server',
          message: passwordError,
        })
      }

      if (abilityError !== undefined) {
        setError('permission', {
          type: 'server',
          message: abilityError,
        })
      }

      if (expirationError !== undefined) {
        setError('expirationDays', {
          type: 'server',
          message: expirationError,
        })
      }
    }
  }

  async function copyToken(): Promise<void> {
    if (createdToken === null) {
      return
    }

    try {
      await navigator.clipboard.writeText(
        createdToken.plain_text_token,
      )
      setCopyStatus('copied')
    } catch {
      setCopyStatus('failed')
    }
  }

  const validationErrors = createMutation.isError
    ? getValidationErrors(createMutation.error)
    : {}

  const hasGeneralError =
    createMutation.isError
    && Object.keys(validationErrors).length === 0

  return (
    <div
      aria-labelledby="create-token-title"
      aria-modal="true"
      className="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-black/70 px-4 py-8 backdrop-blur-sm"
      role="dialog"
    >
      <button
        aria-label="Close create token dialog"
        className="absolute inset-0 cursor-default"
        disabled={createMutation.isPending}
        onClick={closeDialog}
        type="button"
      />

      <section className="relative w-full max-w-xl rounded-2xl border border-white/10 bg-[#161b22] shadow-2xl shadow-black/50">
        <header className="flex items-start justify-between border-b border-white/10 px-6 py-5">
          <div>
            <h2
              className="text-xl font-semibold text-white"
              id="create-token-title"
            >
              {createdToken === null
                ? 'Create repository access token'
                : 'Token created'}
            </h2>

            <p className="mt-1 text-sm text-slate-400">
              {createdToken === null
                ? 'Use this token with Git over HTTP.'
                : 'Copy the token before closing this dialog.'}
            </p>
          </div>

          <button
            aria-label="Close"
            className="rounded-lg p-2 text-slate-500 transition hover:bg-white/5 hover:text-white disabled:opacity-50"
            disabled={createMutation.isPending}
            onClick={closeDialog}
            type="button"
          >
            <X aria-hidden="true" className="size-5" />
          </button>
        </header>

        {createdToken !== null ? (
          <div className="space-y-5 p-6">
            <div
              className="flex gap-3 rounded-xl border border-amber-400/20 bg-amber-500/10 p-4 text-amber-200"
              role="alert"
            >
              <ShieldAlert
                aria-hidden="true"
                className="mt-0.5 size-5 shrink-0"
              />

              <div>
                <p className="text-sm font-semibold">
                  This token is shown only once
                </p>

                <p className="mt-1 text-sm leading-6 text-amber-200/80">
                  Store it securely. GitForge cannot display it again
                  after this dialog is closed.
                </p>
              </div>
            </div>

            <div>
              <label
                className="mb-2 block text-sm font-medium text-slate-200"
                htmlFor="created-token-value"
              >
                Personal access token
              </label>

              <div className="flex flex-col gap-2 sm:flex-row">
                <input
                  className="h-11 min-w-0 flex-1 rounded-lg border border-white/10 bg-[#0d1117] px-3 font-mono text-sm text-emerald-300 outline-none"
                  id="created-token-value"
                  onFocus={(event) => event.currentTarget.select()}
                  readOnly
                  value={createdToken.plain_text_token}
                />

                <button
                  className="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-white/10 px-4 text-sm font-semibold text-slate-200 transition hover:bg-white/5 hover:text-white"
                  onClick={copyToken}
                  type="button"
                >
                  {copyStatus === 'copied' ? (
                    <Check
                      aria-hidden="true"
                      className="size-4 text-emerald-400"
                    />
                  ) : (
                    <Copy aria-hidden="true" className="size-4" />
                  )}

                  {copyStatus === 'copied' ? 'Copied' : 'Copy'}
                </button>
              </div>

              {copyStatus === 'failed' && (
                <p className="mt-2 text-sm text-amber-300">
                  Automatic copying failed. Select and copy the token
                  manually.
                </p>
              )}
            </div>

            <div className="rounded-xl border border-white/10 bg-[#0d1117] p-4">
              <p className="text-sm font-semibold text-white">
                {createdToken.token.name}
              </p>

              <p className="mt-1 text-xs text-slate-500">
                Permissions:{' '}
                {createdToken.token.abilities.includes(
                  'repository:write',
                )
                  ? 'Repository read and write'
                  : 'Repository read only'}
              </p>
            </div>

            <div className="flex justify-end border-t border-white/10 pt-5">
              <button
                className="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-500"
                onClick={closeDialog}
                type="button"
              >
                I have saved the token
              </button>
            </div>
          </div>
        ) : (
          <form
            className="space-y-6 p-6"
            noValidate
            onSubmit={handleSubmit(submit)}
          >
            {hasGeneralError && (
              <div
                className="rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300"
                role="alert"
              >
                {getApiErrorMessage(
                  createMutation.error,
                  'Unable to create the token. Try again.',
                )}
              </div>
            )}

            <div>
              <label
                className="mb-2 block text-sm font-medium text-slate-200"
                htmlFor="token-name"
              >
                Token name
              </label>

              <input
                aria-describedby={
                  errors.name
                    ? 'token-name-error'
                    : 'token-name-help'
                }
                aria-invalid={errors.name ? 'true' : 'false'}
                autoComplete="off"
                autoFocus
                className={inputClassName}
                id="token-name"
                placeholder="Development laptop"
                type="text"
                {...register('name')}
              />

              {errors.name?.message !== undefined ? (
                <p
                  className="mt-2 text-sm text-red-400"
                  id="token-name-error"
                >
                  {errors.name.message}
                </p>
              ) : (
                <p
                  className="mt-2 text-xs text-slate-500"
                  id="token-name-help"
                >
                  Choose a name that identifies the device or purpose.
                </p>
              )}
            </div>

            <fieldset>
              <legend className="mb-3 text-sm font-medium text-slate-200">
                Repository permissions
              </legend>

              <div className="grid gap-3 sm:grid-cols-2">
                <label className="cursor-pointer">
                  <input
                    className="peer sr-only"
                    type="radio"
                    value="read"
                    {...register('permission')}
                  />

                  <span className="flex h-full gap-3 rounded-xl border border-white/10 bg-[#0d1117] p-4 transition peer-checked:border-blue-500 peer-checked:bg-blue-500/10 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500">
                    <LockKeyhole
                      aria-hidden="true"
                      className="mt-0.5 size-5 shrink-0 text-blue-300"
                    />

                    <span>
                      <span className="block text-sm font-semibold text-white">
                        Read only
                      </span>

                      <span className="mt-1 block text-xs leading-5 text-slate-500">
                        Clone and fetch private repositories.
                      </span>
                    </span>
                  </span>
                </label>

                <label className="cursor-pointer">
                  <input
                    className="peer sr-only"
                    type="radio"
                    value="write"
                    {...register('permission')}
                  />

                  <span className="flex h-full gap-3 rounded-xl border border-white/10 bg-[#0d1117] p-4 transition peer-checked:border-blue-500 peer-checked:bg-blue-500/10 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500">
                    <KeyRound
                      aria-hidden="true"
                      className="mt-0.5 size-5 shrink-0 text-emerald-300"
                    />

                    <span>
                      <span className="block text-sm font-semibold text-white">
                        Read and write
                      </span>

                      <span className="mt-1 block text-xs leading-5 text-slate-500">
                        Clone, fetch, and push repositories.
                      </span>
                    </span>
                  </span>
                </label>
              </div>

              {errors.permission?.message !== undefined && (
                <p className="mt-2 text-sm text-red-400">
                  {errors.permission.message}
                </p>
              )}
            </fieldset>

            <div>
              <label
                className="mb-2 block text-sm font-medium text-slate-200"
                htmlFor="token-expiration"
              >
                Expiration
              </label>

              <select
                aria-invalid={
                  errors.expirationDays ? 'true' : 'false'
                }
                className={inputClassName}
                id="token-expiration"
                {...register('expirationDays')}
              >
                <option value="7">7 days</option>
                <option value="30">30 days</option>
                <option value="90">90 days</option>
                <option value="365">1 year</option>
              </select>

              {errors.expirationDays?.message !== undefined && (
                <p className="mt-2 text-sm text-red-400">
                  {errors.expirationDays.message}
                </p>
              )}
            </div>

            <div>
              <label
                className="mb-2 block text-sm font-medium text-slate-200"
                htmlFor="token-current-password"
              >
                Current password
              </label>

              <div className="relative">
                <input
                  aria-describedby={
                    errors.currentPassword
                      ? 'token-current-password-error'
                      : 'token-current-password-help'
                  }
                  aria-invalid={
                    errors.currentPassword ? 'true' : 'false'
                  }
                  autoComplete="current-password"
                  className={`${inputClassName} pr-11`}
                  id="token-current-password"
                  placeholder="Confirm your password"
                  type={showPassword ? 'text' : 'password'}
                  {...register('currentPassword')}
                />

                <button
                  aria-label={
                    showPassword ? 'Hide password' : 'Show password'
                  }
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 transition hover:text-white"
                  onClick={() => setShowPassword((value) => !value)}
                  type="button"
                >
                  {showPassword ? (
                    <EyeOff
                      aria-hidden="true"
                      className="size-4"
                    />
                  ) : (
                    <Eye
                      aria-hidden="true"
                      className="size-4"
                    />
                  )}
                </button>
              </div>

              {errors.currentPassword?.message !== undefined ? (
                <p
                  className="mt-2 text-sm text-red-400"
                  id="token-current-password-error"
                >
                  {errors.currentPassword.message}
                </p>
              ) : (
                <p
                  className="mt-2 text-xs text-slate-500"
                  id="token-current-password-help"
                >
                  Password confirmation protects sensitive token
                  creation.
                </p>
              )}
            </div>

            <div className="flex flex-col-reverse gap-3 border-t border-white/10 pt-5 sm:flex-row sm:justify-end">
              <button
                className="inline-flex h-10 items-center justify-center rounded-lg border border-white/10 px-4 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white disabled:opacity-50"
                disabled={createMutation.isPending}
                onClick={closeDialog}
                type="button"
              >
                Cancel
              </button>

              <button
                className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                disabled={createMutation.isPending}
                type="submit"
              >
                {createMutation.isPending ? (
                  <LoaderCircle
                    aria-hidden="true"
                    className="size-4 animate-spin"
                  />
                ) : (
                  <KeyRound
                    aria-hidden="true"
                    className="size-4"
                  />
                )}

                {createMutation.isPending
                  ? 'Creating token...'
                  : 'Create token'}
              </button>
            </div>
          </form>
        )}
      </section>
    </div>
  )
}