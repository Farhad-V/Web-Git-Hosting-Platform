export function getGitCloneUrl(fullName: string): string {
  const baseUrl = import.meta.env.VITE_GIT_BASE_URL?.trim()
    || (import.meta.env.DEV
      ? 'http://127.0.0.1:8000'
      : window.location.origin)

  return `${baseUrl.replace(/\/$/, '')}/${fullName}.git`
}
