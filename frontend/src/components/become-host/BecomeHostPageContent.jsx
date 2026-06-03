import { Link } from 'react-router-dom'
import HostPhoto from './HostPhoto'
import HostStars, { HostRatingStars } from './HostStars'
import { becomeHostImages, faqItems, howTabs, proofPairs, reviewColumns } from '../../data/becomeHostData'

const LOGO_MARK = (
  <svg viewBox="0 0 56.97 71.17" fill="currentColor" aria-hidden="true">
    <path d="M.07,71.17l-.07-.34V.07l.34-.07h34.32c9.88,0,18.36,7.39,19.31,16.83.48,4.78.31,8.6-3.62,13.23-.33.39-.8.59-1.39.59-1.5,0-3.34-1.31-3.42-1.36l-.27-.19.24-.23c4.22-4.02,5.28-9.84,3.2-15.3-2.09-5.49-7.68-9.18-13.9-9.18H4.39v35.74l.94-.98c2.85-3,6.51-4.96,10.6-5.68-.21-.92-.5-1.92-.79-2.88-.19-.63-.38-1.26-.54-1.86l-.11-.4c-.09-.3-.18-.62-.23-.9l.02-.11.11-.28.11-.07.13.04h.31s.04,0,.06,0c.35,0,.51.03.63.13.63.8,1.23,1.58,1.82,2.36,1,1.32,2.01,2.64,3.07,3.92l.15.06,12.23-.93s.06-.04.11-.28l-4.97-18.29,1.96.13.2.09,11.24,17.75.3.08c1.17-.15,2.48-.23,3.79-.32l.57-.04c.42-.03.86-.07,1.31-.1.92-.08,1.88-.16,2.81-.16.77,0,1.43.06,2.03.17.75.15,3.19.75,3,2.39-.22,1.86-3.82,2.48-4.77,2.55-.71.05-1.45.06-2.19.06s-1.45-.01-2.17-.03c-.72-.01-1.44-.03-2.15-.03-.37,0-.75,0-1.11.01h-.32c-.51,0-.88.03-1,.23l-9.72,18.4-1.91.38-.12-.27-.04-.21,3.45-18.25c-.05-.09-.13-.16-.36-.19l-12.18.1-.08.1-4.35,6.61-1.18.23-.15-.25-.04-.2,1.01-5.25c-6.63,1.6-11.45,7.67-11.45,14.55v14.28h33.6c8.04,0,14.58-6.58,14.58-14.66,0-5.07-2.56-9.7-6.85-12.38l-.21-.13.09-.3c.05-.07.49-.5,1.03-1.02.6-.57,1.39-.89,2.22-.89.79,0,1.54.29,2.1.82,3.82,3.56,6.02,8.62,6.02,13.89,0,10.5-8.51,19.05-18.97,19.05H.07Z" />
  </svg>
)

function ProofPair({ pair, duplicate = false }) {
  return (
    <>
      <div className="pcol-tall" aria-hidden={duplicate || undefined}>
        <HostPhoto src={pair.tall.image} alt={pair.tall.name} />
        <div className="pmeta">
          <div className="nm">{pair.tall.name}</div>
          <div className="rl">{pair.tall.role}</div>
        </div>
      </div>
      <div className="pcol-stack" aria-hidden={duplicate || undefined}>
        {pair.stack.map((item) =>
          item.type === 'stat' ? (
            <div key={`${item.big}-${duplicate}`} className={`psq stat ${item.variant}`}>
              <div className="big">{item.big}</div>
              <div className="desc">{item.desc}</div>
            </div>
          ) : (
            <div key={`${item.name}-${duplicate}`} className="psq photo">
              <HostPhoto src={item.image} alt={item.name} />
              <div className="pmeta">
                <div className="nm">{item.name}</div>
                <div className="rl">{item.role}</div>
              </div>
            </div>
          ),
        )}
      </div>
    </>
  )
}

function ReviewCard({ review, duplicate = false }) {
  return (
    <div className="rev" aria-hidden={duplicate || undefined}>
      <div className="ravatar">
        <HostPhoto src="" alt={review.name} style={{ background: review.fill }} />
      </div>
      <div className="nm">{review.name}</div>
      <div className="rl">{review.role}</div>
      <div className="rdiv" />
      <HostStars />
      <blockquote>{review.quote}</blockquote>
    </div>
  )
}

