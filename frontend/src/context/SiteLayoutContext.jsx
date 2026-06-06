import { createContext, useContext } from 'react'
import { useSiteContent } from './SiteContentContext'

const SiteLayoutContext = createContext(null)

/** @deprecated Prefer useSiteContent(); kept for existing layout components. */
export function SiteLayoutProvider({ children }) {
  const { siteData } = useSiteContent()
  return <SiteLayoutContext.Provider value={{ siteData }}>{children}</SiteLayoutContext.Provider>
}

export function useSiteLayout() {
  const fromLegacy = useContext(SiteLayoutContext)
  if (fromLegacy) {
    return fromLegacy
  }

  const { siteData } = useSiteContent()
  return { siteData }
}
