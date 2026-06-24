import { api } from '../api'
import { fmtDisplayDate } from './requestToBookUtils'

async function downloadBlob(url, filename) {
  const res = await api.get(url, { responseType: 'blob' })
  const blobUrl = URL.createObjectURL(res.data)
  const link = document.createElement('a')
  link.href = blobUrl
  link.download = filename
  link.click()
  URL.revokeObjectURL(blobUrl)
}

export function hostMemberLabel(host) {
  if (!host?.member_since) return 'Verified host'
  const year = String(host.member_since).slice(0, 4)
  return year ? `Member since ${year}` : 'Verified host'
}

export function buildConfirmationEmailHref({
  reference,
  itemName,
  total,
  startLabel,
  endLabel,
  startDate,
  endDate,
  customerEmail,
  confirmationPath,
}) {
  const lines = [
    `Booking reference: ${reference}`,
    `Listing: ${itemName}`,
    `Total: ${total}`,
  ]
  if (startDate) lines.push(`${startLabel}: ${fmtDisplayDate(startDate)}`)
  if (endDate) lines.push(`${endLabel}: ${fmtDisplayDate(endDate)}`)
  if (confirmationPath) {
    lines.push('', `View online: ${window.location.origin}${confirmationPath}`)
  }

  const params = new URLSearchParams({
    subject: `Your booking ${reference}`,
    body: lines.join('\n'),
  })

  const recipient = customerEmail ? encodeURIComponent(customerEmail) : ''
  return `mailto:${recipient}?${params.toString()}`
}

export function buildHostMessageHref({ hostName, reference, itemName }) {
  const params = new URLSearchParams({
    subject: `Question about booking ${reference}`,
    body: [
      `Hi ${hostName},`,
      '',
      `I have a question about my booking (${reference}) for ${itemName}.`,
      '',
      'Thanks,',
    ].join('\n'),
  })

  return `mailto:support@myterrabook.is?${params.toString()}`
}

export async function downloadConfirmationCalendar(token, reference) {
  await downloadBlob(
    `/booking-confirmation/${encodeURIComponent(token)}/calendar.ics`,
    `booking-${reference}.ics`,
  )
}
