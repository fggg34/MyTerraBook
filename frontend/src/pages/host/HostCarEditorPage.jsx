import { useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import {
  addHostCarAvailability,
  addHostCarSpecialPrice,
  createHostCar,
  createHostCarDailyFare,
  createHostCarExtraHourFare,
  createHostCarHourlyFare,
  createHostCarUnit,
  deleteHostCarDailyFare,
  deleteHostCarExtraHourFare,
  deleteHostCarHourlyFare,
  deleteHostCarUnit,
  getHostCar,
  getHostCarAvailability,
  getHostCarDailyFares,
  getHostCarExtraHourFares,
  getHostCarHourlyFares,
  getHostCarSpecialPrices,
  getHostCarUnits,
  getHostCatalog,
  removeHostCarAvailability,
  removeHostCarSpecialPrice,
  resolveStorageUrl,
  submitHostCar,
  syncHostCarRelations,
  updateHostCar,
  uploadHostCarImages,
} from '../../api/host'
import ListingStatusBadge from '../../components/host/ListingStatusBadge'
import { PageLoader } from '../../components/ui/LoadingSpinner'
import { useToast } from '../../context/ToastContext'

const STEPS = ['Vehicle', 'Specs', 'Locations', 'Units', 'Pricing', 'Availability', 'SEO', 'Review']

const emptyForm = {
  name: '',
  category_id: '',
  description: '',
  transmission: 'manual',
  fuel_type: 'diesel',
  units_available: 1,
  ical_import_url: '',
  meta_title: '',
  meta_description: '',
  details_image_paths: [],
  pickup_location_ids: [],
  dropoff_location_ids: [],
  characteristic_ids: [],
  rental_option_ids: [],
}

export default function HostCarEditorPage() {
  const { id } = useParams()
  const isNew = !id || id === 'new'
  const navigate = useNavigate()
  const { toast } = useToast()
  const [step, setStep] = useState(0)
  const [form, setForm] = useState(emptyForm)
  const [status, setStatus] = useState('draft')
  const [rejectionReason, setRejectionReason] = useState('')
  const [mainImage, setMainImage] = useState(null)
  const [ogImage, setOgImage] = useState(null)
  const [catalog, setCatalog] = useState({ categories: [], locations: [], characteristics: [], rentalOptions: [], priceTypes: [] })
  const [units, setUnits] = useState([])
  const [dailyFares, setDailyFares] = useState([])
  const [hourlyFares, setHourlyFares] = useState([])
  const [extraHourFares, setExtraHourFares] = useState([])
  const [availability, setAvailability] = useState([])
  const [specialPrices, setSpecialPrices] = useState([])
  const [fareDraft, setFareDraft] = useState({ price_type_id: '', from_days: 1, to_days: 7, price_per_day_euros: 100 })
  const [hourlyDraft, setHourlyDraft] = useState({ price_type_id: '', min_minutes: 60, max_minutes: 240, total_price_euros: 50 })
  const [extraDraft, setExtraDraft] = useState({ price_type_id: '', charge_per_extra_hour_euros: 10 })
  const [blockDraft, setBlockDraft] = useState({ starts_at: '', ends_at: '', units_blocked: 1, notes: '' })
  const [specialDraft, setSpecialDraft] = useState({ name: '', date_from: '', date_to: '', type: 'charge', value_mode: 'percentage', value_percent_bips: 1000, value_fixed_cents: 0 })
  const [loading, setLoading] = useState(!isNew)
  const [saving, setSaving] = useState(false)
  const [recordId, setRecordId] = useState(isNew ? null : Number(id))

  useEffect(() => {
    Promise.all([
      getHostCatalog('categories'),
      getHostCatalog('locations'),
      getHostCatalog('characteristics'),
      getHostCatalog('rental-options'),
      getHostCatalog('price-types'),
    ]).then(([c, l, ch, ro, pt]) => {
      setCatalog({
        categories: c.data.data || [],
        locations: l.data.data || [],
        characteristics: ch.data.data || [],
        rentalOptions: ro.data.data || [],
        priceTypes: pt.data.data || [],
      })
    })
  }, [])

  const loadPricing = (carId) => {
    getHostCarUnits(carId).then((res) => setUnits(res.data.data || []))
    getHostCarDailyFares(carId).then((res) => setDailyFares(res.data.data || []))
    getHostCarHourlyFares(carId).then((res) => setHourlyFares(res.data.data || []))
    getHostCarExtraHourFares(carId).then((res) => setExtraHourFares(res.data.data || []))
    getHostCarAvailability(carId).then((res) => setAvailability(res.data.data || []))
    getHostCarSpecialPrices(carId).then((res) => setSpecialPrices(res.data.data || []))
  }

  const reloadCar = (carId) => {
    getHostCar(carId).then((res) => {
      const data = res.data.data
      setForm((prev) => ({ ...prev, details_image_paths: data.details_image_paths || [] }))
      setMainImage(data.main_image_path || null)
      setOgImage(data.og_image || null)
    })
  }

  useEffect(() => {
    if (isNew) return
    getHostCar(id)
      .then((res) => {
        const data = res.data.data
        setForm({
          ...emptyForm,
          ...data,
          category_id: data.category_id || '',
          meta_title: data.meta_title || '',
          meta_description: data.meta_description || '',
          details_image_paths: data.details_image_paths || [],
          pickup_location_ids: data.pickup_location_ids || data.location_ids || [],
          dropoff_location_ids: data.dropoff_location_ids || data.location_ids || [],
          characteristic_ids: data.characteristic_ids || [],
          rental_option_ids: data.rental_option_ids || [],
        })
        setMainImage(data.main_image_path || null)
        setOgImage(data.og_image || null)
        setStatus(data.listing_status)
        setRejectionReason(data.rejection_reason || '')
        setRecordId(data.id)
        loadPricing(data.id)
      })
      .catch(() => toast('Could not load vehicle', 'error'))
      .finally(() => setLoading(false))
  }, [id, isNew, toast])

  const save = async () => {
    setSaving(true)
    try {
      const payload = {
        name: form.name,
        category_id: Number(form.category_id),
        description: form.description,
        transmission: form.transmission,
        fuel_type: form.fuel_type,
        units_available: form.units_available,
        ical_import_url: form.ical_import_url,
        meta_title: form.meta_title,
        meta_description: form.meta_description,
      }
      const relations = {
        pickup_location_ids: form.pickup_location_ids,
        dropoff_location_ids: form.dropoff_location_ids,
        characteristic_ids: form.characteristic_ids,
        rental_option_ids: form.rental_option_ids,
      }
      if (recordId) {
        await updateHostCar(recordId, payload)
        await syncHostCarRelations(recordId, relations)
        toast('Saved', 'success')
        return recordId
      }

      const res = await createHostCar({ ...payload, name: form.name || 'New vehicle' })
      const newId = res.data.data.id
      setRecordId(newId)
      setStatus(res.data.data.listing_status)
      await syncHostCarRelations(newId, relations)
      toast('Vehicle created', 'success')
      navigate(`/host/cars/${newId}/edit`, { replace: true })
      return newId
    } catch (err) {
      toast(err.response?.data?.message || 'Could not save', 'error')
      return null
    } finally {
      setSaving(false)
    }
  }

  const handleSubmitReview = async () => {
    let carId = recordId
    if (!carId) {
      carId = await save()
    }
    if (!carId) return
    try {
      const res = await submitHostCar(carId)
      setStatus(res.data.data.listing_status)
      toast('Submitted for review', 'success')
    } catch (err) {
      toast(err.response?.data?.message || 'Could not submit', 'error')
    }
  }

  const toggleId = (key, value) => {
    const list = form[key]
    setForm({
      ...form,
      [key]: list.includes(value) ? list.filter((x) => x !== value) : [...list, value],
    })
  }

  const handleMainImage = async (event) => {
    if (!recordId || !event.target.files?.[0]) return
    const fd = new FormData()
    fd.append('main_image', event.target.files[0])
    try {
      await uploadHostCarImages(recordId, fd)
      reloadCar(recordId)
      toast('Image uploaded', 'success')
    } catch {
      toast('Upload failed', 'error')
    }
  }

  const handleDetailsImages = async (event) => {
    if (!recordId || !event.target.files?.length) return
    const fd = new FormData()
    Array.from(event.target.files).forEach((file) => fd.append('details_images[]', file))
    try {
      await uploadHostCarImages(recordId, fd)
      reloadCar(recordId)
      toast('Images uploaded', 'success')
    } catch {
      toast('Upload failed', 'error')
    }
  }

  const removeDetailsImage = async (path) => {
    if (!recordId) return
    const remaining = form.details_image_paths.filter((p) => p !== path)
    const fd = new FormData()
    if (remaining.length === 0) {
      fd.append('details_image_paths[]', '')
    } else {
      remaining.forEach((p) => fd.append('details_image_paths[]', p))
    }
    try {
      await uploadHostCarImages(recordId, fd)
      setForm((prev) => ({ ...prev, details_image_paths: remaining }))
      toast('Image removed', 'success')
    } catch {
      toast('Could not remove image', 'error')
    }
  }

  const handleOgImage = async (event) => {
    if (!recordId || !event.target.files?.[0]) return
    const fd = new FormData()
    fd.append('og_image', event.target.files[0])
    try {
      await uploadHostCarImages(recordId, fd)
      reloadCar(recordId)
      toast('Share image uploaded', 'success')
    } catch {
      toast('Upload failed', 'error')
    }
  }

  if (loading) return <PageLoader message="Loading editor…" />

  return (
    <div className="host-wizard">
      <div className="mb-4 flex items-center justify-between gap-3">
        <h2 className="text-xl font-bold text-brand-950">{isNew ? 'New vehicle' : form.name}</h2>
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
            <div className="host-field"><label>Category</label>
              <select value={form.category_id} onChange={(e) => setForm({ ...form, category_id: e.target.value })}>
                <option value="">Select category</option>
                {catalog.categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select>
            </div>
            <div className="host-field"><label>Description</label><textarea rows={5} value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} /></div>
            {recordId ? (
              <>
                <div className="host-field"><label>Main image</label>
                  {mainImage && <img src={resolveStorageUrl(mainImage)} alt="Main" className="mb-2 h-24 w-auto rounded-lg object-cover" />}
                  <input type="file" accept="image/*" onChange={handleMainImage} />
                </div>
                <div className="host-field"><label>Detail images</label>
                  <div className="mb-2 flex flex-wrap gap-2">
                    {form.details_image_paths.map((path) => (
                      <div key={path} className="relative">
                        <img src={resolveStorageUrl(path)} alt="Detail" className="h-20 w-28 rounded-lg object-cover" />
                        <button type="button" className="host-btn danger mt-1 w-full" onClick={() => removeDetailsImage(path)}>Remove</button>
                      </div>
                    ))}
                  </div>
                  <input type="file" accept="image/*" multiple onChange={handleDetailsImages} />
                </div>
              </>
            ) : (
              <p className="text-sm text-slate-500">Save the vehicle first to upload images.</p>
            )}
          </>
        )}
        {step === 1 && (
          <>
            <div className="host-field"><label>Transmission</label>
              <select value={form.transmission} onChange={(e) => setForm({ ...form, transmission: e.target.value })}>
                {['manual', 'automatic'].map((v) => <option key={v} value={v}>{v}</option>)}
              </select>
            </div>
            <div className="host-field"><label>Fuel type</label>
              <select value={form.fuel_type} onChange={(e) => setForm({ ...form, fuel_type: e.target.value })}>
                {['petrol', 'diesel', 'electric', 'hybrid'].map((v) => <option key={v} value={v}>{v}</option>)}
              </select>
            </div>
            <div className="host-field"><label>Characteristics</label>
              <div className="grid grid-cols-2 gap-2">
                {catalog.characteristics.map((c) => (
                  <label key={c.id} className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={form.characteristic_ids.includes(c.id)} onChange={() => toggleId('characteristic_ids', c.id)} />
                    {c.name}
                  </label>
                ))}
              </div>
            </div>
            <div className="host-field"><label>Rental options</label>
              <div className="grid grid-cols-2 gap-2">
                {catalog.rentalOptions.map((c) => (
                  <label key={c.id} className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={form.rental_option_ids.includes(c.id)} onChange={() => toggleId('rental_option_ids', c.id)} />
                    {c.name}
                  </label>
                ))}
              </div>
            </div>
          </>
        )}
        {step === 2 && (
          <>
            <div className="host-field"><label>Pickup locations</label>
              <div className="grid grid-cols-2 gap-2">
                {catalog.locations.map((loc) => (
                  <label key={loc.id} className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={form.pickup_location_ids.includes(loc.id)} onChange={() => toggleId('pickup_location_ids', loc.id)} />
                    {loc.name}
                  </label>
                ))}
              </div>
            </div>
            <div className="host-field"><label>Drop-off locations</label>
              <div className="grid grid-cols-2 gap-2">
                {catalog.locations.map((loc) => (
                  <label key={loc.id} className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={form.dropoff_location_ids.includes(loc.id)} onChange={() => toggleId('dropoff_location_ids', loc.id)} />
                    {loc.name}
                  </label>
                ))}
              </div>
            </div>
          </>
        )}
        {step === 3 && (
          recordId ? (
            <>
              <p className="mb-3 text-sm text-slate-600">{units.length} unit(s)</p>
              <button type="button" className="host-btn secondary" onClick={async () => {
                await createHostCarUnit(recordId, {})
                loadPricing(recordId)
              }}>Add unit</button>
              <ul className="mt-3 space-y-2">
                {units.map((u) => (
                  <li key={u.id} className="flex items-center justify-between text-sm">
                    <span>Unit #{u.id} {u.is_active ? '(active)' : '(inactive)'}</span>
                    <button type="button" className="host-btn danger" onClick={async () => { await deleteHostCarUnit(recordId, u.id); loadPricing(recordId) }}>Remove</button>
                  </li>
                ))}
              </ul>
            </>
          ) : <p className="text-sm text-slate-500">Save the vehicle first to manage units.</p>
        )}
        {step === 4 && (
          recordId ? (
            <>
              <h3 className="mb-2 font-semibold text-brand-950">Daily fares</h3>
              <div className="grid grid-cols-2 gap-3">
                <div className="host-field"><label>Price type</label>
                  <select value={fareDraft.price_type_id} onChange={(e) => setFareDraft({ ...fareDraft, price_type_id: e.target.value })}>
                    <option value="">Select</option>
                    {catalog.priceTypes.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
                  </select>
                </div>
                <div className="host-field"><label>From days</label><input type="number" value={fareDraft.from_days} onChange={(e) => setFareDraft({ ...fareDraft, from_days: Number(e.target.value) })} /></div>
                <div className="host-field"><label>To days</label><input type="number" value={fareDraft.to_days} onChange={(e) => setFareDraft({ ...fareDraft, to_days: Number(e.target.value) })} /></div>
                <div className="host-field"><label>€ / day</label><input type="number" value={fareDraft.price_per_day_euros} onChange={(e) => setFareDraft({ ...fareDraft, price_per_day_euros: Number(e.target.value) })} /></div>
              </div>
              <button type="button" className="host-btn secondary" disabled={!fareDraft.price_type_id} onClick={async () => {
                await createHostCarDailyFare(recordId, fareDraft)
                loadPricing(recordId)
              }}>Add daily fare</button>
              <ul className="mt-3 space-y-2 text-sm">
                {dailyFares.map((f) => (
                  <li key={f.id} className="flex justify-between">
                    <span>{f.price_type?.name}: days {f.from_days}-{f.to_days} @ €{(f.price_per_day_cents / 100).toFixed(2)}</span>
                    <button type="button" className="host-btn danger" onClick={async () => { await deleteHostCarDailyFare(recordId, f.id); loadPricing(recordId) }}>Remove</button>
                  </li>
                ))}
              </ul>

              <h3 className="mb-2 mt-6 font-semibold text-brand-950">Hourly fares</h3>
              <div className="grid grid-cols-2 gap-3">
                <div className="host-field"><label>Price type</label>
                  <select value={hourlyDraft.price_type_id} onChange={(e) => setHourlyDraft({ ...hourlyDraft, price_type_id: e.target.value })}>
                    <option value="">Select</option>
                    {catalog.priceTypes.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
                  </select>
                </div>
                <div className="host-field"><label>Min minutes</label><input type="number" value={hourlyDraft.min_minutes} onChange={(e) => setHourlyDraft({ ...hourlyDraft, min_minutes: Number(e.target.value) })} /></div>
                <div className="host-field"><label>Max minutes</label><input type="number" value={hourlyDraft.max_minutes} onChange={(e) => setHourlyDraft({ ...hourlyDraft, max_minutes: Number(e.target.value) })} /></div>
                <div className="host-field"><label>Total € price</label><input type="number" value={hourlyDraft.total_price_euros} onChange={(e) => setHourlyDraft({ ...hourlyDraft, total_price_euros: Number(e.target.value) })} /></div>
              </div>
              <button type="button" className="host-btn secondary" disabled={!hourlyDraft.price_type_id} onClick={async () => {
                await createHostCarHourlyFare(recordId, hourlyDraft)
                loadPricing(recordId)
              }}>Add hourly fare</button>
              <ul className="mt-3 space-y-2 text-sm">
                {hourlyFares.map((f) => (
                  <li key={f.id} className="flex justify-between">
                    <span>{f.price_type?.name}: {f.min_minutes}-{f.max_minutes} min @ €{(f.total_price_cents / 100).toFixed(2)}</span>
                    <button type="button" className="host-btn danger" onClick={async () => { await deleteHostCarHourlyFare(recordId, f.id); loadPricing(recordId) }}>Remove</button>
                  </li>
                ))}
              </ul>

              <h3 className="mb-2 mt-6 font-semibold text-brand-950">Extra-hour fares</h3>
              <div className="grid grid-cols-2 gap-3">
                <div className="host-field"><label>Price type</label>
                  <select value={extraDraft.price_type_id} onChange={(e) => setExtraDraft({ ...extraDraft, price_type_id: e.target.value })}>
                    <option value="">Select</option>
                    {catalog.priceTypes.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
                  </select>
                </div>
                <div className="host-field"><label>€ / extra hour</label><input type="number" value={extraDraft.charge_per_extra_hour_euros} onChange={(e) => setExtraDraft({ ...extraDraft, charge_per_extra_hour_euros: Number(e.target.value) })} /></div>
              </div>
              <button type="button" className="host-btn secondary" disabled={!extraDraft.price_type_id} onClick={async () => {
                await createHostCarExtraHourFare(recordId, extraDraft)
                loadPricing(recordId)
              }}>Add extra-hour fare</button>
              <ul className="mt-3 space-y-2 text-sm">
                {extraHourFares.map((f) => (
                  <li key={f.id} className="flex justify-between">
                    <span>{f.price_type?.name}: €{(f.charge_per_extra_hour_cents / 100).toFixed(2)} / extra hour</span>
                    <button type="button" className="host-btn danger" onClick={async () => { await deleteHostCarExtraHourFare(recordId, f.id); loadPricing(recordId) }}>Remove</button>
                  </li>
                ))}
              </ul>

              <div className="host-field mt-6"><label>iCal import URL</label><input value={form.ical_import_url} onChange={(e) => setForm({ ...form, ical_import_url: e.target.value })} /></div>
            </>
          ) : <p className="text-sm text-slate-500">Save the vehicle first to manage pricing.</p>
        )}
        {step === 5 && (
          recordId ? (
            <>
              <h3 className="mb-2 font-semibold text-brand-950">Availability blocks</h3>
              <div className="grid grid-cols-2 gap-3">
                <div className="host-field"><label>From</label><input type="datetime-local" value={blockDraft.starts_at} onChange={(e) => setBlockDraft({ ...blockDraft, starts_at: e.target.value })} /></div>
                <div className="host-field"><label>To</label><input type="datetime-local" value={blockDraft.ends_at} onChange={(e) => setBlockDraft({ ...blockDraft, ends_at: e.target.value })} /></div>
                <div className="host-field"><label>Units blocked</label><input type="number" min={1} value={blockDraft.units_blocked} onChange={(e) => setBlockDraft({ ...blockDraft, units_blocked: Number(e.target.value) })} /></div>
                <div className="host-field"><label>Notes</label><input value={blockDraft.notes} onChange={(e) => setBlockDraft({ ...blockDraft, notes: e.target.value })} /></div>
              </div>
              <button type="button" className="host-btn secondary" disabled={!blockDraft.starts_at || !blockDraft.ends_at} onClick={async () => {
                try {
                  await addHostCarAvailability(recordId, blockDraft)
                  setBlockDraft({ starts_at: '', ends_at: '', units_blocked: 1, notes: '' })
                  loadPricing(recordId)
                } catch (err) {
                  toast(err.response?.data?.message || 'Could not add block', 'error')
                }
              }}>Add block</button>
              <ul className="mt-3 space-y-2 text-sm">
                {availability.map((b) => (
                  <li key={b.id} className="flex justify-between">
                    <span>{new Date(b.starts_at).toLocaleDateString()} → {new Date(b.ends_at).toLocaleDateString()} ({b.units_blocked} unit) {b.source === 'manual' ? '' : `[${b.source}]`}</span>
                    {b.source === 'manual' && <button type="button" className="host-btn danger" onClick={async () => { await removeHostCarAvailability(recordId, b.id); loadPricing(recordId) }}>Remove</button>}
                  </li>
                ))}
              </ul>

              <h3 className="mb-2 mt-6 font-semibold text-brand-950">Special prices</h3>
              <div className="grid grid-cols-2 gap-3">
                <div className="host-field"><label>Name</label><input value={specialDraft.name} onChange={(e) => setSpecialDraft({ ...specialDraft, name: e.target.value })} /></div>
                <div className="host-field"><label>Type</label>
                  <select value={specialDraft.type} onChange={(e) => setSpecialDraft({ ...specialDraft, type: e.target.value })}>
                    <option value="charge">Charge</option>
                    <option value="discount">Discount</option>
                  </select>
                </div>
                <div className="host-field"><label>From date</label><input type="date" value={specialDraft.date_from} onChange={(e) => setSpecialDraft({ ...specialDraft, date_from: e.target.value })} /></div>
                <div className="host-field"><label>To date</label><input type="date" value={specialDraft.date_to} onChange={(e) => setSpecialDraft({ ...specialDraft, date_to: e.target.value })} /></div>
                <div className="host-field"><label>Value type</label>
                  <select value={specialDraft.value_mode} onChange={(e) => setSpecialDraft({ ...specialDraft, value_mode: e.target.value })}>
                    <option value="percentage">Percentage</option>
                    <option value="fixed">Fixed</option>
                  </select>
                </div>
                {specialDraft.value_mode === 'percentage' ? (
                  <div className="host-field"><label>Percentage (bips, 1000 = 10%)</label><input type="number" min={0} value={specialDraft.value_percent_bips} onChange={(e) => setSpecialDraft({ ...specialDraft, value_percent_bips: Number(e.target.value) })} /></div>
                ) : (
                  <div className="host-field"><label>Fixed (cents)</label><input type="number" min={0} value={specialDraft.value_fixed_cents} onChange={(e) => setSpecialDraft({ ...specialDraft, value_fixed_cents: Number(e.target.value) })} /></div>
                )}
              </div>
              <button type="button" className="host-btn secondary" disabled={!specialDraft.name || !specialDraft.date_from || !specialDraft.date_to} onClick={async () => {
                try {
                  const payload = {
                    name: specialDraft.name,
                    date_from: specialDraft.date_from,
                    date_to: specialDraft.date_to,
                    type: specialDraft.type,
                    value_mode: specialDraft.value_mode,
                    value_percent_bips: specialDraft.value_mode === 'percentage' ? specialDraft.value_percent_bips : null,
                    value_fixed_cents: specialDraft.value_mode === 'fixed' ? specialDraft.value_fixed_cents : null,
                  }
                  await addHostCarSpecialPrice(recordId, payload)
                  setSpecialDraft({ name: '', date_from: '', date_to: '', type: 'charge', value_mode: 'percentage', value_percent_bips: 1000, value_fixed_cents: 0 })
                  loadPricing(recordId)
                } catch (err) {
                  toast(err.response?.data?.message || 'Could not add special price', 'error')
                }
              }}>Add special price</button>
              <ul className="mt-3 space-y-2 text-sm">
                {specialPrices.map((sp) => (
                  <li key={sp.id} className="flex justify-between">
                    <span>{sp.name}: {sp.type} {sp.value_mode === 'percentage' ? `${(sp.value_percent_bips / 100).toFixed(2)}%` : `€${((sp.value_fixed_cents || 0) / 100).toFixed(2)}`} ({sp.date_from} → {sp.date_to})</span>
                    <button type="button" className="host-btn danger" onClick={async () => { await removeHostCarSpecialPrice(recordId, sp.id); loadPricing(recordId) }}>Remove</button>
                  </li>
                ))}
              </ul>
            </>
          ) : <p className="text-sm text-slate-500">Save the vehicle first to manage availability.</p>
        )}
        {step === 6 && (
          <>
            <div className="host-field"><label>Meta title</label><input value={form.meta_title} onChange={(e) => setForm({ ...form, meta_title: e.target.value })} /></div>
            <div className="host-field"><label>Meta description</label><textarea rows={3} value={form.meta_description} onChange={(e) => setForm({ ...form, meta_description: e.target.value })} /></div>
            {recordId ? (
              <div className="host-field"><label>Share image (OG)</label>
                {ogImage && <img src={resolveStorageUrl(ogImage)} alt="Share" className="mb-2 h-24 w-auto rounded-lg object-cover" />}
                <input type="file" accept="image/*" onChange={handleOgImage} />
              </div>
            ) : <p className="text-sm text-slate-500">Save the vehicle first to upload a share image.</p>}
          </>
        )}
        {step === 7 && (
          <p className="text-sm text-slate-600">Save your vehicle and submit for admin approval when ready.</p>
        )}
        <div className="host-actions">
          {step > 0 && <button type="button" className="host-btn secondary" onClick={() => setStep(step - 1)}>Back</button>}
          {step < STEPS.length - 1 && <button type="button" className="host-btn secondary" onClick={() => setStep(step + 1)}>Next</button>}
          <button type="button" className="host-btn primary" disabled={saving} onClick={save}>{saving ? 'Saving…' : 'Save'}</button>
          {recordId && ['draft', 'rejected'].includes(status) && (
            <button type="button" className="host-btn primary" onClick={handleSubmitReview}>Submit for review</button>
          )}
          <Link to="/host/cars" className="host-btn secondary">Back to list</Link>
        </div>
      </div>
    </div>
  )
}
