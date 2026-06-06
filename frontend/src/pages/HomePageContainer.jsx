import { useEffect, useMemo, useState } from 'react'
import { api } from '../api'
import { useSiteLayout } from '../context/SiteLayoutContext'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'
import { mapCarsToPickCards } from '../utils/mapCarsToPickCards'
import { mapGuestHousesToStayCards } from '../utils/mapGuestHousesToStayCards'
import HomePage from './HomePage'

function withBackendListings(pageData, cars = [], guesthouses = []) {
  let next = pageData

  if (cars.length) {
    const pickCards = mapCarsToPickCards(cars).slice(0, 8)
    next = {
      ...next,
      picksSection: {
        ...next.picksSection,
        items: {
          ...next.picksSection.items,
          car: pickCards,
        },
      },
    }
  }

  if (guesthouses.length) {
    const stayCards = mapGuestHousesToStayCards(guesthouses).slice(0, 8)
    next = {
      ...next,
      staySection: {
        ...next.staySection,
        cards: stayCards,
      },
    }
  }

  return next
}

export default function HomePageContainer() {
  const { siteData } = useSiteLayout()
  const seo = usePageSeo('home', {
    source: {
      heading: siteData?.hero?.heading,
      subtitle: siteData?.hero?.subtitle,
      backgroundImage: siteData?.hero?.backgroundImage,
    },
  })
  const [cars, setCars] = useState([])
  const [guesthouses, setGuesthouses] = useState([])

  useEffect(() => {
    api
      .get('/cars')
      .then((res) => setCars(res.data?.data ?? []))
      .catch(() => setCars([]))
  }, [])

  useEffect(() => {
    api
      .get('/guest-houses', { params: { per_page: 8 } })
      .then((res) => setGuesthouses(res.data?.data ?? []))
      .catch(() => setGuesthouses([]))
  }, [])

  const pageData = useMemo(
    () => withBackendListings(siteData, cars, guesthouses),
    [siteData, cars, guesthouses],
  )

  return (
    <>
      <PageHead {...seo} />
      <HomePage pageData={pageData} />
    </>
  )
}
