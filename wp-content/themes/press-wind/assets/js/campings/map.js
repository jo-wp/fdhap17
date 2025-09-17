function displayCardMapCamping() {
  
  const latitudeMap =   document.getElementById('map').dataset.latitude
  const longitudeMap = document.getElementById('map').dataset.longitude

  const POINTS = [
    { lat: latitudeMap, lng: longitudeMap, label: 'test' },
  ]

const start = POINTS[0];
const map = L.map('map', { zoomControl: true }).setView([start.lat, start.lng], 13);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    minZoom: 3,
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">Contributeurs OpenStreetMap</a>',
  }).addTo(map)

  // Icône personnalisée (SVG inline, aucune ressource externe nécessaire)
  const pinSVG = encodeURIComponent(`
<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 48'>
<defs>
<filter id='shadow' x='-50%' y='-50%' width='200%' height='200%'>
<feGaussianBlur in='SourceAlpha' stdDeviation='2' result='blur'/>
<feOffset in='blur' dy='2' result='offset'/>
<feMerge>
<feMergeNode in='offset'/>
<feMergeNode in='SourceGraphic'/>
</feMerge>
</filter>
</defs>
<path filter='url(#shadow)' d='M16 0C8.82 0 3 5.82 3 13c0 9.75 12.08 22.79 12.59 23.34a1 1 0 0 0 1.42 0C16.92 35.79 29 22.75 29 13 29 5.82 23.18 0 16 0z' fill='#ff3b30'/>
<circle cx='16' cy='13' r='5' fill='white'/>
</svg>
`)
  const customIcon = L.icon({
    iconUrl: `data:image/svg+xml;utf8,${pinSVG}`,
    iconSize: [32, 48],
    iconAnchor: [16, 48],
    popupAnchor: [0, -44],
  })

  const markers = POINTS.map((p) => {
    const m = L.marker([p.lat, p.lng], { icon: customIcon }).addTo(map)
    m.bindPopup(p.label || `${p.lat.toFixed(5)}, ${p.lng.toFixed(5)}`)
    return m
  })

  // Adapte la vue pour montrer tous les points
  // const group = L.featureGroup(markers)
  // map.fitBounds(group.getBounds().pad(0.2))


}
export default displayCardMapCamping
