import { useMemo } from 'react'
import { useLocation } from 'react-router-dom'
import { usePageContent } from '../context/SiteContentContext'
import { resolveSeo } from '../utils/resolveSeo'

export default function usePageSeo(pageKey, { source = {}, robots, skipPageSeo = false } = {}) {
  const { pathname } = useLocation()
  const { page: globalPage } = usePageContent('global')
  const { page: contentPage } = usePageContent(pageKey || '__none__')

  const mergedSource = useMemo(
    () => (skipPageSeo || !pageKey ? source : { ...contentPage, ...source }),
    [skipPageSeo, pageKey, contentPage, source],
  )

  return useMemo(
    () =>
      resolveSeo({
        globalSeo: globalPage.seo ?? {},
        pageSeo: skipPageSeo || !pageKey ? {} : (contentPage.seo ?? {}),
        source: mergedSource,
        pathname,
        robots,
      }),
    [globalPage.seo, contentPage.seo, mergedSource, pathname, robots, skipPageSeo, pageKey],
  )
}