export default function BecomeHostPageContent() {
  return (
    <>
      <div className="topbar">
        <div className="wrap">
          <span>
            Iceland hosts earned <strong>€2.4M+</strong> last year.
          </span>
          <a href="#signup">
            See what you&apos;d earn
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </a>
        </div>
      </div>

      <header className="nav">
        <div className="wrap">
          <Link to="/" className="logo">
            <span className="mark">{LOGO_MARK}</span>
            My<span className="terra">Terra</span>Book
          </Link>
          <nav className="main">
            <div className="nav-item" id="navWhy">
              <button className="nav-link" type="button">
                Why host
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                  <path d="m6 9 6 6 6-6" />
                </svg>
              </button>
              <div className="mega">
                <a className="mega-card" href="#how">
                  <span className="mega-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M5 13l4 4L19 7" />
                    </svg>
                  </span>
                  <span className="mega-tx">
                    <h4>Get started</h4>
                    <p>List your van or room in under 15 minutes.</p>
                  </span>
                </a>
                <a className="mega-card ochre" href="#feat">
                  <span className="mega-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M3 17l6-6 4 4 8-8" />
                      <path d="M21 7v6h-6" />
                    </svg>
                  </span>
                  <span className="mega-tx">
                    <h4>Grow your earnings</h4>
                    <p>Pricing tips and tools that lift your bookings.</p>
                  </span>
                </a>
                <a className="mega-card moss" href="#feat">
                  <span className="mega-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M4 19V5M10 19V9M16 19v-7M20 19V7" />
                    </svg>
                  </span>
                  <span className="mega-tx">
                    <h4>Host insights</h4>
                    <p>See demand across Iceland season by season.</p>
                  </span>
                </a>
                <a className="mega-card" href="#revs">
                  <span className="mega-ic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                      <path d="m12 3 2.5 5.5L20 9l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-.5L12 3Z" />
                    </svg>
                  </span>
                  <span className="mega-tx">
                    <h4>Success stories</h4>
                    <p>How hosts across Iceland are thriving.</p>
                  </span>
                </a>
              </div>
            </div>
            <a className="nav-link" href="#how">
              How it works
            </a>
            <a className="nav-link" href="#feat">
              Earnings
            </a>
            <a className="nav-link" href="#faq">
              Help center
            </a>
          </nav>
          <div className="nav-right">
            <Link className="signin" to="/login">
              Log in
            </Link>
            <a className="btn-host" href="#signup">
              <span className="cta-spin">Start hosting</span>
            </a>
            <button className="hamburger" type="button" aria-label="Menu">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                <path d="M4 7h16M4 12h16M4 17h16" />
              </svg>
            </button>
          </div>
        </div>
      </header>

      <section className="hero" id="signup">
        <div className="wrap">
          <div className="hero-copy">
            <h1 className="reveal-title" data-reveal-now="1">
              Built for real hosts, <span className="em">loved by travellers.</span>
            </h1>
            <p className="lead reveal-desc" data-reveal-now="1">
              Your campervan sits idle between trips and your spare room stays empty. Put them to work on the platform Iceland&apos;s travellers already trust.
            </p>
          </div>
          <div className="signup">
            <h3>Create your host account</h3>
            <p className="su-sub">Free to list. No commitment. Earn on your own schedule.</p>
            <form onSubmit={(e) => e.preventDefault()}>
              <div className="su-field">
                <label htmlFor="su-email">Email</label>
                <input id="su-email" type="email" placeholder="your@email.com" autoComplete="email" />
              </div>
              <div className="su-field">
                <label htmlFor="su-pass">Password</label>
                <input id="su-pass" type="password" placeholder="Create a password" autoComplete="new-password" />
              </div>
              <button className="su-btn" type="submit">
                <span className="cta-spin">Get started</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
              </button>
            </form>
            <div className="su-or">OR</div>
            <button className="su-google" type="button">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.24 1.5-1.7 4.4-5.5 4.4-3.3 0-6-2.7-6-6.1s2.7-6.1 6-6.1c1.9 0 3.1.8 3.8 1.5l2.6-2.5C17.1 3.1 14.8 2 12 2 6.9 2 2.8 6.1 2.8 11.2S6.9 20.4 12 20.4c5.9 0 9.8-4.1 9.8-9.9 0-.7-.1-1.2-.2-1.7H12Z" />
              </svg>
              Continue with Google
            </button>
            <p className="su-foot">
              Already a host? <Link to="/login">Log in</Link>
            </p>
          </div>
        </div>
      </section>

      <section className="proof">
        <div className="proof-marquee">
          <div className="proof-track" id="proofRail">
            {proofPairs.map((pair) => (
              <ProofPair key={pair.tall.name} pair={pair} />
            ))}
            {proofPairs.map((pair) => (
              <ProofPair key={`${pair.tall.name}-dup`} pair={pair} duplicate />
            ))}
          </div>
        </div>
      </section>

      <section className="how" id="how">
        <div className="wrap">
          <div className="how-head">
            <span className="eyebrow">
              <span className="rule" />
              How it works
            </span>
            <h2 className="reveal-title">
              From idle to earning, <span className="em">in four simple steps.</span>
            </h2>
          </div>
          <div className="how-tabs" id="howTabs">
            {howTabs.map((tab, index) => (
              <button key={tab.title} className={`how-tab ${index === 0 ? 'active' : ''}`} type="button" data-tab={index}>
                <span className="tn">{index + 1}.</span>
                {tab.title}
                <span className="bar" />
              </button>
            ))}
          </div>
          <div className="how-stage" id="howStage">
            {howTabs.map((tab, index) => (
              <div key={tab.title} className={`how-slide ${index === 0 ? 'active' : ''}`} data-panel={index}>
                <img src={tab.image} alt={tab.imageAlt} />
                <div className="how-cap">
                  {tab.caption}
                  <span className="muted">{tab.muted}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="feat" id="feat">
        <div className="wrap">
          <div className="feat-head">
            <div>
              <span className="eyebrow">
                <span className="rule" />
                Why hosts choose us
              </span>
              <h2 className="reveal-title">Everything you need to earn more.</h2>
            </div>
            <p className="fsub reveal-desc">We bring the travellers, the tools and the protection. You bring the van, the car or the spare room.</p>
          </div>
          <div className="bento">
            <div className="fcard clay v-textbottom">
              <div className="fc-art">
                <div className="gmini">
                  <div className="gicon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                      <circle cx="12" cy="12" r="9" />
                      <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" />
                    </svg>
                  </div>
                  <div className="gname">
                    Global
                    <br />
                    exposure
                  </div>
                  <div className="gchips">
                    <span className="gchip">Online publishers</span>
                    <span className="gchip">Transport</span>
                    <span className="gchip">OTAs</span>
                    <span className="gchip">Accommodation</span>
                  </div>
                </div>
              </div>
              <div className="fc-top">
                <h3>Get discovered across 30K+ touchpoints</h3>
                <p>We partner with 2.5K+ affiliates like Emirates, Expedia, Hilton, American Express and more — putting you in front of travellers as they plan and book their next trip.</p>
              </div>
            </div>
            <div className="fcard espresso v-texttop">
              <div className="fc-top">
                <h3>Premium ad placement</h3>
                <p>Targeted ads ensure you appear at the top of travellers&apos; search results at every stage of their planning.</p>
              </div>
              <div className="fc-art bleed-br">
                <HostPhoto src={becomeHostImages.cardHouse} alt="Search results preview" className="feat-google" />
              </div>
            </div>
            <div className="fcard espresso v-textbottom phone-card">
              <HostPhoto src={becomeHostImages.whyPhoto} alt="Phone in hand" className="feat-phone" />
              <div className="fc-top">
                <h3>Showcase your quality</h3>
                <p>Join a curated marketplace where every supplier is verified and only top activities are approved.</p>
              </div>
            </div>
            <div className="fcard clay v-textbottom">
              <div className="fc-art">
                <HostPhoto src={becomeHostImages.cardCar} alt="Analytics charts" className="feat-stats" />
              </div>
              <div className="fc-top">
                <h3>Turn data into insights</h3>
                <p>Data-driven insights and tools make it easy to optimize your listings, stay competitive and fill more slots. All on your own terms.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="revs" id="revs">
        <div className="wrap">
          <div className="revs-l">
            <span className="eyebrow">
              <span className="rule" />
              Host stories
            </span>
            <h2 className="reveal-title">
              Our hosts <span className="em">talk about us.</span>
            </h2>
            <p className="reveal-desc">Thousands of Icelanders are already turning idle vehicles and spare rooms into income. Here&apos;s what they say.</p>
            <div className="revs-agg">
              <div className="cluster">
                <HostPhoto src="" alt="Host" style={{ background: '#a9d4e6' }} />
                <HostPhoto src="" alt="Host" style={{ background: '#bcdcab' }} />
                <HostPhoto src="" alt="Host" style={{ background: '#f1d79a' }} />
              </div>
              <div>
                <div className="av-v">
                  4.8/5
                  <HostRatingStars />
                </div>
                <div className="av-l">Based on 14K+ host reviews</div>
              </div>
            </div>
          </div>
          <div className="revs-marquee">
            <div className="revs-col up">
              {reviewColumns.up.map((review) => (
                <ReviewCard key={review.name} review={review} />
              ))}
              {reviewColumns.up.map((review) => (
                <ReviewCard key={`${review.name}-dup`} review={review} duplicate />
              ))}
            </div>
            <div className="revs-col down">
              {reviewColumns.down.map((review) => (
                <ReviewCard key={review.name} review={review} />
              ))}
              {reviewColumns.down.map((review) => (
                <ReviewCard key={`${review.name}-dup`} review={review} duplicate />
              ))}
            </div>
          </div>
        </div>
      </section>

      <section className="faq" id="faq">
        <div className="wrap">
          <div className="faq-l">
            <span className="eyebrow">
              <span className="rule" />
              Good to know
            </span>
            <h2 className="reveal-title">Questions, answered.</h2>
            <p className="reveal-desc">Thinking about hosting but not sure where to start? Our team in Reykjavík is one message away.</p>
            <div className="faq-contact">
              <span className="ci">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                  <rect x="3" y="5" width="18" height="14" rx="2.5" />
                  <path d="m4 7 8 6 8-6" />
                </svg>
              </span>
              <span className="ct">
                Talk to the host team
                <b>
                  <a href="mailto:hosts@myterrabook.com">hosts@myterrabook.com</a>
                </b>
              </span>
            </div>
          </div>
          <div className="faq-list" id="faqList">
            {faqItems.map((item, index) => (
              <div key={item.num} className={`faq-item ${index === 0 ? 'open' : ''}`}>
                <button className="faq-q" type="button" aria-expanded={index === 0}>
                  <span className="num">{item.num}</span>
                  <span className="qt">{item.question}</span>
                  <svg className="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M12 5v14M5 12h14" />
                  </svg>
                </button>
                <div className="faq-a">
                  <div className="faq-a-inner">{item.answer}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="cta">
        <div className="wrap">
          <div className="cta-box">
            <div className="cta-topo" />
            <div className="cta-inner">
              <span className="cta-eyebrow">
                <span className="cd" />
                Start hosting
              </span>
              <h2 className="reveal-title">Got a van or a spare room sitting idle?</h2>
              <p className="reveal-desc">Join 1,800+ Iceland hosts already earning with MyTerraBook. It&apos;s free to list, and you could be booked within the week.</p>
              <div className="cta-actions">
                <a className="cta-btn solid" href="#signup">
                  <span className="cta-spin">Become a host</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                  </svg>
                </a>
                <button className="cta-btn ghost" type="button">
                  <span className="cta-spin">Estimate my earnings</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <footer className="ftr">
        <div className="wrap">
          <div className="ftr-top">
            <div className="ftr-brand">
              <div className="logo">
                <span className="mark">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M3 19h18" />
                    <path d="m4 17 5-9 4 7 3-4 4 6" />
                  </svg>
                </span>
                My<span className="terra">Terra</span>Book
              </div>
              <p className="ftr-tag">Iceland&apos;s locally-run platform for campervans, 4×4s, cars and guesthouses — booked in minutes, hosted with care.</p>
              <div className="ftr-social">
                <a href="#" aria-label="Instagram">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="5" />
                    <circle cx="12" cy="12" r="4" />
                    <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
                  </svg>
                </a>
                <a href="#" aria-label="Facebook">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.7c0-.9.3-1.5 1.5-1.5h1.5V4.6c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.2H8v3h2.6V21h2.9Z" />
                  </svg>
                </a>
              </div>
            </div>
            <div className="ftr-col">
              <h4>Host</h4>
              <ul>
                <li>
                  <a href="#how">How it works</a>
                </li>
                <li>
                  <a href="#feat">Earnings</a>
                </li>
                <li>
                  <a href="#signup">List your van</a>
                </li>
                <li>
                  <a href="#signup">List your guesthouse</a>
                </li>
                <li>
                  <a href="#faq">Host insurance</a>
                </li>
              </ul>
            </div>
            <div className="ftr-col">
              <h4>Company</h4>
              <ul>
                <li>
                  <a href="#">About us</a>
                </li>
                <li>
                  <a href="#">Sustainability</a>
                </li>
                <li>
                  <a href="#faq">Help center</a>
                </li>
                <li>
                  <a href="#">Contact</a>
                </li>
              </ul>
            </div>
            <div className="ftr-col ftr-cta">
              <h4>Get started</h4>
              <Link className="ftr-btn ghost" to="/login">
                Log in
              </Link>
              <a className="ftr-btn solid" href="#signup">
                <span className="cta-spin">Start hosting</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
              </a>
            </div>
          </div>
          <div className="ftr-bot">
            <span className="ftr-copy">
              © 2026 <b>MyTerraBook ehf.</b>
            </span>
            <div className="ftr-legal">
              <a href="#">Terms &amp; Conditions</a>
              <span className="dot" />
              <a href="#">Privacy</a>
              <span className="dot" />
              <a href="#">Cookies</a>
            </div>
          </div>
        </div>
        <div className="ftr-word" aria-hidden="true">
          <svg viewBox="0 0 1200 120" preserveAspectRatio="xMidYMid meet" role="img" aria-label="MyTerraBook">
            <text
              x="50%"
              y="78%"
              textAnchor="middle"
              fill="currentColor"
              style={{ fontFamily: 'Quicksand, sans-serif', fontWeight: 700, fontSize: 110, letterSpacing: '-0.03em' }}
            >
              MyTerraBook
            </text>
          </svg>
        </div>
      </footer>
    </>
  )
}
