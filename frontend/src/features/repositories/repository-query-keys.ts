export const repositoryQueryKeys = {
  all: ['repositories'] as const,

  lists: () => [
    ...repositoryQueryKeys.all,
    'list',
  ] as const,

  list: (page: number) => [
    ...repositoryQueryKeys.lists(),
    {
      page,
    },
  ] as const,

  details: () => [
    ...repositoryQueryKeys.all,
    'detail',
  ] as const,

  detail: (repositoryId: number) => [
    ...repositoryQueryKeys.details(),
    repositoryId,
  ] as const,

  browser: (repositoryId: number) => [
    ...repositoryQueryKeys.detail(repositoryId),
    'browser',
  ] as const,

  branches: (repositoryId: number) => [
    ...repositoryQueryKeys.browser(repositoryId),
    'branches',
  ] as const,

  commits: (
    repositoryId: number,
    branch: string,
    limit: number,
  ) => [
    ...repositoryQueryKeys.browser(repositoryId),
    'commits',
    {
      branch,
      limit,
    },
  ] as const,

  tree: (
    repositoryId: number,
    branch: string,
    path: string,
  ) => [
    ...repositoryQueryKeys.browser(repositoryId),
    'tree',
    {
      branch,
      path,
    },
  ] as const,

  blob: (
    repositoryId: number,
    branch: string,
    path: string,
  ) => [
    ...repositoryQueryKeys.browser(repositoryId),
    'blob',
    {
      branch,
      path,
    },
  ] as const,
}