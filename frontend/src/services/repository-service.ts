import { apiClient } from '../lib/api'
import type {
  CreateRepositoryInput,
  RepositoryCollectionResponse,
  RepositoryMutationResponse,
  RepositoryResponse,
  UpdateRepositoryInput,
} from '../types/repository'

export async function getRepositories(
  page = 1,
): Promise<RepositoryCollectionResponse> {
  const response =
    await apiClient.get<RepositoryCollectionResponse>(
      '/repositories',
      {
        params: {
          page,
        },
      },
    )

  return response.data
}

export async function getRepository(
  repositoryId: number,
): Promise<RepositoryResponse> {
  const response =
    await apiClient.get<RepositoryResponse>(
      `/repositories/${repositoryId}`,
    )

  return response.data
}

export async function createRepository(
  input: CreateRepositoryInput,
): Promise<RepositoryMutationResponse> {
  const response =
    await apiClient.post<RepositoryMutationResponse>(
      '/repositories',
      input,
    )

  return response.data
}

export async function updateRepository(
  repositoryId: number,
  input: UpdateRepositoryInput,
): Promise<RepositoryMutationResponse> {
  const response =
    await apiClient.patch<RepositoryMutationResponse>(
      `/repositories/${repositoryId}`,
      input,
    )

  return response.data
}