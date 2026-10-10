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
import { getGitCloneUrl } from '../../lib/git-url'
import { getRepository } from '../../services/repository-service'
import type { Repository } from '../../types/repository'
import { useAuth } from '../auth/auth-context'
import { RepositoryBrowser } from './RepositoryBrowser'
import { repositoryQueryKeys } from './repository-query-keys'
import { RepositorySettingsForm } from './RepositorySettingsForm'

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
      <main className="grid min-h-screen place-items-center bg-[var(--inset)] text-[var(--muted)]">
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
    getGitCloneUrl(repository.full_name)

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
    <div className="min-h-screen text-[var(--text)]">
      <header className="sticky top-0 z-30 border-b border-[var(--border)] bg-[var(--glass-header)] backdrop-blur-xl">
        <div className="mx-auto flex h-16 max-w-7xl items-center gap-4 px-5 lg:px-8">
          <Link
            aria-label="GitForge home"
            className="flex shrink-0 items-center gap-2 font-semibold text-[var(--text)]"
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
            className="h-5 w-px bg-[var(--hover)]"
          />

          <Link
            className="inline-flex items-center gap-2 text-sm text-[var(--muted)] transition hover:text-[var(--text)]"
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
        <section className="border-b border-[var(--border)] pb-8">
          <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div className="min-w-0">
              <div className="flex flex-wrap items-center gap-3">
                <h1 className="break-all text-3xl font-bold tracking-tight text-[var(--text)]">
                  {
                    repository.full_name
                  }
                </h1>

                <span className="rounded-full border border-[var(--border)] px-2.5 py-1 text-xs capitalize text-[var(--muted)]">
                  {
                    repository.visibility
                  }
                </span>
              </div>

              <div className="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-sm text-[var(--muted)]">
                <span>
                  Owner:{' '}
                  <span className="text-[var(--text)]">
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
                  ? 'grid size-12 shrink-0 place-items-center rounded-xl bg-blue-500/10 text-[var(--accent)]'
                  : 'grid size-12 shrink-0 place-items-center rounded-xl bg-emerald-500/10 text-[var(--emerald)]'
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
          <section className="rounded-xl border border-[var(--border)] glass-panel">
            <header className="flex items-center gap-3 border-b border-[var(--border)] px-6 py-5">
              <span className="grid size-9 place-items-center rounded-lg bg-blue-500/10 text-[var(--accent)]">
                <Terminal
                  aria-hidden="true"
                  className="size-4"
                />
              </span>

              <div>
                <h2 className="font-semibold text-[var(--text)]">
                  Clone repository
                </h2>

                <p className="mt-0.5 text-sm text-[var(--muted)]">
                  Use Git Smart HTTP.
                </p>
              </div>
            </header>

            <div className="p-6">
              <label
                className="mb-2 block text-sm font-medium text-[var(--text)]"
                htmlFor="repository-clone-url"
              >
                HTTP clone URL
              </label>

              <div className="flex flex-col gap-2 sm:flex-row">
                <input
                  className="h-11 min-w-0 flex-1 rounded-lg border border-[var(--border)] bg-[var(--inset)] px-3 font-mono text-sm text-[var(--text)] outline-none"
                  id="repository-clone-url"
                  readOnly
                  type="text"
                  value={
                    cloneUrl
                  }
                />

                <button
                  className="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg border border-[var(--border)] px-4 text-sm font-semibold text-[var(--text)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)]"
                  onClick={
                    copyCloneUrl
                  }
                  type="button"
                >
                  {isCopied ? (
                    <Check
                      aria-hidden="true"
                      className="size-4 text-[var(--emerald)]"
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
                <p className="mt-3 text-sm text-[var(--muted)]">
                  Private Git operations require a personal access
                  token. The repository owner is the only V1 writer.
                </p>
              )}
            </div>
          </section>

          <aside className="rounded-xl border border-[var(--border)] glass-panel p-5">
            <h2 className="font-semibold text-[var(--text)]">
              Repository information
            </h2>

            <dl className="mt-5 space-y-4 text-sm">
              <div>
                <dt className="text-[var(--muted)]">
                  Name
                </dt>

                <dd className="mt-1 font-medium text-[var(--text)]">
                  {
                    repository.name
                  }
                </dd>
              </div>

              <div>
                <dt className="text-[var(--muted)]">
                  Namespace
                </dt>

                <dd className="mt-1 font-medium text-[var(--text)]">
                  @
                  {
                    repository.owner.username
                  }
                </dd>
              </div>

              <div>
                <dt className="text-[var(--muted)]">
                  Visibility
                </dt>

                <dd className="mt-1 font-medium capitalize text-[var(--text)]">
                  {
                    repository.visibility
                  }
                </dd>
              </div>

              <div>
                <dt className="text-[var(--muted)]">
                  Storage
                </dt>

                <dd className="mt-1 font-medium text-[var(--text)]">
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
          <section className="rounded-xl border border-[var(--border)] glass-panel p-6">
            <h2 className="font-semibold text-[var(--text)]">
              Read-only web access
            </h2>

            <p className="mt-2 text-sm leading-6 text-[var(--muted)]">
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
    <main className="grid min-h-screen place-items-center bg-[var(--inset)] px-5 text-center">
      <div>
        <span className="mx-auto grid size-12 place-items-center rounded-xl bg-blue-600 text-white">
          <GitBranch
            aria-hidden="true"
            className="size-6"
          />
        </span>

        <h1 className="mt-5 text-2xl font-bold text-[var(--text)]">
          {title}
        </h1>

        <p className="mx-auto mt-3 max-w-md text-[var(--muted)]">
          {description}
        </p>

        <Link
          className="mt-6 inline-flex items-center gap-2 rounded-lg border border-[var(--border)] px-4 py-2 text-sm font-semibold text-[var(--text)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)]"
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