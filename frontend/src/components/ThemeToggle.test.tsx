import { render, screen, fireEvent, cleanup } from '@testing-library/react'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { ThemeToggle } from './ThemeToggle'

describe('appearance preference', () => {
  beforeEach(() => {
    localStorage.clear()
    vi.stubGlobal('matchMedia', vi.fn(() => ({ matches: false })))
  })
  afterEach(() => { cleanup(); vi.unstubAllGlobals(); localStorage.clear(); delete document.documentElement.dataset.theme })
  it('uses system appearance when no preference exists', () => {
    vi.stubGlobal('matchMedia', vi.fn(() => ({ matches: true })))
    render(<ThemeToggle />)
    expect(document.documentElement.dataset.theme).toBe('dark')
    expect(screen.getByRole('button', { name: 'Switch to light mode' })).toBeInTheDocument()
  })
  it('switches modes and restores the saved choice on remount', () => {
    const view = render(<ThemeToggle />)
    fireEvent.click(screen.getByRole('button', { name: 'Switch to dark mode' }))
    expect(document.documentElement.dataset.theme).toBe('dark')
    expect(localStorage.getItem('gitforge-theme')).toBe('dark')
    view.unmount()
    render(<ThemeToggle />)
    expect(screen.getByRole('button', { name: 'Switch to light mode' })).toBeInTheDocument()
    fireEvent.click(screen.getByRole('button', { name: 'Switch to light mode' }))
    expect(document.documentElement.style.colorScheme).toBe('light')
  })
  it('remains usable when browser storage is blocked', () => {
    vi.spyOn(Storage.prototype, 'getItem').mockImplementation(() => { throw new Error('blocked') })
    vi.spyOn(Storage.prototype, 'setItem').mockImplementation(() => { throw new Error('blocked') })
    render(<ThemeToggle />)
    fireEvent.click(screen.getByRole('button', { name: 'Switch to dark mode' }))
    expect(document.documentElement.dataset.theme).toBe('dark')
    vi.restoreAllMocks()
  })
})
