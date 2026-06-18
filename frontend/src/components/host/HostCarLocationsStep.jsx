import { useState } from 'react'
import HostDisclosure from './HostDisclosure'
import HostMultiSelect from './HostMultiSelect'
import HostSelect from './HostSelect'
import HostTimePicker from './HostTimePicker'
import { useHostCurrency } from '../../hooks/useHostCurrency'
import { useToast } from '../../context/ToastContext'

function toLocationOptions(locations = []) {
  return locations.map((loc) => ({
    value: String(loc.id),
    label: loc.name,
    subtitle: loc.address || undefined,
  }))
}

function CustomLocationForm({
  customLocationDraft,
  setCustomLocationDraft,
  creatingLocation,
  onCreate,
}) {
  return (
    <>
      <div className="grid grid-cols-2 gap-3">
        <div className="host-field">
          <label>Location name</label>
          <input
            value={customLocationDraft.name}
            onChange={(e) => setCustomLocationDraft({ ...customLocationDraft, name: e.target.value })}
            placeholder="e.g. My guesthouse driveway"
          />
        </div>
        <div className="host-field">
          <label>Address (optional)</label>
          <input
            value={customLocationDraft.address}
            onChange={(e) => setCustomLocationDraft({ ...customLocationDraft, address: e.target.value })}
            placeholder="Street or area"
          />
        </div>
      </div>
      <button
        type="button"
        className="host-btn secondary"
        disabled={creatingLocation || !customLocationDraft.name.trim()}
        onClick={onCreate}
      >
        {creatingLocation ? 'Adding…' : 'Add location'}
      </button>
    </>
  )
}

