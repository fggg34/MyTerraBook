export default function ContentProse({ html }) {
  if (!html) return null

  return (
    <div
      className="content-prose"
      dangerouslySetInnerHTML={{ __html: html }}
    />
  )
}
