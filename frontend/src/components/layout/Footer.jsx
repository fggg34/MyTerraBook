import { AtSign, Car, Link2, Mail, MapPin, Phone, Rss } from 'lucide-react'
import { Link } from 'react-router-dom'

const footerLinks = {
  Company: [
    { label: 'About Us', to: '/' },
    { label: 'Our Fleet', to: '/cars' },
    { label: 'How It Works', to: '/#how-it-works' },
  ],
  Support: [
    { label: 'Help Center', to: '/' },
    { label: 'Contact Us', to: '/' },
    { label: 'FAQs', to: '/' },
  ],
  Legal: [
    { label: 'Terms of Service', to: '/' },
    { label: 'Privacy Policy', to: '/' },
    { label: 'Rental Agreement', to: '/' },
  ],
}

export default function Footer() {
  return (
    <footer className="bg-brand-950 text-white">
      <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div className="grid gap-10 sm:grid-cols-2 lg:grid-cols-5">
          <div className="lg:col-span-2">
            <Link to="/" className="flex items-center gap-2">
              <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-accent">
                <Car className="h-5 w-5" aria-hidden />
              </div>
              <span className="text-lg font-bold">MyTerraBook</span>
            </Link>
            <p className="mt-4 max-w-sm text-sm leading-relaxed text-slate-400">
              Premium car rentals across Albania. Transparent pricing, flexible pickup locations,
              and a fleet you can trust.
            </p>
            <div className="mt-6 space-y-2 text-sm text-slate-400">
              <p className="flex items-center gap-2">
                <MapPin className="h-4 w-4 shrink-0 text-accent" aria-hidden />
                Tirana Airport &amp; City Center
              </p>
              <p className="flex items-center gap-2">
                <Phone className="h-4 w-4 shrink-0 text-accent" aria-hidden />
                +355 00 000 0000
              </p>
              <p className="flex items-center gap-2">
                <Mail className="h-4 w-4 shrink-0 text-accent" aria-hidden />
                hello@terrabook.com
              </p>
            </div>
          </div>

          {Object.entries(footerLinks).map(([title, links]) => (
            <div key={title}>
              <h4 className="text-sm font-semibold uppercase tracking-wider text-slate-300">
                {title}
              </h4>
              <ul className="mt-4 space-y-2">
                {links.map((link) => (
                  <li key={link.label}>
                    <Link
                      to={link.to}
                      className="text-sm text-slate-400 transition-colors hover:text-white"
                    >
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>

        <div className="mt-12 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-8 sm:flex-row">
          <p className="text-sm text-slate-500">
            © {new Date().getFullYear()} MyTerraBook Rentals. All rights reserved.
          </p>
          <div className="flex gap-4">
            {[Link2, AtSign, Rss].map((Icon, i) => (
              <a
                key={i}
                href="#"
                className="rounded-full bg-white/10 p-2 text-slate-400 transition-colors hover:bg-accent hover:text-white"
                aria-label="Social link"
              >
                <Icon className="h-4 w-4" aria-hidden />
              </a>
            ))}
          </div>
        </div>
      </div>
    </footer>
  )
}
