import HostMultiSelect from './HostMultiSelect'
import HostSelect from './HostSelect'
import { formatWindowLabel } from '../../utils/locationHours'

function toLocationOptions(locations = []) {
  return locations.map((loc) => ({
    value: String(loc.id),
    label: loc.name,
    subtitle: loc.address || undefined,
  }))
}

function toTimeOptions(times = []) {
  return times.map((t) => ({ value: t, label: t }))
}

export default function HostCarLocationsStep({
  catalogLocations,
  form,
  setForm,
  selectedPickupLocations,
  selectedDropoffLocations,
  pickupBounds,
  dropoffBounds,
  pickupTimeOptions,
  dropoffTimeOptions,
  canConfigureTimes,
  recordId,
  locationFees,
  outOfHoursFees,
  locationFeeDraft,
  setLocationFeeDraft,
  oohFeeDraft,
  setOohFeeDraft,
  onAddLocationFee,
  onDeleteLocationFee,
  onAddOohFee,
  onDeleteOohFee,
}) {
  const allLocationOptions = toLocationOptions(catalogLocations)
  const pickupOptions = toLocationOptions(selectedPickupLocations)
  const dropoffOptions = toLocationOptions(selectedDropoffLocations)
  const oohLocationOptions = toLocationOptions(
    [...selectedPickupLocations, ...selectedDropoffLocations].filter(
      (loc, idx, arr) => arr.findIndex((x) => x.id === loc.id) === idx,
    ),
  )

  if (catalogLocations.length === 0) {
    return (
      <p className="host-field-hint">
        No pickup or drop-off locations are available yet. Ask an admin to add active locations in Impact Rent.
      </p>
    )
  }

  return (
    <>
      <div className="host-locations-grid">
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

      {canConfigureTimes ? (
        <section className="host-subsection">
          <div className="host-subsection-head">
            <h3>Pickup & drop-off times</h3>
            <p>Times must fall within admin opening hours for all selected locations.</p>
          </div>
          <div className="host-locations-grid">
            <div className="host-field">
              <label>Pickup from</label>
              <p className="host-field-hint">Allowed: {formatWindowLabel(pickupBounds)}</p>
              <HostSelect
                value={form.pickup_time_from}
                onChange={(v) => setForm({ ...form, pickup_time_from: v })}
                options={toTimeOptions(pickupTimeOptions)}
                placeholder="Select time"
                ariaLabel="Pickup from"
              />
            </div>
            <div className="host-field">
              <label>Pickup to</label>
              <HostSelect
                value={form.pickup_time_to}
                onChange={(v) => setForm({ ...form, pickup_time_to: v })}
                options={toTimeOptions(pickupTimeOptions)}
                placeholder="Select time"
                ariaLabel="Pickup to"
              />
            </div>
            <div className="host-field">
              <label>Drop-off from</label>
              <p className="host-field-hint">Allowed: {formatWindowLabel(dropoffBounds)}</p>
              <HostSelect
                value={form.dropoff_time_from}
                onChange={(v) => setForm({ ...form, dropoff_time_from: v })}
                options={toTimeOptions(dropoffTimeOptions)}
                placeholder="Select time"
                ariaLabel="Drop-off from"
              />
            </div>
            <div className="host-field">
              <label>Drop-off to</label>
              <HostSelect
                value={form.dropoff_time_to}
                onChange={(v) => setForm({ ...form, dropoff_time_to: v })}
                options={toTimeOptions(dropoffTimeOptions)}
                placeholder="Select time"
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
          <section className="host-fees-section">
            <div className="host-subsection-head">
              <h3>Pickup / drop-off fees</h3>
              <p>Add a flat fee for specific pickup and drop-off combinations.</p>
            </div>

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
                <label>Fee amount (€)</label>
                <input
                  type="number"
                  min={0}
                  step={0.01}
                  value={locationFeeDraft.cost_euros}
                  onChange={(e) => setLocationFeeDraft({ ...locationFeeDraft, cost_euros: Number(e.target.value) })}
                />
              </div>
              <div className="host-field host-fees-toggle">
                <label className="host-check-card">
                  <input
                    type="checkbox"
                    checked={locationFeeDraft.is_one_way_fee}
                    onChange={(e) => setLocationFeeDraft({ ...locationFeeDraft, is_one_way_fee: e.target.checked })}
                  />
                  <span>
                    <strong>One-way fee</strong>
                    <small>Charge when pickup and drop-off locations differ</small>
                  </span>
                </label>
              </div>
            </div>

            <button
              type="button"
              className="host-btn secondary"
              disabled={!locationFeeDraft.pickup_location_id || !locationFeeDraft.dropoff_location_id}
              onClick={onAddLocationFee}
            >
              Add location fee
            </button>

            {locationFees.length > 0 && (
              <ul className="host-fee-list">
                {locationFees.map((fee) => (
                  <li key={fee.id} className="host-fee-item">
                    <div className="host-fee-item-main">
                      <span className="host-fee-route">
                        {fee.pickup_location?.name || 'Pickup'} → {fee.dropoff_location?.name || 'Drop-off'}
                      </span>
                      <span className="host-fee-amount">
                        €{fee.cost_euros ?? (fee.cost_cents / 100).toFixed(2)}
                        {fee.is_one_way_fee ? ' · one-way' : ''}
                      </span>
                    </div>
                    <button type="button" className="host-btn danger" onClick={() => onDeleteLocationFee(fee.id)}>
                      Remove
                    </button>
                  </li>
                ))}
              </ul>
            )}
          </section>

          <section className="host-fees-section">
            <div className="host-subsection-head">
              <h3>Out-of-hours fees</h3>
              <p>Charge extra for pickups or drop-offs outside standard hours.</p>
            </div>

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
                <input type="time" value={oohFeeDraft.time_from} onChange={(e) => setOohFeeDraft({ ...oohFeeDraft, time_from: e.target.value })} />
              </div>
              <div className="host-field">
                <label>To time</label>
                <input type="time" value={oohFeeDraft.time_to} onChange={(e) => setOohFeeDraft({ ...oohFeeDraft, time_to: e.target.value })} />
              </div>
              <div className="host-field">
                <label>Pick-up charge (€)</label>
                <input
                  type="number"
                  min={0}
                  value={oohFeeDraft.pickup_cost_euros}
                  onChange={(e) => setOohFeeDraft({ ...oohFeeDraft, pickup_cost_euros: Number(e.target.value) })}
                />
              </div>
              <div className="host-field">
                <label>Drop-off charge (€)</label>
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

            <button
              type="button"
              className="host-btn secondary"
              disabled={!oohFeeDraft.time_from || !oohFeeDraft.time_to}
              onClick={onAddOohFee}
            >
              Add out-of-hours fee
            </button>

            {outOfHoursFees.length > 0 && (
              <ul className="host-fee-list">
                {outOfHoursFees.map((fee) => (
                  <li key={fee.id} className="host-fee-item">
                    <div className="host-fee-item-main">
                      <span className="host-fee-route">{fee.name}</span>
                      <span className="host-fee-amount">
                        {fee.time_from}–{fee.time_to} · pick-up €{fee.pickup_cost_euros}, drop-off €{fee.dropoff_cost_euros}
                      </span>
                    </div>
                    <button type="button" className="host-btn danger" onClick={() => onDeleteOohFee(fee.id)}>
                      Remove
                    </button>
                  </li>
                ))}
              </ul>
            )}
          </section>
        </>
      ) : (
        <p className="host-field-hint">Save the vehicle first to configure fees.</p>
      )}
    </>
  )
}
