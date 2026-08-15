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
  getRepositoryCommits,
  getRepositoryTree,
} from '../../services/repository-browser-service'
import type { Repository } from '../../types/repository'
import { repositoryQueryKeys } from './repository-query-keys'

interface RepositoryBrowserProps {
  repository: Repository
}

function formatDate(value: string | null): string {
  if (value === null) {
    return 'Unknown'
  }

  return new Intl.DateTimeFormat('en', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

function formatFileSize(value: number | null): string {
  if (value === null) {
    return '—'
  }

  if (value < 1024) {
    return `${value} B`
  }

  if (value < 1024 * 1024) {
    return `${(value / 1024).toFixed(1)} KB`
  }

  return `${(value / (1024 * 1024)).toFixed(1)} MB`
}

function shortHash(value: string): string {
  return value.slice(0, 7)
}

export function RepositoryBrowser({
  repository,
}: RepositoryBrowserProps) {
  const queryClient = useQueryClient()

  const [branch, setBranch] = useState(
    repository.default_branch ?? '',
  )

  const [currentPath, setCurrentPath] = useState('')
  const [selectedFile, setSelectedFile] =
    useState<string | null>(null)

  const branchesQuery = useQuery({
    queryKey: repositoryQueryKeys.branches(repository.id),
    queryFn: () => getRepositoryBranches(repository.id),
    enabled: !repository.is_empty,
    retry: false,
  })

  const treeQuery = useQuery({
    queryKey: repositoryQueryKeys.tree(
      repository.id,
      branch,
      currentPath,
    ),
    queryFn: () =>
      getRepositoryTree(repository.id, {
        branch,
        path: currentPath,
      }),
    enabled:
      !repository.is_empty
      && branch !== ''
      && selectedFile === null,
    retry: false,
  })

  const commitsQuery = useQuery({
    queryKey: repositoryQueryKeys.commits(
      repository.id,
      branch,
      10,
    ),
    queryFn: () =>
      getRepositoryCommits(repository.id, {
        branch,
        limit: 10,
      }),
    enabled:
      !repository.is_empty
      && branch !== '',
    retry: false,
  })

  const blobQuery = useQuery({
    queryKey: repositoryQueryKeys.blob(
      repository.id,
      branch,
      selectedFile ?? '',
    ),
    queryFn: () =>
      getRepositoryBlob(repository.id, {
        branch,
        path: selectedFile ?? '',
      }),
    enabled:
      !repository.is_empty
      && branch !== ''
      && selectedFile !== null,
    retry: false,
  })

  const pathSegments = currentPath === ''
    ? []
    : currentPath.split('/')

  function handleBranchChange(
    nextBranch: string,
  ): void {
    setBranch(nextBranch)
    setCurrentPath('')
    setSelectedFile(null)
  }

  function openDirectory(path: string): void {
    setCurrentPath(path)
    setSelectedFile(null)
  }

  function openFile(path: string): void {
    setSelectedFile(path)
  }

  function returnToDirectory(): void {
    setSelectedFile(null)
  }

  function openBreadcrumb(index: number): void {
    const nextPath = pathSegments
      .slice(0, index + 1)
      .join('/')

    openDirectory(nextPath)
  }

  function refreshBrowser(): void {
    void queryClient.invalidateQueries({
      queryKey: repositoryQueryKeys.browser(repository.id),
    })
  }

  if (repository.is_empty) {
    return null
  }

  return (
    <section className="overflow-hidden rounded-xl border border-white/10 bg-[#161b22]">
      <header className="flex flex-col gap-4 border-b border-white/10 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-3">
          <span className="grid size-9 place-items-center rounded-lg bg-blue-500/10 text-blue-300">
            <FileCode2
              aria-hidden="true"
              className="size-4"
            />
          </span>

          <div>
            <h2 className="font-semibold text-white">
              Repository browser
            </h2>

            <p className="mt-0.5 text-sm text-slate-500">
              Browse branches, files, and recent commits.
            </p>
          </div>
        </div>

        <button
          aria-label="Refresh repository browser"
          className="inline-flex size-9 items-center justify-center rounded-lg border border-white/10 text-slate-400 transition hover:bg-white/5 hover:text-white"
          onClick={refreshBrowser}
          type="button"
        >
          <RefreshCw
            aria-hidden="true"
            className="size-4"
          />
        </button>
      </header>

      <div className="grid lg:grid-cols-[minmax(0,1fr)_21rem]">
        <div className="min-w-0 border-b border-white/10 lg:border-b-0 lg:border-r">
          <div className="flex flex-col gap-3 border-b border-white/10 p-4 sm:flex-row sm:items-center">
            <label
              className="sr-only"
              htmlFor="repository-browser-branch"
            >
              Repository branch
            </label>

            <div className="relative shrink-0">
              <GitBranch
                aria-hidden="true"
                className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"
              />

              <select
                className="h-10 min-w-44 appearance-none rounded-lg border border-white/10 bg-[#0d1117] pl-9 pr-8 text-sm font-medium text-slate-300 outline-none transition focus:border-blue-500/60"
                disabled={branchesQuery.isPending}
                id="repository-browser-branch"
                onChange={(event) => {
                  handleBranchChange(event.target.value)
                }}
                value={branch}
              >
                {branchesQuery.data?.data.map(
                  (repositoryBranch) => (
                    <option
                      key={repositoryBranch.name}
                      value={repositoryBranch.name}
                    >
                      {repositoryBranch.name}
                    </option>
                  ),
                )}

                {branchesQuery.isPending && (
                  <option value={branch}>
                    Loading branches...
                  </option>
                )}
              </select>
            </div>

            <nav
              aria-label="Repository path"
              className="flex min-w-0 flex-1 items-center gap-1 overflow-x-auto text-sm"
            >
              <button
                aria-label="Repository root"
                className="inline-flex shrink-0 items-center gap-1 rounded-md px-2 py-1.5 text-slate-400 transition hover:bg-white/5 hover:text-white"
                onClick={() => {
                  openDirectory('')
                }}
                type="button"
              >
                <Home
                  aria-hidden="true"
                  className="size-4"
                />
              </button>

              {pathSegments.map((segment, index) => (
                <span
                  className="flex items-center gap-1"
                  key={`${segment}-${index}`}
                >
                  <ChevronRight
                    aria-hidden="true"
                    className="size-4 shrink-0 text-slate-600"
                  />

                  <button
                    className="max-w-48 truncate rounded-md px-2 py-1.5 text-slate-400 transition hover:bg-white/5 hover:text-white"
                    onClick={() => {
                      openBreadcrumb(index)
                    }}
                    title={segment}
                    type="button"
                  >
                    {segment}
                  </button>
                </span>
              ))}
            </nav>
          </div>

          {selectedFile !== null ? (
            <FileViewer
              error={blobQuery.error}
              filePath={selectedFile}
              isError={blobQuery.isError}
              isPending={blobQuery.isPending}
              onBack={returnToDirectory}
              size={blobQuery.data?.data.size ?? null}
            >
              {blobQuery.data?.data.content ?? ''}
            </FileViewer>
          ) : (
            <TreeViewer
              error={treeQuery.error}
              isError={treeQuery.isError}
              isPending={treeQuery.isPending}
              onOpenDirectory={openDirectory}
              onOpenFile={openFile}
              tree={treeQuery.data?.data ?? []}
            />
          )}
        </div>

        <aside>
          <header className="flex items-center gap-2 border-b border-white/10 px-5 py-4">
            <History
              aria-hidden="true"
              className="size-4 text-blue-300"
            />

            <h3 className="font-semibold text-white">
              Recent commits
            </h3>
          </header>

          <div className="max-h-[32rem] overflow-y-auto">
            {commitsQuery.isPending && (
              <LoadingState message="Loading commits..." />
            )}

            {commitsQuery.isError && (
              <ErrorState
                error={commitsQuery.error}
                fallback="Recent commits could not be loaded."
              />
            )}

            {commitsQuery.data?.data.length === 0 && (
              <EmptyState message="No commits found on this branch." />
            )}

            {commitsQuery.data?.data.map((commit) => (
              <article
                className="border-b border-white/10 px-5 py-4 last:border-b-0"
                key={commit.hash}
              >
                <div className="flex items-start gap-3">
                  <span className="mt-0.5 grid size-8 shrink-0 place-items-center rounded-lg bg-white/5 text-slate-400">
                    <GitCommitHorizontal
                      aria-hidden="true"
                      className="size-4"
                    />
                  </span>

                  <div className="min-w-0">
                    <h4
                      className="line-clamp-2 text-sm font-medium leading-5 text-slate-200"
                      title={commit.subject}
                    >
                      {commit.subject}
                    </h4>

                    <p className="mt-1 truncate text-xs text-slate-500">
                      {commit.author.name}
                    </p>

                    <div className="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                      <code className="rounded bg-black/20 px-1.5 py-0.5 font-mono text-blue-300">
                        {shortHash(commit.hash)}
                      </code>

                      <span>
                        {formatDate(commit.authored_at)}
                      </span>
                    </div>
                  </div>
                </div>
              </article>
            ))}
          </div>
        </aside>
      </div>
    </section>
  )
}

interface TreeViewerProps {
  tree: {
    name: string
    path: string
    type: 'directory' | 'file'
    size: number | null
    object_id: string
    mode: string
  }[]
  isPending: boolean
  isError: boolean
  error: unknown
  onOpenDirectory: (path: string) => void
  onOpenFile: (path: string) => void
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
        error={error}
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
      {tree.map((entry) => {
        const isDirectory = entry.type === 'directory'

        return (
          <button
            className="flex w-full items-center gap-3 px-5 py-3.5 text-left transition hover:bg-white/[0.03]"
            key={entry.object_id}
            onClick={() => {
              if (isDirectory) {
                onOpenDirectory(entry.path)
              } else {
                onOpenFile(entry.path)
              }
            }}
            type="button"
          >
            {isDirectory ? (
              <Folder
                aria-hidden="true"
                className="size-5 shrink-0 text-blue-400"
              />
            ) : (
              <FileCode2
                aria-hidden="true"
                className="size-5 shrink-0 text-slate-500"
              />
            )}

            <span className="min-w-0 flex-1 truncate text-sm font-medium text-slate-300">
              {entry.name}
            </span>

            <span className="shrink-0 text-xs text-slate-600">
              {isDirectory
                ? 'Directory'
                : formatFileSize(entry.size)}
            </span>

            <ChevronRight
              aria-hidden="true"
              className="size-4 shrink-0 text-slate-600"
            />
          </button>
        )
      })}
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
      <header className="flex items-center justify-between gap-4 border-b border-white/10 px-5 py-3">
        <div className="flex min-w-0 items-center gap-3">
          <button
            aria-label="Return to directory"
            className="inline-flex size-8 shrink-0 items-center justify-center rounded-lg border border-white/10 text-slate-400 transition hover:bg-white/5 hover:text-white"
            onClick={onBack}
            type="button"
          >
            <ArrowLeft
              aria-hidden="true"
              className="size-4"
            />
          </button>

          <div className="min-w-0">
            <p
              className="truncate text-sm font-medium text-slate-300"
              title={filePath}
            >
              {filePath}
            </p>

            <p className="mt-0.5 text-xs text-slate-600">
              {formatFileSize(size)}
            </p>
          </div>
        </div>
      </header>

      {isPending && (
        <LoadingState message="Loading file..." />
      )}

      {isError && (
        <ErrorState
          error={error}
          fallback="The repository file could not be loaded."
        />
      )}

      {!isPending && !isError && (
        <pre className="max-h-[38rem] overflow-auto bg-[#0d1117] p-5 text-sm leading-6 text-slate-300">
          <code>{children}</code>
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
    <div className="flex min-h-40 items-center justify-center gap-3 p-6 text-sm text-slate-500">
      <LoaderCircle
        aria-hidden="true"
        className="size-5 animate-spin text-blue-400"
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
      <span className="grid size-10 place-items-center rounded-lg bg-red-500/10 text-red-300">
        <AlertCircle
          aria-hidden="true"
          className="size-5"
        />
      </span>

      <p className="mt-3 max-w-md text-sm leading-6 text-red-200">
        {getApiErrorMessage(error, fallback)}
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
    <div className="flex min-h-40 items-center justify-center p-6 text-center text-sm text-slate-500">
      {message}
    </div>
  )
}