import { useNavigate } from 'react-router-dom'
import SearchBar from '../components/cars/SearchBar'
import FeaturedCars from '../components/home/FeaturedCars'
import HowItWorks from '../components/home/HowItWorks'
import Testimonials from '../components/home/Testimonials'
import WhyChooseUs from '../components/home/WhyChooseUs'

export default function HomeSearchPage() {
  const navigate = useNavigate()

  const handleSearch = (form) => {
    const params = new URLSearchParams()
    Object.entries(form).forEach(([k, v]) => {
      if (v) params.set(k, v)
    })
    navigate(`/cars?${params.toString()}`)
  }

  return (
    <>
      <section className="relative min-h-[640px] bg-brand-950 pb-12">
        <div className="absolute inset-0 overflow-hidden" aria-hidden>
          <div
            className="absolute inset-0 bg-cover bg-center opacity-40"
            style={{
              backgroundImage:
                'url(https://images.unsplash.com/photo-1449965408869-eaa3f725e40c?w=1920&q=80)',
            }}
          />
          <div className="absolute inset-0 bg-gradient-to-r from-brand-950/95 via-brand-950/80 to-brand-950/60" />
        </div>

        <div className="relative z-10 mx-auto max-w-7xl overflow-visible px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
          <div className="max-w-2xl">
            <p className="text-sm font-semibold uppercase tracking-widest text-accent">
              Premium Car Rentals
            </p>
            <h1 className="mt-3 text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
              Find Your Perfect Ride Today
            </h1>
            <p className="mt-4 text-lg text-slate-300">
              Search from our fleet of economy, SUV, and luxury vehicles. Pick up at the airport
              or city center — your journey starts here.
            </p>
          </div>

          <div className="relative z-20 mt-10 max-w-5xl overflow-visible">
            <SearchBar onSearch={handleSearch} variant="hero" />
          </div>
        </div>
      </section>

      <WhyChooseUs />
      <FeaturedCars />
      <HowItWorks />
      <Testimonials />
    </>
  )
}
