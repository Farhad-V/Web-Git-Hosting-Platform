import { useState, type FormEvent, type ReactNode } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  ArrowLeft,
  GitBranch,
  GitPullRequest,
  Play,
  RefreshCw,
} from 'lucide-react'
import { apiClient } from '../../lib/api'
import { getApiErrorMessage, getValidationErrors } from '../../lib/api-errors'
import { getRepositoryBranches } from '../../services/repository-browser-service'
import type { Repository } from '../../types/repository'
import { repositoryQueryKeys } from './repository-query-keys'

const button =
  'inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-[var(--border)] px-3 py-2 text-sm font-medium transition hover:bg-[var(--subtle)] disabled:cursor-not-allowed disabled:opacity-40'
const primary = button + ' bg-blue-600 text-white hover:bg-blue-500'
const inputClass =
  'w-full rounded-lg border border-[var(--border)] bg-[var(--inset)] px-3 py-2.5 text-sm'
type Page<T> = { data: T[]; current_page: number; next_page_url: string | null }
type Author = { username: string; name: string }
type Pull = {
  id: number
  number: number
  title: string
  description: string | null
  base_branch: string
  head_branch: string
  status: 'open' | 'closed' | 'merging' | 'merged'
  author: Author
  created_at: string
  merge_commit: string | null
}
type Comparison = {
  base_commit: string
  head_commit: string
  commit_count: number
  diff: string
  diff_truncated: boolean
}
type PullDetail = {
  data: Pull
  comparison: Comparison | null
  comparison_error: string | null
  can_manage: boolean
  can_merge: boolean
}
type Comment = { id: number; body: string; author: Author; created_at: string }
type Run = {
  id: number
  branch: string
  commit: string
  status: string
  created_at: string
  started_at: string | null
  finished_at: string | null
  log: string | null
  steps: { name: string; status: string }[] | null
}
const active = (status: string) => ['queued', 'in_progress'].includes(status)
const time = (value: string) => new Date(value).toLocaleString()
function errorText(error: unknown) {
  return (
    Object.values(getValidationErrors(error)).flat()[0] ??
    getApiErrorMessage(error, 'Something went wrong. Please try again.')
  )
}
function ErrorMessage({ error }: { error: unknown }) {
  return error ? (
    <p
      role="alert"
      className="rounded-lg border border-red-400/20 bg-red-500/10 p-4 text-sm text-[var(--red)]"
    >
      {errorText(error)}
    </p>
  ) : null
}
function Panel({
  title,
  description,
  tools,
  children,
}: {
  title: string
  description: string
  tools?: ReactNode
  children: ReactNode
}) {
  return (
    <section className="glass-panel overflow-hidden rounded-xl border border-[var(--border)]">
      <header className="flex flex-wrap items-center justify-between gap-4 border-b border-[var(--border)] p-5">
        <div>
          <h2 className="font-semibold">{title}</h2>
          <p className="mt-1 text-sm text-[var(--muted)]">{description}</p>
        </div>
        {tools}
      </header>
      <div className="space-y-5 p-5">{children}</div>
    </section>
  )
}
function Pager({
  page,
  setPage,
  hasMore,
  pending,
}: {
  page: number
  setPage: (page: number) => void
  hasMore: boolean
  pending: boolean
}) {
  return (
    <div className="flex items-center justify-between gap-3">
      <span className="text-sm text-[var(--muted)]">Page {page}</span>
      <div className="flex gap-2">
        <button
          className={button}
          disabled={page === 1 || pending}
          onClick={() => setPage(page - 1)}
        >
          Previous
        </button>
        <button
          className={button}
          disabled={!hasMore || pending || page === 1000}
          onClick={() => setPage(page + 1)}
        >
          Next
        </button>
      </div>
    </div>
  )
}
function Status({ value }: { value: string }) {
  const color = ['success', 'merged'].includes(value)
    ? 'text-[var(--emerald)] bg-emerald-500/10'
    : ['failure', 'closed', 'cancelled'].includes(value)
      ? 'text-[var(--red)] bg-red-500/10'
      : 'text-[var(--accent)] bg-blue-500/10'
  return (
    <span
      className={
        'inline-flex shrink-0 rounded-full px-2.5 py-1 text-xs font-medium ' +
        color
      }
    >
      {value.replaceAll('_', ' ')}
    </span>
  )
}
function useBranches(repository: Repository) {
  return useQuery({
    queryKey: ['repository', repository.id, 'workflow-branches'],
    queryFn: () => getRepositoryBranches(repository.id),
    retry: false,
  })
}
function useRefreshRepository(repository: Repository) {
  const client = useQueryClient()
  return async () => {
    await Promise.all([
      client.invalidateQueries({ queryKey: ['repository', repository.id] }),
      client.invalidateQueries({
        queryKey: repositoryQueryKeys.browser(repository.id),
      }),
    ])
  }
}

