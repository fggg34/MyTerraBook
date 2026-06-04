import { defaultHomepageData } from '../data/defaultHomepageData'

function mergeImages(value, fallback) {
  if (value === null || value === undefined || value === '') return fallback
  return value
}

function mergeCards(cards, fallbackCards) {
  if (!Array.isArray(cards) || !cards.length) return fallbackCards
  return cards.map((card, index) => ({
    ...fallbackCards[index],
    ...card,
    image: mergeImages(card.image, fallbackCards[index]?.image),
  }))
}

export function mergeHomepageData(apiData = {}) {
  const defaults = defaultHomepageData

  const hero = {
    ...defaults.hero,
    ...apiData.hero,
    backgroundImage: mergeImages(apiData.hero?.backgroundImage, defaults.hero.backgroundImage),
  }

  const rentSection = {
    ...defaults.rentSection,
    ...apiData.rentSection,
    cards: mergeCards(apiData.rentSection?.cards, defaults.rentSection.cards),
  }

  const whySection = {
    ...defaults.whySection,
    ...apiData.whySection,
    photo: mergeImages(apiData.whySection?.photo, defaults.whySection.photo),
    badge: {
      ...defaults.whySection.badge,
      ...apiData.whySection?.badge,
    },
    featuresLeft: apiData.whySection?.featuresLeft?.length
      ? apiData.whySection.featuresLeft
      : defaults.whySection.featuresLeft,
    featuresRight: apiData.whySection?.featuresRight?.length
      ? apiData.whySection.featuresRight
      : defaults.whySection.featuresRight,
  }

  return {
    topbar: { ...defaults.topbar, ...apiData.topbar },
    header: {
      ...defaults.header,
      ...apiData.header,
      navLinks: apiData.header?.navLinks?.length ? apiData.header.navLinks : defaults.header.navLinks,
    },
    hero,
    trustItems: apiData.trustItems?.length ? apiData.trustItems : defaults.trustItems,
    rentSection,
    whySection,
    picksSection: { ...defaults.picksSection, ...apiData.picksSection },
    guestHousesHighlight: {
      ...defaults.guestHousesHighlight,
      ...apiData.guestHousesHighlight,
    },
    howSection: { ...defaults.howSection, ...apiData.howSection },
    staySection: { ...defaults.staySection, ...apiData.staySection },
    blogSection: { ...defaults.blogSection, ...apiData.blogSection },
    hostCtaSection: { ...defaults.hostCtaSection, ...apiData.hostCtaSection },
    reviewsSection: { ...defaults.reviewsSection, ...apiData.reviewsSection },
    faqSection: { ...defaults.faqSection, ...apiData.faqSection },
    newsSection: {
      ...defaults.newsSection,
      ...apiData.newsSection,
      backgroundImage: mergeImages(
        apiData.newsSection?.backgroundImage,
        defaults.newsSection.backgroundImage,
      ),
    },
    footer: {
      ...defaults.footer,
      ...apiData.footer,
      columns: apiData.footer?.columns?.length ? apiData.footer.columns : defaults.footer.columns,
    },
  }
}
