import {
  ArrowRight,
  BookOpen,
  Box,
  Check,
  Code2,
  GitBranch,
  GitFork,
  LockKeyhole,
  Search,
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
    <div className="min-h-screen text-slate-200">
      <header className="border-b border-white/10 bg-[#0d1117]/85 backdrop-blur-xl">
        <div className="mx-auto flex h-16 max-w-7xl items-center gap-6 px-5 lg:px-8">
          <Link
            className="flex shrink-0 items-center gap-2 font-semibold text-white"
            to="/"
          >
            <span className="grid size-9 place-items-center rounded-lg bg-blue-600 shadow-lg shadow-blue-950/40">
              <GitBranch aria-hidden="true" className="size-5" />
            </span>

            <span>GitForge</span>
          </Link>

          <nav
            aria-label="Primary navigation"
            className="hidden items-center gap-6 text-sm text-slate-400 md:flex"
          >
            <a
              className="transition hover:text-white"
              href="#features"
            >
              Features
            </a>

            <a
              className="transition hover:text-white"
              href="#security"
            >
              Security
            </a>

            <a
              className="transition hover:text-white"
              href="#developers"
            >
              Developers
            </a>
          </nav>

          <div className="ml-auto hidden w-full max-w-sm sm:block">
            <label className="relative block">
              <span className="sr-only">
                Search repositories
              </span>

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
          </div>

          <div className="flex shrink-0 items-center gap-2">
            <Link
              className="rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
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
        <section className="relative overflow-hidden border-b border-white/10">
          <div
            aria-hidden="true"
            className="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.035)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.035)_1px,transparent_1px)] bg-[size:48px_48px] [mask-image:linear-gradient(to_bottom,black,transparent)]"
          />

          <div className="relative mx-auto grid max-w-7xl items-center gap-16 px-5 py-24 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-32">
            <div>
              <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-500/10 px-3 py-1 text-sm text-blue-300">
                <ShieldCheck
                  aria-hidden="true"
                  className="size-4"
                />
                Self-hosted and under your control
              </div>

              <h1 className="max-w-3xl text-5xl font-bold tracking-tight text-white sm:text-6xl lg:text-7xl">
                Your code.

                <span className="block bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">
                  Your infrastructure.
                </span>
              </h1>

              <p className="mt-6 max-w-2xl text-lg leading-8 text-slate-400">
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
                  className="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 bg-white/5 px-5 py-3 font-semibold text-slate-200 transition hover:border-white/20 hover:bg-white/10"
                  href="#developers"
                >
                  <BookOpen
                    aria-hidden="true"
                    className="size-4"
                  />
                  Read documentation
                </a>
              </div>

              <ul className="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm text-slate-400">
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
                      className="size-4 text-emerald-400"
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

              <div className="relative overflow-hidden rounded-2xl border border-white/10 bg-[#161b22] shadow-2xl shadow-black/40">
                <div className="flex h-12 items-center gap-2 border-b border-white/10 px-4">
                  <span className="size-3 rounded-full bg-red-400/80" />
                  <span className="size-3 rounded-full bg-amber-400/80" />
                  <span className="size-3 rounded-full bg-emerald-400/80" />

                  <span className="ml-3 text-xs text-slate-500">
                    git-hosting-platform
                  </span>
                </div>

                <div className="p-5 font-mono text-sm leading-7">
                  <p className="text-slate-500">
                    $ git clone \
                  </p>

                  <p className="pl-4 text-cyan-300">
                    http://gitforge.local/farhad/platform.git
                  </p>

                  <p className="mt-4 text-slate-400">
                    Cloning into{' '}
                    <span className="text-white">
                      'platform'
                    </span>
                    ...
                  </p>

                  <p className="text-slate-400">
                    remote: Enumerating objects:{' '}
                    <span className="text-white">128</span>, done.
                  </p>

                  <p className="text-slate-400">
                    Receiving objects:{' '}
                    <span className="text-emerald-400">
                      100% (128/128)
                    </span>
                  </p>

                  <p className="mt-4 text-slate-500">
                    $ git status
                  </p>

                  <p className="text-emerald-400">
                    On branch main
                  </p>

                  <p className="text-slate-400">
                    Your branch is up to date with origin/main.
                  </p>

                  <p className="mt-4 animate-pulse text-blue-400">
                    ▌
                  </p>
                </div>
              </div>

              <div className="absolute -bottom-6 -left-6 hidden rounded-xl border border-white/10 bg-[#161b22] p-4 shadow-xl md:block">
                <div className="flex items-center gap-3">
                  <span className="grid size-10 place-items-center rounded-lg bg-emerald-500/10 text-emerald-400">
                    <ShieldCheck
                      aria-hidden="true"
                      className="size-5"
                    />
                  </span>

                  <div>
                    <p className="text-sm font-semibold text-white">
                      Secure transport
                    </p>

                    <p className="text-xs text-slate-500">
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
            <p className="text-sm font-semibold uppercase tracking-widest text-blue-400">
              Built for self-hosted workflows
            </p>

            <h2 className="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">
              The essentials of modern Git hosting
            </h2>

            <p className="mt-4 text-slate-400">
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
                className="group rounded-xl border border-white/10 bg-white/[0.025] p-6 transition hover:-translate-y-1 hover:border-blue-400/30 hover:bg-blue-500/[0.04]"
                key={title}
              >
                <span className="grid size-11 place-items-center rounded-lg border border-blue-400/15 bg-blue-500/10 text-blue-300">
                  <Icon
                    aria-hidden="true"
                    className="size-5"
                  />
                </span>

                <h3 className="mt-5 font-semibold text-white">
                  {title}
                </h3>

                <p className="mt-2 text-sm leading-6 text-slate-400">
                  {description}
                </p>
              </article>
            ))}
          </div>
        </section>

        <section
          className="border-y border-white/10 bg-white/[0.02]"
          id="security"
        >
          <div className="mx-auto grid max-w-7xl gap-10 px-5 py-20 lg:grid-cols-2 lg:items-center lg:px-8">
            <div>
              <p className="text-sm font-semibold uppercase tracking-widest text-emerald-400">
                Security foundation
              </p>

              <h2 className="mt-3 text-3xl font-bold text-white">
                Access rules enforced at every layer
              </h2>

              <p className="mt-4 max-w-xl leading-7 text-slate-400">
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
                  className="rounded-xl border border-white/10 bg-[#0d1117] p-5"
                  key={label}
                >
                  <p className="text-2xl font-bold text-white">
                    {value}
                  </p>

                  <p className="mt-1 text-sm text-slate-500">
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
            className="mx-auto size-8 fill-blue-500/20 text-blue-400"
          />

          <h2 className="mt-5 text-3xl font-bold text-white">
            Start hosting your repositories
          </h2>

          <p className="mx-auto mt-4 max-w-xl text-slate-400">
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

      <footer className="border-t border-white/10">
        <div className="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between lg:px-8">
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
  )
}