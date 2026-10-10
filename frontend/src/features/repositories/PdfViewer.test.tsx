import { render, screen, waitFor, cleanup } from '@testing-library/react'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { apiClient } from '../../lib/api'
import { PdfViewer } from './PdfViewer'
vi.mock('../../lib/api', () => ({ apiClient: { get: vi.fn() } }))
describe('PDF viewer', () => {
  beforeEach(() => {
    vi.stubGlobal('URL', { createObjectURL: vi.fn(() => 'blob:pdf-preview'), revokeObjectURL: vi.fn() })
  })
  afterEach(() => { cleanup(); vi.unstubAllGlobals(); vi.clearAllMocks() })
  it('loads a PDF with authenticated API settings and releases its URL on exit', async () => {
    vi.mocked(apiClient.get).mockResolvedValue({ data: new Blob(['%PDF-1.7'], { type: 'application/pdf' }) })
    const view = render(<PdfViewer repositoryId={2} branch="main" path="docs/manual.pdf" onBack={vi.fn()} />)
    expect(screen.getByRole('status')).toHaveTextContent('Loading PDF')
    await waitFor(() => expect(screen.getByTitle('PDF preview: docs/manual.pdf')).toHaveAttribute('src', 'blob:pdf-preview'))
    expect(screen.getByRole('link', { name: 'Download' })).toHaveAttribute('download', 'manual.pdf')
    expect(apiClient.get).toHaveBeenCalledWith('/repositories/2/pdf', expect.objectContaining({ params: { branch: 'main', path: 'docs/manual.pdf' }, responseType: 'blob' }))
    view.unmount()
    expect(URL.revokeObjectURL).toHaveBeenCalledWith('blob:pdf-preview')
  })
  it('shows an accessible failure without embedding an unauthorized document', async () => {
    vi.mocked(apiClient.get).mockRejectedValue(new Error('Forbidden'))
    render(<PdfViewer repositoryId={2} branch="main" path="manual.pdf" onBack={vi.fn()} />)
    expect(await screen.findByRole('alert')).toHaveTextContent('could not be loaded')
    expect(screen.queryByTitle('PDF preview: manual.pdf')).not.toBeInTheDocument()
  })
})
