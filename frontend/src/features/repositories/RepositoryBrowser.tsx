import { useState } from 'react'
import {
  useQuery,
  useQueryClient,
} from '@tanstack/react-query'
import {
  AlertCircle,
  ArrowLeft,
  ChevronRight,
  FileCode2,
  Folder,
  GitBranch,
  GitCommitHorizontal,
  History,
  Home,
  LoaderCircle,
  RefreshCw,
} from 'lucide-react'
import { getApiErrorMessage } from '../../lib/api-errors'
import {
  getRepositoryBlob,
  getRepositoryBranches,
  getRepositoryCommit,
  getRepositoryCommits,
  getRepositoryTree,
} from '../../services/repository-browser-service'
import type { Repository } from '../../types/repository'
import type {
  RepositoryCommitDetails,
  RepositoryTreeEntry,
} from '../../types/repository-browser'
import { repositoryQueryKeys } from './repository-query-keys'

interface RepositoryBrowserProps {
  repository: Repository
}

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

function formatFileSize(
  value: number | null,
): string {
  if (value === null) {
    return '—'
  }

  if (value < 1024) {
    return `${value} B`
  }

  if (
    value <
    1024 * 1024
  ) {
    return `${(
      value / 1024
    ).toFixed(1)} KB`
  }

  return `${(
    value /
    (1024 * 1024)
  ).toFixed(1)} MB`
}

function shortHash(
  value: string,
): string {
  return value.slice(
    0,
    7,
  )
}

