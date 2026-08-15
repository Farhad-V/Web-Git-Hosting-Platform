import { apiClient } from '../lib/api'
import type {
  RepositoryBlobResponse,
  RepositoryBranchesResponse,
  RepositoryBrowserParameters,
  RepositoryCommitParameters,
  RepositoryCommitsResponse,
  RepositoryTreeResponse,
} from '../types/repository-browser'

export async function getRepositoryBranches(
  repositoryId: number,
): Promise<RepositoryBranchesResponse> {
  const response =
    await apiClient.get<RepositoryBranchesResponse>(
      `/repositories/${repositoryId}/branches`,
    )

  return response.data
}

export async function getRepositoryCommits(
  repositoryId: number,
  parameters: RepositoryCommitParameters = {},
): Promise<RepositoryCommitsResponse> {
  const response =
    await apiClient.get<RepositoryCommitsResponse>(
      `/repositories/${repositoryId}/commits`,
      {
        params: parameters,
      },
    )

  return response.data
}

export async function getRepositoryTree(
  repositoryId: number,
  parameters: RepositoryBrowserParameters = {},
): Promise<RepositoryTreeResponse> {
  const response =
    await apiClient.get<RepositoryTreeResponse>(
      `/repositories/${repositoryId}/tree`,
      {
        params: parameters,
      },
    )

  return response.data
}

export async function getRepositoryBlob(
  repositoryId: number,
  parameters: RepositoryBrowserParameters,
): Promise<RepositoryBlobResponse> {
  const response =
    await apiClient.get<RepositoryBlobResponse>(
      `/repositories/${repositoryId}/blob`,
      {
        params: parameters,
      },
    )

  return response.data
}