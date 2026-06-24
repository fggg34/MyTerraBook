import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { AlertCircle, ArrowRight, CalendarDays, ChevronLeft, ChevronRight, Plus, Shield, Timer, Trash2 } from 'lucide-react'
import {
  addHostCarAvailability,
  addHostCarSpecialPrice,
  createHostCar,
  createHostCarDailyFare,
  createHostCarExtraHourFare,
  createHostCarLocationFee,
  createHostCarOutOfHoursFee,
  createHostCarUnit,
  deleteHostCarDailyFare,
  deleteHostCarExtraHourFare,
  deleteHostCarLocationFee,
  deleteHostCarOutOfHoursFee,
  deleteHostCarUnit,
  getHostCar,
  getHostCarAvailability,
  getHostCarDailyFares,
  getHostCarExtraHourFares,
  getHostCarLocationFees,
  getHostCarOutOfHoursFees,
  getHostCarSpecialPrices,
  getHostCarUnits,
  getHostCatalog,
  createHostLocation,
  getPublicMainCategories,
  getPublicSubCategories,
  removeHostCarAvailability,
  removeHostCarSpecialPrice,
  resolveStorageUrl,
  submitHostCar,
  syncHostCarRelations,
  updateHostCar,
  updateHostCarDailyFare,
  updateHostCarExtraHourFare,
  updateHostCarLocationFee,
  updateHostCarOutOfHoursFee,
  updateHostCarSpecialPrice,
  uploadHostCarImages,
} from '../../api/host'
import {
  BASE_FARE_FROM_DAYS,
  BASE_FARE_TO_DAYS,
  dateRangesOverlap,
  durationTierFares,
  filterStandardPriceTypeRows,
  findBaseDailyFare,
  findPriceType,
  findOverlappingTier,
  baseDailyRateEuros,
  hasUnsavedProtectionPricing,
  inferProtectionAddOnEuros,
  readProtectionOffers,
  isBaseDailyPriceDirty,
  standardDailyFares,
  standardPriceTypeId,
  syncProtectionAddOn,
  validateSeasonalDraft,
} from '../../utils/hostCarPricingUtils'
import {
  buildHostRentalOptionSyncPayload,
  normalizeHostRentalOptionFromApi,
} from '../../utils/rentalOptionPricing'
import HostCarExtrasPanel from '../../components/host/HostCarExtrasPanel'
import HostCarProtectionPlansPanel from '../../components/host/HostCarProtectionPlansPanel'
import HostCarLocationsStep from '../../components/host/HostCarLocationsStep'
import HostDisclosure from '../../components/host/HostDisclosure'
import {
  HostImageDropzone,
  HostImageGallery,
  HOST_IMAGE_FORMAT_HINT,
  HOST_MIN_DETAIL_IMAGES,
} from '../../components/host/HostImageUpload'
import HostIconMultiSelect from '../../components/host/HostIconMultiSelect'
import HostReadinessChecklist from '../../components/host/HostReadinessChecklist'
import HostDatePicker from '../../components/host/HostDatePicker'
import HostDateTimePicker from '../../components/host/HostDateTimePicker'
import HostSelect from '../../components/host/HostSelect'
import ListingStatusBadge from '../../components/host/ListingStatusBadge'
import LucideIcon from '../../utils/iconCatalog'
import { normalizeTimeString, formatDate } from '../../utils/format'
import { useToast } from '../../context/ToastContext'
import { useHostCurrency } from '../../hooks/useHostCurrency'

const specIcon = (name) => <LucideIcon name={name} size={18} strokeWidth={1.8} />

const TRANSMISSION_OPTIONS = [
  { value: 'manual', label: 'Manual', icon: specIcon('cog') },
  { value: 'automatic', label: 'Automatic', icon: specIcon('settings') },
]

const FUEL_OPTIONS = [
  { value: 'petrol', label: 'Petrol', icon: specIcon('fuel') },
  { value: 'diesel', label: 'Diesel', icon: specIcon('fuel') },
  { value: 'electric', label: 'Electric', icon: specIcon('zap') },
  { value: 'hybrid', label: 'Hybrid', icon: specIcon('leaf') },
]

const DRIVE_OPTIONS = [
  { value: 'fwd', label: 'Front-wheel drive (FWD)', icon: specIcon('car') },
  { value: 'rwd', label: 'Rear-wheel drive (RWD)', icon: specIcon('car') },
  { value: 'awd', label: 'All-wheel drive (AWD)', icon: specIcon('mountain') },
  { value: '4wd', label: '4×4', icon: specIcon('mountain') },
]

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

const STEPS = ['Vehicle', 'Specs', 'Locations', 'Units', 'Pricing', 'Availability', 'Submit and Review']

const emptyForm = {
  name: '',
  main_category_id: '',
  sub_category_id: '',
  description: '',
  transmission: 'manual',
  fuel_type: 'diesel',
  drive_type: 'fwd',
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
  rental_options: [],
  rental_condition_ids: [],
}

