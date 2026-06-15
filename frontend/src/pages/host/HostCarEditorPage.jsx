import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { AlertCircle, ArrowRight, CalendarDays, Clock, Plus, Timer, Trash2 } from 'lucide-react'
import {
  addHostCarAvailability,
  addHostCarSpecialPrice,
  createHostCar,
  createHostCarDailyFare,
  createHostCarExtraHourFare,
  createHostCarHourlyFare,
  createHostCarLocationFee,
  createHostCarOutOfHoursFee,
  createHostCarUnit,
  deleteHostCarDailyFare,
  deleteHostCarExtraHourFare,
  deleteHostCarHourlyFare,
  deleteHostCarLocationFee,
  deleteHostCarOutOfHoursFee,
  deleteHostCarUnit,
  getHostCar,
  getHostCarAvailability,
  getHostCarDailyFares,
  getHostCarExtraHourFares,
  getHostCarHourlyFares,
  getHostCarLocationFees,
  getHostCarOutOfHoursFees,
  getHostCarSpecialPrices,
  getHostCarUnits,
  getHostCatalog,
  getPublicMainCategories,
  getPublicSubCategories,
  removeHostCarAvailability,
  removeHostCarSpecialPrice,
  resolveStorageUrl,
  submitHostCar,
  syncHostCarRelations,
  updateHostCar,
  uploadHostCarImages,
} from '../../api/host'
import HostCarLocationsStep from '../../components/host/HostCarLocationsStep'
import HostDatePicker from '../../components/host/HostDatePicker'
import HostDateTimePicker from '../../components/host/HostDateTimePicker'
import HostSelect from '../../components/host/HostSelect'
import ListingStatusBadge from '../../components/host/ListingStatusBadge'
import { useToast } from '../../context/ToastContext'

function timeToMinutes(time) {
  if (!time) return null
  const [h, m] = String(time).split(':').map(Number)
  if (Number.isNaN(h) || Number.isNaN(m)) return null
  return h * 60 + m
}

function validateCarTimes(form) {
  const hasPickup = form.pickup_location_ids.length > 0
  const hasDropoff = form.dropoff_location_ids.length > 0
  if (!hasPickup || !hasDropoff) return null

  const fields = ['pickup_time_from', 'pickup_time_to', 'dropoff_time_from', 'dropoff_time_to']
  if (fields.some((field) => !form[field])) {
    return 'Set pickup and drop-off times for all four fields.'
  }

  if (timeToMinutes(form.pickup_time_from) >= timeToMinutes(form.pickup_time_to)) {
    return 'Pickup end time must be after the start time.'
  }

  if (timeToMinutes(form.dropoff_time_from) >= timeToMinutes(form.dropoff_time_to)) {
    return 'Drop-off end time must be after the start time.'
  }

  return null
}

const STEPS = ['Vehicle', 'Specs', 'Locations', 'Units', 'Pricing', 'Availability', 'Review']

