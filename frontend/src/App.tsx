import { HomeHero } from './components/HomeHero'
import { ThemeToggle } from './components/ThemeToggle'
import {
  ArrowRight,
  Box,
  Code2,
  GitBranch,
  GitFork,
  LockKeyhole,
  Server,
  ShieldCheck,
  Star,
} from 'lucide-react'
import { Link, Navigate, Route, Routes } from 'react-router-dom'
import { PersonalAccessTokensPage } from './features/access-tokens/PersonalAccessTokensPage'
import {
  GuestRoute,
  ProtectedRoute,
} from './features/auth/AuthRoutes'
import { LoginPage } from './features/auth/LoginPage'
import { RegisterPage } from './features/auth/RegisterPage'
import { DashboardPage } from './features/dashboard/DashboardPage'
import { RepositoryDetailPage } from './features/repositories/RepositoryDetailPage'

function HomePage() {
  return (
    <div className="landing-page min-h-screen text-[var(--text)]">
      <header className="landing-header border-b border-[var(--border)] bg-[var(--glass-header)] backdrop-blur-xl">
        <div className="mx-auto flex h-16 max-w-7xl items-center gap-6 px-5 lg:px-8">
          <Link
            className="flex shrink-0 items-center gap-2 font-semibold text-[var(--text)]"
            to="/"
          >
            <span className="grid size-9 place-items-center rounded-lg bg-blue-600 shadow-lg shadow-blue-950/40">
              <GitBranch aria-hidden="true" className="size-5" />
            </span>

            <span>GitForge</span>
          </Link>

          <nav
            aria-label="Primary navigation"
            className="hidden items-center gap-6 text-sm text-[var(--muted)] md:flex"
          >
            <a
              className="transition hover:text-[var(--text)]"
              href="#features"
            >
              Features
            </a>

            <a
              className="transition hover:text-[var(--text)]"
              href="#security"
            >
              Security
            </a>

            <a
              className="transition hover:text-[var(--text)]"
              href="#developers"
            >
              Developers
            </a>
          </nav>

          <div className="ml-auto" />

          <div className="flex shrink-0 items-center gap-2">
            <Link
              className="rounded-lg px-3 py-2 text-sm font-medium text-[var(--text)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)]"
              to="/login"
            >
              Sign in
            </Link>

            <Link
              className="rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-950/30 transition hover:bg-blue-500"
              to="/register"
            >
              Get started
            </Link>
          </div>
        </div>
      </header>

      <main>
        <HomeHero />

        <section
          className="mx-auto max-w-7xl px-5 py-24 lg:px-8"
          id="features"
        >
          <div className="max-w-2xl">
            <p className="text-sm font-semibold uppercase tracking-widest text-[var(--accent)]">
              Built for self-hosted workflows
            </p>

            <h2 className="mt-3 text-3xl font-bold tracking-tight text-[var(--text)] sm:text-4xl">
              The essentials of modern Git hosting
            </h2>

            <p className="mt-4 text-[var(--muted)]">
              A clean foundation for managing code without unnecessary
              complexity.
            </p>
          </div>

          <div className="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            {[
              {
                icon: LockKeyhole,
                title: 'Private by design',
                description:
                  'Protect repositories with token-based authentication and owner-level authorization.',
              },
              {
                icon: GitFork,
                title: 'Repository lifecycle',
                description:
                  'Create repositories and control public or private visibility.',
              },
              {
                icon: Server,
                title: 'Self-hosted storage',
                description:
                  'Keep managed bare Git repositories on infrastructure you control.',
              },
              {
                icon: Code2,
                title: 'Git Smart HTTP',
                description:
                  'Use familiar clone, fetch, and push workflows over standard HTTP transport.',
              },
              {
                icon: Box,
                title: 'Versioned API',
                description:
                  'Integrate through a consistent Laravel API designed for future clients.',
              },
              {
                icon: ShieldCheck,
                title: 'Tested behavior',
                description:
                  'Authentication, authorization, storage, and transport rules are covered by automated tests.',
              },
            ].map(({ description, icon: Icon, title }) => (
              <article
                className="group rounded-xl border border-[var(--border)] glass-panel p-6 transition hover:-translate-y-1 hover:border-blue-400/30 hover:bg-blue-500/[0.04]"
                key={title}
              >
                <span className="grid size-11 place-items-center rounded-lg border border-blue-400/15 bg-blue-500/10 text-[var(--accent)]">
                  <Icon
                    aria-hidden="true"
                    className="size-5"
                  />
                </span>

                <h3 className="mt-5 font-semibold text-[var(--text)]">
                  {title}
                </h3>

                <p className="mt-2 text-sm leading-6 text-[var(--muted)]">
                  {description}
                </p>
              </article>
            ))}
          </div>
        </section>

        <section
          className="border-y border-[var(--border)] bg-[var(--subtle)]"
          id="security"
        >
          <div className="mx-auto grid max-w-7xl gap-10 px-5 py-20 lg:grid-cols-2 lg:items-center lg:px-8">
            <div>
              <p className="text-sm font-semibold uppercase tracking-widest text-[var(--emerald)]">
                Security foundation
              </p>

              <h2 className="mt-3 text-3xl font-bold text-[var(--text)]">
                Access rules enforced at every layer
              </h2>

              <p className="mt-4 max-w-xl leading-7 text-[var(--muted)]">
                Repository visibility, ownership, credentials, and Git protocol operations are validated before storage is accessed.
              </p>
            </div>

            <div className="grid grid-cols-2 gap-4">
              {[
                ['PHP 8.4', 'Reference runtime'],
                ['PostgreSQL 18', 'Reference database'],
                ['Owner', 'Write authorization'],
                ['Managed', 'Git storage'],
              ].map(([value, label]) => (
                <div
                  className="rounded-xl border border-[var(--border)] bg-[var(--inset)] p-5"
                  key={label}
                >
                  <p className="text-2xl font-bold text-[var(--text)]">
                    {value}
                  </p>

                  <p className="mt-1 text-sm text-[var(--muted)]">
                    {label}
                  </p>
                </div>
              ))}
            </div>
          </div>
        </section>

        <section
          className="mx-auto max-w-7xl px-5 py-24 text-center lg:px-8"
          id="developers"
        >
          <Star
            aria-hidden="true"
            className="mx-auto size-8 fill-blue-500/20 text-[var(--accent)]"
          />

          <h2 className="mt-5 text-3xl font-bold text-[var(--text)]">
            Start hosting your repositories
          </h2>

          <p className="mx-auto mt-4 max-w-xl text-[var(--muted)]">
            Create an account, generate a repository access token,
            initialize a repository, and use the Git commands you
            already know.
          </p>

          <Link
            className="mt-8 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500"
            to="/register"
          >
            Get started

            <ArrowRight
              aria-hidden="true"
              className="size-4"
            />
          </Link>
        </section>
      </main>

      <footer className="border-t border-[var(--border)]">
        <div className="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-8 text-sm text-[var(--muted)] sm:flex-row sm:items-center sm:justify-between lg:px-8">
          <p>
            © 2026 GitForge. Self-hosted Git repository hosting.
          </p>

          <p>
            Laravel · React · TypeScript · PostgreSQL
          </p>
        </div>
      </footer>
    </div>
  )
}

export default function App() {
  return (
    <>
    <ThemeToggle />
    <Routes>
      <Route element={<HomePage />} path="/" />

      <Route element={<GuestRoute />}>
        <Route element={<LoginPage />} path="/login" />
        <Route element={<RegisterPage />} path="/register" />
      </Route>

      <Route
        element={<RepositoryDetailPage />}
        path="/repositories/:repositoryId"
      />
      <Route element={<ProtectedRoute />}>
        <Route element={<DashboardPage />} path="/dashboard" />

        <Route
          element={<PersonalAccessTokensPage />}
          path="/settings/access-tokens"
        />
      </Route>

      <Route element={<Navigate replace to="/" />} path="*" />
    </Routes>
    </>
  )
}