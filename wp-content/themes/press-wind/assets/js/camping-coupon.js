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

  // ---- Génération PDF avec fond noir + texte blanc ----
  async function generateCouponPDF(container, btn) {
    if (typeof window.html2pdf !== 'function') {
      console.error('html2pdf non chargé.');
      return;
    }

    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.classList.add('is-loading');
    btn.innerHTML = `
      <span class="spinner" style="
        display:inline-block;
        width:16px;
        height:16px;
        border:2px solid currentColor;
        border-right-color:transparent;
        border-radius:50%;
        margin-right:8px;
        vertical-align:middle;
        animation:spin 0.7s linear infinite;"></span>
      Génération...
    `;

    const camping  = container.dataset.camping || '';
    const title    = container.dataset.title || '';
    const desc     = container.dataset.desc || '';
    const code     = container.dataset.code || '';
    const dates    = container.dataset.dates || '';
    const filename = (container.dataset.filename || 'bon').replace(/\s+/g, '-').toLowerCase();

    // ✅ Élément temporaire visible + fond noir + texte blanc
    const wrapper = document.createElement('div');
    wrapper.style.position = 'fixed';
    wrapper.style.left = '0';
    wrapper.style.top = '0';
    wrapper.style.zIndex = '9999';
    wrapper.style.opacity = '1';
    wrapper.style.pointerEvents = 'none';
    wrapper.style.width = '800px';
    wrapper.style.background = '#000'; // fond noir
    wrapper.style.color = '#fff'; // texte blanc
    wrapper.innerHTML = `
      <div style="font-family: Arial, Helvetica, sans-serif; padding:40px; line-height:1.5;">
        <h1 style="margin:0 0 16px 0; font-size:22px; font-weight:700; color:#fff;">
          ${camping ? camping.replace(/</g, '&lt;') : ''}${title ? ' — ' + title.replace(/</g, '&lt;') : ''}
        </h1>
        ${desc ? `<p style="margin:0 0 12px 0; font-size:14px; color:#fff;">${desc.replace(/</g, '&lt;')}</p>` : ''}
        ${code ? `<p style="margin:0 0 6px 0; font-size:14px; color:#fff;"><strong>Code :</strong> ${String(code).replace(/</g, '&lt;')}</p>` : ''}
        ${dates ? `<p style="margin:0; font-size:14px; color:#fff;"><strong>Validité :</strong> ${dates.replace(/</g, '&lt;')}</p>` : ''}
      </div>
    `;
    document.body.appendChild(wrapper);

    try {
      if (document.fonts && document.fonts.ready) {
        try { await document.fonts.ready; } catch (e) {}
      }

      const opt = {
        margin: [10, 10, 10, 10],
        filename: `${filename}.pdf`,
        image: { type: 'jpeg', quality: 1 },
        html2canvas: {
          scale: 2,
          useCORS: true,
          backgroundColor: '#000000',
          logging: false,
          windowWidth: 800,
          windowHeight: wrapper.scrollHeight + 100
        },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };

      await window.html2pdf().set(opt).from(wrapper).save();
    } catch (err) {
      console.error('Erreur PDF :', err);
      alert('Une erreur est survenue lors de la génération du PDF.');
    } finally {
      document.body.removeChild(wrapper);
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
