import {
  QueryClient,
  QueryClientProvider,
} from '@tanstack/react-query'
import {
  render,
  screen,
} from '@testing-library/react'
import {
  MemoryRouter,
  Route,
  Routes,
} from 'react-router-dom'
import {
  beforeEach,
  describe,
  expect,
  it,
  vi,
} from 'vitest'
import type { AuthContextValue } from '../auth/auth-context'
import { AuthContext } from '../auth/auth-context'
import {
  getRepository,
  updateRepository,
} from '../../services/repository-service'
import type { User } from '../../types/auth'
import type { Repository } from '../../types/repository'
import { RepositoryDetailPage } from './RepositoryDetailPage'

vi.mock(
  '../../services/repository-service',
  () => ({
    getRepository: vi.fn(),
    updateRepository: vi.fn(),
  }),
)

vi.mock(
  './RepositoryBrowser',
  () => ({
    RepositoryBrowser: () => (
      <section>
        Repository browser test double
      </section>
    ),
  }),
)

const repository: Repository = {
  id: 36,
  name: 'test-project',
  full_name:
    'farhad-dev/test-project',
  visibility: 'public',
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

const owner: User = {
  id: 1,
  name:
    'Farhad Valizadegan',
  username:
    'farhad-dev',
  email:
    'farhad@example.test',
  email_verified_at:
    null,
  created_at:
    '2026-08-20T09:00:00.000Z',
  updated_at:
    '2026-08-20T09:00:00.000Z',
}

const viewer: User = {
  id: 2,
  name:
    'Public Viewer',
  username:
    'viewer',
  email:
    'viewer@example.test',
  email_verified_at:
    null,
  created_at:
    '2026-08-20T09:00:00.000Z',
  updated_at:
    '2026-08-20T09:00:00.000Z',
}

function authContext(
  user: User | null,
): AuthContextValue {
  return {
    user,
    isAuthenticated:
      user !== null,
    isLoading: false,

    login:
      vi.fn<
        AuthContextValue['login']
      >(),

    register:
      vi.fn<
        AuthContextValue['register']
      >(),

    logout:
      vi.fn<
        AuthContextValue['logout']
      >(),
  }
}

function renderRepositoryDetail(
  user: User | null,
) {
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
      <MemoryRouter
        initialEntries={[
          '/repositories/36',
        ]}
      >
        <AuthContext.Provider
          value={
            authContext(
              user,
            )
          }
        >
          <Routes>
            <Route
              element={
                <RepositoryDetailPage />
              }
              path="/repositories/:repositoryId"
            />
          </Routes>
        </AuthContext.Provider>
      </MemoryRouter>
    </QueryClientProvider>,
  )
}

describe(
  'RepositoryDetailPage',
  () => {
    beforeEach(() => {
      vi.clearAllMocks()

      vi.mocked(
        getRepository,
      ).mockResolvedValue({
        data:
          repository,
      })

      vi.mocked(
        updateRepository,
      ).mockReset()
    })

    it('allows an anonymous visitor to view a public repository without owner controls', async () => {
      renderRepositoryDetail(
        null,
      )

      expect(
        await screen.findByRole(
          'heading',
          {
            name:
              repository.full_name,
          },
        ),
      ).toBeInTheDocument()

      expect(
        screen.getByRole(
          'heading',
          {
            name:
              /read-only web access/i,
          },
        ),
      ).toBeInTheDocument()

      expect(
        screen.queryByRole(
          'heading',
          {
            name:
              /repository settings/i,
          },
        ),
      ).not.toBeInTheDocument()

      expect(
        getRepository,
      ).toHaveBeenCalledWith(
        36,
      )
    })

    it('does not expose owner settings to another authenticated user', async () => {
      renderRepositoryDetail(
        viewer,
      )

      expect(
        await screen.findByRole(
          'heading',
          {
            name:
              repository.full_name,
          },
        ),
      ).toBeInTheDocument()

      expect(
        screen.getByRole(
          'heading',
          {
            name:
              /read-only web access/i,
          },
        ),
      ).toBeInTheDocument()

      expect(
        screen.queryByRole(
          'heading',
          {
            name:
              /repository settings/i,
          },
        ),
      ).not.toBeInTheDocument()
    })

    it('shows visibility settings to the repository owner', async () => {
      renderRepositoryDetail(
        owner,
      )

      expect(
        await screen.findByRole(
          'heading',
          {
            name:
              repository.full_name,
          },
        ),
      ).toBeInTheDocument()

      expect(
        screen.getByRole(
          'heading',
          {
            name:
              /repository settings/i,
          },
        ),
      ).toBeInTheDocument()

      expect(
        screen.getByText(
          /v1 allows the repository owner to change visibility only/i,
        ),
      ).toBeInTheDocument()

      expect(
        screen.queryByRole(
          'heading',
          {
            name:
              /read-only web access/i,
          },
        ),
      ).not.toBeInTheDocument()
    })
  },
)