export function RepositoryBrowser({
  repository,
}: RepositoryBrowserProps) {
  const queryClient =
    useQueryClient()

  const [
    branch,
    setBranch,
  ] = useState('')

  const [
    currentPath,
    setCurrentPath,
  ] = useState('')

  const [
    selectedFile,
    setSelectedFile,
  ] = useState<string | null>(
    null,
  )

  const [
    selectedCommit,
    setSelectedCommit,
  ] = useState<string | null>(
    null,
  )

  const branchesQuery =
    useQuery({
      queryKey:
        repositoryQueryKeys.branches(
          repository.id,
        ),

      queryFn:
        () =>
          getRepositoryBranches(
            repository.id,
          ),

      retry: false,
    })

  const isEmpty =
    branchesQuery.isSuccess
    && branchesQuery.data.data.length ===
      0
    && branchesQuery.data.default_branch ===
      null

  const selectedBranch =
    branch !== ''
      ? branch
      : branchesQuery.data?.default_branch
        ?? ''

  const treeQuery =
    useQuery({
      queryKey:
        repositoryQueryKeys.tree(
          repository.id,
          selectedBranch,
          currentPath,
        ),

      queryFn:
        () =>
          getRepositoryTree(
            repository.id,
            {
              branch:
                selectedBranch,
              path:
                currentPath,
            },
          ),

      enabled:
        selectedBranch !== ''
        && selectedFile ===
          null,

      retry: false,
    })

  const commitsQuery =
    useQuery({
      queryKey:
        repositoryQueryKeys.commits(
          repository.id,
          selectedBranch,
          10,
        ),

      queryFn:
        () =>
          getRepositoryCommits(
            repository.id,
            {
              branch:
                selectedBranch,
              limit: 10,
            },
          ),

      enabled:
        selectedBranch !== '',

      retry: false,
    })

  const commitQuery =
    useQuery({
      queryKey:
        repositoryQueryKeys.commit(
          repository.id,
          selectedCommit
            ?? '',
        ),

      queryFn:
        () =>
          getRepositoryCommit(
            repository.id,
            selectedCommit
              ?? '',
          ),

      enabled:
        selectedCommit !==
        null,

      retry: false,
    })

  const blobQuery =
    useQuery({
      queryKey:
        repositoryQueryKeys.blob(
          repository.id,
          selectedBranch,
          selectedFile
            ?? '',
        ),

      queryFn:
        () =>
          getRepositoryBlob(
            repository.id,
            {
              branch:
                selectedBranch,
              path:
                selectedFile
                ?? '',
            },
          ),

      enabled:
        selectedBranch !== ''
        && selectedFile !==
          null,

      retry: false,
    })

  const pathSegments =
    currentPath === ''
      ? []
      : currentPath.split(
          '/',
        )

  function handleBranchChange(
    nextBranch: string,
  ): void {
    setBranch(
      nextBranch,
    )

    setCurrentPath('')
    setSelectedFile(null)
    setSelectedCommit(null)
  }

  function openDirectory(
    path: string,
  ): void {
    setCurrentPath(
      path,
    )

    setSelectedFile(null)
  }

  function openFile(
    path: string,
  ): void {
    setSelectedFile(
      path,
    )
  }

  function returnToDirectory(): void {
    setSelectedFile(null)
  }

  function openCommit(
    commit: string,
  ): void {
    setSelectedCommit(
      commit,
    )
  }

  function returnToCommits(): void {
    setSelectedCommit(null)
  }

  function openBreadcrumb(
    index: number,
  ): void {
    const nextPath =
      pathSegments
        .slice(
          0,
          index + 1,
        )
        .join('/')

    openDirectory(
      nextPath,
    )
  }

  function refreshBrowser(): void {
    void queryClient.invalidateQueries({
      queryKey:
        repositoryQueryKeys.browser(
          repository.id,
        ),
    })
  }

  if (
    branchesQuery.isPending
  ) {
    return (
      <section className="rounded-xl border border-[var(--border)] glass-panel">
        <LoadingState message="Loading repository state..." />
      </section>
    )
  }

  if (
    branchesQuery.isError
  ) {
    return (
      <section className="rounded-xl border border-[var(--border)] glass-panel">
        <ErrorState
          error={
            branchesQuery.error
          }
          fallback="The repository browser could not be initialized."
        />
      </section>
    )
  }

  if (isEmpty) {
    return (
      <section className="rounded-xl border border-[var(--border)] glass-panel p-6">
        <div className="flex items-start gap-4">
          <span className="grid size-11 shrink-0 place-items-center rounded-lg bg-blue-500/10 text-[var(--accent)]">
            <GitBranch
              aria-hidden="true"
              className="size-5"
            />
          </span>

          <div>
            <h2 className="font-semibold text-[var(--text)]">
              Empty repository
            </h2>

            <p className="mt-2 max-w-2xl text-sm leading-6 text-[var(--muted)]">
              This repository does not contain a committed branch yet.
              Push the first branch through Git Smart HTTP and the
              repository browser will use the Git-native default branch.
            </p>
          </div>
        </div>
      </section>
    )
  }

  if (selectedBranch === '') {
    return (
      <section className="rounded-xl border border-[var(--border)] glass-panel">
        <LoadingState message="Resolving default branch..." />
      </section>
    )
  }

  return (
    <section className="overflow-hidden rounded-xl border border-[var(--border)] glass-panel">
      <header className="flex flex-col gap-4 border-b border-[var(--border)] px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-3">
          <span className="grid size-9 place-items-center rounded-lg bg-blue-500/10 text-[var(--accent)]">
            <FileCode2
              aria-hidden="true"
              className="size-4"
            />
          </span>

          <div>
            <h2 className="font-semibold text-[var(--text)]">
              Repository browser
            </h2>

            <p className="mt-0.5 text-sm text-[var(--muted)]">
              Browse Git-native branches, files, and recent commits.
            </p>
          </div>
        </div>

        <button
          aria-label="Refresh repository browser"
          className="inline-flex size-9 items-center justify-center rounded-lg border border-[var(--border)] text-[var(--muted)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)]"
          onClick={
            refreshBrowser
          }
          type="button"
        >
          <RefreshCw
            aria-hidden="true"
            className="size-4"
          />
        </button>
      </header>

      <div className="grid lg:grid-cols-[minmax(0,1fr)_21rem]">
        <div className="min-w-0 border-b border-[var(--border)] lg:border-b-0 lg:border-r">
          <div className="flex flex-col gap-3 border-b border-[var(--border)] p-4 sm:flex-row sm:items-center">
            <label
              className="sr-only"
              htmlFor="repository-browser-branch"
            >
              Repository branch
            </label>

            <div className="relative shrink-0">
              <GitBranch
                aria-hidden="true"
                className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[var(--muted)]"
              />

              <select
                className="h-10 min-w-44 appearance-none rounded-lg border border-[var(--border)] bg-[var(--inset)] pl-9 pr-8 text-sm font-medium text-[var(--text)] outline-none transition focus:border-blue-500/60"
                id="repository-browser-branch"
                onChange={(
                  event,
                ) =>
                  handleBranchChange(
                    event.target.value,
                  )
                }
                value={
                  selectedBranch
                }
              >
                {branchesQuery.data.data.map(
                  (
                    repositoryBranch,
                  ) => (
                    <option
                      key={
                        repositoryBranch.name
                      }
                      value={
                        repositoryBranch.name
                      }
                    >
                      {
                        repositoryBranch.name
                      }
                    </option>
                  ),
                )}
              </select>
            </div>

            <nav
              aria-label="Repository path"
              className="flex min-w-0 flex-1 items-center gap-1 overflow-x-auto text-sm"
            >
              <button
                aria-label="Repository root"
                className="inline-flex shrink-0 items-center gap-1 rounded-md px-2 py-1.5 text-[var(--muted)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)]"
                onClick={() =>
                  openDirectory(
                    '',
                  )
                }
                type="button"
              >
                <Home
                  aria-hidden="true"
                  className="size-4"
                />
              </button>

              {pathSegments.map(
                (
                  segment,
                  index,
                ) => (
                  <span
                    className="flex items-center gap-1"
                    key={`${segment}-${index}`}
                  >
                    <ChevronRight
                      aria-hidden="true"
                      className="size-4 shrink-0 text-[var(--muted)]"
                    />

                    <button
                      className="max-w-48 truncate rounded-md px-2 py-1.5 text-[var(--muted)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)]"
                      onClick={() =>
                        openBreadcrumb(
                          index,
                        )
                      }
                      title={
                        segment
                      }
                      type="button"
                    >
                      {
                        segment
                      }
                    </button>
                  </span>
                ),
              )}
            </nav>
          </div>

          {selectedFile !==
          null ? (
            <FileViewer
              error={
                blobQuery.error
              }
              filePath={
                selectedFile
              }
              isError={
                blobQuery.isError
              }
              isPending={
                blobQuery.isPending
              }
              onBack={
                returnToDirectory
              }
              size={
                blobQuery.data?.data.size
                ?? null
              }
            >
              {blobQuery.data?.data.content
                ?? ''}
            </FileViewer>
          ) : (
            <TreeViewer
              error={
                treeQuery.error
              }
              isError={
                treeQuery.isError
              }
              isPending={
                treeQuery.isPending
              }
              onOpenDirectory={
                openDirectory
              }
              onOpenFile={
                openFile
              }
              tree={
                treeQuery.data?.data
                ?? []
              }
            />
          )}
        </div>

        <aside>
          {selectedCommit ===
          null ? (
            <RecentCommits
              commits={
                commitsQuery.data?.data
                ?? []
              }
              error={
                commitsQuery.error
              }
              isError={
                commitsQuery.isError
              }
              isPending={
                commitsQuery.isPending
              }
              onOpenCommit={
                openCommit
              }
            />
          ) : (
            <CommitDetails
              commit={
                commitQuery.data?.commit
                ?? null
              }
              error={
                commitQuery.error
              }
              isError={
                commitQuery.isError
              }
              isPending={
                commitQuery.isPending
              }
              onBack={
                returnToCommits
              }
            />
          )}
        </aside>
      </div>
    </section>
  )
}

