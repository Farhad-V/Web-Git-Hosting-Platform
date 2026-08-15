export type RepositoryTokenAbility =
  | 'repository:read'
  | 'repository:write'

export interface PersonalAccessToken {
  id: number
  name: string
  abilities: RepositoryTokenAbility[]
  last_used_at: string | null
  expires_at: string | null
  is_expired: boolean
  created_at: string | null
}

export interface PersonalAccessTokenCollectionResponse {
  data: PersonalAccessToken[]
}

export interface CreatePersonalAccessTokenInput {
  name: string
  current_password: string
  abilities: RepositoryTokenAbility[]
  expires_at: string | null
}

export interface CreatePersonalAccessTokenResponse {
  message: string
  token: PersonalAccessToken
  plain_text_token: string
}

export interface RevokePersonalAccessTokenResponse {
  message: string
}