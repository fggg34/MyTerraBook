import { Award, Car, Headphones, ShieldCheck } from 'lucide-react'

const features = [
  {
    icon: ShieldCheck,
    title: 'Free Cancellation',
    description: 'Cancel up to 24 hours before pick-up at no extra charge.',
  },
  {
    icon: Headphones,
    title: '24/7 Support',
    description: 'Our team is always available to help with your rental.',
  },
  {
    icon: Award,
    title: 'Best Price Guarantee',
    description: 'Competitive daily rates with no hidden fees at checkout.',
  },
  {
    icon: Car,
    title: 'Wide Selection',
    description: 'Economy to luxury — find the perfect car for every trip.',
  },
]

export default function WhyChooseUs() {
  return (
    <section className="bg-white py-16 sm:py-20">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="text-center">
          <h2 className="section-title">Why Choose Us</h2>
          <p className="section-subtitle mx-auto">
            Everything you need for a smooth, worry-free rental experience.
          </p>
        </div>
        <div className="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
          {features.map(({ icon: Icon, title, description }) => (
            <div
              key={title}
              className="group rounded-xl border border-slate-100 bg-slate-50 p-6 text-center transition-all hover:border-accent/30 hover:bg-white hover:shadow-card"
            >
              <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-accent/10 text-accent transition-colors group-hover:bg-accent group-hover:text-white">
                <Icon className="h-7 w-7" aria-hidden />
              </div>
              <h3 className="mt-4 font-semibold text-brand-950">{title}</h3>
              <p className="mt-2 text-sm leading-relaxed text-slate-600">{description}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
