import { Search, CreditCard, KeyRound } from 'lucide-react'

const steps = [
  {
    icon: Search,
    step: '01',
    title: 'Search',
    description: 'Enter your pick-up location and dates to browse available vehicles.',
  },
  {
    icon: CreditCard,
    step: '02',
    title: 'Book',
    description: 'Choose your car, review pricing, and confirm your reservation online.',
  },
  {
    icon: KeyRound,
    step: '03',
    title: 'Drive',
    description: 'Pick up your keys and hit the road. Return at your chosen location.',
  },
]

export default function HowItWorks() {
  return (
    <section id="how-it-works" className="bg-brand-950 py-16 text-white sm:py-20">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="text-center">
          <h2 className="text-2xl font-bold tracking-tight sm:text-3xl">How It Works</h2>
          <p className="mx-auto mt-2 max-w-xl text-slate-400">
            Rent a car in three simple steps — fast, transparent, and hassle-free.
          </p>
        </div>
        <div className="mt-12 grid gap-8 md:grid-cols-3">
          {steps.map(({ icon: Icon, step, title, description }) => (
            <div key={title} className="relative rounded-xl border border-white/10 bg-white/5 p-8 text-center">
              <span className="text-4xl font-black text-accent/40">{step}</span>
              <div className="mx-auto mt-4 flex h-14 w-14 items-center justify-center rounded-full bg-accent text-white">
                <Icon className="h-7 w-7" aria-hidden />
              </div>
              <h3 className="mt-4 text-xl font-bold">{title}</h3>
              <p className="mt-2 text-sm leading-relaxed text-slate-400">{description}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
