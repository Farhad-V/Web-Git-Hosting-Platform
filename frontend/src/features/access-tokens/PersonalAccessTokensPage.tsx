import { useState } from 'react'
import {
  useMutation,
  useQuery,
  useQueryClient,
} from '@tanstack/react-query'
import {
  AlertTriangle,
  ArrowLeft,
  CalendarDays,
  GitBranch,
  KeyRound,
  LoaderCircle,
  Plus,
  RefreshCw,
  ShieldCheck,
  Trash2,
  X,
} from 'lucide-react'
import { Link } from 'react-router-dom'
import { getApiErrorMessage } from '../../lib/api-errors'
import {
  getPersonalAccessTokens,
  revokePersonalAccessToken,
} from '../../services/personal-access-token-service'
import type { PersonalAccessToken } from '../../types/personal-access-token'
import { CreatePersonalAccessTokenDialog } from './CreatePersonalAccessTokenDialog'
import { personalAccessTokenQueryKeys } from './personal-access-token-query-keys'

function formatDate(
  value: string | null,
): string {
  if (
    value === null
  ) {
    return 'Unknown'
  }

  const date =
    new Date(value)

  if (
    Number.isNaN(
      date.getTime(),
    )
  ) {
    return 'Unknown'
  }

  return new Intl.DateTimeFormat(
    undefined,
    {
      dateStyle:
        'medium',
      timeStyle:
        'short',
    },
  ).format(date)
}

