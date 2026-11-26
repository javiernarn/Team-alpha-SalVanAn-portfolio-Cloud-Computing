/* SalValAn cinematic motion: GSAP + ScrollTrigger + Lenis (all bundled in assets/js/vendor). Falls back to the plain site if anything is missing. */
(function(){var d=document,h=d.documentElement;
function $(s,c){return(c||d).querySelector(s)}function $$(s,c){return[].slice.call((c||d).querySelectorAll(s))}
/* motion on/off switch in the footer */
var on=true;try{on=localStorage.getItem('sva-motion')!=='off'}catch(e){}
var rm=matchMedia('(prefers-reduced-motion:reduce)').matches;
$$('[data-motion]').forEach(function(b){if(rm){b.hidden=true;return}b.textContent='Cinematic motion: '+(on?'on':'off');b.addEventListener('click',function(){try{localStorage.setItem('sva-motion',on?'off':'on');sessionStorage.removeItem('sva-pre')}catch(e){}location.reload()})});
if(!h.classList.contains('mo')||!window.gsap||!window.ScrollTrigger){h.classList.remove('mo','mo-pre');var pp=$('.pre');pp&&pp.remove();return}
gsap.registerPlugin(ScrollTrigger);ScrollTrigger.config({ignoreMobileResize:true});
var fine=matchMedia('(hover:hover) and (pointer:fine)').matches,L=null;window.SVAMO={on:true,lenis:null};
/* Lenis smooth scroll on desktop, driven by the GSAP ticker (touch screens keep their own native momentum) */
if(window.Lenis&&fine){L=new Lenis({lerp:.1,anchors:false});SVAMO.lenis=L;L.on('scroll',ScrollTrigger.update);gsap.ticker.add(function(t){L.raf(t*1000)});gsap.ticker.lagSmoothing(0);
 new MutationObserver(function(){h.classList.contains('menu-open')?L.stop():L.start()}).observe(h,{attributes:true,attributeFilter:['class']})}
/* text splitter: words in masks (or plain words) */
function split(el,mask){var t=el.textContent.trim();el.setAttribute('aria-label',t);el.textContent='';return t.split(/\s+/).map(function(w){var o=d.createElement('span'),i=d.createElement('span');o.setAttribute('aria-hidden','true');o.style.cssText='display:inline-block;vertical-align:top'+(mask?';overflow:hidden;padding-bottom:.1em':'');i.style.display='inline-block';i.textContent=w;o.appendChild(i);el.appendChild(o);el.appendChild(d.createTextNode(' '));return i})}
/* ---- intro: preloader then hero ---- */
var big=$$('.big i'),crew=$$('.crew .mem'),sub=$$('.hero .sub,.hero .acts');gsap.set(big,{yPercent:105,opacity:1});
function intro(){gsap.timeline({defaults:{ease:'expo.out'}}).to(big,{yPercent:0,duration:1.4,stagger:.14},0)
 .from(sub,{autoAlpha:0,y:30,duration:1,stagger:.12,clearProps:'transform,opacity'},.5)
 .from(crew,{autoAlpha:0,yPercent:14,clipPath:'inset(100% 0 0 0)',duration:1.5,stagger:.15,clearProps:'clipPath,transform,opacity'},.3)
 .add(function(){SVAMO.introDone=true;d.dispatchEvent(new Event('sva:intro'))},.3)}
var pre=$('.pre');
if(pre&&h.classList.contains('mo-pre')){
 try{sessionStorage.setItem('sva-pre','1')}catch(e){}
 var ct=$('.ct',pre),n={v:0},rc=$$('.mk rect',pre);h.style.overflow='hidden';L&&L.stop();gsap.set(rc,{scaleY:0});
 var ptl=gsap.timeline({paused:h.classList.contains('sva-gate')}).to(rc,{scaleY:1,duration:.9,stagger:.15,ease:'expo.out'},0)
 .to(n,{v:100,duration:1.7,ease:'power2.inOut',onUpdate:function(){ct.textContent=String(Math.round(n.v)).padStart(3,'0')}},0)
 .to($('.mk',pre),{opacity:0,y:-24,duration:.5},'>.1')
 .to($('.pn1',pre),{yPercent:-101,duration:1.2,ease:'expo.inOut'},'>-.05').to($('.pn2',pre),{yPercent:101,duration:1.2,ease:'expo.inOut'},'<')
 .add(intro,'<.5').add(function(){pre.remove();h.classList.remove('mo-pre');h.style.overflow='';L&&L.start();ScrollTrigger.refresh()});
 if(h.classList.contains('sva-gate'))d.addEventListener('sva:enter',function(){ptl.play()},{once:true});
}else if(h.classList.contains('sva-gate'))d.addEventListener('sva:enter',intro,{once:true});else intro();
/* ---- hero scroll scene: headline lines drift apart, spotlight blooms, portraits parallax ---- */
gsap.timeline({scrollTrigger:{trigger:'.hero',start:'top top',end:'55% top',scrub:true}})
 .to('.big .ln:nth-child(odd)',{xPercent:-22,opacity:.15,ease:'none'},0).to('.big .ln:nth-child(2)',{xPercent:22,opacity:.15,ease:'none'},0).to('.spot',{scale:1.8,opacity:0,ease:'none'},0);
$$('.mem .av').forEach(function(a){gsap.fromTo(a,{yPercent:6,scale:1.16},{yPercent:-6,scale:1.16,ease:'none',scrollTrigger:{trigger:'.crew',start:'top bottom',end:'bottom top',scrub:true}})});
/* marquee leans into the scroll speed */
ScrollTrigger.create({onUpdate:function(s){var v=gsap.utils.clamp(-10,10,s.getVelocity()/-300);gsap.to('.mq',{skewX:v,duration:.25,overwrite:true,onComplete:function(){gsap.to('.mq',{skewX:0,duration:.7,ease:'power3.out'})}})}});
/* ---- section headings rise out of masks ---- */
$$('.sh').forEach(function(e){e.classList.remove('rv');var w=split(e,true);gsap.set(w,{yPercent:115});ScrollTrigger.create({trigger:e,start:'top 88%',once:true,onEnter:function(){gsap.to(w,{yPercent:0,duration:1.2,ease:'expo.out',stagger:.07})}})});
/* about: the story lights up word by word */
$$('.big2').forEach(function(e){var w=split(e,false);gsap.fromTo(w,{opacity:.15},{opacity:1,ease:'none',stagger:.1,scrollTrigger:{trigger:e,start:'top 82%',end:'bottom 50%',scrub:true}})});
/* everything else that used to fade in: staggered rise */
$$('.rv').forEach(function(e){e.classList.remove('rv');e.setAttribute('data-rvb','');gsap.set(e,{opacity:0,y:56})});
ScrollTrigger.batch('[data-rvb]',{start:'top 90%',once:true,onEnter:function(b){gsap.to(b,{opacity:1,y:0,duration:1.2,ease:'expo.out',stagger:.1,clearProps:'transform'})}});
/* project screenshots: wipe in, then drift inside their frame */
$$('.shot a').forEach(function(a){var im=$('img',a);
 gsap.fromTo(a,{clipPath:'inset(0 0 100% 0)'},{clipPath:'inset(0 0 0% 0)',duration:1.4,ease:'expo.inOut',scrollTrigger:{trigger:a,start:'top 88%',once:true}});
 gsap.fromTo(im,{yPercent:-6,scale:1.14},{yPercent:6,scale:1.14,ease:'none',scrollTrigger:{trigger:a,start:'top bottom',end:'bottom top',scrub:true}})});
/* ---- showreel: pinned film stage, scrubbed by scroll ---- */
var st=$('.reel .stage');
if(st){var fr=$$('.fr',st),N=fr.length,tc=$('.tcode',st),T=N*4,z=function(v){return(v<10?'0':'')+v};
 gsap.fromTo($$('.lb',st),{height:'46vh'},{height:'12vh',ease:'none',scrollTrigger:{trigger:'.reel',start:'top bottom',end:'top top',scrub:true}});
 var tl=gsap.timeline({defaults:{ease:'none'},scrollTrigger:{trigger:'.reel',start:'top top',end:'+='+(N*(innerWidth<700?60:90))+'%',pin:st,scrub:.6,anticipatePin:1,
  onUpdate:function(s){var t=s.progress*T;tc.textContent='00:00:'+z(Math.floor(t))+':'+z(Math.floor((t%1)*24))}}});
 fr.forEach(function(f,i){var im=$('img',f),cp=$('figcaption',f),k=i-.3;
  if(i){tl.fromTo(f,{opacity:0},{opacity:1,duration:.3},k);tl.fromTo(im,{scale:1.3,xPercent:-3},{scale:1.02,xPercent:3,duration:1.3},k);tl.fromTo(cp,{y:50,opacity:0},{y:0,opacity:1,duration:.3},i-.1)}
  if(i<N-1)tl.to(f,{opacity:0,duration:.3},i+.7)});
 tl.to({},{duration:.001},N)}
/* ---- cursor ---- */
if(fine){var c=d.createElement('div'),lb=d.createElement('span');c.className='cur';c.appendChild(lb);d.body.appendChild(c);
 var qx=gsap.quickTo(c,'x',{duration:.25,ease:'power3'}),qy=gsap.quickTo(c,'y',{duration:.25,ease:'power3'});
 addEventListener('pointermove',function(e){qx(e.clientX);qy(e.clientY);var t=e.target.closest&&e.target.closest('.shot a,.mem'),l=t?(t.matches('.mem')?'View':'Open'):'';c.classList.toggle('big',!!l);if(lb.textContent!==l)lb.textContent=l},{passive:true})}
/* layout changes (filters) and late images need a fresh measure */
$$('[data-filters] button').forEach(function(b){b.addEventListener('click',function(){setTimeout(function(){ScrollTrigger.refresh()},60)})});
addEventListener('load',function(){ScrollTrigger.refresh()});
})();
