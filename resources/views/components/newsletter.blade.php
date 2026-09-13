<!-- NEWSLETTER COMPONENT -->
<div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-left:4px solid var(--color-red);border-radius:4px;padding:20px 24px;margin-top:36px">
  <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px">
    <div style="flex:1;min-width:260px">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <i class="fas fa-envelope-open-text" style="color:var(--color-red);font-size:1.2rem"></i>
        <span style="font-family:var(--font-title-montserrat);font-weight:800;color:#fff;font-size:.95rem;text-transform:uppercase;letter-spacing:.5px">Boletín Informativo Diario</span>
      </div>
      <p style="font-size:.78rem;color:#94A3B8;margin:0">Recibe las noticias más importantes y el resumen del día directamente en tu correo electrónico. Cero spam.</p>
    </div>
    <form id="footer-newsletter-form" onsubmit="handleNewsletterSubmit(event, 'Footer')" style="display:flex;gap:8px;flex:1;min-width:280px;max-width:500px">
      <input type="email" id="footer-newsletter-email" name="email" required placeholder="Tu correo electrónico..." style="flex:1;padding:10px 14px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.3);color:#fff;border-radius:2px;font-size:.85rem;outline:none">
      <button type="submit" id="footer-newsletter-btn" style="background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.75rem;text-transform:uppercase;padding:10px 18px;border:none;border-radius:2px;cursor:pointer;white-space:nowrap;transition:background .2s" onmouseover="this.style.background='var(--color-red-dark)'" onmouseout="this.style.background='var(--color-red)'">
        SUSCRIBIRME
      </button>
    </form>
  </div>
  <div id="footer-newsletter-msg" style="display:none;margin-top:10px;font-size:.8rem;padding:6px 12px;border-radius:2px"></div>
</div>
