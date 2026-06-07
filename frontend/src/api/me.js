import { api } from '../api'

export function updateProfile(payload) {
  return api.patch('/me/profile', payload)
}

export function updatePassword(payload) {
  return api.patch('/me/password', payload)
}
