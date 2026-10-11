export type RepositoryVisibility =
  | 'public'
  | 'private'

export interface RepositoryOwner {
  id: number
  name: string
  username: string
}

export interface Repository {
  id: number
  name: string
  full_name: string
  visibility: RepositoryVisibility
  owner: RepositoryOwner
  created_at: string | null
  updated_at: string | null
}

export interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

export interface RepositoryCollectionResponse {
  data: Repository[]
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
  meta: {
    current_page: number
    from: number | null
    last_page: number
    links: PaginationLink[]
    path: string
    per_page: number
    to: number | null
    total: number
  }
}

export interface RepositoryResponse {
  data: Repository
}

export interface RepositoryMutationResponse {
  message: string
  repository: Repository
}

export interface CreateRepositoryInput {
  name: string
  visibility: RepositoryVisibility
}

export interface UpdateRepositoryInput {
  name?: string
  visibility: RepositoryVisibility
}