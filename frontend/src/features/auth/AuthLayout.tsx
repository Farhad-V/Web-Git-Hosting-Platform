import type { ReactNode } from 'react'
import { GitBranch, ShieldCheck } from 'lucide-react'
import { Link } from 'react-router-dom'

interface AuthLayoutProps {
  title: string
  description: string
  children: ReactNode
  footer: ReactNode
}

export function AuthLayout({
  title,
  description,
  children,
  footer,
}: AuthLayoutProps) {
  return (
    <main className="relative grid min-h-screen place-items-center overflow-hidden px-5 py-12">
      <div
        aria-hidden="true"
        className="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.035)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.035)_1px,transparent_1px)] bg-[size:48px_48px] [mask-image:radial-gradient(circle_at_center,black,transparent_80%)]"
      />

      <div
        aria-hidden="true"
        className="absolute left-1/2 top-0 size-[32rem] -translate-x-1/2 rounded-full bg-blue-600/10 blur-3xl"
      />

      <section className="relative w-full max-w-md">
        <Link
          className="mx-auto mb-8 flex w-fit items-center gap-2 font-semibold text-[var(--text)]"
          to="/"
        >
          <span className="grid size-10 place-items-center rounded-lg bg-blue-600 shadow-lg shadow-blue-950/40">
            <GitBranch aria-hidden="true" className="size-5" />
          </span>
          <span className="text-lg">GitForge</span>
        </Link>

        <div className="rounded-2xl border border-[var(--border)] glass-panel p-6 shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-8">
          <div className="text-center">
            <h1 className="text-2xl font-bold tracking-tight text-[var(--text)]">
              {title}
            </h1>
            <p className="mt-2 text-sm leading-6 text-[var(--muted)]">
              {description}
            </p>
          </div>

          <div className="mt-7">{children}</div>

          <div className="mt-6 border-t border-[var(--border)] pt-6 text-center text-sm text-[var(--muted)]">
            {footer}
          </div>
        </div>

        <p className="mt-6 flex items-center justify-center gap-2 text-xs text-[var(--muted)]">
          <ShieldCheck aria-hidden="true" className="size-4" />
          Credentials are transmitted securely to your GitForge server.
        </p>
      </section>
    </main>
  )
}