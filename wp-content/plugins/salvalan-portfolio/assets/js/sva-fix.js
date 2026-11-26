/* SalValAn 6.4 fixes + cinematic layer (loads last) */
(function(){var d=document,h=d.documentElement;
function $(s,c){return(c||d).querySelector(s)}function $$(s,c){return[].slice.call((c||d).querySelectorAll(s))}
function safe(n,f){try{f()}catch(e){window.console&&console.warn('sva-fix '+n,e)}}
var g=window.gsap,ST=window.ScrollTrigger,mo=h.classList.contains('mo')&&g&&ST;
var touchy=matchMedia('(hover:none),(max-width:700px)').matches;

/* ===== 1. Team cards: stable tap-to-open (accordion, never disappears) ===== */
safe('cards',function(){
 var mems=$$('.mem'),crew=$('.crew');if(!mems.length)return;
 function settle(){ /* clear leftover intro styles so a card can never stay hidden */
  if(g){g.killTweensOf(mems);mems.forEach(function(m){g.set(m,{clearProps:'visibility,opacity,transform,clipPath'});var a=$('.av',m);if(a){g.killTweensOf(a);if(touchy)g.set(a,{clearProps:'transform,clipPath,filter'})}})}
  if(touchy&&ST)ST.getAll().forEach(function(t){if(t.trigger===crew)t.kill()});
  crew&&crew.classList.add('ready')}
 if(mo&&!touchy&&!(window.SVAMO&&SVAMO.introDone)){d.addEventListener('sva:intro',function(){setTimeout(settle,1900)},{once:true});setTimeout(settle,9000)}else settle();
 var rt=0;
 function open(m){
  mems.forEach(function(x){var on=x===m;x.classList.toggle('on',on);x.setAttribute('aria-expanded',on)});
  clearTimeout(rt); /* one refresh + one gentle scroll after the height animation ends (no repeated refreshes) */
  rt=setTimeout(function(){ST&&ST.refresh();var r=m.getBoundingClientRect();if(r.top<70||r.bottom>innerHeight-84){var y=scrollY+r.top-80;if(window.SVAMO&&SVAMO.lenis)SVAMO.lenis.scrollTo(y);else scrollTo(0,y)}},520)}
 mems.forEach(function(m){
  m.addEventListener('click',function(e){if(!touchy||e.target.closest('a'))return;e.preventDefault();if(!m.classList.contains('on'))open(m)});
  m.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){if(e.target.closest('a'))return;e.preventDefault();if(touchy)open(m);else m.focus()}})});
 if(touchy&&!$('.mem.on'))mems[0].classList.add('on');
});

