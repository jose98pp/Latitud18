<!-- ========== PWA & GLOBAL SCRIPTS ========== -->
<script src="{{ asset('js/dark-mode.js') }}"></script>
<script src="{{ asset('js/error-handler.js') }}"></script>
<script>
// Theme Management
function toggleTheme(){
  const h=document.documentElement,c=h.getAttribute('data-theme'),n=c==='dark'?'light':'dark';
  h.setAttribute('data-theme',n);
  localStorage.setItem('latitud18-theme',n);
  applyTheme(n);
}
function applyTheme(t){
  const s=document.getElementById('themeIconSun'),m=document.getElementById('themeIconMoon');
  if(s)s.style.display=t==='dark'?'inline':'none';
  if(m)m.style.display=t==='light'?'inline':'none';
  const me=document.querySelector('meta[name="theme-color"]');
  if(me)me.setAttribute('content',t==='dark'?'#080E18':'#0B1F3A');
}
(function(){
  const s=localStorage.getItem('latitud18-theme'),pd=window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches,t=s||(pd?'dark':'light');
  document.documentElement.setAttribute('data-theme',t);
  applyTheme(t);
})();

// Mobile Menu Navigation
function openMobileMenu(){
  document.getElementById('mobileMenu').style.transform='translateX(0)';
  document.getElementById('mobileMenuOverlay').style.opacity='1';
  document.getElementById('mobileMenuOverlay').style.pointerEvents='auto';
  document.body.style.overflow='hidden';
}
function closeMobileMenu(){
  document.getElementById('mobileMenu').style.transform='translateX(-100%)';
  document.getElementById('mobileMenuOverlay').style.opacity='0';
  document.getElementById('mobileMenuOverlay').style.pointerEvents='none';
  document.body.style.overflow='';
}

// Search Modal
function openSearchModal(){
  document.getElementById('search-modal-suite').classList.add('active');
  setTimeout(()=>{const i=document.getElementById('site-search-input');if(i)i.focus()},100);
}
function closeSearchModal(){
  document.getElementById('search-modal-suite').classList.remove('active');
}

// Live Modal & TV Player Controller
let isTvPlaying = true;
let isTvMuted = false;
let isGraphicsVisible = true;

function sendTvCommand(func, args) {
  const iframe = document.getElementById('liveTvIframe');
  if (!iframe || !iframe.contentWindow) return;
  try {
    iframe.contentWindow.postMessage(JSON.stringify({
      event: 'command',
      func: func,
      args: args || []
    }), '*');
  } catch (err) {
    console.warn('Error enviando comando a YouTube:', err);
  }
}

function openLiveModal(videoId, title) {
  const modal = document.getElementById('live-streaming-modal-suite');
  if (!modal) return;
  modal.classList.add('active');
  document.body.style.overflow = 'hidden';

  const iframe = document.getElementById('liveTvIframe');
  const headline = document.getElementById('lt-headline-text');
  const subheadline = document.getElementById('lt-subheadline-text');
  const headerTitle = document.getElementById('liveModalTitleHeader');
  const extBtn = document.getElementById('btnTvExternal');

  const defaultYtId = iframe ? iframe.getAttribute('data-default-id') : 'lvbgd2JETfI';
  const targetId = videoId || defaultYtId || 'lvbgd2JETfI';

  if (title) {
    if (headline) headline.textContent = title;
    if (subheadline) subheadline.textContent = 'Transmisión en directo';
    if (headerTitle) headerTitle.textContent = title.toUpperCase();
  }

  if (extBtn) {
    extBtn.href = `https://www.youtube.com/watch?v=${targetId}`;
  }

  // Recarga e inicializa el iframe de YouTube asegurando reproducción limpia
  if (iframe) {
    const origin = encodeURIComponent(window.location.origin);
    const newSrc = `https://www.youtube-nocookie.com/embed/${targetId}?autoplay=1&mute=0&enablejsapi=1&rel=0&playsinline=1&controls=1&origin=${origin}`;
    
    if (iframe.src !== newSrc) {
      iframe.src = newSrc;
    } else {
      sendTvCommand('playVideo');
    }
  }

  isTvPlaying = true;
  const playBtn = document.getElementById('btnTvPlayPause');
  if (playBtn) {
    const icon = playBtn.querySelector('i');
    const label = playBtn.querySelector('span');
    if (icon) icon.className = 'fas fa-pause';
    if (label) label.textContent = 'Pausar';
    playBtn.classList.remove('paused');
  }
}

