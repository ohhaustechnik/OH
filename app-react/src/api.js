// Spricht das bestehende PHP-Backend (buero.php) an – gleiche Endpunkte wie bisher.
const API_URL = import.meta.env.VITE_API_URL || '/buero.php'
export async function api(action, data = {}) {
  const fd = new FormData()
  fd.append('action', action)
  fd.append('data', JSON.stringify(data))
  const r = await fetch(API_URL, { method: 'POST', body: fd, credentials: 'include' })
  return r.json()
}
export async function login(pw) { return api('login', { pw }) }
export function eur(n){ return (Number(n)||0).toLocaleString('de-DE',{minimumFractionDigits:0,maximumFractionDigits:0})+' €' }
