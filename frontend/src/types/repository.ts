export type RepositoryVisibility = 'public' | 'private'

export type RepositoryStatus =
  | 'active'
  | 'archived'
  | 'deletion_pending'
  | 'deletion_failed'

export interface RepositoryOwner {
  id: number
  name: string
  username: string
}

export interface RepositoryLinks {
  api: string
}

export interface Repository {
  id: number
  name: string
  slug: string
  full_name: string
  description: string | null
  visibility: RepositoryVisibility
  status: RepositoryStatus
  default_branch: string | null
  is_empty: boolean
  is_writable: boolean
  is_archived: boolean
  owner: RepositoryOwner
  archived_at: string | null
  created_at: string | null
  updated_at: string | null
  links: RepositoryLinks
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
  description: string | null
  visibility: RepositoryVisibility
}

export interface UpdateRepositoryInput {
  name?: string
  description?: string | null
  visibility?: RepositoryVisibility
}

export interface DeleteRepositoryResponse {
  message: string
}