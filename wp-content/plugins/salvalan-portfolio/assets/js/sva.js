(function(){var d=document,h=d.documentElement;
function $(s,c){return(c||d).querySelector(s)}function $$(s,c){return[].slice.call((c||d).querySelectorAll(s))}
function toast(t){var e=d.createElement('div');e.className='toast';e.setAttribute('role','status');e.textContent=t;d.body.appendChild(e);setTimeout(function(){e.remove()},1800)}
/* theme */
function setT(t){h.setAttribute('data-theme',t);try{localStorage.setItem('sva-theme',t)}catch(e){}$$('[data-theme-toggle]').forEach(function(b){b.setAttribute('aria-pressed',t==='dark')});var m=$('meta[name=theme-color]');if(m)m.setAttribute('content',t==='dark'?'#0A1124':'#F1F3F8')}
$$('[data-theme-toggle]').forEach(function(b){b.setAttribute('aria-pressed',h.getAttribute('data-theme')==='dark');b.addEventListener('click',function(){setT(h.getAttribute('data-theme')==='dark'?'light':'dark')})});
/* active nav */
var p=location.pathname.replace(/\/+$/,'')||'/';
$$('.nav a,.tabs a,.drawer .pn a').forEach(function(a){var u;try{u=new URL(a.href).pathname.replace(/\/+$/,'')||'/'}catch(e){return}if(u===p)a.setAttribute('aria-current','page')});
/* header shadow once scrolled */
var hd=$('.sva-hd');if(hd){var tk=false;addEventListener('scroll',function(){if(tk)return;tk=true;requestAnimationFrame(function(){hd.classList.toggle('sc',scrollY>8);tk=false})},{passive:true})}
/* drawer */
var dr=$('#sva-drawer');function dx(o){if(!dr)return;dr.classList.toggle('open',o);h.style.overflow=o?'hidden':'';$$('[data-menu]').forEach(function(b){b.setAttribute('aria-expanded',o)});if(o){var c=$('.close',dr);c&&c.focus()}}
$$('[data-menu]').forEach(function(b){b.addEventListener('click',function(){dx(!dr.classList.contains('open'))})});
if(dr){$$('[data-close],.pn a',dr).forEach(function(e){e.addEventListener('click',function(){dx(false)})})}
d.addEventListener('keydown',function(e){if(e.key==='Escape')dx(false)});
/* filters */
$$('[data-filters]').forEach(function(g){var box=$(g.getAttribute('data-filters'));if(!box)return;$$('button',g).forEach(function(b){b.addEventListener('click',function(){var f=b.getAttribute('data-f');$$('button',g).forEach(function(x){x.setAttribute('aria-pressed',x===b)});$$('[data-r]',box).forEach(function(i){i.hidden=!(f==='all'||(' '+i.getAttribute('data-r')+' ').indexOf(' '+f+' ')>-1)})})})});
/* copy */
$$('[data-copy]').forEach(function(b){b.addEventListener('click',function(){var t=b.getAttribute('data-copy');(navigator.clipboard?navigator.clipboard.writeText(t):Promise.reject()).then(function(){toast('Copied: '+t)},function(){var i=d.createElement('input');i.value=t;d.body.appendChild(i);i.select();try{d.execCommand('copy');toast('Copied: '+t)}catch(e){toast('Copy failed')}i.remove()})})});
/* contact form -> mailto */
var f=$('#sva-form');if(f){var q=new URLSearchParams(location.search);var to=f.elements.to,sv=q.get('service'),who=q.get('to');if(who&&to)$$('option',to).forEach(function(o){if(o.getAttribute('data-k')===who)to.value=o.value});if(sv&&f.elements.msg&&!f.elements.msg.value)f.elements.msg.value='Hi, I would like to ask about: '+sv+'.\n\n';
f.addEventListener('submit',function(e){e.preventDefault();if(!f.checkValidity()){f.reportValidity();return}var v=f.elements;var s=encodeURIComponent('Portfolio message from '+v.name.value);var b=encodeURIComponent(v.msg.value+'\n\n- '+v.name.value+' ('+v.email.value+')');location.href='mailto:'+v.to.value+'?subject='+s+'&body='+b;toast('Opening your email app…')})}
/* top */
$$('[data-top]').forEach(function(b){b.addEventListener('click',function(){scrollTo({top:0,behavior:'smooth'})})});
/* install (PWA) */
var ev=null,ios=/iphone|ipad|ipod/i.test(navigator.userAgent),st=matchMedia('(display-mode:standalone)').matches||navigator.standalone;
function showI(){$$('[data-install]').forEach(function(b){b.hidden=false})}
addEventListener('beforeinstallprompt',function(e){e.preventDefault();ev=e;showI()});
if(ios&&!st)showI();
$$('[data-install]').forEach(function(b){b.addEventListener('click',function(){if(ev){ev.prompt();ev.userChoice.then(function(){ev=null;$$('[data-install]').forEach(function(x){x.hidden=true})})}else toast('Tap Share, then Add to Home Screen')})});
addEventListener('appinstalled',function(){$$('[data-install]').forEach(function(x){x.hidden=true})});
/* service worker */
if('serviceWorker' in navigator&&window.SVA&&SVA.sw){addEventListener('load',function(){navigator.serviceWorker.register(SVA.sw,{scope:'/'}).catch(function(){})})}
})();
