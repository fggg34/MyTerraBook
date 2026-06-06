export function normalizePromo(promo) {
  if (!promo) return null
  return {
    id: promo.id ?? promo.key ?? 'promo',
    kicker: promo.kicker ?? '',
    title: promo.title ?? '',
    text: promo.text ?? '',
    cta: promo.cta ?? promo.cta_label ?? '',
    href: promo.href ?? promo.cta_href ?? '#',
    layout: promo.layout ?? 'card',
    insert_after: Number.isFinite(promo.insert_after) ? promo.insert_after : 2,
    image: promo.image ?? null,
    image_alt: promo.image_alt ?? '',
    sort_order: promo.sort_order ?? 0,
  }
}

export function buildSearchGridItems(cards, promotions = [], fallbackPromo = null) {
  const normalized = promotions.map(normalizePromo).filter(Boolean)
  const cardPromos = normalized
    .filter((p) => p.layout !== 'landscape')
    .sort((a, b) => a.insert_after - b.insert_after || a.sort_order - b.sort_order)
  const landscapePromos = normalized
    .filter((p) => p.layout === 'landscape')
    .sort((a, b) => a.sort_order - b.sort_order)

  if (!cardPromos.length && fallbackPromo && cards.length > 0) {
    cardPromos.push(normalizePromo({ ...fallbackPromo, layout: 'card', insert_after: 2 }))
  }

  const items = []
  let cardIndex = 0

  cards.forEach((card, index) => {
    cardPromos
      .filter((promo) => promo.insert_after === cardIndex)
      .forEach((promo) => {
        items.push({ type: 'promo', key: `promo-${promo.id}`, promo, layout: 'card' })
      })
    items.push({ type: 'card', key: card.id, card })
    if (index === 5 && cards.length > 6) {
      items.push({ type: 'banner', key: 'banner' })
    }
    cardIndex += 1
  })

  cardPromos
    .filter((promo) => promo.insert_after >= cards.length)
    .forEach((promo) => {
      items.push({ type: 'promo', key: `promo-${promo.id}`, promo, layout: 'card' })
    })

  landscapePromos.forEach((promo) => {
    items.push({ type: 'promo', key: `promo-landscape-${promo.id}`, promo, layout: 'landscape' })
  })

  return items
}
