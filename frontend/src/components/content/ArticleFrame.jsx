import { Link } from 'react-router-dom'
import Container from '../site/Container'
import StrokeIcon from './StrokeIcon'
import { GUIDE_ARTICLES, SEVEN_DAY } from './guidesData'
import '../../styles/frames.css'

export default function ArticleFrame({ slug }) {
  const article = GUIDE_ARTICLES.find((item) => item.slug === slug) || GUIDE_ARTICLES[0]
  const related = GUIDE_ARTICLES.filter((item) => item.slug !== article.slug && !item.featured).slice(0, 3)
  const detailed = article.slug === 'how-to-plan-a-road-trip-in-7-days'

  const share = async (kind) => {
    const url = window.location.href
    if (kind === 'link') {
      try { await navigator.clipboard.writeText(url) } catch { /* ignore */ }
      return
    }
    if (kind === 'mail') window.location.href = `mailto:?subject=${encodeURIComponent(article.title)}&body=${encodeURIComponent(url)}`
  }

  return (
    <div className="tb-frame tb-article">
      <section className="tb-article-top">
        <Container>
          <Link className="tb-back" to="/good-to-know"><StrokeIcon name="chevronLeft" size={14} /> All articles</Link>
          <div className="tb-meta" style={{ marginTop: 28 }}>
            <span className="tb-pills"><em><StrokeIcon name="route" size={14} /> {article.category}</em></span>
            <span><StrokeIcon name="clock" size={14} /> {article.read}</span>
            <span><StrokeIcon name="calendar" size={14} /> {article.date}</span>
          </div>
          <h1>{article.titleBefore || article.title}{article.italic ? <em>{article.italic}</em> : null}</h1>
          <p className="deck">{article.deck || article.excerpt}</p>
          <img className="tb-article-hero" src={article.image} alt="" />
          <div className="tb-article-body">
            {detailed ? (
              <>
                <p>Seven days is the sweet spot for a first road trip in Iceland: enough time for the Golden Circle and the south coast all the way to the glacier lagoon, without spending every hour behind the wheel. This route starts and ends at Keflavík airport, so you can pick up your vehicle the moment you land.</p>
                <h2>Before you go</h2>
                <p>Book your campervan or car early — especially for June to August — and choose the insurance that matches your route. Gravel roads and sand on the south coast are the most common causes of damage, so most travellers add Premium or Platinum cover.</p>
                <div className="tb-tip">
                  <i><StrokeIcon name="sparkles" size={18} /></i>
                  <div>
                    <small>LOCAL TIP</small>
                    <p>Fill up whenever you pass a petrol station east of Vík — the next one can be 100 km away. Most pumps are card-only and ask for a PIN.</p>
                  </div>
                </div>
                <h2>The 7-day route</h2>
                {SEVEN_DAY.days.map((day) => (
                  <div className="tb-day" key={day[0]}>
                    <b>{day[0]}</b>
                    <div>
                      <small>{day[1]}</small>
                      <h3>{day[2]}</h3>
                      <p>{day[3]}</p>
                      <span><StrokeIcon name="route" size={13} /> {day[4]}</span>
                    </div>
                  </div>
                ))}
                <h3>Where to sleep</h3>
                <p>With a campervan you can stay at registered campsites along the whole route — Reykjavík, Vík and Skaftafell are open all year. Prefer a real bed? Mix in a guesthouse night in Vík or Hella; you can add it to the same booking.</p>
                <div className="tb-route-book">
                  <div>
                    <small>DRIVE THIS ROUTE</small>
                    <h3>Book a campervan for this route</h3>
                    <p>Pick up at Keflavík airport, unlimited mileage and free cancellation until 48h before pick-up.</p>
                    <Link className="tb-btn gold sm" to="/campervans">See campervans <StrokeIcon name="arrow" size={16} /></Link>
                  </div>
                  <div className="card">
                    <img src="/figma/camper-card.png" alt="" />
                    <strong>4×4 campervan</strong>
                    <p>Airport pick-up · unlimited km</p>
                    <Link className="tb-btn sm" to="/campervans">Book <StrokeIcon name="arrow" size={14} /></Link>
                  </div>
                </div>
                <h2>Driving tips for the route</h2>
                <p>Speed limits are 90 km/h on paved roads, 80 km/h on gravel and 50 km/h in towns. Headlights stay on day and night, and you should never stop on the road for photos — use the marked pull-outs instead.</p>
                <h3>Weather & road conditions</h3>
                <p>Check road.is and vedur.is every morning; wind is the biggest risk for high vehicles on the south coast. In winter, pick a winter-ready vehicle and keep an extra day free in case a pass closes.</p>
              </>
            ) : (
              <>
                <p>{article.excerpt}</p>
                <h2>What to know</h2>
                <p>This guide is written by the MyTerraBook team in Reykjanesbær. Dates, road openings and campsite facilities change with the season — check road.is the morning you drive, and confirm your vehicle is approved for the roads on your route.</p>
                <p>When you are ready, search campervans, cars and guesthouses for the same dates. Nothing is charged until the host approves the request.</p>
                <Link className="tb-btn" to="/campervans">Find a vehicle <StrokeIcon name="arrow" size={16} /></Link>
              </>
            )}
            <div className="tb-author">
              <i>B</i>
              <div>
                <strong>Written by the MyTerraBook team</strong>
                <p>Local guides from Reykjanesbær · Updated {article.date}</p>
              </div>
              <div className="share">
                <span>Share</span>
                <button type="button" aria-label="Copy link" onClick={() => share('link')}><StrokeIcon name="link" size={16} /></button>
                <a href="https://www.facebook.com/myterrabook" aria-label="Facebook"><StrokeIcon name="facebook" size={16} /></a>
                <a href="https://www.instagram.com/myterrabook" aria-label="Instagram"><StrokeIcon name="instagram" size={16} /></a>
                <button type="button" aria-label="Email" onClick={() => share('mail')}><StrokeIcon name="mail" size={16} /></button>
              </div>
            </div>
          </div>
        </Container>
      </section>
      <section className="tb-related">
        <Container>
          <div className="tb-related-head">
            <div>
              <p className="tb-kicker">KEEP READING</p>
              <h2>Related guides</h2>
            </div>
            <Link className="tb-btn ghost sm" to="/good-to-know">All guides</Link>
          </div>
          <div className="tb-guide-grid">
            {related.map((item) => (
              <Link key={item.slug} className="tb-guide-card" to={`/good-to-know/${item.slug}`}>
                <img src={item.image} alt="" />
                <small>{item.category.toUpperCase()}</small>
                <h3>{item.title}</h3>
                <p>{item.excerpt}</p>
                <footer><StrokeIcon name="clock" size={13} /> {item.read}<b>Read <StrokeIcon name="arrow" size={13} /></b></footer>
              </Link>
            ))}
          </div>
        </Container>
      </section>
    </div>
  )
}
