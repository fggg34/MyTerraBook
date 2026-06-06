import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { api } from '../api'
import {
  buildSiteDataFromContent,
  defaultSiteContentData,
  mergeAllSiteContent,
} from '../data/defaultSiteContentData'
import { mergePageContent } from '../utils/mergePageContent'

const SiteContentContext = createContext(null)

export function SiteContentProvider({ children }) {
  const [pages, setPages] = useState(defaultSiteContentData)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api
      .get('/site-content')
      .then((res) => {
        const apiPages = res.data?.data ?? res.data ?? {}
        setPages(mergeAllSiteContent(apiPages))
      })
      .catch(() => setPages(defaultSiteContentData))
      .finally(() => setLoading(false))
  }, [])

  const siteData = useMemo(() => buildSiteDataFromContent(pages), [pages])

  const getPage = useCallback((pageKey) => pages[pageKey] ?? {}, [pages])

  const getSection = useCallback(
    (pageKey, sectionKey) => {
      const page = pages[pageKey] ?? {}
      return page[sectionKey] ?? {}
    },
    [pages],
  )

  const global = useMemo(() => pages.global ?? defaultSiteContentData.global ?? {}, [pages])

  const value = useMemo(
    () => ({
      pages,
      siteData,
      global,
      loading,
      getPage,
      getSection,
      branding: global.branding ?? {},
    }),
    [pages, siteData, global, loading, getPage, getSection],
  )

  return <SiteContentContext.Provider value={value}>{children}</SiteContentContext.Provider>
}

export function useSiteContent() {
  const ctx = useContext(SiteContentContext)
  if (!ctx) {
    throw new Error('useSiteContent must be used within SiteContentProvider')
  }
  return ctx
}

export function usePageContent(pageKey, fallback = {}) {
  const { getPage, loading } = useSiteContent()
  const page = useMemo(
    () => mergePageContent(fallback, getPage(pageKey)),
    [fallback, getPage, pageKey],
  )
  return { page, loading }
}
