import { afterEach, describe, expect, it, vi } from 'vitest'
import { getGitCloneUrl } from './git-url'

afterEach(() => {
  vi.unstubAllEnvs()
})

describe('Git clone URLs', () => {
  it('uses the site origin in production when no override is configured', () => {
    vi.stubEnv('DEV', false)
    vi.stubEnv('VITE_GIT_BASE_URL', '')
    expect(getGitCloneUrl('owner/project')).toBe(`${window.location.origin}/owner/project.git`)
  })

  it('keeps the local Laravel address in development', () => {
    vi.stubEnv('DEV', true)
    vi.stubEnv('VITE_GIT_BASE_URL', '')
    expect(getGitCloneUrl('owner/project')).toBe('http://127.0.0.1:8000/owner/project.git')
  })

  it('honors an explicitly configured Git server', () => {
    vi.stubEnv('DEV', false)
    vi.stubEnv('VITE_GIT_BASE_URL', 'https://git.example.test/')
    expect(getGitCloneUrl('owner/project')).toBe('https://git.example.test/owner/project.git')
  })
})
