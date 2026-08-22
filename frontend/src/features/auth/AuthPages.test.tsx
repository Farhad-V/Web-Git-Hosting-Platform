import {
  QueryClient,
  QueryClientProvider,
} from '@tanstack/react-query'
import {
  render,
  screen,
} from '@testing-library/react'
import userEvent from '@testing-library/user-event'
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
import type { User } from '../../types/auth'
import {
  AuthContext,
  type AuthContextValue,
} from './auth-context'
import { LoginPage } from './LoginPage'
import { RegisterPage } from './RegisterPage'

const authenticatedUser: User = {
  id: 1,
  name: 'Farhad Valizadegan',
  username: 'farhad',
  email: 'farhad@example.test',
  email_verified_at: null,
  created_at: '2026-08-22T10:00:00Z',
  updated_at: '2026-08-22T10:00:00Z',
}

function createAuthContext(): AuthContextValue {
  return {
    user: null,
    isAuthenticated: false,
    isLoading: false,
    login: vi.fn().mockResolvedValue(undefined),
    register: vi.fn().mockResolvedValue(undefined),
    logout: vi.fn().mockResolvedValue(undefined),
  }
}

function renderAuthRoute(
  initialEntry: string,
  context: AuthContextValue,
) {
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
      <MemoryRouter initialEntries={[initialEntry]}>
        <AuthContext.Provider value={context}>
          <Routes>
            <Route
              path="/login"
              element={<LoginPage />}
            />

            <Route
              path="/register"
              element={<RegisterPage />}
            />

            <Route
              path="/dashboard"
              element={
                <h1>
                  Dashboard
                </h1>
              }
            />
          </Routes>
        </AuthContext.Provider>
      </MemoryRouter>
    </QueryClientProvider>,
  )
}

describe('authentication pages', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('registers a valid user and navigates to the dashboard', async () => {
    const user = userEvent.setup()
    const context = createAuthContext()

    vi.mocked(
      context.register,
    ).mockImplementation(
      async () => {
        context.user =
          authenticatedUser
      },
    )

    renderAuthRoute(
      '/register',
      context,
    )

    await user.type(
      screen.getByLabelText(
        /full name/i,
      ),
      'Farhad Valizadegan',
    )

    await user.type(
      screen.getByLabelText(
        /^username$/i,
      ),
      'farhad',
    )

    await user.type(
      screen.getByLabelText(
        /email address/i,
      ),
      'farhad@example.test',
    )

    await user.type(
      screen.getByLabelText(
        /^password$/i,
      ),
      'SecurePassword1!',
    )

    await user.type(
      screen.getByLabelText(
        /confirm password/i,
      ),
      'SecurePassword1!',
    )

    await user.click(
      screen.getByRole(
        'button',
        {
          name:
            /^create account$/i,
        },
      ),
    )

    expect(
      context.register,
    ).toHaveBeenCalledTimes(
      1,
    )

    expect(
      context.register,
    ).toHaveBeenCalledWith({
      name: 'Farhad Valizadegan',
      username: 'farhad',
      email: 'farhad@example.test',
      password: 'SecurePassword1!',
      password_confirmation:
        'SecurePassword1!',
    })

    expect(
      await screen.findByRole(
        'heading',
        {
          name:
            /^dashboard$/i,
        },
      ),
    ).toBeInTheDocument()
  })

  it('logs in with valid credentials and navigates to the dashboard', async () => {
    const user = userEvent.setup()
    const context = createAuthContext()

    vi.mocked(
      context.login,
    ).mockImplementation(
      async () => {
        context.user =
          authenticatedUser
      },
    )

    renderAuthRoute(
      '/login',
      context,
    )

    await user.type(
      screen.getByLabelText(
        /email address/i,
      ),
      'farhad@example.test',
    )

    await user.type(
      screen.getByLabelText(
        /^password$/i,
      ),
      'SecurePassword1!',
    )

    await user.click(
      screen.getByRole(
        'button',
        {
          name:
            /^sign in$/i,
        },
      ),
    )

    expect(
      context.login,
    ).toHaveBeenCalledTimes(
      1,
    )

    expect(
      context.login,
    ).toHaveBeenCalledWith({
      email: 'farhad@example.test',
      password: 'SecurePassword1!',
    })

    expect(
      await screen.findByRole(
        'heading',
        {
          name:
            /^dashboard$/i,
        },
      ),
    ).toBeInTheDocument()
  })

  it('shows controlled client-side validation errors without submitting invalid forms', async () => {
    const user = userEvent.setup()
    const context = createAuthContext()

    renderAuthRoute(
      '/register',
      context,
    )

    await user.click(
      screen.getByRole(
        'button',
        {
          name:
            /^create account$/i,
        },
      ),
    )

    expect(
      await screen.findByText(
        /name must be at least 2 characters/i,
      ),
    ).toBeInTheDocument()

    expect(
      screen.getByText(
        /username must be at least 3 characters/i,
      ),
    ).toBeInTheDocument()

    expect(
      screen.getByText(
        /email address is required/i,
      ),
    ).toBeInTheDocument()

    expect(
      screen.getByText(
        /password must be at least 12 characters/i,
      ),
    ).toBeInTheDocument()

    expect(
      screen.getByText(
        /password confirmation is required/i,
      ),
    ).toBeInTheDocument()

    expect(
      context.register,
    ).not.toHaveBeenCalled()
  })
})