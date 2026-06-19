import { useEffect, useMemo, useState } from 'react'
import { api } from '../api'
import { useSiteContent } from '../context/SiteContentContext'
import useSiteChromeData from '../hooks/useSiteChromeData'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'
import { mergeHomepageData } from '../utils/mergeHomepageData'
import { getBootstrappedHomepage } from '../utils/siteBootstrap'
import HomePage from './HomePage'

const bootstrappedHomepage = getBootstrappedHomepage()
const hasBootstrappedHomepage = bootstrappedHomepage != null

export default function HomePageContainer() {
  const siteData = useSiteChromeData()
  const { loading: siteLoading, useDefaults } = useSiteContent()
  const [homepageData, setHomepageData] = useState(bootstrappedHomepage)
  const [homepageLoading, setHomepageLoading] = useState(!hasBootstrappedHomepage)

  const seo = usePageSeo('home', {
    source: {
      heading: siteData?.hero?.heading,
      subtitle: siteData?.hero?.subtitle,
      backgroundImage: siteData?.hero?.backgroundImage,
    },
  })

  useEffect(() => {
    let cancelled = false

    api
      .get('/homepage')
      .then((res) => {
        if (!cancelled) setHomepageData(res.data || null)
      })
      .catch(() => {
        if (!cancelled && !hasBootstrappedHomepage) setHomepageData(null)
      })
      .finally(() => {
        if (!cancelled) setHomepageLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [])

  const contentReady = !siteLoading && !homepageLoading

  const pageData = useMemo(() => {
    if (!contentReady) {
      return mergeHomepageData({}, { useImageFallbacks: false })
    }

    return mergeHomepageData(
      { ...siteData, ...homepageData },
      { useImageFallbacks: useDefaults },
    )
  }, [siteData, homepageData, contentReady, useDefaults])

  return (
    <>
      <PageHead {...seo} />
      <HomePage pageData={pageData} contentReady={contentReady} />
    </>
  )
}
