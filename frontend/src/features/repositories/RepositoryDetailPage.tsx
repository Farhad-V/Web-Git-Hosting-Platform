import { useState } from 'react'
import {
  useQuery,
  useQueryClient,
} from '@tanstack/react-query'
import {
  ArrowLeft,
  Check,
  Clipboard,
  GitBranch,
  Globe2,
  LockKeyhole,
  Terminal,
} from 'lucide-react'
import {
  Link,
  useParams,
} from 'react-router-dom'
import { getApiErrorMessage } from '../../lib/api-errors'
import { getRepository } from '../../services/repository-service'
import type { Repository } from '../../types/repository'
import { useAuth } from '../auth/auth-context'
import { RepositoryBrowser } from './RepositoryBrowser'
import { repositoryQueryKeys } from './repository-query-keys'
import { RepositorySettingsForm } from './RepositorySettingsForm'

const configuredGitBaseUrl =
  import.meta.env.VITE_GIT_BASE_URL
  ?? 'http://127.0.0.1:8000'

function formatDate(
  value: string | null,
): string {
  if (value === null) {
    return 'Unknown'
  }

  const date =
    new Date(value)

  if (
    Number.isNaN(
      date.getTime(),
    )
  ) {
    return 'Unknown'
  }

  return new Intl.DateTimeFormat(
    'en',
    {
      dateStyle:
        'medium',
      timeStyle:
        'short',
    },
  ).format(date)
}

