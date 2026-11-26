/* SalValAn v6.2 FX layer. Loads after sva-fx.js and only runs when cinematic motion is on.
   Adds: numbered section eyebrows, cursor-following project preview on the services list,
   browser-window frames on screenshots, a timeline that draws itself with the scroll,
   a giant "Let's build it" call to action with a scroll-speed marquee, a scroll cue and a live clock.
   Every block is isolated: if one fails the rest of the site still works. */
(function(){var d=document,h=d.documentElement;
if(!window.SVAMO||!window.gsap||!window.ScrollTrigger)return;
function $(s,c){return(c||d).querySelector(s)}function $$(s,c){return[].slice.call((c||d).querySelectorAll(s))}
var fine=matchMedia('(hover:hover) and (pointer:fine)').matches;
function safe(n,f){try{f()}catch(e){if(window.console)console.warn('SVA fx2: '+n,e)}}
function clamp(v,a,b){return Math.max(a,Math.min(b,v))}
function fonts(f){(d.fonts&&d.fonts.ready)?d.fonts.ready.then(f):f()}

/* ============ 1. Numbered eyebrows above each section title ============ */
safe('eyebrow',function(){
 var list=[['about','Who we are'],['services','What we do'],['projects','Selected work'],['experience','The journey'],['contact','Get in touch']],n=0;
 list.forEach(function(it){
  var t=$('#'+it[0]+' .wrap > .sh');if(!t)return;n++;
  var e=d.createElement('p'),b=d.createElement('b'),l=d.createElement('i'),s=d.createElement('span');
  e.className='eb';e.setAttribute('aria-hidden','true');b.textContent='0'+n;s.textContent=it[1];
  e.appendChild(b);e.appendChild(l);e.appendChild(s);t.parentNode.insertBefore(e,t);
  gsap.set([b,s],{yPercent:120});gsap.set(l,{scaleX:0,transformOrigin:'0 50%'});
  ScrollTrigger.create({trigger:e,start:'top 92%',once:true,onEnter:function(){
   gsap.timeline({defaults:{ease:'expo.out'}}).to(l,{scaleX:1,duration:1.3},0).to([b,s],{yPercent:0,duration:1,stagger:.1},0)}});
 });
});

/* ============ 2. Services: a screenshot follows the cursor over each row ============ */
safe('preview',function(){
 var rows=$$('#svcgrid .row2'),ref=$('.mem .av');if(!fine||!rows.length||!ref)return;
 var base=ref.getAttribute('src').replace(/[^\/]*$/,''),
 map={'Web Application Development':['atms-home.jpg','left top'],'UI/UX and Website Design':['sclf-home.jpg','center'],
  'System Analysis':['ak-admin-web.jpg','left top'],'Process Analysis':['occdl-home.jpg','center'],
  'Technical Documentation':['ak-admin-web.jpg','right center'],'Editing and Proofreading':['atms-home.jpg','right top']};
 var pv=d.createElement('div'),im=d.createElement('img'),shown=false,lx=0,rt=0;
 pv.className='pv';pv.setAttribute('aria-hidden','true');im.alt='';im.decoding='async';pv.appendChild(im);d.body.appendChild(pv);
 gsap.set(pv,{xPercent:-50,yPercent:-50,scale:.7,autoAlpha:0});
 var qx=gsap.quickTo(pv,'x',{duration:.55,ease:'power3'}),qy=gsap.quickTo(pv,'y',{duration:.55,ease:'power3'}),qr=gsap.quickTo(pv,'rotation',{duration:.7,ease:'power3'});
 function put(e){qx(clamp(e.clientX+210,180,innerWidth-190));qy(e.clientY);qr(clamp((e.clientX-lx)*.3,-9,9));lx=e.clientX;clearTimeout(rt);rt=setTimeout(function(){qr(0)},90)}
 rows.forEach(function(r){var m=map[r.getAttribute('data-service')];if(!m)return;
  r.addEventListener('pointerenter',function(e){
   im.src=base+m[0];im.style.objectPosition=m[1];pv.style.setProperty('--c',getComputedStyle(r).getPropertyValue('--c'));
   gsap.fromTo(im,{clipPath:'inset(0 0 100% 0)',scale:1.25},{clipPath:'inset(0 0 0% 0)',scale:1,duration:.8,ease:'expo.out',overwrite:true});
   if(!shown){shown=true;lx=e.clientX;gsap.set(pv,{x:clamp(e.clientX+210,180,innerWidth-190),y:e.clientY})}
   gsap.to(pv,{autoAlpha:1,scale:1,duration:.6,ease:'expo.out',overwrite:'auto'})});
  r.addEventListener('pointermove',put,{passive:true});
  r.addEventListener('pointerleave',function(){shown=false;gsap.to(pv,{autoAlpha:0,scale:.8,duration:.35,ease:'power2.in',overwrite:'auto'})})});
});

/* ============ 3. Screenshots get a browser-window frame ============ */
safe('chrome',function(){
 $$('.shot').forEach(function(s){
  var a=$('a',s),im=a&&$('img',a);if(!a||!im||s.closest('.phones'))return;
  var card=s.closest('.pc'),btn=card&&$('.btn[href^="http"]',card),host='',bar=d.createElement('span'),i,em=d.createElement('em');
  try{if(btn)host=new URL(btn.href).host.replace(/^www\./,'')}catch(e){}
  bar.className='bar';bar.setAttribute('aria-hidden','true');
  for(i=0;i<3;i++)bar.appendChild(d.createElement('i'));
  em.textContent=host||'admin dashboard';bar.appendChild(em);a.insertBefore(bar,a.firstChild)});
});

/* ============ 4. Experience timeline draws itself and lights up each milestone ============ */
safe('timeline',function(){
 var tl=$('.tl');if(!tl)return;
 var ln=d.createElement('i');ln.className='tlp';ln.setAttribute('aria-hidden','true');tl.appendChild(ln);
 gsap.fromTo(ln,{scaleY:0},{scaleY:1,ease:'none',scrollTrigger:{trigger:tl,start:'top 70%',end:'bottom 62%',scrub:.4}});
 $$('li',tl).forEach(function(li){
  var dt=d.createElement('i');dt.className='dt';dt.setAttribute('aria-hidden','true');li.insertBefore(dt,li.firstChild);
  ScrollTrigger.create({trigger:li,start:'top 66%',onEnter:function(){li.classList.add('on')},onLeaveBack:function(){li.classList.remove('on')}})});
});

/* ============ 5. Giant call to action + marquee that speeds up with the scroll ============ */
safe('cta',function(){
 var s=$('.cta');if(!s)return;
 var l=$('.ctl',s),t=$('.tx',s);
 if(t&&window.SplitText)fonts(function(){safe('cta-split',function(){
  var sp=SplitText.create(t,{type:'words,chars',charsClass:'ch',mask:'chars'}),cs=['build','analyze','doc'];
  sp.chars.forEach(function(c,i){c.style.setProperty('--k','var(--'+cs[i%3]+')');c.style.transitionDelay=(i*22)+'ms'});
  gsap.from(sp.chars,{yPercent:118,duration:1.3,stagger:.035,ease:'expo.out',scrollTrigger:{trigger:l,start:'top 90%',once:true}});
  gsap.from($('.ar',s),{scale:0,rotation:-180,duration:1.2,ease:'back.out(1.6)',clearProps:'transform',scrollTrigger:{trigger:l,start:'top 90%',once:true}})})});
 var tr=$('.ctt',s);if(!tr)return;
 var tw=gsap.to(tr,{xPercent:-50,duration:36,ease:'none',repeat:-1});
 ScrollTrigger.create({trigger:s,start:'top bottom',end:'bottom top',
  onToggle:function(x){x.isActive?tw.resume():tw.pause()},
  onUpdate:function(x){var v=clamp(Math.abs(x.getVelocity())/260,0,9),dir=x.direction||1;
   gsap.to(tw,{timeScale:dir*(1+v),duration:.25,overwrite:true,onComplete:function(){gsap.to(tw,{timeScale:dir,duration:1.2,ease:'power2.out'})}})}});
});

/* ============ 6. Scroll cue (bottom left) ============ */
safe('cue',function(){
 if(!fine||!$('.hero'))return;
 var c=d.createElement('div');c.className='cue';c.setAttribute('aria-hidden','true');c.innerHTML='<i></i><span>Scroll</span>';d.body.appendChild(c);
 function sync(){c.classList.toggle('hide',scrollY>120)}addEventListener('scroll',sync,{passive:true});sync();
});

/* ============ 7. Live clock in the footer ============ */
safe('clock',function(){
 var t=$('.sva-ft .mut.small');if(!t||!window.Intl||!Intl.DateTimeFormat)return;
 var s=d.createElement('span'),f=new Intl.DateTimeFormat('en-GB',{hour:'2-digit',minute:'2-digit',hour12:false,timeZone:'Asia/Manila'});
 s.className='clk';t.appendChild(s);
 function up(){s.textContent='Misamis Oriental, PH · '+f.format(new Date())+' PHT'}up();setInterval(up,30000);
});

addEventListener('load',function(){ScrollTrigger.refresh()});
})();