function closeLiveModal() {
  const modal = document.getElementById('live-streaming-modal-suite');
  if (modal) modal.classList.remove('active');
  document.body.style.overflow = '';

  // Pausar video de inmediato para silenciar audio
  sendTvCommand('pauseVideo');

  // Si estaba en pantalla completa, salir
  if (document.fullscreenElement || document.webkitFullscreenElement) {
    if (document.exitFullscreen) document.exitFullscreen();
    else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
  }

  // Detener radio si estaba sonando
  const a = document.getElementById('global-radio-audio');
  if (a && !a.paused) { 
    a.pause(); 
    const icon = document.getElementById('radio-play-icon'); 
    if (icon) { icon.className = 'fas fa-play'; } 
  }
}

function toggleTvPlayPause() {
  const btn = document.getElementById('btnTvPlayPause');
  const icon = btn ? btn.querySelector('i') : null;
  const label = btn ? btn.querySelector('span') : null;

  if (isTvPlaying) {
    sendTvCommand('pauseVideo');
    isTvPlaying = false;
    if (icon) icon.className = 'fas fa-play';
    if (label) label.textContent = 'Reanudar';
    if (btn) btn.classList.add('paused');
  } else {
    sendTvCommand('playVideo');
    isTvPlaying = true;
    if (icon) icon.className = 'fas fa-pause';
    if (label) label.textContent = 'Pausar';
    if (btn) btn.classList.remove('paused');
  }
}

function toggleTvMute() {
  const btn = document.getElementById('btnTvMute');
  const icon = btn ? btn.querySelector('i') : null;
  const label = btn ? btn.querySelector('span') : null;

  if (isTvMuted) {
    sendTvCommand('unMute');
    isTvMuted = false;
    if (icon) icon.className = 'fas fa-volume-up';
    if (label) label.textContent = 'Audio';
    if (btn) btn.classList.remove('muted');
  } else {
    sendTvCommand('mute');
    isTvMuted = true;
    if (icon) icon.className = 'fas fa-volume-mute';
    if (label) label.textContent = 'Silenciado';
    if (btn) btn.classList.add('muted');
  }
}

function toggleTvFullscreen() {
  const frame = document.getElementById('tvSetWrapper') || document.getElementById('tvScreenFrame');
  if (!frame) return;

  const isFs = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;

  if (!isFs) {
    if (frame.requestFullscreen) {
      frame.requestFullscreen().catch(err => {
        const ifr = document.getElementById('liveTvIframe');
        if (ifr && ifr.requestFullscreen) ifr.requestFullscreen();
      });
    } else if (frame.webkitRequestFullscreen) {
      frame.webkitRequestFullscreen();
    } else if (frame.mozRequestFullScreen) {
      frame.mozRequestFullScreen();
    } else if (frame.msRequestFullscreen) {
      frame.msRequestFullscreen();
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen();
    }
  }
}

function toggleTvGraphics() {
  const box = document.getElementById('lower-third-box');
  const btn = document.getElementById('btnToggleGraphics');
  if (!box) return;
  const icon = btn ? btn.querySelector('i') : null;
  const label = btn ? btn.querySelector('span') : null;

  isGraphicsVisible = !isGraphicsVisible;
  if (isGraphicsVisible) {
    box.classList.remove('hidden-graphics');
    if (icon) icon.className = 'fas fa-eye-slash';
    if (label) label.textContent = 'Ocultar Zócalo';
  } else {
    box.classList.add('hidden-graphics');
    if (icon) icon.className = 'fas fa-eye';
    if (label) label.textContent = 'Mostrar Zócalo';
  }
}

