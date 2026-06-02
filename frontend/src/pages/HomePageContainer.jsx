import { useEffect, useState } from 'react'
import { api } from '../api'
import { defaultHomepageData } from '../data/defaultHomepageData'
import { mapCarsToPickCards } from '../utils/mapCarsToPickCards'
import { mergeHomepageData } from '../utils/mergeHomepageData'
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
  const [pageData, setPageData] = useState(null)

  useEffect(() => {
    document.body.classList.add('homepage-active')
    document.documentElement.style.scrollBehavior = 'smooth'

    Promise.all([
      api.get('/homepage').catch(() => ({ data: {} })),
      api.get('/cars').catch(() => ({ data: { data: [] } })),
    ])
      .then(([homeRes, carsRes]) => {
        const merged = mergeHomepageData(homeRes.data)
        const cars = carsRes.data?.data ?? []
        setPageData(withBackendCars(merged, cars))
      })
      .catch(() => setPageData(defaultHomepageData))

    return () => {
      document.body.classList.remove('homepage-active')
      document.documentElement.style.scrollBehavior = ''
    }
  }, [])

  if (!pageData) {
    return <HomePage pageData={defaultHomepageData} />
  }

  return <HomePage pageData={pageData} />
}
