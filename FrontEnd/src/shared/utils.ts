/**
 * Formatea una fecha ISO a "hace X tiempo" — reemplaza timeAgo() inline de admin_main.php.
 */
export function timeAgo(dateStr: string): string {
  const diff = Date.now() - new Date(dateStr).getTime()
  const minutes = Math.floor(diff / 60000)
  if (minutes < 1) return 'hace un momento'
  if (minutes < 60) return `hace ${minutes} min`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `hace ${hours}h`
  const days = Math.floor(hours / 24)
  return `hace ${days}d`
}

/**
 * Devuelve el label de rol en español.
 */
export function roleLabel(role: 'ADMIN' | 'USER'): string {
  return role === 'ADMIN' ? 'Administrador' : 'Usuario'
}
