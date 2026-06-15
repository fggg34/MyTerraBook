import { ArrowRight } from 'lucide-react'
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../../api'
import CarCard from '../cars/CarCard'

export default function FeaturedCars() {
  const [cars, setCars] = useState([])
  const [categories, setCategories] = useState({})
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([api.get('/cars'), api.get('/categories')])
      .then(([carsRes, catRes]) => {
        const allCars = carsRes.data.data || []
        setCars(allCars.slice(0, 6))
        const catMap = {}
        ;(catRes.data.data || []).forEach((c) => {
          catMap[c.id] = c.name
        })
        setCategories(catMap)
      })
      .finally(() => setLoading(false))
  }, [])

  return (
    <section className="py-16 sm:py-20">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
          <div>
            <h2 className="section-title">Featured Cars</h2>
            <p className="section-subtitle">Hand-picked vehicles ready for your next adventure.</p>
          </div>
          <Link to="/cars" className="btn-secondary shrink-0">
            View all cars
            <ArrowRight className="h-4 w-4" aria-hidden />
          </Link>
        </div>

        <div className="mt-10">
          {!loading && (
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {cars.map((car) => (
                <CarCard
                  key={car.id}
                  car={car}
                  categoryName={categories[car.category_id]}
                />
              ))}
            </div>
          )}
        </div>
      </div>
    </section>
  )
}
