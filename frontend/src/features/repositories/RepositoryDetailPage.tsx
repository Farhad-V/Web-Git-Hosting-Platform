import { useState } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import {
  ArrowLeft,
  Archive,
  Check,
  Clipboard,
  GitBranch,
  Globe2,
  LoaderCircle,
  LockKeyhole,
  Terminal,
} from 'lucide-react'
import {
  Link,
  useNavigate,
  useParams,
} from 'react-router-dom'
import { getApiErrorMessage } from '../../lib/api-errors'
import { getRepository } from '../../services/repository-service'
import type { Repository } from '../../types/repository'
import { useAuth } from '../auth/auth-context'
import { RepositoryLifecycleActions } from './RepositoryLifecycleActions'
import { repositoryQueryKeys } from './repository-query-keys'
import { RepositorySettingsForm } from './RepositorySettingsForm'

const configuredGitBaseUrl =
  import.meta.env.VITE_GIT_BASE_URL
  ?? 'http://127.0.0.1:8000'

function formatDate(value: string | null): string {
  if (value === null) {
    return 'Unknown'
  }

  return new Intl.DateTimeFormat('en', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

export function RepositoryDetailPage() {
  const { repositoryId: repositoryIdParameter } = useParams()
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { user } = useAuth()

  const [isCopied, setIsCopied] = useState(false)

  const repositoryId = Number(repositoryIdParameter)

  const isValidRepositoryId =
    Number.isSafeInteger(repositoryId)
    && repositoryId > 0

  const repositoryQuery = useQuery({
    queryKey: repositoryQueryKeys.detail(repositoryId),
    queryFn: () => getRepository(repositoryId),
    enabled: isValidRepositoryId,
    retry: false,
  })

  if (!isValidRepositoryId) {
    return (
      <RepositoryPageMessage
        description="The repository identifier is invalid."
        title="Invalid repository"
      />
    )
  }

  if (repositoryQuery.isPending) {
    return (
      <main className="grid min-h-screen place-items-center bg-[#0d1117]">
        <div className="flex items-center gap-3 text-sm text-slate-400">
          <LoaderCircle
            aria-hidden="true"
            className="size-5 animate-spin text-blue-400"
          />
          Loading repository...
        </div>
      </main>
    )
  }

  if (repositoryQuery.isError) {
    return (
      <RepositoryPageMessage
        description={getApiErrorMessage(
          repositoryQuery.error,
          'The repository could not be loaded or you do not have access.',
        )}
        title="Repository unavailable"
      />
    )
  }

  const repository = repositoryQuery.data.data
  const isOwner = repository.owner.id === user?.id

  const cloneUrl = `${configuredGitBaseUrl.replace(/\/$/, '')}/${repository.full_name}.git`

  function handleRepositoryUpdated(
    updatedRepository: Repository,
  ): void {
    queryClient.setQueryData(
      repositoryQueryKeys.detail(updatedRepository.id),
      {
        data: updatedRepository,
      },
    )
  }

  function handleDeletionScheduled(): void {
    navigate('/dashboard', {
      replace: true,
    })
  }

  async function copyCloneUrl(): Promise<void> {
    try {
      await navigator.clipboard.writeText(cloneUrl)
      setIsCopied(true)

      window.setTimeout(() => {
        setIsCopied(false)
      }, 2000)
    } catch {
      setIsCopied(false)
    }
  }

  return (
    <div className="min-h-screen bg-[#0d1117] text-slate-200">
      <header className="sticky top-0 z-30 border-b border-white/10 bg-[#0d1117]/90 backdrop-blur-xl">
        <div className="mx-auto flex h-16 max-w-7xl items-center gap-4 px-5 lg:px-8">
          <Link
            aria-label="GitForge dashboard"
            className="flex shrink-0 items-center gap-2 font-semibold text-white"
            to="/dashboard"
          >
            <span className="grid size-9 place-items-center rounded-lg bg-blue-600">
              <GitBranch aria-hidden="true" className="size-5" />
            </span>
            GitForge
          </Link>

          <span
            aria-hidden="true"
            className="h-5 w-px bg-white/10"
          />

          <Link
            className="inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-white"
            to="/dashboard"
          >
            <ArrowLeft aria-hidden="true" className="size-4" />
            Dashboard
          </Link>
        </div>
      </header>

      <main className="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        <section className="border-b border-white/10 pb-8">
          <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div className="min-w-0">
              <div className="flex flex-wrap items-center gap-3">
                <h1 className="break-all text-3xl font-bold tracking-tight text-white">
                  {repository.full_name}
                </h1>

                <span className="rounded-full border border-white/10 px-2.5 py-1 text-xs capitalize text-slate-400">
                  {repository.visibility}
                </span>

                {repository.is_archived && (
                  <span className="inline-flex items-center gap-1.5 rounded-full border border-amber-400/20 bg-amber-500/10 px-2.5 py-1 text-xs text-amber-300">
                    <Archive
                      aria-hidden="true"
                      className="size-3"
                    />
                    Archived
                  </span>
                )}

                {repository.status === 'deletion_pending' && (
                  <span className="rounded-full border border-red-400/20 bg-red-500/10 px-2.5 py-1 text-xs text-red-300">
                    Deletion pending
                  </span>
                )}

                {repository.status === 'deletion_failed' && (
                  <span className="rounded-full border border-red-400/20 bg-red-500/10 px-2.5 py-1 text-xs text-red-300">
                    Deletion failed
                  </span>
                )}
              </div>

              <p className="mt-3 max-w-3xl leading-7 text-slate-400">
                {repository.description ?? 'No description provided.'}
              </p>

              <div className="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-500">
                <span>
                  Owner:{' '}
                  <span className="text-slate-300">
                    @{repository.owner.username}
                  </span>
                </span>

                <span>
                  Created: {formatDate(repository.created_at)}
                </span>

                <span>
                  Updated: {formatDate(repository.updated_at)}
                </span>
              </div>
            </div>

            <span
              className={
                repository.visibility === 'private'
                  ? 'grid size-12 shrink-0 place-items-center rounded-xl bg-blue-500/10 text-blue-300'
                  : 'grid size-12 shrink-0 place-items-center rounded-xl bg-emerald-500/10 text-emerald-300'
              }
            >
              {repository.visibility === 'private' ? (
                <LockKeyhole
                  aria-label="Private repository"
                  className="size-5"
                />
              ) : (
                <Globe2
                  aria-label="Public repository"
                  className="size-5"
                />
              )}
            </span>
          </div>
        </section>

        <div className="grid gap-6 py-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
          <section className="rounded-xl border border-white/10 bg-[#161b22]">
            <header className="flex items-center gap-3 border-b border-white/10 px-6 py-5">
              <span className="grid size-9 place-items-center rounded-lg bg-blue-500/10 text-blue-300">
                <Terminal aria-hidden="true" className="size-4" />
              </span>

              <div>
                <h2 className="font-semibold text-white">
                  Clone repository
                </h2>

                <p className="mt-0.5 text-sm text-slate-500">
                  Use Git Smart HTTP with your personal access token.
                </p>
              </div>
            </header>

            <div className="p-6">
              <label
                className="mb-2 block text-sm font-medium text-slate-300"
                htmlFor="repository-clone-url"
              >
                HTTP clone URL
              </label>

              <div className="flex flex-col gap-2 sm:flex-row">
                <input
                  className="h-11 min-w-0 flex-1 rounded-lg border border-white/10 bg-[#0d1117] px-3 font-mono text-sm text-slate-300 outline-none"
                  id="repository-clone-url"
                  readOnly
                  type="text"
                  value={cloneUrl}
                />

                <button
                  className="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg border border-white/10 px-4 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                  onClick={copyCloneUrl}
                  type="button"
                >
                  {isCopied ? (
                    <Check
                      aria-hidden="true"
                      className="size-4 text-emerald-400"
                    />
                  ) : (
                    <Clipboard
                      aria-hidden="true"
                      className="size-4"
                    />
                  )}

                  {isCopied ? 'Copied' : 'Copy'}
                </button>
              </div>

              {repository.is_empty && (
                <div className="mt-6 rounded-xl border border-white/10 bg-[#0d1117] p-5">
                  <h3 className="font-semibold text-white">
                    Empty repository
                  </h3>

                  <p className="mt-2 text-sm leading-6 text-slate-500">
                    Initialize a local Git repository, add this clone URL as
                    the remote, and push your first branch.
                  </p>

                  <pre className="mt-4 overflow-x-auto rounded-lg border border-white/10 bg-black/20 p-4 text-sm leading-7 text-slate-300">
                    <code>{`git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin ${cloneUrl}
git push -u origin main`}</code>
                  </pre>
                </div>
              )}
            </div>
          </section>

          <aside className="rounded-xl border border-white/10 bg-[#161b22] p-5">
            <h2 className="font-semibold text-white">
              Repository information
            </h2>

            <dl className="mt-5 space-y-4 text-sm">
              <div>
                <dt className="text-slate-500">Default branch</dt>
                <dd className="mt-1 font-medium text-slate-300">
                  {repository.default_branch ?? 'Not created'}
                </dd>
              </div>

              <div>
                <dt className="text-slate-500">Status</dt>
                <dd className="mt-1 font-medium capitalize text-slate-300">
                  {repository.status.replaceAll('_', ' ')}
                </dd>
              </div>

              <div>
                <dt className="text-slate-500">Write access</dt>
                <dd className="mt-1 font-medium text-slate-300">
                  {repository.is_writable ? 'Writable' : 'Read-only'}
                </dd>
              </div>

              <div>
                <dt className="text-slate-500">Storage</dt>
                <dd className="mt-1 font-medium text-slate-300">
                  Managed bare repository
                </dd>
              </div>
            </dl>
          </aside>
        </div>

        {isOwner ? (
          <div className="space-y-6">
            {repository.status !== 'deletion_pending' && (
              <RepositorySettingsForm
                onUpdated={handleRepositoryUpdated}
                repository={repository}
              />
            )}

            <RepositoryLifecycleActions
              onDeletionScheduled={handleDeletionScheduled}
              onUpdated={handleRepositoryUpdated}
              repository={repository}
            />
          </div>
        ) : (
          <section className="rounded-xl border border-white/10 bg-[#161b22] p-6">
            <h2 className="font-semibold text-white">
              Read-only access
            </h2>

            <p className="mt-2 text-sm leading-6 text-slate-500">
              You can view and clone this public repository, but only its
              owner can update settings or change its lifecycle state.
            </p>
          </section>
        )}
      </main>
    </div>
  )
}

interface RepositoryPageMessageProps {
  title: string
  description: string
}

function RepositoryPageMessage({
  title,
  description,
}: RepositoryPageMessageProps) {
  return (
    <main className="grid min-h-screen place-items-center bg-[#0d1117] px-5 text-center">
      <div>
        <span className="mx-auto grid size-12 place-items-center rounded-xl bg-blue-600 text-white">
          <GitBranch aria-hidden="true" className="size-6" />
        </span>

        <h1 className="mt-5 text-2xl font-bold text-white">
          {title}
        </h1>

        <p className="mx-auto mt-3 max-w-md text-slate-400">
          {description}
        </p>

        <Link
          className="mt-6 inline-flex items-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
          to="/dashboard"
        >
          <ArrowLeft aria-hidden="true" className="size-4" />
          Return to dashboard
        </Link>
      </div>
    </main>
  )
}