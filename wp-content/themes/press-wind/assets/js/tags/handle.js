document.addEventListener('DOMContentLoaded', function () {
  (function () {
  function safeCall(fn, args) {
    try {
      if (typeof fn === "function") return fn.apply(window, args || []);
    } catch (e) {}
    return undefined;
  }

  function openLink(href, target) {
    if (!href) return;
    if ((target || "").toLowerCase() === "_blank") {
      window.open(href, "_blank", "noopener,noreferrer");
    } else {
      window.location.href = href;
    }
  }

  document.addEventListener("click", function (e) {
    const el = e.target.closest("a.js-track");
    if (!el) return;

    const action = el.getAttribute("data-track") || "";
    const hrefAttr = el.getAttribute("href") || "";
    const href = el.href; // url résolue
    const target = el.getAttribute("target") || "";

    // 1) EMAIL (modale) : pas de navigation
    if (action === "email") {
      e.preventDefault(); // évite le jump "#"
      safeCall(window.uet_email);
      safeCall(window.gtag_conv_email);
      // IMPORTANT: ne pas stopper la propagation, ta modale peut écouter le clic
      return;
    }

    // 2) TELEPHONE : tracking best-effort, ne pas bloquer tel:
    if (action === "phone") {
      safeCall(window.uet_event, ["Telephone", "Contact"]);
      // ton ancien code faisait "return gtag_conv_phone('tel:...')" => ça bloquait potentiellement
      const phone = el.getAttribute("data-phone") || hrefAttr;
      safeCall(window.gtag_conv_phone, [phone.startsWith("tel:") ? phone : `tel:${phone}`]);
      return;
    }

    // 3) LIENS SORTANTS (Réserver, Site camping) : on doit éviter le "return false" de gtag
    const isOutbound = hrefAttr && hrefAttr !== "#" && (target || "").toLowerCase() === "_blank";

    if (isOutbound && (action === "buy_fiche" || action === "camping_link")) {
      e.preventDefault();

      let opened = false;
      const fallbackOpen = () => {
        if (opened) return;
        opened = true;
        openLink(href, target);
      };

      // UET
      if (action === "buy_fiche") safeCall(window.uet_buy_fiche);
      if (action === "camping_link") safeCall(window.uet_site);

      // GTAG conversion (ta fonction retourne false et redirige via callback)
      // On l'appelle quand même, puis on met un fallback au cas où callback ne part jamais.
      if (action === "buy_fiche") safeCall(window.gtag_conv_buy_fiche, [href]);
      if (action === "camping_link") safeCall(window.gtag_conv_camping_link, [href]);

      // fallback si gtag est bloqué / callback jamais appelé
      setTimeout(fallbackOpen, 350);
      return;
    }

    // 4) Autres cas : ne rien faire, laisser le comportement natif
  });
  })();
});
