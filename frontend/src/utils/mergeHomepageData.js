import { resolveCmsImage, resolveStorageUrl } from '../api'
import { defaultHomepageData } from '../data/defaultHomepageData'
import { normalizeHomepageHref, normalizeLinkList } from './normalizeHomepageHref'

function mergeCards(cards, fallbackCards, useImageFallbacks) {
  if (!Array.isArray(cards) || !cards.length) return useImageFallbacks ? fallbackCards : []
  return cards.map((card, index) => ({
    ...(useImageFallbacks ? fallbackCards[index] : {}),
    ...card,
    image: resolveCmsImage(card.image, useImageFallbacks ? fallbackCards[index]?.image : null),
    href: normalizeHomepageHref(card.href ?? fallbackCards[index]?.href),
  }))
}

function mergeSteps(steps, fallbackSteps, useImageFallbacks) {
  if (!Array.isArray(steps) || !steps.length) return useImageFallbacks ? fallbackSteps : []
  return steps.map((step, index) => {
    const fallback = fallbackSteps[index] || {}
    const mergedTags = Array.isArray(step.tags)
      ? [...new Set(step.tags.filter(Boolean))]
      : fallback.tags
    return {
      ...(useImageFallbacks ? fallback : {}),
      ...step,
      title: fixIcelandicPlaceNames(step.title ?? fallback.title),
      description: fixIcelandicPlaceNames(step.description ?? fallback.description),
      tags: mergedTags?.length ? mergedTags : fallback.tags,
      image: resolveCmsImage(step.image, useImageFallbacks ? fallback.image : null),
    }
  })
}

function fixIcelandicPlaceNames(text) {
  if (typeof text !== 'string') return text
  return text.replace(/\bReykjavik\b/g, 'Reykjavík').replace(/\bKeflavik\b/g, 'Keflavík')
}

function mergeFeatures(features, fallbackFeatures, useImageFallbacks) {
  if (!Array.isArray(fallbackFeatures) || !fallbackFeatures.length) {
    return Array.isArray(features) ? features : []
  }
  if (!Array.isArray(features) || !features.length) return useImageFallbacks ? fallbackFeatures : []
  return fallbackFeatures.map((feature, index) => ({
    ...feature,
    ...(features[index] || {}),
  }))
}

