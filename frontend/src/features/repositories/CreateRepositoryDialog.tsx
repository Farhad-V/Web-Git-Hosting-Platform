import { useEffect } from 'react'
import { zodResolver } from '@hookform/resolvers/zod'
import {
  useMutation,
  useQueryClient,
} from '@tanstack/react-query'
import {
  BookOpen,
  Globe2,
  LoaderCircle,
  LockKeyhole,
  X,
} from 'lucide-react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import {
  getApiErrorMessage,
  getValidationErrors,
} from '../../lib/api-errors'
import { createRepository } from '../../services/repository-service'
import type {
  CreateRepositoryInput,
  Repository,
} from '../../types/repository'
import { repositoryQueryKeys } from './repository-query-keys'

const repositoryNamePattern =
  /^[a-z0-9](?:[a-z0-9._-]{0,98}[a-z0-9])?$/

const createRepositorySchema = z.object({
  name: z
    .string()
    .trim()
    .toLowerCase()
    .min(
      1,
      'Repository name is required.',
    )
    .max(
      100,
      'Repository name cannot exceed 100 characters.',
    )
    .regex(
      repositoryNamePattern,
      'Use lowercase letters, numbers, dots, underscores, and hyphens.',
    )
    .refine(
      (name) => !name.includes('..'),
      'Repository names cannot contain consecutive dots.',
    ),

  visibility: z.enum([
    'private',
    'public',
  ]),
})

type CreateRepositoryFormValues =
  z.infer<typeof createRepositorySchema>

interface CreateRepositoryDialogProps {
  isOpen: boolean
  onClose: () => void
  onCreated: (
    repository: Repository,
  ) => void
}

const inputClassName =
  'h-11 w-full rounded-lg border border-[var(--border)] bg-[var(--inset)] px-3 text-sm text-white outline-none transition placeholder:text-[var(--muted)] focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20'

