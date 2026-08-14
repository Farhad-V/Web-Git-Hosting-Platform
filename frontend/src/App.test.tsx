import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it } from 'vitest'
import App from './App'

function renderApp(initialRoute = '/') {
  return render(
    <MemoryRouter initialEntries={[initialRoute]}>
      <App />
    </MemoryRouter>,
  )
}

describe('App', () => {
  it('renders the GitForge landing page', () => {
    renderApp()

    expect(
      screen.getByRole('heading', {
        name: /your code\.\s*your infrastructure\./i,
      }),
    ).toBeInTheDocument()

    expect(
      screen.getByRole('link', { name: /create your account/i }),
    ).toHaveAttribute('href', '/register')

    expect(
      screen.getByText(/self-hosted and under your control/i),
    ).toBeInTheDocument()
  })

  it('renders the sign-in route', () => {
    renderApp('/login')

    expect(
      screen.getByRole('heading', { name: /sign in/i }),
    ).toBeInTheDocument()

    expect(
      screen.getByText(
        /this screen will be implemented in the authentication stage/i,
      ),
    ).toBeInTheDocument()
  })
})