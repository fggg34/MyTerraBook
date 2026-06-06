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

  const branding = useMemo(() => global.branding ?? {}, [global])

  useEffect(() => {
    if (typeof document === 'undefined') return

    const favicon = branding.favicon
    if (favicon) {
      let link = document.querySelector("link[rel~='icon']")
      if (!link) {
        link = document.createElement('link')
        link.rel = 'icon'
        document.head.appendChild(link)
      }
      const ext = String(favicon).split('.').pop()?.toLowerCase()
      const typeMap = { svg: 'image/svg+xml', png: 'image/png', ico: 'image/x-icon' }
      if (ext && typeMap[ext]) {
        link.type = typeMap[ext]
      } else {
        link.removeAttribute('type')
      }
      link.href = favicon
    }
  }, [branding.favicon])

  const value = useMemo(
    () => ({
      pages,
      siteData,
      global,
      loading,
      getPage,
      getSection,
      branding,
    }),
    [pages, siteData, global, branding, loading, getPage, getSection],
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
