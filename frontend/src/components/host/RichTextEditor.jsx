import { useEffect, useRef } from 'react'

const COMMANDS = [
  { command: 'bold', label: 'Bold' },
  { command: 'italic', label: 'Italic' },
  { command: 'insertUnorderedList', label: 'Bullets' },
  { command: 'insertOrderedList', label: 'Numbers' },
]

export default function RichTextEditor({ id, value = '', onChange }) {
  const areaRef = useRef(null)
  const focused = useRef(false)

  useEffect(() => {
    const area = areaRef.current
    if (!area || focused.current) return
    const next = value || ''
    if (area.innerHTML !== next) {
      area.innerHTML = next
    }
  }, [value])

  const sync = () => {
    onChange?.(areaRef.current?.innerHTML || '')
  }

  const run = (command) => {
    areaRef.current?.focus()
    document.execCommand(command, false, null)
    sync()
  }

  return (
    <div className="rich-text-editor">
      <div className="rich-text-editor__toolbar" role="toolbar" aria-label="Terms formatting">
        {COMMANDS.map((item) => (
          <button key={item.command} type="button" onMouseDown={(event) => event.preventDefault()} onClick={() => run(item.command)}>
            {item.label}
          </button>
        ))}
      </div>
      <div
        id={id}
        ref={areaRef}
        className="rich-text-editor__area"
        contentEditable
        role="textbox"
        aria-multiline="true"
        aria-label="Contract rental terms and conditions"
        onFocus={() => {
          focused.current = true
        }}
        onBlur={() => {
          focused.current = false
          sync()
        }}
        onInput={sync}
      />
    </div>
  )
}
