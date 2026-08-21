export interface RepositoryBranch {
  name: string
  commit: string
  committed_at: string | null
  subject: string
}

export interface RepositoryBranchesResponse {
  data: RepositoryBranch[]
  default_branch: string | null
}

export interface RepositoryCommitAuthor {
  name: string
  email: string
}

export interface RepositoryCommit {
  hash: string
  parents: string[]
  author: RepositoryCommitAuthor
  authored_at: string
  subject: string
}

export interface RepositoryCommitsResponse {
  data: RepositoryCommit[]
  branch: string
}

export interface RepositoryCommitDetails {
  id: string
  tree: string
  parents: string[]
  author_name: string
  author_email: string
  authored_at: string
  committer_name: string
  committer_email: string
  committed_at: string
  message: string
}

export interface RepositoryCommitDetailsResponse {
  commit: RepositoryCommitDetails
}

export type RepositoryTreeEntryType =
  | 'directory'
  | 'file'
  | 'submodule'

export interface RepositoryTreeEntry {
  name: string
  path: string
  type: RepositoryTreeEntryType
  mode: string
  object_id: string
  size: number | null
}

export interface RepositoryTreeResponse {
  data: RepositoryTreeEntry[]
  branch: string
  path: string
}

export interface RepositoryBlob {
  path: string
  size: number
  content: string
  encoding: 'utf-8'
}

export interface RepositoryBlobResponse {
  data: RepositoryBlob
  branch: string
}

export interface RepositoryBrowserParameters {
  branch?: string
  path?: string
}

export interface RepositoryCommitParameters {
  branch?: string
  limit?: number
}