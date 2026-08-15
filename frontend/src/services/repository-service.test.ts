import { beforeEach, describe, expect, it, vi } from 'vitest'
import { apiClient } from '../lib/api'
import type {
  Repository,
  RepositoryCollectionResponse,
  RepositoryMutationResponse,
} from '../types/repository'
import {
  archiveRepository,
  createRepository,
  getRepositories,
  getRepository,
  scheduleRepositoryDeletion,
  unarchiveRepository,
  updateRepository,
} from './repository-service'

vi.mock('../lib/api', () => ({
  apiClient: {
    delete: vi.fn(),
    get: vi.fn(),
    patch: vi.fn(),
    post: vi.fn(),
  },
}))

const repository: Repository = {
  id: 36,
  name: 'test-project',
  slug: 'test-project',
  full_name: 'farhad-dev/test-project',
  description: 'Test repository',
  visibility: 'public',
  status: 'active',
  default_branch: null,
  is_empty: true,
  is_writable: true,
  is_archived: false,
  owner: {
    id: 1,
    name: 'Farhad Valizadegan',
    username: 'farhad-dev',
  },
  archived_at: null,
  created_at: '2026-08-15T10:00:00.000Z',
  updated_at: '2026-08-15T10:00:00.000Z',
  links: {
    api: 'http://127.0.0.1:8000/api/v1/repositories/36',
  },
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

  it('requests a paginated repository list', async () => {
    vi.mocked(apiClient.get).mockResolvedValue({
      data: collectionResponse,
    })

    await expect(getRepositories(2)).resolves.toEqual(
      collectionResponse,
    )

    expect(apiClient.get).toHaveBeenCalledWith(
      '/repositories',
      {
        params: {
          page: 2,
        },
      },
    )
  })

  it('requests a repository by identifier', async () => {
    vi.mocked(apiClient.get).mockResolvedValue({
      data: {
        data: repository,
      },
    })

    await expect(getRepository(36)).resolves.toEqual({
      data: repository,
    })

    expect(apiClient.get).toHaveBeenCalledWith(
      '/repositories/36',
    )
  })

  it('creates a repository', async () => {
    const response: RepositoryMutationResponse = {
      message: 'Repository created successfully.',
      repository,
    }

    vi.mocked(apiClient.post).mockResolvedValue({
      data: response,
    })

    const input = {
      name: 'test-project',
      description: 'Test repository',
      visibility: 'public' as const,
    }

    await expect(createRepository(input)).resolves.toEqual(
      response,
    )

    expect(apiClient.post).toHaveBeenCalledWith(
      '/repositories',
      input,
    )
  })

  it('updates repository metadata', async () => {
    const input = {
      name: 'renamed-project',
      description: 'Updated description',
      visibility: 'private' as const,
    }

    const response: RepositoryMutationResponse = {
      message: 'Repository updated successfully.',
      repository: {
        ...repository,
        ...input,
        slug: 'renamed-project',
        full_name: 'farhad-dev/renamed-project',
      },
    }

    vi.mocked(apiClient.patch).mockResolvedValue({
      data: response,
    })

    await expect(
      updateRepository(36, input),
    ).resolves.toEqual(response)

    expect(apiClient.patch).toHaveBeenCalledWith(
      '/repositories/36',
      input,
    )
  })

  it('archives and restores a repository', async () => {
    const archivedResponse: RepositoryMutationResponse = {
      message: 'Repository archived successfully.',
      repository: {
        ...repository,
        status: 'archived',
        is_archived: true,
        is_writable: false,
      },
    }

    const restoredResponse: RepositoryMutationResponse = {
      message: 'Repository unarchived successfully.',
      repository,
    }

    vi.mocked(apiClient.post)
      .mockResolvedValueOnce({
        data: archivedResponse,
      })
      .mockResolvedValueOnce({
        data: restoredResponse,
      })

    await expect(
      archiveRepository(36),
    ).resolves.toEqual(archivedResponse)

    expect(apiClient.post).toHaveBeenNthCalledWith(
      1,
      '/repositories/36/archive',
    )

    await expect(
      unarchiveRepository(36),
    ).resolves.toEqual(restoredResponse)

    expect(apiClient.post).toHaveBeenNthCalledWith(
      2,
      '/repositories/36/unarchive',
    )
  })

  it('schedules repository deletion', async () => {
    vi.mocked(apiClient.delete).mockResolvedValue({
      data: {
        message: 'Repository deletion scheduled.',
      },
    })

    await expect(
      scheduleRepositoryDeletion(36),
    ).resolves.toEqual({
      message: 'Repository deletion scheduled.',
    })

    expect(apiClient.delete).toHaveBeenCalledWith(
      '/repositories/36',
    )
  })
})