export function RepositoryBranches({
  repository,
  isOwner,
  onBrowse,
}: {
  repository: Repository
  isOwner: boolean
  onBrowse: (branch: string) => void
}) {
  const branches = useBranches(repository),
    refresh = useRefreshRepository(repository)
  const [search, setSearch] = useState(''),
    [name, setName] = useState(''),
    [source, setSource] = useState(''),
    [creating, setCreating] = useState(false)
  const selectedSource =
    source ||
    branches.data?.default_branch ||
    branches.data?.data[0]?.name ||
    ''
  const create = useMutation({
    mutationFn: () =>
      apiClient.post('/repositories/' + repository.id + '/branches', {
        name,
        source: selectedSource,
      }),
    onSuccess: async () => {
      setName('')
      setCreating(false)
      await refresh()
    },
  })
  const setDefault = useMutation({
    mutationFn: (branch: string) =>
      apiClient.patch('/repositories/' + repository.id + '/default-branch', {
        name: branch,
      }),
    onSuccess: refresh,
  })
  async function submit(event: FormEvent) {
    event.preventDefault()
    create.mutate()
  }
  return (
    <Panel
      title="Branches"
      description="Create a workspace for your next change, then push commits from your clone."
      tools={
        <div className="flex gap-2">
          <button
            className={button}
            aria-label="Refresh branches"
            onClick={() => void branches.refetch()}
          >
            <RefreshCw size={16} />
          </button>
          {isOwner && (
            <button
              className={primary}
              disabled={!selectedSource}
              onClick={() => setCreating(!creating)}
            >
              <GitBranch size={16} />
              New branch
            </button>
          )}
        </div>
      }
    >
      <label className="block">
        <span className="sr-only">Find a branch</span>
        <input
          className={inputClass}
          placeholder="Find a branch…"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
        />
      </label>
      <ErrorMessage
        error={branches.error || create.error || setDefault.error}
      />
      {branches.isPending && <p role="status">Loading branches…</p>}
      {isOwner && creating && (
        <form
          className="space-y-4 rounded-lg border border-[var(--border)] bg-[var(--inset)] p-5"
          onSubmit={(event) => void submit(event)}
        >
          <h3 className="font-semibold">Create branch</h3>
          <label className="block text-sm">
            Branch name
            <input
              className={'mt-2 ' + inputClass}
              required
              maxLength={255}
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="feature/my-change"
            />
          </label>
          <label className="block text-sm">
            Create from
            <select
              className={'mt-2 ' + inputClass}
              value={selectedSource}
              onChange={(e) => setSource(e.target.value)}
            >
              {branches.data?.data.map((branch) => (
                <option key={branch.name}>{branch.name}</option>
              ))}
            </select>
          </label>
          <button className={primary} disabled={create.isPending}>
            {create.isPending ? 'Creating…' : 'Create branch'}
          </button>
        </form>
      )}
      {branches.data?.data
        .filter((branch) =>
          branch.name.toLowerCase().includes(search.toLowerCase()),
        )
        .map((branch) => (
          <article
            key={branch.name}
            className="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-[var(--border)] p-4"
          >
            <div className="min-w-0">
              <div className="flex flex-wrap items-center gap-2">
                <GitBranch className="text-[var(--accent)]" size={17} />
                <h3 className="break-all font-medium">{branch.name}</h3>
                {branch.name === branches.data?.default_branch && (
                  <span className="rounded-full bg-blue-500/10 px-2 py-1 text-xs text-[var(--accent)]">
                    Default
                  </span>
                )}
              </div>
              <p className="mt-2 break-words text-sm text-[var(--muted)]">
                {branch.subject}
              </p>
              <p className="mt-1 text-xs text-[var(--muted)]">
                {branch.commit.slice(0, 7)}
                {branch.committed_at ? ' · ' + time(branch.committed_at) : ''}
              </p>
            </div>
            <div className="flex gap-2">
              <button
                className={button}
                aria-label={`View code on ${branch.name}`}
                onClick={() => onBrowse(branch.name)}
              >
                View code<span className="sr-only"> on {branch.name}</span>
              </button>
              {isOwner && branch.name !== branches.data?.default_branch && (
                <button
                  className={button}
                  disabled={setDefault.isPending}
                  onClick={() => setDefault.mutate(branch.name)}
                >
                  Make default<span className="sr-only">: {branch.name}</span>
                </button>
              )}
            </div>
          </article>
        ))}
      {branches.isSuccess && branches.data.data.length === 0 && (
        <p className="py-8 text-center text-sm text-[var(--muted)]">
          No branches yet. Push the first branch from your local clone to get
          started.
        </p>
      )}
      {branches.isSuccess &&
        branches.data.data.length > 0 &&
        !branches.data.data.some((branch) =>
          branch.name.toLowerCase().includes(search.toLowerCase()),
        ) && (
          <p className="text-sm text-[var(--muted)]">
            No branches match your search.
          </p>
        )}
    </Panel>
  )
}