export default function HostCarEditorPage() {
  const { id } = useParams()
  const isNew = !id || id === 'new'
  const navigate = useNavigate()
  const { toast } = useToast()
  const currency = useHostCurrency()
  const [step, setStep] = useState(0)
  const [form, setForm] = useState(emptyForm)
  const [status, setStatus] = useState('draft')
  const [rejectionReason, setRejectionReason] = useState('')
  const [mainImage, setMainImage] = useState(null)
  const [pendingMainFile, setPendingMainFile] = useState(null)
  const [pendingMainPreview, setPendingMainPreview] = useState(null)
  const [pendingDetails, setPendingDetails] = useState([])
  const [catalog, setCatalog] = useState({
    mainCategories: [],
    subCategories: [],
    locations: [],
    characteristics: [],
    rentalOptions: [],
    rentalConditions: [],
    priceTypes: [],
  })
  const [units, setUnits] = useState([])
  const [dailyFares, setDailyFares] = useState([])
  const [extraHourFares, setExtraHourFares] = useState([])
  const [availability, setAvailability] = useState([])
  const [specialPrices, setSpecialPrices] = useState([])
  const [locationFees, setLocationFees] = useState([])
  const [outOfHoursFees, setOutOfHoursFees] = useState([])
  const [locationFeeDraft, setLocationFeeDraft] = useState({
    pickup_location_id: '',
    dropoff_location_id: '',
    cost_euros: '',
    is_one_way_fee: false,
    multiply_by_days: false,
  })
  const [editingLocationFeeId, setEditingLocationFeeId] = useState(null)
  const [oohFeeDraft, setOohFeeDraft] = useState({
    name: 'Out-of-hours',
    time_from: '20:00',
    time_to: '08:00',
    applies_to: 'both',
    pickup_cost_euros: '',
    dropoff_cost_euros: '',
    location_ids: [],
  })
  const [baseDailyPrice, setBaseDailyPrice] = useState('')
  const [baseDailySaving, setBaseDailySaving] = useState(false)
  const [tierDraft, setTierDraft] = useState({ from_days: 7, to_days: 14, price_per_day_euros: '' })
  const [tierValidationError, setTierValidationError] = useState('')
  const [extraDraft, setExtraDraft] = useState({ charge_per_extra_hour_euros: '' })
  const [plusAddOn, setPlusAddOn] = useState('')
  const [maxAddOn, setMaxAddOn] = useState('')
  const [protectionOffers, setProtectionOffers] = useState({ plus: false, max: false })
  const [protectionSaving, setProtectionSaving] = useState(false)
  const [blockDraft, setBlockDraft] = useState({ starts_at: '', ends_at: '', units_blocked: 1, notes: '' })
  const [unitsBusy, setUnitsBusy] = useState(false)
  const [specialDraft, setSpecialDraft] = useState({ name: '', date_from: '', date_to: '', type: 'charge', value_mode: 'percentage', value_percent_bips: 1000, value_fixed_cents: 0 })
  const [editingSpecialPriceId, setEditingSpecialPriceId] = useState(null)
  const [editingExtraHourFareId, setEditingExtraHourFareId] = useState(null)
  const [editingOohFeeId, setEditingOohFeeId] = useState(null)
  const [loading, setLoading] = useState(!isNew)
  const [saving, setSaving] = useState(false)
  const [recordId, setRecordId] = useState(isNew ? null : Number(id))
  const [pendingFocusId, setPendingFocusId] = useState(null)

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
      getHostCatalog('rental-conditions'),
      getHostCatalog('price-types'),
    ]).then(([mc, sc, l, ch, ro, rc, pt]) => {
      const mainCategories = unwrap(mc)
      const subCategories = unwrap(sc)

      setCatalog({
        mainCategories,
        subCategories,
        locations: unwrap(l),
        characteristics: unwrap(ch),
        rentalOptions: unwrap(ro),
        rentalConditions: unwrap(rc),
        priceTypes: unwrap(pt),
      })

      if (mainCategories.length === 0) {
        toast('Could not load vehicle categories. Please refresh or contact support.', 'error')
      }
    })
  }, [toast])

  const loadPricing = (carId) => Promise.all([
    getHostCarUnits(carId).then((res) => setUnits(res.data.data || [])),
    getHostCarDailyFares(carId).then((res) => setDailyFares(res.data.data || [])),
    getHostCarExtraHourFares(carId).then((res) => setExtraHourFares(res.data.data || [])),
    getHostCarAvailability(carId).then((res) => setAvailability(res.data.data || [])),
    getHostCarSpecialPrices(carId).then((res) => setSpecialPrices(res.data.data || [])),
    getHostCarLocationFees(carId).then((res) => setLocationFees(res.data.data || [])),
    getHostCarOutOfHoursFees(carId).then((res) => setOutOfHoursFees(res.data.data || [])),
  ])

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
          pickup_time_from: normalizeTimeString(data.pickup_time_from || ''),
          pickup_time_to: normalizeTimeString(data.pickup_time_to || ''),
          dropoff_time_from: normalizeTimeString(data.dropoff_time_from || ''),
          dropoff_time_to: normalizeTimeString(data.dropoff_time_to || ''),
          seats: data.seats ?? 5,
          sleeps: data.sleeps ?? 0,
          bags: data.bags ?? 2,
          drive_type: data.drive_type || 'fwd',
          year: data.year ?? '',
          characteristic_ids: data.characteristic_ids || [],
          rental_options: (data.rental_options?.length
            ? data.rental_options
            : (data.rental_option_ids || []).map((optionId) => ({ id: optionId, cost_euros: 0 }))
          ).map((row) => normalizeHostRentalOptionFromApi(row, currency)),
          rental_condition_ids: data.rental_condition_ids || [],
        })
        setMainImage(data.main_image_path || null)
        setStatus(data.listing_status)
        setRejectionReason(data.rejection_reason || '')
        setRecordId(data.id)
        loadPricing(data.id)
      })
      .catch(() => toast('Could not load vehicle', 'error'))
      .finally(() => setLoading(false))
  }, [id, isNew, toast, currency])

  useEffect(() => {
    if (isNew || !recordId || status !== 'pending_review') return undefined

    const refreshStatus = () => {
      getHostCar(recordId)
        .then((res) => {
          const data = res.data.data
          setStatus(data.listing_status)
          setRejectionReason(data.rejection_reason || '')
        })
        .catch(() => {})
    }

    const onVisibilityChange = () => {
      if (document.visibilityState === 'visible') refreshStatus()
    }

    window.addEventListener('focus', refreshStatus)
    document.addEventListener('visibilitychange', onVisibilityChange)

    return () => {
      window.removeEventListener('focus', refreshStatus)
      document.removeEventListener('visibilitychange', onVisibilityChange)
    }
  }, [isNew, recordId, status])

  const save = async () => {
    setSaving(true)
    try {
      const mainSlug = catalog.mainCategories.find(
        (c) => String(c.id) === String(form.main_category_id),
      )?.slug
      const campervanSave = mainSlug === 'campervan'

      const payload = {
        name: form.name,
        sub_category_id: form.sub_category_id ? Number(form.sub_category_id) : null,
        description: form.description,
        transmission: form.transmission || null,
        fuel_type: form.fuel_type || null,
        drive_type: form.drive_type || null,
        seats: form.seats ?? null,
        sleeps: campervanSave ? (form.sleeps ?? null) : 0,
        bags: form.bags ?? null,
        year: form.year ? Number(form.year) : null,
        pickup_time_from: form.pickup_time_from || null,
        pickup_time_to: form.pickup_time_to || null,
        dropoff_time_from: form.dropoff_time_from || null,
        dropoff_time_to: form.dropoff_time_to || null,
      }
      const relations = {
        pickup_location_ids: form.pickup_location_ids,
        dropoff_location_ids: form.dropoff_location_ids,
        characteristic_ids: form.characteristic_ids,
        rental_options: form.rental_options.map((row) => (
          buildHostRentalOptionSyncPayload(row, catalog.rentalOptions, currency)
        )),
        rental_condition_ids: form.rental_condition_ids,
      }
      if (recordId) {
        await updateHostCar(recordId, payload)
        await syncHostCarRelations(recordId, relations)
        try {
          await flushPendingImages(recordId)
        } catch {
          toast('Saved, but some photos could not upload. Try saving again.', 'error')
          return recordId
        }
        toast('Saved', 'success')
        return recordId
      }

      const res = await createHostCar({ ...payload, name: form.name || 'New vehicle' })
      const newId = res.data.data.id
      setRecordId(newId)
      setStatus(res.data.data.listing_status)
      await syncHostCarRelations(newId, relations)
      try {
        await flushPendingImages(newId)
      } catch {
        toast('Vehicle created, but some photos could not upload. Save again to retry.', 'error')
        navigate(`/host/cars/${newId}/edit`, { replace: true })
        return newId
      }
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
      toast('Set pickup and drop-off times on the Locations step (all four fields).', 'error')
      setStep(2)
      setPendingFocusId('host-car-times')
      return
    }

    const carId = await save()
    if (!carId) return

    try {
      const pricingSaved = await flushPendingPricing(carId)
      if (!pricingSaved) return
    } catch (err) {
      toast(err.response?.data?.message || 'Could not save pricing before submit', 'error')
      return
    }

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
    fd.append('main_image', file)
    try {
      await uploadHostCarImages(recordId, fd)
      if (pendingMainPreview) URL.revokeObjectURL(pendingMainPreview)
      setPendingMainFile(null)
      setPendingMainPreview(null)
      reloadCar(recordId)
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

  const handleDetailsImages = async (files) => {
    if (!files.length) return

    if (!recordId) {
      setPendingDetails((prev) => [
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
    files.forEach((file) => fd.append('details_images[]', file))
    try {
      await uploadHostCarImages(recordId, fd)
      reloadCar(recordId)
      toast('Images uploaded', 'success')
    } catch {
      toast('Upload failed', 'error')
    }
  }

  const removePendingDetail = (id) => {
    setPendingDetails((prev) => {
      const item = prev.find((p) => p.id === id)
      if (item?.previewUrl) URL.revokeObjectURL(item.previewUrl)
      return prev.filter((p) => p.id !== id)
    })
  }

  const flushPendingImages = async (carId) => {
    let uploaded = false

    if (pendingMainFile) {
      const fd = new FormData()
      fd.append('main_image', pendingMainFile)
      await uploadHostCarImages(carId, fd)
      if (pendingMainPreview) URL.revokeObjectURL(pendingMainPreview)
      setPendingMainFile(null)
      setPendingMainPreview(null)
      uploaded = true
    }

    if (pendingDetails.length > 0) {
      const fd = new FormData()
      pendingDetails.forEach((item) => fd.append('details_images[]', item.file))
      await uploadHostCarImages(carId, fd)
      pendingDetails.forEach((item) => URL.revokeObjectURL(item.previewUrl))
      setPendingDetails([])
      uploaded = true
    }

    if (uploaded) reloadCar(carId)
  }

  useEffect(() => () => {
    if (pendingMainPreview) URL.revokeObjectURL(pendingMainPreview)
    pendingDetails.forEach((item) => {
      if (item.previewUrl) URL.revokeObjectURL(item.previewUrl)
    })
  }, [pendingMainPreview, pendingDetails])

  const removeDetailsImage = async (path) => {
    const remaining = form.details_image_paths.filter((p) => p !== path)

    if (!recordId) {
      setForm((prev) => ({ ...prev, details_image_paths: remaining }))
      return
    }

    const fd = new FormData()
    fd.append('replace_details_image_paths', '1')
    remaining.forEach((p) => fd.append('details_image_paths[]', p))
    try {
      await uploadHostCarImages(recordId, fd)
      setForm((prev) => ({ ...prev, details_image_paths: remaining }))
      toast('Image removed', 'success')
    } catch (err) {
      toast(err.response?.data?.message || 'Could not remove image', 'error')
    }
  }

  const mainImagePreview = mainImage ? resolveStorageUrl(mainImage) : pendingMainPreview
  const detailImageCount = form.details_image_paths.length + pendingDetails.length
  const hasMainImage = !!(mainImage || pendingMainFile)

  const filteredSubCategories = catalog.subCategories.filter(
    (sub) => !form.main_category_id || String(sub.main_category_id) === String(form.main_category_id),
  )

  const selectedMainCategory = useMemo(
    () => catalog.mainCategories.find((c) => String(c.id) === String(form.main_category_id)),
    [catalog.mainCategories, form.main_category_id],
  )

  const isCampervan = selectedMainCategory?.slug === 'campervan'

  const readinessItems = useMemo(() => [
    { label: 'Vehicle name', done: !!String(form.name || '').trim(), step: 0, focusId: 'host-car-name' },
    { label: 'Category selected', done: !!form.sub_category_id, step: 0, focusId: 'host-car-sub-category' },
    {
      label: 'Main image uploaded',
      done: hasMainImage,
      step: 0,
      focusId: 'host-car-main-image',
    },
    {
      label: `At least ${HOST_MIN_DETAIL_IMAGES} detail photos`,
      done: detailImageCount >= HOST_MIN_DETAIL_IMAGES,
      step: 0,
      focusId: 'host-car-detail-images',
    },
    {
      label: 'Pickup & drop-off locations',
      done: (form.pickup_location_ids || []).length > 0 && (form.dropoff_location_ids || []).length > 0,
      step: 2,
      focusId: 'host-car-locations',
    },
    {
      label: 'Pickup & drop-off times',
      done: !!(form.pickup_time_from && form.pickup_time_to && form.dropoff_time_from && form.dropoff_time_to),
      step: 2,
      focusId: 'host-car-times',
    },
    {
      label: 'Daily rental rate set',
      done: !!findBaseDailyFare(standardDailyFares(dailyFares, catalog.priceTypes)),
      step: 4,
      focusId: 'host-base-daily-price',
    },
    {
      label: 'At least one unit available',
      done: units.length > 0,
      step: 3,
      focusId: 'host-car-units',
    },
    { label: 'Transmission selected', done: !!form.transmission, step: 1, focusId: 'host-car-transmission' },
    { label: 'Fuel type selected', done: !!form.fuel_type, step: 1, focusId: 'host-car-fuel-type' },
    { label: 'Drive system selected', done: !!form.drive_type, step: 1, focusId: 'host-car-drive-type' },
    { label: 'Bags capacity set', done: Number(form.bags) > 0, step: 1, focusId: 'car-bags' },
    {
      label: isCampervan ? 'Sleeps (berths) set' : 'Seats set',
      done: isCampervan ? Number(form.sleeps) > 0 : Number(form.seats) > 0,
      step: 1,
      focusId: isCampervan ? 'car-sleeps' : 'car-seats',
    },
  ], [form, dailyFares, catalog.priceTypes, isCampervan, units.length, hasMainImage, detailImageCount])
  const isReady = readinessItems.every((i) => i.done)

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

  const standardPriceType = useMemo(() => standardPriceTypeId(catalog.priceTypes), [catalog.priceTypes])
  const plusTierName = useMemo(() => findPriceType(catalog.priceTypes, 'plus')?.name || 'Plus', [catalog.priceTypes])
  const maxTierName = useMemo(() => findPriceType(catalog.priceTypes, 'max')?.name || 'Max', [catalog.priceTypes])
  const hostDailyFares = useMemo(
    () => standardDailyFares(dailyFares, catalog.priceTypes),
    [dailyFares, catalog.priceTypes],
  )
  const baseDailyFare = useMemo(() => findBaseDailyFare(hostDailyFares), [hostDailyFares])
  const durationTiers = useMemo(() => durationTierFares(hostDailyFares), [hostDailyFares])
  const hostExtraHourFares = useMemo(
    () => filterStandardPriceTypeRows(extraHourFares, catalog.priceTypes),
    [extraHourFares, catalog.priceTypes],
  )
  const baseDailyPriceDirty = useMemo(
    () => isBaseDailyPriceDirty(baseDailyPrice, baseDailyFare),
    [baseDailyPrice, baseDailyFare],
  )
  const protectionPricingDirty = useMemo(
    () => hasUnsavedProtectionPricing(
      protectionOffers,
      plusAddOn,
      maxAddOn,
      dailyFares,
      catalog.priceTypes,
    ),
    [protectionOffers, plusAddOn, maxAddOn, dailyFares, catalog.priceTypes],
  )
  const seasonalFixedAmountLabel = specialDraft.type === 'discount'
    ? currency.fixedDiscountLabel
    : currency.fixedSurchargeLabel

  const tierRangeInvalid = Number(tierDraft.to_days) <= Number(tierDraft.from_days)
  const overlappingTier = useMemo(
    () => findOverlappingTier(durationTiers, tierDraft.from_days, tierDraft.to_days),
    [durationTiers, tierDraft.from_days, tierDraft.to_days],
  )
  const baseRateEuros = useMemo(() => baseDailyRateEuros(baseDailyFare), [baseDailyFare])
  const tierPriceTooHigh = useMemo(() => {
    const price = Number(tierDraft.price_per_day_euros)
    if (!price || !baseRateEuros) return false
    return price >= baseRateEuros
  }, [tierDraft.price_per_day_euros, baseRateEuros])
  const tierPriceZero = tierDraft.price_per_day_euros !== '' && Number(tierDraft.price_per_day_euros) <= 0
  const seasonalValidationError = useMemo(() => validateSeasonalDraft(specialDraft), [specialDraft])
  const missingReadinessLabels = useMemo(
    () => readinessItems.filter((i) => !i.done).map((i) => i.label),
    [readinessItems],
  )
  const submitDisabledTitle = missingReadinessLabels.length > 0
    ? `Still needed: ${missingReadinessLabels.join(', ')}`
    : undefined

  const refreshLocations = () => {
    getHostCatalog('locations').then((res) => {
      setCatalog((prev) => ({ ...prev, locations: res.data?.data || [] }))
    })
  }

  const validateTierDraft = () => {
    if (tierRangeInvalid) {
      return '“From day” must be lower than “To day”.'
    }
    if (overlappingTier) {
      return `This range overlaps with Days ${overlappingTier.from_days}–${overlappingTier.to_days}. Adjust the dates or remove the existing tier first.`
    }
    if (tierPriceZero || tierDraft.price_per_day_euros === '' || Number(tierDraft.price_per_day_euros) <= 0) {
      return 'Enter a daily rate greater than zero.'
    }
    if (tierPriceTooHigh && baseDailyFare) {
      return `Duration tiers must be cheaper than your standard daily rate (${currency.formatCents(baseDailyFare.price_per_day_cents)}/day).`
    }
    return ''
  }

  const addDurationTier = async () => {
    const error = validateTierDraft()
    if (error) {
      setTierValidationError(error)
      return
    }
    setTierValidationError('')
    try {
      await createHostCarDailyFare(recordId, buildStandardFarePayload({
        ...tierDraft,
        price_per_day_euros: Number(tierDraft.price_per_day_euros),
      }))
      setTierDraft({ from_days: 7, to_days: 14, price_per_day_euros: '' })
      loadPricing(recordId)
    } catch (err) {
      toast(err.response?.data?.message || 'Could not add tier', 'error')
    }
  }

  const findOverlappingBlock = (startsAt, endsAt) => availability.find(
    (block) => dateRangesOverlap(startsAt, endsAt, block.starts_at, block.ends_at),
  )

  const addAvailabilityBlock = async () => {
    const overlap = findOverlappingBlock(blockDraft.starts_at, blockDraft.ends_at)
    if (overlap) {
      toast(`This block overlaps an existing block (${formatDate(overlap.starts_at)} – ${formatDate(overlap.ends_at)}). Remove or adjust the existing block first.`, 'error')
      return
    }
    try {
      await addHostCarAvailability(recordId, blockDraft)
      setBlockDraft({ starts_at: '', ends_at: '', units_blocked: 1, notes: '' })
      loadPricing(recordId)
    } catch (err) {
      toast(err.response?.data?.message || 'Could not add block', 'error')
    }
  }

  useEffect(() => {
    const base = findBaseDailyFare(standardDailyFares(dailyFares, catalog.priceTypes))
    setBaseDailyPrice(base ? String(base.price_per_day_cents / 100) : '')
  }, [dailyFares, catalog.priceTypes])

  useEffect(() => {
    if (!catalog.priceTypes.length) return
    setProtectionOffers(readProtectionOffers(dailyFares, catalog.priceTypes))
  }, [dailyFares, catalog.priceTypes])

  useEffect(() => {
    const plus = inferProtectionAddOnEuros(dailyFares, catalog.priceTypes, 'plus')
    const max = inferProtectionAddOnEuros(dailyFares, catalog.priceTypes, 'max')
    setPlusAddOn(plus == null ? '' : String(plus))
    setMaxAddOn(max == null ? '' : String(max))
  }, [dailyFares, catalog.priceTypes])

  const buildStandardFarePayload = (draft) => ({
    ...draft,
    price_type_id: standardPriceType,
  })

  const saveBaseDailyPrice = async (options = {}) => {
    const { silent = false } = options
    const price = Number(baseDailyPrice)
    if (!price || price <= 0) {
      if (!silent) toast('Enter a daily price greater than 0', 'error')
      return false
    }
    if (!recordId || !standardPriceType) return false

    setBaseDailySaving(true)
    try {
      const payload = buildStandardFarePayload({
        from_days: BASE_FARE_FROM_DAYS,
        to_days: BASE_FARE_TO_DAYS,
        price_per_day_euros: price,
      })
      if (baseDailyFare) {
        await updateHostCarDailyFare(recordId, baseDailyFare.id, payload)
      } else {
        await createHostCarDailyFare(recordId, payload)
      }
      await loadPricing(recordId)
      if (!silent) toast('Daily rate saved', 'success')
      return true
    } catch (err) {
      if (!silent) toast(err.response?.data?.message || 'Could not save daily rate', 'error')
      throw err
    } finally {
      setBaseDailySaving(false)
    }
  }

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

  const saveProtectionPricing = async (options = {}) => {
    const { silent = false, faresOverride = null } = options
    if (!recordId || !standardPriceType) return false
    const fares = faresOverride ?? dailyFares
    const baseFare = findBaseDailyFare(standardDailyFares(fares, catalog.priceTypes))
    if (!baseFare) {
      if (!silent) toast('Save your daily rental rate first, then set protection plans.', 'error')
      return false
    }
    if (protectionOffers.plus && (!plusAddOn || Number(plusAddOn) <= 0)) {
      if (!silent) toast(`Enter a daily price for ${plusTierName} or turn it off.`, 'error')
      return false
    }
    if (protectionOffers.max && (!maxAddOn || Number(maxAddOn) <= 0)) {
      if (!silent) toast(`Enter a daily price for ${maxTierName} or turn it off.`, 'error')
      return false
    }
    setProtectionSaving(true)
    try {
      const api = { createHostCarDailyFare, updateHostCarDailyFare, deleteHostCarDailyFare }
      await syncProtectionAddOn(
        recordId,
        'plus',
        protectionOffers.plus ? plusAddOn : '',
        fares,
        catalog.priceTypes,
        api,
      )
      await syncProtectionAddOn(
        recordId,
        'max',
        protectionOffers.max ? maxAddOn : '',
        fares,
        catalog.priceTypes,
        api,
      )
      await loadPricing(recordId)
      if (!silent) toast('Protection settings saved', 'success')
      return true
    } catch (err) {
      if (!silent) toast(err.response?.data?.message || 'Could not save protection settings', 'error')
      throw err
    } finally {
      setProtectionSaving(false)
    }
  }

  const handleProtectionOffersChange = (nextOffers) => {
    setProtectionOffers(nextOffers)
    if (!nextOffers.plus) setPlusAddOn('')
    if (!nextOffers.max) setMaxAddOn('')
  }

  const flushPendingPricing = async (carId) => {
    if (!carId) return false

    if (baseDailyPriceDirty) {
      const ok = await saveBaseDailyPrice({ silent: true })
      if (!ok) {
        toast('Save a valid daily rental rate before submitting.', 'error')
        return false
      }
    }

    if (protectionPricingDirty) {
      const freshFaresRes = await getHostCarDailyFares(carId)
      const freshFares = freshFaresRes.data.data || []
      await saveProtectionPricing({ silent: true, faresOverride: freshFares })
    }

    return true
  }

  const resetSpecialDraft = () => {
    setSpecialDraft({
      name: '',
      date_from: '',
      date_to: '',
      type: 'charge',
      value_mode: 'percentage',
      value_percent_bips: 1000,
      value_fixed_cents: 0,
    })
    setEditingSpecialPriceId(null)
  }

  const resetExtraDraft = () => {
    setExtraDraft({ charge_per_extra_hour_euros: '' })
    setEditingExtraHourFareId(null)
  }

  const resetLocationFeeDraft = () => {
    setLocationFeeDraft({
      pickup_location_id: '',
      dropoff_location_id: '',
      cost_euros: '',
      is_one_way_fee: false,
      multiply_by_days: false,
    })
    setEditingLocationFeeId(null)
  }

  const resetOohFeeDraft = () => {
    setOohFeeDraft({
      name: 'Out-of-hours',
      time_from: '20:00',
      time_to: '08:00',
      applies_to: 'both',
      pickup_cost_euros: '',
      dropoff_cost_euros: '',
      location_ids: [],
    })
    setEditingOohFeeId(null)
  }

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
            <div className="host-field"><label htmlFor="host-car-name">Name</label><input id="host-car-name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} /></div>
            <div className="host-field"><label>Main category</label>
              <HostSelect
                value={String(form.main_category_id || '')}
                onChange={(v) => {
                  const main = catalog.mainCategories.find((c) => String(c.id) === v)
                  const next = { ...form, main_category_id: v, sub_category_id: '' }
                  if (main?.slug === 'campervan' && (!form.sleeps || form.sleeps === 0)) {
                    next.sleeps = form.seats || 2
                  }
                  if (main?.slug === 'car') {
                    next.sleeps = 0
                  }
                  setForm(next)
                }}
                options={catalog.mainCategories.map((c) => ({ value: String(c.id), label: c.name }))}
                placeholder="Select main category"
                ariaLabel="Main category"
              />
            </div>
            <div className="host-field" id="host-car-sub-category"><label>Sub category</label>
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
            <div className="host-images-section">
              {!recordId && (pendingMainPreview || pendingDetails.length > 0) && (
                <p className="host-capacity-hint">Photos are previewed locally and upload when you save the vehicle.</p>
              )}
              <HostImageDropzone
                fieldId="host-car-main-image"
                label="Main image"
                hint={`Used on search cards and browse results only, not shown in the listing photo gallery. ${HOST_IMAGE_FORMAT_HINT}`}
                previewSrc={mainImagePreview}
                emptyLabel="Upload main photo"
                onSelect={handleMainImage}
                onInvalid={toastInvalidImageFiles}
                onClear={pendingMainPreview ? clearMainImage : undefined}
              />
              <HostImageGallery
                fieldId="host-car-detail-images"
                label="Detail images"
                hint={`Shown in the listing page gallery. Add at least ${HOST_MIN_DETAIL_IMAGES} photos of the interior, exterior, and key features so guests can browse your listing. ${HOST_IMAGE_FORMAT_HINT}`}
                minCount={HOST_MIN_DETAIL_IMAGES}
                items={[
                  ...form.details_image_paths.map((path) => ({
                    key: path,
                    src: resolveStorageUrl(path),
                    onRemove: () => removeDetailsImage(path),
                  })),
                  ...pendingDetails.map((item) => ({
                    key: item.id,
                    src: item.previewUrl,
                    onRemove: () => removePendingDetail(item.id),
                  })),
                ]}
                onSelect={handleDetailsImages}
                onInvalid={toastInvalidImageFiles}
              />
            </div>
          </>
        )}
        {step === 1 && (
          <>
            <p className="host-step-note">You can save a draft at any time. Transmission, fuel, drive system, seats and bags are required before submit. Campervans also need sleeps (berths).</p>
            <div className="host-field" id="host-car-transmission"><label>Transmission</label>
              <HostSelect
                value={form.transmission}
                onChange={(v) => setForm({ ...form, transmission: v })}
                options={TRANSMISSION_OPTIONS}
                ariaLabel="Transmission"
              />
            </div>
            <div className="host-field" id="host-car-fuel-type"><label>Fuel type</label>
              <HostSelect
                value={form.fuel_type}
                onChange={(v) => setForm({ ...form, fuel_type: v })}
                options={FUEL_OPTIONS}
                ariaLabel="Fuel type"
              />
            </div>
            <div className="host-field" id="host-car-drive-type"><label>Drive system</label>
              <HostSelect
                value={form.drive_type}
                onChange={(v) => setForm({ ...form, drive_type: v })}
                options={DRIVE_OPTIONS}
                ariaLabel="Drive system"
              />
            </div>
            <div className="host-field">
              <label>Capacity</label>
              <div className={`host-capacity-grid${isCampervan ? '' : ' host-capacity-grid--car'}`}>
                <div>
                  <label className="host-capacity-label" htmlFor="car-seats">
                    Seats
                  </label>
                  <input
                    id="car-seats"
                    type="number"
                    min={1}
                    max={50}
                    value={form.seats}
                    onChange={(e) => setCapacity('seats', e.target.value, 50)}
                  />
                </div>
                {isCampervan && (
                  <div>
                    <label className="host-capacity-label" htmlFor="car-sleeps">
                      Sleeps (berths)
                    </label>
                    <input
                      id="car-sleeps"
                      type="number"
                      min={1}
                      max={20}
                      value={form.sleeps}
                      onChange={(e) => setCapacity('sleeps', e.target.value, 20)}
                    />
                  </div>
                )}
                <div>
                  <label className="host-capacity-label" htmlFor="car-bags">
                    Bags
                  </label>
                  <input
                    id="car-bags"
                    type="number"
                    min={1}
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
              <p className="host-field-inline-hint">Included with the vehicle at no extra charge.</p>
              <HostIconMultiSelect
                items={catalog.characteristics}
                selectedIds={form.characteristic_ids}
                onToggle={(id) => toggleId('characteristic_ids', id)}
                placeholder="Search characteristics…"
                emptyLabel="No characteristics match your search."
              />
            </div>
            <div className="host-field" id="host-car-extras">
              <label>Optional extras</label>
              <p className="host-field-inline-hint">Paid add-ons guests can select at checkout. Set your price for each one you offer.</p>
              <HostCarExtrasPanel
                options={catalog.rentalOptions}
                enabledOptions={form.rental_options}
                onChange={(rental_options) => setForm((prev) => ({ ...prev, rental_options }))}
                currency={currency}
              />
            </div>
            <div className="host-field"><label>Rental conditions</label>
              <HostIconMultiSelect
                items={catalog.rentalConditions}
                selectedIds={form.rental_condition_ids}
                onToggle={(id) => toggleId('rental_condition_ids', id)}
                placeholder="Search rental conditions…"
                emptyLabel="No rental conditions match your search."
                showDescription
              />
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
            editingLocationFeeId={editingLocationFeeId}
            editingOohFeeId={editingOohFeeId}
            onAddLocationFee={async () => {
              const duplicate = locationFees.find(
                (fee) => String(fee.pickup_location_id) === String(locationFeeDraft.pickup_location_id)
                  && String(fee.dropoff_location_id) === String(locationFeeDraft.dropoff_location_id)
                  && fee.id !== editingLocationFeeId,
              )
              if (duplicate) {
                toast('A fee for this pickup and drop-off combination already exists. Edit the existing fee instead.', 'error')
                return
              }
              try {
                const payload = {
                  pickup_location_id: Number(locationFeeDraft.pickup_location_id),
                  dropoff_location_id: Number(locationFeeDraft.dropoff_location_id),
                  cost_euros: locationFeeDraft.cost_euros,
                  is_one_way_fee: locationFeeDraft.is_one_way_fee,
                  multiply_by_days: locationFeeDraft.multiply_by_days,
                }
                if (editingLocationFeeId) {
                  await updateHostCarLocationFee(recordId, editingLocationFeeId, payload)
                  toast('Fee updated', 'success')
                } else {
                  await createHostCarLocationFee(recordId, payload)
                  toast('Fee added', 'success')
                }
                getHostCarLocationFees(recordId).then((res) => setLocationFees(res.data.data || []))
                resetLocationFeeDraft()
              } catch (err) {
                toast(err.response?.data?.message || 'Could not save fee', 'error')
              }
            }}
            onEditLocationFee={(fee) => {
              setEditingLocationFeeId(fee.id)
              setLocationFeeDraft({
                pickup_location_id: String(fee.pickup_location_id),
                dropoff_location_id: String(fee.dropoff_location_id),
                cost_euros: fee.cost_euros ?? fee.cost_cents / 100,
                is_one_way_fee: !!fee.is_one_way_fee,
                multiply_by_days: !!fee.multiply_by_days,
              })
            }}
            onCancelLocationFeeEdit={resetLocationFeeDraft}
            onDeleteLocationFee={async (feeId) => {
              await deleteHostCarLocationFee(recordId, feeId)
              setLocationFees((prev) => prev.filter((f) => f.id !== feeId))
              if (editingLocationFeeId === feeId) resetLocationFeeDraft()
            }}
            onAddOohFee={async () => {
              const duplicate = outOfHoursFees.find(
                (fee) => fee.time_from?.slice(0, 5) === oohFeeDraft.time_from
                  && fee.time_to?.slice(0, 5) === oohFeeDraft.time_to
                  && fee.applies_to === oohFeeDraft.applies_to
                  && fee.id !== editingOohFeeId,
              )
              if (duplicate) {
                toast('An out-of-hours fee with the same time window already exists. Edit the existing fee instead.', 'error')
                return
              }
              try {
                const payload = {
                  ...oohFeeDraft,
                  location_ids: (oohFeeDraft.location_ids || []).map(Number),
                }
                if (editingOohFeeId) {
                  await updateHostCarOutOfHoursFee(recordId, editingOohFeeId, payload)
                  toast('Out-of-hours fee updated', 'success')
                } else {
                  await createHostCarOutOfHoursFee(recordId, payload)
                  toast('Out-of-hours fee added', 'success')
                }
                getHostCarOutOfHoursFees(recordId).then((res) => setOutOfHoursFees(res.data.data || []))
                resetOohFeeDraft()
              } catch (err) {
                toast(err.response?.data?.message || 'Could not save fee', 'error')
              }
            }}
            onEditOohFee={(fee) => {
              setEditingOohFeeId(fee.id)
              setOohFeeDraft({
                name: fee.name || 'Out-of-hours',
                time_from: normalizeTimeString(fee.time_from?.slice(0, 5) || fee.time_from || ''),
                time_to: normalizeTimeString(fee.time_to?.slice(0, 5) || fee.time_to || ''),
                applies_to: fee.applies_to,
                pickup_cost_euros: fee.pickup_cost_euros ?? (fee.pickup_cost_cents || 0) / 100,
                dropoff_cost_euros: fee.dropoff_cost_euros ?? (fee.dropoff_cost_cents || 0) / 100,
                location_ids: (fee.location_ids || []).map(String),
              })
            }}
            onCancelOohFeeEdit={resetOohFeeDraft}
            onDeleteOohFee={async (feeId) => {
              await deleteHostCarOutOfHoursFee(recordId, feeId)
              setOutOfHoursFees((prev) => prev.filter((f) => f.id !== feeId))
              if (editingOohFeeId === feeId) resetOohFeeDraft()
            }}
            onCreateLocation={async (payload) => {
              const res = await createHostLocation(payload)
              refreshLocations()
              return res.data.data
            }}
          />
        )}
        {step === 3 && (
          recordId ? (
            <div className="host-field">
              <label>Units available</label>
              <p className="host-capacity-hint">How many identical copies of this vehicle can be booked at the same time?</p>
              <div className="host-unit-stepper" id="host-car-units">
                <button
                  type="button"
                  className="host-btn secondary"
                  disabled={unitsBusy || units.length <= 1}
                  aria-label="Remove one unit"
                  onClick={async () => {
                    const lastUnit = units[units.length - 1]
                    if (!lastUnit) return
                    setUnitsBusy(true)
                    try {
                      await deleteHostCarUnit(recordId, lastUnit.id)
                      loadPricing(recordId)
                    } catch (err) {
                      toast(err.response?.data?.message || 'Could not remove unit', 'error')
                    } finally {
                      setUnitsBusy(false)
                    }
                  }}
                >
                  −
                </button>
                <span className="host-unit-stepper__count" aria-live="polite">{units.length}</span>
                <button
                  type="button"
                  className="host-btn secondary"
                  disabled={unitsBusy}
                  aria-label="Add one unit"
                  onClick={async () => {
                    setUnitsBusy(true)
                    try {
                      await createHostCarUnit(recordId, {})
                      loadPricing(recordId)
                    } catch (err) {
                      toast(err.response?.data?.message || 'Could not add unit', 'error')
                    } finally {
                      setUnitsBusy(false)
                    }
                  }}
                >
                  +
                </button>
              </div>
            </div>
          ) : <p className="text-sm text-slate-500">Save the vehicle first to manage units.</p>
        )}
        {step === 4 && (
          recordId ? (
            <div className="host-pricing">
              <p className="host-step-note">
                Enter your standard <strong>daily price</strong> first, then optionally add duration tiers and seasonal rates below.
                {' '}All amounts are in <strong>{currency.code}</strong>, change this in <Link to="/host/settings">Settings</Link> if needed.
              </p>
              {/* Daily rate */}
              <section className="host-fare-section">
                <div className="host-fare-head">
                  <span className="host-fare-head-icon"><CalendarDays size={18} /></span>
                  <div className="host-fare-head-text">
                    <h3>Daily rental rate</h3>
                    <p>What you charge per day for a standard booking.</p>
                  </div>
                </div>
                <div className="host-field host-field--inline-rate">
                  <label htmlFor="base-daily-price">Daily price</label>
                  <div className="host-addon-input">
                    <span className="host-addon-input__prefix">{currency.inputPrefix}</span>
                    <input
                      id="base-daily-price"
                      type="number"
                      min={0}
                      placeholder={`e.g. ${currency.exampleAmounts.dailyRate}`}
                      value={baseDailyPrice}
                      onChange={(e) => setBaseDailyPrice(e.target.value)}
                    />
                    <span className="host-addon-input__suffix">/ day</span>
                  </div>
                </div>
                <button
                  type="button"
                  className="host-btn primary"
                  disabled={baseDailySaving || !standardPriceType}
                  onClick={() => saveBaseDailyPrice()}
                >
                  {baseDailySaving ? 'Saving…' : baseDailyFare ? 'Update daily rate' : 'Save daily rate'}
                </button>
                {baseDailyFare && (
                  <p className="host-capacity-hint">Current rate: {currency.formatCents(baseDailyFare.price_per_day_cents)}/day</p>
                )}
                {baseDailyPriceDirty && (
                  <p className="host-field-hint">You have unsaved daily rate changes, save before leaving or submit to auto-save.</p>
                )}
              </section>

              <HostDisclosure
                title="Duration tiers (optional)"
                hint="Offer a lower daily rate when guests book for longer, e.g. 7–14 days."
                count={durationTiers.length}
                defaultOpen={durationTiers.length > 0}
              >
                <p className="host-capacity-hint">These override your standard daily price for bookings within the day range.</p>
                <div className="grid grid-cols-3 gap-3">
                  <div className="host-field"><label>From day</label><input type="number" min={1} className={tierRangeInvalid ? 'has-error' : ''} value={tierDraft.from_days} onChange={(e) => setTierDraft({ ...tierDraft, from_days: Number(e.target.value) })} /></div>
                  <div className="host-field"><label>To day</label><input type="number" min={1} className={tierRangeInvalid ? 'has-error' : ''} value={tierDraft.to_days} onChange={(e) => setTierDraft({ ...tierDraft, to_days: Number(e.target.value) })} /></div>
                  <div className="host-field"><label>{currency.amountLabel('/ day')}</label><input type="number" min={0} className={(tierRangeInvalid || tierPriceTooHigh || tierPriceZero) ? 'has-error' : ''} value={tierDraft.price_per_day_euros} onChange={(e) => { setTierDraft({ ...tierDraft, price_per_day_euros: e.target.value === '' ? '' : Number(e.target.value) }); setTierValidationError('') }} placeholder={`e.g. ${Math.round((currency.exampleAmounts.dailyRate || 95) * 0.85)}`} /></div>
                </div>
                {tierRangeInvalid && (
                  <p className="host-field-error"><AlertCircle size={14} /> “From day” must be lower than “To day”.</p>
                )}
                {overlappingTier && !tierRangeInvalid && (
                  <p className="host-field-error"><AlertCircle size={14} /> This range overlaps with Days {overlappingTier.from_days}–{overlappingTier.to_days}. Adjust the dates or remove the existing tier first.</p>
                )}
                {tierPriceTooHigh && baseDailyFare && (
                  <p className="host-field-error"><AlertCircle size={14} /> Duration tiers must be cheaper than your standard daily rate ({currency.formatCents(baseDailyFare.price_per_day_cents)}/day).</p>
                )}
                {tierPriceZero && (
                  <p className="host-field-error"><AlertCircle size={14} /> Enter a daily rate greater than zero.</p>
                )}
                {tierValidationError && !tierRangeInvalid && !overlappingTier && !tierPriceTooHigh && !tierPriceZero && (
                  <p className="host-field-error"><AlertCircle size={14} /> {tierValidationError}</p>
                )}
                <button
                  type="button"
                  className="host-btn-add"
                  disabled={!standardPriceType || !baseDailyFare || tierRangeInvalid}
                  onClick={addDurationTier}
                >
                  <Plus size={16} /> Add duration tier
                </button>
                {durationTiers.length > 0 && (
                  <ul className="host-fare-list">
                    {durationTiers.map((f) => (
                      <li key={f.id} className="host-fare-tag">
                        <CalendarDays size={14} className="host-fare-tag-icon" />
                        <span className="host-fare-tag-text">
                          Days {f.from_days}–{f.to_days} <ArrowRight size={12} /> <strong>{currency.formatCents(f.price_per_day_cents)}/day</strong>
                        </span>
                        <button type="button" className="host-fare-tag-remove" aria-label="Remove tier" title="Remove tier" onClick={async () => { await deleteHostCarDailyFare(recordId, f.id); loadPricing(recordId) }}><Trash2 size={14} /></button>
                      </li>
                    ))}
                  </ul>
                )}
              </HostDisclosure>

              <HostDisclosure
                title="Seasonal prices (optional)"
                hint="Raise or lower your daily rate for specific date ranges, e.g. summer peak or winter discount."
                count={specialPrices.length}
                defaultOpen={specialPrices.length > 0}
              >
                <div className="grid grid-cols-2 gap-3">
                  <div className="host-field"><label>Name</label><input value={specialDraft.name} onChange={(e) => setSpecialDraft({ ...specialDraft, name: e.target.value })} placeholder="e.g. Summer peak" /></div>
                  <div className="host-field"><label>Type</label>
                    <HostSelect
                      value={specialDraft.type}
                      onChange={(v) => setSpecialDraft({ ...specialDraft, type: v })}
                      options={[
                        { value: 'charge', label: 'Surcharge' },
                        { value: 'discount', label: 'Discount' },
                      ]}
                      ariaLabel="Seasonal price type"
                    />
                  </div>
                  <div className="host-field"><label>From date</label><HostDatePicker value={specialDraft.date_from} onChange={(v) => setSpecialDraft({ ...specialDraft, date_from: v })} /></div>
                  <div className="host-field"><label>To date</label><HostDatePicker value={specialDraft.date_to} onChange={(v) => setSpecialDraft({ ...specialDraft, date_to: v })} minDate={specialDraft.date_from ? new Date(specialDraft.date_from) : undefined} /></div>
                  <div className="host-field"><label>Adjustment</label>
                    <HostSelect
                      value={specialDraft.value_mode}
                      onChange={(v) => setSpecialDraft({ ...specialDraft, value_mode: v })}
                      options={[
                        { value: 'percentage', label: 'Percentage' },
                        { value: 'fixed', label: 'Fixed amount' },
                      ]}
                      ariaLabel="Seasonal adjustment type"
                    />
                  </div>
                  {specialDraft.value_mode === 'percentage' ? (
                    <div className="host-field"><label>Percentage</label><input type="number" min={0} max={specialDraft.type === 'discount' ? 100 : 200} step={0.1} value={specialDraft.value_percent_bips / 100} onChange={(e) => {
                      const raw = Number(e.target.value)
                      const max = specialDraft.type === 'discount' ? 100 : 200
                      const clamped = Math.min(max, Math.max(0, raw))
                      setSpecialDraft({ ...specialDraft, value_percent_bips: Math.round(clamped * 100) })
                    }} /></div>
                  ) : (
                    <div className="host-field">
                      <label>{seasonalFixedAmountLabel}</label>
                      <input type="number" min={0} value={specialDraft.value_fixed_cents ? specialDraft.value_fixed_cents / 100 : ''} onChange={(e) => {
                        setSpecialDraft({ ...specialDraft, value_fixed_cents: e.target.value === '' ? 0 : Math.round(Number(e.target.value) * 100) })
                      }} />
                    </div>
                  )}
                </div>
                {seasonalValidationError && (
                  <p className="host-field-error"><AlertCircle size={14} /> {seasonalValidationError}</p>
                )}
                <div className="host-fare-actions">
                  <button type="button" className="host-btn-add" disabled={!specialDraft.name || !specialDraft.date_from || !specialDraft.date_to || !!seasonalValidationError} onClick={async () => {
                    if (seasonalValidationError) {
                      toast(seasonalValidationError, 'error')
                      return
                    }
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
                      if (editingSpecialPriceId) {
                        const res = await updateHostCarSpecialPrice(recordId, editingSpecialPriceId, payload)
                        toast('Seasonal price updated', 'success')
                        setSpecialPrices((prev) => prev.map((row) => (row.id === editingSpecialPriceId ? res.data.data : row)))
                      } else {
                        const res = await addHostCarSpecialPrice(recordId, payload)
                        toast('Seasonal price added', 'success')
                        setSpecialPrices((prev) => [...prev, res.data.data])
                      }
                      resetSpecialDraft()
                      await loadPricing(recordId)
                    } catch (err) {
                      toast(err.response?.data?.message || 'Could not save seasonal price', 'error')
                    }
                  }}>
                    <Plus size={16} /> {editingSpecialPriceId ? 'Update seasonal price' : 'Add seasonal price'}
                  </button>
                  {editingSpecialPriceId && (
                    <button type="button" className="host-btn secondary" onClick={resetSpecialDraft}>Cancel edit</button>
                  )}
                </div>
                {specialPrices.length > 0 && (
                  <ul className="host-fare-list">
                    {specialPrices.map((sp) => (
                      <li key={sp.id} className="host-fare-tag">
                        <CalendarDays size={14} className="host-fare-tag-icon" />
                        <span className="host-fare-tag-text">
                          <em>{sp.name}</em>
                          {' '}
                          {sp.type === 'discount' ? 'Discount' : 'Surcharge'}{' '}
                          {sp.value_mode === 'percentage'
                            ? `${(sp.value_percent_bips / 100).toFixed(1)}%`
                            : `${currency.formatCents(sp.value_fixed_cents || 0)}/day`}
                          {' '}({formatDate(sp.date_from)} → {formatDate(sp.date_to)})
                        </span>
                        <button type="button" className="host-btn secondary host-btn-compact" onClick={() => {
                          setEditingSpecialPriceId(sp.id)
                          setSpecialDraft({
                            name: sp.name,
                            date_from: sp.date_from,
                            date_to: sp.date_to,
                            type: sp.type,
                            value_mode: sp.value_mode,
                            value_percent_bips: sp.value_percent_bips || 0,
                            value_fixed_cents: sp.value_fixed_cents || 0,
                          })
                        }}>Edit</button>
                        <button type="button" className="host-fare-tag-remove" aria-label="Remove seasonal price" title="Remove" onClick={async () => { await removeHostCarSpecialPrice(recordId, sp.id); if (editingSpecialPriceId === sp.id) resetSpecialDraft(); loadPricing(recordId) }}><Trash2 size={14} /></button>
                      </li>
                    ))}
                  </ul>
                )}
              </HostDisclosure>

              <HostCarProtectionPlansPanel
                priceTypes={catalog.priceTypes}
                offers={protectionOffers}
                onChangeOffers={handleProtectionOffersChange}
                baseDailyFare={baseDailyFare}
              />

              <section className="host-fare-section">
                <div className="host-fare-head">
                  <span className="host-fare-head-icon"><Shield size={18} /></span>
                  <div className="host-fare-head-text">
                    <h3>Protection upgrade pricing</h3>
                    <p>Set the daily add-on price for each upgrade tier you offer above.</p>
                  </div>
                </div>
                {!protectionOffers.plus && !protectionOffers.max && (
                  <p className="host-field-hint">Select {plusTierName} or {maxTierName} above to set upgrade prices.</p>
                )}
                <div className="grid grid-cols-2 gap-3">
                  {protectionOffers.plus && (
                    <div className="host-field">
                      <label>{plusTierName}</label>
                      <span className="host-field-note">Lower guest deposit</span>
                      <div className="host-addon-input">
                        <span className="host-addon-input__prefix">+ {currency.inputPrefix}</span>
                        <input type="number" min={0} placeholder={`e.g. ${currency.exampleAmounts.enhancedCoverage}`} value={plusAddOn} onChange={(e) => setPlusAddOn(e.target.value)} />
                        <span className="host-addon-input__suffix">/ day</span>
                      </div>
                    </div>
                  )}
                  {protectionOffers.max && (
                    <div className="host-field">
                      <label>{maxTierName}</label>
                      <span className="host-field-note">Zero guest deposit</span>
                      <div className="host-addon-input">
                        <span className="host-addon-input__prefix">+ {currency.inputPrefix}</span>
                        <input type="number" min={0} placeholder={`e.g. ${currency.exampleAmounts.fullCoverage}`} value={maxAddOn} onChange={(e) => setMaxAddOn(e.target.value)} />
                        <span className="host-addon-input__suffix">/ day</span>
                      </div>
                    </div>
                  )}
                </div>
                {(protectionOffers.plus || protectionOffers.max) && (plusAddOn || maxAddOn) && baseDailyFare && (
                  <div className="host-fare-preview">
                    <span className="host-fare-preview-label">Guest will see</span>
                    {protectionOffers.plus && plusAddOn && (
                      <span className="host-fare-tag is-preview">
                        <span className="host-fare-tag-text">{plusTierName} <ArrowRight size={12} /> <strong>+{currency.formatAmount(Number(plusAddOn))}/day</strong> on top of your rate</span>
                      </span>
                    )}
                    {protectionOffers.max && maxAddOn && (
                      <span className="host-fare-tag is-preview">
                        <span className="host-fare-tag-text">{maxTierName} <ArrowRight size={12} /> <strong>+{currency.formatAmount(Number(maxAddOn))}/day</strong> on top of your rate</span>
                      </span>
                    )}
                  </div>
                )}
                <button
                  type="button"
                  className="host-btn secondary"
                  disabled={protectionSaving || !baseDailyFare || !protectionPricingDirty}
                  onClick={() => saveProtectionPricing()}
                >
                  {protectionSaving ? 'Saving…' : 'Save protection settings'}
                </button>
                {protectionPricingDirty && (
                  <p className="host-field-hint">You have unsaved protection changes, save or submit to auto-save.</p>
                )}
              </section>

              <HostDisclosure
                title="Advanced rates (optional)"
                hint="Overtime charge when a guest keeps the vehicle past their booked window."
                count={hostExtraHourFares.length}
                defaultOpen={hostExtraHourFares.length > 0}
              >
              {/* Extra-hour fares */}
              <section className="host-fare-section">
                <div className="host-fare-head">
                  <span className="host-fare-head-icon"><Timer size={18} /></span>
                  <div className="host-fare-head-text">
                    <h3>Extra-hour charge</h3>
                    <p>Amount charged for each hour a guest keeps the vehicle beyond their booked window.</p>
                  </div>
                </div>
                <div className="host-field"><label>{currency.amountLabel('/ extra hour')}</label><input type="number" min={0} placeholder={`e.g. ${currency.exampleAmounts.extraHour}`} value={extraDraft.charge_per_extra_hour_euros} onChange={(e) => setExtraDraft({ ...extraDraft, charge_per_extra_hour_euros: e.target.value === '' ? '' : Number(e.target.value) })} /></div>
                <div className="host-fare-preview">
                  <span className="host-fare-preview-label">Preview</span>
                  <span className="host-fare-tag is-preview">
                    <Timer size={14} className="host-fare-tag-icon" />
                    <span className="host-fare-tag-text"><strong>{currency.formatAmount(Number(extraDraft.charge_per_extra_hour_euros || 0))}</strong> / extra hour</span>
                  </span>
                </div>
                <div className="host-fare-actions">
                  <button type="button" className="host-btn-add" disabled={!standardPriceType || !extraDraft.charge_per_extra_hour_euros} onClick={async () => {
                    try {
                      const payload = buildStandardFarePayload({
                        ...extraDraft,
                        charge_per_extra_hour_euros: Number(extraDraft.charge_per_extra_hour_euros),
                      })
                      if (editingExtraHourFareId) {
                        await updateHostCarExtraHourFare(recordId, editingExtraHourFareId, payload)
                        toast('Extra-hour charge updated', 'success')
                      } else if (hostExtraHourFares.length > 0) {
                        await updateHostCarExtraHourFare(recordId, hostExtraHourFares[0].id, payload)
                        toast('Extra-hour charge updated', 'success')
                      } else {
                        await createHostCarExtraHourFare(recordId, payload)
                        toast('Extra-hour charge added', 'success')
                      }
                      resetExtraDraft()
                      loadPricing(recordId)
                    } catch (err) {
                      toast(err.response?.data?.message || 'Could not save extra-hour charge', 'error')
                    }
                  }}>
                    <Plus size={16} /> {editingExtraHourFareId ? 'Update extra-hour charge' : 'Add extra-hour charge'}
                  </button>
                  {editingExtraHourFareId && (
                    <button type="button" className="host-btn secondary" onClick={resetExtraDraft}>Cancel edit</button>
                  )}
                </div>
                {hostExtraHourFares.length > 0 && (
                  <ul className="host-fare-list">
                    {hostExtraHourFares.map((f) => (
                      <li key={f.id} className="host-fare-tag">
                        <Timer size={14} className="host-fare-tag-icon" />
                        <span className="host-fare-tag-text">
                          <strong>{currency.formatCents(f.charge_per_extra_hour_cents)}</strong> / extra hour
                        </span>
                        <button type="button" className="host-btn secondary host-btn-compact" onClick={() => {
                          setEditingExtraHourFareId(f.id)
                          setExtraDraft({ charge_per_extra_hour_euros: f.charge_per_extra_hour_cents / 100 })
                        }}>Edit</button>
                        <button type="button" className="host-fare-tag-remove" aria-label="Remove charge" title="Remove charge" onClick={async () => { await deleteHostCarExtraHourFare(recordId, f.id); if (editingExtraHourFareId === f.id) resetExtraDraft(); loadPricing(recordId) }}><Trash2 size={14} /></button>
                      </li>
                    ))}
                  </ul>
                )}
              </section>
              </HostDisclosure>
            </div>
          ) : <p className="text-sm text-slate-500">Save the vehicle first to manage pricing.</p>
        )}
        {step === 5 && (
          recordId ? (
            <>
              <p className="host-step-note">Block dates when the vehicle is unavailable for booking, maintenance, personal use, etc.</p>
              <h3 className="mb-2 font-semibold text-brand-950">Blocked dates</h3>
              <div className="grid grid-cols-2 gap-3">
                <div className="host-field"><label>From</label><HostDateTimePicker value={blockDraft.starts_at} onChange={(v) => setBlockDraft({ ...blockDraft, starts_at: v })} minDate={new Date()} placeholder="Select start date & time" /></div>
                <div className="host-field"><label>To</label><HostDateTimePicker value={blockDraft.ends_at} onChange={(v) => setBlockDraft({ ...blockDraft, ends_at: v })} minDate={blockDraft.starts_at ? new Date(blockDraft.starts_at) : new Date()} placeholder="Select end date & time" /></div>
                <div className="host-field">
                  <label>Units blocked</label>
                  <input
                    type="number"
                    min={1}
                    max={Math.max(1, units.length || form.units_available || 1)}
                    value={blockDraft.units_blocked}
                    onChange={(e) => {
                      const maxUnits = Math.max(1, units.length || form.units_available || 1)
                      const next = Math.min(maxUnits, Math.max(1, Number(e.target.value) || 1))
                      setBlockDraft({ ...blockDraft, units_blocked: next })
                    }}
                  />
                  <p className="host-field-hint">You have {Math.max(1, units.length || form.units_available || 1)} unit(s) in your fleet.</p>
                </div>
                <div className="host-field"><label>Notes</label><input value={blockDraft.notes} onChange={(e) => setBlockDraft({ ...blockDraft, notes: e.target.value })} /></div>
              </div>
              <button type="button" className="host-btn secondary" disabled={!blockDraft.starts_at || !blockDraft.ends_at} onClick={addAvailabilityBlock}>Add block</button>
              <ul className="mt-3 space-y-2 text-sm">
                {availability.map((b) => (
                  <li key={b.id} className="flex justify-between">
                    <span>{formatDate(b.starts_at)} → {formatDate(b.ends_at)} ({b.units_blocked} unit) {b.source === 'manual' ? '' : `[${b.source}]`}</span>
                    {b.source === 'manual' && <button type="button" className="host-btn danger" onClick={async () => { await removeHostCarAvailability(recordId, b.id); loadPricing(recordId) }}>Remove</button>}
                  </li>
                ))}
              </ul>
            </>
          ) : <p className="text-sm text-slate-500">Save the vehicle first to manage availability.</p>
        )}
        {step === 6 && (
          <div>
            <p className="host-step-note">
              {isReady
                ? 'Everything looks ready. Save your vehicle, then submit for admin approval.'
                : 'Finish the checklist below, then save and submit. Each row takes you straight to the right step.'}
            </p>
            <HostReadinessChecklist items={readinessItems} onGoTo={goToReadinessItem} />
          </div>
        )}
        <div className="host-wizard-actions">
          <div className="host-wizard-actions__left">
            <Link to="/host/cars" className="host-btn secondary">Back to list</Link>
          </div>
          <div className="host-wizard-actions__nav">
            <button
              type="button"
              className="host-btn secondary host-btn-step"
              disabled={step === 0}
              aria-label="Previous step"
              onClick={() => setStep(step - 1)}
            >
              <ChevronLeft size={18} strokeWidth={2} aria-hidden />
            </button>
            <button
              type="button"
              className="host-btn secondary host-btn-step"
              disabled={step >= STEPS.length - 1}
              aria-label="Next step"
              onClick={() => setStep(step + 1)}
            >
              <ChevronRight size={18} strokeWidth={2} aria-hidden />
            </button>
          </div>
          <div className="host-wizard-actions__right">
            <button type="button" className="host-btn primary" disabled={saving} onClick={save}>{saving ? 'Saving…' : (step === STEPS.length - 1 ? 'Save draft' : 'Save')}</button>
            {step === STEPS.length - 1 && recordId && ['draft', 'rejected'].includes(status) && (
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
          </div>
        </div>
      </div>
    </div>
  )
}
