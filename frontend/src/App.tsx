import { ThemeToggle } from './components/ThemeToggle'
import {
  ArrowRight,
  BookOpen,
  Box,
  Check,
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
    <div className="min-h-screen text-[var(--text)]">
      <header className="border-b border-[var(--border)] bg-[var(--glass-header)] backdrop-blur-xl">
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
        <section className="relative overflow-hidden border-b border-[var(--border)]">
          <div
            aria-hidden="true"
            className="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.035)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.035)_1px,transparent_1px)] bg-[size:48px_48px] [mask-image:linear-gradient(to_bottom,black,transparent)]"
          />

          <div className="relative mx-auto grid max-w-7xl items-center gap-16 px-5 py-24 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-32">
            <div>
              <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-500/10 px-3 py-1 text-sm text-[var(--accent)]">
                <ShieldCheck
                  aria-hidden="true"
                  className="size-4"
                />
                Self-hosted and under your control
              </div>

              <h1 className="max-w-3xl text-5xl font-bold tracking-tight text-[var(--text)] sm:text-6xl lg:text-7xl">
                Your code.

                <span className="block bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">
                  Your infrastructure.
                </span>
              </h1>

              <p className="mt-6 max-w-2xl text-lg leading-8 text-[var(--muted)]">
                A focused self-hosted Git platform for developers who want
                secure repositories, familiar Git workflows, and control over
                their source-code infrastructure.
              </p>

              <div className="mt-9 flex flex-col gap-3 sm:flex-row">
                <Link
                  className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white shadow-xl shadow-blue-950/40 transition hover:bg-blue-500"
                  to="/register"
                >
                  Create your account

                  <ArrowRight
                    aria-hidden="true"
                    className="size-4"
                  />
                </Link>

                <a
                  className="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--border)] bg-[var(--subtle)] px-5 py-3 font-semibold text-[var(--text)] transition hover:border-[var(--border-strong)] hover:bg-[var(--hover)]"
                  href="#developers"
                >
                  <BookOpen
                    aria-hidden="true"
                    className="size-4"
                  />
                  Read documentation
                </a>
              </div>

              <ul className="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm text-[var(--muted)]">
                {[
                  'Private repositories',
                  'Token authentication',
                  'Git Smart HTTP',
                ].map((item) => (
                  <li
                    className="flex items-center gap-2"
                    key={item}
                  >
                    <Check
                      aria-hidden="true"
                      className="size-4 text-[var(--emerald)]"
                    />

                    {item}
                  </li>
                ))}
              </ul>
            </div>

            <div className="relative">
              <div
                aria-hidden="true"
                className="absolute -inset-8 rounded-full bg-blue-600/15 blur-3xl"
              />

              <div className="relative overflow-hidden rounded-2xl border border-[var(--border)] glass-panel shadow-2xl shadow-black/40">
                <div className="flex h-12 items-center gap-2 border-b border-[var(--border)] px-4">
                  <span className="size-3 rounded-full bg-red-400/80" />
                  <span className="size-3 rounded-full bg-amber-400/80" />
                  <span className="size-3 rounded-full bg-emerald-400/80" />

                  <span className="ml-3 text-xs text-[var(--muted)]">
                    git-hosting-platform
                  </span>
                </div>

                <div className="p-5 font-mono text-sm leading-7">
                  <p className="text-[var(--muted)]">
                    $ git clone \
                  </p>

                  <p className="pl-4 text-[var(--accent)]">
                    http://gitforge.local/farhad/platform.git
                  </p>

                  <p className="mt-4 text-[var(--muted)]">
                    Cloning into{' '}
                    <span className="text-[var(--text)]">
                      'platform'
                    </span>
                    ...
                  </p>

                  <p className="text-[var(--muted)]">
                    remote: Enumerating objects:{' '}
                    <span className="text-[var(--text)]">128</span>, done.
                  </p>

                  <p className="text-[var(--muted)]">
                    Receiving objects:{' '}
                    <span className="text-[var(--emerald)]">
                      100% (128/128)
                    </span>
                  </p>

                  <p className="mt-4 text-[var(--muted)]">
                    $ git status
                  </p>

                  <p className="text-[var(--emerald)]">
                    On branch main
                  </p>

                  <p className="text-[var(--muted)]">
                    Your branch is up to date with origin/main.
                  </p>

                  <p className="mt-4 animate-pulse text-[var(--accent)]">
                    ▌
                  </p>
                </div>
              </div>

              <div className="absolute -bottom-6 -left-6 hidden rounded-xl border border-[var(--border)] glass-panel p-4 shadow-xl md:block">
                <div className="flex items-center gap-3">
                  <span className="grid size-10 place-items-center rounded-lg bg-emerald-500/10 text-[var(--emerald)]">
                    <ShieldCheck
                      aria-hidden="true"
                      className="size-5"
                    />
                  </span>

                  <div>
                    <p className="text-sm font-semibold text-[var(--text)]">
                      Secure transport
                    </p>

                    <p className="text-xs text-[var(--muted)]">
                      Authentication verified
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

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