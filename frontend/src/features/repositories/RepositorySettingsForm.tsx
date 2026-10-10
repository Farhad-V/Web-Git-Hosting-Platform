import { useEffect } from 'react'
import { zodResolver } from '@hookform/resolvers/zod'
import {
  useMutation,
  useQueryClient,
} from '@tanstack/react-query'
import {
  Globe2,
  LoaderCircle,
  LockKeyhole,
  Save,
} from 'lucide-react'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import {
  getApiErrorMessage,
  getValidationErrors,
} from '../../lib/api-errors'
import { updateRepository } from '../../services/repository-service'
import type {
  Repository,
  UpdateRepositoryInput,
} from '../../types/repository'
import { repositoryQueryKeys } from './repository-query-keys'

const repositorySettingsSchema =
  z.object({
    name: z.string().trim().toLowerCase().min(1).max(100).regex(/^[a-z0-9](?:[a-z0-9._-]{0,98}[a-z0-9])?$/).refine(value => !value.includes('..'), 'Consecutive dots are not allowed.'),
    visibility: z.enum([
      'private',
      'public',
    ]),
  })

type RepositorySettingsValues =
  z.infer<
    typeof repositorySettingsSchema
  >

interface RepositorySettingsFormProps {
  repository: Repository
  onUpdated: (
    repository: Repository,
  ) => void
}

export function RepositorySettingsForm({
  repository,
  onUpdated,
}: RepositorySettingsFormProps) {
  const queryClient =
    useQueryClient()

  const {
    formState: {
      errors,
      isDirty,
    },
    handleSubmit,
    register,
    reset,
    setError,
  } = useForm<RepositorySettingsValues>({
    resolver:
      zodResolver(
        repositorySettingsSchema,
      ),

    defaultValues: {
      name: repository.name,
      visibility:
        repository.visibility,
    },
  })

  const updateMutation =
    useMutation({
      mutationFn:
        (
          input: UpdateRepositoryInput,
        ) =>
          updateRepository(
            repository.id,
            input,
          ),
    })

  useEffect(() => {
    reset({
      name: repository.name,
      visibility:
        repository.visibility,
    })
  }, [
    repository.name,
    repository.visibility,
    reset,
  ])

  async function submit(
    values: RepositorySettingsValues,
  ): Promise<void> {
    updateMutation.reset()

    try {
      const response =
        await updateMutation.mutateAsync({
          ...(values.name !== repository.name ? { name: values.name } : {}),
          visibility:
            values.visibility,
        })

      queryClient.setQueryData(
        repositoryQueryKeys.detail(
          repository.id,
        ),
        {
          data:
            response.repository,
        },
      )

      await queryClient.invalidateQueries({
        queryKey:
          repositoryQueryKeys.lists(),
      })

      reset({
        name: response.repository.name,
        visibility:
          response.repository.visibility,
      })

      onUpdated(
        response.repository,
      )
    } catch (error) {
      const validationErrors =
        getValidationErrors(
          error,
        )

      if (validationErrors.name?.[0]) setError('name', { type: 'server', message: validationErrors.name[0] })

      const visibilityError =
        validationErrors.visibility?.[0]

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
    updateMutation.isError
      ? getValidationErrors(
          updateMutation.error,
        )
      : {}

  const hasGeneralError =
    updateMutation.isError
    && Object.keys(
      validationErrors,
    ).length === 0

  return (
    <section className="rounded-xl border border-[var(--border)] glass-panel">
      <header className="border-b border-[var(--border)] px-6 py-5">
        <h2 className="font-semibold text-[var(--text)]">
          Repository settings
        </h2>

        <p className="mt-1 text-sm text-[var(--muted)]">
          Rename your repository and manage who can read it.
        </p>
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
        {updateMutation.isSuccess && (
          <div
            className="rounded-lg border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-[var(--emerald)]"
            role="status"
          >
            Repository settings updated successfully.
          </div>
        )}

        {hasGeneralError && (
          <div
            className="rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-[var(--red)]"
            role="alert"
          >
            {getApiErrorMessage(
              updateMutation.error,
              'Unable to update repository settings.',
            )}
          </div>
        )}

        <div>
          <label className="mb-2 block text-sm font-medium" htmlFor="repository-name">Repository name</label>
          <input id="repository-name" {...register('name')} disabled={updateMutation.isPending} aria-invalid={Boolean(errors.name)} aria-describedby="repository-name-help repository-name-error" className="w-full rounded-lg border border-[var(--border)] bg-[var(--inset)] px-3 py-2.5 text-sm" />
          <p id="repository-name-help" className="mt-2 text-xs leading-6 text-[var(--muted)]">Names use lowercase letters, numbers, dots, hyphens, and underscores. Renaming preserves your files, commits, and tags. Existing clones must update their remote URL to the new name.</p>
          <p id="repository-name-error" className="mt-1 text-sm text-[var(--red)]">{errors.name?.message}</p>
        </div>
        <fieldset
          disabled={
            updateMutation.isPending
          }
        >
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
                    Only you may read or manage this repository.
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

          {errors.visibility
            ?.message !==
          undefined && (
            <p className="mt-2 text-sm text-[var(--red)]">
              {
                errors.visibility
                  .message
              }
            </p>
          )}
        </fieldset>

        <div className="flex justify-end border-t border-[var(--border)] pt-5">
          <button
            className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
            disabled={
              !isDirty
              || updateMutation.isPending
            }
            type="submit"
          >
            {updateMutation.isPending ? (
              <LoaderCircle
                aria-hidden="true"
                className="size-4 animate-spin"
              />
            ) : (
              <Save
                aria-hidden="true"
                className="size-4"
              />
            )}

            {updateMutation.isPending
              ? 'Saving...'
              : 'Save settings'}
          </button>
        </div>
      </form>
    </section>
  )
}