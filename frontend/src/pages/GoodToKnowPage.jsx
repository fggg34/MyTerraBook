import GuidesHub from '../components/content/GuidesHub'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'

export default function GoodToKnowPage() {
  const seo = usePageSeo('good-to-know')

  return (
    <>
      <PageHead {...seo} />
      <GuidesHub />
    </>
  )
}