export function PersonalAccessTokensPage() {
  const queryClient =
    useQueryClient()

  const [
    isCreateDialogOpen,
    setIsCreateDialogOpen,
  ] = useState(false)

  const [
    tokenToRevoke,
    setTokenToRevoke,
  ] = useState<PersonalAccessToken | null>(
    null,
  )

  const tokensQuery =
    useQuery({
      queryKey:
        personalAccessTokenQueryKeys.list(),

      queryFn:
        getPersonalAccessTokens,
    })

  const revokeMutation =
    useMutation({
      mutationFn:
        revokePersonalAccessToken,

      onSuccess:
        async () => {
          await queryClient.invalidateQueries({
            queryKey:
              personalAccessTokenQueryKeys.all,
          })

          setTokenToRevoke(
            null,
          )
        },
    })

  const tokens =
    tokensQuery.data
      ?.data
    ?? []

  const activeTokens =
    tokens.filter(
      (token) =>
        token.revoked_at ===
        null,
    )

  async function confirmRevocation(): Promise<void> {
    if (
      tokenToRevoke ===
      null
    ) {
      return
    }

    revokeMutation.reset()

    try {
      await revokeMutation.mutateAsync(
        tokenToRevoke.id,
      )
    } catch {
      // Mutation error is rendered in the confirmation dialog.
    }
  }

  function closeRevokeDialog(): void {
    if (
      revokeMutation.isPending
    ) {
      return
    }

    revokeMutation.reset()
    setTokenToRevoke(null)
  }

  return (
    <div className="min-h-screen text-[var(--text)]">
      <header className="app-header sticky top-0 z-30 border-b border-[var(--border)] bg-[var(--glass-header)] backdrop-blur-xl">
        <div className="mx-auto flex h-16 max-w-7xl items-center gap-5 px-5 lg:px-8">
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

            GitForge
          </Link>

          <span
            aria-hidden="true"
            className="hidden h-6 w-px bg-[var(--hover)] sm:block"
          />

          <Link
            className="hidden items-center gap-2 text-sm text-[var(--muted)] transition hover:text-[var(--text)] sm:inline-flex"
            to="/dashboard"
          >
            <ArrowLeft
              aria-hidden="true"
              className="size-4"
            />

            Dashboard
          </Link>

          <button
            className="ml-auto inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-500"
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

            New token
          </button>
        </div>
      </header>

      <main className="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        <section className="border-b border-[var(--border)] pb-8">
          <p className="text-sm text-[var(--accent)]">
            Developer settings
          </p>

          <h1 className="mt-1 text-3xl font-bold tracking-tight text-[var(--text)]">
            Personal access tokens
          </h1>

          <p className="mt-3 max-w-3xl leading-7 text-[var(--muted)]">
            Create credentials for Git Smart HTTP authentication.
            Tokens are separate from your browser session and can be
            revoked independently.
          </p>
        </section>

        <section
          aria-labelledby="token-security-title"
          className="mt-8 flex gap-4 rounded-xl border border-blue-400/20 bg-blue-500/10 p-5"
        >
          <span className="grid size-11 shrink-0 place-items-center rounded-lg bg-blue-500/15 text-[var(--accent)]">
            <ShieldCheck
              aria-hidden="true"
              className="size-5"
            />
          </span>

          <div>
            <h2
              className="font-semibold text-[var(--text)]"
              id="token-security-title"
            >
              Use a PAT for Git authentication
            </h2>

            <p className="mt-1 text-sm leading-6 text-[var(--muted)]">
              Enter your GitForge username as the Git HTTP username and
              the generated token as the password. Repository
              authorization is enforced separately from the credential.
            </p>
          </div>
        </section>

        <section className="mt-8 overflow-hidden rounded-2xl border border-[var(--border)] glass-panel">
          <header className="flex items-center justify-between border-b border-[var(--border)] px-6 py-5">
            <div>
              <h2 className="font-semibold text-[var(--text)]">
                Personal access tokens
              </h2>

              <p className="mt-1 text-sm text-[var(--muted)]">
                {activeTokens.length ===
                1
                  ? '1 active token'
                  : `${activeTokens.length} active tokens`}
                {' · '}
                {tokens.length} total
              </p>
            </div>

            <button
              aria-label="Refresh tokens"
              className="rounded-lg p-2 text-[var(--muted)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)] disabled:opacity-50"
              disabled={
                tokensQuery.isFetching
              }
              onClick={() =>
                tokensQuery.refetch()
              }
              type="button"
            >
              <RefreshCw
                aria-hidden="true"
                className={`size-5 ${
                  tokensQuery.isFetching
                    ? 'animate-spin'
                    : ''
                }`}
              />
            </button>
          </header>

          {tokensQuery.isPending ? (
            <div
              className="grid min-h-72 place-items-center"
              role="status"
            >
              <div className="text-center">
                <LoaderCircle
                  aria-hidden="true"
                  className="mx-auto size-7 animate-spin text-[var(--accent)]"
                />

                <p className="mt-3 text-sm text-[var(--muted)]">
                  Loading access tokens...
                </p>
              </div>
            </div>
          ) : tokensQuery.isError ? (
            <div className="grid min-h-72 place-items-center px-6 py-10">
              <div className="max-w-md text-center">
                <AlertTriangle
                  aria-hidden="true"
                  className="mx-auto size-8 text-[var(--red)]"
                />

                <h3 className="mt-4 font-semibold text-[var(--text)]">
                  Unable to load access tokens
                </h3>

                <p className="mt-2 text-sm leading-6 text-[var(--muted)]">
                  {getApiErrorMessage(
                    tokensQuery.error,
                    'The token list could not be loaded. Try again.',
                  )}
                </p>

                <button
                  className="mt-5 inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-[var(--border)] px-4 text-sm font-semibold text-[var(--text)] transition hover:bg-[var(--subtle)]"
                  onClick={() =>
                    tokensQuery.refetch()
                  }
                  type="button"
                >
                  <RefreshCw
                    aria-hidden="true"
                    className="size-4"
                  />

                  Try again
                </button>
              </div>
            </div>
          ) : tokens.length === 0 ? (
            <div className="grid min-h-72 place-items-center px-6 py-10">
              <div className="max-w-md text-center">
                <span className="mx-auto grid size-16 place-items-center rounded-2xl border border-[var(--border)] bg-[var(--inset)] text-[var(--muted)]">
                  <KeyRound
                    aria-hidden="true"
                    className="size-7"
                  />
                </span>

                <h3 className="mt-5 font-semibold text-[var(--text)]">
                  No personal access tokens
                </h3>

                <p className="mt-2 text-sm leading-6 text-[var(--muted)]">
                  Create a token before authenticating Git operations
                  against a private repository or pushing changes.
                </p>

                <button
                  className="mt-5 inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-500"
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

                  Create your first token
                </button>
              </div>
            </div>
          ) : (
            <ul className="divide-y divide-white/10">
              {tokens.map(
                (token) => {
                  const isRevoked =
                    token.revoked_at !==
                    null

                  return (
                    <li
                      className="flex flex-col gap-5 px-6 py-5 sm:flex-row sm:items-center"
                      key={
                        token.id
                      }
                    >
                      <span
                        className={
                          isRevoked
                            ? 'grid size-11 shrink-0 place-items-center rounded-lg bg-red-500/10 text-[var(--red)]'
                            : 'grid size-11 shrink-0 place-items-center rounded-lg bg-blue-500/10 text-[var(--accent)]'
                        }
                      >
                        <KeyRound
                          aria-hidden="true"
                          className="size-5"
                        />
                      </span>

                      <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-2">
                          <h3 className="truncate font-semibold text-[var(--text)]">
                            {
                              token.name
                            }
                          </h3>

                          <span
                            className={
                              isRevoked
                                ? 'rounded-full border border-red-400/20 bg-red-500/10 px-2 py-0.5 text-xs text-[var(--red)]'
                                : 'rounded-full border border-emerald-400/20 bg-emerald-500/10 px-2 py-0.5 text-xs text-[var(--emerald)]'
                            }
                          >
                            {isRevoked
                              ? 'Revoked'
                              : 'Active'}
                          </span>
                        </div>

                        <dl className="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-xs text-[var(--muted)]">
                          <div className="flex items-center gap-1.5">
                            <CalendarDays
                              aria-hidden="true"
                              className="size-3.5"
                            />

                            <dt className="sr-only">
                              Created
                            </dt>

                            <dd>
                              Created{' '}
                              {formatDate(
                                token.created_at,
                              )}
                            </dd>
                          </div>

                          {token.revoked_at !==
                            null && (
                            <div className="flex items-center gap-1.5">
                              <CalendarDays
                                aria-hidden="true"
                                className="size-3.5"
                              />

                              <dt className="sr-only">
                                Revoked
                              </dt>

                              <dd>
                                Revoked{' '}
                                {formatDate(
                                  token.revoked_at,
                                )}
                              </dd>
                            </div>
                          )}
                        </dl>
                      </div>

                      {!isRevoked && (
                        <button
                          className="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-lg border border-red-400/20 px-3 text-sm font-medium text-[var(--red)] transition hover:bg-red-500/10"
                          onClick={() => {
                            revokeMutation.reset()

                            setTokenToRevoke(
                              token,
                            )
                          }}
                          type="button"
                        >
                          <Trash2
                            aria-hidden="true"
                            className="size-4"
                          />

                          Revoke
                        </button>
                      )}
                    </li>
                  )
                },
              )}
            </ul>
          )}
        </section>
      </main>

      <CreatePersonalAccessTokenDialog
        isOpen={
          isCreateDialogOpen
        }
        onClose={() =>
          setIsCreateDialogOpen(
            false,
          )
        }
        onCreated={() =>
          undefined
        }
      />

      {tokenToRevoke !== null && (
        <div
          aria-labelledby="revoke-token-title"
          aria-modal="true"
          className="fixed inset-0 z-50 grid place-items-center bg-black/70 px-4 backdrop-blur-sm"
          role="dialog"
        >
          <button
            aria-label="Close revoke token dialog"
            className="absolute inset-0 cursor-default"
            disabled={
              revokeMutation.isPending
            }
            onClick={
              closeRevokeDialog
            }
            type="button"
          />

          <section className="relative w-full max-w-md rounded-2xl border border-[var(--border)] glass-panel shadow-2xl shadow-black/50">
            <header className="flex items-start justify-between border-b border-[var(--border)] px-6 py-5">
              <div>
                <h2
                  className="text-xl font-semibold text-[var(--text)]"
                  id="revoke-token-title"
                >
                  Revoke access token?
                </h2>

                <p className="mt-1 text-sm text-[var(--muted)]">
                  This action takes effect immediately.
                </p>
              </div>

              <button
                aria-label="Close"
                className="rounded-lg p-2 text-[var(--muted)] transition hover:bg-[var(--subtle)] hover:text-[var(--text)] disabled:opacity-50"
                disabled={
                  revokeMutation.isPending
                }
                onClick={
                  closeRevokeDialog
                }
                type="button"
              >
                <X
                  aria-hidden="true"
                  className="size-5"
                />
              </button>
            </header>

            <div className="space-y-5 p-6">
              <div className="flex gap-3 rounded-xl border border-red-400/20 bg-red-500/10 p-4">
                <AlertTriangle
                  aria-hidden="true"
                  className="mt-0.5 size-5 shrink-0 text-[var(--red)]"
                />

                <p className="text-sm leading-6 text-[var(--red)]">
                  Git authentication using{' '}
                  <strong>
                    {
                      tokenToRevoke.name
                    }
                  </strong>{' '}
                  will stop working. The token cannot be reactivated.
                </p>
              </div>

              {revokeMutation.isError && (
                <div
                  className="rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-[var(--red)]"
                  role="alert"
                >
                  {getApiErrorMessage(
                    revokeMutation.error,
                    'Unable to revoke the token. Try again.',
                  )}
                </div>
              )}

              <div className="flex flex-col-reverse gap-3 border-t border-[var(--border)] pt-5 sm:flex-row sm:justify-end">
                <button
                  className="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--border)] px-4 text-sm font-semibold text-[var(--text)] transition hover:bg-[var(--subtle)] disabled:opacity-50"
                  disabled={
                    revokeMutation.isPending
                  }
                  onClick={
                    closeRevokeDialog
                  }
                  type="button"
                >
                  Cancel
                </button>

                <button
                  className="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-semibold text-[var(--text)] transition hover:bg-red-500 disabled:opacity-60"
                  disabled={
                    revokeMutation.isPending
                  }
                  onClick={
                    confirmRevocation
                  }
                  type="button"
                >
                  {revokeMutation.isPending ? (
                    <LoaderCircle
                      aria-hidden="true"
                      className="size-4 animate-spin"
                    />
                  ) : (
                    <Trash2
                      aria-hidden="true"
                      className="size-4"
                    />
                  )}

                  {revokeMutation.isPending
                    ? 'Revoking...'
                    : 'Revoke token'}
                </button>
              </div>
            </div>
          </section>
        </div>
      )}
    </div>
  )
}