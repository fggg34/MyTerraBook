import { useId, useRef } from 'react'
import { ImagePlus, Trash2, Upload } from 'lucide-react'

export function HostImageDropzone({
  label,
  hint,
  previewSrc,
  emptyLabel = 'Choose an image',
  accept = 'image/*',
  onSelect,
  onClear,
  disabled = false,
}) {
  const inputId = useId()
  const inputRef = useRef(null)

  const openPicker = () => {
    if (!disabled) inputRef.current?.click()
  }

  const handleFiles = (fileList) => {
    const file = fileList?.[0]
    if (file && file.type.startsWith('image/')) onSelect?.(file)
  }

  return (
    <div className="host-image-field">
      {label && <label className="host-image-field__label" htmlFor={inputId}>{label}</label>}
      {hint && <p className="host-image-field__hint">{hint}</p>}
      <div
        className={`host-image-dropzone${previewSrc ? ' has-preview' : ''}${disabled ? ' is-disabled' : ''}`}
        onDragOver={(e) => e.preventDefault()}
        onDrop={(e) => {
          e.preventDefault()
          if (!disabled) handleFiles(e.dataTransfer.files)
        }}
      >
        {previewSrc ? (
          <div className="host-image-dropzone__preview">
            <img src={previewSrc} alt="" />
            <div className="host-image-dropzone__actions">
              <button type="button" className="host-image-dropzone__btn" onClick={openPicker}>
                <Upload size={15} aria-hidden />
                Replace
              </button>
              {onClear && (
                <button type="button" className="host-image-dropzone__btn host-image-dropzone__btn--danger" onClick={onClear}>
                  <Trash2 size={15} aria-hidden />
                  Remove
                </button>
              )}
            </div>
          </div>
        ) : (
          <button type="button" className="host-image-dropzone__empty" onClick={openPicker} disabled={disabled}>
            <span className="host-image-dropzone__icon" aria-hidden><ImagePlus size={28} strokeWidth={1.5} /></span>
            <span className="host-image-dropzone__title">{emptyLabel}</span>
            <span className="host-image-dropzone__meta">JPG or PNG · drag & drop also works</span>
          </button>
        )}
        <input
          ref={inputRef}
          id={inputId}
          type="file"
          accept={accept}
          className="host-image-dropzone__input"
          disabled={disabled}
          onChange={(e) => handleFiles(e.target.files)}
        />
      </div>
    </div>
  )
}

export function HostImageGallery({
  label,
  hint,
  items = [],
  onSelect,
  disabled = false,
}) {
  const inputId = useId()
  const inputRef = useRef(null)

  const openPicker = () => {
    if (!disabled) inputRef.current?.click()
  }

  return (
    <div className="host-image-field">
      {label && <span className="host-image-field__label">{label}</span>}
      {hint && <p className="host-image-field__hint">{hint}</p>}
      <div className="host-image-gallery">
        {items.map((item) => (
          <div key={item.key} className="host-image-gallery__item">
            <img src={item.src} alt="" />
            <button
              type="button"
              className="host-image-gallery__remove"
              aria-label="Remove image"
              title="Remove"
              onClick={(e) => {
                e.preventDefault()
                e.stopPropagation()
                item.onRemove?.()
              }}
            >
              <Trash2 size={14} aria-hidden />
            </button>
          </div>
        ))}
        <button type="button" className="host-image-gallery__add" onClick={openPicker} disabled={disabled}>
          <ImagePlus size={22} strokeWidth={1.5} aria-hidden />
          <span>Add photos</span>
        </button>
        <input
          ref={inputRef}
          id={inputId}
          type="file"
          accept="image/*"
          multiple
          className="host-image-dropzone__input"
          disabled={disabled}
          onChange={(e) => {
            const files = Array.from(e.target.files || [])
            if (files.length) onSelect?.(files)
            e.target.value = ''
          }}
        />
      </div>
    </div>
  )
}
