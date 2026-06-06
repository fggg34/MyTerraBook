import { useState } from 'react'
import { api } from '../../api'
import { useToast } from '../../context/ToastContext'

export default function ContactForm() {
  const { toast } = useToast()
  const [form, setForm] = useState({ name: '', email: '', message: '' })
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (event) => {
    event.preventDefault()
    setLoading(true)
    try {
      const { data } = await api.post('/contact', form)
      toast(data.message || 'Message sent', 'success')
      setForm({ name: '', email: '', message: '' })
    } catch (err) {
      toast(err.response?.data?.message || 'Could not send message', 'error')
    } finally {
      setLoading(false)
    }
  }

  return (
    <form className="content-contact-form" onSubmit={handleSubmit}>
      <label htmlFor="contact-name">Name</label>
      <input
        id="contact-name"
        required
        value={form.name}
        onChange={(e) => setForm({ ...form, name: e.target.value })}
      />
      <label htmlFor="contact-email">Email</label>
      <input
        id="contact-email"
        type="email"
        required
        value={form.email}
        onChange={(e) => setForm({ ...form, email: e.target.value })}
      />
      <label htmlFor="contact-message">Message</label>
      <textarea
        id="contact-message"
        required
        value={form.message}
        onChange={(e) => setForm({ ...form, message: e.target.value })}
      />
      <button type="submit" disabled={loading}>
        {loading ? 'Sending…' : 'Send message'}
      </button>
    </form>
  )
}