export default function HostCarLocationsStep({
  catalogLocations,
  form,
  setForm,
  selectedPickupLocations,
  selectedDropoffLocations,
  canConfigureTimes,
  recordId,
  locationFees,
  outOfHoursFees,
  locationFeeDraft,
  setLocationFeeDraft,
  oohFeeDraft,
  setOohFeeDraft,
  editingLocationFeeId,
  editingOohFeeId,
  onAddLocationFee,
  onEditLocationFee,
  onCancelLocationFeeEdit,
  onDeleteLocationFee,
  onAddOohFee,
  onEditOohFee,
  onCancelOohFeeEdit,
  onDeleteOohFee,
  onCreateLocation,
}) {
  const currency = useHostCurrency()
  const { toast } = useToast()
  const [customLocationDraft, setCustomLocationDraft] = useState({ name: '', address: '' })
  const [creatingLocation, setCreatingLocation] = useState(false)
  const allLocationOptions = toLocationOptions(catalogLocations)
  const pickupOptions = toLocationOptions(selectedPickupLocations)
  const dropoffOptions = toLocationOptions(selectedDropoffLocations)
  const oohLocationOptions = toLocationOptions(
    [...selectedPickupLocations, ...selectedDropoffLocations].filter(
      (loc, idx, arr) => arr.findIndex((x) => x.id === loc.id) === idx,
    ),
  )

  const handleCreateLocation = async () => {
    if (!customLocationDraft.name.trim()) {
      toast('Enter a location name.', 'error')
      return
    }
    if (!onCreateLocation) return
    setCreatingLocation(true)
    try {
      const location = await onCreateLocation({
        name: customLocationDraft.name.trim(),
        address: customLocationDraft.address.trim() || null,
      })
      const id = String(location.id)
      setForm({
        ...form,
        pickup_location_ids: [...form.pickup_location_ids, id],
        dropoff_location_ids: [...form.dropoff_location_ids, id],
      })
      setCustomLocationDraft({ name: '', address: '' })
      toast('Location added', 'success')
    } catch (err) {
      toast(err.response?.data?.message || 'Could not add location', 'error')
    } finally {
      setCreatingLocation(false)
    }
  }

  if (catalogLocations.length === 0 && !onCreateLocation) {
    return (
      <p className="host-field-hint">
        No pickup or drop-off locations are available yet. Ask an admin to add active locations in Impact Rent.
      </p>
    )
  }

  if (catalogLocations.length === 0) {
    return (
      <>
        <p className="host-step-note">Add a custom pickup and drop-off location below, then set all four time windows (required before submit).</p>
        <HostDisclosure title="Add a custom location" hint="Create a pickup or drop-off point for this vehicle." defaultOpen>
          <CustomLocationForm
            customLocationDraft={customLocationDraft}
            setCustomLocationDraft={setCustomLocationDraft}
            creatingLocation={creatingLocation}
            onCreate={handleCreateLocation}
          />
        </HostDisclosure>
      </>
    )
  }

  return (
    <>
      <p className="host-step-note">Choose at least one pickup and one drop-off location, then set all four time windows (required before submit). Location fees are optional.</p>
      <div className="host-locations-grid" id="host-car-locations">
        <div className="host-field">
          <label>Pickup locations</label>
          <p className="host-field-hint">Where guests can collect this vehicle.</p>
          <HostMultiSelect
            value={form.pickup_location_ids}
            onChange={(ids) => setForm({ ...form, pickup_location_ids: ids })}
            options={allLocationOptions}
            placeholder="Select pickup locations"
            ariaLabel="Pickup locations"
          />
        </div>

        <div className="host-field">
          <label>Drop-off locations</label>
          <p className="host-field-hint">Where guests can return this vehicle.</p>
          <HostMultiSelect
            value={form.dropoff_location_ids}
            onChange={(ids) => setForm({ ...form, dropoff_location_ids: ids })}
            options={allLocationOptions}
            placeholder="Select drop-off locations"
            ariaLabel="Drop-off locations"
          />
        </div>
      </div>

      {onCreateLocation && (
        <HostDisclosure title="Add a custom location" hint="Create a pickup or drop-off point that is not in the list yet." defaultOpen={false}>
          <CustomLocationForm
            customLocationDraft={customLocationDraft}
            setCustomLocationDraft={setCustomLocationDraft}
            creatingLocation={creatingLocation}
            onCreate={handleCreateLocation}
          />
        </HostDisclosure>
      )}

      {canConfigureTimes ? (
        <section className="host-subsection" id="host-car-times">
          <div className="host-subsection-head">
            <h3>Pickup & drop-off times <span className="host-required-badge">Required before submit</span></h3>
            <p>Set the pickup and drop-off time windows guests can book for this vehicle.</p>
          </div>
          <div className="host-locations-grid">
            <div className="host-field">
              <label>Pickup from</label>
              <HostTimePicker
                value={form.pickup_time_from}
                onChange={(v) => setForm({ ...form, pickup_time_from: v })}
                placeholder="Select start time"
                ariaLabel="Pickup from"
              />
            </div>
            <div className="host-field">
              <label>Pickup to</label>
              <HostTimePicker
                value={form.pickup_time_to}
                onChange={(v) => setForm({ ...form, pickup_time_to: v })}
                placeholder="Select end time"
                ariaLabel="Pickup to"
              />
            </div>
            <div className="host-field">
              <label>Drop-off from</label>
              <HostTimePicker
                value={form.dropoff_time_from}
                onChange={(v) => setForm({ ...form, dropoff_time_from: v })}
                placeholder="Select start time"
                ariaLabel="Drop-off from"
              />
            </div>
            <div className="host-field">
              <label>Drop-off to</label>
              <HostTimePicker
                value={form.dropoff_time_to}
                onChange={(v) => setForm({ ...form, dropoff_time_to: v })}
                placeholder="Select end time"
                ariaLabel="Drop-off to"
              />
            </div>
          </div>
        </section>
      ) : (
        <p className="host-field-hint">Select at least one pickup and one drop-off location to set times.</p>
      )}

      {recordId ? (
        <>
          <HostDisclosure
            title="Pickup / drop-off fees (optional)"
            hint="Add a flat fee for specific pickup and drop-off combinations."
            count={locationFees.length}
            defaultOpen={locationFees.length > 0}
          >
          <section className="host-fees-section">
            <div className="host-fees-form">
              <div className="host-field">
                <label>Pickup location</label>
                <HostSelect
                  value={locationFeeDraft.pickup_location_id}
                  onChange={(v) => setLocationFeeDraft({ ...locationFeeDraft, pickup_location_id: v })}
                  options={pickupOptions}
                  placeholder="Select pickup"
                  disabled={!pickupOptions.length}
                  ariaLabel="Fee pickup location"
                />
              </div>
              <div className="host-field">
                <label>Drop-off location</label>
                <HostSelect
                  value={locationFeeDraft.dropoff_location_id}
                  onChange={(v) => setLocationFeeDraft({ ...locationFeeDraft, dropoff_location_id: v })}
                  options={dropoffOptions}
                  placeholder="Select drop-off"
                  disabled={!dropoffOptions.length}
                  ariaLabel="Fee drop-off location"
                />
              </div>
              <div className="host-field">
                <label>Fee amount ({currency.code})</label>
                <input
                  type="number"
                  min={0}
                  step={0.01}
                  value={locationFeeDraft.cost_euros}
                  onChange={(e) => setLocationFeeDraft({ ...locationFeeDraft, cost_euros: Number(e.target.value) })}
                />
              </div>
              <div className="host-field">
                <label>One-way fee</label>
                <HostSelect
                  value={locationFeeDraft.is_one_way_fee ? 'yes' : 'no'}
                  onChange={(v) => setLocationFeeDraft({ ...locationFeeDraft, is_one_way_fee: v === 'yes' })}
                  options={[
                    { value: 'no', label: 'No, applies to all bookings' },
                    { value: 'yes', label: 'Yes, only when pickup and drop-off differ' },
                  ]}
                  ariaLabel="One-way fee"
                />
              </div>
              <div className="host-field">
                <label>Multiply by rental days</label>
                <HostSelect
                  value={locationFeeDraft.multiply_by_days ? 'yes' : 'no'}
                  onChange={(v) => setLocationFeeDraft({ ...locationFeeDraft, multiply_by_days: v === 'yes' })}
                  options={[
                    { value: 'no', label: 'No, flat fee per booking' },
                    { value: 'yes', label: 'Yes, fee × number of days' },
                  ]}
                  ariaLabel="Multiply fee by days"
                />
              </div>
            </div>

            <div className="host-fare-actions">
              <button
                type="button"
                className="host-btn secondary"
                disabled={!locationFeeDraft.pickup_location_id || !locationFeeDraft.dropoff_location_id}
                onClick={onAddLocationFee}
              >
                {editingLocationFeeId ? 'Update location fee' : 'Add location fee'}
              </button>
              {editingLocationFeeId && (
                <button type="button" className="host-btn secondary" onClick={onCancelLocationFeeEdit}>
                  Cancel edit
                </button>
              )}
            </div>

            {locationFees.length > 0 && (
              <ul className="host-fee-list">
                {locationFees.map((fee) => (
                  <li key={fee.id} className="host-fee-item">
                    <div className="host-fee-item-main">
                      <span className="host-fee-route">
                        {fee.pickup_location?.name || 'Pickup'} → {fee.dropoff_location?.name || 'Drop-off'}
                      </span>
                      <span className="host-fee-amount">
                        {currency.formatAmount(fee.cost_euros ?? fee.cost_cents / 100)}
                        {fee.multiply_by_days ? ' · per day' : ''}
                        {fee.is_one_way_fee ? ' · one-way' : ''}
                      </span>
                    </div>
                    <div className="host-fee-item-actions">
                      <button type="button" className="host-btn secondary host-btn-compact" onClick={() => onEditLocationFee(fee)}>
                        Edit
                      </button>
                      <button type="button" className="host-btn danger" onClick={() => onDeleteLocationFee(fee.id)}>
                        Remove
                      </button>
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </section>
          </HostDisclosure>

          <HostDisclosure
            title="Out-of-hours fees (optional)"
            hint="Charge extra for pickups or drop-offs outside standard hours."
            count={outOfHoursFees.length}
            defaultOpen={outOfHoursFees.length > 0}
          >
          <section className="host-fees-section">
            <div className="host-fees-form">
              <div className="host-field">
                <label>Name</label>
                <input value={oohFeeDraft.name} onChange={(e) => setOohFeeDraft({ ...oohFeeDraft, name: e.target.value })} />
              </div>
              <div className="host-field">
                <label>Applies to</label>
                <HostSelect
                  value={oohFeeDraft.applies_to}
                  onChange={(v) => setOohFeeDraft({ ...oohFeeDraft, applies_to: v })}
                  options={[
                    { value: 'pickup', label: 'Pick-up only' },
                    { value: 'dropoff', label: 'Drop-off only' },
                    { value: 'both', label: 'Both' },
                  ]}
                  placeholder="Select"
                  ariaLabel="Applies to"
                />
              </div>
              <div className="host-field">
                <label>From time</label>
                <HostTimePicker
                  value={oohFeeDraft.time_from}
                  onChange={(v) => setOohFeeDraft({ ...oohFeeDraft, time_from: v })}
                  placeholder="Select start time"
                  ariaLabel="Out-of-hours from time"
                />
              </div>
              <div className="host-field">
                <label>To time</label>
                <HostTimePicker
                  value={oohFeeDraft.time_to}
                  onChange={(v) => setOohFeeDraft({ ...oohFeeDraft, time_to: v })}
                  placeholder="Select end time"
                  ariaLabel="Out-of-hours to time"
                />
              </div>
              <div className="host-field">
                <label>Pick-up charge ({currency.code})</label>
                <input
                  type="number"
                  min={0}
                  value={oohFeeDraft.pickup_cost_euros}
                  onChange={(e) => setOohFeeDraft({ ...oohFeeDraft, pickup_cost_euros: Number(e.target.value) })}
                />
              </div>
              <div className="host-field">
                <label>Drop-off charge ({currency.code})</label>
                <input
                  type="number"
                  min={0}
                  value={oohFeeDraft.dropoff_cost_euros}
                  onChange={(e) => setOohFeeDraft({ ...oohFeeDraft, dropoff_cost_euros: Number(e.target.value) })}
                />
              </div>
              <div className="host-field host-field-span-2">
                <label>Locations (optional)</label>
                <p className="host-field-hint">Leave empty to apply at all selected locations.</p>
                <HostMultiSelect
                  value={oohFeeDraft.location_ids}
                  onChange={(ids) => setOohFeeDraft({ ...oohFeeDraft, location_ids: ids })}
                  options={oohLocationOptions}
                  placeholder="All locations"
                  disabled={!oohLocationOptions.length}
                  ariaLabel="Out-of-hours locations"
                />
              </div>
            </div>

            <div className="host-fare-actions">
              <button
                type="button"
                className="host-btn secondary"
                disabled={!oohFeeDraft.time_from || !oohFeeDraft.time_to}
                onClick={onAddOohFee}
              >
                {editingOohFeeId ? 'Update out-of-hours fee' : 'Add out-of-hours fee'}
              </button>
              {editingOohFeeId && (
                <button type="button" className="host-btn secondary" onClick={onCancelOohFeeEdit}>
                  Cancel edit
                </button>
              )}
            </div>

            {outOfHoursFees.length > 0 && (
              <ul className="host-fee-list">
                {outOfHoursFees.map((fee) => (
                  <li key={fee.id} className="host-fee-item">
                    <div className="host-fee-item-main">
                      <span className="host-fee-route">{fee.name}</span>
                      <span className="host-fee-amount">
                        {fee.time_from}–{fee.time_to} · pick-up {currency.formatAmount(fee.pickup_cost_euros ?? (fee.pickup_cost_cents || 0) / 100)}, drop-off {currency.formatAmount(fee.dropoff_cost_euros ?? (fee.dropoff_cost_cents || 0) / 100)}
                      </span>
                    </div>
                    <div className="host-fee-item-actions">
                      <button type="button" className="host-btn secondary host-btn-compact" onClick={() => onEditOohFee(fee)}>
                        Edit
                      </button>
                      <button type="button" className="host-btn danger" onClick={() => onDeleteOohFee(fee.id)}>
                        Remove
                      </button>
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </section>
          </HostDisclosure>
        </>
      ) : (
        <p className="host-field-hint">Save the vehicle first to configure fees.</p>
      )}
    </>
  )
}
