export const personalAccessTokenQueryKeys = {
  all: ['personal-access-tokens'] as const,
  list: () =>
    [...personalAccessTokenQueryKeys.all, 'list'] as const,
}