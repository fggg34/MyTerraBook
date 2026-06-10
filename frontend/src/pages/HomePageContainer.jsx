import { useEffect, useMemo, useState } from 'react'
import { api } from '../api'
import { useSiteLayout } from '../context/SiteLayoutContext'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'
import { mergeHomepageData } from '../utils/mergeHomepageData'
import HomePage from './HomePage'

export default function HomePageContainer() {
  const { siteData } = useSiteLayout()
  const seo = usePageSeo('home', {
    source: {
      heading: siteData?.hero?.heading,
      subtitle: siteData?.hero?.subtitle,
      backgroundImage: siteData?.hero?.backgroundImage,
    },
  })
  const [homepageData, setHomepageData] = useState(null)

  useEffect(() => {
    api
      .get('/homepage')
      .then((res) => setHomepageData(res.data || null))
      .catch(() => setHomepageData(null))
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
