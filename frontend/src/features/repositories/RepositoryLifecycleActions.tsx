import { useMutation, useQueryClient } from '@tanstack/react-query'
import {
  Archive,
  ArchiveRestore,
  LoaderCircle,
  Trash2,
} from 'lucide-react'
import { getApiErrorMessage } from '../../lib/api-errors'
import {
  archiveRepository,
  scheduleRepositoryDeletion,
  unarchiveRepository,
} from '../../services/repository-service'
import type { Repository } from '../../types/repository'
import { repositoryQueryKeys } from './repository-query-keys'

interface RepositoryLifecycleActionsProps {
  repository: Repository
  onUpdated: (repository: Repository) => void
  onDeletionScheduled: () => void
}

export function RepositoryLifecycleActions({
  repository,
  onUpdated,
  onDeletionScheduled,
}: RepositoryLifecycleActionsProps) {
  const queryClient = useQueryClient()

  const archiveMutation = useMutation({
    mutationFn: () => archiveRepository(repository.id),
  })

  const unarchiveMutation = useMutation({
    mutationFn: () => unarchiveRepository(repository.id),
  })

  const deleteMutation = useMutation({
    mutationFn: () => scheduleRepositoryDeletion(repository.id),
  })

  const isMutating =
    archiveMutation.isPending
    || unarchiveMutation.isPending
    || deleteMutation.isPending

  async function updateCachedRepository(
    updatedRepository: Repository,
  ): Promise<void> {
    queryClient.setQueryData(
      repositoryQueryKeys.detail(repository.id),
      {
        data: updatedRepository,
      },
    )

    await queryClient.invalidateQueries({
      queryKey: repositoryQueryKeys.lists(),
    })

    onUpdated(updatedRepository)
  }

  async function handleArchive(): Promise<void> {
    const confirmed = window.confirm(
      `Archive "${repository.full_name}"? The repository will become read-only.`,
    )

    if (!confirmed) {
      return
    }

    archiveMutation.reset()

    try {
      const response = await archiveMutation.mutateAsync()
      await updateCachedRepository(response.repository)
    } catch {
      // The mutation state renders the API error.
    }
  }

  async function handleUnarchive(): Promise<void> {
    unarchiveMutation.reset()

    try {
      const response = await unarchiveMutation.mutateAsync()
      await updateCachedRepository(response.repository)
    } catch {
      // The mutation state renders the API error.
    }
  }

  async function handleDelete(): Promise<void> {
    const confirmed = window.confirm(
      `Schedule "${repository.full_name}" for permanent deletion? This action cannot be undone after storage deletion completes.`,
    )

    if (!confirmed) {
      return
    }

    deleteMutation.reset()

    try {
      await deleteMutation.mutateAsync()

      await queryClient.invalidateQueries({
        queryKey: repositoryQueryKeys.lists(),
      })

      queryClient.removeQueries({
        queryKey: repositoryQueryKeys.detail(repository.id),
      })

      onDeletionScheduled()
    } catch {
      // The mutation state renders the API error.
    }
  }

  const mutationError =
    archiveMutation.error
    ?? unarchiveMutation.error
    ?? deleteMutation.error

  return (
    <section className="rounded-xl border border-red-400/20 bg-[#161b22]">
      <header className="border-b border-red-400/15 px-6 py-5">
        <h2 className="font-semibold text-white">
          Repository lifecycle
        </h2>

        <p className="mt-1 text-sm text-slate-500">
          Control write access or schedule permanent repository deletion.
        </p>
      </header>

      <div className="space-y-5 p-6">
        {mutationError !== null && (
          <div
            className="rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300"
            role="alert"
          >
            {getApiErrorMessage(
              mutationError,
              'Unable to change the repository lifecycle state.',
            )}
          </div>
        )}

        <div className="flex flex-col gap-4 rounded-xl border border-white/10 bg-[#0d1117] p-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 className="text-sm font-semibold text-white">
              {repository.is_archived
                ? 'Restore repository'
                : 'Archive repository'}
            </h3>

            <p className="mt-1 max-w-xl text-sm leading-6 text-slate-500">
              {repository.is_archived
                ? 'Return this repository to active status and allow writes again.'
                : 'Make this repository read-only while preserving its metadata and Git storage.'}
            </p>
          </div>

          {repository.is_archived ? (
            <button
              className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg border border-emerald-400/20 bg-emerald-500/10 px-4 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500/20 disabled:cursor-not-allowed disabled:opacity-50"
              disabled={isMutating}
              onClick={handleUnarchive}
              type="button"
            >
              {unarchiveMutation.isPending ? (
                <LoaderCircle
                  aria-hidden="true"
                  className="size-4 animate-spin"
                />
              ) : (
                <ArchiveRestore
                  aria-hidden="true"
                  className="size-4"
                />
              )}

              {unarchiveMutation.isPending
                ? 'Restoring...'
                : 'Restore repository'}
            </button>
          ) : (
            <button
              className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg border border-amber-400/20 bg-amber-500/10 px-4 text-sm font-semibold text-amber-300 transition hover:bg-amber-500/20 disabled:cursor-not-allowed disabled:opacity-50"
              disabled={
                isMutating
                || repository.status !== 'active'
              }
              onClick={handleArchive}
              type="button"
            >
              {archiveMutation.isPending ? (
                <LoaderCircle
                  aria-hidden="true"
                  className="size-4 animate-spin"
                />
              ) : (
                <Archive aria-hidden="true" className="size-4" />
              )}

              {archiveMutation.isPending
                ? 'Archiving...'
                : 'Archive repository'}
            </button>
          )}
        </div>

        <div className="flex flex-col gap-4 rounded-xl border border-red-400/20 bg-red-500/[0.04] p-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 className="text-sm font-semibold text-red-200">
              Delete repository
            </h3>

            <p className="mt-1 max-w-xl text-sm leading-6 text-slate-500">
              Schedule coordinated deletion of the managed Git storage and
              repository metadata. This operation is irreversible after the
              deletion job completes.
            </p>
          </div>

          <button
            className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-50"
            disabled={
              isMutating
              || repository.status === 'deletion_pending'
            }
            onClick={handleDelete}
            type="button"
          >
            {deleteMutation.isPending ? (
              <LoaderCircle
                aria-hidden="true"
                className="size-4 animate-spin"
              />
            ) : (
              <Trash2 aria-hidden="true" className="size-4" />
            )}

            {deleteMutation.isPending
              ? 'Scheduling deletion...'
              : repository.status === 'deletion_pending'
                ? 'Deletion pending'
                : 'Delete repository'}
          </button>
        </div>
      </div>
    </section>
  )
}