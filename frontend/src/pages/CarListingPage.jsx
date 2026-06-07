import { SlidersHorizontal } from 'lucide-react'
import { useEffect, useMemo, useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { api } from '../api'
import CarCard from '../components/cars/CarCard'
import SearchBar from '../components/cars/SearchBar'
import EmptyState from '../components/ui/EmptyState'
import { CarGridSkeleton } from '../components/ui/Skeleton'
import { toApiDateTime } from '../utils/format'

export default function CarListingPage() {
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const query = Object.fromEntries(searchParams.entries())
  const searchQuery = searchParams.toString()

  const [cars, setCars] = useState([])
  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(true)
  const [filtersOpen, setFiltersOpen] = useState(false)
  const [filters, setFilters] = useState({
    category_id: '',
    transmission: '',
    fuel_type: '',
    maxPrice: 200,
    sort: 'price_asc',
  })

  useEffect(() => {
    setLoading(true)
    const params = {}
    if (query.pickup_location_id) params.pickup_location_id = query.pickup_location_id
    if (query.dropoff_location_id) params.dropoff_location_id = query.dropoff_location_id
    if (query.pickup_at) params.pickup_at = toApiDateTime(query.pickup_at)
    if (query.dropoff_at) params.dropoff_at = toApiDateTime(query.dropoff_at)

    Promise.all([
      api.get('/cars', { params }),
      api.get('/categories'),
    ])
      .then(([carsRes, catRes]) => {
        setCars(carsRes.data.data || [])
        setCategories(catRes.data.data || [])
      })
      .finally(() => setLoading(false))
  }, [
    query.pickup_location_id,
    query.dropoff_location_id,
    query.pickup_at,
    query.dropoff_at,
  ])

  const hasSearchDates = Boolean(query.pickup_at && query.dropoff_at)
  const carsWithSpecialDiscount = useMemo(
    () => cars.filter((car) => car.search_pricing?.has_special_discount),
    [cars],
  )

  const categoryMap = useMemo(() => {
    const m = {}
    categories.forEach((c) => {
      m[c.id] = c.name
    })
    return m
  }, [categories])

  const transmissions = useMemo(
    () => [...new Set(cars.map((c) => c.transmission).filter(Boolean))],
    [cars],
  )
  const fuelTypes = useMemo(
    () => [...new Set(cars.map((c) => c.fuel_type).filter(Boolean))],
    [cars],
  )

  const filteredCars = useMemo(() => {
    let result = [...cars]

    if (filters.category_id) {
      result = result.filter((c) => String(c.category_id) === filters.category_id)
    }
    if (filters.transmission) {
      result = result.filter((c) => c.transmission === filters.transmission)
    }
    if (filters.fuel_type) {
      result = result.filter((c) => c.fuel_type === filters.fuel_type)
    }
    result = result.filter((c) => {
      const price = parseFloat(c.base_daily_price) || 0
      return price <= filters.maxPrice
    })

    result.sort((a, b) => {
      const pa = parseFloat(a.base_daily_price) || 0
      const pb = parseFloat(b.base_daily_price) || 0
      if (filters.sort === 'price_desc') return pb - pa
      if (filters.sort === 'name') return a.name.localeCompare(b.name)
      return pa - pb
    })

    return result
  }, [cars, filters])

  return (
    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div className="mb-8">
        <h1 className="section-title">Available Cars</h1>
        <p className="section-subtitle">
          {filteredCars.length} vehicle{filteredCars.length !== 1 ? 's' : ''} found
          {query.pickup_location_id ? ' for your selected route' : ''}
        </p>
      </div>

      <div className="mb-8">
        <SearchBar initialValues={query} variant="compact" onSearch={(form) => {
          const params = new URLSearchParams()
          Object.entries(form).forEach(([k, v]) => {
            if (v) params.set(k, v)
          })
          navigate(`/cars?${params.toString()}`)
        }} />
        {hasSearchDates && (
          <div className="mt-4 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm shadow-card">
            {carsWithSpecialDiscount.length > 0 ? (
              <p className="text-emerald-700">
                Special price discount active for {carsWithSpecialDiscount.length} vehicle
                {carsWithSpecialDiscount.length !== 1 ? 's' : ''} on your selected dates.
              </p>
            ) : (
              <p className="text-slate-600">
                No special price discount applies to your selected dates.
              </p>
            )}
          </div>
        )}
      </div>

      <div className="flex flex-col gap-8 lg:flex-row">
        <aside className={`lg:w-64 lg:shrink-0 ${filtersOpen ? 'block' : 'hidden lg:block'}`}>
          <div className="sticky top-24 rounded-xl border border-slate-200 bg-white p-5 shadow-card">
            <h2 className="flex items-center gap-2 font-semibold text-brand-950">
              <SlidersHorizontal className="h-4 w-4 text-accent" aria-hidden />
              Filters
            </h2>

            <div className="mt-5 space-y-5">
              <div>
                <label className="label-field">Sub category</label>
                <select
                  className="input-field"
                  value={filters.category_id}
                  onChange={(e) => setFilters({ ...filters, category_id: e.target.value })}
                >
                  <option value="">All types</option>
                  {categories.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="label-field">Transmission</label>
                <select
                  className="input-field"
                  value={filters.transmission}
                  onChange={(e) => setFilters({ ...filters, transmission: e.target.value })}
                >
                  <option value="">Any</option>
                  {transmissions.map((t) => (
                    <option key={t} value={t}>
                      {t}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="label-field">Fuel type</label>
                <select
                  className="input-field"
                  value={filters.fuel_type}
                  onChange={(e) => setFilters({ ...filters, fuel_type: e.target.value })}
                >
                  <option value="">Any</option>
                  {fuelTypes.map((f) => (
                    <option key={f} value={f}>
                      {f}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="label-field">Max price: €{filters.maxPrice}/day</label>
                <input
                  type="range"
                  min={20}
                  max={200}
                  step={5}
                  value={filters.maxPrice}
                  onChange={(e) => setFilters({ ...filters, maxPrice: Number(e.target.value) })}
                  className="w-full accent-accent"
                />
              </div>

              <div>
                <label className="label-field">Sort by</label>
                <select
                  className="input-field"
                  value={filters.sort}
                  onChange={(e) => setFilters({ ...filters, sort: e.target.value })}
                >
                  <option value="price_asc">Price: low to high</option>
                  <option value="price_desc">Price: high to low</option>
                  <option value="name">Name</option>
                </select>
              </div>
            </div>
          </div>
        </aside>

        <div className="flex-1">
          <button
            type="button"
            className="btn-secondary mb-4 lg:hidden"
            onClick={() => setFiltersOpen(!filtersOpen)}
          >
            <SlidersHorizontal className="h-4 w-4" aria-hidden />
            {filtersOpen ? 'Hide filters' : 'Show filters'}
          </button>

          {loading ? (
            <CarGridSkeleton count={6} />
          ) : filteredCars.length === 0 ? (
            <EmptyState
              title="No cars match your filters"
              description="Try adjusting your filters or search dates."
            />
          ) : (
            <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
              {filteredCars.map((car) => (
                <CarCard
                  key={car.id}
                  car={car}
                  searchQuery={searchQuery}
                  categoryName={categoryMap[car.category_id]}
                />
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
