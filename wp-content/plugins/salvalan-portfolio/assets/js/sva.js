(function(){var d=document,h=d.documentElement;
function $(s,c){return(c||d).querySelector(s)}function $$(s,c){return[].slice.call((c||d).querySelectorAll(s))}
function toast(t){var e=d.createElement('div');e.className='toast';e.setAttribute('role','status');e.textContent=t;d.body.appendChild(e);setTimeout(function(){e.remove()},1800)}
/* theme */
function setT(t){h.setAttribute('data-theme',t);try{localStorage.setItem('sva-theme',t)}catch(e){}$$('[data-theme-toggle]').forEach(function(b){b.setAttribute('aria-pressed',t==='dark')});var m=$('meta[name=theme-color]');if(m)m.setAttribute('content',t==='dark'?'#0B0B0F':'#F3EEE4')}
$$('[data-theme-toggle]').forEach(function(b){b.setAttribute('aria-pressed',h.getAttribute('data-theme')==='dark');b.addEventListener('click',function(){setT(h.getAttribute('data-theme')==='dark'?'light':'dark')})});

/* ---- long-scroll navigation: smooth glide, scroll-spy, section title, progress bar ---- */
var SITE='SalValAn',base=d.title,rm=matchMedia('(prefers-reduced-motion:reduce)');
var hd=$('.sva-hd'),bar=$('.prog i',hd),tt=$('.totop'),ttl=$('[data-title]',hd);
var links=$$('.nav a[href^="#"],.tabs a[href^="#"],.drawer .pn a[href^="#"]');
var secs=$$('main > section[id]').filter(function(s){return links.some(function(a){return a.getAttribute('href')==='#'+s.id})});
var labels={};links.forEach(function(a){var i=a.getAttribute('href').slice(1),t=a.textContent.replace(/\s+/g,' ').trim();if(i&&t&&!labels[i])labels[i]=t});
var cur='',gl=false,first=true,anim=0;
function setTitle(id){
 var t=labels[id]||'';
 if(ttl&&t&&ttl.textContent!==t){ttl.textContent=t;try{ttl.animate([{opacity:0,transform:'translateY(6px)'},{opacity:1,transform:'none'}],{duration:260,easing:'cubic-bezier(.2,.8,.2,1)'})}catch(e){}}
 d.title=(id==='home'||!t)?base:t+' · '+SITE;
}
function mark(id){
 if(id===cur)return;cur=id;
 links.forEach(function(a){if(a.getAttribute('href')==='#'+id)a.setAttribute('aria-current','true');else a.removeAttribute('aria-current')});
 setTitle(id);
 if(first){first=false;return}
 try{history.replaceState(null,'',id==='home'?location.pathname+location.search:'#'+id)}catch(e){}
}
function spy(){
 var max=h.scrollHeight-innerHeight,y0=scrollY;
 if(bar)bar.style.transform='scaleX('+(max>0?Math.min(1,y0/max):0)+')';
 if(hd)hd.classList.toggle('sc',y0>8);
 if(tt)tt.classList.toggle('show',y0>700);
 if(!secs.length||gl)return;
 var y=innerHeight*.35,id=secs[0].id;
 for(var i=0;i<secs.length;i++){if(secs[i].getBoundingClientRect().top<=y)id=secs[i].id}
 if(max>0&&y0>=max-4)id=secs[secs.length-1].id;
 mark(id);
}
var tk=false;function tick(){if(tk)return;tk=true;requestAnimationFrame(function(){tk=false;spy()})}
addEventListener('scroll',tick,{passive:true});addEventListener('resize',tick);
/* our own eased scroll: same on every phone, PWA and desktop, and the user can interrupt it */
function ease(t){return t<.5?4*t*t*t:1-Math.pow(-2*t+2,3)/2}
function stopGlide(){if(anim){cancelAnimationFrame(anim);anim=0}if(gl){gl=false;tick()}}
function glide(y){
 stopGlide();
 var max=Math.max(0,h.scrollHeight-innerHeight);y=Math.max(0,Math.min(y,max));
 var y0=scrollY,dist=y-y0;
 if(Math.abs(dist)<2||rm.matches){scrollTo(0,y);tick();return}
 gl=true;var dur=Math.min(1100,Math.max(450,Math.abs(dist)*.4)),t0=0;
 function step(t){
  if(!t0)t0=t;var p=Math.min(1,(t-t0)/dur);
  scrollTo(0,y0+dist*ease(p));
  if(p<1)anim=requestAnimationFrame(step);else{anim=0;gl=false;spy()}
 }
 anim=requestAnimationFrame(step);
}
function glideTo(t,id){
 var off=parseFloat(getComputedStyle(h).scrollPaddingTop)||70;
 glide(id==='home'?0:t.getBoundingClientRect().top+scrollY-off);
}
['wheel','touchstart','mousedown','keydown'].forEach(function(n){addEventListener(n,function(e){if(anim&&!(n==='keydown'&&e.key==='Escape'))stopGlide()},{passive:true})});
/* every in-page link (header, drawer, tab bar, buttons, footer) goes through the glide */
d.addEventListener('click',function(e){
 var a=e.target.closest&&e.target.closest('a[href^="#"]');
 if(!a||a.classList.contains('skip')||e.defaultPrevented||e.button>0||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey)return;
 var id=a.getAttribute('href').slice(1);if(!id)return;
 try{id=decodeURIComponent(id)}catch(x){}
 var t=d.getElementById(id);if(!t)return;
 e.preventDefault();
 var s=t.closest('main > section[id]');if(s&&secs.indexOf(s)>-1){stopGlide();gl=true;mark(s.id)}
 if(dr&&dr.contains(a))dx(false,true);
 requestAnimationFrame(function(){glideTo(t,id)});
});
spy();
/* opened with #section (PWA shortcut, shared link): settle on it once fonts and images have laid out */
addEventListener('load',function(){var i=location.hash.slice(1),t=i&&d.getElementById(i);if(t&&!scrollY){gl=false;glideTo(t,i);}tick()});

/* reveal on scroll */
h.classList.add('js');
if('IntersectionObserver' in window&&!matchMedia('(prefers-reduced-motion:reduce)').matches){
 var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){var t=e.target;t.classList.add('in');io.unobserve(t);setTimeout(function(){t.classList.remove('rv','in')},1300)}})},{rootMargin:'0px 0px -8% 0px',threshold:.05});
 $$('.rv').forEach(function(el){io.observe(el)});
}else{$$('.rv').forEach(function(el){el.classList.remove('rv')})}
/* animations only run while visible (saves battery and memory on phones) */
if('IntersectionObserver' in window){var po=new IntersectionObserver(function(es){es.forEach(function(e){e.target.classList.toggle('off',!e.isIntersecting)})},{rootMargin:'160px 0px'});$$('.mq,.feat').forEach(function(el){po.observe(el)})}
/* hero spotlight follows the pointer and takes the colour of the hovered role */
var hero=$('.hero'),sp=$('.spot');
if(hero&&sp&&matchMedia('(pointer:fine)').matches){
 hero.addEventListener('pointermove',function(e){var r=hero.getBoundingClientRect();sp.style.setProperty('--mx',(e.clientX-r.left)+'px');sp.style.setProperty('--my',(e.clientY-r.top)+'px')});
 $$('.big .ln,.mem',hero).forEach(function(n){n.addEventListener('pointerenter',function(){var k=n.getAttribute('data-r');sp.style.setProperty('--c0','var(--'+k+')')})});
}
/* magnetic buttons */
if(matchMedia('(pointer:fine)').matches&&!matchMedia('(prefers-reduced-motion:reduce)').matches){
 $$('.mag').forEach(function(b){b.addEventListener('pointermove',function(e){var r=b.getBoundingClientRect();b.style.transform='translate('+((e.clientX-r.left-r.width/2)*.25)+'px,'+((e.clientY-r.top-r.height/2)*.35)+'px)'});b.addEventListener('pointerleave',function(){b.style.transform=''})});
}
/* team panels: tap to open on touch screens */
var touchy=matchMedia('(hover:none),(max-width:700px)').matches;
$$('.mem').forEach(function(m){m.addEventListener('click',function(e){if(e.target.closest('a')||!touchy)return;var o=m.classList.contains('on');$$('.mem').forEach(function(x){x.classList.remove('on')});if(!o){m.classList.add('on');setTimeout(function(){var r=m.getBoundingClientRect();if(r.top<76||r.bottom>innerHeight-84)glide(scrollY+r.top-84)},650)}});m.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();m.click()}})});
if(touchy){var f1=$('.mem');f1&&f1.classList.add('on')}

