import rawDefaults from './defaultSiteContentData.json'
import { defaultHomepageData } from './defaultHomepageData'
import { mergeHomepageData } from '../utils/mergeHomepageData'
import { mergePageContent } from '../utils/mergePageContent'

/** Static fallbacks when /api/site-content is unavailable. */
export const defaultSiteContentData = rawDefaults

export function getDefaultPageContent(pageKey) {
  return defaultSiteContentData[pageKey] ?? {}
}

/**
 * Build legacy homepage-shaped siteData from global + home page content.
 */
export function buildSiteDataFromContent(pages = {}, { useDefaults = false } = {}) {
  const global = pages.global ?? (useDefaults ? defaultSiteContentData.global : {}) ?? {}
  const home = pages.home ?? (useDefaults ? defaultSiteContentData.home : {}) ?? {}

  const apiShape = {
    topbar: global.topbar,
    header: global.header,
    hero: home.hero,
    trustItems: home.trustItems,
    rentSection: home.rentSection,
    whySection: home.whySection,
    picksSection: home.picksSection,
    howSection: home.howSection,
    staySection: home.staySection,
    blogSection: home.blogSection,
    hostCtaSection: home.hostCtaSection,
    reviewsSection: home.reviewsSection,
    faqSection: global.faqSection,
    newsSection: global.newsSection,
    footer: global.footer,
    guestHousesHighlight: home.guestHousesHighlight,
  }

  return mergeHomepageData(apiShape, { useImageFallbacks: useDefaults })
}

export function mergeAllSiteContent(apiPages = {}) {
  const merged = {}

  for (const [key, defaults] of Object.entries(defaultSiteContentData)) {
    merged[key] = mergePageContent(defaults, apiPages[key])
  }

  for (const [key, value] of Object.entries(apiPages)) {
    if (!merged[key]) {
      merged[key] = value
    }
  }

  return merged
}

export { defaultHomepageData }
