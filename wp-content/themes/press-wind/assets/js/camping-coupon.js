(function () {
  const pad = (n) => String(n).padStart(2, '0');

  function renderCountdown(container) {
    const root = container.querySelector('.js-countdown');
    if (!root) return;

    const endMs = Number(root.dataset.end || 0) * 1000;
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

// ---- PDF en texte pur (version robuste, détection automatique jsPDF) ----
async function generateCouponPDF(container, btn) {
  const originalText = btn.innerHTML;
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
    // Récup données
    const camping  = container.dataset.camping || '';
    const title    = container.dataset.title   || '';
    const desc     = container.dataset.desc    || '';
    const code     = container.dataset.code    || '';
    const dates    = container.dataset.dates   || '';
    const filename = (container.dataset.filename || 'bon').replace(/\s+/g, '-').toLowerCase();

    // ✅ Détection robuste de jsPDF (peu importe le bundle)
    const jsPDF =
      (window.jspdf && window.jspdf.jsPDF)
      || window.jsPDF
      || (window.html2pdf && window.html2pdf.jsPDF);

    if (typeof jsPDF !== 'function') {
      console.error('❌ jsPDF introuvable : vérifie le chargement de html2pdf.bundle.min.js');
      alert('Impossible de générer le PDF (jsPDF non disponible).');
      return;
    }

    // Création du PDF
    const doc = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });
    const W = doc.internal.pageSize.getWidth();
    const H = doc.internal.pageSize.getHeight();
    const M = 15;
    const maxW = W - M * 2;
    let y = M;

    // Fond blanc explicite
    doc.setFillColor(255, 255, 255);
    doc.rect(0, 0, W, H, 'F');

    // Styles
    const setH1 = () => { doc.setFont('helvetica', 'bold'); doc.setFontSize(18); doc.setTextColor(17, 17, 17); };
    const setH2 = () => { doc.setFont('helvetica', 'bold'); doc.setFontSize(14); doc.setTextColor(17, 17, 17); };
    const setP  = () => { doc.setFont('helvetica', 'normal'); doc.setFontSize(12); doc.setTextColor(17, 17, 17); };

    // Titre
    setH1();
    const titleText = [camping, title && `— ${title}`].filter(Boolean).join(' ') || 'Bon';
    const titleLines = doc.splitTextToSize(titleText, maxW);
    doc.text(titleLines, M, y);
    y += 10 + (titleLines.length - 1) * 6;

    // Description
    if (desc) {
      setP();
      const descLines = doc.splitTextToSize(desc, maxW);
      doc.text(descLines, M, y);
      y += descLines.length * 6 + 4;
    }

    // Code
    if (code) {
      setH2(); doc.text('Code :', M, y);
      setP();  doc.text(String(code), M + 25, y);
      y += 8;
    }

    // Dates
    if (dates) {
      setH2(); doc.text('Validité :', M, y);
      setP();  doc.text(doc.splitTextToSize(dates, maxW - 25), M + 25, y);
      y += 10;
    }

    // Pied de page
    doc.setDrawColor(200);
    doc.line(M, H - 20, W - M, H - 20);
    doc.setFont('helvetica', 'normal'); doc.setFontSize(10); doc.setTextColor(120, 120, 120);
    doc.text('Document généré automatiquement', M, H - 12);

    // Sauvegarde
    doc.save(`${filename}.pdf`);
  } catch (e) {
    console.error(e);
    alert('Erreur pendant la génération du PDF.');
  } finally {
    btn.disabled = false;
    btn.classList.remove('is-loading');
    btn.innerHTML = originalText;
  }
}


  // ---- Boot ----
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
