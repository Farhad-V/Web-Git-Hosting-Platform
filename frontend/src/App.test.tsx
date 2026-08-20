import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import {
  beforeEach,
  describe,
  expect,
  it,
  vi,
} from 'vitest'
import App from './App'
import { AuthProvider } from './features/auth/AuthProvider'
import {
  getCurrentUser,
  login,
  logout,
  register,
} from './services/auth-service'

vi.mock('./services/auth-service', () => ({
  getCurrentUser: vi.fn(),
  login: vi.fn(),
  logout: vi.fn(),
  register: vi.fn(),
}))

function unauthenticatedError() {
  return {
    isAxiosError: true,
    response: {
      status: 401,
      data: {
        message: 'Unauthenticated.',
      },
    },
  }
}

function renderApp(
  initialRoute = '/',
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
      <MemoryRouter initialEntries={[initialRoute]}>
        <AuthProvider>
          <App />
        </AuthProvider>
      </MemoryRouter>
    </QueryClientProvider>,
  )
}

describe('App', () => {
  beforeEach(() => {
    vi.clearAllMocks()

    vi.mocked(
      getCurrentUser,
    ).mockRejectedValue(
      unauthenticatedError(),
    )

    vi.mocked(login).mockReset()
    vi.mocked(register).mockReset()
    vi.mocked(logout).mockReset()
  })

  it('renders the GitForge landing page', () => {
    renderApp()

    expect(
      screen.getByRole('heading', {
        name: /your code\.\s*your infrastructure\./i,
      }),
    ).toBeInTheDocument()

    expect(
      screen.getByRole('link', {
        name: /create your account/i,
      }),
    ).toHaveAttribute(
      'href',
      '/register',
    )

    expect(
      screen.getByText(
        /self-hosted and under your control/i,
      ),
    ).toBeInTheDocument()
  })

  it('renders the sign-in route after guest session bootstrap', async () => {
    renderApp('/login')

    expect(
      screen.getByText(
        /verifying your session/i,
      ),
    ).toBeInTheDocument()

    expect(
      await screen.findByRole('heading', {
        name: /welcome back/i,
      }),
    ).toBeInTheDocument()

    expect(
      screen.getByText(
        /use your account credentials to access repositories and settings/i,
      ),
    ).toBeInTheDocument()

    expect(
      screen.getByRole('button', {
        name: /^sign in$/i,
      }),
    ).toBeInTheDocument()

    expect(
      screen.getByRole('link', {
        name: /create an account/i,
      }),
    ).toHaveAttribute(
      'href',
      '/register',
    )

    expect(
      getCurrentUser,
    ).toHaveBeenCalledTimes(1)
  })
})