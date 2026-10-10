import {
  useMemo,
  useState,
} from 'react'
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
  const {
    logout,
    user,
  } = useAuth()

  const [
    page,
    setPage,
  ] = useState(1)

  const [
    searchTerm,
    setSearchTerm,
  ] = useState('')

  const [
    isCreateDialogOpen,
    setIsCreateDialogOpen,
  ] = useState(false)

  const [
    isLoggingOut,
    setIsLoggingOut,
  ] = useState(false)

  const [
    logoutError,
    setLogoutError,
  ] = useState<string | null>(
    null,
  )

  const repositoriesQuery =
    useQuery({
      queryKey:
        repositoryQueryKeys.list(
          page,
        ),

      queryFn:
        () =>
          getRepositories(
            page,
          ),
    })

  const repositories =
    useMemo(
      () =>
        repositoriesQuery.data?.data
        ?? [],
      [
        repositoriesQuery.data?.data,
      ],
    )

  const filteredRepositories =
    useMemo(() => {
      const normalizedSearchTerm =
        searchTerm
          .trim()
          .toLowerCase()

      if (
        normalizedSearchTerm ===
        ''
      ) {
        return repositories
      }

      return repositories.filter(
        (repository) => {
          const searchableValues = [
            repository.name,
            repository.full_name,
            repository.owner.name,
            repository.owner.username,
            repository.visibility,
          ]

          return searchableValues.some(
            (value) =>
              value
                .toLowerCase()
                .includes(
                  normalizedSearchTerm,
                ),
          )
        },
      )
    }, [
      repositories,
      searchTerm,
    ])

  const publicRepositoriesOnPage =
    repositories.filter(
      (repository) =>
        repository.visibility ===
        'public',
    ).length

  const totalRepositories =
    repositoriesQuery.data?.meta.total
    ?? 0

  const currentPage =
    repositoriesQuery.data?.meta.current_page
    ?? page

  const lastPage =
    repositoriesQuery.data?.meta.last_page
    ?? 1

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
    setIsCreateDialogOpen(
      false,
    )

    setSearchTerm('')
    setPage(1)
  }

  return (
    <div className="min-h-screen text-[var(--text)]">
      <header className="app-header sticky top-0 z-30 border-b border-[var(--border)] bg-[var(--glass-header)] backdrop-blur-xl">
        <div className="mx-auto flex min-h-16 flex-wrap py-3 max-w-7xl items-center gap-3 px-5 lg:gap-5 lg:px-8">
          <Link
            className="flex shrink-0 items-center gap-2 font-semibold text-[var(--text)]"
            to="/dashboard"
          >
            <span className="grid size-9 place-items-center rounded-lg bg-blue-600">
              <GitBranch
                aria-hidden="true"
                className="size-5"
              />
            </span>

            <span className="hidden sm:inline">
              GitForge
            </span>
          </Link>

          <label className="relative order-last w-full sm:order-none sm:ml-auto sm:max-w-md">
            <span className="sr-only">
              Search repositories
            </span>

            <Search
              aria-hidden="true"
              className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[var(--muted)]"
            />

            <input
              className="h-9 w-full rounded-lg border border-[var(--border)] bg-[var(--subtle)] pl-9 pr-3 text-sm text-[var(--text)] outline-none transition placeholder:text-[var(--muted)] focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
              onChange={(
                event,
              ) =>
                setSearchTerm(
                  event.target.value,
                )
              }
              placeholder="Search your repositories"
              type="search"
              value={
                searchTerm
              }
            />
          </label>

          <Link
            className="inline-flex shrink-0 items-center gap-2 rounded-lg border border-[var(--border)] px-3 py-2 text-sm font-medium text-[var(--text)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)]"
            to="/settings/access-tokens"
          >
            <KeyRound
              aria-hidden="true"
              className="size-4"
            />

            <span className="hidden lg:inline">
              Access tokens
            </span>

            <span className="lg:hidden">
              Tokens
            </span>
          </Link>

          <button
            className="inline-flex shrink-0 items-center gap-2 rounded-lg border border-[var(--border)] px-3 py-2 text-sm font-medium text-[var(--text)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)] disabled:opacity-60"
            disabled={
              isLoggingOut
            }
            onClick={
              handleLogout
            }
            type="button"
          >
            {isLoggingOut ? (
              <LoaderCircle
                aria-hidden="true"
                className="size-4 animate-spin"
              />
            ) : (
              <LogOut
                aria-hidden="true"
                className="size-4"
              />
            )}

            <span className="hidden md:inline">
              {isLoggingOut
                ? 'Signing out...'
                : 'Sign out'}
            </span>
          </button>
        </div>
      </header>

      <main className="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        {logoutError !==
          null && (
          <div
            className="mb-6 rounded-lg border border-amber-400/20 bg-amber-500/10 px-4 py-3 text-sm text-[var(--amber)]"
            role="alert"
          >
            {logoutError}
          </div>
        )}

        <section className="workspace-welcome flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p className="text-sm text-[var(--accent)]">
              Dashboard
            </p>

            <h1 className="mt-1 text-3xl font-bold tracking-tight text-[var(--text)]">
              Welcome, {user?.name}
            </h1>

            <p className="mt-2 text-[var(--muted)]">
              Manage repositories in the{' '}
              <span className="font-medium text-[var(--text)]">
                @{user?.username}
              </span>{' '}
              namespace.
            </p>
          </div>

          <button
            className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
            onClick={() =>
              setIsCreateDialogOpen(
                true,
              )
            }
            type="button"
          >
            <Plus
              aria-hidden="true"
              className="size-4"
            />

            New repository
          </button>
        </section>

        <section className="grid gap-5 py-8 md:grid-cols-3">
          <article className="rounded-xl border border-[var(--border)] glass-panel p-5">
            <div className="flex items-center justify-between">
              <span className="grid size-10 place-items-center rounded-lg bg-blue-500/10 text-[var(--accent)]">
                <GitFork
                  aria-hidden="true"
                  className="size-5"
                />
              </span>

              <span className="text-2xl font-bold text-[var(--text)]">
                {repositoriesQuery.isPending
                  ? '—'
                  : totalRepositories}
              </span>
            </div>

            <p className="mt-4 text-sm text-[var(--muted)]">
              Owned repositories
            </p>
          </article>

          <article className="rounded-xl border border-[var(--border)] glass-panel p-5">
            <div className="flex items-center justify-between">
              <span className="grid size-10 place-items-center rounded-lg bg-blue-500/10 text-[var(--accent)]">
                <BookOpen
                  aria-hidden="true"
                  className="size-5"
                />
              </span>

              <span className="text-2xl font-bold text-[var(--text)]">
                {repositoriesQuery.isPending
                  ? '—'
                  : publicRepositoriesOnPage}
              </span>
            </div>

            <p className="mt-4 text-sm text-[var(--muted)]">
              Public on this page
            </p>
          </article>

          <article className="rounded-xl border border-[var(--border)] glass-panel p-5">
            <div className="flex items-center justify-between">
              <span className="grid size-10 place-items-center rounded-lg bg-blue-500/10 text-[var(--accent)]">
                <UserRound
                  aria-hidden="true"
                  className="size-5"
                />
              </span>

              <span className="truncate pl-4 text-xl font-bold text-[var(--text)]">
                @{user?.username ?? ''}
              </span>
            </div>

            <p className="mt-4 text-sm text-[var(--muted)]">
              Repository namespace
            </p>
          </article>
        </section>

        <section className="overflow-hidden rounded-xl border border-[var(--border)] glass-panel">
          <div className="border-b border-[var(--border)] px-5 py-4">
            <h2 className="font-semibold text-[var(--text)]">
              Your repositories
            </h2>

            <p className="mt-1 text-sm text-[var(--muted)]">
              Only repositories owned by your account are listed here.
            </p>
          </div>

          <div className="p-5">
            {repositoriesQuery.isPending && (
              <div className="grid place-items-center py-16">
                <div className="flex items-center gap-3 text-sm text-[var(--muted)]">
                  <LoaderCircle
                    aria-hidden="true"
                    className="size-5 animate-spin text-[var(--accent)]"
                  />

                  Loading repositories...
                </div>
              </div>
            )}

            {repositoriesQuery.isError && (
              <div
                className="rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-4 text-sm text-[var(--red)]"
                role="alert"
              >
                <p>
                  {getApiErrorMessage(
                    repositoriesQuery.error,
                    'Unable to load repositories.',
                  )}
                </p>

                <button
                  className="mt-3 font-semibold text-[var(--red)] underline underline-offset-4"
                  onClick={() =>
                    repositoriesQuery.refetch()
                  }
                  type="button"
                >
                  Try again
                </button>
              </div>
            )}

            {repositoriesQuery.isSuccess
              && repositories.length ===
                0 && (
                <div className="grid place-items-center py-16 text-center">
                  <span className="grid size-14 place-items-center rounded-full border border-[var(--border)] bg-[var(--subtle)] text-[var(--muted)]">
                    <GitBranch
                      aria-hidden="true"
                      className="size-6"
                    />
                  </span>

                  <h3 className="mt-4 font-semibold text-[var(--text)]">
                    No repositories yet
                  </h3>

                  <p className="mt-2 max-w-sm text-sm leading-6 text-[var(--muted)]">
                    Create your first repository to start hosting code
                    with GitForge.
                  </p>

                  <button
                    className="mt-5 inline-flex items-center gap-2 rounded-lg border border-[var(--border)] bg-[var(--subtle)] px-4 py-2 text-sm font-semibold text-[var(--text)] transition hover:bg-[var(--hover)]"
                    onClick={() =>
                      setIsCreateDialogOpen(
                        true,
                      )
                    }
                    type="button"
                  >
                    <Plus
                      aria-hidden="true"
                      className="size-4"
                    />

                    Create repository
                  </button>
                </div>
              )}

            {repositoriesQuery.isSuccess
              && repositories.length >
                0
              && filteredRepositories.length ===
                0 && (
                <div className="py-14 text-center">
                  <Search
                    aria-hidden="true"
                    className="mx-auto size-7 text-[var(--muted)]"
                  />

                  <h3 className="mt-3 font-semibold text-[var(--text)]">
                    No matching repositories
                  </h3>

                  <p className="mt-2 text-sm text-[var(--muted)]">
                    Try a different search term.
                  </p>
                </div>
              )}

            {repositoriesQuery.isSuccess
              && filteredRepositories.length >
                0 && (
                <div className="space-y-4">
                  {filteredRepositories.map(
                    (
                      repository,
                    ) => (
                      <RepositoryCard
                        key={
                          repository.id
                        }
                        repository={
                          repository
                        }
                      />
                    ),
                  )}
                </div>
              )}
          </div>

          {repositoriesQuery.isSuccess
            && lastPage > 1 && (
              <footer className="flex items-center justify-between border-t border-[var(--border)] px-5 py-4">
                <p className="text-sm text-[var(--muted)]">
                  Page {currentPage} of{' '}
                  {lastPage}
                </p>

                <div className="flex gap-2">
                  <button
                    className="rounded-lg border border-[var(--border)] px-3 py-1.5 text-sm text-[var(--text)] transition hover:bg-[var(--subtle)] disabled:cursor-not-allowed disabled:opacity-40"
                    disabled={
                      currentPage <= 1
                    }
                    onClick={() =>
                      setPage(
                        (
                          current,
                        ) =>
                          Math.max(
                            1,
                            current - 1,
                          ),
                      )
                    }
                    type="button"
                  >
                    Previous
                  </button>

                  <button
                    className="rounded-lg border border-[var(--border)] px-3 py-1.5 text-sm text-[var(--text)] transition hover:bg-[var(--subtle)] disabled:cursor-not-allowed disabled:opacity-40"
                    disabled={
                      currentPage >=
                      lastPage
                    }
                    onClick={() =>
                      setPage(
                        (
                          current,
                        ) =>
                          Math.min(
                            lastPage,
                            current + 1,
                          ),
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
        isOpen={
          isCreateDialogOpen
        }
        onClose={() =>
          setIsCreateDialogOpen(
            false,
          )
        }
        onCreated={
          handleRepositoryCreated
        }
      />
    </div>
  )
}