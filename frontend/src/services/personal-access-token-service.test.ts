import {
  beforeEach,
  describe,
  expect,
  it,
  vi,
} from 'vitest'
import { apiClient } from '../lib/api'
import type {
  CreatePersonalAccessTokenInput,
  CreatePersonalAccessTokenResponse,
  PersonalAccessToken,
  PersonalAccessTokenCollectionResponse,
} from '../types/personal-access-token'
import {
  createPersonalAccessToken,
  getPersonalAccessTokens,
  revokePersonalAccessToken,
} from './personal-access-token-service'

vi.mock('../lib/api', () => ({
  apiClient: {
    delete: vi.fn(),
    get: vi.fn(),
    post: vi.fn(),
  },
}))

const token: PersonalAccessToken = {
  id: 12,
  name: 'Development laptop',
  revoked_at: null,
  created_at: '2026-08-20T10:00:00.000Z',
  updated_at: '2026-08-20T10:00:00.000Z',
}

describe('personal access token service', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('requests the personal access token list', async () => {
    const response: PersonalAccessTokenCollectionResponse = {
      data: [token],
    }

    vi.mocked(
      apiClient.get,
    ).mockResolvedValue({
      data: response,
    })

    await expect(
      getPersonalAccessTokens(),
    ).resolves.toEqual(response)

    expect(
      apiClient.get,
    ).toHaveBeenCalledWith(
      '/personal-access-tokens',
    )
  })

  it('creates a personal access token using only its name', async () => {
    const input: CreatePersonalAccessTokenInput = {
      name: 'Development laptop',
    }

    const response: CreatePersonalAccessTokenResponse = {
      message: 'Personal access token created successfully.',
      token,
      plain_text_token:
        'gitforge_pat_aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa_bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
    }

    vi.mocked(
      apiClient.post,
    ).mockResolvedValue({
      data: response,
    })

    await expect(
      createPersonalAccessToken(input),
    ).resolves.toEqual(response)

    expect(
      apiClient.post,
    ).toHaveBeenCalledWith(
      '/personal-access-tokens',
      {
        name: 'Development laptop',
      },
    )
  })

  it('revokes a personal access token', async () => {
    const response = {
      message: 'Personal access token revoked successfully.',
    }

    vi.mocked(
      apiClient.delete,
    ).mockResolvedValue({
      data: response,
    })

    await expect(
      revokePersonalAccessToken(12),
    ).resolves.toEqual(response)

    expect(
      apiClient.delete,
    ).toHaveBeenCalledWith(
      '/personal-access-tokens/12',
    )
  })
})