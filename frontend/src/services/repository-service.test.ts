import {
  beforeEach,
  describe,
  expect,
  it,
  vi,
} from 'vitest'
import { apiClient } from '../lib/api'
import type {
  Repository,
  RepositoryCollectionResponse,
  RepositoryMutationResponse,
} from '../types/repository'
import {
  createRepository,
  getRepositories,
  getRepository,
  updateRepository,
} from './repository-service'

vi.mock('../lib/api', () => ({
  apiClient: {
    get: vi.fn(),
    patch: vi.fn(),
    post: vi.fn(),
  },
}))

const repository: Repository = {
  id: 36,
  name: 'test-project',
  full_name: 'farhad-dev/test-project',
  visibility: 'public',
  owner: {
    id: 1,
    name: 'Farhad Valizadegan',
    username: 'farhad-dev',
  },
  created_at: '2026-08-20T10:00:00.000Z',
  updated_at: '2026-08-20T10:00:00.000Z',
}

const collectionResponse: RepositoryCollectionResponse = {
  data: [repository],
  links: {
    first: 'http://127.0.0.1:8000/api/v1/repositories?page=1',
    last: 'http://127.0.0.1:8000/api/v1/repositories?page=1',
    prev: null,
    next: null,
  },
  meta: {
    current_page: 1,
    from: 1,
    last_page: 1,
    links: [],
    path: 'http://127.0.0.1:8000/api/v1/repositories',
    per_page: 20,
    to: 1,
    total: 1,
  },
}

describe('repository service', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('requests the owned repository list', async () => {
    vi.mocked(
      apiClient.get,
    ).mockResolvedValue({
      data: collectionResponse,
    })

    await expect(
      getRepositories(2),
    ).resolves.toEqual(
      collectionResponse,
    )

    expect(
      apiClient.get,
    ).toHaveBeenCalledWith(
      '/repositories',
      {
        params: {
          page: 2,
        },
      },
    )
  })

  it('requests repository metadata', async () => {
    vi.mocked(
      apiClient.get,
    ).mockResolvedValue({
      data: {
        data: repository,
      },
    })

    await expect(
      getRepository(36),
    ).resolves.toEqual({
      data: repository,
    })

    expect(
      apiClient.get,
    ).toHaveBeenCalledWith(
      '/repositories/36',
    )
  })

  it('creates repository using name and visibility only', async () => {
    const input = {
      name: 'test-project',
      visibility: 'public' as const,
    }

    const response: RepositoryMutationResponse = {
      message: 'Repository created successfully.',
      repository,
    }

    vi.mocked(
      apiClient.post,
    ).mockResolvedValue({
      data: response,
    })

    await expect(
      createRepository(input),
    ).resolves.toEqual(
      response,
    )

    expect(
      apiClient.post,
    ).toHaveBeenCalledWith(
      '/repositories',
      input,
    )
  })

  it('updates repository visibility only', async () => {
    const response: RepositoryMutationResponse = {
      message: 'Repository visibility updated successfully.',
      repository: {
        ...repository,
        visibility: 'private',
      },
    }

    vi.mocked(
      apiClient.patch,
    ).mockResolvedValue({
      data: response,
    })

    await expect(
      updateRepository(
        36,
        {
          visibility: 'private',
        },
      ),
    ).resolves.toEqual(
      response,
    )

    expect(
      apiClient.patch,
    ).toHaveBeenCalledWith(
      '/repositories/36',
      {
        visibility: 'private',
      },
    )
  })
})