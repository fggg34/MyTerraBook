/**
 * Deep-merge API page content over static defaults.
 */
export function mergePageContent(defaults = {}, apiData = {}) {
  if (!apiData || typeof apiData !== 'object') {
    return { ...defaults }
  }

  return deepMerge(defaults, apiData)
}

function deepMerge(base, patch) {
  if (Array.isArray(patch)) {
    return patch
  }

  const result = { ...base }

  for (const [key, value] of Object.entries(patch)) {
    if (value === null || value === undefined) {
      continue
    }

    if (Array.isArray(value)) {
      result[key] = value
      continue
    }

    if (typeof value === 'object' && typeof result[key] === 'object' && !Array.isArray(result[key])) {
      result[key] = deepMerge(result[key] ?? {}, value)
    } else {
      result[key] = value
    }
  }

  return result
}
