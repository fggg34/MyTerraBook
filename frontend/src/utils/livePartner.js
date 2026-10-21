export function isLivePartner(record) {
  const provider = record?.external_provider
  return provider === 'caren' || provider === 'foxie'
}
