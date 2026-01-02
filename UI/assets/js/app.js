// tiny helpers (no framework)
(function(){
  const toasts = document.querySelectorAll("[data-auto-dismiss]");
  toasts.forEach(el=>{
    const ms = parseInt(el.getAttribute("data-auto-dismiss"),10) || 3500;
    setTimeout(()=>{ el.style.opacity = "0"; el.style.transform="translateY(-4px)"; }, ms);
    setTimeout(()=>{ el.remove(); }, ms+450);
  });

  // confirm buttons
  document.querySelectorAll("[data-confirm]").forEach(btn=>{
    btn.addEventListener("click",(e)=>{
      const msg = btn.getAttribute("data-confirm") || "Yakin?";
      if(!confirm(msg)){ e.preventDefault(); }
    });
  });
})();
