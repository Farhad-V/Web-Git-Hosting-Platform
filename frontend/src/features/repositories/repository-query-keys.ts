export const repositoryQueryKeys = {
  all: ['repositories'] as const,
  lists: () => [...repositoryQueryKeys.all, 'list'] as const,
  list: (page: number) => [
    ...repositoryQueryKeys.lists(),
    {
      page,
    },
  ] as const,
  details: () => [...repositoryQueryKeys.all, 'detail'] as const,
  detail: (repositoryId: number) => [
    ...repositoryQueryKeys.details(),
    repositoryId,
  ] as const,
}