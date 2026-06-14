import assert from 'node:assert/strict'
import {
  buildCanonical,
  resolveAutoTitle,
  resolveSeo,
  truncateDescription,
} from './resolveSeo.js'

const globalSeo = {
  siteName: 'MyTerraBook',
  titleSuffix: ' | MyTerraBook',
  defaultDescription: 'Default site description for Iceland travel bookings.',
  defaultOgImage: '/images/homepage/hero.jpg',
}

const listing = resolveSeo({
  globalSeo,
  pageSeo: {},
  source: {
    name: 'Ring Road Camper',
    description: '<p>Perfect for two travellers exploring Iceland.</p>',
    main_image_path: '/images/homepage/cardcamper.jpg',
    listingType: 'campervan',
  },
  pathname: '/campervans/ring-road-camper',
})

assert.match(listing.title, /Ring Road Camper, Campervan in Iceland \| MyTerraBook/)
assert.match(listing.description, /Perfect for two travellers/)
assert.equal(listing.robots, 'index')
assert.equal(listing.canonical, buildCanonical('/campervans/ring-road-camper'))

const overridden = resolveSeo({
  globalSeo,
  pageSeo: { title: 'Custom About Title' },
  source: { hero: { title: 'About MyTerraBook', lead: 'Local team in Reykjavík.' } },
  pathname: '/about',
})

assert.equal(overridden.title, 'Custom About Title | MyTerraBook')
assert.equal(overridden.description, truncateDescription('Local team in Reykjavík.'))

const listingOverride = resolveSeo({
  globalSeo,
  pageSeo: {},
  source: {
    meta_title: 'Book this camper now',
    meta_description: 'Override description for search.',
    name: 'Ignored when override set',
    listingType: 'campervan',
  },
  pathname: '/campervans/demo',
})

assert.equal(listingOverride.title, 'Book this camper now | MyTerraBook')
assert.equal(listingOverride.description, 'Override description for search.')

console.log('resolveSeo tests passed')
