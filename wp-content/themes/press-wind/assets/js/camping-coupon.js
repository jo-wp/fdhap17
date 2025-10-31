(function () {
  const pad = (n) => String(n).padStart(2, '0');

  function renderCountdown(container) {
    const root = container.querySelector('.js-countdown');
    if (!root) return;

    const endMs  = Number(root.dataset.end || 0) * 1000;
    const daysEl = root.querySelector('.js-countdown-days');
    const timeEl = root.querySelector('.js-countdown-time');
    if (!daysEl || !timeEl) return;

    const diff = endMs - Date.now();
    if (diff <= 0) {
      daysEl.textContent = '0';
      timeEl.textContent = '00:00:00';
      container.dataset.expired = 'true';
      return;
    }

    const total = Math.floor(diff / 1000);
    const d = Math.floor(total / 86400);
    const h = Math.floor((total % 86400) / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = total % 60;

    daysEl.textContent = d;
    timeEl.textContent = `${pad(h)}:${pad(m)}:${pad(s)}`;
  }

  function tickAll() {
    document.querySelectorAll('.js-coupon').forEach(renderCountdown);
  }

  // ---- PDF programmatique (jsPDF est fourni par html2pdf.bundle) ----
async function generateCouponPDF(container) {
  // 0) Sanity checks
  if (typeof window.html2pdf !== 'function') {
    console.error('html2pdf non chargé.');
    return;
  }

  // 1) Récup des données
  const camping  = container.dataset.camping || '';
  const title    = container.dataset.title   || '';
  const desc     = container.dataset.desc    || '';
  const code     = container.dataset.code    || '';
  const dates    = container.dataset.dates   || '';
  const filename = (container.dataset.filename || 'bon').replace(/\s+/g, '-').toLowerCase();

  // 2) Conteneur temporaire (visible pour html2canvas, mais hors écran)
  const wrapper = document.createElement('div');
  // IMPORTANT: ne pas display:none; sinon html2canvas verra 0x0.
  wrapper.style.position   = 'absolute';
  wrapper.style.left       = '-10000px';
  wrapper.style.top        = '0';
  wrapper.style.width      = '800px'; // largeur de rendu confortable
  wrapper.style.background = '#ffffff'; // pour éviter un fond transparent -> page "blanche"

  // 3) Contenu minimal (pas de CSS externe requis)
  //    On échappe juste les chevrons au cas où.
  const esc = (s) => String(s).replace(/</g, '&lt;');
  wrapper.innerHTML = `
    <div style="font-family: Arial, Helvetica, sans-serif; color:#111; padding:24px; line-height:1.5;">
      <h1 style="margin:0 0 12px 0; font-size:24px; font-weight:700;">
        ${esc(camping)}${title ? ' — ' + esc(title) : ''}
      </h1>
      ${desc ? `<p style="margin:0 0 12px 0; font-size:14px;">${esc(desc)}</p>` : ''}
      ${code ? `<p style="margin:0 0 6px 0; font-size:14px;"><strong>Code :</strong> ${esc(code)}</p>` : ''}
      ${dates ? `<p style="margin:0; font-size:14px;"><strong>Validité :</strong> ${esc(dates)}</p>` : ''}
    </div>
  `;
  document.body.appendChild(wrapper);

  try {
    // 4) Attendre les polices (quand supporté)
    if (document.fonts && document.fonts.ready) {
      try { await document.fonts.ready; } catch (e) {}
    }

    // 5) Options html2pdf -> A4 portrait
    const opt = {
      margin: [10, 10, 10, 10],
      filename: `${filename}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    // 6) Génération
    await window.html2pdf().set(opt).from(wrapper).save();
  } finally {
    // 7) Nettoyage
    document.body.removeChild(wrapper);
  }
}

  async function generateCouponPDF_withHtml2Pdf(container) {
  const camping  = container.dataset.camping || '';
  const title    = container.dataset.title || '';
  const desc     = container.dataset.desc || '';
  const code     = container.dataset.code || '';
  const dates    = container.dataset.dates || '';
  const filename = (container.dataset.filename || 'bon').replace(/\s+/g, '-').toLowerCase();

  const wrapper = document.createElement('div');
  wrapper.style.position = 'fixed';
  wrapper.style.left = '-99999px';
  wrapper.style.top = '0';
  wrapper.style.width = '800px'; // largeur de rendu pour un A4 propre
  wrapper.innerHTML = `
    <div style="font-family: Arial, sans-serif; padding:24px;">
      <h1 style="margin:0 0 12px 0; font-size:24px;">
        ${camping ? camping.replace(/</g,'&lt;') : ''} ${title ? ' — ' + title.replace(/</g,'&lt;') : ''}
      </h1>
      ${desc ? `<p style="font-size:14px; line-height:1.5; margin:0 0 12px 0;">${desc.replace(/</g,'&lt;')}</p>` : ''}
      ${code ? `<p style="font-size:14px; margin:0 0 6px 0;"><strong>Code :</strong> ${String(code).replace(/</g,'&lt;')}</p>` : ''}
      ${dates ? `<p style="font-size:14px; margin:0;"><strong>Validité :</strong> ${dates.replace(/</g,'&lt;')}</p>` : ''}
    </div>
  `;
  document.body.appendChild(wrapper);

  const opt = {
    margin: [10, 10, 10, 10],
    filename: `${filename}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true, logging: false },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  };

  await html2pdf().set(opt).from(wrapper).save();
  document.body.removeChild(wrapper);
}

  // ---- Boot (c’est ça qui manquait pour tes compteurs) ----
  document.addEventListener('DOMContentLoaded', () => {
    // Rendu initial + interval GLOBAL unique
    tickAll();
    if (window._couponTimer) clearInterval(window._couponTimer);
    window._couponTimer = setInterval(tickAll, 1000);

    // Clic PDF
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.js-pdf-btn');
      if (!btn) return;
      const container = btn.closest('.js-coupon');
      if (!container) return;
 generateCouponPDF(container); // <-- version html2pdf ci-dessus
    });
  });
})();