/* drawer */
var dr=$('#sva-drawer'),pn=dr&&$('.pn',dr),opener=null;
function dx(o,keepFocus){
 if(!dr)return;o=!!o;if(o===dr.classList.contains('open'))return;
 dr.classList.toggle('open',o);h.classList.toggle('menu-open',o);
 $$('[data-menu]').forEach(function(b){b.setAttribute('aria-expanded',o);b.setAttribute('aria-label',o?'Close menu':b.getAttribute('data-label')||'Open menu')});
 if(o){
  opener=d.activeElement;dr.scrollLeft=0;dr.scrollTop=0;
  /* preventScroll: focusing an element that is still off-screen used to scroll the drawer sideways, which was the left/right blink */
  var c=$('.close',dr);if(c)try{c.focus({preventScroll:true})}catch(e){c.focus()}
 }else{
  if(!keepFocus&&opener&&opener.focus)try{opener.focus({preventScroll:true})}catch(e){}
  opener=null;
 }
}
$$('[data-menu]').forEach(function(b){b.setAttribute('data-label',b.getAttribute('aria-label')||'Open menu');b.addEventListener('click',function(e){e.preventDefault();dx(!(dr&&dr.classList.contains('open')))})});
if(dr){$$('[data-close]',dr).forEach(function(e){e.addEventListener('click',function(){dx(false)})})}
d.addEventListener('keydown',function(e){
 if(!dr||!dr.classList.contains('open'))return;
 if(e.key==='Escape'){dx(false);return}
 if(e.key==='Tab'){var f=$$('a[href],button',pn).filter(function(x){return !x.hidden&&x.offsetParent!==null});if(!f.length)return;var a=f[0],z=f[f.length-1];
  if(e.shiftKey&&d.activeElement===a){e.preventDefault();z.focus()}else if(!e.shiftKey&&d.activeElement===z){e.preventDefault();a.focus()}else if(!pn.contains(d.activeElement)){e.preventDefault();a.focus()}}
});
/* never leave the page locked: wide screens, rotation, back/forward cache */
matchMedia('(min-width:961px)').addEventListener&&matchMedia('(min-width:961px)').addEventListener('change',function(m){if(m.matches)dx(false,true)});
addEventListener('pageshow',function(e){if(e.persisted){dx(false,true);h.classList.remove('menu-open')}});
addEventListener('pagehide',function(){h.classList.remove('menu-open')});
/* filters */
$$('[data-filters]').forEach(function(g){var box=$(g.getAttribute('data-filters'));if(!box)return;$$('button',g).forEach(function(b){b.addEventListener('click',function(){var f=b.getAttribute('data-f');$$('button',g).forEach(function(x){x.setAttribute('aria-pressed',x===b)});$$('[data-r]',box).forEach(function(i){i.hidden=!(f==='all'||(' '+i.getAttribute('data-r')+' ').indexOf(' '+f+' ')>-1)})})})});
/* copy */
$$('[data-copy]').forEach(function(b){b.addEventListener('click',function(){var t=b.getAttribute('data-copy');(navigator.clipboard?navigator.clipboard.writeText(t):Promise.reject()).then(function(){toast('Copied: '+t)},function(){var i=d.createElement('input');i.value=t;d.body.appendChild(i);i.select();try{d.execCommand('copy');toast('Copied: '+t)}catch(e){toast('Copy failed')}i.remove()})})});
/* contact form -> mailto, prefilled by "Message ..." and "Ask about this" buttons */
var f=$('#sva-form');if(f){
 var auto='';
 function fill(who,sv){var to=f.elements.to,m=f.elements.msg;
  if(who&&to)$$('option',to).forEach(function(o){if(o.getAttribute('data-k')===who)to.value=o.value});
  if(sv&&m&&(m.value===''||m.value===auto)){auto='Hi, I would like to ask about: '+sv+'.\n\n';m.value=auto}}
 var q=new URLSearchParams(location.search);fill(q.get('to'),q.get('service'));
 $$('[data-to]').forEach(function(a){a.addEventListener('click',function(){fill(a.getAttribute('data-to'),a.getAttribute('data-service'))})});
 f.addEventListener('submit',function(e){e.preventDefault();if(!f.checkValidity()){f.reportValidity();return}var v=f.elements;var s=encodeURIComponent('Portfolio message from '+v.name.value);var b=encodeURIComponent(v.msg.value+'\n\n- '+v.name.value+' ('+v.email.value+')');location.href='mailto:'+v.to.value+'?subject='+s+'&body='+b;toast('Opening your email app…')})}
/* top */
$$('[data-top]').forEach(function(b){b.addEventListener('click',function(){glide(0)})});
/* install (PWA) */
var ev=null,ios=/iphone|ipad|ipod/i.test(navigator.userAgent),st=matchMedia('(display-mode:standalone)').matches||navigator.standalone;
function showI(){$$('[data-install]').forEach(function(b){b.hidden=false})}
addEventListener('beforeinstallprompt',function(e){e.preventDefault();ev=e;showI()});
if(st)h.classList.add('standalone');if(ios&&!st)showI();
$$('[data-install]').forEach(function(b){b.addEventListener('click',function(){if(ev){ev.prompt();ev.userChoice.then(function(){ev=null;$$('[data-install]').forEach(function(x){x.hidden=true})})}else toast(ios?'Tap the Share icon, then Add to Home Screen':'Open the browser menu, then Install app')})});
addEventListener('appinstalled',function(){$$('[data-install]').forEach(function(x){x.hidden=true})});
/* service worker */
if('serviceWorker' in navigator&&window.SVA&&SVA.sw){addEventListener('load',function(){navigator.serviceWorker.register(SVA.sw,{scope:SVA.scope||'/'}).catch(function(){})})}
})();