export function RepositoryPullRequests({
  repository,
  canContribute,
}: {
  repository: Repository
  canContribute: boolean
}) {
  const [selected, setSelected] = useState<number | null>(null),
    [status, setStatus] = useState('open'),
    [page, setPage] = useState(1),
    [creating, setCreating] = useState(false)
  const [title, setTitle] = useState(''),
    [description, setDescription] = useState(''),
    [base, setBase] = useState(''),
    [head, setHead] = useState('')
  const branches = useBranches(repository),
    refresh = useRefreshRepository(repository)
  const selectedBase =
    base || branches.data?.default_branch || branches.data?.data[0]?.name || ''
  const selectedHead =
    head ||
    branches.data?.data.find((branch) => branch.name !== selectedBase)?.name ||
    ''
  const query = useQuery({
    queryKey: ['repository', repository.id, 'pulls', status, page],
    queryFn: async () =>
      (
        await apiClient.get<Page<Pull>>(
          '/repositories/' + repository.id + '/pulls',
          { params: { status, page } },
        )
      ).data,
    retry: false,
  })
  const create = useMutation({
    mutationFn: () =>
      apiClient.post<{ data: Pull }>(
        '/repositories/' + repository.id + '/pulls',
        {
          title,
          description,
          base_branch: selectedBase,
          head_branch: selectedHead,
        },
      ),
    onSuccess: async (response) => {
      setCreating(false)
      setSelected(response.data.data.id)
      setTitle('')
      setDescription('')
      await refresh()
    },
  })
  if (selected !== null)
    return (
      <PullRequestDetail
        repository={repository}
        pullId={selected}
        canContribute={canContribute}
        onBack={() => {
          setSelected(null)
          void query.refetch()
        }}
      />
    )
  return (
    <Panel
      title="Pull requests"
      description="Propose, discuss, and merge changes between branches in this repository."
      tools={
        <div className="flex gap-2">
          <button
            className={button}
            aria-label="Refresh pull requests"
            onClick={() => void query.refetch()}
          >
            <RefreshCw size={16} />
          </button>
          {canContribute && (
            <button
              className={primary}
              disabled={!selectedHead}
              onClick={() => setCreating(!creating)}
            >
              <GitPullRequest size={16} />
              New pull request
            </button>
          )}
        </div>
      }
    >
      <div className="flex flex-wrap items-center gap-3">
        <label className="text-sm">
          Filter pull requests
          <select
            className={'ml-3 rounded-lg ' + inputClass.replace('w-full', '')}
            value={status}
            onChange={(e) => {
              setStatus(e.target.value)
              setPage(1)
            }}
          >
            <option value="open">Open</option>
            <option value="closed">Closed</option>
            <option value="merged">Merged</option>
            <option value="all">All</option>
          </select>
        </label>
      </div>
      <ErrorMessage error={query.error || create.error} />
      {canContribute && creating && (
        <form
          className="space-y-4 rounded-lg border border-[var(--border)] bg-[var(--inset)] p-5"
          onSubmit={(e) => {
            e.preventDefault()
            create.mutate()
          }}
        >
          <h3 className="font-semibold">Open a pull request</h3>
          <div className="grid gap-4 sm:grid-cols-2">
            <label className="text-sm">
              Base branch
              <select
                className={'mt-2 ' + inputClass}
                value={selectedBase}
                onChange={(e) => {
                  setBase(e.target.value)
                  setHead('')
                }}
              >
                {branches.data?.data.map((branch) => (
                  <option key={branch.name}>{branch.name}</option>
                ))}
              </select>
            </label>
            <label className="text-sm">
              Compare branch
              <select
                className={'mt-2 ' + inputClass}
                value={selectedHead}
                onChange={(e) => setHead(e.target.value)}
              >
                {branches.data?.data
                  .filter((branch) => branch.name !== selectedBase)
                  .map((branch) => (
                    <option key={branch.name}>{branch.name}</option>
                  ))}
              </select>
            </label>
          </div>
          <label className="block text-sm">
            Pull request title
            <input
              className={'mt-2 ' + inputClass}
              required
              maxLength={200}
              value={title}
              onChange={(e) => setTitle(e.target.value)}
            />
          </label>
          <label className="block text-sm">
            Description
            <textarea
              className={'mt-2 min-h-32 ' + inputClass}
              maxLength={20000}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              placeholder="What changed, and why?"
            />
          </label>
          <button
            className={primary}
            disabled={create.isPending || !selectedHead}
          >
            {create.isPending ? 'Opening…' : 'Open pull request'}
          </button>
        </form>
      )}
      {query.isPending && <p role="status">Loading pull requests…</p>}
      {query.data?.data.map((pull) => (
        <button
          key={pull.id}
          className="flex w-full items-start gap-3 rounded-lg border border-[var(--border)] p-4 text-left transition hover:bg-[var(--subtle)]"
          onClick={() => setSelected(pull.id)}
        >
          <GitPullRequest
            size={19}
            className="mt-1 shrink-0 text-[var(--accent)]"
          />
          <div className="min-w-0 flex-1">
            <h3 className="break-words font-semibold">{pull.title}</h3>
            <p className="mt-2 text-xs text-[var(--muted)]">
              #{pull.number} · {pull.author.username} · {time(pull.created_at)}
            </p>
            <p className="mt-1 break-all text-xs text-[var(--muted)]">
              {pull.head_branch} → {pull.base_branch}
            </p>
          </div>
          <Status value={pull.status} />
        </button>
      ))}
      {query.isSuccess && query.data.data.length === 0 && (
        <div className="py-8 text-center">
          <h3 className="font-semibold">
            No {status === 'all' ? '' : status + ' '}pull requests
          </h3>
          <p className="mt-2 text-sm text-[var(--muted)]">
            Push a change to a separate branch, then open a pull request to
            review it.
          </p>
          {!canContribute && (
            <p className="mt-2 text-sm text-[var(--muted)]">
              Sign in to open a pull request or join a discussion.
            </p>
          )}
        </div>
      )}
      <Pager
        page={page}
        setPage={setPage}
        hasMore={Boolean(query.data?.next_page_url)}
        pending={query.isPending}
      />
    </Panel>
  )
}

