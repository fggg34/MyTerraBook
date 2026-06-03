import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import { api } from '../api'
import { defaultHomepageData } from '../data/defaultHomepageData'
import { mergeHomepageData } from '../utils/mergeHomepageData'

const SiteLayoutContext = createContext(null)

export function SiteLayoutProvider({ children }) {
  const [siteData, setSiteData] = useState(defaultHomepageData)

  useEffect(() => {
    document.body.classList.add('homepage-active')
    document.documentElement.style.scrollBehavior = 'smooth'

    api
      .get('/homepage')
      .then((res) => setSiteData(mergeHomepageData(res.data)))
      .catch(() => setSiteData(defaultHomepageData))

    return () => {
      document.body.classList.remove('homepage-active')
      document.documentElement.style.scrollBehavior = ''
    }
  }, [])

  const value = useMemo(() => ({ siteData }), [siteData])

  return <SiteLayoutContext.Provider value={value}>{children}</SiteLayoutContext.Provider>
}

export function useSiteLayout() {
  const ctx = useContext(SiteLayoutContext)
  if (!ctx) {
    throw new Error('useSiteLayout must be used within SiteLayoutProvider')
  }
  return ctx
}
