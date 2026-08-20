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
      visibility:
        repository.visibility,
    })
  }, [
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
    <section className="rounded-xl border border-white/10 bg-[#161b22]">
      <header className="border-b border-white/10 px-6 py-5">
        <h2 className="font-semibold text-white">
          Repository settings
        </h2>

        <p className="mt-1 text-sm text-slate-500">
          V1 allows the repository owner to change visibility only.
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
            className="rounded-lg border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300"
            role="status"
          >
            Repository visibility updated successfully.
          </div>
        )}

        {hasGeneralError && (
          <div
            className="rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300"
            role="alert"
          >
            {getApiErrorMessage(
              updateMutation.error,
              'Unable to update repository visibility.',
            )}
          </div>
        )}

        <fieldset
          disabled={
            updateMutation.isPending
          }
        >
          <legend className="mb-3 text-sm font-medium text-slate-200">
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
                    Anyone may view and clone the repository.
                  </span>
                </span>
              </span>
            </label>
          </div>

          {errors.visibility
            ?.message !==
          undefined && (
            <p className="mt-2 text-sm text-red-400">
              {
                errors.visibility
                  .message
              }
            </p>
          )}
        </fieldset>

        <div className="flex justify-end border-t border-white/10 pt-5">
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
              : 'Save visibility'}
          </button>
        </div>
      </form>
    </section>
  )
}