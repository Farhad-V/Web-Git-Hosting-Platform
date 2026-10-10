import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import {
  fireEvent,
  render,
  screen,
  within,
  waitFor,
} from '@testing-library/react'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { RepositoryBrowser } from './RepositoryBrowser'
import {
  getRepositoryBlob,
  getRepositoryBranches,
  getRepositoryCommits,
  getRepositoryTree,
} from '../../services/repository-browser-service'
import type { RepositoryTreeEntry } from '../../types/repository-browser'

vi.mock('../../services/repository-browser-service', () => ({
  getRepositoryBlob: vi.fn(),
  getRepositoryBranches: vi.fn(),
  getRepositoryCommit: vi.fn(),
  getRepositoryCommits: vi.fn(),
  getRepositoryTree: vi.fn(),
}))
vi.mock('./PdfViewer', () => ({
  PdfViewer: ({ path }: { path: string }) => (
    <div role="region" aria-label="PDF preview">
      {path}
    </div>
  ),
}))
const entry = (
  name: string,
  type: RepositoryTreeEntry['type'] = 'file',
  path = name,
): RepositoryTreeEntry => ({
  name,
  path,
  type,
  mode: '100644',
  object_id: 'a'.repeat(40),
  size: 12,
})
const repository = {
  id: 36,
  name: 'demo',
  full_name: 'owner/demo',
  visibility: 'public' as const,
  owner: { id: 1, name: 'Owner', username: 'owner' },
  created_at: '',
  updated_at: '',
}
function mount() {
  return render(
    <QueryClientProvider
      client={
        new QueryClient({ defaultOptions: { queries: { retry: false } } })
      }
    >
      <RepositoryBrowser repository={repository} />
    </QueryClientProvider>,
  )
}
describe('Repository file sidebar', () => {
  beforeEach(() => {
    vi.resetAllMocks()
    vi.mocked(getRepositoryBranches).mockResolvedValue({
      default_branch: 'main',
      data: ['main', 'feature'].map((name) => ({
        name,
        commit: 'a'.repeat(40),
        committed_at: null,
        subject: '',
      })),
    })
    vi.mocked(getRepositoryCommits).mockResolvedValue({
      data: [],
      branch: 'main',
    })
    vi.mocked(getRepositoryTree).mockImplementation(
      async (_id, parameters) => ({
        branch: parameters?.branch ?? 'main',
        path: parameters?.path ?? '',
        data:
          parameters?.path === 'src'
            ? [entry('index.ts', 'file', 'src/index.ts')]
            : [
                entry('src', 'directory'),
                entry('README.md'),
                entry('vendor', 'submodule'),
              ],
      }),
    )
    vi.mocked(getRepositoryBlob).mockImplementation(
      async (_id, parameters) => ({
        branch: parameters?.branch ?? 'main',
        data: {
          path: parameters?.path ?? '',
          size: 12,
          encoding: 'utf-8',
          content: 'export const demo = true',
        },
      }),
    )
  })

  it('retains the file sidebar beside a PDF preview without requesting the text blob endpoint', async () => {
    vi.mocked(getRepositoryTree).mockResolvedValue({
      branch: 'main',
      path: '',
      data: [entry('guide.pdf')],
    })
    mount()
    const sidebar = await screen.findByRole('navigation', {
      name: 'Repository files',
    })
    fireEvent.click(
      await within(sidebar).findByRole('button', {
        name: 'Open file guide.pdf',
      }),
    )
    expect(
      await screen.findByRole('region', { name: 'PDF preview' }),
    ).toHaveTextContent('guide.pdf')
    expect(
      within(sidebar).getByRole('button', { name: 'Open file guide.pdf' }),
    ).toHaveAttribute('aria-current', 'page')
    expect(getRepositoryBlob).not.toHaveBeenCalled()
  })

  it('loads expanded folders on demand and keeps the selected file highlighted alongside its contents', async () => {
    mount()
    const sidebar = await screen.findByRole('navigation', {
      name: 'Repository files',
    })
    expect(getRepositoryTree).not.toHaveBeenCalledWith(36, {
      branch: 'main',
      path: 'src',
    })
    fireEvent.click(
      await within(sidebar).findByRole('button', { name: 'Expand folder src' }),
    )
    fireEvent.click(
      await within(sidebar).findByRole('button', {
        name: 'Open file src/index.ts',
      }),
    )
    expect(
      await screen.findByText('export const demo = true'),
    ).toBeInTheDocument()
    expect(
      within(sidebar).getByRole('button', { name: 'Open file src/index.ts' }),
    ).toHaveAttribute('aria-current', 'page')
    expect(
      screen.getByRole('navigation', { name: 'Repository path' }),
    ).toHaveTextContent('src')
    fireEvent.click(screen.getByRole('button', { name: 'Return to directory' }))
    expect(
      await screen.findByText('index.ts', { selector: 'span.min-w-0' }),
    ).toBeInTheDocument()
    expect(getRepositoryTree).toHaveBeenCalledWith(36, {
      branch: 'main',
      path: 'src',
    })
    fireEvent.click(
      within(sidebar).getByRole('button', { name: 'Collapse folder src' }),
    )
    expect(
      within(sidebar).queryByRole('button', { name: 'Open file src/index.ts' }),
    ).not.toBeInTheDocument()
  })

  it('clears file selection and expanded folders when switching branches', async () => {
    mount()
    const sidebar = await screen.findByRole('navigation', {
      name: 'Repository files',
    })
    fireEvent.click(
      await within(sidebar).findByRole('button', { name: 'Expand folder src' }),
    )
    fireEvent.click(
      await within(sidebar).findByRole('button', {
        name: 'Open file src/index.ts',
      }),
    )
    await screen.findByText('export const demo = true')
    fireEvent.change(screen.getByLabelText('Repository branch'), {
      target: { value: 'feature' },
    })
    await waitFor(() =>
      expect(getRepositoryTree).toHaveBeenCalledWith(36, {
        branch: 'feature',
        path: '',
      }),
    )
    expect(
      screen.queryByText('export const demo = true'),
    ).not.toBeInTheDocument()
    expect(
      await within(sidebar).findByRole('button', { name: 'Expand folder src' }),
    ).toHaveAttribute('aria-expanded', 'false')
    expect(
      within(sidebar).queryByRole('button', { name: 'Open file src/index.ts' }),
    ).not.toBeInTheDocument()
  })

  it('can hide and restore the sidebar without losing the selected file', async () => {
    mount()
    const sidebar = await screen.findByRole('navigation', {
      name: 'Repository files',
    })
    fireEvent.click(
      await within(sidebar).findByRole('button', {
        name: 'Open file README.md',
      }),
    )
    await screen.findByText('export const demo = true')
    fireEvent.click(screen.getByRole('button', { name: 'Hide files' }))
    expect(
      screen.queryByRole('navigation', { name: 'Repository files' }),
    ).not.toBeInTheDocument()
    expect(screen.getByText('export const demo = true')).toBeInTheDocument()
    fireEvent.click(screen.getByRole('button', { name: 'Show files' }))
    expect(
      within(
        screen.getByRole('navigation', { name: 'Repository files' }),
      ).getByRole('button', { name: 'Open file README.md' }),
    ).toHaveAttribute('aria-current', 'page')
  })

  it('shows a recoverable folder error and does not open submodules as files', async () => {
    const original = vi.mocked(getRepositoryTree).getMockImplementation()!
    let fail = true
    vi.mocked(getRepositoryTree).mockImplementation(async (...args) => {
      if (args[1]?.path === 'src' && fail) {
        fail = false
        throw new Error('Unavailable')
      }
      return original(...args)
    })
    mount()
    const sidebar = await screen.findByRole('navigation', {
      name: 'Repository files',
    })
    expect(
      await within(sidebar).findByRole('button', { name: 'Submodule vendor' }),
    ).toBeDisabled()
    fireEvent.click(
      within(sidebar).getByRole('button', { name: 'Expand folder src' }),
    )
    expect(await within(sidebar).findByRole('alert')).toHaveTextContent(
      'Could not load src.',
    )
    fireEvent.click(
      within(sidebar).getByRole('button', { name: 'Retry loading src' }),
    )
    expect(
      await within(sidebar).findByRole('button', {
        name: 'Open file src/index.ts',
      }),
    ).toBeInTheDocument()
  })
})
