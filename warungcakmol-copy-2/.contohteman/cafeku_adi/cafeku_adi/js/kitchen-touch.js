// kitchen-touch.js
// Simple ripple and touch feedback for Kitchen pages
(function(){
  function createRipple(ev, el) {
    const rect = el.getBoundingClientRect();
    const ripple = document.createElement('span');
    ripple.className = 'ripple';
    const size = Math.max(rect.width, rect.height) * 1.2;
    ripple.style.width = ripple.style.height = size + 'px';
    const x = (ev.clientX || (ev.touches && ev.touches[0].clientX)) - rect.left - size/2;
    const y = (ev.clientY || (ev.touches && ev.touches[0].clientY)) - rect.top - size/2;
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.style.position = 'absolute';
    ripple.style.overflow = 'hidden';
    ripple.style.pointerEvents = 'none';
    el.style.position = el.style.position || 'relative';
    el.appendChild(ripple);
    setTimeout(()=>{ try{ el.removeChild(ripple);}catch(e){} }, 600);
  }

  function bindRipple(selector){
    const els = document.querySelectorAll(selector);
    els.forEach(el => {
      el.addEventListener('pointerdown', function(ev){
        createRipple(ev, el);
      }, {passive:true});
    });
  }

  function bindTouchPress(selector){
    const els = document.querySelectorAll(selector);
    els.forEach(el => {
      el.addEventListener('pointerdown', function(){
        el.classList.add('touch-press');
      });
      function clear(){ el.classList.remove('touch-press'); }
      el.addEventListener('pointerup', clear);
      el.addEventListener('pointercancel', clear);
      el.addEventListener('pointerout', clear);
      el.addEventListener('pointerleave', clear);
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    bindRipple('.btn, .order-item');
    bindTouchPress('.order-item');
  });
})();
