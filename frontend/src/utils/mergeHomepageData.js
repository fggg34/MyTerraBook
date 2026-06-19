import { resolveCmsImage, resolveStorageUrl } from '../api'
import { defaultHomepageData } from '../data/defaultHomepageData'
import { normalizeHomepageHref, normalizeLinkList } from './normalizeHomepageHref'

function mergeCards(cards, fallbackCards) {
  if (!Array.isArray(cards) || !cards.length) return fallbackCards
  return cards.map((card, index) => ({
    ...fallbackCards[index],
    ...card,
    image: resolveCmsImage(card.image, fallbackCards[index]?.image),
    href: normalizeHomepageHref(card.href ?? fallbackCards[index]?.href),
  }))
}

function fixIcelandicPlaceNames(text) {
  if (typeof text !== 'string') return text
  return text.replace(/\bReykjavik\b/g, 'Reykjavík').replace(/\bKeflavik\b/g, 'Keflavík')
}

function mergeSteps(steps, fallbackSteps) {
  if (!Array.isArray(steps) || !steps.length) return fallbackSteps
  return steps.map((step, index) => {
    const fallback = fallbackSteps[index] || {}
    const mergedTags = Array.isArray(step.tags)
      ? [...new Set(step.tags.filter(Boolean))]
      : fallback.tags
    return {
      ...fallback,
      ...step,
      title: fixIcelandicPlaceNames(step.title ?? fallback.title),
      description: fixIcelandicPlaceNames(step.description ?? fallback.description),
      tags: mergedTags?.length ? mergedTags : fallback.tags,
      image: resolveCmsImage(step.image, fallback.image),
    }
  })
}

function mergeFeatures(features, fallbackFeatures) {
  if (!Array.isArray(fallbackFeatures) || !fallbackFeatures.length) {
    return Array.isArray(features) ? features : []
  }
  if (!Array.isArray(features) || !features.length) return fallbackFeatures
  return fallbackFeatures.map((feature, index) => ({
    ...feature,
    ...(features[index] || {}),
  }))
}

function mergeTrustItems(items, fallbackItems) {
  if (!Array.isArray(items) || !items.length) return fallbackItems
  return items.map((item, index) => ({
    ...fallbackItems[index],
    ...item,
    iconImage: item.iconImage ? resolveStorageUrl(item.iconImage) : fallbackItems[index]?.iconImage,
  }))
}

function normalizePicksTabs(tabs = []) {
  return tabs.map((tab) => {
    let allHref = tab.allHref
    if (!allHref || allHref === '#') {
      if (tab.id === 'camper') allHref = '/campervans'
      else if (tab.id === 'car') allHref = '/cars'
      else if (tab.id === 'guesthouse') allHref = '/guesthouses'
    }
    return {
      ...tab,
      allHref: normalizeHomepageHref(allHref),
    }
  })
}

function normalizeFooterColumns(columns = []) {
  return columns.map((column) => ({
    ...column,
    links: normalizeLinkList(column.links),
  }))
}

function mapFeaturedBlogPost(post) {
  return {
    slug: post.slug,
    featured: post.is_featured,
    title: post.title,
    description: post.excerpt,
    meta: post.read_time,
    kicker: post.kicker,
    image: resolveCmsImage(post.featured_image, null),
    imageAlt: post.image_alt,
    aurora: post.aurora,
  }
}

