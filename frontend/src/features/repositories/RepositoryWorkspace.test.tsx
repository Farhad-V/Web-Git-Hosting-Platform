import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { fireEvent, render, screen, waitFor } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { beforeEach, expect, it, vi } from 'vitest'
import { RepositoryWorkspace } from './RepositoryWorkspace'
import { apiClient } from '../../lib/api'
import {
  getRepositoryBranches,
  getRepositoryCommits,
  getRepositoryCommit,
} from '../../services/repository-browser-service'
import type { Repository } from '../../types/repository'
vi.mock('../../lib/api', () => ({ apiClient: { get: vi.fn() } }))
vi.mock('../../services/repository-browser-service', () => ({
  getRepositoryBranches: vi.fn(),
  getRepositoryCommits: vi.fn(),
  getRepositoryCommit: vi.fn(),
}))
vi.mock('./RepositoryBrowser', () => ({
  RepositoryBrowser: () => <div>Code browser</div>,
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
function show(tab = 'code', isOwner = false) {
  render(
    <QueryClientProvider
      client={
        new QueryClient({ defaultOptions: { queries: { retry: false } } })
      }
    >
      <MemoryRouter initialEntries={['/?tab=' + tab]}>
        <RepositoryWorkspace
          repository={repository}
          isOwner={isOwner}
          onUpdated={vi.fn()}
        />
      </MemoryRouter>
    </QueryClientProvider>,
  )
}
beforeEach(() => {
  vi.clearAllMocks()
})
it('protects owner settings and switches repository navigation', async () => {
  show('settings')
  expect(screen.getByText('Code browser')).toBeInTheDocument()
  expect(
    screen.queryByRole('button', { name: 'Settings' }),
  ).not.toBeInTheDocument()
  vi.mocked(apiClient.get).mockResolvedValue({
    data: { data: [], page: 1, has_more: false },
  })
  fireEvent.click(screen.getByRole('button', { name: 'Tags' }))
  expect(await screen.findByText('No tags yet')).toBeInTheDocument()
  expect(apiClient.get).toHaveBeenCalledWith('/repositories/2/tags', {
    params: { page: 1, limit: 30 },
  })
})
it('opens the target commit from a tag', async () => {
  const hash = 'a'.repeat(40)
  vi.mocked(apiClient.get).mockResolvedValue({
    data: {
      data: [
        {
          name: 'v1.0.0',
          object_id: hash,
          commit: hash,
          annotated: true,
          created_at: null,
          subject: 'Release',
        },
      ],
      page: 1,
      has_more: false,
    },
  })
  vi.mocked(getRepositoryCommit).mockResolvedValue({
    commit: {
      id: hash,
      tree: hash,
      parents: [],
      author_name: 'Owner',
      author_email: 'a@example.test',
      authored_at: '2026-01-01',
      committer_name: 'Owner',
      committer_email: 'a@example.test',
      committed_at: '2026-01-01',
      message: 'Full commit message',
    },
  })
  show('tags')
  fireEvent.click(
    await screen.findByRole('button', { name: 'View tagged commit v1.0.0' }),
  )
  expect(await screen.findByText('Full commit message')).toBeInTheDocument()
  expect(getRepositoryCommit).toHaveBeenCalledWith(2, hash)
})
it('paginates commit history and resets the page when changing branches', async () => {
  vi.mocked(getRepositoryBranches).mockResolvedValue({
    default_branch: 'main',
    data: [
      {
        name: 'main',
        commit: 'a'.repeat(40),
        committed_at: null,
        subject: 'First',
      },
      {
        name: 'develop',
        commit: 'b'.repeat(40),
        committed_at: null,
        subject: 'Second',
      },
    ],
  })
  vi.mocked(getRepositoryCommits).mockResolvedValue({
    data: [],
    branch: 'main',
    has_more: true,
    page: 1,
  })
  show('commits')
  await waitFor(() =>
    expect(screen.getByRole('button', { name: 'Next' })).toBeEnabled(),
  )
  fireEvent.click(screen.getByRole('button', { name: 'Next' }))
  await waitFor(() =>
    expect(getRepositoryCommits).toHaveBeenCalledWith(2, {
      branch: 'main',
      limit: 30,
      page: 2,
    }),
  )
  fireEvent.change(screen.getByRole('combobox', { name: 'History branch' }), {
    target: { value: 'develop' },
  })
  await waitFor(() =>
    expect(getRepositoryCommits).toHaveBeenCalledWith(2, {
      branch: 'develop',
      limit: 30,
      page: 1,
    }),
  )
})
