import { apiClient } from '../lib/api'
import type {
  CreatePersonalAccessTokenInput,
  CreatePersonalAccessTokenResponse,
  PersonalAccessTokenCollectionResponse,
  RevokePersonalAccessTokenResponse,
} from '../types/personal-access-token'

export async function getPersonalAccessTokens(): Promise<PersonalAccessTokenCollectionResponse> {
  const response =
    await apiClient.get<PersonalAccessTokenCollectionResponse>(
      '/personal-access-tokens',
    )

  return response.data
}

export async function createPersonalAccessToken(
  input: CreatePersonalAccessTokenInput,
): Promise<CreatePersonalAccessTokenResponse> {
  const response =
    await apiClient.post<CreatePersonalAccessTokenResponse>(
      '/personal-access-tokens',
      input,
    )

  return response.data
}

export async function revokePersonalAccessToken(
  tokenId: number,
): Promise<RevokePersonalAccessTokenResponse> {
  const response =
    await apiClient.delete<RevokePersonalAccessTokenResponse>(
      `/personal-access-tokens/${tokenId}`,
    )

  return response.data
}