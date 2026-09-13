<!-- ========== POPUP PROMO MODAL ========== -->
@if(isset($banners['popup']) && $banners['popup']->count() > 0)
  @php $popupBanner = $banners['popup']->first(); @endphp
  <div id="promoModal" style="position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.85);backdrop-filter:blur(4px);opacity:0;pointer-events:none;transition:opacity .3s">
    <div id="promoModalContent" style="max-width:500px;width:90%;transform:scale(.9);opacity:0;transition:transform .3s,opacity .3s">
      <div style="position:relative;background:#000;border:6px solid var(--color-navy);border-radius:2px;overflow:hidden">
        <button onclick="closePromoModal()" style="position:absolute;top:8px;right:12px;z-index:10;background:none;border:none;color:var(--color-red);font-size:1.5rem;cursor:pointer"><i class="fas fa-times"></i></button>
        <a href="{{ $popupBanner->link ?? '#' }}" {{ $popupBanner->link ? 'target="_blank"' : '' }} style="display:block"><img src="{{ asset($popupBanner->image_path) }}" alt="{{ $popupBanner->title }}" style="width:100%;height:auto;display:block"></a>
      </div>
      <div style="text-align:center;margin-top:12px"><span style="font-size:.7rem;background:rgba(0,0,0,.6);color:#94A3B8;padding:4px 12px;border-radius:2px">Presiona [Z] para cerrar</span></div>
    </div>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded',function(){setTimeout(()=>{const m=document.getElementById('promoModal'),c=document.getElementById('promoModalContent');if(m&&c){m.style.opacity='1';m.style.pointerEvents='auto';c.style.transform='scale(1)';c.style.opacity='1';document.body.style.overflow='hidden'}},1500);document.addEventListener('keydown',e=>{if(e.key==='z'||e.key==='Z')closePromoModal()})});
  function closePromoModal(){const m=document.getElementById('promoModal'),c=document.getElementById('promoModalContent');if(m&&c){c.style.transform='scale(.9)';c.style.opacity='0';m.style.opacity='0';m.style.pointerEvents='none';document.body.style.overflow=''}}
  </script>
@endif