interface RecentCommitsProps {
  commits: {
    hash: string
    parents: string[]
    author: {
      name: string
      email: string
    }
    authored_at: string
    subject: string
  }[]

  isPending: boolean
  isError: boolean
  error: unknown
  onOpenCommit: (
    commit: string,
  ) => void
}

function RecentCommits({
  commits,
  isPending,
  isError,
  error,
  onOpenCommit,
}: RecentCommitsProps) {
  return (
    <>
      <header className="flex items-center gap-2 border-b border-[var(--border)] px-5 py-4">
        <History
          aria-hidden="true"
          className="size-4 text-[var(--accent)]"
        />

        <h3 className="font-semibold text-[var(--text)]">
          Recent commits
        </h3>
      </header>

      <div className="max-h-[32rem] overflow-y-auto">
        {isPending && (
          <LoadingState message="Loading commits..." />
        )}

        {isError && (
          <ErrorState
            error={
              error
            }
            fallback="Recent commits could not be loaded."
          />
        )}

        {!isPending
          && !isError
          && commits.length ===
            0 && (
          <EmptyState message="No commits found on this branch." />
        )}

        {!isPending
          && !isError
          && commits.map(
            (
              commit,
            ) => (
              <button
                aria-label={`View commit ${shortHash(
                  commit.hash,
                )}`}
                className="block w-full border-b border-[var(--border)] px-5 py-4 text-left transition last:border-b-0 hover:bg-[var(--subtle)]"
                key={
                  commit.hash
                }
                onClick={() =>
                  onOpenCommit(
                    commit.hash,
                  )
                }
                type="button"
              >
                <div className="flex items-start gap-3">
                  <span className="mt-0.5 grid size-8 shrink-0 place-items-center rounded-lg bg-[var(--subtle)] text-[var(--muted)]">
                    <GitCommitHorizontal
                      aria-hidden="true"
                      className="size-4"
                    />
                  </span>

                  <div className="min-w-0">
                    <h4
                      className="line-clamp-2 text-sm font-medium leading-5 text-[var(--text)]"
                      title={
                        commit.subject
                      }
                    >
                      {
                        commit.subject
                      }
                    </h4>

                    <p className="mt-1 truncate text-xs text-[var(--muted)]">
                      {
                        commit.author.name
                      }
                    </p>

                    <div className="mt-2 flex flex-wrap items-center gap-2 text-xs text-[var(--muted)]">
                      <code className="rounded bg-black/20 px-1.5 py-0.5 font-mono text-[var(--accent)]">
                        {shortHash(
                          commit.hash,
                        )}
                      </code>

                      <span>
                        {formatDate(
                          commit.authored_at,
                        )}
                      </span>
                    </div>
                  </div>
                </div>
              </button>
            ),
          )}
      </div>
    </>
  )
}

