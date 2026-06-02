import { useEffect, useState } from 'react'
import { api } from '../api'
import { PageLoader } from '../components/ui/LoadingSpinner'
import HomePage from './HomePage'

export default function HomePageContainer() {
  const [pageData, setPageData] = useState(null)
  const [error, setError] = useState(null)

  useEffect(() => {
    api
      .get('/homepage')
      .then((res) => setPageData(res.data))
      .catch((err) => {
        setError(err.response?.data?.message || 'Could not load homepage content.')
      })
  }, [])

  if (error) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-brand-950 px-6 text-center text-white">
        <p>{error}</p>
      </div>
    )
  }

  if (!pageData) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-brand-950">
        <PageLoader />
      </div>
    )
  }

  return <HomePage pageData={pageData} />
}
