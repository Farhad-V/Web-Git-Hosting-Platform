import {
  QueryClient,
  QueryClientProvider,
} from '@tanstack/react-query'
import {
  render,
  screen,
  within,
} from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import {
  beforeEach,
  describe,
  expect,
  it,
  vi,
} from 'vitest'
import {
  createRepository,
  getRepositories,
} from '../../services/repository-service'
import type { User } from '../../types/auth'
import type {
  Repository,
  RepositoryCollectionResponse,
  RepositoryMutationResponse,
} from '../../types/repository'
import {
  AuthContext,
  type AuthContextValue,
} from '../auth/auth-context'
import { DashboardPage } from './DashboardPage'

vi.mock(
  '../../services/repository-service',
  () => ({
    createRepository: vi.fn(),
    getRepositories: vi.fn(),
  }),
)

const authenticatedUser: User = {
  id: 1,
  name: 'Farhad Valizadegan',
  username: 'farhad',
  email: 'farhad@example.test',
  email_verified_at: null,
  created_at: '2026-08-22T10:00:00Z',
  updated_at: '2026-08-22T10:00:00Z',
}

const publicRepository: Repository = {
  id: 11,
  name: 'public-project',
  full_name: 'farhad/public-project',
  visibility: 'public',
  owner: {
    id: 1,
    name: 'Farhad Valizadegan',
    username: 'farhad',
  },
  created_at: '2026-08-20T10:00:00Z',
  updated_at: '2026-08-21T11:00:00Z',
}

const privateRepository: Repository = {
  id: 12,
  name: 'private-project',
  full_name: 'farhad/private-project',
  visibility: 'private',
  owner: {
    id: 1,
    name: 'Farhad Valizadegan',
    username: 'farhad',
  },
  created_at: '2026-08-20T12:00:00Z',
  updated_at: '2026-08-21T13:00:00Z',
}

function repositoryCollection(
  repositories: Repository[],
): RepositoryCollectionResponse {
  return {
    data: repositories,
    links: {
      first: null,
      last: null,
      prev: null,
      next: null,
    },
    meta: {
      current_page: 1,
      from:
        repositories.length > 0
          ? 1
          : null,
      last_page: 1,
      links: [],
      path: '/api/v1/repositories',
      per_page: 15,
      to:
        repositories.length > 0
          ? repositories.length
          : null,
      total: repositories.length,
    },
  }
}

function createAuthContext(): AuthContextValue {
  return {
    user: authenticatedUser,
    isAuthenticated: true,
    isLoading: false,
    login: vi.fn().mockResolvedValue(undefined),
    register: vi.fn().mockResolvedValue(undefined),
    logout: vi.fn().mockResolvedValue(undefined),
  }
}

function renderDashboard() {
  const queryClient = new QueryClient({
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
    <QueryClientProvider client={queryClient}>
      <MemoryRouter initialEntries={['/dashboard']}>
        <AuthContext.Provider
          value={createAuthContext()}
        >
          <DashboardPage />
        </AuthContext.Provider>
      </MemoryRouter>
    </QueryClientProvider>,
  )
}

describe('DashboardPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('displays owned repository data on the dashboard', async () => {
    vi.mocked(
      getRepositories,
    ).mockResolvedValue(
      repositoryCollection([
        publicRepository,
        privateRepository,
      ]),
    )

    renderDashboard()

    expect(
      screen.getByRole(
        'heading',
        {
          name:
            /welcome, farhad valizadegan/i,
        },
      ),
    ).toBeInTheDocument()

    expect(
      screen.getAllByText(
        '@farhad',
      ),
    ).toHaveLength(
      2,
    )

    expect(
      await screen.findByRole(
        'link',
        {
          name:
            'farhad/public-project',
        },
      ),
    ).toHaveAttribute(
      'href',
      '/repositories/11',
    )

    expect(
      screen.getByRole(
        'link',
        {
          name:
            'farhad/private-project',
        },
      ),
    ).toHaveAttribute(
      'href',
      '/repositories/12',
    )

    expect(
      screen.getByText(
        'Owned repositories',
      ),
    ).toBeInTheDocument()

    expect(
      screen.getByText(
        'Public on this page',
      ),
    ).toBeInTheDocument()

    expect(
      getRepositories,
    ).toHaveBeenCalledWith(
      1,
    )
  })

  it('creates a repository through the dashboard dialog', async () => {
    const user = userEvent.setup()

    vi.mocked(
      getRepositories,
    ).mockResolvedValue(
      repositoryCollection([]),
    )

    const createdRepository: Repository = {
      id: 21,
      name: 'new-project',
      full_name: 'farhad/new-project',
      visibility: 'public',
      owner: {
        id: 1,
        name: 'Farhad Valizadegan',
        username: 'farhad',
      },
      created_at: '2026-08-22T15:00:00Z',
      updated_at: '2026-08-22T15:00:00Z',
    }

    const mutationResponse: RepositoryMutationResponse = {
      message: 'Repository created successfully.',
      repository: createdRepository,
    }

    vi.mocked(
      createRepository,
    ).mockResolvedValue(
      mutationResponse,
    )

    renderDashboard()

    await screen.findByText(
      /no repositories yet/i,
    )

    await user.click(
      screen.getByRole(
        'button',
        {
          name:
            /^new repository$/i,
        },
      ),
    )

    const dialog = screen.getByRole(
      'dialog',
      {
        name:
          /create a new repository/i,
      },
    )

    expect(
      dialog,
    ).toBeInTheDocument()

    await user.type(
      within(
        dialog,
      ).getByLabelText(
        /repository name/i,
      ),
      'New-Project',
    )

    await user.click(
      within(
        dialog,
      ).getByRole(
        'radio',
        {
          name: /^public/i,
        },
      ),
    )

    await user.click(
      within(
        dialog,
      ).getByRole(
        'button',
        {
          name:
            /^create repository$/i,
        },
      ),
    )

    expect(
      createRepository,
    ).toHaveBeenCalledTimes(
      1,
    )

    expect(
      createRepository,
    ).toHaveBeenCalledWith({
      name: 'new-project',
      visibility: 'public',
    })

    expect(
      await screen.findByText(
        /no repositories yet/i,
      ),
    ).toBeInTheDocument()

    expect(
      screen.queryByRole(
        'dialog',
        {
          name:
            /create a new repository/i,
        },
      ),
    ).not.toBeInTheDocument()
  })

  it('shows repository form validation without submitting invalid data', async () => {
    const user = userEvent.setup()

    vi.mocked(
      getRepositories,
    ).mockResolvedValue(
      repositoryCollection([]),
    )

    renderDashboard()

    await screen.findByText(
      /no repositories yet/i,
    )

    await user.click(
      screen.getByRole(
        'button',
        {
          name:
            /^new repository$/i,
        },
      ),
    )

    const dialog = screen.getByRole(
      'dialog',
      {
        name:
          /create a new repository/i,
      },
    )

    await user.click(
      within(
        dialog,
      ).getByRole(
        'button',
        {
          name:
            /^create repository$/i,
        },
      ),
    )

    expect(
      await within(
        dialog,
      ).findByText(
        /repository name is required/i,
      ),
    ).toBeInTheDocument()

    expect(
      createRepository,
    ).not.toHaveBeenCalled()
  })
})