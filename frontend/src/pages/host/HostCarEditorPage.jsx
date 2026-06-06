import { useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import {
  createHostCar,
  createHostCarDailyFare,
  createHostCarExtraHourFare,
  createHostCarHourlyFare,
  createHostCarUnit,
  deleteHostCarDailyFare,
  deleteHostCarUnit,
  getHostCar,
  getHostCarDailyFares,
  getHostCarExtraHourFares,
  getHostCarHourlyFares,
  getHostCarUnits,
  getHostCatalog,
  submitHostCar,
  syncHostCarRelations,
  updateHostCar,
  uploadHostCarImages,
} from '../../api/host'
import ListingStatusBadge from '../../components/host/ListingStatusBadge'
import { PageLoader } from '../../components/ui/LoadingSpinner'
import { useToast } from '../../context/ToastContext'

const STEPS = ['Vehicle', 'Specs', 'Locations', 'Units', 'Pricing', 'Review']

const emptyForm = {
  name: '',
  category_id: '',
  description: '',
  transmission: 'manual',
  fuel_type: 'diesel',
  units_available: 1,
  ical_import_url: '',
  location_ids: [],
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
  const [catalog, setCatalog] = useState({ categories: [], locations: [], characteristics: [], rentalOptions: [], priceTypes: [] })
  const [units, setUnits] = useState([])
  const [dailyFares, setDailyFares] = useState([])
  const [hourlyFares, setHourlyFares] = useState([])
  const [extraHourFares, setExtraHourFares] = useState([])
  const [fareDraft, setFareDraft] = useState({ price_type_id: '', from_days: 1, to_days: 7, price_per_day_euros: 100 })
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
          location_ids: data.location_ids || [],
          characteristic_ids: data.characteristic_ids || [],
          rental_option_ids: data.rental_option_ids || [],
        })
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
        ...form,
        category_id: Number(form.category_id),
      }
      if (recordId) {
        await updateHostCar(recordId, payload)
        await syncHostCarRelations(recordId, {
          location_ids: form.location_ids,
          characteristic_ids: form.characteristic_ids,
          rental_option_ids: form.rental_option_ids,
        })
        toast('Saved', 'success')
        return recordId
      }

      const res = await createHostCar({ name: form.name || 'New vehicle', ...payload })
      const newId = res.data.data.id
      setRecordId(newId)
      setStatus(res.data.data.listing_status)
      await syncHostCarRelations(newId, {
        location_ids: form.location_ids,
        characteristic_ids: form.characteristic_ids,
        rental_option_ids: form.rental_option_ids,
      })
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
    let id = recordId
    if (!id) {
      id = await save()
    }
    if (!id) return
    try {
      const res = await submitHostCar(id)
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
            {recordId && <div className="host-field"><label>Main image</label><input type="file" accept="image/*" onChange={async (e) => {
              if (!e.target.files?.[0]) return
              const fd = new FormData()
              fd.append('main_image', e.target.files[0])
              await uploadHostCarImages(recordId, fd)
              toast('Image uploaded', 'success')
            }} /></div>}
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
          <div className="host-field"><label>Pickup / drop-off locations</label>
            <div className="grid grid-cols-2 gap-2">
              {catalog.locations.map((loc) => (
                <label key={loc.id} className="flex items-center gap-2 text-sm">
                  <input type="checkbox" checked={form.location_ids.includes(loc.id)} onChange={() => toggleId('location_ids', loc.id)} />
                  {loc.name}
                </label>
              ))}
            </div>
          </div>
        )}
        {step === 3 && recordId && (
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
        )}
        {step === 4 && recordId && (
          <>
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
            <button type="button" className="host-btn secondary" onClick={async () => {
              await createHostCarDailyFare(recordId, fareDraft)
              loadPricing(recordId)
            }}>Add daily fare</button>
            <ul className="mt-4 space-y-2 text-sm">
              {dailyFares.map((f) => (
                <li key={f.id} className="flex justify-between">
                  <span>{f.price_type?.name}: days {f.from_days}-{f.to_days} @ €{(f.price_per_day_cents / 100).toFixed(2)}</span>
                  <button type="button" className="host-btn danger" onClick={async () => { await deleteHostCarDailyFare(recordId, f.id); loadPricing(recordId) }}>Remove</button>
                </li>
              ))}
            </ul>
            <div className="host-field mt-4"><label>iCal import URL</label><input value={form.ical_import_url} onChange={(e) => setForm({ ...form, ical_import_url: e.target.value })} /></div>
          </>
        )}
        {step === 5 && (
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
