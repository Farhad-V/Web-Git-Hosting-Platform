import { useEffect, useState } from 'react'
import { ArrowLeft, Download, LoaderCircle } from 'lucide-react'
import { apiClient } from '../../lib/api'

export function PdfViewer({ repositoryId, branch, path, onBack }: { repositoryId: number; branch: string; path: string; onBack: () => void }) {
  const [document, setDocument] = useState<{ url: string; size: number } | null>(null)
  const [error, setError] = useState(false)
  useEffect(() => {
    const controller = new AbortController()
    let url: string | undefined
    apiClient.get<Blob>(`/repositories/${repositoryId}/pdf`, {
      params: { branch, path }, responseType: 'blob', timeout: 120000, signal: controller.signal,
      headers: { Accept: 'application/pdf' },
    }).then(({ data }) => {
      if (controller.signal.aborted) return
      url = URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
      setDocument({ url, size: data.size })
    }).catch(() => { if (!controller.signal.aborted) setError(true) })
    return () => { controller.abort(); if (url) URL.revokeObjectURL(url) }
  }, [repositoryId, branch, path])
  return (
    <section aria-label="PDF preview">
      <div className="flex flex-wrap items-center gap-3 border-b border-[var(--border)] px-5 py-4">
        <button aria-label="Back to files" className="rounded-lg border border-[var(--border)] p-2" onClick={onBack} type="button"><ArrowLeft size={18} aria-hidden="true" /></button>
        <div className="min-w-0 flex-1"><h3 className="break-all text-sm font-semibold">{path.split('/').pop()}</h3><p className="mt-1 text-xs text-[var(--muted)]">PDF document{document ? ` · ${(document.size / 1048576).toFixed(1)} MB` : ''}</p></div>
        {document && <a href={document.url} download={path.split('/').pop()} className="inline-flex items-center gap-2 rounded-lg border border-[var(--border)] px-3 py-2 text-sm"><Download size={16} aria-hidden="true" /> Download</a>}
      </div>
      {!document && !error && <p role="status" className="flex items-center justify-center gap-2 p-12 text-sm text-[var(--muted)]"><LoaderCircle className="animate-spin" size={18} aria-hidden="true" /> Loading PDF…</p>}
      {error && <p role="alert" className="p-8 text-sm text-[var(--red)]">The PDF could not be loaded. Check your access and refresh the repository.</p>}
      {document && <><iframe title={`PDF preview: ${path}`} src={document.url} className="h-[75vh] min-h-96 w-full border-0 bg-white" /><p className="px-5 py-3 text-xs text-[var(--muted)]">If your browser cannot display the PDF, use Download to open it in your PDF reader.</p></>}
    </section>
  )
}
