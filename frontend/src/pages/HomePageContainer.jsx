import { useEffect, useMemo, useState } from 'react'
import { api } from '../api'
import { useSiteLayout } from '../context/SiteLayoutContext'
import { mapCarsToPickCards } from '../utils/mapCarsToPickCards'
import HomePage from './HomePage'

function withBackendCars(pageData, cars = []) {
  if (!cars.length) return pageData

  const pickCards = mapCarsToPickCards(cars).slice(0, 8)
  return {
    ...pageData,
    picksSection: {
      ...pageData.picksSection,
      items: {
        ...pageData.picksSection.items,
        car: pickCards,
      },
    },
  }
}

export default function HomePageContainer() {
  const { siteData } = useSiteLayout()
  const [cars, setCars] = useState([])

  useEffect(() => {
    api
      .get('/cars')
      .then((res) => setCars(res.data?.data ?? []))
      .catch(() => setCars([]))
  }, [])

  const pageData = useMemo(() => withBackendCars(siteData, cars), [siteData, cars])

  return <HomePage pageData={pageData} />
}
