import { useEffect, useMemo, useState } from 'react'
import { api } from '../api'
import { VEHICLE_TYPES } from '../data/searchResultsConfig'
import { useFormatPrice } from './useFormatPrice'
import { mapCarsToPickCards } from '../utils/mapCarsToPickCards'
import { mapGuestHousesToStayCards } from '../utils/mapGuestHousesToStayCards'
import { mainCategoryMatchesVehicleType } from '../utils/vehicleCategoryFilter'

function splitCarsIntoPickTabs(cars = [], priceFormatter) {
  const camper = []
  const car = []

  for (const item of cars) {
    if (mainCategoryMatchesVehicleType(item.main_category_slug, 'campervan')) {
      camper.push(
        mapCarsToPickCards([item], { detailBase: VEHICLE_TYPES.campervan.route, priceFormatter })[0],
      )
    } else if (mainCategoryMatchesVehicleType(item.main_category_slug, 'car')) {
      car.push(mapCarsToPickCards([item], { detailBase: VEHICLE_TYPES.car.route, priceFormatter })[0])
    }
  }

  return {
    camper: camper.slice(0, 8),
    car: car.slice(0, 8),
  }
}

export function usePicksListings() {
  const priceFormatter = useFormatPrice()
  const [cars, setCars] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let cancelled = false
    setLoading(true)

    api
      .get('/cars')
      .then((res) => {
        if (!cancelled) setCars(res.data?.data ?? [])
      })
      .catch(() => {
        if (!cancelled) setCars([])
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [])

  const items = useMemo(() => splitCarsIntoPickTabs(cars, priceFormatter), [cars, priceFormatter])

  return { items, loading }
}

export function useStayListings(limit = 8) {
  const priceFormatter = useFormatPrice()
  const [houses, setHouses] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let cancelled = false
    setLoading(true)

    api
      .get('/guest-houses', { params: { per_page: limit } })
      .then((res) => {
        if (!cancelled) setHouses(res.data?.data ?? [])
      })
      .catch(() => {
        if (!cancelled) setHouses([])
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [limit])

  const cards = useMemo(() => mapGuestHousesToStayCards(houses, { priceFormatter }), [houses, priceFormatter])

  return { cards, loading }
}