function PullRequestDetail({
  repository,
  pullId,
  canContribute,
  onBack,
}: {
  repository: Repository
  pullId: number
  canContribute: boolean
  onBack: () => void
}) {
  const [page, setPage] = useState(1),
    [body, setBody] = useState(''),
    [confirmMerge, setConfirmMerge] = useState(false)
  const refresh = useRefreshRepository(repository),
    path = '/repositories/' + repository.id + '/pulls/' + pullId
  const query = useQuery({
    queryKey: ['repository', repository.id, 'pull', pullId],
    queryFn: async () =>
      (await apiClient.get<PullDetail>(path, { timeout: 60000 })).data,
    retry: false,
  })
  const comments = useQuery({
    queryKey: ['repository', repository.id, 'pull-comments', pullId, page],
    queryFn: async () =>
      (
        await apiClient.get<Page<Comment>>(path + '/comments', {
          params: { page },
        })
      ).data,
    retry: false,
  })
  const comment = useMutation({
    mutationFn: () => apiClient.post(path + '/comments', { body }),
    onSuccess: async () => {
      setBody('')
      await comments.refetch()
    },
  })
  const change = useMutation({
    mutationFn: (status: string) => apiClient.patch(path, { status }),
    onSuccess: refresh,
  })
  const merge = useMutation({
    mutationFn: () =>
      apiClient.post(
        path + '/merge',
        {
          expected_base: query.data?.comparison?.base_commit,
          expected_head: query.data?.comparison?.head_commit,
        },
        { timeout: 60000 },
      ),
    onSuccess: async () => {
      setConfirmMerge(false)
      await refresh()
    },
  })
  const pull = query.data?.data,
    comparison = query.data?.comparison
  return (
    <Panel
      title={pull ? '#' + pull.number + ' ' + pull.title : 'Pull request'}
      description={
        pull
          ? pull.head_branch + ' → ' + pull.base_branch
          : 'Loading the proposed change…'
      }
      tools={
        <div className="flex gap-2">
          <button className={button} onClick={onBack}>
            <ArrowLeft size={16} />
            Back to pull requests
          </button>
          <button
            className={button}
            aria-label="Refresh comparison"
            onClick={() => {
              setConfirmMerge(false)
              void query.refetch()
            }}
          >
            <RefreshCw size={16} />
          </button>
        </div>
      }
    >
      <ErrorMessage
        error={
          query.error ||
          comments.error ||
          comment.error ||
          change.error ||
          merge.error
        }
      />
      {query.isPending && <p role="status">Loading comparison…</p>}
      {pull && (
        <>
          <div className="flex flex-wrap items-center gap-3">
            <Status value={pull.status} />
            <span className="text-sm text-[var(--muted)]">
              Opened by {pull.author.username} · {time(pull.created_at)}
            </span>
          </div>
          <p className="whitespace-pre-wrap break-words text-sm">
            {pull.description || 'No description provided.'}
          </p>
          {query.data?.comparison_error && (
            <p role="alert" className="text-sm text-[var(--red)]">
              {query.data.comparison_error}
            </p>
          )}
          {comparison && (
            <details open>
              <summary className="cursor-pointer font-semibold">
                Changes · {comparison.commit_count}{' '}
                {comparison.commit_count === 1 ? 'commit' : 'commits'}
              </summary>
              <p className="mt-3 text-xs text-[var(--muted)]">
                Reviewed commits: {comparison.base_commit.slice(0, 7)} →{' '}
                {comparison.head_commit.slice(0, 7)}
              </p>
              <pre className="mt-3 max-h-[32rem] overflow-auto rounded-lg bg-[var(--inset)] p-4 text-xs leading-6">
                {comparison.diff ||
                  'No file changes. This pull request adds commit history.'}
              </pre>
              {comparison.diff_truncated && (
                <p className="mt-2 text-sm text-[var(--muted)]">
                  This comparison exceeds the 256 KiB preview limit. Review the
                  full diff in your local clone.
                </p>
              )}
            </details>
          )}
          {pull.merge_commit && pull.status === 'merged' && (
            <p className="break-all text-sm text-[var(--muted)]">
              Merge commit: <code>{pull.merge_commit}</code>
            </p>
          )}
          {canContribute &&
            query.data?.can_manage &&
            ['open', 'closed'].includes(pull.status) && (
              <button
                className={button}
                disabled={change.isPending || merge.isPending}
                onClick={() =>
                  change.mutate(pull.status === 'closed' ? 'open' : 'closed')
                }
              >
                {pull.status === 'closed'
                  ? 'Reopen pull request'
                  : 'Close pull request'}
              </button>
            )}
          {canContribute &&
            query.data?.can_merge &&
            ['open', 'merging'].includes(pull.status) &&
            comparison &&
            comparison.commit_count > 0 && (
              <div className="space-y-3 rounded-lg border border-emerald-400/20 bg-emerald-500/5 p-4">
                <p className="text-sm">
                  Merging adds these commits to{' '}
                  <strong>{pull.base_branch}</strong>. Conflicts and branch
                  changes are checked before the branch is updated.
                </p>
                {confirmMerge ? (
                  <div className="flex gap-2">
                    <button
                      className={primary}
                      disabled={merge.isPending || change.isPending}
                      onClick={() => merge.mutate()}
                    >
                      {merge.isPending ? 'Merging…' : 'Confirm merge'}
                    </button>
                    <button
                      className={button}
                      disabled={merge.isPending}
                      onClick={() => setConfirmMerge(false)}
                    >
                      Cancel
                    </button>
                  </div>
                ) : (
                  <button
                    className={primary}
                    disabled={change.isPending}
                    onClick={() => setConfirmMerge(true)}
                  >
                    {pull.status === 'merging'
                      ? 'Retry merge'
                      : 'Merge pull request'}
                  </button>
                )}
              </div>
            )}
          <div className="space-y-4 border-t border-[var(--border)] pt-5">
            <h3 className="font-semibold">Discussion</h3>
            {comments.isPending && <p role="status">Loading discussion…</p>}
            {comments.data?.data.map((item) => (
              <article
                key={item.id}
                className="rounded-lg border border-[var(--border)] p-4"
              >
                <p className="text-xs text-[var(--muted)]">
                  {item.author.username} · {time(item.created_at)}
                </p>
                <p className="mt-3 whitespace-pre-wrap break-words text-sm">
                  {item.body}
                </p>
              </article>
            ))}
            {comments.isSuccess && comments.data.data.length === 0 && (
              <p className="text-sm text-[var(--muted)]">
                Start the discussion.
              </p>
            )}
            <Pager
              page={page}
              setPage={setPage}
              hasMore={Boolean(comments.data?.next_page_url)}
              pending={comments.isPending}
            />
            {canContribute ? (
              <form
                onSubmit={(e) => {
                  e.preventDefault()
                  comment.mutate()
                }}
                className="space-y-3"
              >
                <label className="block text-sm">
                  Leave a comment
                  <textarea
                    className={'mt-2 min-h-28 ' + inputClass}
                    required
                    maxLength={10000}
                    value={body}
                    onChange={(e) => setBody(e.target.value)}
                  />
                </label>
                <button
                  className={primary}
                  disabled={comment.isPending || !body.trim()}
                >
                  {comment.isPending ? 'Posting…' : 'Comment'}
                </button>
              </form>
            ) : (
              <p className="text-sm text-[var(--muted)]">
                Sign in to join the discussion.
              </p>
            )}
          </div>
        </>
      )}
    </Panel>
  )
}