const emptyForm = {
  name: '',
  main_category_id: '',
  sub_category_id: '',
  description: '',
  transmission: 'manual',
  fuel_type: 'diesel',
  seats: 5,
  sleeps: 0,
  bags: 2,
  year: '',
  units_available: 1,
  details_image_paths: [],
  pickup_location_ids: [],
  dropoff_location_ids: [],
  pickup_time_from: '',
  pickup_time_to: '',
  dropoff_time_from: '',
  dropoff_time_to: '',
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
  const [catalog, setCatalog] = useState({
    mainCategories: [],
    subCategories: [],
    locations: [],
    characteristics: [],
    rentalOptions: [],
    priceTypes: [],
  })
  const [units, setUnits] = useState([])
  const [dailyFares, setDailyFares] = useState([])
  const [hourlyFares, setHourlyFares] = useState([])
  const [extraHourFares, setExtraHourFares] = useState([])
  const [availability, setAvailability] = useState([])
  const [specialPrices, setSpecialPrices] = useState([])
  const [locationFees, setLocationFees] = useState([])
  const [outOfHoursFees, setOutOfHoursFees] = useState([])
  const [locationFeeDraft, setLocationFeeDraft] = useState({
    pickup_location_id: '',
    dropoff_location_id: '',
    cost_euros: 0,
    is_one_way_fee: false,
  })
  const [oohFeeDraft, setOohFeeDraft] = useState({
    name: 'Out-of-hours',
    time_from: '20:00',
    time_to: '08:00',
    applies_to: 'both',
    pickup_cost_euros: 35,
    dropoff_cost_euros: 35,
    location_ids: [],
  })
  const [fareDraft, setFareDraft] = useState({ price_type_id: '', from_days: 1, to_days: 7, price_per_day_euros: 100 })
  const [hourlyDraft, setHourlyDraft] = useState({ price_type_id: '', min_minutes: 60, max_minutes: 240, total_price_euros: 50 })
  const [extraDraft, setExtraDraft] = useState({ price_type_id: '', charge_per_extra_hour_euros: 10 })
  const [blockDraft, setBlockDraft] = useState({ starts_at: '', ends_at: '', units_blocked: 1, notes: '' })
  const [specialDraft, setSpecialDraft] = useState({ name: '', date_from: '', date_to: '', type: 'charge', value_mode: 'percentage', value_percent_bips: 1000, value_fixed_cents: 0 })
  const [loading, setLoading] = useState(!isNew)
  const [saving, setSaving] = useState(false)
  const [recordId, setRecordId] = useState(isNew ? null : Number(id))

  useEffect(() => {
    const unwrap = (result, fallback = []) => (
      result.status === 'fulfilled' ? (result.value.data?.data || []) : fallback
    )

    const loadMainCategories = () => getHostCatalog('main-categories').catch(() => getPublicMainCategories())
    const loadSubCategories = () => getHostCatalog('categories').catch(() => getPublicSubCategories())

    Promise.allSettled([
      loadMainCategories(),
      loadSubCategories(),
      getHostCatalog('locations'),
      getHostCatalog('characteristics'),
      getHostCatalog('rental-options'),
      getHostCatalog('price-types'),
    ]).then(([mc, sc, l, ch, ro, pt]) => {
      const mainCategories = unwrap(mc)
      const subCategories = unwrap(sc)

      setCatalog({
        mainCategories,
        subCategories,
        locations: unwrap(l),
        characteristics: unwrap(ch),
        rentalOptions: unwrap(ro),
        priceTypes: unwrap(pt),
      })

      if (mainCategories.length === 0) {
        toast('Could not load vehicle categories. Please refresh or contact support.', 'error')
      }
    })
  }, [toast])

  const loadPricing = (carId) => {
    getHostCarUnits(carId).then((res) => setUnits(res.data.data || []))
    getHostCarDailyFares(carId).then((res) => setDailyFares(res.data.data || []))
    getHostCarHourlyFares(carId).then((res) => setHourlyFares(res.data.data || []))
    getHostCarExtraHourFares(carId).then((res) => setExtraHourFares(res.data.data || []))
    getHostCarAvailability(carId).then((res) => setAvailability(res.data.data || []))
    getHostCarSpecialPrices(carId).then((res) => setSpecialPrices(res.data.data || []))
    getHostCarLocationFees(carId).then((res) => setLocationFees(res.data.data || []))
    getHostCarOutOfHoursFees(carId).then((res) => setOutOfHoursFees(res.data.data || []))
  }

  const reloadCar = (carId) => {
    getHostCar(carId).then((res) => {
      const data = res.data.data
      setForm((prev) => ({ ...prev, details_image_paths: data.details_image_paths || [] }))
      setMainImage(data.main_image_path || null)
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
          main_category_id: data.main_category_id || data.sub_category?.main_category_id || '',
          sub_category_id: data.sub_category_id || data.category_id || '',
          details_image_paths: data.details_image_paths || [],
          pickup_location_ids: data.pickup_location_ids || data.location_ids || [],
          dropoff_location_ids: data.dropoff_location_ids || data.location_ids || [],
          pickup_time_from: data.pickup_time_from || '',
          pickup_time_to: data.pickup_time_to || '',
          dropoff_time_from: data.dropoff_time_from || '',
          dropoff_time_to: data.dropoff_time_to || '',
          seats: data.seats ?? 5,
          sleeps: data.sleeps ?? 0,
          bags: data.bags ?? 2,
          year: data.year ?? '',
          characteristic_ids: data.characteristic_ids || [],
          rental_option_ids: data.rental_option_ids || [],
        })
        setMainImage(data.main_image_path || null)
        setStatus(data.listing_status)
        setRejectionReason(data.rejection_reason || '')
        setRecordId(data.id)
        loadPricing(data.id)
      })
      .catch(() => toast('Could not load vehicle', 'error'))
      .finally(() => setLoading(false))
  }, [id, isNew, toast])

  const save = async () => {
    const timeError = validateCarTimes(form)
    if (timeError) {
      toast(timeError, 'error')
      return null
    }

    setSaving(true)
    try {
      const payload = {
        name: form.name,
        sub_category_id: Number(form.sub_category_id),
        description: form.description,
        transmission: form.transmission,
        fuel_type: form.fuel_type,
        seats: form.seats,
        sleeps: form.sleeps,
        bags: form.bags,
        year: form.year ? Number(form.year) : null,
        units_available: form.units_available,
        pickup_time_from: form.pickup_time_from || null,
        pickup_time_to: form.pickup_time_to || null,
        dropoff_time_from: form.dropoff_time_from || null,
        dropoff_time_to: form.dropoff_time_to || null,
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
      const validation = err.response?.data?.errors
      const firstError = validation && Object.values(validation).flat()[0]
      toast(firstError || err.response?.data?.message || 'Could not save', 'error')
      return null
    } finally {
      setSaving(false)
    }
  }

  const handleSubmitReview = async () => {
    const timeError = validateCarTimes(form)
    if (timeError) {
      toast(timeError, 'error')
      return
    }

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

  const hasId = (list, id) => list.some((x) => String(x) === String(id))

  const toggleId = (key, value) => {
    const list = form[key]
    setForm({
      ...form,
      [key]: hasId(list, value)
        ? list.filter((x) => String(x) !== String(value))
        : [...list, value],
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

  const filteredSubCategories = catalog.subCategories.filter(
    (sub) => !form.main_category_id || String(sub.main_category_id) === String(form.main_category_id),
  )

  const selectedMainCategory = useMemo(
    () => catalog.mainCategories.find((c) => String(c.id) === String(form.main_category_id)),
    [catalog.mainCategories, form.main_category_id],
  )

  const isCampervan = selectedMainCategory?.slug === 'campervan'

  const yearOptions = useMemo(
    () => Array.from({ length: 2026 - 1990 + 1 }, (_, i) => {
      const year = 2026 - i
      return { value: String(year), label: String(year) }
    }),
    [],
  )

  const setCapacity = (key, rawValue, max) => {
    const parsed = parseInt(rawValue, 10)
    const next = Number.isNaN(parsed) ? 0 : Math.max(0, Math.min(parsed, max))
    setForm((prev) => ({ ...prev, [key]: next }))
  }

  const selectedPickupLocations = useMemo(
    () => catalog.locations.filter((loc) => hasId(form.pickup_location_ids, loc.id)),
    [catalog.locations, form.pickup_location_ids],
  )

  const selectedDropoffLocations = useMemo(
    () => catalog.locations.filter((loc) => hasId(form.dropoff_location_ids, loc.id)),
    [catalog.locations, form.dropoff_location_ids],
  )

  const canConfigureTimes = selectedPickupLocations.length > 0 && selectedDropoffLocations.length > 0


  const dailyRangeInvalid = Number(fareDraft.to_days) <= Number(fareDraft.from_days)
  const hourlyRangeInvalid = Number(hourlyDraft.max_minutes) <= Number(hourlyDraft.min_minutes)

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
            <div className="host-field"><label>Main category</label>
              <HostSelect
                value={String(form.main_category_id || '')}
                onChange={(v) => {
                  const main = catalog.mainCategories.find((c) => String(c.id) === v)
                  const next = { ...form, main_category_id: v, sub_category_id: '' }
                  if (main?.slug === 'campervan' && (!form.sleeps || form.sleeps === 0)) {
                    next.sleeps = form.seats || 2
                  }
                  setForm(next)
                }}
                options={catalog.mainCategories.map((c) => ({ value: String(c.id), label: c.name }))}
                placeholder="Select main category"
                ariaLabel="Main category"
              />
            </div>
            <div className="host-field"><label>Sub category</label>
              <HostSelect
                value={String(form.sub_category_id || '')}
                disabled={!form.main_category_id}
                onChange={(v) => setForm({ ...form, sub_category_id: v })}
                options={filteredSubCategories.map((c) => ({ value: String(c.id), label: c.name }))}
                placeholder="Select sub category"
                ariaLabel="Sub category"
              />
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
              <HostSelect
                value={form.transmission}
                onChange={(v) => setForm({ ...form, transmission: v })}
                options={['manual', 'automatic'].map((v) => ({ value: v, label: v }))}
                ariaLabel="Transmission"
              />
            </div>
            <div className="host-field"><label>Fuel type</label>
              <HostSelect
                value={form.fuel_type}
                onChange={(v) => setForm({ ...form, fuel_type: v })}
                options={['petrol', 'diesel', 'electric', 'hybrid'].map((v) => ({ value: v, label: v }))}
                ariaLabel="Fuel type"
              />
            </div>
            <div className="host-field">
              <label>Capacity</label>
              <div className="host-capacity-grid">
                <div>
                  <label className="host-capacity-label" htmlFor="car-seats">Seats</label>
                  <input
                    id="car-seats"
                    type="number"
                    min={0}
                    max={50}
                    value={form.seats}
                    onChange={(e) => setCapacity('seats', e.target.value, 50)}
                  />
                </div>
                <div>
                  <label className="host-capacity-label" htmlFor="car-sleeps">
                    {isCampervan ? 'Sleeps (berths)' : 'Sleeps'}
                  </label>
                  <input
                    id="car-sleeps"
                    type="number"
                    min={0}
                    max={20}
                    value={form.sleeps}
                    onChange={(e) => setCapacity('sleeps', e.target.value, 20)}
                  />
                </div>
                <div>
                  <label className="host-capacity-label" htmlFor="car-bags">Bags</label>
                  <input
                    id="car-bags"
                    type="number"
                    min={0}
                    max={50}
                    value={form.bags}
                    onChange={(e) => setCapacity('bags', e.target.value, 50)}
                  />
                </div>
              </div>
              {isCampervan && (
                <p className="host-capacity-hint">Sleeps is how many people can stay overnight in the campervan.</p>
              )}
            </div>
            <div className="host-field"><label>Year</label>
              <HostSelect
                value={String(form.year || '')}
                onChange={(v) => setForm({ ...form, year: v })}
                options={yearOptions}
                placeholder="Select a year"
                searchable
                ariaLabel="Year"
              />
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
          <HostCarLocationsStep
            catalogLocations={catalog.locations}
            form={form}
            setForm={setForm}
            selectedPickupLocations={selectedPickupLocations}
            selectedDropoffLocations={selectedDropoffLocations}
            canConfigureTimes={canConfigureTimes}
            recordId={recordId}
            locationFees={locationFees}
            outOfHoursFees={outOfHoursFees}
            locationFeeDraft={locationFeeDraft}
            setLocationFeeDraft={setLocationFeeDraft}
            oohFeeDraft={oohFeeDraft}
            setOohFeeDraft={setOohFeeDraft}
            onAddLocationFee={async () => {
              try {
                await createHostCarLocationFee(recordId, {
                  ...locationFeeDraft,
                  multiply_by_days: false,
                })
                getHostCarLocationFees(recordId).then((res) => setLocationFees(res.data.data || []))
                setLocationFeeDraft({
                  pickup_location_id: '',
                  dropoff_location_id: '',
                  cost_euros: 0,
                  is_one_way_fee: false,
                })
                toast('Fee added', 'success')
              } catch (err) {
                toast(err.response?.data?.message || 'Could not add fee', 'error')
              }
            }}
            onDeleteLocationFee={async (feeId) => {
              await deleteHostCarLocationFee(recordId, feeId)
              setLocationFees((prev) => prev.filter((f) => f.id !== feeId))
            }}
            onAddOohFee={async () => {
              try {
                await createHostCarOutOfHoursFee(recordId, oohFeeDraft)
                getHostCarOutOfHoursFees(recordId).then((res) => setOutOfHoursFees(res.data.data || []))
                setOohFeeDraft({
                  name: 'Out-of-hours',
                  time_from: '20:00',
                  time_to: '08:00',
                  applies_to: 'both',
                  pickup_cost_euros: 35,
                  dropoff_cost_euros: 35,
                  location_ids: [],
                })
                toast('Out-of-hours fee added', 'success')
              } catch (err) {
                toast(err.response?.data?.message || 'Could not add fee', 'error')
              }
            }}
            onDeleteOohFee={async (feeId) => {
              await deleteHostCarOutOfHoursFee(recordId, feeId)
              setOutOfHoursFees((prev) => prev.filter((f) => f.id !== feeId))
            }}
          />
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
            <div className="host-pricing">
              {/* Daily fares */}
              <section className="host-fare-section">
                <div className="host-fare-head">
                  <span className="host-fare-head-icon"><CalendarDays size={18} /></span>
                  <div className="host-fare-head-text">
                    <h3>Daily fares</h3>
                    <p>Set a price per day based on how long the guest rents. You can add multiple tiers — e.g. a lower rate for longer bookings.</p>
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-3">
                  <div className="host-field"><label>Price type</label>
                    <HostSelect
                      value={String(fareDraft.price_type_id || '')}
                      onChange={(v) => setFareDraft({ ...fareDraft, price_type_id: v })}
                      options={catalog.priceTypes.map((p) => ({ value: String(p.id), label: p.name }))}
                      placeholder="Select"
                      ariaLabel="Daily fare price type"
                    />
                  </div>
                  <div className="host-field"><label>€ / day</label><input type="number" min={0} value={fareDraft.price_per_day_euros} onChange={(e) => setFareDraft({ ...fareDraft, price_per_day_euros: Number(e.target.value) })} /></div>
                  <div className="host-field"><label>From days</label><input type="number" min={1} className={dailyRangeInvalid ? 'has-error' : ''} value={fareDraft.from_days} onChange={(e) => setFareDraft({ ...fareDraft, from_days: Number(e.target.value) })} /></div>
                  <div className="host-field"><label>To days</label><input type="number" min={1} className={dailyRangeInvalid ? 'has-error' : ''} value={fareDraft.to_days} onChange={(e) => setFareDraft({ ...fareDraft, to_days: Number(e.target.value) })} /></div>
                </div>
                {dailyRangeInvalid && (
                  <p className="host-field-error"><AlertCircle size={14} /> “From days” must be lower than “To days”.</p>
                )}
                <div className="host-fare-preview">
                  <span className="host-fare-preview-label">Preview</span>
                  <span className="host-fare-tag is-preview">
                    <CalendarDays size={14} className="host-fare-tag-icon" />
                    <span className="host-fare-tag-text">Days {fareDraft.from_days}–{fareDraft.to_days} <ArrowRight size={12} /> <strong>€{Number(fareDraft.price_per_day_euros || 0).toFixed(0)}/day</strong></span>
                  </span>
                </div>
                <span className="host-tooltip-wrap" data-tooltip={!fareDraft.price_type_id ? 'Select a price type to add a fare' : undefined}>
                  <button type="button" className="host-btn-add" disabled={!fareDraft.price_type_id || dailyRangeInvalid} onClick={async () => {
                    await createHostCarDailyFare(recordId, fareDraft)
                    loadPricing(recordId)
                  }}><Plus size={16} /> Add daily fare</button>
                </span>
                {dailyFares.length > 0 && (
                  <ul className="host-fare-list">
                    {dailyFares.map((f) => (
                      <li key={f.id} className="host-fare-tag">
                        <CalendarDays size={14} className="host-fare-tag-icon" />
                        <span className="host-fare-tag-text">
                          {f.price_type?.name && <em>{f.price_type.name}</em>}
                          Days {f.from_days}–{f.to_days} <ArrowRight size={12} /> <strong>€{(f.price_per_day_cents / 100).toFixed(2)}/day</strong>
                        </span>
                        <button type="button" className="host-fare-tag-remove" aria-label="Remove fare" title="Remove fare" onClick={async () => { await deleteHostCarDailyFare(recordId, f.id); loadPricing(recordId) }}><Trash2 size={14} /></button>
                      </li>
                    ))}
                  </ul>
                )}
              </section>

              {/* Hourly fares */}
              <section className="host-fare-section">
                <div className="host-fare-head">
                  <span className="host-fare-head-icon"><Clock size={18} /></span>
                  <div className="host-fare-head-text">
                    <h3>Hourly fares</h3>
                    <p>Set a flat price for short rentals within a duration window. Guests pay this when the trip lasts between the minimum and maximum.</p>
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-3">
                  <div className="host-field"><label>Price type</label>
                    <HostSelect
                      value={String(hourlyDraft.price_type_id || '')}
                      onChange={(v) => setHourlyDraft({ ...hourlyDraft, price_type_id: v })}
                      options={catalog.priceTypes.map((p) => ({ value: String(p.id), label: p.name }))}
                      placeholder="Select"
                      ariaLabel="Hourly fare price type"
                    />
                  </div>
                  <div className="host-field"><label>Total € price</label><input type="number" min={0} value={hourlyDraft.total_price_euros} onChange={(e) => setHourlyDraft({ ...hourlyDraft, total_price_euros: Number(e.target.value) })} /></div>
                  <div className="host-field"><label>Minimum rental duration <span className="host-field-note">(e.g. 60 min = 1 hour)</span></label><input type="number" min={0} className={hourlyRangeInvalid ? 'has-error' : ''} value={hourlyDraft.min_minutes} onChange={(e) => setHourlyDraft({ ...hourlyDraft, min_minutes: Number(e.target.value) })} /></div>
                  <div className="host-field"><label>Maximum rental duration <span className="host-field-note">(e.g. 240 min = 4 hours)</span></label><input type="number" min={0} className={hourlyRangeInvalid ? 'has-error' : ''} value={hourlyDraft.max_minutes} onChange={(e) => setHourlyDraft({ ...hourlyDraft, max_minutes: Number(e.target.value) })} /></div>
                </div>
                {hourlyRangeInvalid && (
                  <p className="host-field-error"><AlertCircle size={14} /> Minimum duration must be lower than the maximum.</p>
                )}
                <div className="host-fare-preview">
                  <span className="host-fare-preview-label">Preview</span>
                  <span className="host-fare-tag is-preview">
                    <Clock size={14} className="host-fare-tag-icon" />
                    <span className="host-fare-tag-text">{hourlyDraft.min_minutes}–{hourlyDraft.max_minutes} min <ArrowRight size={12} /> <strong>€{Number(hourlyDraft.total_price_euros || 0).toFixed(0)}</strong></span>
                  </span>
                </div>
                <span className="host-tooltip-wrap" data-tooltip={!hourlyDraft.price_type_id ? 'Select a price type to add a fare' : undefined}>
                  <button type="button" className="host-btn-add" disabled={!hourlyDraft.price_type_id || hourlyRangeInvalid} onClick={async () => {
                    await createHostCarHourlyFare(recordId, hourlyDraft)
                    loadPricing(recordId)
                  }}><Plus size={16} /> Add hourly fare</button>
                </span>
                {hourlyFares.length > 0 && (
                  <ul className="host-fare-list">
                    {hourlyFares.map((f) => (
                      <li key={f.id} className="host-fare-tag">
                        <Clock size={14} className="host-fare-tag-icon" />
                        <span className="host-fare-tag-text">
                          {f.price_type?.name && <em>{f.price_type.name}</em>}
                          {f.min_minutes}–{f.max_minutes} min <ArrowRight size={12} /> <strong>€{(f.total_price_cents / 100).toFixed(2)}</strong>
                        </span>
                        <button type="button" className="host-fare-tag-remove" aria-label="Remove fare" title="Remove fare" onClick={async () => { await deleteHostCarHourlyFare(recordId, f.id); loadPricing(recordId) }}><Trash2 size={14} /></button>
                      </li>
                    ))}
                  </ul>
                )}
              </section>

              {/* Extra-hour fares */}
              <section className="host-fare-section">
                <div className="host-fare-head">
                  <span className="host-fare-head-icon"><Timer size={18} /></span>
                  <div className="host-fare-head-text">
                    <h3>Extra-hour fares</h3>
                    <p>Set the amount charged for each hour a guest keeps the vehicle beyond their booked window.</p>
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-3">
                  <div className="host-field"><label>Price type</label>
                    <HostSelect
                      value={String(extraDraft.price_type_id || '')}
                      onChange={(v) => setExtraDraft({ ...extraDraft, price_type_id: v })}
                      options={catalog.priceTypes.map((p) => ({ value: String(p.id), label: p.name }))}
                      placeholder="Select"
                      ariaLabel="Extra-hour fare price type"
                    />
                  </div>
                  <div className="host-field"><label>€ / extra hour</label><input type="number" min={0} value={extraDraft.charge_per_extra_hour_euros} onChange={(e) => setExtraDraft({ ...extraDraft, charge_per_extra_hour_euros: Number(e.target.value) })} /></div>
                </div>
                <div className="host-fare-preview">
                  <span className="host-fare-preview-label">Preview</span>
                  <span className="host-fare-tag is-preview">
                    <Timer size={14} className="host-fare-tag-icon" />
                    <span className="host-fare-tag-text"><strong>€{Number(extraDraft.charge_per_extra_hour_euros || 0).toFixed(0)}</strong> / extra hour</span>
                  </span>
                </div>
                <span className="host-tooltip-wrap" data-tooltip={!extraDraft.price_type_id ? 'Select a price type to add a fare' : undefined}>
                  <button type="button" className="host-btn-add" disabled={!extraDraft.price_type_id} onClick={async () => {
                    await createHostCarExtraHourFare(recordId, extraDraft)
                    loadPricing(recordId)
                  }}><Plus size={16} /> Add extra-hour fare</button>
                </span>
                {extraHourFares.length > 0 && (
                  <ul className="host-fare-list">
                    {extraHourFares.map((f) => (
                      <li key={f.id} className="host-fare-tag">
                        <Timer size={14} className="host-fare-tag-icon" />
                        <span className="host-fare-tag-text">
                          {f.price_type?.name && <em>{f.price_type.name}</em>}
                          <strong>€{(f.charge_per_extra_hour_cents / 100).toFixed(2)}</strong> / extra hour
                        </span>
                        <button type="button" className="host-fare-tag-remove" aria-label="Remove fare" title="Remove fare" onClick={async () => { await deleteHostCarExtraHourFare(recordId, f.id); loadPricing(recordId) }}><Trash2 size={14} /></button>
                      </li>
                    ))}
                  </ul>
                )}
              </section>
            </div>
          ) : <p className="text-sm text-slate-500">Save the vehicle first to manage pricing.</p>
        )}
        {step === 5 && (
          recordId ? (
            <>
              <h3 className="mb-2 font-semibold text-brand-950">Availability blocks</h3>
              <div className="grid grid-cols-2 gap-3">
                <div className="host-field"><label>From</label><HostDateTimePicker value={blockDraft.starts_at} onChange={(v) => setBlockDraft({ ...blockDraft, starts_at: v })} placeholder="Select start date & time" /></div>
                <div className="host-field"><label>To</label><HostDateTimePicker value={blockDraft.ends_at} onChange={(v) => setBlockDraft({ ...blockDraft, ends_at: v })} placeholder="Select end date & time" /></div>
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
                  <HostSelect
                    value={specialDraft.type}
                    onChange={(v) => setSpecialDraft({ ...specialDraft, type: v })}
                    options={[
                      { value: 'charge', label: 'Charge' },
                      { value: 'discount', label: 'Discount' },
                    ]}
                    ariaLabel="Special price type"
                  />
                </div>
                <div className="host-field"><label>From date</label><HostDatePicker value={specialDraft.date_from} onChange={(v) => setSpecialDraft({ ...specialDraft, date_from: v })} /></div>
                <div className="host-field"><label>To date</label><HostDatePicker value={specialDraft.date_to} onChange={(v) => setSpecialDraft({ ...specialDraft, date_to: v })} minDate={specialDraft.date_from ? new Date(specialDraft.date_from) : undefined} /></div>
                <div className="host-field"><label>Value type</label>
                  <HostSelect
                    value={specialDraft.value_mode}
                    onChange={(v) => setSpecialDraft({ ...specialDraft, value_mode: v })}
                    options={[
                      { value: 'percentage', label: 'Percentage' },
                      { value: 'fixed', label: 'Fixed' },
                    ]}
                    ariaLabel="Special price value type"
                  />
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
