import { useEffect, useRef, useState } from 'react'
import { Loader } from '@googlemaps/js-api-loader'
import { parseGooglePlace } from '../../utils/parseGooglePlace'

let loaderPromise = null

function loadGoogleMaps(apiKey) {
  if (!apiKey) return Promise.reject(new Error('Missing Maps API key'))
  if (!loaderPromise) {
    const loader = new Loader({
      apiKey,
      version: 'weekly',
      libraries: ['places'],
    })
    loaderPromise = loader.load()
  }
  return loaderPromise
}

export default function AddressAutocomplete({
  value = {},
  onChange,
  mapsApiKey,
  disabled = false,
  countryRestriction = 'is',
}) {
  const inputRef = useRef(null)
  const autocompleteRef = useRef(null)
  const mapsEventRef = useRef(null)
  const [ready, setReady] = useState(false)
  const [loadError, setLoadError] = useState(false)

  const displayValue = value.formattedAddress || value.address || ''

  useEffect(() => {
    if (!mapsApiKey || disabled) {
      setReady(false)
      return undefined
    }

    let cancelled = false

    loadGoogleMaps(mapsApiKey)
      .then((google) => {
        if (cancelled || !inputRef.current) return

        const options = {
          fields: ['address_components', 'formatted_address', 'geometry', 'name'],
        }
        if (countryRestriction) {
          options.componentRestrictions = { country: countryRestriction }
        }

        const autocomplete = new google.maps.places.Autocomplete(inputRef.current, options)
        autocomplete.addListener('place_changed', () => {
          const place = autocomplete.getPlace()
          if (!place || !place.geometry) return
          const parsed = parseGooglePlace(place)
          onChange?.({
            address: parsed.address,
            city: parsed.city || value.city || '',
            country: parsed.country || value.country || 'Iceland',
            latitude: parsed.latitude,
            longitude: parsed.longitude,
            formattedAddress: parsed.formattedAddress,
          })
        })

        autocompleteRef.current = autocomplete
        mapsEventRef.current = google.maps.event
        setReady(true)
        setLoadError(false)
      })
      .catch(() => {
        if (!cancelled) {
          setLoadError(true)
          setReady(false)
        }
      })

    return () => {
      cancelled = true
      if (autocompleteRef.current && mapsEventRef.current) {
        mapsEventRef.current.clearInstanceListeners(autocompleteRef.current)
        autocompleteRef.current = null
      }
    }
  }, [mapsApiKey, disabled, countryRestriction, onChange, value.city, value.country])

  if (!mapsApiKey || loadError) {
    return (
      <div className="host-location-fallback">
        <div className="host-field">
          <label>Street address</label>
          <input
            value={value.address || ''}
            disabled={disabled}
            placeholder="e.g. Laugavegur 12"
            onChange={(e) => onChange?.({ ...value, address: e.target.value, formattedAddress: e.target.value })}
          />
        </div>
        <div className="grid grid-cols-2 gap-3">
          <div className="host-field">
            <label>City</label>
            <input
              value={value.city || ''}
              disabled={disabled}
              onChange={(e) => onChange?.({ ...value, city: e.target.value })}
            />
          </div>
          <div className="host-field">
            <label>Country</label>
            <input
              value={value.country || ''}
              disabled={disabled}
              onChange={(e) => onChange?.({ ...value, country: e.target.value })}
            />
          </div>
        </div>
        {!mapsApiKey && (
          <p className="host-field-hint">Enter your address manually below.</p>
        )}
        {mapsApiKey && loadError && (
          <p className="host-field-hint">Address suggestions are unavailable. Enter your address manually below.</p>
        )}
      </div>
    )
  }

  return (
    <div className="host-location-autocomplete">
      <div className="host-field">
        <label>Property address</label>
        <input
          ref={inputRef}
          type="text"
          defaultValue={displayValue}
          key={displayValue || 'empty'}
          disabled={disabled || !ready}
          placeholder="Start typing your address…"
          autoComplete="off"
        />
        <p className="host-field-hint">
          Start typing your address and pick the closest match. Rural properties without a street number can use the place name.
        </p>
      </div>
      {(value.city || value.country) && (
        <div className="host-location-preview">
          {value.address && <span>{value.address}</span>}
          {value.city && <span>{value.city}</span>}
          {value.country && <span>{value.country}</span>}
        </div>
      )}
      <div className="grid grid-cols-2 gap-3">
        <div className="host-field">
          <label>City</label>
          <input
            value={value.city || ''}
            disabled={disabled}
            onChange={(e) => onChange?.({ ...value, city: e.target.value })}
          />
        </div>
        <div className="host-field">
          <label>Country</label>
          <input
            value={value.country || ''}
            disabled={disabled}
            onChange={(e) => onChange?.({ ...value, country: e.target.value })}
          />
        </div>
      </div>
    </div>
  )
}