function reloadLiveTv() {
  const iframe = document.getElementById('liveTvIframe');
  if (!iframe) return;
  const currSrc = iframe.src;
  iframe.src = '';
  setTimeout(() => {
    iframe.src = currSrc;
  }, 100);
}

// Sincronizar icono del botón al cambiar estado de pantalla completa
function updateTvFullscreenButtonState() {
  const isFs = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement;
  const btn = document.getElementById('btnTvFullscreen');
  if (!btn) return;
  const icon = btn.querySelector('i');
  const label = btn.querySelector('span');
  if (isFs) {
    if (icon) icon.className = 'fas fa-compress';
    if (label) label.textContent = 'Salir';
  } else {
    if (icon) icon.className = 'fas fa-expand';
    if (label) label.textContent = 'Pantalla Completa';
  }
}
document.addEventListener('fullscreenchange', updateTvFullscreenButtonState);
document.addEventListener('webkitfullscreenchange', updateTvFullscreenButtonState);
document.addEventListener('mozfullscreenchange', updateTvFullscreenButtonState);

// Atajos de teclado cuando el modal está abierto
document.addEventListener('keydown', (e) => {
  const modal = document.getElementById('live-streaming-modal-suite');
  if (!modal || !modal.classList.contains('active')) return;
  
  // No interferir si el usuario está escribiendo en el chat
  const tag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
  if (tag === 'input' || tag === 'textarea') return;

  if (e.key === 'Escape') {
    if (!document.fullscreenElement) {
      closeLiveModal();
    }
  } else if (e.key === 'f' || e.key === 'F') {
    e.preventDefault();
    toggleTvFullscreen();
  } else if (e.key === ' ' || e.code === 'Space') {
    e.preventDefault();
    toggleTvPlayPause();
  } else if (e.key === 'm' || e.key === 'M') {
    e.preventDefault();
    toggleTvMute();
  }
});

// Switcher de Rótulos
function switchLowerThird(badge, headline, sub, btn) {
  const box = document.getElementById('lower-third-box');
  if (box && !isGraphicsVisible) {
    toggleTvGraphics();
  }
  document.getElementById('lt-badge-text').textContent = badge;
  document.getElementById('lt-headline-text').textContent = headline;
  document.getElementById('lt-subheadline-text').textContent = sub;
  document.querySelectorAll('.btn-lt-switcher').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
}

// Live Chat Simulator
function sendChatMessage(e){
  e.preventDefault();
  const input=document.getElementById('chat-input-text'),container=document.getElementById('chat-messages-container');
  if(!input.value.trim())return;
  const msg=document.createElement('div');
  msg.className='chat-msg';
  msg.innerHTML=`<strong>Tú:</strong> ${input.value}`;
  container.appendChild(msg);
  container.scrollTop=container.scrollHeight;
  input.value='';
  setTimeout(()=>{
    const reply=document.createElement('div');
    reply.className='chat-msg admin';
    reply.innerHTML=`<strong style="color:var(--color-red)">LATITUD18:</strong> Gracias por tu mensaje. ¡Sigue interactuando!`;
    container.appendChild(reply);
    container.scrollTop=container.scrollHeight;
  },1500);
}

// PWA Service Worker & Install Prompt
let deferredPrompt;
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(reg => console.log('PWA Service Worker registrado con éxito:', reg.scope))
      .catch(err => console.log('Error registrando Service Worker:', err));
  });
}

window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  const btn = document.getElementById('btn-pwa-install');
  if (btn) btn.style.display = 'inline-flex';
});

