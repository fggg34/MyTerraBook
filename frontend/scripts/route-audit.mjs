import { chromium } from 'playwright'

const base = process.env.AUDIT_BASE || 'http://127.0.0.1:5174'
const routes = [
  '/',
  '/about',
  '/faq',
  '/contact',
  '/terms',
  '/privacy',
  '/refund-policy',
  '/cookies',
  '/cardholder-terms',
  '/good-to-know',
  '/campsite-map',
  '/become-a-host',
  '/coming-soon',
  '/newsletter/unsubscribe',
  '/login',
  '/register',
  '/forgot-password',
  '/reset-password',
  '/campervans',
  '/cars',
  '/guesthouses',
  '/checkout',
  '/host',
  '/host/cars',
  '/host/guesthouses',
  '/host/bookings',
  '/host/integrations',
  '/host/settings',
  '/dashboard',
  '/dashboard/settings',
]

const viewports = [
  { name: '1440', width: 1440, height: 900 },
  { name: '390', width: 390, height: 844 },
]

const browser = await chromium.launch({ headless: true })
const rows = []

for (const viewport of viewports) {
  const page = await browser.newPage({ viewport: { width: viewport.width, height: viewport.height } })
  for (const route of routes) {
    const errors = []
    const onConsole = (message) => {
      if (message.type() === 'error') errors.push(message.text().slice(0, 180))
    }
    const onPageError = (error) => errors.push(String(error).slice(0, 180))
    const onResponse = (response) => {
      if (response.status() >= 400) errors.push(`${response.status()} ${response.url().slice(0, 140)}`)
    }
    page.on('console', onConsole)
    page.on('pageerror', onPageError)
    page.on('response', onResponse)
    let status = 0
    try {
      const response = await page.goto(base + route, { waitUntil: 'domcontentloaded', timeout: 20000 })
      status = response?.status() || 0
      await page.waitForTimeout(400)
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1)
      if (overflow) errors.push('horizontal scroll')
    } catch (error) {
      errors.push(String(error).slice(0, 180))
    }
    page.off('console', onConsole)
    page.off('pageerror', onPageError)
    page.off('response', onResponse)
    rows.push({ route: `${route} @${viewport.name}`, status, errors: errors.slice(0, 4).join(' | ') })
  }
  await page.close()
}

await browser.close()
console.log(['route', 'status', 'errors'].join('\t'))
for (const row of rows) {
  console.log([row.route, row.status, row.errors].join('\t'))
}