/* ===== 2. Image viewer: modal with zoom, pan, rotate (stays on the page) ===== */
safe('lightbox',function(){
 var ico={zi:'<path d="M11 5v12M5 11h12" transform="translate(1 1)"/>',zo:'<path d="M5 12h14"/>',rot:'<path d="M20 12a8 8 0 1 1-2.6-5.9M20 4v5h-5"/>',rs:'<path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/>',x:'<path d="M6 6l12 12M18 6L6 18"/>'};
 function b(k,l){return '<button type="button" data-a="'+k+'" aria-label="'+l+'"><svg viewBox="0 0 24 24" aria-hidden="true">'+ico[k]+'</svg></button>'}
 var lb=d.createElement('div');lb.className='lb';lb.setAttribute('role','dialog');lb.setAttribute('aria-modal','true');lb.setAttribute('aria-label','Image viewer');
 lb.innerHTML='<div class="tb"><span class="cap"></span>'+b('zo','Zoom out')+b('zi','Zoom in')+b('rot','Rotate')+b('rs','Reset')+b('x','Close')+'</div><div class="st"><img alt="" draggable="false"></div><div class="hint">Pinch or double-tap to zoom, drag to move</div>';
 d.body.appendChild(lb);
 var st=$('.st',lb),im=$('img',lb),cap=$('.cap',lb),hint=$('.hint',lb),opener=null;
 var s=1,tx=0,ty=0,rot=0,bw=0,bh=0,nw=1,nh=1,ptr={},p0=null,moved=false,lastTap=0;
 function fit(){var W=st.clientWidth,H=st.clientHeight,odd=rot/90%2!==0,aw=odd?H:W,ah=odd?W:H,k=Math.min(aw*.96/nw,ah*.96/nh);bw=nw*k;bh=nh*k;im.style.width=bw+'px';im.style.height=bh+'px';clamp();paint()}
 function clamp(){var W=st.clientWidth,H=st.clientHeight,odd=rot/90%2!==0,vw=(odd?bh:bw)*s,vh=(odd?bw:bh)*s,mx=Math.max(0,(vw-W)/2),my=Math.max(0,(vh-H)/2);tx=Math.max(-mx,Math.min(mx,tx));ty=Math.max(-my,Math.min(my,ty))}
 function paint(){im.style.transform='translate(-50%,-50%) translate('+tx+'px,'+ty+'px) rotate('+rot+'deg) scale('+s+')'}
 function zoom(ns,cx,cy){ns=Math.max(1,Math.min(6,ns));if(cx!=null){var r=st.getBoundingClientRect(),ox=cx-r.left-r.width/2,oy=cy-r.top-r.height/2,k=ns/s;tx=ox-(ox-tx)*k;ty=oy-(oy-ty)*k}s=ns;if(s===1){tx=0;ty=0}clamp();paint()}
 function reset(){s=1;tx=ty=0;rot=0;im.style.transition='transform .3s';fit();setTimeout(function(){im.style.transition=''},320)}
 function show(a){var i=$('img',a),src=a.getAttribute('href')||(i&&i.currentSrc)||'';opener=a;cap.textContent=(i&&i.alt)||'';
  s=1;tx=ty=0;rot=0;im.style.width=im.style.height='';im.style.transform='translate(-50%,-50%)';
  im.onload=function(){nw=im.naturalWidth||1;nh=im.naturalHeight||1;fit()};im.src=src;if(im.complete&&im.naturalWidth)im.onload();
  lb.classList.add('open');h.classList.add('lb-open');window.SVAMO&&SVAMO.lenis&&SVAMO.lenis.stop();
  hint.style.opacity=.6;setTimeout(function(){hint.style.opacity=0},3200);$('[data-a=x]',lb).focus({preventScroll:true})}
 function close(){if(!lb.classList.contains('open'))return;lb.classList.remove('open');h.classList.remove('lb-open');window.SVAMO&&SVAMO.lenis&&SVAMO.lenis.start();ptr={};opener&&opener.focus&&opener.focus({preventScroll:true})}
 d.addEventListener('click',function(e){var a=e.target.closest&&e.target.closest('.shot a');if(!a||!a.getAttribute('href'))return;e.preventDefault();e.stopPropagation();show(a)},true);
 lb.addEventListener('click',function(e){var a=e.target.closest('[data-a]');if(!a)return;var k=a.getAttribute('data-a');
  if(k==='x')close();else if(k==='zi')zoom(s*1.5);else if(k==='zo')zoom(s/1.5);else if(k==='rot'){rot=(rot+90)%360;tx=ty=0;fit()}else if(k==='rs')reset()});
 d.addEventListener('keydown',function(e){if(!lb.classList.contains('open'))return;if(e.key==='Escape')close();else if(e.key==='+'||e.key==='=')zoom(s*1.5);else if(e.key==='-')zoom(s/1.5);else if(e.key==='r'||e.key==='R'){rot=(rot+90)%360;tx=ty=0;fit()}});
 function dist(){var k=Object.keys(ptr);if(k.length<2)return 0;var a=ptr[k[0]],c=ptr[k[1]];return Math.hypot(a.x-c.x,a.y-c.y)}
 function mid(){var k=Object.keys(ptr),a=ptr[k[0]],c=ptr[k[1]];return{x:(a.x+c.x)/2,y:(a.y+c.y)/2}}
 st.addEventListener('pointerdown',function(e){st.setPointerCapture&&st.setPointerCapture(e.pointerId);ptr[e.pointerId]={x:e.clientX,y:e.clientY};moved=false;p0={d:dist(),s:s};st.classList.add('drag')});
 st.addEventListener('pointermove',function(e){var o=ptr[e.pointerId];if(!o)return;var n=Object.keys(ptr).length;
  if(n>=2){ptr[e.pointerId]={x:e.clientX,y:e.clientY};var dd=dist();if(p0&&p0.d){var m=mid();zoom(p0.s*dd/p0.d,m.x,m.y)}moved=true}
  else{var dx=e.clientX-o.x,dy=e.clientY-o.y;if(Math.abs(dx)+Math.abs(dy)>2)moved=true;ptr[e.pointerId]={x:e.clientX,y:e.clientY};if(s>1){tx+=dx;ty+=dy;clamp();paint()}}});
 function up(e){var had=ptr[e.pointerId];delete ptr[e.pointerId];p0={d:dist(),s:s};if(!Object.keys(ptr).length)st.classList.remove('drag');
  if(had&&!moved&&e.type==='pointerup'){var t=Date.now();if(t-lastTap<300){im.style.transition='transform .3s';zoom(s>1?1:2.5,e.clientX,e.clientY);setTimeout(function(){im.style.transition=''},320);lastTap=0}else{lastTap=t;if(e.target===st)setTimeout(function(){if(lastTap&&Date.now()-lastTap>=290&&s===1&&e.target===st)close()},300)}}}
 st.addEventListener('pointerup',up);st.addEventListener('pointercancel',up);
 st.addEventListener('wheel',function(e){e.preventDefault();zoom(s*(e.deltaY<0?1.15:1/1.15),e.clientX,e.clientY)},{passive:false});
 addEventListener('resize',function(){lb.classList.contains('open')&&fit()});addEventListener('orientationchange',function(){setTimeout(function(){lb.classList.contains('open')&&fit()},250)});
});

