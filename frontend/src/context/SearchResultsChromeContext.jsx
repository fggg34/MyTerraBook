import { createContext, useContext, useMemo, useState } from 'react'

const SearchResultsChromeContext = createContext(null)

export function SearchResultsChromeProvider({ pillText, children }) {
  const [condensed, setCondensed] = useState(false)
  const [stuck, setStuck] = useState(false)

  const value = useMemo(
    () => ({
      pillText,
      condensed,
      setCondensed,
      stuck,
      setStuck,
    }),
    [pillText, condensed, stuck],
  )

  return <SearchResultsChromeContext.Provider value={value}>{children}</SearchResultsChromeContext.Provider>
}

export function useSearchResultsChrome() {
  return useContext(SearchResultsChromeContext)
}
