import {
  BookOpen,
  Globe2,
  LockKeyhole,
} from 'lucide-react'
import { Link } from 'react-router-dom'
import type { Repository } from '../../types/repository'

interface RepositoryCardProps {
  repository: Repository
}

function formatDate(
  value: string | null,
): string {
  if (value === null) {
    return 'Unknown date'
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return 'Unknown date'
  }

  return new Intl.DateTimeFormat(
    'en',
    {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    },
  ).format(date)
}

export function RepositoryCard({
  repository,
}: RepositoryCardProps) {
  return (
    <article className="repository-card rounded-xl border border-[var(--border)] glass-panel p-5 transition hover:border-blue-400/30">
      <div className="flex items-start justify-between gap-4">
        <div className="min-w-0">
          <div className="flex flex-wrap items-center gap-2">
            <BookOpen
              aria-hidden="true"
              className="size-4 shrink-0 text-[var(--accent)]"
            />

            <Link
              className="truncate font-semibold text-[var(--accent)] transition hover:text-[var(--accent)] hover:underline"
              to={`/repositories/${repository.id}`}
            >
              {repository.full_name}
            </Link>

            <span className="rounded-full border border-[var(--border)] px-2 py-0.5 text-xs capitalize text-[var(--muted)]">
              {repository.visibility}
            </span>
          </div>

          <p className="mt-3 text-sm leading-6 text-[var(--muted)]">
            Repository owned by{' '}
            <span className="text-[var(--text)]">
              @{repository.owner.username}
            </span>
          </p>
        </div>

        <span
          className={
            repository.visibility === 'private'
              ? 'grid size-9 shrink-0 place-items-center rounded-lg bg-blue-500/10 text-[var(--accent)]'
              : 'grid size-9 shrink-0 place-items-center rounded-lg bg-emerald-500/10 text-[var(--emerald)]'
          }
          title={
            repository.visibility === 'private'
              ? 'Private repository'
              : 'Public repository'
          }
        >
          {repository.visibility === 'private' ? (
            <LockKeyhole
              aria-hidden="true"
              className="size-4"
            />
          ) : (
            <Globe2
              aria-hidden="true"
              className="size-4"
            />
          )}
        </span>
      </div>

      <div className="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-[var(--border)] pt-4 text-xs text-[var(--muted)]">
        <span>
          Created {formatDate(repository.created_at)}
        </span>

        <span>
          Updated {formatDate(repository.updated_at)}
        </span>
      </div>
    </article>
  )
}