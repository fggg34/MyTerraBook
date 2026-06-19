import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react'
import { api } from '../api'
import {
  buildSiteDataFromContent,
  defaultSiteContentData,
} from '../data/defaultSiteContentData'
import { mergePageContent } from '../utils/mergePageContent'
import { getBootstrappedSiteContent, mergeBranding } from '../utils/siteBootstrap'

const SiteContentContext = createContext(null)

const bootstrappedPages = getBootstrappedSiteContent()
const hasBootstrappedPages = bootstrappedPages != null

export function SiteContentProvider({ children }) {
  const [pages, setPages] = useState(() => bootstrappedPages ?? {})
  const [loading, setLoading] = useState(!hasBootstrappedPages)
  const [useDefaults, setUseDefaults] = useState(false)
  const bootstrappedRef = useRef(hasBootstrappedPages)

  useEffect(() => {
    let cancelled = false

    api
      .get('/site-content')
      .then((res) => {
        if (cancelled) return
        const apiPages = res.data?.data ?? res.data ?? {}
        setPages(apiPages)
      })
      .catch(() => {
        if (cancelled) return
        if (bootstrappedRef.current) return
        setPages(defaultSiteContentData)
        setUseDefaults(true)
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [])

  const siteData = useMemo(
    () => buildSiteDataFromContent(pages, { useDefaults }),
    [pages, useDefaults],
  )

  const getPage = useCallback((pageKey) => pages[pageKey] ?? {}, [pages])

  const getSection = useCallback(
    (pageKey, sectionKey) => {
      const page = pages[pageKey] ?? {}
      return page[sectionKey] ?? {}
    },
    [pages],
  )

  const global = useMemo(
    () => pages.global ?? (useDefaults ? defaultSiteContentData.global : {}) ?? {},
    [pages, useDefaults],
  )

  const branding = useMemo(() => mergeBranding(global.branding ?? {}), [global])

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
      useDefaults,
      getPage,
      getSection,
      branding,
    }),
    [pages, siteData, global, branding, loading, useDefaults, getPage, getSection],
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
  const { getPage, loading, useDefaults } = useSiteContent()
  const page = useMemo(() => {
    if (loading) return {}
    const apiPage = getPage(pageKey)
    if (useDefaults) {
      return mergePageContent(fallback, apiPage)
    }
    return { ...fallback, ...apiPage }
  }, [fallback, getPage, pageKey, loading, useDefaults])
  return { page, loading }
}