export function RepositoryDetailPage() {
  const {
    repositoryId:
      repositoryIdParameter,
  } = useParams()

  const queryClient =
    useQueryClient()

  const {
    user,
    isAuthenticated,
  } = useAuth()

  const [
    isCopied,
    setIsCopied,
  ] = useState(false)

  const repositoryId =
    Number(
      repositoryIdParameter,
    )

  const isValidRepositoryId =
    Number.isSafeInteger(
      repositoryId,
    )
    && repositoryId > 0

  const repositoryQuery =
    useQuery({
      queryKey:
        repositoryQueryKeys.detail(
          repositoryId,
        ),

      queryFn:
        () =>
          getRepository(
            repositoryId,
          ),

      enabled:
        isValidRepositoryId,

      retry: false,
    })

  if (
    !isValidRepositoryId
  ) {
    return (
      <RepositoryPageMessage
        description="The repository identifier is invalid."
        title="Invalid repository"
      />
    )
  }

  if (
    repositoryQuery.isPending
  ) {
    return (
      <main className="grid min-h-screen place-items-center bg-[#0d1117] text-slate-400">
        Loading repository...
      </main>
    )
  }

  if (
    repositoryQuery.isError
  ) {
    return (
      <RepositoryPageMessage
        description={
          getApiErrorMessage(
            repositoryQuery.error,
            'The repository could not be loaded or you do not have access.',
          )
        }
        title="Repository unavailable"
      />
    )
  }

  const repository =
    repositoryQuery.data.data

  const isOwner =
    user?.id ===
    repository.owner.id

  const cloneUrl =
    `${configuredGitBaseUrl.replace(/\/$/, '')}/${repository.full_name}.git`

  function handleRepositoryUpdated(
    updatedRepository: Repository,
  ): void {
    queryClient.setQueryData(
      repositoryQueryKeys.detail(
        updatedRepository.id,
      ),
      {
        data:
          updatedRepository,
      },
    )
  }

  async function copyCloneUrl(): Promise<void> {
    try {
      await navigator.clipboard.writeText(
        cloneUrl,
      )

      setIsCopied(true)

      window.setTimeout(
        () => {
          setIsCopied(false)
        },
        2000,
      )
    } catch {
      setIsCopied(false)
    }
  }

  return (
    <div className="min-h-screen bg-[#0d1117] text-slate-200">
      <header className="sticky top-0 z-30 border-b border-white/10 bg-[#0d1117]/90 backdrop-blur-xl">
        <div className="mx-auto flex h-16 max-w-7xl items-center gap-4 px-5 lg:px-8">
          <Link
            aria-label="GitForge home"
            className="flex shrink-0 items-center gap-2 font-semibold text-white"
            to={
              isAuthenticated
                ? '/dashboard'
                : '/'
            }
          >
            <span className="grid size-9 place-items-center rounded-lg bg-blue-600">
              <GitBranch
                aria-hidden="true"
                className="size-5"
              />
            </span>

            GitForge
          </Link>

          <span
            aria-hidden="true"
            className="h-5 w-px bg-white/10"
          />

          <Link
            className="inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-white"
            to={
              isAuthenticated
                ? '/dashboard'
                : '/'
            }
          >
            <ArrowLeft
              aria-hidden="true"
              className="size-4"
            />

            {isAuthenticated
              ? 'Dashboard'
              : 'Home'}
          </Link>
        </div>
      </header>

      <main className="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        <section className="border-b border-white/10 pb-8">
          <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div className="min-w-0">
              <div className="flex flex-wrap items-center gap-3">
                <h1 className="break-all text-3xl font-bold tracking-tight text-white">
                  {
                    repository.full_name
                  }
                </h1>

                <span className="rounded-full border border-white/10 px-2.5 py-1 text-xs capitalize text-slate-400">
                  {
                    repository.visibility
                  }
                </span>
              </div>

              <div className="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-500">
                <span>
                  Owner:{' '}
                  <span className="text-slate-300">
                    @
                    {
                      repository.owner.username
                    }
                  </span>
                </span>

                <span>
                  Created:{' '}
                  {formatDate(
                    repository.created_at,
                  )}
                </span>

                <span>
                  Updated:{' '}
                  {formatDate(
                    repository.updated_at,
                  )}
                </span>
              </div>
            </div>

            <span
              className={
                repository.visibility ===
                'private'
                  ? 'grid size-12 shrink-0 place-items-center rounded-xl bg-blue-500/10 text-blue-300'
                  : 'grid size-12 shrink-0 place-items-center rounded-xl bg-emerald-500/10 text-emerald-300'
              }
            >
              {repository.visibility ===
              'private' ? (
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
                <Terminal
                  aria-hidden="true"
                  className="size-4"
                />
              </span>

              <div>
                <h2 className="font-semibold text-white">
                  Clone repository
                </h2>

                <p className="mt-0.5 text-sm text-slate-500">
                  Use Git Smart HTTP.
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
                  value={
                    cloneUrl
                  }
                />

                <button
                  className="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg border border-white/10 px-4 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                  onClick={
                    copyCloneUrl
                  }
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

                  {isCopied
                    ? 'Copied'
                    : 'Copy'}
                </button>
              </div>

              {repository.visibility ===
                'private' && (
                <p className="mt-3 text-sm text-slate-500">
                  Private Git operations require a personal access
                  token. The repository owner is the only V1 writer.
                </p>
              )}
            </div>
          </section>

          <aside className="rounded-xl border border-white/10 bg-[#161b22] p-5">
            <h2 className="font-semibold text-white">
              Repository information
            </h2>

            <dl className="mt-5 space-y-4 text-sm">
              <div>
                <dt className="text-slate-500">
                  Name
                </dt>

                <dd className="mt-1 font-medium text-slate-300">
                  {
                    repository.name
                  }
                </dd>
              </div>

              <div>
                <dt className="text-slate-500">
                  Namespace
                </dt>

                <dd className="mt-1 font-medium text-slate-300">
                  @
                  {
                    repository.owner.username
                  }
                </dd>
              </div>

              <div>
                <dt className="text-slate-500">
                  Visibility
                </dt>

                <dd className="mt-1 font-medium capitalize text-slate-300">
                  {
                    repository.visibility
                  }
                </dd>
              </div>

              <div>
                <dt className="text-slate-500">
                  Storage
                </dt>

                <dd className="mt-1 font-medium text-slate-300">
                  Managed bare Git repository
                </dd>
              </div>
            </dl>
          </aside>
        </div>

        <div className="pb-8">
          <RepositoryBrowser
            repository={
              repository
            }
          />
        </div>

        {isOwner ? (
          <RepositorySettingsForm
            onUpdated={
              handleRepositoryUpdated
            }
            repository={
              repository
            }
          />
        ) : (
          <section className="rounded-xl border border-white/10 bg-[#161b22] p-6">
            <h2 className="font-semibold text-white">
              Read-only web access
            </h2>

            <p className="mt-2 text-sm leading-6 text-slate-500">
              Public repositories may be viewed anonymously. Only the
              repository owner may change visibility or push changes in
              V1.
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
          <GitBranch
            aria-hidden="true"
            className="size-6"
          />
        </span>

        <h1 className="mt-5 text-2xl font-bold text-white">
          {title}
        </h1>

        <p className="mx-auto mt-3 max-w-md text-slate-400">
          {description}
        </p>

        <Link
          className="mt-6 inline-flex items-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
          to="/"
        >
          <ArrowLeft
            aria-hidden="true"
            className="size-4"
          />

          Return home
        </Link>
      </div>
    </main>
  )
}