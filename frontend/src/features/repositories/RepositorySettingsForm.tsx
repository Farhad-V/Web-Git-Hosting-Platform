import { useEffect } from 'react'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQueryClient } from '@tanstack/react-query'
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

const repositoryNamePattern = /^[A-Za-z0-9][A-Za-z0-9._-]*$/

const repositorySettingsSchema = z.object({
  name: z
    .string()
    .trim()
    .min(1, 'Repository name is required.')
    .max(100, 'Repository name cannot exceed 100 characters.')
    .regex(
      repositoryNamePattern,
      'Start with a letter or number and use only letters, numbers, dots, underscores, and hyphens.',
    )
    .refine(
      (name) => name !== '.' && name !== '..',
      'This repository name is not allowed.',
    )
    .refine(
      (name) => !name.toLowerCase().endsWith('.git'),
      'Repository names cannot end with .git.',
    ),
  description: z
    .string()
    .max(2000, 'Description cannot exceed 2,000 characters.'),
  visibility: z.enum(['private', 'public']),
})

type RepositorySettingsValues = z.infer<
  typeof repositorySettingsSchema
>

interface RepositorySettingsFormProps {
  repository: Repository
  onUpdated: (repository: Repository) => void
}

const inputClassName =
  'h-11 w-full rounded-lg border border-white/10 bg-[#0d1117] px-3 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60'

