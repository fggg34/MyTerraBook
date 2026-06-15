import { useEffect, useState } from 'react'
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
import HostSelect from '../../components/host/HostSelect'
import ListingStatusBadge from '../../components/host/ListingStatusBadge'
import { useToast } from '../../context/ToastContext'
import { useMapsConfig } from '../../hooks/useMapsConfig'
import { formatLocationLine } from '../../utils/parseGooglePlace'

const STEPS = ['Basics', 'Details', 'Pricing', 'Rules', 'Availability', 'Review']

const emptyForm = {
  name: '',
  slug: '',
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
  base_price_per_night_euros: 120,
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
  const [step, setStep] = useState(0)
  const [form, setForm] = useState(emptyForm)
  const [status, setStatus] = useState('draft')
  const [rejectionReason, setRejectionReason] = useState('')
  const [amenities, setAmenities] = useState([])
  const [taxRates, setTaxRates] = useState([])
  const [thumbnail, setThumbnail] = useState(null)
  const [gallery, setGallery] = useState([])
  const [seasonalPrices, setSeasonalPrices] = useState([])
  const [seasonalDraft, setSeasonalDraft] = useState({ name: '', date_from: '', date_to: '', price_per_night_euros: 120, minimum_nights: '' })
  const [availability, setAvailability] = useState([])
  const [blockDraft, setBlockDraft] = useState({ blocked_from: '', blocked_to: '', note: '' })
  const [loading, setLoading] = useState(!isNew)
  const [saving, setSaving] = useState(false)
  const [recordId, setRecordId] = useState(isNew ? null : Number(id))
  const { mapsApiKey } = useMapsConfig()

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
      slug: data.slug || '',
      max_nights: data.max_nights || '',
      latitude: data.latitude ?? '',
      longitude: data.longitude ?? '',
      amenity_ids: data.amenity_ids || [],
    })
    setThumbnail(data.thumbnail || null)
    setGallery(data.images || [])
    setSeasonalPrices(data.seasonal_prices || [])
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

  const save = async () => {
    setSaving(true)
    try {
      const payload = {
        ...form,
        slug: form.slug || null,
        max_nights: form.max_nights ? Number(form.max_nights) : null,
        latitude: form.latitude === '' ? null : Number(form.latitude),
        longitude: form.longitude === '' ? null : Number(form.longitude),
        amenity_ids: form.amenity_ids,
        seasonal_prices: seasonalPrices.map((sp) => ({
          id: sp.id,
          name: sp.name,
          date_from: sp.date_from,
          date_to: sp.date_to,
          price_per_night_euros: sp.price_per_night_euros,
          minimum_nights: sp.minimum_nights || null,
        })),
      }
      if (recordId) {
        await updateHostGuestHouse(recordId, payload)
        toast('Saved', 'success')
        reload(recordId)
        return recordId
      }

      const res = await createHostGuestHouse({ ...payload, name: form.name || 'New guesthouse' })
      const newId = res.data.data.id
      setRecordId(newId)
      setStatus(res.data.data.status)
      toast('Guesthouse created', 'success')
      navigate(`/host/guesthouses/${newId}/edit`, { replace: true })
      return newId
    } catch (err) {
      toast(err.response?.data?.message || 'Could not save', 'error')
      return null
    } finally {
      setSaving(false)
    }
  }

  const handleSubmitReview = async () => {
    let houseId = recordId
    if (!houseId) {
      houseId = await save()
    }
    if (!houseId) return
    try {
      const res = await submitHostGuestHouse(houseId)
      setStatus(res.data.data.status)
      toast('Submitted for review', 'success')
    } catch (err) {
      toast(err.response?.data?.message || 'Could not submit', 'error')
    }
  }

  const handleThumbnail = async (event) => {
    if (!recordId || !event.target.files?.[0]) return
    const fd = new FormData()
    fd.append('thumbnail', event.target.files[0])
    try {
      await uploadHostGuestHouseImages(recordId, fd)
      reload(recordId)
      toast('Cover photo uploaded', 'success')
    } catch {
      toast('Upload failed', 'error')
    }
  }

  const handleGallery = async (event) => {
    if (!recordId || !event.target.files?.length) return
    const fd = new FormData()
    Array.from(event.target.files).forEach((file) => fd.append('gallery[]', file))
    try {
      await uploadHostGuestHouseImages(recordId, fd)
      reload(recordId)
      toast('Gallery photos uploaded', 'success')
    } catch {
      toast('Upload failed', 'error')
    }
  }

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

  const toggleAmenity = (amenityId, checked) => {
    setForm((prev) => ({
      ...prev,
      amenity_ids: checked ? [...prev.amenity_ids, amenityId] : prev.amenity_ids.filter((x) => x !== amenityId),
    }))
  }


  return (
    <div className="host-wizard">
      <div className="mb-4 flex items-center justify-between gap-3">
        <h2 className="text-xl font-bold text-brand-950">{isNew ? 'New guesthouse' : form.name}</h2>
        <ListingStatusBadge status={status} />
      </div>
      {rejectionReason && <p className="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{rejectionReason}</p>}
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
            <div className="host-field"><label>Name</label><input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} /></div>
            <div className="host-field"><label>Slug</label><input value={form.slug} placeholder="Auto-generated from name" onChange={(e) => setForm({ ...form, slug: e.target.value })} /></div>
            <div className="host-field"><label>Type</label>
              <HostSelect
                value={form.type}
                onChange={(v) => setForm({ ...form, type: v })}
                options={['room', 'apartment', 'villa', 'cottage', 'chalet', 'studio'].map((t) => ({ value: t, label: t }))}
                ariaLabel="Property type"
              />
            </div>
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
            <div className="host-field"><label>Short description</label><textarea rows={3} value={form.short_description} onChange={(e) => setForm({ ...form, short_description: e.target.value })} /></div>
            {recordId ? (
              <>
                <div className="host-field"><label>Cover photo</label>
                  {thumbnail && <img src={resolveStorageUrl(thumbnail)} alt="Cover" className="mb-2 h-24 w-auto rounded-lg object-cover" />}
                  <input type="file" accept="image/*" onChange={handleThumbnail} />
                </div>
                <div className="host-field"><label>Gallery photos</label>
                  <div className="mb-2 flex flex-wrap gap-2">
                    {gallery.map((img) => (
                      <div key={img.id} className="relative">
                        <img src={resolveStorageUrl(img.path)} alt="Gallery" className="h-20 w-28 rounded-lg object-cover" />
                        <button type="button" className="host-btn danger mt-1 w-full" onClick={() => removeGalleryImage(img.id)}>Remove</button>
                      </div>
                    ))}
                  </div>
                  <input type="file" accept="image/*" multiple onChange={handleGallery} />
                </div>
              </>
            ) : (
              <p className="text-sm text-slate-500">Save the guesthouse first to upload photos.</p>
            )}
          </>
        )}
        {step === 1 && (
          <>
            <div className="host-field"><label>Full description</label><textarea rows={6} value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} /></div>
            <div className="grid grid-cols-2 gap-3">
              <div className="host-field"><label>Max guests</label><input type="number" value={form.max_guests} onChange={(e) => setForm({ ...form, max_guests: Number(e.target.value) })} /></div>
              <div className="host-field"><label>Bedrooms</label><input type="number" value={form.bedrooms} onChange={(e) => setForm({ ...form, bedrooms: Number(e.target.value) })} /></div>
              <div className="host-field"><label>Bathrooms</label><input type="number" value={form.bathrooms} onChange={(e) => setForm({ ...form, bathrooms: Number(e.target.value) })} /></div>
              <div className="host-field"><label>Total beds</label><input type="number" value={form.beds} onChange={(e) => setForm({ ...form, beds: Number(e.target.value) })} /></div>
            </div>
            <div className="host-field"><label>Amenities</label>
              <div className="grid grid-cols-2 gap-2">
                {amenities.map((a) => (
                  <label key={a.id} className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={form.amenity_ids.includes(a.id)} onChange={(e) => toggleAmenity(a.id, e.target.checked)} />
                    {a.name}
                  </label>
                ))}
              </div>
            </div>
          </>
        )}
        {step === 2 && (
          <>
            <div className="grid grid-cols-2 gap-3">
              <div className="host-field"><label>Nightly price (€)</label><input type="number" value={form.base_price_per_night_euros} onChange={(e) => setForm({ ...form, base_price_per_night_euros: Number(e.target.value) })} /></div>
              <div className="host-field"><label>Cleaning fee (€)</label><input type="number" value={form.cleaning_fee_euros} onChange={(e) => setForm({ ...form, cleaning_fee_euros: Number(e.target.value) })} /></div>
              <div className="host-field"><label>Security deposit (€)</label><input type="number" value={form.security_deposit_euros} onChange={(e) => setForm({ ...form, security_deposit_euros: Number(e.target.value) })} /></div>
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

            <h3 className="mb-2 mt-6 font-semibold text-brand-950">Seasonal prices</h3>
            <div className="grid grid-cols-2 gap-3">
              <div className="host-field"><label>Name</label><input value={seasonalDraft.name} onChange={(e) => setSeasonalDraft({ ...seasonalDraft, name: e.target.value })} /></div>
              <div className="host-field"><label>Price / night (€)</label><input type="number" value={seasonalDraft.price_per_night_euros} onChange={(e) => setSeasonalDraft({ ...seasonalDraft, price_per_night_euros: Number(e.target.value) })} /></div>
              <div className="host-field"><label>From date</label><HostDatePicker value={seasonalDraft.date_from} onChange={(v) => setSeasonalDraft({ ...seasonalDraft, date_from: v })} /></div>
              <div className="host-field"><label>To date</label><HostDatePicker value={seasonalDraft.date_to} onChange={(v) => setSeasonalDraft({ ...seasonalDraft, date_to: v })} minDate={seasonalDraft.date_from ? new Date(seasonalDraft.date_from) : undefined} /></div>
              <div className="host-field"><label>Minimum nights</label><input type="number" value={seasonalDraft.minimum_nights} onChange={(e) => setSeasonalDraft({ ...seasonalDraft, minimum_nights: e.target.value })} /></div>
            </div>
            <button type="button" className="host-btn secondary" disabled={!seasonalDraft.name || !seasonalDraft.date_from || !seasonalDraft.date_to} onClick={() => {
              setSeasonalPrices((prev) => [...prev, { ...seasonalDraft, id: null }])
              setSeasonalDraft({ name: '', date_from: '', date_to: '', price_per_night_euros: 120, minimum_nights: '' })
            }}>Add seasonal price</button>
            <ul className="mt-3 space-y-2 text-sm">
              {seasonalPrices.map((sp, index) => (
                <li key={sp.id ?? `new-${index}`} className="flex justify-between">
                  <span>{sp.name}: €{sp.price_per_night_euros}/night ({sp.date_from} → {sp.date_to}){sp.minimum_nights ? `, min ${sp.minimum_nights} nights` : ''}</span>
                  <button type="button" className="host-btn danger" onClick={() => setSeasonalPrices((prev) => prev.filter((_, i) => i !== index))}>Remove</button>
                </li>
              ))}
            </ul>
            <p className="mt-2 text-xs text-slate-500">Seasonal prices are saved when you click Save.</p>
          </>
        )}
        {step === 3 && (
          <>
            <div className="grid grid-cols-2 gap-3">
              <div className="host-field"><label>Check-in</label><input type="time" value={form.check_in_time} onChange={(e) => setForm({ ...form, check_in_time: e.target.value })} /></div>
              <div className="host-field"><label>Check-out</label><input type="time" value={form.check_out_time} onChange={(e) => setForm({ ...form, check_out_time: e.target.value })} /></div>
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
              <h3 className="mb-2 font-semibold text-brand-950">Availability blocks</h3>
              <div className="grid grid-cols-3 gap-3">
                <div className="host-field"><label>From</label><HostDatePicker value={blockDraft.blocked_from} onChange={(v) => setBlockDraft({ ...blockDraft, blocked_from: v })} /></div>
                <div className="host-field"><label>To</label><HostDatePicker value={blockDraft.blocked_to} onChange={(v) => setBlockDraft({ ...blockDraft, blocked_to: v })} minDate={blockDraft.blocked_from ? new Date(blockDraft.blocked_from) : undefined} /></div>
                <div className="host-field"><label>Note</label><input value={blockDraft.note} onChange={(e) => setBlockDraft({ ...blockDraft, note: e.target.value })} /></div>
              </div>
              <button type="button" className="host-btn secondary" disabled={!blockDraft.blocked_from || !blockDraft.blocked_to} onClick={async () => {
                try {
                  await addHostGuestHouseAvailability(recordId, blockDraft)
                  setBlockDraft({ blocked_from: '', blocked_to: '', note: '' })
                  getHostGuestHouseAvailability(recordId).then((r) => setAvailability(r.data.data || []))
                } catch (err) {
                  toast(err.response?.data?.message || 'Could not add block', 'error')
                }
              }}>Add block</button>
              <ul className="mt-3 space-y-2 text-sm">
                {availability.map((b) => (
                  <li key={b.id} className="flex justify-between">
                    <span>{b.blocked_from} → {b.blocked_to} {b.source === 'manual' ? '' : `[${b.source}]`}</span>
                    {b.source === 'manual' && <button type="button" className="host-btn danger" onClick={async () => {
                      await removeHostGuestHouseAvailability(recordId, b.id)
                      getHostGuestHouseAvailability(recordId).then((r) => setAvailability(r.data.data || []))
                    }}>Remove</button>}
                  </li>
                ))}
              </ul>
            </>
          ) : <p className="text-sm text-slate-500">Save the guesthouse first to manage availability.</p>
        )}
        {step === 5 && (
          <div>
            <p className="text-sm text-slate-600">Review your listing, save changes, then submit for admin approval.</p>
            <ul className="mt-4 space-y-2 text-sm">
              <li><strong>Name:</strong> {form.name}</li>
              <li><strong>Location:</strong> {formatLocationLine(form) || '-'}</li>
              <li><strong>Price:</strong> €{form.base_price_per_night_euros}/night</li>
            </ul>
          </div>
        )}
        <div className="host-actions">
          {step > 0 && <button type="button" className="host-btn secondary" onClick={() => setStep(step - 1)}>Back</button>}
          {step < STEPS.length - 1 && <button type="button" className="host-btn secondary" onClick={() => setStep(step + 1)}>Next</button>}
          <button type="button" className="host-btn primary" disabled={saving} onClick={save}>{saving ? 'Saving…' : 'Save'}</button>
          {recordId && ['draft', 'rejected'].includes(status) && (
            <button type="button" className="host-btn primary" onClick={handleSubmitReview}>Submit for review</button>
          )}
          <Link to="/host/guesthouses" className="host-btn secondary">Back to list</Link>
        </div>
      </div>
    </div>
  )
}
