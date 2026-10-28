import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import Container from '../site/Container'
import StrokeIcon from './StrokeIcon'
import '../../styles/frames.css'

const SITES = [
  { id: 'reykjavik', name: 'Reykjavík Campsite', region: 'Capital', place: 'Capital region', season: 'Open all year', winter: true, facilities: ['bath', 'zap', 'wifi', 'house'], top: '18%', left: '22%', featured: true },
  { id: 'thingvellir', name: 'Þingvellir – Leirar', region: 'South', place: 'South · Golden Circle', season: 'Jun – Sep', winter: false, facilities: ['bath', 'zap'], top: '32%', left: '36%' },
  { id: 'vik', name: 'Vík Campsite', region: 'South', place: 'South coast', season: 'Apr – Oct', winter: false, facilities: ['bath', 'zap', 'house', 'wifi'], top: '58%', left: '42%' },
  { id: 'skaftafell', name: 'Skaftafell', region: 'Southeast', place: 'Southeast', season: 'Open all year', winter: true, facilities: ['bath', 'zap', 'house'], top: '62%', left: '62%' },
  { id: 'egilsstadir', name: 'Egilsstaðir', region: 'East', place: 'East', season: 'May – Sep', winter: false, facilities: ['bath', 'wifi', 'sparkles'], top: '34%', left: '78%' },
  { id: 'asbyrgi', name: 'Ásbyrgi', region: 'North', place: 'North', season: 'Jun – Sep', winter: false, facilities: ['bath', 'zap'], top: '16%', left: '58%' },
  { id: 'husafell', name: 'Húsafell', region: 'West', place: 'West', season: 'May – Oct', winter: false, facilities: ['bath', 'zap', 'house', 'wifi'], top: '36%', left: '28%' },
]

const FACILITY_FILTERS = [
  { id: 'bath', label: 'Showers', icon: 'bath' },
  { id: 'zap', label: 'Electricity', icon: 'zap' },
  { id: 'wifi', label: 'Wi-Fi', icon: 'wifi' },
  { id: 'house', label: 'Kitchen', icon: 'house' },
  { id: 'sparkles', label: 'Laundry', icon: 'sparkles' },
]

const REGIONS = ['All regions', 'Capital', 'South', 'Southeast', 'East', 'North', 'West']

