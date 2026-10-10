import { ArrowRight, Check, Code2, FileCode2, Folder, GitBranch, GitCommitHorizontal, LockKeyhole, ShieldCheck, Terminal } from 'lucide-react'
import { Link } from 'react-router-dom'

export function HomeHero() {
  return (
    <section className="forge-hero" aria-labelledby="hero-heading">
      <div className="hero-atmosphere" aria-hidden="true"><i /><i /><i /></div>
      <div className="hero-copy">
        <p className="hero-eyebrow"><span /> Self-hosted and under your control</p>
        <h1 id="hero-heading">Your code.<br /><span>Your infrastructure.</span></h1>
        <p className="hero-description">A home for your best work. Host your repositories, explore your code, and keep every commit on infrastructure you control.</p>
        <div className="hero-actions">
          <Link className="hero-primary" to="/register">Create your account <span><ArrowRight size={22} aria-hidden="true" /></span></Link>
          <a className="hero-secondary" href="#features">Explore GitForge <ArrowRight size={16} aria-hidden="true" /></a>
        </div>
        <p className="hero-assurance"><ShieldCheck size={15} aria-hidden="true" /> Private repositories. Familiar Git workflows.</p>
      </div>
      <div className="product-stage" role="img" aria-label="Illustrative GitForge repository preview with files, commit history, and a Git terminal">
        <div className="stage-orbit" aria-hidden="true" />
        <div className="preview-badge"><LockKeyhole size={16} /> Your code stays yours</div>
        <article className="preview-repository">
          <div className="preview-topbar"><span className="preview-brand"><GitBranch size={18} /> GitForge</span><span className="preview-caption">Repository preview</span><span className="window-dots"><i /><i /><i /></span></div>
          <div className="preview-body">
            <p className="preview-path">workspace <span>/</span> <strong>platform</strong><span className="preview-private"><LockKeyhole size={11} /> Private</span></p>
            <p className="preview-description">A place to build something extraordinary.</p>
            <div className="preview-tabs"><span><Code2 size={15} /> Code</span><span><GitCommitHorizontal size={15} /> Commits</span></div>
            <div className="preview-branch"><span><GitBranch size={14} /> main</span><span>Latest commit <strong>8f3a2c1</strong></span></div>
            <div className="preview-files">
              {[[Folder, 'src', 'Build a better experience'], [Folder, 'public', 'Update application assets'], [FileCode2, 'README.md', 'A fresh start'], [FileCode2, 'package.json', 'Keep things up to date']].map(([Icon, name, message]) => {
                const FileIcon = Icon as typeof Folder
                return <div key={String(name)}><FileIcon size={16} /><strong>{String(name)}</strong><span>{String(message)}</span></div>
              })}
            </div>
            <div className="preview-readme"><span><FileCode2 size={14} /> README.md</span><h2>Make great things.</h2><p>Small ideas. Thoughtful commits. Endless possibilities.</p><div><i /><i /><i /></div></div>
          </div>
        </article>
        <article className="preview-terminal"><p><Terminal size={15} /> Your everyday workflow <span>HTTP</span></p><code><span>$</span> git push origin main</code><div><Check size={14} /> Changes pushed. Ready for what’s next.</div></article>
        <article className="preview-commit"><span className="commit-icon"><GitCommitHorizontal size={20} /></span><div><strong>Every commit, a step forward.</strong><p>main <span>·</span> All your work, in one place</p></div><span className="commit-check"><Check size={16} /></span></article>
      </div>
      <div className="hero-foundation"><span>Built around your workflow</span><p><GitBranch size={17} /> Git Smart HTTP</p><p><LockKeyhole size={17} /> Owner-controlled access</p><p><Terminal size={17} /> Your tools. Your server.</p></div>
    </section>
  )
}