export function CreateRepositoryDialog({
  isOpen,
  onClose,
  onCreated,
}: CreateRepositoryDialogProps) {
  const queryClient =
    useQueryClient()

  const {
    clearErrors,
    formState: {
      errors,
    },
    handleSubmit,
    register,
    reset,
    setError,
  } = useForm<CreateRepositoryFormValues>({
    resolver:
      zodResolver(
        createRepositorySchema,
      ),

    defaultValues: {
      name: '',
      visibility: 'private',
    },
  })

  const createMutation =
    useMutation({
      mutationFn:
        (input: CreateRepositoryInput) =>
          createRepository(input),
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

  function closeDialog(): void {
    if (
      createMutation.isPending
    ) {
      return
    }

    createMutation.reset()
    clearErrors()
    reset()
    onClose()
  }

  async function submit(
    values: CreateRepositoryFormValues,
  ): Promise<void> {
    createMutation.reset()

    try {
      const response =
        await createMutation.mutateAsync({
          name:
            values.name
              .trim()
              .toLowerCase(),

          visibility:
            values.visibility,
        })

      await queryClient.invalidateQueries({
        queryKey:
          repositoryQueryKeys.lists(),
      })

      reset()

      onCreated(
        response.repository,
      )
    } catch (error) {
      const validationErrors =
        getValidationErrors(
          error,
        )

      const nameError =
        validationErrors.name?.[0]

      const visibilityError =
        validationErrors.visibility?.[0]

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

      if (
        visibilityError !== undefined
      ) {
        setError(
          'visibility',
          {
            type: 'server',
            message:
              visibilityError,
          },
        )
      }
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
      aria-labelledby="create-repository-title"
      aria-modal="true"
      className="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-black/70 px-4 py-8 backdrop-blur-sm"
      role="dialog"
    >
      <button
        aria-label="Close create repository dialog"
        className="absolute inset-0 cursor-default"
        disabled={
          createMutation.isPending
        }
        onClick={
          closeDialog
        }
        type="button"
      />

      <section className="relative w-full max-w-xl rounded-2xl border border-[var(--border)] glass-panel shadow-2xl shadow-black/50">
        <header className="flex items-start justify-between border-b border-[var(--border)] px-6 py-5">
          <div>
            <h2
              className="text-xl font-semibold text-[var(--text)]"
              id="create-repository-title"
            >
              Create a new repository
            </h2>

            <p className="mt-1 text-sm text-[var(--muted)]">
              GitForge creates the repository metadata and managed bare
              Git storage together.
            </p>
          </div>

          <button
            aria-label="Close"
            className="rounded-lg p-2 text-[var(--muted)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)] disabled:opacity-50"
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
              className="rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-[var(--red)]"
              role="alert"
            >
              {getApiErrorMessage(
                createMutation.error,
                'Unable to create the repository. Try again.',
              )}
            </div>
          )}

          <div>
            <label
              className="mb-2 block text-sm font-medium text-[var(--text)]"
              htmlFor="repository-name"
            >
              Repository name
            </label>

            <input
              aria-describedby={
                errors.name
                  ? 'repository-name-error'
                  : 'repository-name-help'
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
              id="repository-name"
              placeholder="my-project"
              spellCheck="false"
              type="text"
              {...register(
                'name',
                {
                  setValueAs:
                    (
                      value: string,
                    ) =>
                      value
                        .trim()
                        .toLowerCase(),
                },
              )}
            />

            {errors.name
              ?.message !==
            undefined ? (
              <p
                className="mt-2 text-sm text-[var(--red)]"
                id="repository-name-error"
              >
                {
                  errors.name
                    .message
                }
              </p>
            ) : (
              <p
                className="mt-2 text-xs text-[var(--muted)]"
                id="repository-name-help"
              >
                Names are normalized to lowercase and must be unique
                inside your username namespace.
              </p>
            )}
          </div>

          <fieldset>
            <legend className="mb-3 text-sm font-medium text-[var(--text)]">
              Visibility
            </legend>

            <div className="grid gap-3 sm:grid-cols-2">
              <label className="cursor-pointer">
                <input
                  className="peer sr-only"
                  type="radio"
                  value="private"
                  {...register(
                    'visibility',
                  )}
                />

                <span className="flex h-full gap-3 rounded-xl border border-[var(--border)] bg-[var(--inset)] p-4 transition peer-checked:border-blue-500 peer-checked:bg-blue-500/10 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500">
                  <LockKeyhole
                    aria-hidden="true"
                    className="mt-0.5 size-5 shrink-0 text-[var(--accent)]"
                  />

                  <span>
                    <span className="block text-sm font-semibold text-[var(--text)]">
                      Private
                    </span>

                    <span className="mt-1 block text-xs leading-5 text-[var(--muted)]">
                      Only the repository owner may read or manage it.
                    </span>
                  </span>
                </span>
              </label>

              <label className="cursor-pointer">
                <input
                  className="peer sr-only"
                  type="radio"
                  value="public"
                  {...register(
                    'visibility',
                  )}
                />

                <span className="flex h-full gap-3 rounded-xl border border-[var(--border)] bg-[var(--inset)] p-4 transition peer-checked:border-blue-500 peer-checked:bg-blue-500/10 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500">
                  <Globe2
                    aria-hidden="true"
                    className="mt-0.5 size-5 shrink-0 text-[var(--emerald)]"
                  />

                  <span>
                    <span className="block text-sm font-semibold text-[var(--text)]">
                      Public
                    </span>

                    <span className="mt-1 block text-xs leading-5 text-[var(--muted)]">
                      Anyone may view and clone the repository.
                    </span>
                  </span>
                </span>
              </label>
            </div>
          </fieldset>

          <div className="flex flex-col-reverse gap-3 border-t border-[var(--border)] pt-5 sm:flex-row sm:justify-end">
            <button
              className="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--border)] px-4 text-sm font-semibold text-[var(--text)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)]"
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
              className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:opacity-60"
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
                <BookOpen
                  aria-hidden="true"
                  className="size-4"
                />
              )}

              {createMutation.isPending
                ? 'Creating repository...'
                : 'Create repository'}
            </button>
          </div>
        </form>
      </section>
    </div>
  )
}