/* ===== 3. Cinematic motion for Projects, Experience, Contact ===== */
safe('cinema',function(){
 if(!mo)return;
 var fine=matchMedia('(hover:hover) and (pointer:fine)').matches,E='expo.out';
 function from(t,v,tr,extra){var o=Object.assign({duration:1.1,ease:E,clearProps:'transform,opacity,filter'},v);return g.from(t,Object.assign(o,{scrollTrigger:Object.assign({trigger:tr,start:'top 85%',once:true},extra||{})}))}
 /* PROJECTS: colour bar draws across each card, text cascades in, screenshot rises with a soft blur, bullets slide */
 $$('#projects .pc').forEach(function(c,i){
  var bar=d.createElement('i');bar.className='fxbar';bar.setAttribute('aria-hidden','true');c.insertBefore(bar,c.firstChild);
  g.fromTo(bar,{scaleX:0},{scaleX:1,ease:'none',scrollTrigger:{trigger:c,start:'top 88%',end:'top 35%',scrub:.6}});
  var t=$$('.pt > *',c);if(t.length)from(t,{y:34,x:i%2?40:-40,autoAlpha:0,filter:'blur(8px)',stagger:.08},c);
  var f=$('.shot',c);if(f&&!f.closest('.two'))from(f,{y:50,autoAlpha:0,duration:1.2},c)
  else $$('.shot',c).forEach(function(s,k){from(s,{y:40,autoAlpha:0,duration:1.1,delay:k*.12},s)});
  $$('.ck li',c).forEach(function(l,k){from(l,{x:-30,autoAlpha:0,duration:.8,delay:k*.06},l,{start:'top 92%'})})});
 $$('#projects .filters button, #projects .feat .flow span').forEach(function(b,k){from(b,{y:24,scale:.85,autoAlpha:0,duration:.8,delay:k*.07},b,{start:'top 95%'})});
 /* EXPERIENCE: year slides from the left, text from the right, each row has a glow sweep */
 $$('#experience .tl li').forEach(function(li,k){
  var t=$('time',li),dv=$('div',li);
  if(t)from(t,{x:-60,autoAlpha:0,duration:1},li,{start:'top 90%'});
  if(dv)from(dv,{x:70,autoAlpha:0,clipPath:'inset(0 0 100% 0)',filter:'blur(6px)',duration:1.2,clearProps:'transform,opacity,filter,clipPath'},li,{start:'top 90%'});
  if(fine)g.to(li,{x:k%2?6:-6,ease:'none',scrollTrigger:{trigger:li,start:'top bottom',end:'bottom top',scrub:true}})});
 /* CONTACT: form flies in from the left, cards swing in from the right, rows cascade, glowing orbs drift */
 var ct=$('#contact');
 if(ct){
  ['var(--build)','var(--doc)'].forEach(function(c,k){var o=d.createElement('i');o.className='fxorb';o.setAttribute('aria-hidden','true');o.style.background=c;o.style.cssText+=k?';right:-8%;bottom:6%':';left:-8%;top:10%';ct.insertBefore(o,ct.firstChild);
   g.to(o,{x:k?-60:70,y:k?-50:60,scale:1.25,duration:6+k*2,ease:'sine.inOut',yoyo:true,repeat:-1})});
  var fm=$('.card',ct);
  if(fm)from($$('label,.btn',fm),{y:34,x:-30,autoAlpha:0,stagger:.09,duration:.9},fm,{start:'top 85%'});
  $$('.stack .card',ct).forEach(function(c){
   var av=$('.av',c),nm=$('.idr h3, .idr span',c);
   from($$('.row',c),{x:50,autoAlpha:0,stagger:.08,duration:.9},c,{start:'top 85%'});
   if(nm)from($$('.idr > div',c),{x:40,autoAlpha:0,duration:1},c,{start:'top 88%'});
   if(av)from(av,{scale:.4,rotation:-25,autoAlpha:0,duration:1.1,ease:'back.out(1.8)'},c,{start:'top 88%'})})}
 setTimeout(function(){ST.refresh()},400);
});
})();
