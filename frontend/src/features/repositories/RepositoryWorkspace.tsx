import { lazy, Suspense, useState, type ReactNode } from 'react'
import { useQuery } from '@tanstack/react-query'
import { useSearchParams } from 'react-router-dom'
import {
  Code2,
  GitCommitHorizontal,
  Tag,
  Settings,
  ArrowLeft,
  RefreshCw,
  GitBranch,
  GitPullRequest,
  Play,
} from 'lucide-react'
import { apiClient } from '../../lib/api'
import { getApiErrorMessage } from '../../lib/api-errors'
import { getGitCloneUrl } from '../../lib/git-url'
import {
  getRepositoryBranches,
  getRepositoryCommits,
  getRepositoryCommit,
} from '../../services/repository-browser-service'
import type { Repository } from '../../types/repository'
import { RepositoryBrowser } from './RepositoryBrowser'
import { RepositorySettingsForm } from './RepositorySettingsForm'
const RepositoryBranches = lazy(() =>
  import('./RepositoryCollaboration').then((module) => ({
    default: module.RepositoryBranches,
  })),
)
const RepositoryPullRequests = lazy(() =>
  import('./RepositoryCollaboration').then((module) => ({
    default: module.RepositoryPullRequests,
  })),
)
const RepositoryActions = lazy(() =>
  import('./RepositoryCollaboration').then((module) => ({
    default: module.RepositoryActions,
  })),
)

type RepositoryTag = {
  name: string
  object_id: string
  commit: string | null
  annotated: boolean
  created_at: string | null
  subject: string
}
type TagsResponse = { data: RepositoryTag[]; page: number; has_more: boolean }
function date(value: string | null) {
  return value ? new Date(value).toLocaleString() : 'Unknown date'
}

export function RepositoryWorkspace({
  repository,
  isOwner,
  onUpdated,
  children,
  canContribute = isOwner,
}: {
  repository: Repository
  isOwner: boolean
  canContribute?: boolean
  children?: ReactNode
  onUpdated: (repository: Repository) => void
}) {
  const [params, setParams] = useSearchParams()
  const selected = params.get('tab') ?? 'code'
  const tab = [
    'code',
    'commits',
    'tags',
    'branches',
    'pulls',
    'actions',
    ...(isOwner ? ['settings'] : []),
  ].includes(selected)
    ? selected
    : 'code'
  const tabs = [
    { id: 'code', name: 'Code', icon: Code2 },
    { id: 'branches', name: 'Branches', icon: GitBranch },
    { id: 'pulls', name: 'Pull requests', icon: GitPullRequest },
    { id: 'actions', name: 'Actions', icon: Play },
    { id: 'commits', name: 'Commits', icon: GitCommitHorizontal },
    { id: 'tags', name: 'Tags', icon: Tag },
    ...(isOwner ? [{ id: 'settings', name: 'Settings', icon: Settings }] : []),
  ]
  return (
    <>
      <nav
        aria-label="Repository navigation"
        className="mt-6 flex gap-1 overflow-x-auto border-b border-[var(--border)]"
      >
        {tabs.map(({ id, name, icon: Icon }) => (
          <button
            key={id}
            type="button"
            aria-current={tab === id ? 'page' : undefined}
            className={`inline-flex min-h-12 shrink-0 items-center gap-2 border-b-2 px-4 text-sm font-medium transition ${tab === id ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-[var(--muted)] hover:bg-[var(--subtle)]'}`}
            onClick={() =>
              setParams((previous) => {
                const next = new URLSearchParams(previous)
                next.set('tab', id)
                return next
              })
            }
          >
            <Icon size={16} aria-hidden="true" />
            {name}
          </button>
        ))}
      </nav>
      {(tab === 'code' || tab === 'settings') && children}
      {tab === 'code' && (
        <>
          <RepositoryBrowser
            key={params.get('branch') ?? ''}
            repository={repository}
            initialBranch={params.get('branch') ?? ''}
          />
          {!isOwner && (
            <section className="mt-6 rounded-xl border border-[var(--border)] glass-panel p-6">
              <h2 className="font-semibold">Read-only web access</h2>
              <p className="mt-2 text-sm text-[var(--muted)]">
                Only the repository owner can rename this repository, change
                visibility, or push changes.
              </p>
            </section>
          )}
        </>
      )}
      {tab === 'commits' && (
        <RepositoryHistory key={repository.id} repository={repository} />
      )}
      {tab === 'tags' && (
        <RepositoryTags key={repository.id} repository={repository} />
      )}
      <Suspense
        fallback={
          <p role="status" className="glass-panel rounded-xl p-6">
            Loading repository tools…
          </p>
        }
      >
        {tab === 'branches' && (
          <RepositoryBranches
            repository={repository}
            isOwner={isOwner}
            onBrowse={(branch) =>
              setParams((previous) => {
                const next = new URLSearchParams(previous)
                next.set('tab', 'code')
                next.set('branch', branch)
                return next
              })
            }
          />
        )}
        {tab === 'pulls' && (
          <RepositoryPullRequests
            repository={repository}
            canContribute={canContribute}
          />
        )}
        {tab === 'actions' && (
          <RepositoryActions repository={repository} isOwner={isOwner} />
        )}
      </Suspense>
      {tab === 'settings' && isOwner && (
        <RepositorySettingsForm repository={repository} onUpdated={onUpdated} />
      )}
    </>
  )
}

