let map, markersLayer

export function ensureMap() {
  if (!map) {
    const el = document.getElementById('campings-map')

    if (!el) return

    map = L.map(el, { scrollWheelZoom: false })
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 12,
      attribution: '&copy; OpenStreetMap',
    }).addTo(map)

    markersLayer = L.layerGroup().addTo(map)
    map.setView([46.1603, -1.1511], 9)
  }
}

export function rebuildMarkers() {
  ensureMap()
  if (!map || !markersLayer) return

  markersLayer.clearLayers()

  const markerSvg = `
  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="39" viewBox="0 0 30 39">
  <path d="M17.5074 37.8874C22.1164 31.5693 29.8464 19.9367 29.8464 13.0156C29.8464 5.83806 23.2253 0 15.089 0C6.95256 0 0.331543 5.83806 0.331543 13.0156C0.331543 19.9367 8.06145 31.5693 12.6705 37.8874C13.7522 39.3709 16.4257 39.3709 17.5074 37.8874ZM5.2144 13.0156C5.2144 8.21258 9.64315 4.30654 15.089 4.30654C20.5347 4.30654 24.9635 8.21258 24.9635 13.0156C24.9635 17.8172 20.5347 21.7232 15.089 21.7232C9.64315 21.7232 5.2144 17.8157 5.2144 13.0156Z" fill="#51AB7E"/>
  </svg>
  `.trim()

  const icon = L.icon({
    iconUrl: 'data:image/svg+xml;base64,' + btoa(markerSvg),
    iconSize: [30, 39],
    iconAnchor: [15, 39],
    popupAnchor: [0, -39],
  })

  const $items = jQuery('.js-camping-item[data-lat][data-lng]')

  const bounds = L.latLngBounds([])
  $items.each(function () {
    const $it = jQuery(this)
    const lat = parseFloat($it.attr('data-lat'))
    const lng = parseFloat($it.attr('data-lng'))
    if (isFinite(lat) && isFinite(lng)) {
      const title = $it.attr('data-title') || ''
      const url = $it.attr('data-url') || '#'
      const marker = L.marker([lat, lng],{ icon }).bindPopup(
        `<strong>${title}</strong><br><a href="${url}">Voir la fiche</a>`,
      )
      markersLayer.addLayer(marker)
      bounds.extend([lat, lng])
    }
  })

  if (bounds.isValid()) {
    map.fitBounds(bounds, { padding: [20, 20] })
  }
}

export function initFacetwpLeaflet() {
  document.addEventListener('DOMContentLoaded', rebuildMarkers)
  document.addEventListener('facetwp-loaded', rebuildMarkers)
}

export default { ensureMap, rebuildMarkers, initFacetwpLeaflet }
