import { useState } from 'react'
import {
  BookOpen,
  GitBranch,
  GitFork,
  LogOut,
  Plus,
  Search,
  Settings,
  UserRound,
} from 'lucide-react'
import { Link } from 'react-router-dom'
import { getApiErrorMessage } from '../../lib/api-errors'
import { useAuth } from '../auth/auth-context'

export function DashboardPage() {
  const { logout, user } = useAuth()
  const [isLoggingOut, setIsLoggingOut] = useState(false)
  const [logoutError, setLogoutError] = useState<string | null>(null)

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

  return (
    <div className="min-h-screen bg-[#0d1117] text-slate-200">
      <header className="border-b border-white/10 bg-[#0d1117]/90 backdrop-blur-xl">
        <div className="mx-auto flex h-16 max-w-7xl items-center gap-5 px-5 lg:px-8">
          <Link
            className="flex shrink-0 items-center gap-2 font-semibold text-white"
            to="/dashboard"
          >
            <span className="grid size-9 place-items-center rounded-lg bg-blue-600">
              <GitBranch aria-hidden="true" className="size-5" />
            </span>
            GitForge
          </Link>

          <label className="relative ml-auto hidden w-full max-w-md sm:block">
            <span className="sr-only">Search repositories</span>
            <Search
              aria-hidden="true"
              className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500"
            />
            <input
              className="h-9 w-full rounded-lg border border-white/10 bg-white/5 pl-9 pr-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
              placeholder="Search repositories"
              type="search"
            />
          </label>

          <button
            className="inline-flex items-center gap-2 rounded-lg border border-white/10 px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white disabled:opacity-60"
            disabled={isLoggingOut}
            onClick={handleLogout}
            type="button"
          >
            <LogOut aria-hidden="true" className="size-4" />
            <span className="hidden sm:inline">
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
            type="button"
          >
            <Plus aria-hidden="true" className="size-4" />
            New repository
          </button>
        </section>

        <section className="grid gap-5 py-8 md:grid-cols-3">
          {[
            {
              icon: GitFork,
              label: 'Repositories',
              value: '0',
            },
            {
              icon: BookOpen,
              label: 'Public repositories',
              value: '0',
            },
            {
              icon: UserRound,
              label: 'Account',
              value: `@${user?.username ?? ''}`,
            },
          ].map(({ icon: Icon, label, value }) => (
            <article
              className="rounded-xl border border-white/10 bg-[#161b22] p-5"
              key={label}
            >
              <div className="flex items-center justify-between">
                <span className="grid size-10 place-items-center rounded-lg bg-blue-500/10 text-blue-300">
                  <Icon aria-hidden="true" className="size-5" />
                </span>
                <span className="text-2xl font-bold text-white">{value}</span>
              </div>
              <p className="mt-4 text-sm text-slate-400">{label}</p>
            </article>
          ))}
        </section>

        <section className="rounded-xl border border-white/10 bg-[#161b22]">
          <div className="flex items-center justify-between border-b border-white/10 px-5 py-4">
            <div>
              <h2 className="font-semibold text-white">Your repositories</h2>
              <p className="mt-1 text-sm text-slate-500">
                Repositories you own will appear here.
              </p>
            </div>
            <Settings
              aria-hidden="true"
              className="size-5 text-slate-500"
            />
          </div>

          <div className="grid place-items-center px-5 py-16 text-center">
            <span className="grid size-14 place-items-center rounded-full border border-white/10 bg-white/[0.03] text-slate-400">
              <GitBranch aria-hidden="true" className="size-6" />
            </span>
            <h3 className="mt-4 font-semibold text-white">
              No repositories yet
            </h3>
            <p className="mt-2 max-w-sm text-sm leading-6 text-slate-500">
              Create your first repository to start managing source code with
              GitForge.
            </p>
            <button
              className="mt-5 inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/10"
              type="button"
            >
              <Plus aria-hidden="true" className="size-4" />
              Create repository
            </button>
          </div>
        </section>
      </main>
    </div>
  )
}