function CommitPreview({
  repositoryId,
  hash,
  onBack,
}: {
  repositoryId: number
  hash: string
  onBack: () => void
}) {
  const query = useQuery({
    queryKey: ['repository', repositoryId, 'history-commit', hash],
    queryFn: () => getRepositoryCommit(repositoryId, hash),
    retry: false,
  })
  return (
    <section className="glass-panel rounded-xl border border-[var(--border)] p-6">
      <button
        className="mb-5 inline-flex items-center gap-2 text-sm text-[var(--accent)]"
        onClick={onBack}
        type="button"
      >
        <ArrowLeft size={16} aria-hidden="true" />
        Back to list
      </button>
      <h2 className="text-lg font-semibold">Commit details</h2>
      <p className="mt-2 break-all font-mono text-xs text-[var(--muted)]">
        {hash}
      </p>
      {query.isPending && (
        <p role="status" className="py-8">
          Loading commit…
        </p>
      )}
      {query.isError && (
        <p role="alert" className="py-8 text-[var(--red)]">
          {getApiErrorMessage(query.error, 'Unable to load this commit.')}
        </p>
      )}
      {query.data && (
        <div className="mt-6 space-y-5">
          <p className="whitespace-pre-wrap break-words">
            {query.data.commit.message}
          </p>
          <p className="text-sm text-[var(--muted)]">
            {query.data.commit.author_name} ·{' '}
            {date(query.data.commit.authored_at)}
          </p>
          <p className="text-xs text-[var(--muted)]">
            Parents:{' '}
            {query.data.commit.parents.length
              ? query.data.commit.parents.join(', ')
              : 'Initial commit'}
          </p>
        </div>
      )}
    </section>
  )
}

function RepositoryHistory({ repository }: { repository: Repository }) {
  const [branch, setBranch] = useState('')
  const [page, setPage] = useState(1)
  const [commit, setCommit] = useState<string | null>(null)
  const branches = useQuery({
    queryKey: ['repository', repository.id, 'history-branches'],
    queryFn: () => getRepositoryBranches(repository.id),
    retry: false,
  })
  const selectedBranch =
    branch ||
    branches.data?.default_branch ||
    branches.data?.data[0]?.name ||
    ''
  const query = useQuery({
    queryKey: ['repository', repository.id, 'history', selectedBranch, page],
    queryFn: () =>
      getRepositoryCommits(repository.id, {
        branch: selectedBranch,
        limit: 30,
        page,
      }),
    enabled: Boolean(selectedBranch),
    retry: false,
  })
  if (commit)
    return (
      <CommitPreview
        repositoryId={repository.id}
        hash={commit}
        onBack={() => setCommit(null)}
      />
    )
  return (
    <section className="glass-panel overflow-hidden rounded-xl border border-[var(--border)]">
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-[var(--border)] p-5">
        <div>
          <h2 className="font-semibold">Commit history</h2>
          <p className="mt-1 text-sm text-[var(--muted)]">
            Explore the history of your selected branch.
          </p>
        </div>
        <label className="flex items-center gap-2 text-sm">
          <GitBranch size={16} aria-hidden="true" />
          <span className="sr-only">History branch</span>
          <select
            className="rounded-lg border border-[var(--border)] bg-[var(--inset)] px-3 py-2"
            value={selectedBranch}
            onChange={(event) => {
              setBranch(event.target.value)
              setPage(1)
            }}
          >
            {branches.data?.data.map((item) => (
              <option key={item.name} value={item.name}>
                {item.name}
              </option>
            ))}
          </select>
        </label>
      </div>
      {(branches.isPending || (selectedBranch && query.isPending)) && (
        <p role="status" className="p-8 text-sm">
          Loading history…
        </p>
      )}
      {(branches.isError || query.isError) && (
        <div role="alert" className="p-8 text-sm text-[var(--red)]">
          {getApiErrorMessage(
            branches.error || query.error,
            'Unable to load commit history.',
          )}
          <button
            type="button"
            className="ml-3 underline"
            onClick={() => {
              void branches.refetch()
              void query.refetch()
            }}
          >
            Try again
          </button>
        </div>
      )}
      {branches.isSuccess && !selectedBranch && (
        <p className="p-8 text-sm text-[var(--muted)]">
          No commits yet. Push your first branch to see its history.
        </p>
      )}
      {query.data?.data.map((item) => (
        <button
          key={item.hash}
          type="button"
          onClick={() => setCommit(item.hash)}
          className="flex w-full items-start gap-3 border-b border-[var(--border)] p-5 text-left transition hover:bg-[var(--subtle)]"
        >
          <GitCommitHorizontal
            size={19}
            className="mt-1 shrink-0 text-[var(--accent)]"
            aria-hidden="true"
          />
          <div className="min-w-0 flex-1">
            <h3 className="break-words text-sm font-semibold">
              {item.subject}
            </h3>
            <p className="mt-1 text-xs text-[var(--muted)]">
              {item.author.name} · {date(item.authored_at)}
            </p>
          </div>
          <code className="text-xs text-[var(--accent)]">
            {item.hash.slice(0, 7)}
          </code>
        </button>
      ))}
      {query.isSuccess && query.data.data.length === 0 && selectedBranch && (
        <p className="p-8 text-sm text-[var(--muted)]">
          No more commits on this branch.
        </p>
      )}
      {selectedBranch && (
        <Pagination
          page={page}
          previous={() => setPage(page - 1)}
          next={() => setPage(page + 1)}
          canNext={query.data?.has_more ?? false}
          pending={query.isPending}
        />
      )}
    </section>
  )
}

