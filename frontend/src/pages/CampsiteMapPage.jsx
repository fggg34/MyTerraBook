import CampsiteFigma from '../components/content/CampsiteFigma'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'

export default function CampsiteMapPage() {
  const seo = usePageSeo('campsite-map', { source: { title: 'Campsite map' } })

  return (
    <>
      <PageHead {...seo} />
      <CampsiteFigma />
    </>
  )
}