export default function CampsiteFigma() {
  const [query, setQuery] = useState('')
  const [region, setRegion] = useState('All regions')
  const [winter, setWinter] = useState(false)
  const [facilities, setFacilities] = useState([])
  const [active, setActive] = useState('reykjavik')
  const [saved, setSaved] = useState([])

  const list = useMemo(() => SITES.filter((site) => {
    if (winter && !site.winter) return false
    if (region !== 'All regions' && site.region !== region) return false
    if (facilities.some((item) => !site.facilities.includes(item))) return false
    if (query && !`${site.name} ${site.place}`.toLowerCase().includes(query.toLowerCase())) return false
    return true
  }), [query, region, winter, facilities])

  const current = SITES.find((site) => site.id === active) || list[0]
  const toggleFacility = (id) => setFacilities((currentFacilities) => (
    currentFacilities.includes(id) ? currentFacilities.filter((item) => item !== id) : [...currentFacilities, id]
  ))
  const clear = () => {
    setQuery('')
    setRegion('All regions')
    setWinter(false)
    setFacilities([])
  }

  return (
    <div className="tb-frame">
      <section className="tb-camp-hero">
        <Container>
          <nav className="tb-crumbs">
            <Link to="/">Home</Link>
            <StrokeIcon name="chevron" size={12} />
            <strong>Campsite map</strong>
          </nav>
          <div className="tb-camp-hero-row">
            <div>
              <p className="tb-kicker">CAMPSITE MAP</p>
              <h1>Find a place to park for the <em>night</em>.</h1>
              <p>Campsites across Iceland on one map — with season dates and facilities, so you always know where you can stop with your campervan.</p>
            </div>
            <Link className="tb-btn" to="/campervans">Find a campervan <StrokeIcon name="arrow" size={16} /></Link>
          </div>
        </Container>
      </section>

      <section>
        <Container>
          <div className="tb-filters">
            <label className="search"><StrokeIcon name="search" size={16} /><input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Search campsites" aria-label="Search campsites" /></label>
            <label><StrokeIcon name="map" size={14} />
              <select value={region} onChange={(event) => setRegion(event.target.value)} aria-label="Region">
                {REGIONS.map((item) => <option key={item} value={item}>{item === 'All regions' ? 'Region: All regions' : item}</option>)}
              </select>
            </label>
            <button type="button" className={`winter ${winter ? 'is-on' : ''}`} onClick={() => setWinter((value) => !value)}>
              <StrokeIcon name="snowflake" size={16} /> Open in winter
              <span className={`tb-toggle ${winter ? 'on' : ''}`}><i /></span>
            </button>
            {FACILITY_FILTERS.map((item) => (
              <button key={item.id} type="button" className={facilities.includes(item.id) ? 'is-on' : ''} onClick={() => toggleFacility(item.id)}>
                <StrokeIcon name={item.icon} size={14} /> {item.label}
              </button>
            ))}
            <button type="button" className="clear" onClick={clear}>Clear all</button>
          </div>

          <div className="tb-camp-grid">
            <div className="tb-camp-list">
              <header>
                <h2>Campsites</h2>
                <span>Sort: Nearest</span>
              </header>
              {list.map((site) => (
                <button key={site.id} type="button" className={`tb-site ${active === site.id ? 'is-on' : ''}`} onClick={() => setActive(site.id)}>
                  <span className="mark"><StrokeIcon name="tent" size={20} /></span>
                  <span>
                    <strong>{site.name} <StrokeIcon name="heart" size={16} /></strong>
                    <p><StrokeIcon name="pin" size={13} /> {site.place}</p>
                    <footer>
                      <em>{site.winter ? <StrokeIcon name="snowflake" size={12} /> : <StrokeIcon name="sun" size={12} />} {site.season}</em>
                      {site.facilities.map((facility) => <b key={facility}><StrokeIcon name={facility} size={13} /></b>)}
                    </footer>
                  </span>
                </button>
              ))}
              {!list.length && <p>No campsites match those filters.</p>}
            </div>
            <div className="tb-camp-map">
              <iframe title="Campsites in Iceland" src="https://maps.google.com/maps?q=Iceland&z=6&output=embed" loading="lazy" />
              {list.map((site) => (
                <button key={site.id} type="button" className={`tb-pin ${active === site.id ? 'is-on' : ''}`} style={{ top: site.top, left: site.left }} onClick={() => setActive(site.id)} aria-label={site.name}>
                  <StrokeIcon name="tent" size={site.id === active ? 20 : 16} />
                </button>
              ))}
              {current && (
                <div className="tb-popup" style={{ top: `calc(${current.top} + 48px)`, left: current.left }}>
                  <strong>{current.name}</strong>
                  <p>{current.place} · {current.season}</p>
                  <div>
                    <button type="button" className="tb-btn sm" onClick={() => setSaved((items) => items.includes(current.id) ? items : [...items, current.id])}>{saved.includes(current.id) ? 'Saved' : 'Save'}</button>
                    <a className="tb-btn ghost sm" href={`https://maps.google.com/?q=${encodeURIComponent(current.name + ' Iceland')}`} target="_blank" rel="noreferrer">Directions</a>
                  </div>
                </div>
              )}
              <div className="tb-zoom">
                <button type="button" aria-label="Zoom in"><StrokeIcon name="plus" size={16} /></button>
                <button type="button" aria-label="Zoom out"><StrokeIcon name="minus" size={16} /></button>
              </div>
            </div>
          </div>

          <div className="tb-note">
            <StrokeIcon name="info" size={18} />
            <p>Campsite info is a planning guide — opening dates and facilities can change, so always check with the campsite before you drive. Wild camping in campervans is not allowed in Iceland; please use registered campsites.</p>
          </div>
        </Container>
      </section>

      <section className="tb-tips">
        <Container>
          <div className="tb-tips-head">
            <div>
              <p className="tb-kicker">GOOD TO KNOW</p>
              <h2>Camping in Iceland, <em>made easy</em>.</h2>
            </div>
            <Link className="tb-btn ghost sm" to="/good-to-know">All guides</Link>
          </div>
          <div className="tb-tip-grid">
            <article>
              <i><StrokeIcon name="tent" size={22} /></i>
              <h3>Campsites only</h3>
              <p>Overnight parking outside campsites is not allowed for campervans — plan each night’s stop ahead.</p>
            </article>
            <article>
              <i><StrokeIcon name="snowflake" size={22} /></i>
              <h3>Winter camping</h3>
              <p>Only a handful of campsites stay open in winter. Filter by “Open in winter” and pick a winter-ready van.</p>
            </article>
            <article>
              <i><StrokeIcon name="zap" size={22} /></i>
              <h3>Power & showers</h3>
              <p>Most campsites charge per person; electricity and showers are often paid extras on site.</p>
            </article>
          </div>
        </Container>
      </section>
    </div>
  )
}