function RepositoryTags({ repository }: { repository: Repository }) {
  const [page, setPage] = useState(1)
  const [commit, setCommit] = useState<string | null>(null)
  const query = useQuery({
    queryKey: ['repository', repository.id, 'tags', page],
    queryFn: async () =>
      (
        await apiClient.get<TagsResponse>(
          `/repositories/${repository.id}/tags`,
          { params: { page, limit: 30 } },
        )
      ).data,
    retry: false,
  })
  if (commit)
    return (
      <CommitPreview
        repositoryId={repository.id}
        hash={commit}
        onBack={() => setCommit(null)}
      />
    )
  return (
    <section className="glass-panel overflow-hidden rounded-xl border border-[var(--border)]">
      <div className="flex items-center justify-between border-b border-[var(--border)] p-5">
        <div>
          <h2 className="font-semibold">Repository tags</h2>
          <p className="mt-1 text-sm text-[var(--muted)]">
            Named snapshots pushed to this repository.
          </p>
        </div>
        <button
          aria-label="Refresh tags"
          className="rounded-lg border border-[var(--border)] p-2"
          type="button"
          onClick={() => void query.refetch()}
        >
          <RefreshCw size={16} aria-hidden="true" />
        </button>
      </div>
      {query.isPending && (
        <p role="status" className="p-8 text-sm">
          Loading tags…
        </p>
      )}
      {query.isError && (
        <p role="alert" className="p-8 text-sm text-[var(--red)]">
          {getApiErrorMessage(query.error, 'Unable to load repository tags.')}
        </p>
      )}
      {query.data?.data.map((tag) => (
        <article
          key={tag.name}
          className="flex items-start gap-3 border-b border-[var(--border)] p-5"
        >
          <Tag
            size={19}
            className="mt-1 shrink-0 text-[var(--accent)]"
            aria-hidden="true"
          />
          <div className="min-w-0 flex-1">
            <h3 className="break-all font-semibold">{tag.name}</h3>
            <p className="mt-1 break-words text-sm text-[var(--muted)]">
              {tag.subject}
            </p>
            <p className="mt-2 text-xs text-[var(--muted)]">
              {tag.annotated ? 'Annotated' : 'Lightweight'} ·{' '}
              {date(tag.created_at)}
            </p>
          </div>
          {tag.commit && (
            <button
              className="text-xs text-[var(--accent)] underline"
              type="button"
              aria-label={`View tagged commit ${tag.name}`}
              onClick={() => setCommit(tag.commit)}
            >
              {tag.commit.slice(0, 7)}
            </button>
          )}
        </article>
      ))}
      {query.isSuccess && query.data.data.length === 0 && (
        <div className="p-8">
          <h3 className="font-semibold">No tags yet</h3>
          <p className="mt-2 text-sm text-[var(--muted)]">
            Create a tag in your local clone and push it to GitForge.
          </p>
          <pre className="mt-4 overflow-x-auto rounded-lg bg-[var(--inset)] p-4 text-xs">
            git tag -a v1.0.0 -m "First release"{'\n'}git push gitforge v1.0.0
          </pre>
          <p className="mt-3 break-all text-xs text-[var(--muted)]">
            Remote: {getGitCloneUrl(repository.full_name)}
          </p>
        </div>
      )}
      <Pagination
        page={page}
        previous={() => setPage(page - 1)}
        next={() => setPage(page + 1)}
        canNext={query.data?.has_more ?? false}
        pending={query.isPending}
      />
    </section>
  )
}
function Pagination({
  page,
  previous,
  next,
  canNext,
  pending,
}: {
  page: number
  previous: () => void
  next: () => void
  canNext: boolean
  pending: boolean
}) {
  return (
    <div className="flex items-center justify-between gap-3 p-5 text-sm">
      <span className="text-[var(--muted)]">Page {page}</span>
      <div className="flex gap-2">
        <button
          type="button"
          disabled={page === 1 || pending}
          onClick={previous}
          className="rounded-lg border border-[var(--border)] px-3 py-2 disabled:opacity-40"
        >
          Previous
        </button>
        <button
          type="button"
          disabled={!canNext || pending || page === 1000}
          onClick={next}
          className="rounded-lg border border-[var(--border)] px-3 py-2 disabled:opacity-40"
        >
          Next
        </button>
      </div>
    </div>
  )
}
