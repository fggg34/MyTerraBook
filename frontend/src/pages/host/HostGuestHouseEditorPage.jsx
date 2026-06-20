import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import {
  addHostGuestHouseAvailability,
  createHostGuestHouse,
  deleteHostGuestHouseImage,
  getHostCatalog,
  getHostGuestHouse,
  getHostGuestHouseAvailability,
  removeHostGuestHouseAvailability,
  resolveStorageUrl,
  submitHostGuestHouse,
  updateHostGuestHouse,
  uploadHostGuestHouseImages,
} from '../../api/host'
import AddressAutocomplete from '../../components/host/AddressAutocomplete'
import HostDatePicker from '../../components/host/HostDatePicker'
import HostTimePicker from '../../components/host/HostTimePicker'
import HostDisclosure from '../../components/host/HostDisclosure'
import HostIconMultiSelect from '../../components/host/HostIconMultiSelect'
import HostReadinessChecklist from '../../components/host/HostReadinessChecklist'
import HostSelect from '../../components/host/HostSelect'
import ListingStatusBadge from '../../components/host/ListingStatusBadge'
import { HOST_IMAGE_FORMAT_HINT, HOST_MIN_DETAIL_IMAGES, HostImageDropzone, HostImageGallery } from '../../components/host/HostImageUpload'
import { useToast } from '../../context/ToastContext'
import { useHostCurrency } from '../../hooks/useHostCurrency'
import { dateRangesOverlap } from '../../utils/hostCarPricingUtils'
import { formatDate, normalizeTimeString } from '../../utils/format'
import { useMapsConfig } from '../../hooks/useMapsConfig'
import { formatLocationLine } from '../../utils/parseGooglePlace'

function optionalNumber(value) {
  if (value === '' || value == null || Number.isNaN(Number(value))) return null
  return Number(value)
}

function buildGuestHouseSavePayload(form, seasonalPrices, seasonalDraft, roomDetails) {
  const seasonalPriceRows = buildSeasonalPriceRows(seasonalPrices, seasonalDraft)
  const payload = {
    name: form.name,
    type: form.type,
    description: form.description,
    short_description: form.short_description,
    address: form.address,
    city: form.city,
    country: form.country,
    latitude: form.latitude === '' ? null : optionalNumber(form.latitude),
    longitude: form.longitude === '' ? null : optionalNumber(form.longitude),
    max_guests: optionalNumber(form.max_guests),
    bedrooms: optionalNumber(form.bedrooms),
    bathrooms: optionalNumber(form.bathrooms),
    beds: optionalNumber(form.beds),
    min_nights: optionalNumber(form.min_nights),
    max_nights: optionalNumber(form.max_nights),
    check_in_time: form.check_in_time || null,
    check_out_time: form.check_out_time || null,
    cancellation_policy: form.cancellation_policy,
    tax_rate_id: form.tax_rate_id,
    cleaning_fee_euros: optionalNumber(form.cleaning_fee_euros),
    security_deposit_euros: optionalNumber(form.security_deposit_euros),
    amenity_ids: form.amenity_ids,
    seasonal_prices: seasonalPriceRows,
    room_details: (roomDetails || [])
      .filter((row) => String(row.title || '').trim())
      .map(({ id, title, text, dim }) => ({
        id: id || null,
        title: String(title).trim(),
        text: String(text || '').trim() || null,
        dim: String(dim || '').trim() || null,
      })),
  }

  if (form.base_price_per_night_euros !== '') {
    payload.base_price_per_night_euros = optionalNumber(form.base_price_per_night_euros)
  }

  return payload
}

function buildSeasonalPriceRows(seasonalPrices, seasonalDraft) {
  const rows = [...seasonalPrices]
  const hasPendingDraft = seasonalDraft.name && seasonalDraft.date_from && seasonalDraft.date_to
  if (hasPendingDraft) {
    rows.push({ ...seasonalDraft, id: null })
  }
  return rows.map((sp) => ({
    id: sp.id || null,
    name: sp.name,
    date_from: sp.date_from,
    date_to: sp.date_to,
    price_per_night_euros: Number(sp.price_per_night_euros ?? (sp.price_per_night ? sp.price_per_night / 100 : 0)),
    minimum_nights: sp.minimum_nights ? Number(sp.minimum_nights) : null,
  }))
}

const STEPS = ['Basics', 'Details', 'Pricing', 'Rules', 'Availability', 'Review']

function makeLocalRoomDetailId() {
  return `local-${Date.now()}-${Math.random().toString(36).slice(2, 9)}`
}

function emptyRoomDetail(overrides = {}) {
  return {
    localId: makeLocalRoomDetailId(),
    id: null,
    title: '',
    text: '',
    dim: '',
    image_path: null,
    pendingPreview: null,
    pendingFile: null,
    ...overrides,
  }
}

