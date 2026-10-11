import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { fireEvent, render, screen, waitFor } from '@testing-library/react'
import { beforeEach, expect, it, vi, type Mock } from 'vitest'
import {
  RepositoryActions,
  RepositoryBranches,
  RepositoryPullRequests,
} from './RepositoryCollaboration'
import { apiClient } from '../../lib/api'
import { getRepositoryBranches } from '../../services/repository-browser-service'
import type { Repository } from '../../types/repository'
vi.mock('../../lib/api', () => ({
  apiClient: { get: vi.fn(), post: vi.fn(), patch: vi.fn() },
}))
vi.mock('../../services/repository-browser-service', () => ({
  getRepositoryBranches: vi.fn(),
}))
const repository: Repository = {
  id: 2,
  name: 'project',
  full_name: 'owner/project',
  visibility: 'public',
  owner: { id: 1, name: 'Owner', username: 'owner' },
  created_at: '2026-01-01',
  updated_at: '2026-01-01',
}
const getMock = apiClient.get as Mock
const page = (data: unknown[]) => ({
  data,
  current_page: 1,
  next_page_url: null,
})
const pull = {
  id: 7,
  number: 1,
  title: 'Improve README',
  description: 'Review this change',
  base_branch: 'main',
  head_branch: 'feature',
  status: 'open',
  author: { username: 'owner', name: 'Owner' },
  created_at: '2026-01-01',
  merge_commit: null,
}
const comparison = {
  base_commit: 'a'.repeat(40),
  head_commit: 'b'.repeat(40),
  commit_count: 1,
  diff: '+New documentation',
  diff_truncated: false,
}
const run = {
  id: 3,
  branch: 'main',
  commit: 'a'.repeat(40),
  status: 'queued',
  created_at: '2026-01-01',
  started_at: null,
  finished_at: null,
  steps: [],
  log: 'Waiting for worker',
}
function show(element: React.ReactNode) {
  render(
    <QueryClientProvider
      client={
        new QueryClient({
          defaultOptions: {
            queries: { retry: false },
            mutations: { retry: false },
          },
        })
      }
    >
      {element}
    </QueryClientProvider>,
  )
}
beforeEach(() => {
  vi.clearAllMocks()
  vi.mocked(getRepositoryBranches).mockResolvedValue({
    default_branch: 'main',
    data: [
      {
        name: 'main',
        commit: 'a'.repeat(40),
        committed_at: null,
        subject: 'Initial',
      },
      {
        name: 'feature',
        commit: 'b'.repeat(40),
        committed_at: null,
        subject: 'Feature',
      },
    ],
  })
})
it('creates a branch and opens its code from the branches tab', async () => {
  const onBrowse = vi.fn()
  vi.mocked(apiClient.post).mockResolvedValue({
    data: { message: 'Branch created.' },
  })
  show(
    <RepositoryBranches repository={repository} isOwner onBrowse={onBrowse} />,
  )
  fireEvent.click(
    await screen.findByRole('button', { name: 'View code on feature' }),
  )
  expect(onBrowse).toHaveBeenCalledWith('feature')
  fireEvent.click(screen.getByRole('button', { name: 'New branch' }))
  fireEvent.change(screen.getByRole('textbox', { name: 'Branch name' }), {
    target: { value: 'feature/new' },
  })
  fireEvent.click(screen.getByRole('button', { name: 'Create branch' }))
  await waitFor(() =>
    expect(apiClient.post).toHaveBeenCalledWith('/repositories/2/branches', {
      name: 'feature/new',
      source: 'main',
    }),
  )
})
it('keeps branch management unavailable to visitors', async () => {
  show(
    <RepositoryBranches
      repository={repository}
      isOwner={false}
      onBrowse={vi.fn()}
    />,
  )
  await screen.findByText('feature')
  expect(
    screen.queryByRole('button', { name: 'New branch' }),
  ).not.toBeInTheDocument()
  expect(
    screen.queryByRole('button', { name: /Make default/ }),
  ).not.toBeInTheDocument()
})
it('requires confirmation and submits the exact reviewed commits for merging', async () => {
  getMock.mockImplementation(async (path: string) => ({
    data: path.endsWith('/comments')
      ? page([])
      : path.endsWith('/pulls')
        ? page([pull])
        : {
            data: pull,
            comparison,
            comparison_error: null,
            can_manage: true,
            can_merge: true,
          },
  }))
  vi.mocked(apiClient.post).mockRejectedValue({
    isAxiosError: true,
    response: { data: { message: 'A branch changed since you reviewed it.' } },
  })
  show(<RepositoryPullRequests repository={repository} canContribute />)
  fireEvent.click(await screen.findByRole('button', { name: /Improve README/ }))
  fireEvent.click(
    await screen.findByRole('button', { name: 'Merge pull request' }),
  )
  expect(apiClient.post).not.toHaveBeenCalled()
  fireEvent.click(screen.getByRole('button', { name: 'Confirm merge' }))
  await screen.findByRole('alert')
  expect(apiClient.post).toHaveBeenCalledWith(
    '/repositories/2/pulls/7/merge',
    {
      expected_base: comparison.base_commit,
      expected_head: comparison.head_commit,
    },
    { timeout: 60000 },
  )
  expect(screen.getByRole('alert')).toHaveTextContent(
    'A branch changed since you reviewed it.',
  )
})
it('renders discussion text safely and hides contributor controls for anonymous viewers', async () => {
  const text = '<img src=x onerror=alert(1)>'
  getMock.mockImplementation(async (path: string) => ({
    data: path.endsWith('/comments')
      ? page([
          {
            id: 1,
            body: text,
            author: { username: 'reviewer' },
            created_at: '2026-01-01',
          },
        ])
      : path.endsWith('/pulls')
        ? page([pull])
        : {
            data: pull,
            comparison,
            comparison_error: null,
            can_manage: false,
            can_merge: false,
          },
  }))
  show(<RepositoryPullRequests repository={repository} canContribute={false} />)
  fireEvent.click(await screen.findByRole('button', { name: /Improve README/ }))
  expect(await screen.findByText(text)).toBeInTheDocument()
  expect(screen.queryByRole('img')).not.toBeInTheDocument()
  expect(
    screen.queryByRole('button', { name: 'Merge pull request' }),
  ).not.toBeInTheDocument()
  expect(
    screen.queryByRole('textbox', { name: 'Leave a comment' }),
  ).not.toBeInTheDocument()
})
it('starts repository checks and opens persisted step results and logs', async () => {
  getMock.mockImplementation(async (path: string) => ({
    data: path.endsWith('/actions')
      ? page([])
      : {
          data: {
            ...run,
            status: 'success',
            steps: [{ name: 'Git object connectivity', status: 'success' }],
            log: 'PASS: Git object connectivity',
          },
        },
  }))
  vi.mocked(apiClient.post).mockResolvedValue({ data: { data: run } })
  show(<RepositoryActions repository={repository} isOwner />)
  await waitFor(() =>
    expect(screen.getByRole('button', { name: 'Run checks' })).toBeEnabled(),
  )
  fireEvent.click(screen.getByRole('button', { name: 'Run checks' }))
  expect(
    await screen.findByText('PASS: Git object connectivity'),
  ).toBeInTheDocument()
  expect(apiClient.post).toHaveBeenCalledWith('/repositories/2/actions', {
    branch: 'main',
  })
})
it('cancels a queued run and updates the displayed status', async () => {
  let status = 'queued'
  getMock.mockImplementation(async (path: string) => ({
    data: path.endsWith('/actions')
      ? page([run])
      : { data: { ...run, status } },
  }))
  vi.mocked(apiClient.post).mockImplementation(async () => {
    status = 'cancelled'
    return { data: { data: { ...run, status } } }
  })
  show(<RepositoryActions repository={repository} isOwner />)
  fireEvent.click(
    await screen.findByRole('button', { name: /Repository checks #3/ }),
  )
  fireEvent.click(await screen.findByRole('button', { name: 'Cancel run' }))
  expect(await screen.findByText('cancelled')).toBeInTheDocument()
  expect(apiClient.post).toHaveBeenCalledWith(
    '/repositories/2/actions/3/cancel',
  )
})
