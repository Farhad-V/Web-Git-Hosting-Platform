import {
  QueryClient,
  QueryClientProvider,
} from '@tanstack/react-query'
import {
  render,
  screen,
  waitFor,
} from '@testing-library/react'
import {
  beforeEach,
  describe,
  expect,
  it,
  vi,
} from 'vitest'
import {
  getRepositoryBlob,
  getRepositoryBranches,
  getRepositoryCommits,
  getRepositoryTree,
} from '../../services/repository-browser-service'
import type { Repository } from '../../types/repository'
import { RepositoryBrowser } from './RepositoryBrowser'

vi.mock(
  '../../services/repository-browser-service',
  () => ({
    getRepositoryBlob:
      vi.fn(),

    getRepositoryBranches:
      vi.fn(),

    getRepositoryCommits:
      vi.fn(),

    getRepositoryTree:
      vi.fn(),
  }),
)

const repository: Repository = {
  id: 36,
  name:
    'test-project',
  full_name:
    'farhad-dev/test-project',
  visibility:
    'public',
  owner: {
    id: 1,
    name:
      'Farhad Valizadegan',
    username:
      'farhad-dev',
  },
  created_at:
    '2026-08-20T10:00:00.000Z',
  updated_at:
    '2026-08-20T10:00:00.000Z',
}

function renderBrowser() {
  const queryClient =
    new QueryClient({
      defaultOptions: {
        queries: {
          retry: false,
        },
        mutations: {
          retry: false,
        },
      },
    })

  return render(
    <QueryClientProvider
      client={
        queryClient
      }
    >
      <RepositoryBrowser
        repository={
          repository
        }
      />
    </QueryClientProvider>,
  )
}

describe(
  'RepositoryBrowser',
  () => {
    beforeEach(() => {
      vi.clearAllMocks()

      vi.mocked(
        getRepositoryBlob,
      ).mockReset()

      vi.mocked(
        getRepositoryTree,
      ).mockReset()

      vi.mocked(
        getRepositoryCommits,
      ).mockReset()

      vi.mocked(
        getRepositoryBranches,
      ).mockReset()
    })

    it('renders the controlled empty repository state without requesting tree or commits', async () => {
      vi.mocked(
        getRepositoryBranches,
      ).mockResolvedValue({
        data: [],
        default_branch:
          null,
      })

      renderBrowser()

      expect(
        await screen.findByRole(
          'heading',
          {
            name:
              /empty repository/i,
          },
        ),
      ).toBeInTheDocument()

      expect(
        screen.getByText(
          /does not contain a committed branch yet/i,
        ),
      ).toBeInTheDocument()

      expect(
        getRepositoryTree,
      ).not.toHaveBeenCalled()

      expect(
        getRepositoryCommits,
      ).not.toHaveBeenCalled()
    })

    it('uses the Git-native default branch returned by the branches endpoint', async () => {
      vi.mocked(
        getRepositoryBranches,
      ).mockResolvedValue({
        data: [
          {
            name: 'main',
            commit:
              '0123456789abcdef0123456789abcdef01234567',
            committed_at:
              '2026-08-20T10:00:00+00:00',
            subject:
              'Initial repository content',
          },
        ],
        default_branch:
          'main',
      })

      vi.mocked(
        getRepositoryTree,
      ).mockResolvedValue({
        data: [
          {
            name:
              'README.md',
            path:
              'README.md',
            type:
              'file',
            mode:
              '100644',
            object_id:
              'abcdef0123456789abcdef0123456789abcdef01',
            size: 42,
          },
        ],
        branch:
          'main',
        path: '',
      })

      vi.mocked(
        getRepositoryCommits,
      ).mockResolvedValue({
        data: [
          {
            hash:
              '0123456789abcdef0123456789abcdef01234567',
            parents: [],
            author: {
              name:
                'Repository Browser Test',
              email:
                'browser@example.test',
            },
            authored_at:
              '2026-08-20T10:00:00+00:00',
            subject:
              'Initial repository content',
          },
        ],
        branch:
          'main',
      })

      renderBrowser()

      await waitFor(() => {
        expect(
          getRepositoryTree,
        ).toHaveBeenCalledWith(
          36,
          {
            branch:
              'main',
            path: '',
          },
        )
      })

      await waitFor(() => {
        expect(
          getRepositoryCommits,
        ).toHaveBeenCalledWith(
          36,
          {
            branch:
              'main',
            limit: 10,
          },
        )
      })

      expect(
        screen.getByLabelText(
          /repository branch/i,
        ),
      ).toHaveValue(
        'main',
      )

      expect(
        await screen.findByText(
          'README.md',
        ),
      ).toBeInTheDocument()

      expect(
        screen.getByText(
          'Initial repository content',
        ),
      ).toBeInTheDocument()
    })

    it('renders repository-controlled values as text instead of executable HTML', async () => {
      const maliciousSubject =
        '<img src=x onerror="window.__gitforge_xss = true">'

      const maliciousAuthor =
        '<script>window.__gitforge_xss = true</script>'

      const maliciousFileName =
        '<img src=x onerror="alert(1)">.txt'

      vi.mocked(
        getRepositoryBranches,
      ).mockResolvedValue({
        data: [
          {
            name:
              'main',
            commit:
              '0123456789abcdef0123456789abcdef01234567',
            committed_at:
              '2026-08-20T10:00:00+00:00',
            subject:
              maliciousSubject,
          },
        ],
        default_branch:
          'main',
      })

      vi.mocked(
        getRepositoryTree,
      ).mockResolvedValue({
        data: [
          {
            name:
              maliciousFileName,
            path:
              maliciousFileName,
            type:
              'file',
            mode:
              '100644',
            object_id:
              'abcdef0123456789abcdef0123456789abcdef01',
            size: 42,
          },
        ],
        branch:
          'main',
        path: '',
      })

      vi.mocked(
        getRepositoryCommits,
      ).mockResolvedValue({
        data: [
          {
            hash:
              '0123456789abcdef0123456789abcdef01234567',
            parents: [],
            author: {
              name:
                maliciousAuthor,
              email:
                'attacker@example.test',
            },
            authored_at:
              '2026-08-20T10:00:00+00:00',
            subject:
              maliciousSubject,
          },
        ],
        branch:
          'main',
      })

      const {
        container,
      } = renderBrowser()

      expect(
        await screen.findByText(
          maliciousFileName,
        ),
      ).toBeInTheDocument()

      expect(
        screen.getAllByText(
          maliciousSubject,
        ).length,
      ).toBeGreaterThan(
        0,
      )

      expect(
        screen.getByText(
          maliciousAuthor,
        ),
      ).toBeInTheDocument()

      expect(
        container.querySelector(
          'script',
        ),
      ).toBeNull()

      expect(
        container.querySelector(
          'img',
        ),
      ).toBeNull()

      expect(
        (
          window as typeof window & {
            __gitforge_xss?: boolean
          }
        ).__gitforge_xss,
      ).toBeUndefined()
    })
  },
)