function triggerPwaInstall() {
  if (!deferredPrompt) return;
  deferredPrompt.prompt();
  deferredPrompt.userChoice.then((choiceResult) => {
    if (choiceResult.outcome === 'accepted') {
      console.log('El usuario aceptó la instalación de la PWA');
    }
    deferredPrompt = null;
    const btn = document.getElementById('btn-pwa-install');
    if (btn) btn.style.display = 'none';
  });
}

window.addEventListener('appinstalled', () => {
  const btn = document.getElementById('btn-pwa-install');
  if (btn) btn.style.display = 'none';
  deferredPrompt = null;
});

// AJAX Newsletter Subscription Handler
function handleNewsletterSubmit(event, origin = 'Portal') {
  event.preventDefault();
  const form = event.target;
  const emailInput = form.querySelector('input[type="email"]');
  const submitBtn = form.querySelector('button[type="submit"]');
  const msgContainer = form.id === 'footer-newsletter-form' 
    ? document.getElementById('footer-newsletter-msg') 
    : (document.getElementById(form.dataset.msgTarget) || document.getElementById('footer-newsletter-msg'));

  if (!emailInput || !emailInput.value.trim()) return;

  const email = emailInput.value.trim();
  const originalBtnText = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const token = csrfMeta ? csrfMeta.getAttribute('content') : '';

  fetch('{{ route("newsletter.subscribe") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': token,
      'Accept': 'application/json'
    },
    body: JSON.stringify({ email: email, origen: origin })
  })
  .then(res => res.json())
  .then(data => {
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalBtnText;

    if (msgContainer) {
      msgContainer.style.display = 'block';
      if (data.success) {
        msgContainer.style.background = 'rgba(16, 185, 129, 0.2)';
        msgContainer.style.color = '#34d399';
        msgContainer.style.border = '1px solid rgba(16, 185, 129, 0.4)';
        msgContainer.innerHTML = `<i class="fas fa-check-circle me-1"></i> ${data.message}`;
        emailInput.value = '';
      } else {
        msgContainer.style.background = 'rgba(239, 68, 68, 0.2)';
        msgContainer.style.color = '#f87171';
        msgContainer.style.border = '1px solid rgba(239, 68, 68, 0.4)';
        msgContainer.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> ${data.message || 'Hubo un error al suscribirse.'}`;
      }
    } else {
      alert(data.message);
    }
  })
  .catch(err => {
    console.error(err);
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalBtnText;
    if (msgContainer) {
      msgContainer.style.display = 'block';
      msgContainer.style.background = 'rgba(239, 68, 68, 0.2)';
      msgContainer.style.color = '#f87171';
      msgContainer.innerHTML = 'Error de conexión. Inténtalo de nuevo.';
    }
  });
}

document.addEventListener('DOMContentLoaded',function(){
  const el=document.getElementById('topbar-date-text');
  if(el){
    const o={weekday:'long',day:'numeric',month:'long',year:'numeric'};
    let d=new Date().toLocaleDateString('es-ES',o);
    d=d.split(' ').map(w=>w.length>2&&w!=='de'?w.charAt(0).toUpperCase()+w.slice(1):w).join(' ');
    el.innerHTML='<i class="far fa-calendar-alt" style="margin-right:6px;color:var(--color-red)"></i>'+d+', Bolivia';
  }
  document.querySelectorAll('.nav-item-link').forEach(l=>{
    if(l.getAttribute('href')===window.location.pathname) l.style.borderBottomColor='#D71920';
  });
  const t=document.getElementById('mobileMenuToggle');
  if(t){
    t.style.display=window.innerWidth>=1024?'none':'block';
    t.addEventListener('click',openMobileMenu);
    window.addEventListener('resize',()=>{t.style.display=window.innerWidth>=1024?'none':'block'});
  }
  document.addEventListener('keydown',e=>{
    if(e.key==='Escape'){
      closeMobileMenu();
      closeSearchModal();
      closeLiveModal();
    }
  });
});
</script>
