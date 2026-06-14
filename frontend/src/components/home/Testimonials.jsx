import { Star } from 'lucide-react'

const testimonials = [
  {
    name: 'Arben K.',
    location: 'Tirana',
    text: 'Smooth booking process and the car was spotless at pick-up. Will definitely use MyTerraBook again for my next trip to the coast.',
    rating: 5,
  },
  {
    name: 'Elena M.',
    location: 'Durres',
    text: 'Great prices compared to other rental companies. The GPS add-on was worth every cent navigating Albania\'s roads.',
    rating: 5,
  },
  {
    name: 'Marco R.',
    location: 'Italy',
    text: 'Picked up at Tirana Airport and dropped off in Vlorë, one-way rental made our vacation so much easier. Highly recommended!',
    rating: 5,
  },
]

export default function Testimonials() {
  return (
    <section className="bg-slate-50 py-16 sm:py-20">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="text-center">
          <h2 className="section-title">What Our Customers Say</h2>
          <p className="section-subtitle mx-auto">
            Trusted by travelers across Albania and beyond.
          </p>
        </div>
        <div className="mt-12 grid gap-6 md:grid-cols-3">
          {testimonials.map((t) => (
            <blockquote
              key={t.name}
              className="rounded-xl bg-white p-6 shadow-card"
            >
              <div className="flex gap-0.5 text-amber-400">
                {Array.from({ length: t.rating }).map((_, i) => (
                  <Star key={i} className="h-4 w-4 fill-current" aria-hidden />
                ))}
              </div>
              <p className="mt-4 text-sm leading-relaxed text-slate-600">&ldquo;{t.text}&rdquo;</p>
              <footer className="mt-4 border-t border-slate-100 pt-4">
                <cite className="not-italic font-semibold text-brand-950">{t.name}</cite>
                <p className="text-xs text-slate-500">{t.location}</p>
              </footer>
            </blockquote>
          ))}
        </div>
      </div>
    </section>
  )
}
