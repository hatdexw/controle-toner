// UI enhancements: theme toggle, mobile menu, intersection reveal
(function(){
  function qs(s){return document.querySelector(s);} 
  const html = document.documentElement;
  function setTheme(mode){
    if(mode==='dark'){html.classList.add('dark');localStorage.setItem('theme','dark');}
    else {html.classList.remove('dark');localStorage.setItem('theme','light');}
    syncIcons();
  }
  function toggleTheme(){ html.classList.contains('dark') ? setTheme('light') : setTheme('dark'); }
  function syncIcons(){
    document.querySelectorAll('.theme-icon-sun').forEach(el=>el.classList.toggle('hidden',html.classList.contains('dark')));
    document.querySelectorAll('.theme-icon-moon').forEach(el=>el.classList.toggle('hidden',!html.classList.contains('dark')));
  }
  document.addEventListener('DOMContentLoaded',()=>{
    const t1 = qs('#themeToggle');
    const t2 = qs('#themeToggleMobile');
    t1 && t1.addEventListener('click',toggleTheme);
    t2 && t2.addEventListener('click',toggleTheme);
    syncIcons();
    const mobileBtn = qs('#mobileMenuBtn');
    const panel = qs('#mobilePanel');
    mobileBtn && mobileBtn.addEventListener('click',()=>panel.classList.toggle('hidden'));
    const observer = new IntersectionObserver(entries=>{
      entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('animate-fade-in'); observer.unobserve(e.target);} });
    },{threshold:.08});
    document.querySelectorAll('.glass-card').forEach(el=>observer.observe(el));
  });
})();
