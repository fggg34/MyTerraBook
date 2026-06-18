import { useEffect, useMemo, useState } from 'react'
import { api } from '../api'
import { useSiteLayout } from '../context/SiteLayoutContext'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'
import { mergeHomepageData } from '../utils/mergeHomepageData'
import { getBootstrappedHomepage } from '../utils/siteBootstrap'
import HomePage from './HomePage'

const bootstrappedHomepage = getBootstrappedHomepage()

export default function HomePageContainer() {
  const { siteData } = useSiteLayout()
  const seo = usePageSeo('home', {
    source: {
      heading: siteData?.hero?.heading,
      subtitle: siteData?.hero?.subtitle,
      backgroundImage: siteData?.hero?.backgroundImage,
    },
  })
  const [homepageData, setHomepageData] = useState(bootstrappedHomepage)

  useEffect(() => {
    let cancelled = false

    api
      .get('/homepage')
      .then((res) => {
        if (!cancelled) setHomepageData(res.data || null)
      })
      .catch(() => {
        if (!cancelled && !bootstrappedHomepage) setHomepageData(null)
      })

    return () => {
      cancelled = true
    }
  }, [])

  const pageData = useMemo(() => {
    return mergeHomepageData({ ...siteData, ...homepageData })
  }, [siteData, homepageData])

  return (
    <>
      <PageHead {...seo} />
      <HomePage pageData={pageData} />
    </>
  )
}
