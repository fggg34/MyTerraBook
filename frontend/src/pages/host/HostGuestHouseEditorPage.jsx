import { useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import {
  createHostGuestHouse,
  getHostCatalog,
  getHostGuestHouse,
  submitHostGuestHouse,
  updateHostGuestHouse,
  uploadHostGuestHouseImages,
} from '../../api/host'
import ListingStatusBadge from '../../components/host/ListingStatusBadge'
import { PageLoader } from '../../components/ui/LoadingSpinner'
import { useToast } from '../../context/ToastContext'

const STEPS = ['Basics', 'Details', 'Pricing', 'Rules', 'Review']

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
  base_price_per_night_euros: 120,
  cleaning_fee_euros: 0,
  security_deposit_euros: 0,
  check_in_time: '15:00',
  check_out_time: '11:00',
  cancellation_policy: 'moderate',
  address: '',
  country: 'Iceland',
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
  const [loading, setLoading] = useState(!isNew)
  const [saving, setSaving] = useState(false)
  const [recordId, setRecordId] = useState(isNew ? null : Number(id))

  useEffect(() => {
    getHostCatalog('amenities').then((res) => setAmenities(res.data.data || []))
    getHostCatalog('tax-rates').then((res) => setTaxRates(res.data.data || []))
  }, [])

  useEffect(() => {
    if (isNew) return
    getHostGuestHouse(id)
      .then((res) => {
        const data = res.data.data
        setForm({
          ...emptyForm,
          ...data,
          max_nights: data.max_nights || '',
          amenity_ids: data.amenity_ids || [],
        })
        setStatus(data.status)
        setRejectionReason(data.rejection_reason || '')
        setRecordId(data.id)
      })
      .catch(() => toast('Could not load guesthouse', 'error'))
      .finally(() => setLoading(false))
  }, [id, isNew, toast])

  const save = async () => {
    setSaving(true)
    try {
      const payload = {
        ...form,
        max_nights: form.max_nights ? Number(form.max_nights) : null,
        amenity_ids: form.amenity_ids,
      }
      if (recordId) {
        await updateHostGuestHouse(recordId, payload)
        toast('Saved', 'success')
        return recordId
      }

      const res = await createHostGuestHouse({ name: form.name || 'New guesthouse', ...payload })
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
    let id = recordId
    if (!id) {
      id = await save()
    }
    if (!id) return
    try {
      const res = await submitHostGuestHouse(id)
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
      toast('Cover photo uploaded', 'success')
    } catch {
      toast('Upload failed', 'error')
    }
  }

  if (loading) return <PageLoader message="Loading editor…" />

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
            <div className="host-field"><label>Type</label>
              <select value={form.type} onChange={(e) => setForm({ ...form, type: e.target.value })}>
                {['room', 'apartment', 'villa', 'cottage', 'chalet', 'studio'].map((t) => <option key={t} value={t}>{t}</option>)}
              </select>
            </div>
            <div className="host-field"><label>City</label><input value={form.city} onChange={(e) => setForm({ ...form, city: e.target.value })} /></div>
            <div className="host-field"><label>Short description</label><textarea rows={3} value={form.short_description} onChange={(e) => setForm({ ...form, short_description: e.target.value })} /></div>
            {recordId && <div className="host-field"><label>Cover photo</label><input type="file" accept="image/*" onChange={handleThumbnail} /></div>}
          </>
        )}
        {step === 1 && (
          <>
            <div className="host-field"><label>Full description</label><textarea rows={6} value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} /></div>
            <div className="host-field"><label>Max guests</label><input type="number" value={form.max_guests} onChange={(e) => setForm({ ...form, max_guests: Number(e.target.value) })} /></div>
            <div className="host-field"><label>Bedrooms</label><input type="number" value={form.bedrooms} onChange={(e) => setForm({ ...form, bedrooms: Number(e.target.value) })} /></div>
            <div className="host-field"><label>Bathrooms</label><input type="number" value={form.bathrooms} onChange={(e) => setForm({ ...form, bathrooms: Number(e.target.value) })} /></div>
            <div className="host-field"><label>Address</label><input value={form.address} onChange={(e) => setForm({ ...form, address: e.target.value })} /></div>
            <div className="host-field"><label>Amenities</label>
              <div className="grid grid-cols-2 gap-2">
                {amenities.map((a) => (
                  <label key={a.id} className="flex items-center gap-2 text-sm">
                    <input
                      type="checkbox"
                      checked={form.amenity_ids.includes(a.id)}
                      onChange={(e) => {
                        const next = e.target.checked
                          ? [...form.amenity_ids, a.id]
                          : form.amenity_ids.filter((x) => x !== a.id)
                        setForm({ ...form, amenity_ids: next })
                      }}
                    />
                    {a.name}
                  </label>
                ))}
              </div>
            </div>
          </>
        )}
        {step === 2 && (
          <>
            <div className="host-field"><label>Nightly price (€)</label><input type="number" value={form.base_price_per_night_euros} onChange={(e) => setForm({ ...form, base_price_per_night_euros: Number(e.target.value) })} /></div>
            <div className="host-field"><label>Cleaning fee (€)</label><input type="number" value={form.cleaning_fee_euros} onChange={(e) => setForm({ ...form, cleaning_fee_euros: Number(e.target.value) })} /></div>
            <div className="host-field"><label>Security deposit (€)</label><input type="number" value={form.security_deposit_euros} onChange={(e) => setForm({ ...form, security_deposit_euros: Number(e.target.value) })} /></div>
            <div className="host-field"><label>Tax rate</label>
              <select value={form.tax_rate_id || ''} onChange={(e) => setForm({ ...form, tax_rate_id: e.target.value ? Number(e.target.value) : null })}>
                <option value="">None</option>
                {taxRates.map((t) => <option key={t.id} value={t.id}>{t.name}</option>)}
              </select>
            </div>
          </>
        )}
        {step === 3 && (
          <>
            <div className="host-field"><label>Check-in</label><input type="time" value={form.check_in_time} onChange={(e) => setForm({ ...form, check_in_time: e.target.value })} /></div>
            <div className="host-field"><label>Check-out</label><input type="time" value={form.check_out_time} onChange={(e) => setForm({ ...form, check_out_time: e.target.value })} /></div>
            <div className="host-field"><label>Min nights</label><input type="number" value={form.min_nights} onChange={(e) => setForm({ ...form, min_nights: Number(e.target.value) })} /></div>
            <div className="host-field"><label>Cancellation policy</label>
              <select value={form.cancellation_policy} onChange={(e) => setForm({ ...form, cancellation_policy: e.target.value })}>
                {['flexible', 'moderate', 'strict'].map((p) => <option key={p} value={p}>{p}</option>)}
              </select>
            </div>
          </>
        )}
        {step === 4 && (
          <div>
            <p className="text-sm text-slate-600">Review your listing, save changes, then submit for admin approval.</p>
            <ul className="mt-4 space-y-2 text-sm">
              <li><strong>Name:</strong> {form.name}</li>
              <li><strong>City:</strong> {form.city}</li>
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
