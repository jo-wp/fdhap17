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

  // -------- Génération "PDF" la plus simple : nouvelle fenêtre + print() --------
  async function generateCouponPDF(container, btn) {
    // Loader ON
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.classList.add('is-loading');
    btn.innerHTML = `
      <span class="spinner" style="
        display:inline-block;width:16px;height:16px;border:2px solid currentColor;
        border-right-color:transparent;border-radius:50%;margin-right:8px;vertical-align:middle;
        animation:spin 0.7s linear infinite;"></span>
      Génération...
    `;

    try {
      // Récup données depuis data-*
      const camping  = container.dataset.camping  || '';
      const title    = container.dataset.title    || '';
      const desc     = container.dataset.desc     || '';
      const code     = container.dataset.code     || '';
      const dates    = container.dataset.dates    || '';
      const filename = (container.dataset.filename || 'bon').replace(/\s+/g, '-').toLowerCase();

      // Fenêtre d'impression
      const w = window.open('', '_blank');
      if (!w) {
        alert("Impossible d'ouvrir la fenêtre d'impression (popup bloqué ?).");
        return;
      }

      // HTML minimal, lisible, en noir sur fond blanc
      const esc = (s) => String(s).replace(/[<>&]/g, (m)=>({ '<':'&lt;','>':'&gt;','&':'&amp;' }[m]));
      const html = `<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>${esc(camping)}${title ? ' — ' + esc(title) : ''}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    @page { size: A4; margin: 12mm; }
    body { font-family: Arial, Helvetica, sans-serif; color:#111; background:#fff; }
    .wrap { line-height:1.5; }
    h1 { margin:0 0 12px 0; font-size:22px; font-weight:700; }
    p { margin:0 0 10px 0; font-size:14px; }
    .meta { margin-top:8px; }
    .label { font-weight:700; }
    .hr { margin:14px 0; height:1px; background:#ddd; border:0; }
    .footer { margin-top:20px; font-size:11px; color:#777; }
    /* Impression auto propre */
    @media print {
      .no-print { display:none !important; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>${esc(camping)}${title ? ' — ' + esc(title) : ''}</h1>
    ${desc ? `<p>${esc(desc)}</p>` : ''}
    ${code ? `<p class="meta"><span class="label">Code :</span> ${esc(code)}</p>` : ''}
    ${dates ? `<p class="meta"><span class="label">Validité :</span> ${esc(dates)}</p>` : ''}
    <div class="hr"></div>
    <div class="footer">Document généré automatiquement — ${new Date().toLocaleDateString('fr-FR')}</div>
  </div>
  <script>
    // Petite pause pour que le moteur de rendu peaufine
    window.addEventListener('load', function () {
      setTimeout(function(){
        document.title = ${JSON.stringify(filename)}; // titre proposé au "Enregistrer en PDF"
        window.focus();
        window.print();
        window.close();
      }, 150);
    });
  </script>
</body>
</html>`;

      // Écrit et ferme le flux
      w.document.open();
      w.document.write(html);
      w.document.close();

    } finally {
      // Loader OFF
      btn.disabled = false;
      btn.classList.remove('is-loading');
      btn.innerHTML = originalHTML;
    }
  }

  // ---- Boot (compteurs + clic bouton) ----
  document.addEventListener('DOMContentLoaded', () => {
    tickAll();
    if (window._couponTimer) clearInterval(window._couponTimer);
    window._couponTimer = setInterval(tickAll, 1000);

    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.js-pdf-btn');
      if (!btn) return;
      const container = btn.closest('.js-coupon');
      if (!container) return;
      generateCouponPDF(container, btn);
    });
  });

  // Spinner CSS
  const style = document.createElement('style');
  style.innerHTML = `
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .js-pdf-btn.is-loading { opacity: 0.7; cursor: wait; }
  `;
  document.head.appendChild(style);
})();