interface CommitDetailsProps {
  commit: RepositoryCommitDetails | null
  isPending: boolean
  isError: boolean
  error: unknown
  onBack: () => void
}

function CommitDetails({
  commit,
  isPending,
  isError,
  error,
  onBack,
}: CommitDetailsProps) {
  return (
    <>
      <header className="flex items-center gap-3 border-b border-[var(--border)] px-5 py-4">
        <button
          aria-label="Back to recent commits"
          className="inline-flex size-8 shrink-0 items-center justify-center rounded-lg border border-[var(--border)] text-[var(--muted)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)]"
          onClick={
            onBack
          }
          type="button"
        >
          <ArrowLeft
            aria-hidden="true"
            className="size-4"
          />
        </button>

        <div className="flex min-w-0 items-center gap-2">
          <GitCommitHorizontal
            aria-hidden="true"
            className="size-4 shrink-0 text-[var(--accent)]"
          />

          <h3 className="font-semibold text-[var(--text)]">
            Commit details
          </h3>
        </div>
      </header>

      {isPending && (
        <LoadingState message="Loading commit details..." />
      )}

      {isError && (
        <ErrorState
          error={
            error
          }
          fallback="The commit details could not be loaded."
        />
      )}

      {!isPending
        && !isError
        && commit !==
          null && (
          <div className="max-h-[32rem] space-y-5 overflow-y-auto p-5">
            <div>
              <p className="text-xs font-medium uppercase tracking-wide text-[var(--muted)]">
                Message
              </p>

              <p className="mt-2 whitespace-pre-wrap break-words text-sm leading-6 text-[var(--text)]">
                {
                  commit.message
                }
              </p>
            </div>

            <CommitIdentity
              email={
                commit.author_email
              }
              label="Author"
              name={
                commit.author_name
              }
              timestamp={
                commit.authored_at
              }
            />

            <CommitIdentity
              email={
                commit.committer_email
              }
              label="Committer"
              name={
                commit.committer_name
              }
              timestamp={
                commit.committed_at
              }
            />

            <CommitReference
              label="Commit"
              value={
                commit.id
              }
            />

            <CommitReference
              label="Tree"
              value={
                commit.tree
              }
            />

            <div>
              <p className="text-xs font-medium uppercase tracking-wide text-[var(--muted)]">
                Parents
              </p>

              {commit.parents.length ===
              0 ? (
                <p className="mt-2 text-sm text-[var(--muted)]">
                  Root commit
                </p>
              ) : (
                <div className="mt-2 space-y-2">
                  {commit.parents.map(
                    (
                      parent,
                    ) => (
                      <code
                        className="block break-all rounded-lg bg-black/20 px-3 py-2 font-mono text-xs text-[var(--muted)]"
                        key={
                          parent
                        }
                      >
                        {
                          parent
                        }
                      </code>
                    ),
                  )}
                </div>
              )}
            </div>
          </div>
        )}
    </>
  )
}

