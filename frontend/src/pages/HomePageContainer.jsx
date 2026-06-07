import { useEffect, useMemo, useState } from 'react'
import { api } from '../api'
import { useSiteLayout } from '../context/SiteLayoutContext'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'
import { VEHICLE_TYPES } from '../data/searchResultsConfig'
import { mapCarsToPickCards } from '../utils/mapCarsToPickCards'
import { mapGuestHousesToStayCards } from '../utils/mapGuestHousesToStayCards'
import { mainCategoryMatchesVehicleType } from '../utils/vehicleCategoryFilter'
import HomePage from './HomePage'

function splitCarsIntoPickTabs(cars = []) {
  const camper = []
  const car = []

  for (const item of cars) {
    if (mainCategoryMatchesVehicleType(item.main_category_slug, 'campervan')) {
      camper.push(
        mapCarsToPickCards([item], { detailBase: VEHICLE_TYPES.campervan.route })[0],
      )
    } else if (mainCategoryMatchesVehicleType(item.main_category_slug, 'car')) {
      car.push(mapCarsToPickCards([item], { detailBase: VEHICLE_TYPES.car.route })[0])
    }
  }

  return {
    camper: camper.slice(0, 8),
    car: car.slice(0, 8),
  }
}

function withBackendListings(pageData, cars = [], guesthouses = []) {
  let next = pageData

  if (cars.length) {
    const pickTabs = splitCarsIntoPickTabs(cars)
    next = {
      ...next,
      picksSection: {
        ...next.picksSection,
        items: {
          ...next.picksSection.items,
          ...(pickTabs.camper.length ? { camper: pickTabs.camper } : {}),
          ...(pickTabs.car.length ? { car: pickTabs.car } : {}),
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
