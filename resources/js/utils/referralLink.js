const KEY = 'tori_registration_referral';
const TTL = 30 * 24 * 60 * 60 * 1000;
export function saveReferral(type, code) {
  try {
    if (!['member', 'agent'].includes(type) || typeof code !== 'string' || !code.trim() || code.trim().length > 64) {
      localStorage.removeItem(KEY);
      return;
    }
    const previous = readReferral();
    if (previous?.type === type && previous.code === code.trim()) return;
    localStorage.setItem(KEY, JSON.stringify({ type, code: code.trim(), expires: Date.now() + TTL }));
  } catch { /* Registration still works when browser storage is unavailable. */ }
}
export function readReferral() {
  try {
    const value = JSON.parse(localStorage.getItem(KEY));
    if (value && ['member', 'agent'].includes(value.type) && typeof value.code === 'string' && value.code.length <= 64 && value.expires > Date.now()) return value;
    localStorage.removeItem(KEY);
  } catch { /* Invalid or unavailable browser storage. */ }
  return null;
}
export function clearReferral() { try { localStorage.removeItem(KEY); } catch { /* Optional storage. */ } }
export function captureReferral(query) {
  if (Object.hasOwn(query, 'ref')) saveReferral(query.type, query.ref);
}
