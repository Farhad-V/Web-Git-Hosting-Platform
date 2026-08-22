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
  createPersonalAccessToken,
  getPersonalAccessTokens,
  revokePersonalAccessToken,
} from '../../services/personal-access-token-service'
import type {
  CreatePersonalAccessTokenResponse,
  PersonalAccessToken,
  PersonalAccessTokenCollectionResponse,
} from '../../types/personal-access-token'
import { PersonalAccessTokensPage } from './PersonalAccessTokensPage'

vi.mock(
  '../../services/personal-access-token-service',
  () => ({
    createPersonalAccessToken: vi.fn(),
    getPersonalAccessTokens: vi.fn(),
    revokePersonalAccessToken: vi.fn(),
  }),
)

const activeToken: PersonalAccessToken = {
  id: 1,
  name: 'Development laptop',
  revoked_at: null,
  created_at: '2026-08-22T10:00:00Z',
  updated_at: '2026-08-22T10:00:00Z',
}

const revokedToken: PersonalAccessToken = {
  id: 2,
  name: 'Old workstation',
  revoked_at: '2026-08-22T11:00:00Z',
  created_at: '2026-08-20T10:00:00Z',
  updated_at: '2026-08-22T11:00:00Z',
}

function tokenCollection(
  tokens: PersonalAccessToken[],
): PersonalAccessTokenCollectionResponse {
  return {
    data: tokens,
  }
}

function renderPage() {
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
      <MemoryRouter
        initialEntries={[
          '/settings/access-tokens',
        ]}
      >
        <PersonalAccessTokensPage />
      </MemoryRouter>
    </QueryClientProvider>,
  )
}

describe('PersonalAccessTokensPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('lists safe personal access token metadata without plaintext secrets', async () => {
    vi.mocked(
      getPersonalAccessTokens,
    ).mockResolvedValue(
      tokenCollection([
        activeToken,
        revokedToken,
      ]),
    )

    renderPage()

    expect(
      await screen.findByText(
        'Development laptop',
      ),
    ).toBeInTheDocument()

    expect(
      screen.getByText(
        'Old workstation',
      ),
    ).toBeInTheDocument()

    expect(
      screen.getByText(
        'Active',
      ),
    ).toBeInTheDocument()

    expect(
      screen.getByText(
        'Revoked',
        {
          selector: 'span',
        },
      ),
    ).toBeInTheDocument()

    expect(
      screen.queryByDisplayValue(
        /gitforge_pat_/i,
      ),
    ).not.toBeInTheDocument()

    expect(
      getPersonalAccessTokens,
    ).toHaveBeenCalledTimes(
      1,
    )
  })

  it('creates a personal access token and displays the plaintext token once', async () => {
    const user = userEvent.setup()

    vi.mocked(
      getPersonalAccessTokens,
    ).mockResolvedValue(
      tokenCollection([]),
    )

    const createdToken: PersonalAccessToken = {
      id: 3,
      name: 'Development laptop',
      revoked_at: null,
      created_at: '2026-08-22T15:00:00Z',
      updated_at: '2026-08-22T15:00:00Z',
    }

    const createResponse: CreatePersonalAccessTokenResponse = {
      message: 'Personal access token created successfully.',
      token: createdToken,
      plain_text_token:
        'gitforge_pat_identifier_secret',
    }

    vi.mocked(
      createPersonalAccessToken,
    ).mockResolvedValue(
      createResponse,
    )

    renderPage()

    await screen.findByText(
      /no personal access tokens/i,
    )

    await user.click(
      screen.getByRole(
        'button',
        {
          name:
            /^new token$/i,
        },
      ),
    )

    const dialog = screen.getByRole(
      'dialog',
      {
        name:
          /create personal access token/i,
      },
    )

    await user.type(
      within(
        dialog,
      ).getByLabelText(
        /token name/i,
      ),
      'Development laptop',
    )

    await user.click(
      within(
        dialog,
      ).getByRole(
        'button',
        {
          name:
            /^create token$/i,
        },
      ),
    )

    expect(
      createPersonalAccessToken,
    ).toHaveBeenCalledTimes(
      1,
    )

    expect(
      vi.mocked(
        createPersonalAccessToken,
      ).mock.calls[0]?.[0],
    ).toEqual({
      name: 'Development laptop',
    })

    const createdDialog =
      await screen.findByRole(
        'dialog',
        {
          name:
            /token created/i,
        },
      )

    expect(
      within(
        createdDialog,
      ).getByText(
        /this token is shown only once/i,
      ),
    ).toBeInTheDocument()

    expect(
      within(
        createdDialog,
      ).getByDisplayValue(
        'gitforge_pat_identifier_secret',
      ),
    ).toBeInTheDocument()
  })

  it('shows controlled token-name validation without submitting invalid input', async () => {
    const user = userEvent.setup()

    vi.mocked(
      getPersonalAccessTokens,
    ).mockResolvedValue(
      tokenCollection([]),
    )

    renderPage()

    await screen.findByText(
      /no personal access tokens/i,
    )

    await user.click(
      screen.getByRole(
        'button',
        {
          name:
            /^new token$/i,
        },
      ),
    )

    const dialog = screen.getByRole(
      'dialog',
      {
        name:
          /create personal access token/i,
      },
    )

    await user.click(
      within(
        dialog,
      ).getByRole(
        'button',
        {
          name:
            /^create token$/i,
        },
      ),
    )

    expect(
      await within(
        dialog,
      ).findByText(
        /token name is required/i,
      ),
    ).toBeInTheDocument()

    expect(
      createPersonalAccessToken,
    ).not.toHaveBeenCalled()
  })

  it('revokes an active personal access token through the confirmation dialog', async () => {
    const user = userEvent.setup()

    vi.mocked(
      getPersonalAccessTokens,
    ).mockResolvedValue(
      tokenCollection([
        activeToken,
      ]),
    )

    vi.mocked(
      revokePersonalAccessToken,
    ).mockResolvedValue({
      message:
        'Personal access token revoked successfully.',
    })

    renderPage()

    expect(
      await screen.findByText(
        'Development laptop',
      ),
    ).toBeInTheDocument()

    await user.click(
      screen.getByRole(
        'button',
        {
          name:
            /^revoke$/i,
        },
      ),
    )

    const dialog = screen.getByRole(
      'dialog',
      {
        name:
          /revoke access token/i,
      },
    )

    expect(
      within(
        dialog,
      ).getByText(
        /development laptop/i,
      ),
    ).toBeInTheDocument()

    await user.click(
      within(
        dialog,
      ).getByRole(
        'button',
        {
          name:
            /^revoke token$/i,
        },
      ),
    )

    expect(
      revokePersonalAccessToken,
    ).toHaveBeenCalledTimes(
      1,
    )

    expect(
      vi.mocked(
        revokePersonalAccessToken,
      ).mock.calls[0]?.[0],
    ).toBe(
      1,
    )
  })
})