function mergeTrustItems(items, fallbackItems, useImageFallbacks) {
  if (!Array.isArray(items) || !items.length) return useImageFallbacks ? fallbackItems : []
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

export function mergeHomepageData(apiData = {}, { useImageFallbacks = true } = {}) {
  const defaults = defaultHomepageData
  const withDefaults = (sectionDefaults, sectionData = {}) =>
    useImageFallbacks ? { ...sectionDefaults, ...sectionData } : { ...sectionData }

  const rawMobileBg = apiData.hero?.mobileBackgroundImage
  const hero = {
    ...withDefaults(defaults.hero, apiData.hero),
    backgroundImage: resolveCmsImage(
      apiData.hero?.backgroundImage,
      useImageFallbacks ? defaults.hero.backgroundImage : null,
    ),
    mobileBackgroundImage:
      rawMobileBg === ''
        ? ''
        : resolveCmsImage(
            rawMobileBg ?? (useImageFallbacks ? defaults.hero.mobileBackgroundImage : null),
            useImageFallbacks ? defaults.hero.mobileBackgroundImage : null,
          ),
    footerLinkHref: normalizeHomepageHref(
      apiData.hero?.footerLinkHref ?? (useImageFallbacks ? defaults.hero.footerLinkHref : undefined),
    ),
  }

  const rentSection = {
    ...withDefaults(defaults.rentSection, apiData.rentSection),
    cards: mergeCards(apiData.rentSection?.cards, defaults.rentSection.cards, useImageFallbacks),
  }

  const whySection = {
    ...withDefaults(defaults.whySection, apiData.whySection),
    photo: resolveCmsImage(
      apiData.whySection?.photo,
      useImageFallbacks ? defaults.whySection.photo : null,
    ),
    badge: withDefaults(defaults.whySection.badge, apiData.whySection?.badge),
    featuresLeft: mergeFeatures(
      apiData.whySection?.featuresLeft,
      defaults.whySection.featuresLeft,
      useImageFallbacks,
    ),
    featuresRight: mergeFeatures(
      apiData.whySection?.featuresRight,
      defaults.whySection.featuresRight,
      useImageFallbacks,
    ),
  }

  const picksSection = {
    ...withDefaults(defaults.picksSection, apiData.picksSection),
    tabs: normalizePicksTabs(
      apiData.picksSection?.tabs?.length
        ? apiData.picksSection.tabs
        : useImageFallbacks
          ? defaults.picksSection.tabs
          : [],
    ),
  }

  const header = {
    ...withDefaults(defaults.header, apiData.header),
    navLinks: normalizeLinkList(
      apiData.header?.navLinks?.length
        ? apiData.header.navLinks
        : useImageFallbacks
          ? defaults.header.navLinks
          : [],
    ),
    ctaHref: normalizeHomepageHref(
      apiData.header?.ctaHref ?? (useImageFallbacks ? defaults.header.ctaHref : undefined),
    ),
  }

  const topbar = {
    ...withDefaults(defaults.topbar, apiData.topbar),
    linkHref: normalizeHomepageHref(
      apiData.topbar?.linkHref ?? (useImageFallbacks ? defaults.topbar.linkHref : undefined),
    ),
  }

  const staySection = {
    ...withDefaults(defaults.staySection, apiData.staySection),
    allHref: normalizeHomepageHref(
      apiData.staySection?.allHref ?? (useImageFallbacks ? defaults.staySection.allHref : undefined),
    ),
  }

  const hostCtaSection = {
    ...withDefaults(defaults.hostCtaSection, apiData.hostCtaSection),
    houseImage: resolveCmsImage(
      apiData.hostCtaSection?.houseImage,
      useImageFallbacks ? defaults.hostCtaSection.houseImage : null,
    ),
    vanImage: resolveCmsImage(
      apiData.hostCtaSection?.vanImage,
      useImageFallbacks ? defaults.hostCtaSection.vanImage : null,
    ),
    primaryHref: normalizeHomepageHref(
      apiData.hostCtaSection?.primaryHref ?? (useImageFallbacks ? defaults.hostCtaSection.primaryHref : undefined),
    ),
    secondaryHref: normalizeHomepageHref(
      apiData.hostCtaSection?.secondaryHref ?? (useImageFallbacks ? defaults.hostCtaSection.secondaryHref : undefined),
    ),
  }

  const footer = {
    ...withDefaults(defaults.footer, apiData.footer),
    columns: normalizeFooterColumns(
      apiData.footer?.columns?.length
        ? apiData.footer.columns
        : useImageFallbacks
          ? defaults.footer.columns
          : [],
    ),
    legal: normalizeLinkList(
      apiData.footer?.legal?.length ? apiData.footer.legal : useImageFallbacks ? defaults.footer.legal : [],
    ),
    social: apiData.footer?.social?.length
      ? apiData.footer.social
      : useImageFallbacks
        ? defaults.footer.social
        : [],
  }

  const blogPosts = apiData.featuredBlogPosts?.length
    ? apiData.featuredBlogPosts.map(mapFeaturedBlogPost)
    : useImageFallbacks
      ? defaults.blogSection.posts
      : []

  return {
    topbar,
    header,
    hero,
    trustItems: mergeTrustItems(apiData.trustItems, defaults.trustItems, useImageFallbacks),
    rentSection,
    whySection,
    picksSection,
    howSection: {
      ...withDefaults(defaults.howSection, apiData.howSection),
      steps: mergeSteps(apiData.howSection?.steps, defaults.howSection.steps, useImageFallbacks),
    },
    staySection,
    blogSection: {
      ...withDefaults(defaults.blogSection, apiData.blogSection),
      allHref: normalizeHomepageHref(
        apiData.blogSection?.allHref ?? (useImageFallbacks ? defaults.blogSection.allHref : undefined),
      ),
      posts: blogPosts,
    },
    hostCtaSection,
    reviewsSection: {
      ...withDefaults(defaults.reviewsSection, apiData.reviewsSection),
      reviews: apiData.reviewsSection?.reviews?.length
        ? apiData.reviewsSection.reviews
        : apiData.reviewsSection?.isDemo === false
          ? []
          : useImageFallbacks
            ? defaults.reviewsSection.reviews
            : [],
    },
    guestHousesHighlight: apiData.guestHousesHighlight || null,
    faqSection: withDefaults(defaults.faqSection, apiData.faqSection),
    newsSection: {
      ...withDefaults(defaults.newsSection, apiData.newsSection),
      backgroundImage: resolveCmsImage(
        apiData.newsSection?.backgroundImage,
        useImageFallbacks ? defaults.newsSection.backgroundImage : null,
      ),
    },
    footer,
  }
}
