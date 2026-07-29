// composables/useApi.ts — Composable wrapper untuk axios
export const useApi = () => {
  const { $api } = useNuxtApp()
  return $api
}

// Helper untuk format harga Rupiah
export const formatRupiah = (value: number | string): string => {
  const num = typeof value === 'string' ? parseFloat(value) : value
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(num)
}

// Helper tanggal Indonesia
export const formatTanggal = (date: string | null): string => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(new Date(date))
}

// Helper status badge color
export const statusColor = (status: string): string => {
  const map: Record<string, string> = {
    pending:  'badge-warning',
    diproses: 'badge-primary',
    selesai:  'badge-success',
    batal:    'badge-danger',
  }
  return map[status] ?? 'badge-primary'
}
