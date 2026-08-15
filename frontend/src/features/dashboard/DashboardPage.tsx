import { useMemo, useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import {
  BookOpen,
  GitBranch,
  GitFork,
  KeyRound,
  LoaderCircle,
  LogOut,
  Plus,
  Search,
  Settings,
  UserRound,
} from 'lucide-react'
import { Link } from 'react-router-dom'
import { getApiErrorMessage } from '../../lib/api-errors'
import { getRepositories } from '../../services/repository-service'
import { useAuth } from '../auth/auth-context'
import { CreateRepositoryDialog } from '../repositories/CreateRepositoryDialog'
import { RepositoryCard } from '../repositories/RepositoryCard'
import { repositoryQueryKeys } from '../repositories/repository-query-keys'

export function DashboardPage() {
  const { logout, user } = useAuth()

  const [page, setPage] = useState(1)
  const [searchTerm, setSearchTerm] = useState('')
  const [isCreateDialogOpen, setIsCreateDialogOpen] =
    useState(false)
  const [isLoggingOut, setIsLoggingOut] = useState(false)
  const [logoutError, setLogoutError] = useState<string | null>(null)

  const repositoriesQuery = useQuery({
    queryKey: repositoryQueryKeys.list(page),
    queryFn: () => getRepositories(page),
  })

  const repositoryData = repositoriesQuery.data?.data

  const repositories = useMemo(
    () => repositoryData ?? [],
    [repositoryData],
  )

  const filteredRepositories = useMemo(() => {
    const normalizedSearchTerm = searchTerm.trim().toLowerCase()

    if (normalizedSearchTerm === '') {
      return repositories
    }

    return repositories.filter((repository) => {
      const searchableValues = [
        repository.name,
        repository.slug,
        repository.full_name,
        repository.description ?? '',
        repository.owner.name,
        repository.owner.username,
      ]

      return searchableValues.some((value) =>
        value.toLowerCase().includes(normalizedSearchTerm),
      )
    })
  }, [repositories, searchTerm])

  const publicRepositoriesOnPage = repositories.filter(
    (repository) => repository.visibility === 'public',
  ).length

  async function handleLogout(): Promise<void> {
    setIsLoggingOut(true)
    setLogoutError(null)

    try {
      await logout()
    } catch (error) {
      setLogoutError(
        getApiErrorMessage(
          error,
          'Your local session was cleared, but the server could not be reached.',
        ),
      )
    } finally {
      setIsLoggingOut(false)
    }
  }

  function handleRepositoryCreated(): void {
    setIsCreateDialogOpen(false)
    setSearchTerm('')
    setPage(1)
  }

  const totalRepositories =
    repositoriesQuery.data?.meta.total ?? 0

  const currentPage =
    repositoriesQuery.data?.meta.current_page ?? page

  const lastPage =
    repositoriesQuery.data?.meta.last_page ?? 1

  return (
    <div className="min-h-screen bg-[#0d1117] text-slate-200">
      <header className="sticky top-0 z-30 border-b border-white/10 bg-[#0d1117]/90 backdrop-blur-xl">
        <div className="mx-auto flex h-16 max-w-7xl items-center gap-3 px-5 lg:gap-5 lg:px-8">
          <Link
            className="flex shrink-0 items-center gap-2 font-semibold text-white"
            to="/dashboard"
          >
            <span className="grid size-9 place-items-center rounded-lg bg-blue-600">
              <GitBranch aria-hidden="true" className="size-5" />
            </span>

            <span className="hidden sm:inline">GitForge</span>
          </Link>

          <label className="relative ml-auto hidden w-full max-w-md sm:block">
            <span className="sr-only">Search repositories</span>

            <Search
              aria-hidden="true"
              className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"
            />

            <input
              className="h-9 w-full rounded-lg border border-white/10 bg-white/5 pl-9 pr-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
              onChange={(event) => setSearchTerm(event.target.value)}
              placeholder="Search loaded repositories"
              type="search"
              value={searchTerm}
            />
          </label>

          <Link
            className="inline-flex shrink-0 items-center gap-2 rounded-lg border border-white/10 px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
            to="/settings/access-tokens"
          >
            <KeyRound aria-hidden="true" className="size-4" />

            <span className="hidden lg:inline">Access tokens</span>
            <span className="lg:hidden">Tokens</span>
          </Link>

          <button
            className="inline-flex shrink-0 items-center gap-2 rounded-lg border border-white/10 px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white disabled:opacity-60"
            disabled={isLoggingOut}
            onClick={handleLogout}
            type="button"
          >
            {isLoggingOut ? (
              <LoaderCircle
                aria-hidden="true"
                className="size-4 animate-spin"
              />
            ) : (
              <LogOut aria-hidden="true" className="size-4" />
            )}

            <span className="hidden md:inline">
              {isLoggingOut ? 'Signing out...' : 'Sign out'}
            </span>
          </button>
        </div>
      </header>

      <main className="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        {logoutError !== null && (
          <div
            className="mb-6 rounded-lg border border-amber-400/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-200"
            role="alert"
          >
            {logoutError}
          </div>
        )}

        <section className="flex flex-col gap-6 border-b border-white/10 pb-8 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p className="text-sm text-blue-400">Dashboard</p>

            <h1 className="mt-1 text-3xl font-bold tracking-tight text-white">
              Welcome, {user?.name}
            </h1>

            <p className="mt-2 text-slate-400">
              Manage repositories in the{' '}
              <span className="font-medium text-slate-300">
                @{user?.username}
              </span>{' '}
              namespace.
            </p>
          </div>

          <button
            className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
            onClick={() => setIsCreateDialogOpen(true)}
            type="button"
          >
            <Plus aria-hidden="true" className="size-4" />
            New repository
          </button>
        </section>

        <section className="grid gap-5 py-8 md:grid-cols-3">
          <article className="rounded-xl border border-white/10 bg-[#161b22] p-5">
            <div className="flex items-center justify-between">
              <span className="grid size-10 place-items-center rounded-lg bg-blue-500/10 text-blue-300">
                <GitFork aria-hidden="true" className="size-5" />
              </span>

              <span className="text-2xl font-bold text-white">
                {repositoriesQuery.isPending ? '—' : totalRepositories}
              </span>
            </div>

            <p className="mt-4 text-sm text-slate-400">
              Visible repositories
            </p>
          </article>

          <article className="rounded-xl border border-white/10 bg-[#161b22] p-5">
            <div className="flex items-center justify-between">
              <span className="grid size-10 place-items-center rounded-lg bg-blue-500/10 text-blue-300">
                <BookOpen aria-hidden="true" className="size-5" />
              </span>

              <span className="text-2xl font-bold text-white">
                {repositoriesQuery.isPending
                  ? '—'
                  : publicRepositoriesOnPage}
              </span>
            </div>

            <p className="mt-4 text-sm text-slate-400">
              Public on this page
            </p>
          </article>

          <article className="rounded-xl border border-white/10 bg-[#161b22] p-5">
            <div className="flex items-center justify-between">
              <span className="grid size-10 place-items-center rounded-lg bg-blue-500/10 text-blue-300">
                <UserRound aria-hidden="true" className="size-5" />
              </span>

              <span className="truncate pl-4 text-xl font-bold text-white">
                @{user?.username ?? ''}
              </span>
            </div>

            <p className="mt-4 text-sm text-slate-400">
              Account
            </p>
          </article>
        </section>

        <section className="overflow-hidden rounded-xl border border-white/10 bg-[#161b22]">
          <div className="flex flex-col gap-4 border-b border-white/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 className="font-semibold text-white">
                Available repositories
              </h2>

              <p className="mt-1 text-sm text-slate-500">
                Public repositories and repositories owned by you.
              </p>
            </div>

            <div className="flex items-center gap-3">
              {repositoriesQuery.isFetching
                && !repositoriesQuery.isPending && (
                  <LoaderCircle
                    aria-label="Refreshing repositories"
                    className="size-4 animate-spin text-blue-400"
                  />
                )}

              <Settings
                aria-hidden="true"
                className="size-5 text-slate-500"
              />
            </div>
          </div>

          <div className="p-5">
            {repositoriesQuery.isPending && (
              <div className="grid place-items-center py-16">
                <div className="flex items-center gap-3 text-sm text-slate-400">
                  <LoaderCircle
                    aria-hidden="true"
                    className="size-5 animate-spin text-blue-400"
                  />
                  Loading repositories...
                </div>
              </div>
            )}

            {repositoriesQuery.isError && (
              <div
                className="rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-4 text-sm text-red-300"
                role="alert"
              >
                <p>
                  {getApiErrorMessage(
                    repositoriesQuery.error,
                    'Unable to load repositories.',
                  )}
                </p>

                <button
                  className="mt-3 font-semibold text-red-200 underline underline-offset-4"
                  onClick={() => repositoriesQuery.refetch()}
                  type="button"
                >
                  Try again
                </button>
              </div>
            )}

            {repositoriesQuery.isSuccess
              && repositories.length === 0 && (
                <div className="grid place-items-center py-16 text-center">
                  <span className="grid size-14 place-items-center rounded-full border border-white/10 bg-white/[0.03] text-slate-400">
                    <GitBranch
                      aria-hidden="true"
                      className="size-6"
                    />
                  </span>

                  <h3 className="mt-4 font-semibold text-white">
                    No repositories yet
                  </h3>

                  <p className="mt-2 max-w-sm text-sm leading-6 text-slate-500">
                    Create your first repository to start managing source
                    code with GitForge.
                  </p>

                  <button
                    className="mt-5 inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10"
                    onClick={() => setIsCreateDialogOpen(true)}
                    type="button"
                  >
                    <Plus aria-hidden="true" className="size-4" />
                    Create repository
                  </button>
                </div>
              )}

            {repositoriesQuery.isSuccess
              && repositories.length > 0
              && filteredRepositories.length === 0 && (
                <div className="py-14 text-center">
                  <Search
                    aria-hidden="true"
                    className="mx-auto size-7 text-slate-500"
                  />

                  <h3 className="mt-3 font-semibold text-white">
                    No matching repositories
                  </h3>

                  <p className="mt-2 text-sm text-slate-500">
                    Try a different search term.
                  </p>
                </div>
              )}

            {repositoriesQuery.isSuccess
              && filteredRepositories.length > 0 && (
                <div className="space-y-4">
                  {filteredRepositories.map((repository) => (
                    <RepositoryCard
                      currentUserId={user?.id ?? 0}
                      key={repository.id}
                      repository={repository}
                    />
                  ))}
                </div>
              )}
          </div>

          {repositoriesQuery.isSuccess && lastPage > 1 && (
            <footer className="flex items-center justify-between border-t border-white/10 px-5 py-4">
              <p className="text-sm text-slate-500">
                Page {currentPage} of {lastPage}
              </p>

              <div className="flex gap-2">
                <button
                  className="rounded-lg border border-white/10 px-3 py-1.5 text-sm text-slate-300 transition hover:bg-white/5 disabled:cursor-not-allowed disabled:opacity-40"
                  disabled={currentPage <= 1}
                  onClick={() =>
                    setPage((current) =>
                      Math.max(1, current - 1),
                    )
                  }
                  type="button"
                >
                  Previous
                </button>

                <button
                  className="rounded-lg border border-white/10 px-3 py-1.5 text-sm text-slate-300 transition hover:bg-white/5 disabled:cursor-not-allowed disabled:opacity-40"
                  disabled={currentPage >= lastPage}
                  onClick={() =>
                    setPage((current) =>
                      Math.min(lastPage, current + 1),
                    )
                  }
                  type="button"
                >
                  Next
                </button>
              </div>
            </footer>
          )}
        </section>
      </main>

      <CreateRepositoryDialog
        isOpen={isCreateDialogOpen}
        onClose={() => setIsCreateDialogOpen(false)}
        onCreated={handleRepositoryCreated}
      />
    </div>
  )
}