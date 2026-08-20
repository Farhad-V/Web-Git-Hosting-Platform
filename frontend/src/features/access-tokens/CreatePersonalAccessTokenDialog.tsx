import {
  useEffect,
  useState,
} from 'react'
import { zodResolver } from '@hookform/resolvers/zod'
import {
  useMutation,
  useQueryClient,
} from '@tanstack/react-query'
import {
  Check,
  Copy,
  KeyRound,
  LoaderCircle,
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
  CreatePersonalAccessTokenResponse,
  PersonalAccessToken,
} from '../../types/personal-access-token'
import { personalAccessTokenQueryKeys } from './personal-access-token-query-keys'

const createTokenSchema = z.object({
  name: z
    .string()
    .trim()
    .min(
      1,
      'Token name is required.',
    )
    .max(
      100,
      'Token name cannot exceed 100 characters.',
    ),
})

type CreateTokenFormValues =
  z.infer<typeof createTokenSchema>

interface CreatePersonalAccessTokenDialogProps {
  isOpen: boolean
  onClose: () => void
  onCreated: (
    token: PersonalAccessToken,
  ) => void
}

const inputClassName =
  'h-11 w-full rounded-lg border border-white/10 bg-[#0d1117] px-3 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20'

export function CreatePersonalAccessTokenDialog({
  isOpen,
  onClose,
  onCreated,
}: CreatePersonalAccessTokenDialogProps) {
  const queryClient =
    useQueryClient()

  const [
    createdToken,
    setCreatedToken,
  ] = useState<CreatePersonalAccessTokenResponse | null>(
    null,
  )

  const [
    copyStatus,
    setCopyStatus,
  ] = useState<
    'idle' | 'copied' | 'failed'
  >('idle')

  const {
    clearErrors,
    formState: {
      errors,
    },
    handleSubmit,
    register,
    reset,
    setError,
  } = useForm<CreateTokenFormValues>({
    resolver:
      zodResolver(
        createTokenSchema,
      ),

    defaultValues: {
      name: '',
    },
  })

  const createMutation =
    useMutation({
      mutationFn:
        createPersonalAccessToken,
    })

  useEffect(() => {
    if (!isOpen) {
      return
    }

    function closeOnEscape(
      event: KeyboardEvent,
    ): void {
      if (
        event.key === 'Escape'
        && !createMutation.isPending
      ) {
        closeDialog()
      }
    }

    window.addEventListener(
      'keydown',
      closeOnEscape,
    )

    return () => {
      window.removeEventListener(
        'keydown',
        closeOnEscape,
      )
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
    setCopyStatus('idle')
  }

  function closeDialog(): void {
    if (
      createMutation.isPending
    ) {
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

    try {
      const response =
        await createMutation.mutateAsync({
          name:
            values.name.trim(),
        })

      await queryClient.invalidateQueries({
        queryKey:
          personalAccessTokenQueryKeys.all,
      })

      setCreatedToken(
        response,
      )

      reset()

      onCreated(
        response.token,
      )
    } catch (error) {
      const validationErrors =
        getValidationErrors(
          error,
        )

      const nameError =
        validationErrors.name?.[0]

      if (
        nameError !== undefined
      ) {
        setError(
          'name',
          {
            type: 'server',
            message: nameError,
          },
        )
      }
    }
  }

  async function copyToken(): Promise<void> {
    if (
      createdToken === null
    ) {
      return
    }

    try {
      await navigator.clipboard.writeText(
        createdToken.plain_text_token,
      )

      setCopyStatus(
        'copied',
      )
    } catch {
      setCopyStatus(
        'failed',
      )
    }
  }

  const validationErrors =
    createMutation.isError
      ? getValidationErrors(
          createMutation.error,
        )
      : {}

  const hasGeneralError =
    createMutation.isError
    && Object.keys(
      validationErrors,
    ).length === 0

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
        disabled={
          createMutation.isPending
        }
        onClick={
          closeDialog
        }
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
                ? 'Create personal access token'
                : 'Token created'}
            </h2>

            <p className="mt-1 text-sm text-slate-400">
              {createdToken === null
                ? 'Use this token to authenticate Git operations over HTTP.'
                : 'Copy this token before closing the dialog.'}
            </p>
          </div>

          <button
            aria-label="Close"
            className="rounded-lg p-2 text-slate-500 transition hover:bg-white/5 hover:text-white disabled:opacity-50"
            disabled={
              createMutation.isPending
            }
            onClick={
              closeDialog
            }
            type="button"
          >
            <X
              aria-hidden="true"
              className="size-5"
            />
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
                  Store it securely. GitForge stores only a hash and
                  cannot display the plaintext token again.
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
                  onFocus={(
                    event,
                  ) =>
                    event.currentTarget.select()
                  }
                  readOnly
                  value={
                    createdToken.plain_text_token
                  }
                />

                <button
                  className="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-white/10 px-4 text-sm font-semibold text-slate-200 transition hover:bg-white/5 hover:text-white"
                  onClick={
                    copyToken
                  }
                  type="button"
                >
                  {copyStatus ===
                  'copied' ? (
                    <Check
                      aria-hidden="true"
                      className="size-4 text-emerald-400"
                    />
                  ) : (
                    <Copy
                      aria-hidden="true"
                      className="size-4"
                    />
                  )}

                  {copyStatus ===
                  'copied'
                    ? 'Copied'
                    : 'Copy'}
                </button>
              </div>

              {copyStatus ===
                'failed' && (
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
                Git operations are authorized separately by repository
                ownership and visibility rules.
              </p>
            </div>

            <div className="flex justify-end border-t border-white/10 pt-5">
              <button
                className="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-500"
                onClick={
                  closeDialog
                }
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
            onSubmit={
              handleSubmit(
                submit,
              )
            }
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
                aria-invalid={
                  errors.name
                    ? 'true'
                    : 'false'
                }
                autoComplete="off"
                autoFocus
                className={
                  inputClassName
                }
                id="token-name"
                placeholder="Development laptop"
                type="text"
                {...register(
                  'name',
                )}
              />

              {errors.name
                ?.message !==
              undefined ? (
                <p
                  className="mt-2 text-sm text-red-400"
                  id="token-name-error"
                >
                  {
                    errors.name
                      .message
                  }
                </p>
              ) : (
                <p
                  className="mt-2 text-xs text-slate-500"
                  id="token-name-help"
                >
                  Choose a descriptive name for the device or Git
                  client using this credential.
                </p>
              )}
            </div>

            <div className="rounded-xl border border-blue-400/20 bg-blue-500/10 p-4">
              <div className="flex gap-3">
                <KeyRound
                  aria-hidden="true"
                  className="mt-0.5 size-5 shrink-0 text-blue-300"
                />

                <p className="text-sm leading-6 text-slate-300">
                  V1 personal access tokens do not have scopes or
                  expiration dates. Repository authorization is
                  enforced by GitForge for every Git operation.
                </p>
              </div>
            </div>

            <div className="flex flex-col-reverse gap-3 border-t border-white/10 pt-5 sm:flex-row sm:justify-end">
              <button
                className="inline-flex h-10 items-center justify-center rounded-lg border border-white/10 px-4 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white disabled:opacity-50"
                disabled={
                  createMutation.isPending
                }
                onClick={
                  closeDialog
                }
                type="button"
              >
                Cancel
              </button>

              <button
                className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                disabled={
                  createMutation.isPending
                }
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