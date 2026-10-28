import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../../api'
import Container from '../site/Container'
import StrokeIcon from './StrokeIcon'
import { GUIDE_ARTICLES, GUIDE_CATEGORIES } from './guidesData'
import '../../styles/frames.css'

export default function GuidesHub() {
  const [query, setQuery] = useState('')
  const [category, setCategory] = useState('All')
  const [newest, setNewest] = useState(true)
  const [email, setEmail] = useState('')
  const [note, setNote] = useState('')

  const featured = GUIDE_ARTICLES.find((article) => article.featured)
  const cards = useMemo(() => {
    const q = query.trim().toLowerCase()
    const list = GUIDE_ARTICLES.filter((article) => !article.featured).filter((article) => {
      if (category !== 'All' && article.category !== category) return false
      if (!q) return true
      return `${article.title} ${article.excerpt} ${article.category}`.toLowerCase().includes(q)
    })
    return newest ? list : [...list].reverse()
  }, [query, category, newest])

  const subscribe = async (event) => {
    event.preventDefault()
    try {
      await api.post('/newsletter/subscribe', { email, source: 'good-to-know' })
      setNote('Thanks, you are on the list.')
      setEmail('')
    } catch {
      setNote('Could not subscribe just now. Try again in a moment.')
    }
  }

  return (
    <div className="tb-frame">
      <section className="tb-guides-hero">
        <Container>
          <nav className="tb-crumbs">
            <Link to="/">Home</Link>
            <StrokeIcon name="chevron" size={12} />
            <strong>Good to Know</strong>
          </nav>
          <div className="tb-guides-top">
            <div>
              <p className="tb-kicker">GOOD TO KNOW</p>
              <h1>Guides, routes & <em>tips</em></h1>
              <p>Itineraries, day trips, driving advice and packing lists from people who drive Iceland’s roads every day.</p>
            </div>
            <label className="tb-guides-search">
              <StrokeIcon name="search" size={18} />
              <input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Search guides" aria-label="Search guides" />
            </label>
          </div>
          <div className="tb-guide-tabs">
            {GUIDE_CATEGORIES.map((item) => (
              <button key={item} type="button" className={category === item ? 'is-on' : ''} onClick={() => setCategory(item)}>{item}</button>
            ))}
          </div>
        </Container>
      </section>

      {featured && category === 'All' && !query && (
        <section className="tb-featured">
          <Container>
            <article>
              <img src={featured.image} alt="" />
              <div>
                <div className="tb-pills">
                  <span><StrokeIcon name="star" size={14} /> Featured</span>
                  <em>{featured.category}</em>
                </div>
                <h2>How to plan a road trip in <em>7 days</em></h2>
                <p>{featured.excerpt}</p>
                <div className="tb-meta">
                  <span><StrokeIcon name="clock" size={14} /> {featured.read}</span>
                  <span><StrokeIcon name="calendar" size={14} /> {featured.date}</span>
                </div>
                <Link className="tb-btn" to={`/good-to-know/${featured.slug}`}>Read the guide <StrokeIcon name="arrow" size={16} /></Link>
              </div>
            </article>
          </Container>
        </section>
      )}

      <section>
        <Container>
          <div className="tb-guide-head">
            <h2>Latest guides</h2>
            <i />
            <button type="button" className="tb-sort" onClick={() => setNewest((value) => !value)}>
              Sort: {newest ? 'Newest' : 'Oldest'} <StrokeIcon name="chevronDown" size={12} />
            </button>
          </div>
          <div className="tb-guide-grid">
            {cards.map((article) => (
              <Link key={article.slug} className="tb-guide-card" to={`/good-to-know/${article.slug}`}>
                <img src={article.image} alt="" />
                <small>{article.category.toUpperCase()}</small>
                <h3>{article.title}</h3>
                <p>{article.excerpt}</p>
                <footer>
                  <StrokeIcon name="clock" size={13} /> {article.read}
                  <b>Read <StrokeIcon name="arrow" size={13} /></b>
                </footer>
              </Link>
            ))}
          </div>
          <div className="tb-pages" aria-label="Pagination">
            <button type="button" aria-label="Previous"><StrokeIcon name="chevronLeft" size={16} /></button>
            <button type="button" className="is-on">1</button>
            <button type="button">2</button>
            <button type="button">3</button>
            <span>…</span>
            <button type="button">6</button>
            <button type="button" aria-label="Next"><StrokeIcon name="chevron" size={16} /></button>
          </div>
          <form className="tb-news" onSubmit={subscribe}>
            <i><StrokeIcon name="mail" size={28} /></i>
            <div>
              <h2>New guides, straight to your inbox.</h2>
              <p>Road conditions, seasonal deals & hidden detours.</p>
              {note && <p>{note}</p>}
            </div>
            <label>
              <StrokeIcon name="mail" size={16} />
              <input type="email" required placeholder="your@email.com" value={email} onChange={(event) => setEmail(event.target.value)} aria-label="Email" />
            </label>
            <button className="tb-btn gold" type="submit">Subscribe</button>
          </form>
        </Container>
      </section>
    </div>
  )
}
