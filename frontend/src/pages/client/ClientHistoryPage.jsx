import { useCallback, useEffect, useMemo, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { Car, Download, Home, Tent } from 'lucide-react'
import ClientHistoryCard from '../../components/client/ClientHistoryCard'
import { PageLoader } from '../../components/ui/LoadingSpinner'
import { api } from '../../api'
import { getMeHistory, getMeHistoryExportUrl } from '../../api/me'
import { usePageContent } from '../../context/SiteContentContext'
import { groupHistoryItems } from '../../utils/clientHistory'

const TYPE_FILTERS = [
  { id: 'all', label: 'All trips' },
  { id: 'car', label: 'Cars' },
  { id: 'campervan', label: 'Campervans' },
  { id: 'guesthouse', label: 'Stays' },
]

const PERIOD_FILTERS = [
  { id: 'all', label: 'All time' },
  { id: 'upcoming', label: 'Upcoming' },
  { id: 'past', label: 'Past' },
]

export default function ClientHistoryPage() {
  const { page: copy } = usePageContent('user-dashboard')
  const [searchParams, setSearchParams] = useSearchParams()
  const [items, setItems] = useState([])
  const [summary, setSummary] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  const typeFilter = searchParams.get('type') || 'all'
  const periodFilter = searchParams.get('period') || 'all'

  const load = useCallback(() => {
    setLoading(true)
    const params = {}
    if (typeFilter !== 'all') params.type = typeFilter
    if (periodFilter !== 'all') params.period = periodFilter

    getMeHistory(params)
      .then((res) => {
        setItems(res.data.data || [])
        setSummary(res.data.meta || null)
      })
      .catch((err) => setError(err.response?.data?.message || 'Could not load trip history'))
      .finally(() => setLoading(false))
  }, [typeFilter, periodFilter])

  useEffect(() => {
    load()
  }, [load])

  const setFilter = (key, value) => {
    const next = new URLSearchParams(searchParams)
    if (value === 'all') next.delete(key)
    else next.set(key, value)
    setSearchParams(next, { replace: true })
  }

  const grouped = useMemo(() => {
    if (periodFilter !== 'all') return { all: items }
    return groupHistoryItems(items)
  }, [items, periodFilter])

  const exportCsv = async () => {
    try {
      const res = await api.get(getMeHistoryExportUrl(), { responseType: 'blob' })
      const blobUrl = URL.createObjectURL(res.data)
      const link = document.createElement('a')
      link.href = blobUrl
      link.download = `my-terrabook-trips-${new Date().toISOString().slice(0, 10)}.csv`
      link.click()
      URL.revokeObjectURL(blobUrl)
    } catch {
      setError('Could not export trip history')
    }
  }

  if (loading) return <PageLoader message="Loading your trips…" />

  if (error) {
    return (
      <div className="client-empty">
        <p>{error}</p>
      </div>
    )
  }

  const hasItems = items.length > 0
  const sections = periodFilter === 'all'
    ? [
        { key: 'upcoming', title: 'Upcoming trips', items: grouped.upcoming },
        { key: 'past', title: 'Past trips', items: grouped.past },
      ].filter((section) => section.items.length > 0)
    : [{ key: 'all', title: null, items: grouped.all }]

  return (
    <div className="client-history">
      <div className="client-page-head client-page-head--split">
        <div>
          <h2>{copy.historyTitle ?? 'Trip history'}</h2>
          <p>{copy.historySubtitle ?? 'Cars, campervans and guesthouses you have booked with MyTerraBook.'}</p>
        </div>
        {hasItems && (
          <button type="button" className="client-btn secondary client-export-btn" onClick={exportCsv}>
            <Download size={15} />
            {copy.exportLabel ?? 'Export CSV'}
          </button>
        )}
      </div>

      {summary && (
        <div className="client-stats" aria-label="Trip summary">
          <div className="client-stat client-stat--total">
            <span className="client-stat__value">{summary.total}</span>
            <span className="client-stat__label">Total trips</span>
          </div>
          <div className="client-stat client-stat--upcoming">
            <span className="client-stat__value">{summary.upcoming}</span>
            <span className="client-stat__label">Upcoming</span>
          </div>
          <div className="client-stat client-stat--car">
            <span className="client-stat__value">{summary.car}</span>
            <span className="client-stat__label">Cars</span>
          </div>
          <div className="client-stat client-stat--campervan">
            <span className="client-stat__value">{summary.campervan}</span>
            <span className="client-stat__label">Campervans</span>
          </div>
          <div className="client-stat client-stat--guesthouse">
            <span className="client-stat__value">{summary.guesthouse}</span>
            <span className="client-stat__label">Stays</span>
          </div>
        </div>
      )}

      <div className="client-filters">
        <div className="client-filter-group" role="group" aria-label="Filter by rental type">
          {TYPE_FILTERS.map((filter) => (
            <button
              key={filter.id}
              type="button"
              className={`client-filter-pill${typeFilter === filter.id ? ' is-active' : ''}`}
              onClick={() => setFilter('type', filter.id)}
            >
              {filter.label}
            </button>
          ))}
        </div>
        <div className="client-filter-group" role="group" aria-label="Filter by time period">
          {PERIOD_FILTERS.map((filter) => (
            <button
              key={filter.id}
              type="button"
              className={`client-filter-pill client-filter-pill--muted${periodFilter === filter.id ? ' is-active' : ''}`}
              onClick={() => setFilter('period', filter.id)}
            >
              {filter.label}
            </button>
          ))}
        </div>
      </div>

      {!hasItems ? (
        <div className="client-empty">
          <div className="client-empty-icon">
            <Car size={28} />
          </div>
          <h3>{copy.emptyHistory ?? 'No trips yet'}</h3>
          <p>{copy.emptyHistoryText ?? 'When you book a car, campervan or guesthouse, it will appear here.'}</p>
          <div className="client-empty-actions">
            <Link to="/cars" className="client-btn primary">
              <Car size={15} />
              Browse cars
            </Link>
            <Link to="/campervans" className="client-btn secondary">
              <Tent size={15} />
              Browse campervans
            </Link>
            <Link to="/guesthouses" className="client-btn secondary">
              <Home size={15} />
              Browse stays
            </Link>
          </div>
        </div>
      ) : (
        <div className="client-history-sections">
          {sections.map((section) => (
            <section key={section.key} className="client-history-section">
              {section.title && <h3 className="client-history-section__title">{section.title}</h3>}
              <div className="client-history-grid">
                {section.items.map((item) => (
                  <ClientHistoryCard
                    key={`${item.kind}-${item.id}`}
                    item={item}
                    onCancelled={load}
                    addToCalendarLabel={copy.addToCalendarLabel ?? 'Add to calendar'}
                    downloadPdfLabel={copy.downloadPdfLabel ?? 'Download PDF'}
                    viewListingLabel={copy.viewListingLabel ?? 'View listing'}
                    cancelBookingLabel={copy.cancelBookingLabel ?? 'Cancel booking'}
                  />
                ))}
              </div>
            </section>
          ))}
        </div>
      )}
    </div>
  )
}
