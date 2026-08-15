import { beforeEach, describe, expect, it, vi } from 'vitest'
import { apiClient } from '../lib/api'
import {
  getRepositoryBlob,
  getRepositoryBranches,
  getRepositoryCommits,
  getRepositoryTree,
} from './repository-browser-service'

vi.mock('../lib/api', () => ({
  apiClient: {
    get: vi.fn(),
  },
}))

const mockedGet = vi.mocked(apiClient.get)

describe('repository browser service', () => {
  beforeEach(() => {
    mockedGet.mockReset()
  })

  it('requests repository branches', async () => {
    const payload = {
      data: [
        {
          name: 'main',
          commit: 'a'.repeat(40),
          committed_at: '2026-08-15T12:00:00+00:00',
          subject: 'Initial commit',
        },
      ],
      default_branch: 'main',
    }

    mockedGet.mockResolvedValueOnce({
      data: payload,
    })

    await expect(
      getRepositoryBranches(37),
    ).resolves.toEqual(payload)

    expect(mockedGet).toHaveBeenCalledWith(
      '/repositories/37/branches',
    )
  })

  it('requests repository commits with parameters', async () => {
    const payload = {
      data: [],
      branch: 'main',
    }

    mockedGet.mockResolvedValueOnce({
      data: payload,
    })

    await expect(
      getRepositoryCommits(37, {
        branch: 'main',
        limit: 25,
      }),
    ).resolves.toEqual(payload)

    expect(mockedGet).toHaveBeenCalledWith(
      '/repositories/37/commits',
      {
        params: {
          branch: 'main',
          limit: 25,
        },
      },
    )
  })

  it('requests a repository tree', async () => {
    const payload = {
      data: [],
      branch: 'main',
      path: 'src',
    }

    mockedGet.mockResolvedValueOnce({
      data: payload,
    })

    await expect(
      getRepositoryTree(37, {
        branch: 'main',
        path: 'src',
      }),
    ).resolves.toEqual(payload)

    expect(mockedGet).toHaveBeenCalledWith(
      '/repositories/37/tree',
      {
        params: {
          branch: 'main',
          path: 'src',
        },
      },
    )
  })

  it('requests a repository blob', async () => {
    const payload = {
      data: {
        path: 'README.md',
        size: 14,
        content: '# Repository\n',
        encoding: 'utf-8' as const,
      },
      branch: 'main',
    }

    mockedGet.mockResolvedValueOnce({
      data: payload,
    })

    await expect(
      getRepositoryBlob(37, {
        branch: 'main',
        path: 'README.md',
      }),
    ).resolves.toEqual(payload)

    expect(mockedGet).toHaveBeenCalledWith(
      '/repositories/37/blob',
      {
        params: {
          branch: 'main',
          path: 'README.md',
        },
      },
    )
  })
})