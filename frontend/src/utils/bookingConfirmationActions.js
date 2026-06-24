import { api } from '../api'

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