export function RepositorySettingsForm({
  repository,
  onUpdated,
}: RepositorySettingsFormProps) {
  const queryClient = useQueryClient()

  const {
    formState: { errors, isDirty },
    handleSubmit,
    register,
    reset,
    setError,
  } = useForm<RepositorySettingsValues>({
    resolver: zodResolver(repositorySettingsSchema),
    defaultValues: {
      name: repository.name,
      description: repository.description ?? '',
      visibility: repository.visibility,
    },
  })

  const updateMutation = useMutation({
    mutationFn: (input: UpdateRepositoryInput) =>
      updateRepository(repository.id, input),
  })

  useEffect(() => {
    reset({
      name: repository.name,
      description: repository.description ?? '',
      visibility: repository.visibility,
    })
  }, [
    repository.description,
    repository.name,
    repository.visibility,
    reset,
  ])

  async function submit(
    values: RepositorySettingsValues,
  ): Promise<void> {
    updateMutation.reset()

    const descriptionValue = values.description.trim()

    const input: UpdateRepositoryInput = {
      name: values.name.trim(),
      description:
        descriptionValue === ''
          ? null
          : descriptionValue,
      visibility: values.visibility,
    }

    try {
      const response = await updateMutation.mutateAsync(input)

      queryClient.setQueryData(
        repositoryQueryKeys.detail(repository.id),
        {
          data: response.repository,
        },
      )

      await queryClient.invalidateQueries({
        queryKey: repositoryQueryKeys.lists(),
      })

      reset({
        name: response.repository.name,
        description: response.repository.description ?? '',
        visibility: response.repository.visibility,
      })

      onUpdated(response.repository)
    } catch (error) {
      const validationErrors = getValidationErrors(error)

      const nameError =
        validationErrors.name?.[0]
        ?? validationErrors.slug?.[0]

      const descriptionError =
        validationErrors.description?.[0]

      const visibilityError =
        validationErrors.visibility?.[0]

      if (nameError !== undefined) {
        setError('name', {
          type: 'server',
          message: nameError,
        })
      }

      if (descriptionError !== undefined) {
        setError('description', {
          type: 'server',
          message: descriptionError,
        })
      }

      if (visibilityError !== undefined) {
        setError('visibility', {
          type: 'server',
          message: visibilityError,
        })
      }
    }
  }

  const validationErrors = updateMutation.isError
    ? getValidationErrors(updateMutation.error)
    : {}

  const hasGeneralError =
    updateMutation.isError
    && Object.keys(validationErrors).length === 0

  return (
    <section className="rounded-xl border border-white/10 bg-[#161b22]">
      <header className="border-b border-white/10 px-6 py-5">
        <h2 className="font-semibold text-white">
          Repository settings
        </h2>

        <p className="mt-1 text-sm text-slate-500">
          Update the repository identity, description, and visibility.
        </p>
      </header>

      <form
        className="space-y-6 p-6"
        noValidate
        onSubmit={handleSubmit(submit)}
      >
        {updateMutation.isSuccess && (
          <div
            className="rounded-lg border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300"
            role="status"
          >
            Repository settings saved successfully.
          </div>
        )}

        {hasGeneralError && (
          <div
            className="rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300"
            role="alert"
          >
            {getApiErrorMessage(
              updateMutation.error,
              'Unable to update the repository.',
            )}
          </div>
        )}

        <div>
          <label
            className="mb-2 block text-sm font-medium text-slate-200"
            htmlFor="settings-repository-name"
          >
            Repository name
          </label>

          <input
            aria-describedby={
              errors.name
                ? 'settings-repository-name-error'
                : 'settings-repository-name-help'
            }
            aria-invalid={errors.name ? 'true' : 'false'}
            className={inputClassName}
            disabled={updateMutation.isPending}
            id="settings-repository-name"
            spellCheck="false"
            type="text"
            {...register('name')}
          />

          {errors.name?.message !== undefined ? (
            <p
              className="mt-2 text-sm text-red-400"
              id="settings-repository-name-error"
            >
              {errors.name.message}
            </p>
          ) : (
            <p
              className="mt-2 text-xs text-slate-500"
              id="settings-repository-name-help"
            >
              Renaming also updates the repository URL slug.
            </p>
          )}
        </div>

        <div>
          <label
            className="mb-2 block text-sm font-medium text-slate-200"
            htmlFor="settings-repository-description"
          >
            Description
            <span className="ml-1 text-slate-500">
              (optional)
            </span>
          </label>

          <textarea
            aria-describedby={
              errors.description
                ? 'settings-repository-description-error'
                : undefined
            }
            aria-invalid={errors.description ? 'true' : 'false'}
            className="min-h-28 w-full resize-y rounded-lg border border-white/10 bg-[#0d1117] px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60"
            disabled={updateMutation.isPending}
            id="settings-repository-description"
            placeholder="What is this repository for?"
            {...register('description')}
          />

          {errors.description?.message !== undefined && (
            <p
              className="mt-2 text-sm text-red-400"
              id="settings-repository-description-error"
            >
              {errors.description.message}
            </p>
          )}
        </div>

        <fieldset disabled={updateMutation.isPending}>
          <legend className="mb-3 text-sm font-medium text-slate-200">
            Visibility
          </legend>

          <div className="grid gap-3 sm:grid-cols-2">
            <label className="cursor-pointer">
              <input
                className="peer sr-only"
                type="radio"
                value="private"
                {...register('visibility')}
              />

              <span className="flex h-full gap-3 rounded-xl border border-white/10 bg-[#0d1117] p-4 transition peer-checked:border-blue-500 peer-checked:bg-blue-500/10 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500">
                <LockKeyhole
                  aria-hidden="true"
                  className="mt-0.5 size-5 shrink-0 text-blue-300"
                />

                <span>
                  <span className="block text-sm font-semibold text-white">
                    Private
                  </span>

                  <span className="mt-1 block text-xs leading-5 text-slate-500">
                    Only the owner can access this repository.
                  </span>
                </span>
              </span>
            </label>

            <label className="cursor-pointer">
              <input
                className="peer sr-only"
                type="radio"
                value="public"
                {...register('visibility')}
              />

              <span className="flex h-full gap-3 rounded-xl border border-white/10 bg-[#0d1117] p-4 transition peer-checked:border-blue-500 peer-checked:bg-blue-500/10 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500">
                <Globe2
                  aria-hidden="true"
                  className="mt-0.5 size-5 shrink-0 text-emerald-300"
                />

                <span>
                  <span className="block text-sm font-semibold text-white">
                    Public
                  </span>

                  <span className="mt-1 block text-xs leading-5 text-slate-500">
                    Authenticated users can discover and view it.
                  </span>
                </span>
              </span>
            </label>
          </div>

          {errors.visibility?.message !== undefined && (
            <p className="mt-2 text-sm text-red-400">
              {errors.visibility.message}
            </p>
          )}
        </fieldset>

        <div className="flex justify-end border-t border-white/10 pt-5">
          <button
            className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
            disabled={!isDirty || updateMutation.isPending}
            type="submit"
          >
            {updateMutation.isPending ? (
              <LoaderCircle
                aria-hidden="true"
                className="size-4 animate-spin"
              />
            ) : (
              <Save aria-hidden="true" className="size-4" />
            )}

            {updateMutation.isPending
              ? 'Saving changes...'
              : 'Save changes'}
          </button>
        </div>
      </form>
    </section>
  )
}