export function RepositoryActions({
  repository,
  isOwner,
}: {
  repository: Repository
  isOwner: boolean
}) {
  const [page, setPage] = useState(1),
    [branch, setBranch] = useState(''),
    [selected, setSelected] = useState<number | null>(null)
  const branches = useBranches(repository),
    refresh = useRefreshRepository(repository)
  const selectedBranch =
    branch ||
    branches.data?.default_branch ||
    branches.data?.data[0]?.name ||
    ''
  const path = '/repositories/' + repository.id + '/actions'
  const query = useQuery({
    queryKey: ['repository', repository.id, 'actions', page],
    queryFn: async () =>
      (await apiClient.get<Page<Run>>(path, { params: { page } })).data,
    retry: false,
    refetchInterval: (q) =>
      q.state.data?.data.some((run) => active(run.status)) ? 3000 : false,
  })
  const run = useMutation({
    mutationFn: () =>
      apiClient.post<{ data: Run }>(path, { branch: selectedBranch }),
    onSuccess: async (response) => {
      setSelected(response.data.data.id)
      await refresh()
    },
  })
  if (selected !== null)
    return (
      <ActionDetail
        repository={repository}
        runId={selected}
        isOwner={isOwner}
        onBack={() => {
          setSelected(null)
          void query.refetch()
        }}
      />
    )
  return (
    <Panel
      title="Actions"
      description="Built-in repository checks with saved results and logs."
      tools={
        <button
          className={button}
          aria-label="Refresh Actions"
          onClick={() => void query.refetch()}
        >
          <RefreshCw size={16} />
        </button>
      }
    >
      <p className="rounded-lg bg-[var(--inset)] p-4 text-sm leading-6 text-[var(--muted)]">
        Check commit objects, tree readability, and Git connectivity. Each run
        checks a specific commit. Project scripts and workflow YAML are not
        executed.
      </p>
      <ErrorMessage error={query.error || branches.error || run.error} />
      {isOwner && (
        <form
          className="flex flex-wrap items-end gap-3"
          onSubmit={(e) => {
            e.preventDefault()
            run.mutate()
          }}
        >
          <label className="min-w-48 flex-1 text-sm">
            Run checks on branch
            <select
              className={'mt-2 ' + inputClass}
              value={selectedBranch}
              onChange={(e) => setBranch(e.target.value)}
            >
              {branches.data?.data.map((item) => (
                <option key={item.name}>{item.name}</option>
              ))}
            </select>
          </label>
          <button
            className={primary}
            disabled={
              !selectedBranch ||
              run.isPending ||
              Boolean(query.data?.data.some((item) => active(item.status)))
            }
          >
            <Play size={16} />
            {run.isPending ? 'Queueing…' : 'Run checks'}
          </button>
        </form>
      )}
      {query.isPending && <p role="status">Loading runs…</p>}
      {query.data?.data.map((item) => (
        <button
          key={item.id}
          className="flex w-full flex-wrap items-center justify-between gap-3 rounded-lg border border-[var(--border)] p-4 text-left hover:bg-[var(--subtle)]"
          onClick={() => setSelected(item.id)}
        >
          <div>
            <h3 className="font-semibold">Repository checks #{item.id}</h3>
            <p className="mt-2 break-all text-xs text-[var(--muted)]">
              {item.branch} · {item.commit.slice(0, 7)} ·{' '}
              {time(item.created_at)}
            </p>
          </div>
          <Status value={item.status} />
        </button>
      ))}
      {query.isSuccess && query.data.data.length === 0 && (
        <div className="py-8 text-center">
          <h3 className="font-semibold">No runs yet</h3>
          <p className="mt-2 text-sm text-[var(--muted)]">
            {selectedBranch
              ? 'The repository owner can start the first check above.'
              : 'Push your first branch to enable repository checks.'}
          </p>
        </div>
      )}
      <Pager
        page={page}
        setPage={setPage}
        hasMore={Boolean(query.data?.next_page_url)}
        pending={query.isPending}
      />
    </Panel>
  )
}