interface CommitIdentityProps {
  label: string
  name: string
  email: string
  timestamp: string
}

function CommitIdentity({
  label,
  name,
  email,
  timestamp,
}: CommitIdentityProps) {
  return (
    <div>
      <p className="text-xs font-medium uppercase tracking-wide text-[var(--muted)]">
        {label}
      </p>

      <p className="mt-2 text-sm font-medium text-[var(--text)]">
        {name}
      </p>

      <p className="mt-1 break-all text-xs text-[var(--muted)]">
        {email}
      </p>

      <p className="mt-1 text-xs text-[var(--muted)]">
        {formatDate(
          timestamp,
        )}
      </p>
    </div>
  )
}

interface CommitReferenceProps {
  label: string
  value: string
}

function CommitReference({
  label,
  value,
}: CommitReferenceProps) {
  return (
    <div>
      <p className="text-xs font-medium uppercase tracking-wide text-[var(--muted)]">
        {label}
      </p>

      <code className="mt-2 block break-all rounded-lg bg-black/20 px-3 py-2 font-mono text-xs text-[var(--accent)]">
        {value}
      </code>
    </div>
  )
}

interface TreeViewerProps {
  tree: RepositoryTreeEntry[]
  isPending: boolean
  isError: boolean
  error: unknown
  onOpenDirectory: (
    path: string,
  ) => void
  onOpenFile: (
    path: string,
  ) => void
}

function TreeViewer({
  tree,
  isPending,
  isError,
  error,
  onOpenDirectory,
  onOpenFile,
}: TreeViewerProps) {
  if (isPending) {
    return (
      <LoadingState message="Loading repository files..." />
    )
  }

  if (isError) {
    return (
      <ErrorState
        error={
          error
        }
        fallback="The repository directory could not be loaded."
      />
    )
  }

  if (tree.length === 0) {
    return (
      <EmptyState message="This directory contains no files." />
    )
  }

  return (
    <div className="divide-y divide-white/10">
      {tree.map(
        (
          entry,
        ) => {
          const isDirectory =
            entry.type ===
            'directory'

          const isSubmodule =
            entry.type ===
            'submodule'

          const isBrowsable =
            !isSubmodule

          return (
            <button
              className="flex w-full items-center gap-3 px-5 py-3.5 text-left transition enabled:hover:bg-[var(--subtle)] disabled:cursor-default"
              disabled={
                !isBrowsable
              }
              key={
                `${entry.path}-${entry.object_id}`
              }
              onClick={() => {
                if (
                  isDirectory
                ) {
                  onOpenDirectory(
                    entry.path,
                  )

                  return
                }

                if (
                  entry.type ===
                  'file'
                ) {
                  onOpenFile(
                    entry.path,
                  )
                }
              }}
              type="button"
            >
              {isDirectory ? (
                <Folder
                  aria-hidden="true"
                  className="size-5 shrink-0 text-[var(--accent)]"
                />
              ) : isSubmodule ? (
                <GitCommitHorizontal
                  aria-hidden="true"
                  className="size-5 shrink-0 text-[var(--muted)]"
                />
              ) : (
                <FileCode2
                  aria-hidden="true"
                  className="size-5 shrink-0 text-[var(--muted)]"
                />
              )}

              <span className="min-w-0 flex-1 truncate text-sm font-medium text-[var(--text)]">
                {
                  entry.name
                }
              </span>

              <span className="shrink-0 text-xs text-[var(--muted)]">
                {isDirectory
                  ? 'Directory'
                  : isSubmodule
                    ? 'Submodule'
                    : formatFileSize(
                        entry.size,
                      )}
              </span>

              {isBrowsable && (
                <ChevronRight
                  aria-hidden="true"
                  className="size-4 shrink-0 text-[var(--muted)]"
                />
              )}
            </button>
          )
        },
      )}
    </div>
  )
}

