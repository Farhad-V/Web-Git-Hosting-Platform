export interface PersonalAccessToken {
  id: number
  name: string
  revoked_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface PersonalAccessTokenCollectionResponse {
  data: PersonalAccessToken[]
}

export interface CreatePersonalAccessTokenInput {
  name: string
}

export interface CreatePersonalAccessTokenResponse {
  message: string
  token: PersonalAccessToken
  plain_text_token: string
}

export interface RevokePersonalAccessTokenResponse {
  message: string
}