function mapRoomDetailsFromApi(rows = []) {
  return rows.map((row) => emptyRoomDetail({
    localId: `existing-${row.id}`,
    id: row.id,
    title: row.title || '',
    text: row.text || '',
    dim: row.dim || '',
    image_path: row.image_path || null,
  }))
}

const emptyForm = {
  name: '',
  type: 'apartment',
  city: 'Reykjavík',
  short_description: '',
  description: '',
  max_guests: 2,
  bedrooms: 1,
  bathrooms: 1,
  beds: 1,
  min_nights: 1,
  max_nights: '',
  base_price_per_night_euros: '',
  cleaning_fee_euros: 0,
  security_deposit_euros: 0,
  check_in_time: '15:00',
  check_out_time: '11:00',
  cancellation_policy: 'moderate',
  address: '',
  country: 'Iceland',
  latitude: '',
  longitude: '',
  amenity_ids: [],
}

export default function HostGuestHouseEditorPage() {
  const { id } = useParams()
  const isNew = !id || id === 'new'
  const navigate = useNavigate()
  const { toast } = useToast()
  const currency = useHostCurrency()
  const emptySeasonalDraft = useMemo(() => ({
    name: '',
    date_from: '',
    date_to: '',
    price_per_night_euros: currency.exampleAmounts.guesthouseNightly,
    minimum_nights: '',
  }), [currency.exampleAmounts.guesthouseNightly])
  const [step, setStep] = useState(0)
  const [form, setForm] = useState(emptyForm)
  const [status, setStatus] = useState('draft')
  const [rejectionReason, setRejectionReason] = useState('')
  const [amenities, setAmenities] = useState([])
  const [taxRates, setTaxRates] = useState([])
  const [thumbnail, setThumbnail] = useState(null)
  const [gallery, setGallery] = useState([])
  const [pendingMainFile, setPendingMainFile] = useState(null)
  const [pendingMainPreview, setPendingMainPreview] = useState(null)
  const [pendingGallery, setPendingGallery] = useState([])
  const [roomDetails, setRoomDetails] = useState([])
  const [seasonalPrices, setSeasonalPrices] = useState([])
  const [seasonalDraft, setSeasonalDraft] = useState(emptySeasonalDraft)
  const [availability, setAvailability] = useState([])
  const [blockDraft, setBlockDraft] = useState({ blocked_from: '', blocked_to: '', note: '' })
  const [loading, setLoading] = useState(!isNew)
  const [saving, setSaving] = useState(false)
  const [recordId, setRecordId] = useState(isNew ? null : Number(id))
  const [pendingFocusId, setPendingFocusId] = useState(null)
  const { mapsApiKey } = useMapsConfig()

  useEffect(() => {
    setSeasonalDraft(emptySeasonalDraft)
  }, [emptySeasonalDraft])

  const handleLocationChange = (location) => {
    setForm((prev) => ({
      ...prev,
      address: location.address ?? prev.address,
      city: location.city ?? prev.city,
      country: location.country ?? prev.country,
      latitude: location.latitude ?? prev.latitude,
      longitude: location.longitude ?? prev.longitude,
    }))
  }

  useEffect(() => {
    getHostCatalog('amenities').then((res) => setAmenities(res.data.data || []))
    getHostCatalog('tax-rates').then((res) => setTaxRates(res.data.data || []))
  }, [])

  const hydrate = (data) => {
    setForm({
      ...emptyForm,
      ...data,
      max_nights: data.max_nights || '',
      latitude: data.latitude ?? '',
      longitude: data.longitude ?? '',
      amenity_ids: data.amenity_ids || [],
      base_price_per_night_euros: data.base_price_per_night_euros ?? '',
      check_in_time: normalizeTimeString(data.check_in_time || emptyForm.check_in_time),
      check_out_time: normalizeTimeString(data.check_out_time || emptyForm.check_out_time),
    })
    setThumbnail(data.thumbnail || null)
    setGallery(data.images || [])
    setRoomDetails(mapRoomDetailsFromApi(data.room_details))
    setSeasonalPrices(Array.isArray(data.seasonal_prices) ? data.seasonal_prices : [])
  }

  useEffect(() => {
    if (isNew) return
    getHostGuestHouse(id)
      .then((res) => {
        const data = res.data.data
        hydrate(data)
        setStatus(data.status)
        setRejectionReason(data.rejection_reason || '')
        setRecordId(data.id)
        getHostGuestHouseAvailability(data.id).then((r) => setAvailability(r.data.data || []))
      })
      .catch(() => toast('Could not load guesthouse', 'error'))
      .finally(() => setLoading(false))
  }, [id, isNew, toast])

  const reload = (houseId) => {
    getHostGuestHouse(houseId).then((res) => hydrate(res.data.data))
  }

  const flushPendingRoomImages = async (houseId, savedRoomDetails, pendingRows) => {
    if (!pendingRows.length) return

    for (const pending of pendingRows) {
      const match = savedRoomDetails.find((row) => (
        pending.id ? row.id === pending.id : row.title === pending.title
      ))
      if (!match?.id || !pending.file) continue

      const fd = new FormData()
      fd.append('room_detail_id', match.id)
      fd.append('room_detail_image', pending.file)
      await uploadHostGuestHouseImages(houseId, fd)
    }

    pendingRows.forEach((row) => {
      if (row.previewUrl) URL.revokeObjectURL(row.previewUrl)
    })
  }

  const save = async () => {
    setSaving(true)
    try {
      const pendingRoomUploads = roomDetails
        .filter((row) => row.pendingFile)
        .map((row) => ({
          id: row.id,
          title: String(row.title || '').trim(),
          file: row.pendingFile,
          previewUrl: row.pendingPreview,
        }))
      const payload = buildGuestHouseSavePayload(form, seasonalPrices, seasonalDraft, roomDetails)
      if (recordId) {
        const res = await updateHostGuestHouse(recordId, payload)
        const saved = res.data.data
        await flushPendingRoomImages(recordId, saved?.room_details || [], pendingRoomUploads)
        try {
          await flushPendingImages(recordId)
        } catch {
          toast('Saved, but some photos could not upload. Try saving again.', 'error')
          return recordId
        }
        reload(recordId)
        setSeasonalPrices(Array.isArray(saved?.seasonal_prices) ? saved.seasonal_prices : payload.seasonal_prices)
        if (seasonalDraft.name && seasonalDraft.date_from && seasonalDraft.date_to) {
          setSeasonalDraft(emptySeasonalDraft)
        }
        toast('Saved', 'success')
        return recordId
      }

      const res = await createHostGuestHouse({ ...payload, name: form.name || 'New guesthouse' })
      const newId = res.data.data.id
      try {
        await flushPendingImages(newId)
      } catch {
        toast('Guesthouse created, but some photos could not upload. Try saving again.', 'error')
        setRecordId(newId)
        setStatus(res.data.data.status)
        navigate(`/host/guesthouses/${newId}/edit`, { replace: true })
        return newId
      }
      await flushPendingRoomImages(newId, res.data.data?.room_details || [], pendingRoomUploads)
      setRecordId(newId)
      setStatus(res.data.data.status)
      reload(newId)
      setSeasonalPrices(Array.isArray(res.data.data?.seasonal_prices) ? res.data.data.seasonal_prices : payload.seasonal_prices)
      if (seasonalDraft.name && seasonalDraft.date_from && seasonalDraft.date_to) {
        setSeasonalDraft(emptySeasonalDraft)
      }
      toast('Guesthouse created', 'success')
      navigate(`/host/guesthouses/${newId}/edit`, { replace: true })
      return newId
    } catch (err) {
      const validation = err.response?.data?.errors
      const firstError = validation && Object.values(validation).flat()[0]
      toast(firstError || err.response?.data?.message || 'Could not save', 'error')
      return null
    } finally {
      setSaving(false)
    }
  }

  const handleSubmitReview = async () => {
    const houseId = await save()
    if (!houseId) return
    try {
      const res = await submitHostGuestHouse(houseId)
      setStatus(res.data.data.status)
      toast('Submitted for review', 'success')
    } catch (err) {
      toast(err.response?.data?.message || 'Could not submit', 'error')
    }
  }

  const toastInvalidImageFiles = (files) => {
    const names = files.map((file) => file.name).filter(Boolean)
    const suffix = names.length ? ` (${names.join(', ')})` : ''
    toast(`Unsupported photo format${suffix}. ${HOST_IMAGE_FORMAT_HINT}`, 'error')
  }

  const handleMainImage = async (file) => {
    if (!file) return

    if (!recordId) {
      if (pendingMainPreview) URL.revokeObjectURL(pendingMainPreview)
      setPendingMainFile(file)
      setPendingMainPreview(URL.createObjectURL(file))
      return
    }

    const fd = new FormData()
    fd.append('thumbnail', file)
    try {
      await uploadHostGuestHouseImages(recordId, fd)
      if (pendingMainPreview) URL.revokeObjectURL(pendingMainPreview)
      setPendingMainFile(null)
      setPendingMainPreview(null)
      reload(recordId)
      toast('Image uploaded', 'success')
    } catch {
      toast('Upload failed', 'error')
    }
  }

  const clearMainImage = () => {
    if (!pendingMainPreview) return
    URL.revokeObjectURL(pendingMainPreview)
    setPendingMainFile(null)
    setPendingMainPreview(null)
  }

  const handleGalleryImages = async (files) => {
    if (!files.length) return

    if (!recordId) {
      setPendingGallery((prev) => [
        ...prev,
        ...files.map((file) => ({
          id: `${Date.now()}-${file.name}`,
          file,
          previewUrl: URL.createObjectURL(file),
        })),
      ])
      return
    }

    const fd = new FormData()
    files.forEach((file) => fd.append('gallery[]', file))
    try {
      await uploadHostGuestHouseImages(recordId, fd)
      reload(recordId)
      toast('Images uploaded', 'success')
    } catch {
      toast('Upload failed', 'error')
    }
  }

  const removePendingGalleryImage = (id) => {
    setPendingGallery((prev) => {
      const item = prev.find((p) => p.id === id)
      if (item?.previewUrl) URL.revokeObjectURL(item.previewUrl)
      return prev.filter((p) => p.id !== id)
    })
  }

  const flushPendingImages = async (houseId) => {
    let uploaded = false

    if (pendingMainFile) {
      const fd = new FormData()
      fd.append('thumbnail', pendingMainFile)
      await uploadHostGuestHouseImages(houseId, fd)
      if (pendingMainPreview) URL.revokeObjectURL(pendingMainPreview)
      setPendingMainFile(null)
      setPendingMainPreview(null)
      uploaded = true
    }

    if (pendingGallery.length > 0) {
      const fd = new FormData()
      pendingGallery.forEach((item) => fd.append('gallery[]', item.file))
      await uploadHostGuestHouseImages(houseId, fd)
      pendingGallery.forEach((item) => {
        if (item.previewUrl) URL.revokeObjectURL(item.previewUrl)
      })
      setPendingGallery([])
      uploaded = true
    }

    if (uploaded) reload(houseId)
  }

  useEffect(() => () => {
    if (pendingMainPreview) URL.revokeObjectURL(pendingMainPreview)
    pendingGallery.forEach((item) => {
      if (item.previewUrl) URL.revokeObjectURL(item.previewUrl)
    })
  }, [pendingMainPreview, pendingGallery])

  const removeGalleryImage = async (imageId) => {
    if (!recordId) return
    try {
      await deleteHostGuestHouseImage(recordId, imageId)
      setGallery((prev) => prev.filter((img) => img.id !== imageId))
      toast('Image removed', 'success')
    } catch {
      toast('Could not remove image', 'error')
    }
  }

  const updateRoomDetail = (localId, patch) => {
    setRoomDetails((prev) => prev.map((row) => (row.localId === localId ? { ...row, ...patch } : row)))
  }

  const removeRoomDetail = (localId) => {
    setRoomDetails((prev) => {
      const row = prev.find((item) => item.localId === localId)
      if (row?.pendingPreview) URL.revokeObjectURL(row.pendingPreview)
      return prev.filter((item) => item.localId !== localId)
    })
  }

  const clearRoomDetailImage = (localId) => {
    setRoomDetails((prev) => prev.map((row) => {
      if (row.localId !== localId) return row
      if (row.pendingPreview) URL.revokeObjectURL(row.pendingPreview)
      return { ...row, pendingFile: null, pendingPreview: null }
    }))
  }

  const handleRoomDetailImage = async (localId, file) => {
    if (!file) return

    const row = roomDetails.find((item) => item.localId === localId)
    if (!row) return

    if (!recordId || !row.id) {
      setRoomDetails((prev) => prev.map((item) => {
        if (item.localId !== localId) return item
        if (item.pendingPreview) URL.revokeObjectURL(item.pendingPreview)
        return {
          ...item,
          pendingFile: file,
          pendingPreview: URL.createObjectURL(file),
        }
      }))
      if (!recordId) {
        toast('Save the guesthouse first, then save again to upload room photos.', 'info')
      } else {
        toast('Save to attach this room photo.', 'info')
      }
      return
    }

    const fd = new FormData()
    fd.append('room_detail_id', row.id)
    fd.append('room_detail_image', file)
    try {
      await uploadHostGuestHouseImages(recordId, fd)
      reload(recordId)
      toast('Room photo uploaded', 'success')
    } catch {
      toast('Upload failed', 'error')
    }
  }

  useEffect(() => () => {
    roomDetails.forEach((row) => {
      if (row.pendingPreview) URL.revokeObjectURL(row.pendingPreview)
    })
  }, [roomDetails])

  const toggleAmenity = (amenityId, checked) => {
    setForm((prev) => ({
      ...prev,
      amenity_ids: checked ? [...prev.amenity_ids, amenityId] : prev.amenity_ids.filter((x) => x !== amenityId),
    }))
  }

  const readinessItems = [
    { label: 'Listing name', done: !!String(form.name || '').trim(), step: 0, focusId: 'host-gh-name' },
    {
      label: 'Full address, city & country',
      done: String(form.address || '').trim().length >= 5
        && !!String(form.city || '').trim()
        && !!String(form.country || '').trim(),
      step: 0,
      focusId: 'host-gh-address',
    },
    {
      label: 'Main image uploaded',
      done: !!(thumbnail || pendingMainFile),
      step: 0,
      focusId: 'host-gh-main-image',
    },
    {
      label: `At least ${HOST_MIN_DETAIL_IMAGES} detail photos`,
      done: gallery.length + pendingGallery.length >= HOST_MIN_DETAIL_IMAGES,
      step: 0,
      focusId: 'host-gh-detail-images',
    },
    { label: 'Max guests set', done: Number(form.max_guests) > 0, step: 1, focusId: 'host-gh-max-guests' },
    { label: 'Bedrooms set', done: form.bedrooms != null && Number(form.bedrooms) >= 0, step: 1, focusId: 'host-gh-bedrooms' },
    { label: 'Bathrooms set', done: Number(form.bathrooms) > 0, step: 1, focusId: 'host-gh-bathrooms' },
    { label: 'Nightly price above zero', done: Number(form.base_price_per_night_euros) > 0, step: 2, focusId: 'host-gh-nightly-price' },
    { label: 'At least one amenity', done: (form.amenity_ids || []).length > 0, step: 1, focusId: 'host-gh-amenities' },
  ]
  const mainImagePreview = thumbnail ? resolveStorageUrl(thumbnail) : pendingMainPreview
  const isReady = readinessItems.every((i) => i.done)
  const missingReadinessLabels = readinessItems.filter((i) => !i.done).map((i) => i.label)
  const submitDisabledTitle = missingReadinessLabels.length > 0
    ? `Still needed: ${missingReadinessLabels.join(', ')}`
    : undefined

  const goToReadinessItem = (item) => {
    if (item.step != null) setStep(item.step)
    if (item.focusId) setPendingFocusId(item.focusId)
  }

  useEffect(() => {
    if (!pendingFocusId) return undefined
    const timer = window.setTimeout(() => {
      const el = document.getElementById(pendingFocusId)
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' })
        if (typeof el.focus === 'function') {
          el.focus({ preventScroll: true })
        }
      }
      setPendingFocusId(null)
    }, 80)
    return () => window.clearTimeout(timer)
  }, [step, pendingFocusId])

  const findOverlappingGuestBlock = (from, to) => availability.find(
    (block) => block.source === 'manual' && dateRangesOverlap(from, to, block.blocked_from, block.blocked_to),
  )

  const addGuestBlock = async () => {
    const overlap = findOverlappingGuestBlock(blockDraft.blocked_from, blockDraft.blocked_to)
    if (overlap) {
      toast(`This block overlaps an existing block (${formatDate(overlap.blocked_from)} – ${formatDate(overlap.blocked_to)}). Remove or adjust the existing block first.`, 'error')
      return
    }
    try {
      await addHostGuestHouseAvailability(recordId, blockDraft)
      setBlockDraft({ blocked_from: '', blocked_to: '', note: '' })
      getHostGuestHouseAvailability(recordId).then((r) => setAvailability(r.data.data || []))
    } catch (err) {
      toast(err.response?.data?.message || 'Could not add block', 'error')
    }
  }

  if (loading) return <p className="host-muted">Loading…</p>

  return (
    <div className="host-wizard">
      <div className="host-wizard-head">
        <h2>{isNew ? 'New guesthouse' : form.name}</h2>
        <ListingStatusBadge status={status} />
      </div>
      {rejectionReason && <p className="host-alert--error">{rejectionReason}</p>}
      <div className="host-steps">
        {STEPS.map((label, index) => (
          <button key={label} type="button" className={`host-step-pill ${step === index ? 'active' : ''}`} onClick={() => setStep(index)}>
            {index + 1}. {label}
          </button>
        ))}
      </div>
      <div className="host-form-card">
        {step === 0 && (
          <>
            <p className="host-step-note">You can save a draft at any time. Name, address, main image and detail photos are required before submit.</p>
            <div className="host-field" id="host-gh-name"><label>Name</label><input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} /></div>
            <div className="host-field"><label>Type</label>
              <HostSelect
                value={form.type}
                onChange={(v) => setForm({ ...form, type: v })}
                options={['room', 'apartment', 'villa', 'cottage', 'chalet', 'studio'].map((t) => ({ value: t, label: t }))}
                ariaLabel="Property type"
              />
            </div>
            <div id="host-gh-address">
              <AddressAutocomplete
                mapsApiKey={mapsApiKey}
                value={{
                  address: form.address,
                  city: form.city,
                  country: form.country,
                  formattedAddress: form.address ? formatLocationLine(form) : '',
                }}
                onChange={handleLocationChange}
              />
            </div>
            <div className="host-field"><label>Short description</label><textarea rows={3} value={form.short_description} onChange={(e) => setForm({ ...form, short_description: e.target.value })} /></div>
            <div className="host-images-section" id="host-gh-photos">
              {!recordId && (pendingMainPreview || pendingGallery.length > 0) && (
                <p className="host-capacity-hint">Photos are previewed locally and upload when you save the guesthouse.</p>
              )}
              <HostImageDropzone
                fieldId="host-gh-main-image"
                label="Main image"
                hint={`Used on search cards and browse results only, not shown in the listing photo gallery. ${HOST_IMAGE_FORMAT_HINT}`}
                previewSrc={mainImagePreview}
                emptyLabel="Upload main photo"
                onSelect={handleMainImage}
                onInvalid={toastInvalidImageFiles}
                onClear={pendingMainPreview ? clearMainImage : undefined}
              />
              <HostImageGallery
                fieldId="host-gh-detail-images"
                label="Detail images"
                hint={`Shown in the listing page gallery. Add at least ${HOST_MIN_DETAIL_IMAGES} photos of rooms, amenities, and the property exterior so guests can browse your listing. ${HOST_IMAGE_FORMAT_HINT}`}
                minCount={HOST_MIN_DETAIL_IMAGES}
                items={[
                  ...gallery.map((img) => ({
                    key: String(img.id),
                    src: resolveStorageUrl(img.path),
                    onRemove: () => removeGalleryImage(img.id),
                  })),
                  ...pendingGallery.map((item) => ({
                    key: item.id,
                    src: item.previewUrl,
                    onRemove: () => removePendingGalleryImage(item.id),
                  })),
                ]}
                onSelect={handleGalleryImages}
                onInvalid={toastInvalidImageFiles}
              />
            </div>
          </>
        )}
        {step === 1 && (
          <>
            <p className="host-step-note">You can save a draft at any time. Max guests, bedrooms, bathrooms and city are required before submit.</p>
            <div className="host-field"><label>Full description</label><textarea rows={6} value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} /></div>
            <div className="host-form-grid">
              <div className="host-field" id="host-gh-max-guests"><label>Max guests</label><input type="number" min={1} value={form.max_guests} onChange={(e) => setForm({ ...form, max_guests: Number(e.target.value) })} /></div>
              <div className="host-field" id="host-gh-bedrooms"><label>Bedrooms</label><input type="number" min={0} value={form.bedrooms} onChange={(e) => setForm({ ...form, bedrooms: Number(e.target.value) })} /></div>
              <div className="host-field" id="host-gh-bathrooms"><label>Bathrooms</label><input type="number" min={1} value={form.bathrooms} onChange={(e) => setForm({ ...form, bathrooms: Number(e.target.value) })} /></div>
              <div className="host-field"><label>Total beds</label><input type="number" value={form.beds} onChange={(e) => setForm({ ...form, beds: Number(e.target.value) })} /></div>
            </div>
            <div className="host-field" id="host-gh-amenities"><label>Amenities</label>
              <HostIconMultiSelect
                items={amenities}
                selectedIds={form.amenity_ids}
                onToggle={(id) => toggleAmenity(id, !form.amenity_ids.includes(id))}
                placeholder="Search amenities…"
                emptyLabel="No amenities match your search."
              />
            </div>

            <div id="host-gh-room-details" className="host-room-details">
            <HostDisclosure
              title="Room details"
              hint="Add sleeping arrangements and room layout cards with photos. These appear in the Room details section on your listing page."
              count={roomDetails.length}
              defaultOpen={roomDetails.length > 0}
            >
              <p className="host-capacity-hint">
                Add one card per bedroom, bathroom, or shared space. Each card needs a title; add a photo so guests can browse the layout.
                {' '}
                {HOST_IMAGE_FORMAT_HINT}
              </p>
              <div className="host-room-details__list">
                {roomDetails.map((row, index) => {
                  const previewSrc = row.pendingPreview || (row.image_path ? resolveStorageUrl(row.image_path) : null)
                  return (
                    <div key={row.localId} className="host-room-detail-card">
                      <div className="host-room-detail-card__head">
                        <span className="host-room-detail-card__title">Room {index + 1}</span>
                        <button type="button" className="host-btn danger" onClick={() => removeRoomDetail(row.localId)}>Remove</button>
                      </div>
                      <div className="host-room-detail-card__fields">
                        <div className="host-field">
                          <label>Title</label>
                          <input
                            value={row.title}
                            placeholder="e.g. Master bedroom"
                            onChange={(e) => updateRoomDetail(row.localId, { title: e.target.value })}
                          />
                        </div>
                        <div className="host-field">
                          <label>Description</label>
                          <textarea
                            rows={2}
                            value={row.text}
                            placeholder="Describe the bed type, view, or layout."
                            onChange={(e) => updateRoomDetail(row.localId, { text: e.target.value })}
                          />
                        </div>
                        <div className="host-field">
                          <label>Short label</label>
                          <input
                            value={row.dim}
                            placeholder="e.g. Queen bed · Sleeps 2"
                            onChange={(e) => updateRoomDetail(row.localId, { dim: e.target.value })}
                          />
                        </div>
                      </div>
                      <div className="host-room-detail-card__photo">
                        <HostImageDropzone
                          label="Room photo"
                          hint="Landscape photos work best in the room layout preview."
                          previewSrc={previewSrc}
                          emptyLabel="Upload room photo"
                          onSelect={(file) => handleRoomDetailImage(row.localId, file)}
                          onInvalid={toastInvalidImageFiles}
                          onClear={row.pendingPreview ? () => clearRoomDetailImage(row.localId) : undefined}
                        />
                      </div>
                    </div>
                  )
                })}
              </div>
              <button
                type="button"
                className="host-btn-add host-room-details__add"
                onClick={() => setRoomDetails((prev) => [...prev, emptyRoomDetail()])}
              >
                Add room
              </button>
            </HostDisclosure>
            </div>
          </>
        )}
        {step === 2 && (
          <>
            <p className="host-step-note">Set a <strong>nightly price above zero</strong> to publish. Cleaning fee, deposit and seasonal prices are optional.</p>
            <div className="host-form-grid">
              <div className="host-field" id="host-gh-nightly-price"><label>Nightly price ({currency.code})</label><input type="number" placeholder={`e.g. ${currency.exampleAmounts.guesthouseNightly}`} value={form.base_price_per_night_euros} onChange={(e) => setForm({ ...form, base_price_per_night_euros: e.target.value === '' ? '' : Number(e.target.value) })} /></div>
              <div className="host-field"><label>Cleaning fee ({currency.code})</label><input type="number" value={form.cleaning_fee_euros} onChange={(e) => setForm({ ...form, cleaning_fee_euros: Number(e.target.value) })} /></div>
              <div className="host-field"><label>Security deposit ({currency.code})</label><input type="number" value={form.security_deposit_euros} onChange={(e) => setForm({ ...form, security_deposit_euros: Number(e.target.value) })} /></div>
              <div className="host-field"><label>Tax rate</label>
                <HostSelect
                  value={form.tax_rate_id ? String(form.tax_rate_id) : ''}
                  onChange={(v) => setForm({ ...form, tax_rate_id: v ? Number(v) : null })}
                  options={taxRates.map((t) => ({ value: String(t.id), label: t.name }))}
                  placeholder="None"
                  ariaLabel="Tax rate"
                />
              </div>
            </div>

            <HostDisclosure
              title="Seasonal prices (optional)"
              hint="Override the nightly rate for specific date ranges. Fill in the fields below, then click Add or Save."
              count={seasonalPrices.length}
              defaultOpen={seasonalPrices.length > 0}
            >
            <div className="host-form-grid">
              <div className="host-field"><label>Name</label><input value={seasonalDraft.name} onChange={(e) => setSeasonalDraft({ ...seasonalDraft, name: e.target.value })} /></div>
              <div className="host-field"><label>Price / night ({currency.code})</label><input type="number" placeholder={`e.g. ${currency.exampleAmounts.guesthouseNightly}`} value={seasonalDraft.price_per_night_euros} onChange={(e) => setSeasonalDraft({ ...seasonalDraft, price_per_night_euros: Number(e.target.value) })} /></div>
              <div className="host-field"><label>From date</label><HostDatePicker value={seasonalDraft.date_from} onChange={(v) => setSeasonalDraft({ ...seasonalDraft, date_from: v })} /></div>
              <div className="host-field"><label>To date</label><HostDatePicker value={seasonalDraft.date_to} onChange={(v) => setSeasonalDraft({ ...seasonalDraft, date_to: v })} minDate={seasonalDraft.date_from ? new Date(seasonalDraft.date_from) : undefined} /></div>
              <div className="host-field"><label>Minimum nights</label><input type="number" value={seasonalDraft.minimum_nights} onChange={(e) => setSeasonalDraft({ ...seasonalDraft, minimum_nights: e.target.value })} /></div>
            </div>
            <button type="button" className="host-btn-add" disabled={!seasonalDraft.name || !seasonalDraft.date_from || !seasonalDraft.date_to} onClick={() => {
              setSeasonalPrices((prev) => [...prev, { ...seasonalDraft, id: null }])
              setSeasonalDraft(emptySeasonalDraft)
            }}>Add seasonal price</button>
            <ul className="host-stack-list">
              {seasonalPrices.map((sp, index) => (
                <li key={sp.id ?? `new-${index}`} className="host-stack-list__item">
                  <span className="host-stack-list__item-main">{sp.name}: {currency.formatAmount(sp.price_per_night_euros)}/night ({formatDate(sp.date_from)} → {formatDate(sp.date_to)}){sp.minimum_nights ? `, min ${sp.minimum_nights} nights` : ''}</span>
                  <button type="button" className="host-btn danger" onClick={() => setSeasonalPrices((prev) => prev.filter((_, i) => i !== index))}>Remove</button>
                </li>
              ))}
            </ul>
            </HostDisclosure>
          </>
        )}
        {step === 3 && (
          <>
            <div className="host-form-grid">
              <div className="host-field"><label>Check-in</label><HostTimePicker value={form.check_in_time} onChange={(v) => setForm({ ...form, check_in_time: v })} placeholder="Select check-in time" ariaLabel="Check-in time" /></div>
              <div className="host-field"><label>Check-out</label><HostTimePicker value={form.check_out_time} onChange={(v) => setForm({ ...form, check_out_time: v })} placeholder="Select check-out time" ariaLabel="Check-out time" /></div>
              <div className="host-field"><label>Min nights</label><input type="number" value={form.min_nights} onChange={(e) => setForm({ ...form, min_nights: Number(e.target.value) })} /></div>
              <div className="host-field"><label>Max nights</label><input type="number" value={form.max_nights} onChange={(e) => setForm({ ...form, max_nights: e.target.value })} /></div>
            </div>
            <div className="host-field"><label>Cancellation policy</label>
              <HostSelect
                value={form.cancellation_policy}
                onChange={(v) => setForm({ ...form, cancellation_policy: v })}
                options={['flexible', 'moderate', 'strict'].map((p) => ({ value: p, label: p }))}
                ariaLabel="Cancellation policy"
              />
            </div>
          </>
        )}
        {step === 4 && (
          recordId ? (
            <>
              <h3 className="host-section-title">Availability blocks</h3>
              <div className="host-form-grid host-form-grid--3">
                <div className="host-field"><label>From</label><HostDatePicker value={blockDraft.blocked_from} onChange={(v) => setBlockDraft({ ...blockDraft, blocked_from: v })} /></div>
                <div className="host-field"><label>To</label><HostDatePicker value={blockDraft.blocked_to} onChange={(v) => setBlockDraft({ ...blockDraft, blocked_to: v })} minDate={blockDraft.blocked_from ? new Date(blockDraft.blocked_from) : undefined} /></div>
                <div className="host-field"><label>Note</label><input value={blockDraft.note} onChange={(e) => setBlockDraft({ ...blockDraft, note: e.target.value })} /></div>
              </div>
              <button type="button" className="host-btn-add" disabled={!blockDraft.blocked_from || !blockDraft.blocked_to} onClick={addGuestBlock}>Add block</button>
              <ul className="host-stack-list">
                {availability.map((b) => (
                  <li key={b.id} className="host-stack-list__item">
                    <span className="host-stack-list__item-main">{formatDate(b.blocked_from)} → {formatDate(b.blocked_to)} {b.source === 'manual' ? '' : `[${b.source}]`}</span>
                    {b.source === 'manual' && <button type="button" className="host-btn danger" onClick={async () => {
                      await removeHostGuestHouseAvailability(recordId, b.id)
                      getHostGuestHouseAvailability(recordId).then((r) => setAvailability(r.data.data || []))
                    }}>Remove</button>}
                  </li>
                ))}
              </ul>
            </>
          ) : <p className="host-muted">Save the guesthouse first to manage availability.</p>
        )}
        {step === 5 && (
          <div>
            <p className="host-step-note">
              {isReady
                ? 'Everything looks ready. Save your changes, then submit for admin approval.'
                : 'Complete the items below, then save and submit for admin approval.'}
            </p>
            <HostReadinessChecklist items={readinessItems} onGoTo={goToReadinessItem} />
            <ul className="host-review-summary">
              <li><strong>Name:</strong> {form.name || '-'}</li>
              <li><strong>Location:</strong> {formatLocationLine(form) || '-'}</li>
              <li><strong>Price:</strong> {currency.formatAmount(form.base_price_per_night_euros)}/night</li>
            </ul>
          </div>
        )}
        <div className="host-actions">
          {step > 0 && <button type="button" className="host-btn secondary" onClick={() => setStep(step - 1)}>Back</button>}
          {step < STEPS.length - 1 && <button type="button" className="host-btn secondary" onClick={() => setStep(step + 1)}>Next</button>}
          <button type="button" className="host-btn primary" disabled={saving} onClick={save}>{saving ? 'Saving…' : (step === STEPS.length - 1 ? 'Save draft' : 'Save')}</button>
          {recordId && ['draft', 'rejected'].includes(status) && (
            <button
              type="button"
              className="host-btn primary"
              disabled={!isReady}
              title={submitDisabledTitle}
              onClick={handleSubmitReview}
            >
              Submit for review
            </button>
          )}
          <Link to="/host/guesthouses" className="host-btn secondary">Back to list</Link>
        </div>
      </div>
    </div>
  )
}
