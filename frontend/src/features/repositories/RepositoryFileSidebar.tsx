import { useQuery } from '@tanstack/react-query'
import {
  ChevronRight,
  FileCode2,
  Folder,
  GitBranch,
  GitCommitHorizontal,
  LoaderCircle,
} from 'lucide-react'
import { getRepositoryTree } from '../../services/repository-browser-service'
import { getApiErrorMessage } from '../../lib/api-errors'
import type { RepositoryBranch } from '../../types/repository-browser'
import { repositoryQueryKeys } from './repository-query-keys'

interface Props {
  repositoryId: number
  branch: string
  branches: RepositoryBranch[]
  selectedFile: string | null
  currentPath: string
  expandedPaths: Set<string>
  onToggleDirectory: (path: string) => void
  onOpenFile: (path: string) => void
  onOpenDirectory: (path: string) => void
  onBranchChange: (branch: string) => void
}

export function RepositoryFileSidebar(props: Props) {
  return (
    <nav
      aria-label="Repository files"
      id="repository-file-sidebar"
      className="repository-file-sidebar min-w-0 border-b border-[var(--border)] bg-[var(--subtle)] lg:border-b-0 lg:border-r"
    >
      <div className="border-b border-[var(--border)] p-4">
        <h3 className="mb-3 text-sm font-semibold text-[var(--text)]">Files</h3>
        <label className="sr-only" htmlFor="repository-browser-branch">
          Repository branch
        </label>
        <div className="relative">
          <GitBranch
            aria-hidden="true"
            className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[var(--muted)]"
          />
          <select
            id="repository-browser-branch"
            className="h-10 w-full min-w-0 rounded-lg border border-[var(--border)] bg-[var(--inset)] pl-9 pr-2 text-sm text-[var(--text)] focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
            value={props.branch}
            onChange={(event) => props.onBranchChange(event.target.value)}
          >
            {props.branches.map((branch) => (
              <option key={branch.name} value={branch.name}>
                {branch.name}
              </option>
            ))}
          </select>
        </div>
        <button
          type="button"
          className="mt-3 w-full truncate rounded-md px-2 py-1.5 text-left text-xs font-medium text-[var(--accent)] hover:bg-[var(--inset)] focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
          onClick={() => props.onOpenDirectory('')}
        >
          Repository root
        </button>
      </div>
      <div className="max-h-72 overflow-auto p-2 lg:max-h-[42rem]">
        <DirectoryEntries {...props} path="" depth={0} />
      </div>
    </nav>
  )
}

function DirectoryEntries(props: Props & { path: string; depth: number }) {
  const query = useQuery({
    queryKey: repositoryQueryKeys.tree(
      props.repositoryId,
      props.branch,
      props.path,
    ),
    queryFn: () =>
      getRepositoryTree(props.repositoryId, {
        branch: props.branch,
        path: props.path,
      }),
    retry: false,
  })

  if (query.isPending)
    return (
      <p
        role="status"
        className="flex items-center gap-2 p-3 text-xs text-[var(--muted)]"
      >
        <LoaderCircle aria-hidden="true" className="size-3 animate-spin" />
        Loading {props.path || 'files'}…
      </p>
    )
  if (query.isError)
    return (
      <div className="p-3 text-xs">
        <p role="alert" className="break-words text-[var(--red)]">
          {getApiErrorMessage(
            query.error,
            `Could not load ${props.path || 'files'}.`,
          )}
        </p>
        <button
          type="button"
          className="mt-2 text-[var(--accent)] underline"
          onClick={() => void query.refetch()}
        >
          Retry loading {props.path || 'files'}
        </button>
      </div>
    )
  if (query.data.data.length === 0)
    return (
      <p className="px-3 py-2 text-xs text-[var(--muted)]">Empty directory</p>
    )

  const entries = [...query.data.data].sort(
    (a, b) =>
      Number(b.type === 'directory') - Number(a.type === 'directory') ||
      a.name.localeCompare(b.name),
  )
  return (
    <ul className="space-y-0.5">
      {entries.map((entry) => {
        const directory = entry.type === 'directory'
        const expanded = props.expandedPaths.has(entry.path)
        const active = directory
          ? props.selectedFile === null && props.currentPath === entry.path
          : props.selectedFile === entry.path
        const Icon = directory
          ? Folder
          : entry.type === 'submodule'
            ? GitCommitHorizontal
            : FileCode2
        return (
          <li key={entry.path}>
            <div
              className={`flex min-w-0 items-center rounded-md ${active ? 'bg-[var(--inset)] text-[var(--accent)] ring-1 ring-inset ring-[var(--border)]' : 'text-[var(--text)] hover:bg-[var(--inset)]'}`}
              style={{ paddingLeft: Math.min(props.depth, 12) * 12 }}
            >
              {directory ? (
                <button
                  type="button"
                  aria-label={`${expanded ? 'Collapse' : 'Expand'} folder ${entry.path}`}
                  aria-expanded={expanded}
                  className="grid size-7 shrink-0 place-items-center rounded focus-visible:outline-2 focus-visible:outline-[var(--accent)]"
                  onClick={() => props.onToggleDirectory(entry.path)}
                >
                  <ChevronRight
                    aria-hidden="true"
                    className={`size-3.5 transition-transform motion-reduce:transition-none ${expanded ? 'rotate-90' : ''}`}
                  />
                </button>
              ) : (
                <span className="w-7 shrink-0" />
              )}
              <button
                type="button"
                title={entry.path}
                aria-label={`${directory ? 'Open folder' : entry.type === 'submodule' ? 'Submodule' : 'Open file'} ${entry.path}`}
                aria-current={active ? 'page' : undefined}
                disabled={entry.type === 'submodule'}
                className="flex min-w-0 flex-1 items-center gap-2 rounded py-2 pr-2 text-left text-sm focus-visible:outline-2 focus-visible:outline-[var(--accent)] disabled:cursor-default disabled:opacity-60"
                onClick={() =>
                  directory
                    ? props.onOpenDirectory(entry.path)
                    : props.onOpenFile(entry.path)
                }
              >
                <Icon
                  aria-hidden="true"
                  className={`size-4 shrink-0 ${directory ? 'text-[var(--accent)]' : 'text-[var(--muted)]'}`}
                />
                <span className="truncate">{entry.name}</span>
              </button>
            </div>
            {directory && expanded && (
              <DirectoryEntries
                {...props}
                path={entry.path}
                depth={props.depth + 1}
              />
            )}
          </li>
        )
      })}
    </ul>
  )
}