interface FileViewerProps {
  filePath: string
  size: number | null
  isPending: boolean
  isError: boolean
  error: unknown
  onBack: () => void
  children: string
}

function FileViewer({
  filePath,
  size,
  isPending,
  isError,
  error,
  onBack,
  children,
}: FileViewerProps) {
  return (
    <div>
      <header className="flex items-center justify-between gap-4 border-b border-[var(--border)] px-5 py-3">
        <div className="flex min-w-0 items-center gap-3">
          <button
            aria-label="Return to directory"
            className="inline-flex size-8 shrink-0 items-center justify-center rounded-lg border border-[var(--border)] text-[var(--muted)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)]"
            onClick={
              onBack
            }
            type="button"
          >
            <ArrowLeft
              aria-hidden="true"
              className="size-4"
            />
          </button>

          <div className="min-w-0">
            <p
              className="truncate text-sm font-medium text-[var(--text)]"
              title={
                filePath
              }
            >
              {
                filePath
              }
            </p>

            <p className="mt-0.5 text-xs text-[var(--muted)]">
              {formatFileSize(
                size,
              )}
            </p>
          </div>
        </div>
      </header>

      {isPending && (
        <LoadingState message="Loading file..." />
      )}

      {isError && (
        <ErrorState
          error={
            error
          }
          fallback="The repository file could not be loaded."
        />
      )}

      {!isPending
        && !isError && (
          <pre className="max-h-[38rem] overflow-auto bg-[var(--inset)] p-5 text-sm leading-6 text-[var(--text)]">
            <code>
              {children}
            </code>
          </pre>
        )}
    </div>
  )
}

interface LoadingStateProps {
  message: string
}

function LoadingState({
  message,
}: LoadingStateProps) {
  return (
    <div className="flex min-h-40 items-center justify-center gap-3 p-6 text-sm text-[var(--muted)]">
      <LoaderCircle
        aria-hidden="true"
        className="size-5 animate-spin text-[var(--accent)]"
      />

      {message}
    </div>
  )
}

interface ErrorStateProps {
  error: unknown
  fallback: string
}

function ErrorState({
  error,
  fallback,
}: ErrorStateProps) {
  return (
    <div className="flex min-h-40 flex-col items-center justify-center p-6 text-center">
      <span className="grid size-10 place-items-center rounded-lg bg-red-500/10 text-[var(--red)]">
        <AlertCircle
          aria-hidden="true"
          className="size-5"
        />
      </span>

      <p className="mt-3 max-w-md text-sm leading-6 text-[var(--red)]">
        {getApiErrorMessage(
          error,
          fallback,
        )}
      </p>
    </div>
  )
}

interface EmptyStateProps {
  message: string
}

function EmptyState({
  message,
}: EmptyStateProps) {
  return (
    <div className="flex min-h-40 items-center justify-center p-6 text-center text-sm text-[var(--muted)]">
      {message}
    </div>
  )
}