function ActionDetail({
  repository,
  runId,
  isOwner,
  onBack,
}: {
  repository: Repository
  runId: number
  isOwner: boolean
  onBack: () => void
}) {
  const path = '/repositories/' + repository.id + '/actions/' + runId,
    refresh = useRefreshRepository(repository)
  const query = useQuery({
    queryKey: ['repository', repository.id, 'action', runId],
    queryFn: async () => (await apiClient.get<{ data: Run }>(path)).data,
    retry: false,
    refetchInterval: (q) =>
      q.state.data && active(q.state.data.data.status) ? 2000 : false,
  })
  const cancel = useMutation({
    mutationFn: () => apiClient.post(path + '/cancel'),
    onSuccess: refresh,
  })
  const run = query.data?.data
  return (
    <Panel
      title={'Repository checks #' + runId}
      description={run ? run.branch + ' · ' + run.commit : 'Loading run…'}
      tools={
        <button className={button} onClick={onBack}>
          <ArrowLeft size={16} />
          Back to Actions
        </button>
      }
    >
      <ErrorMessage error={query.error || cancel.error} />
      {query.isPending && <p role="status">Loading check results…</p>}
      {run && (
        <>
          <div className="flex flex-wrap items-center justify-between gap-3">
            <Status value={run.status} />
            {isOwner && active(run.status) && (
              <button
                className={button}
                disabled={cancel.isPending}
                onClick={() => cancel.mutate()}
              >
                Cancel run
              </button>
            )}
          </div>
          <p className="text-xs text-[var(--muted)]">
            Queued {time(run.created_at)}
            {run.finished_at ? ' · Finished ' + time(run.finished_at) : ''}
          </p>
          {run.status === 'queued' && (
            <p className="text-sm text-[var(--muted)]">
              Waiting for the check worker. Results update automatically.
            </p>
          )}
          {run.steps?.map((step) => (
            <div
              key={step.name}
              className="flex items-center justify-between gap-3 rounded-lg border border-[var(--border)] p-4"
            >
              <span className="text-sm">{step.name}</span>
              <Status value={step.status} />
            </div>
          ))}
          <h3 className="font-semibold">Run log</h3>
          <pre className="max-h-96 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-[var(--inset)] p-4 text-xs leading-6">
            {run.log || 'No log output yet.'}
          </pre>
        </>
      )}
    </Panel>
  )
}