export function mergeHomepageData(apiData = {}) {
  const defaults = defaultHomepageData

  const rawMobileBg = apiData.hero?.mobileBackgroundImage
  const hero = {
    ...defaults.hero,
    ...apiData.hero,
    backgroundImage: resolveCmsImage(apiData.hero?.backgroundImage, defaults.hero.backgroundImage),
    mobileBackgroundImage:
      rawMobileBg === ''
        ? ''
        : resolveCmsImage(rawMobileBg ?? defaults.hero.mobileBackgroundImage, defaults.hero.mobileBackgroundImage),
    footerLinkHref: normalizeHomepageHref(apiData.hero?.footerLinkHref ?? defaults.hero.footerLinkHref),
  }

  const rentSection = {
    ...defaults.rentSection,
    ...apiData.rentSection,
    cards: mergeCards(apiData.rentSection?.cards, defaults.rentSection.cards),
  }

  const whySection = {
    ...defaults.whySection,
    ...apiData.whySection,
    photo: resolveCmsImage(apiData.whySection?.photo, defaults.whySection.photo),
    badge: {
      ...defaults.whySection.badge,
      ...apiData.whySection?.badge,
    },
    featuresLeft: mergeFeatures(apiData.whySection?.featuresLeft, defaults.whySection.featuresLeft),
    featuresRight: mergeFeatures(apiData.whySection?.featuresRight, defaults.whySection.featuresRight),
  }

  const picksSection = {
    ...defaults.picksSection,
    ...apiData.picksSection,
    tabs: normalizePicksTabs(
      apiData.picksSection?.tabs?.length ? apiData.picksSection.tabs : defaults.picksSection.tabs,
    ),
  }

  const header = {
    ...defaults.header,
    ...apiData.header,
    navLinks: normalizeLinkList(
      apiData.header?.navLinks?.length ? apiData.header.navLinks : defaults.header.navLinks,
    ),
    ctaHref: normalizeHomepageHref(apiData.header?.ctaHref ?? defaults.header.ctaHref),
  }

  const topbar = {
    ...defaults.topbar,
    ...apiData.topbar,
    linkHref: normalizeHomepageHref(apiData.topbar?.linkHref ?? defaults.topbar.linkHref),
  }

  const staySection = {
    ...defaults.staySection,
    ...apiData.staySection,
    allHref: normalizeHomepageHref(apiData.staySection?.allHref ?? defaults.staySection.allHref),
  }

  const hostCtaSection = {
    ...defaults.hostCtaSection,
    ...apiData.hostCtaSection,
    houseImage: resolveCmsImage(apiData.hostCtaSection?.houseImage, defaults.hostCtaSection.houseImage),
    vanImage: resolveCmsImage(apiData.hostCtaSection?.vanImage, defaults.hostCtaSection.vanImage),
    primaryHref: normalizeHomepageHref(
      apiData.hostCtaSection?.primaryHref ?? defaults.hostCtaSection.primaryHref,
    ),
    secondaryHref: normalizeHomepageHref(
      apiData.hostCtaSection?.secondaryHref ?? defaults.hostCtaSection.secondaryHref,
    ),
  }

  const footer = {
    ...defaults.footer,
    ...apiData.footer,
    columns: normalizeFooterColumns(
      apiData.footer?.columns?.length ? apiData.footer.columns : defaults.footer.columns,
    ),
    legal: normalizeLinkList(
      apiData.footer?.legal?.length ? apiData.footer.legal : defaults.footer.legal,
    ),
    social: apiData.footer?.social?.length ? apiData.footer.social : defaults.footer.social,
  }

  const blogPosts = apiData.featuredBlogPosts?.length
    ? apiData.featuredBlogPosts.map(mapFeaturedBlogPost)
    : defaults.blogSection.posts

  return {
    topbar,
    header,
    hero,
    trustItems: mergeTrustItems(apiData.trustItems, defaults.trustItems),
    rentSection,
    whySection,
    picksSection,
    howSection: {
      ...defaults.howSection,
      ...apiData.howSection,
      steps: mergeSteps(apiData.howSection?.steps, defaults.howSection.steps),
    },
    staySection,
    blogSection: {
      ...defaults.blogSection,
      ...apiData.blogSection,
      allHref: normalizeHomepageHref(apiData.blogSection?.allHref ?? defaults.blogSection.allHref),
      posts: blogPosts,
    },
    hostCtaSection,
    reviewsSection: {
      ...defaults.reviewsSection,
      ...apiData.reviewsSection,
      reviews: apiData.reviewsSection?.reviews?.length
        ? apiData.reviewsSection.reviews
        : apiData.reviewsSection?.isDemo === false
          ? []
          : defaults.reviewsSection.reviews,
    },
    guestHousesHighlight: apiData.guestHousesHighlight || null,
    faqSection: { ...defaults.faqSection, ...apiData.faqSection },
    newsSection: {
      ...defaults.newsSection,
      ...apiData.newsSection,
      backgroundImage: resolveCmsImage(
        apiData.newsSection?.backgroundImage,
        defaults.newsSection.backgroundImage,
      ),
    },
    footer,
  }
}
