import { resolveStorageUrl } from '../../api'

export function getUserProfilePhotoUrl(user) {
  if (!user) return ''
  return user.profile_photo_url || resolveStorageUrl(user.profile_photo_path)
}

export default function UserAvatar({ user, className = 'user-avatar' }) {
  const photoUrl = getUserProfilePhotoUrl(user)
  const initial = user?.name?.charAt(0)?.toUpperCase() || '?'

  if (photoUrl) {
    return <img src={photoUrl} alt="" className={`${className} ${className}--photo`} />
  }

  return (
    <span className={className} aria-hidden>
      {initial}
    </span>
  )
}
