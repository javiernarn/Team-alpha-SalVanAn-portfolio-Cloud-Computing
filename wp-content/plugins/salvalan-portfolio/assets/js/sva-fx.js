/* SalValAn v6 FX layer. Loads after sva-motion.js and only runs when cinematic motion is on.
   Adds: WebGL aurora hero, SplitText title reveals, ScrambleText decode, Flip filters,
   film-camera showreel (REC, scene counter, light leaks, shake), 3D tilt + glare, section rail,
   rolling end credits. Every block is isolated: if one fails the rest of the site still works. */
(function(){var d=document,h=d.documentElement;
if(!window.SVAMO||!window.gsap||!window.ScrollTrigger)return;
function $(s,c){return(c||d).querySelector(s)}function $$(s,c){return[].slice.call((c||d).querySelectorAll(s))}
['SplitText','ScrambleTextPlugin','Flip'].forEach(function(n){try{if(window[n])gsap.registerPlugin(window[n])}catch(e){}});
var fine=matchMedia('(hover:hover) and (pointer:fine)').matches,small=innerWidth<700,
    weak=(navigator.hardwareConcurrency||8)<=4||(navigator.deviceMemory||8)<=2;
function safe(n,f){try{f()}catch(e){if(window.console)console.warn('SVA fx: '+n,e)}}
function clamp(v,a,b){return Math.max(a,Math.min(b,v))}
var hero=$('.hero');

/* ============ 1. WebGL aurora behind the hero ============ */
safe('aurora',function(){
 if(!hero)return;
 var cv=d.createElement('canvas');cv.className='gl';cv.setAttribute('aria-hidden','true');
 var gl=cv.getContext('webgl',{alpha:true,premultipliedAlpha:true,antialias:false,depth:false,stencil:false,powerPreference:'low-power'});
 if(!gl)return;
 var hp=gl.getShaderPrecisionFormat&&gl.getShaderPrecisionFormat(gl.FRAGMENT_SHADER,gl.HIGH_FLOAT);
 if(!hp||!hp.precision)return;
 var fs=['precision highp float;uniform vec2 r;uniform float t;uniform vec2 m;uniform vec3 a;uniform vec3 b;uniform vec3 c;uniform vec3 k;uniform float i;',
 'float hs(vec2 p){return fract(sin(dot(p,vec2(127.1,311.7)))*43758.5453);}',
 'float ns(vec2 p){vec2 f=fract(p),g=floor(p);f=f*f*(3.-2.*f);return mix(mix(hs(g),hs(g+vec2(1.,0.)),f.x),mix(hs(g+vec2(0.,1.)),hs(g+vec2(1.,1.)),f.x),f.y);}',
 'float fbm(vec2 p){float v=0.,s=.5;for(int j=0;j<4;j++){v+=s*ns(p);p=p*2.02+vec2(1.7,9.2);s*=.5;}return v;}',
 'void main(){vec2 p=(gl_FragCoord.xy-.5*r)/r.y;vec2 mm=(m-.5*r)/r.y;float d=length(p-mm);',
 'p+=(p-mm)*.2*exp(-d*3.);float tt=t*.05;',
 'vec2 q=vec2(fbm(p*1.6+tt),fbm(p*1.6+vec2(5.2,1.3)-tt));float f=fbm(p*1.4+2.2*q+tt*1.5);',
 'vec3 col=mix(a,b,smoothstep(.2,.8,f));col=mix(col,c,smoothstep(.35,.9,q.x*q.y*2.2));col=mix(col,k,exp(-d*2.2)*.75);',
 'float vig=smoothstep(1.3,.15,length(p*vec2(.75,1.)));float al=(smoothstep(.22,.95,f)*.85+.18*q.x)*vig*i;',
 'gl_FragColor=vec4(col*al,al);}'].join('\n');
 function sh(t,s){var o=gl.createShader(t);gl.shaderSource(o,s);gl.compileShader(o);if(!gl.getShaderParameter(o,gl.COMPILE_STATUS)){if(window.console)console.warn(gl.getShaderInfoLog(o));return null}return o}
 var v=sh(gl.VERTEX_SHADER,'attribute vec2 p;void main(){gl_Position=vec4(p,0.,1.);}'),f=sh(gl.FRAGMENT_SHADER,fs);if(!v||!f)return;
 var pg=gl.createProgram();gl.attachShader(pg,v);gl.attachShader(pg,f);gl.linkProgram(pg);if(!gl.getProgramParameter(pg,gl.LINK_STATUS))return;gl.useProgram(pg);
 var bf=gl.createBuffer();gl.bindBuffer(gl.ARRAY_BUFFER,bf);gl.bufferData(gl.ARRAY_BUFFER,new Float32Array([-1,-1,3,-1,-1,3]),gl.STATIC_DRAW);
 var lc=gl.getAttribLocation(pg,'p');gl.enableVertexAttribArray(lc);gl.vertexAttribPointer(lc,2,gl.FLOAT,false,0,0);
 var U={};['r','t','m','a','b','c','k','i'].forEach(function(n){U[n]=gl.getUniformLocation(pg,n)});
 hero.insertBefore(cv,hero.firstChild);
 var scale=small?.35:.5,W=2,H=2;
 function size(){W=Math.max(2,Math.round(hero.clientWidth*scale));H=Math.max(2,Math.round(hero.clientHeight*scale));if(cv.width!==W||cv.height!==H){cv.width=W;cv.height=H;gl.viewport(0,0,W,H)}}
 size();if(window.ResizeObserver)new ResizeObserver(size).observe(hero);else addEventListener('resize',size);
 function hex(s){var m=/^#?([0-9a-f]{6})$/i.exec((s||'').trim());if(!m)return[1,.4,.2];var n=parseInt(m[1],16);return[(n>>16)/255,((n>>8)&255)/255,(n&255)/255]}
 var cols={},dark=true;
 function theme(){var cs=getComputedStyle(h);dark=h.getAttribute('data-theme')!=='light';cols={build:hex(cs.getPropertyValue('--build')),analyze:hex(cs.getPropertyValue('--analyze')),doc:hex(cs.getPropertyValue('--doc'))}}
 theme();new MutationObserver(theme).observe(h,{attributes:true,attributeFilter:['data-theme']});
 var tx=.72,ty=.6,mx=tx,my=ty,role='',kc=cols.doc.slice(),fade=1,vis=true,last=0;
 hero.addEventListener('pointermove',function(e){var r=hero.getBoundingClientRect();tx=(e.clientX-r.left)/r.width;ty=1-(e.clientY-r.top)/r.height;var n=e.target.closest&&e.target.closest('[data-r]'),k=n&&n.getAttribute('data-r');role=cols[k]?k:''},{passive:true});
 hero.addEventListener('pointerleave',function(){role=''});
 ScrollTrigger.create({trigger:hero,start:'top bottom',end:'bottom top',onToggle:function(s){vis=s.isActive}});
 ScrollTrigger.create({trigger:hero,start:'top top',end:'bottom top',onUpdate:function(s){fade=1-s.progress*.9}});
 var gap=(weak||!fine)?1/30:0;
 gsap.ticker.add(function(t){
  if(!vis||d.hidden||t-last<gap)return;last=t;
  if(!fine){tx=.5+.32*Math.sin(t*.35);ty=.55+.22*Math.cos(t*.27)}
  mx+=(tx-mx)*.06;my+=(ty-my)*.06;
  var tg=role?cols[role]:cols.doc;for(var j=0;j<3;j++)kc[j]+=(tg[j]-kc[j])*.08;
  gl.uniform2f(U.r,W,H);gl.uniform1f(U.t,t);gl.uniform2f(U.m,mx*W,my*H);
  gl.uniform3fv(U.a,cols.build);gl.uniform3fv(U.b,cols.analyze);gl.uniform3fv(U.c,cols.doc);gl.uniform3fv(U.k,kc);
  gl.uniform1f(U.i,(dark?1:.6)*fade);
  gl.drawArrays(gl.TRIANGLES,0,3)});
 cv.addEventListener('webglcontextlost',function(e){e.preventDefault();vis=false;cv.remove()});
 function show(){gsap.to(cv,{opacity:1,duration:2.4,ease:'power2.out',delay:.2})}
 SVAMO.introDone?show():d.addEventListener('sva:intro',show,{once:true});
});

/* ============ 2. Hero line decodes like a film title ============ */
safe('hero-text',function(){
 var sub=$('.hero .sub');if(!sub||!gsap.plugins.scrambleText)return;var t=sub.textContent.trim();
 function go(){gsap.to(sub,{duration:1.9,scrambleText:{text:t,chars:'01 /<>_',speed:.55,revealDelay:.25,tweenLength:false}})}
 SVAMO.introDone?go():d.addEventListener('sva:intro',go,{once:true});
});
/* tags and nav links decode on hover / when they enter */
safe('scramble',function(){
 if(!gsap.plugins.scrambleText)return;
 $$('.pc .tag,.feat .tag').forEach(function(e){var t=e.textContent;ScrollTrigger.create({trigger:e,start:'top 92%',once:true,onEnter:function(){gsap.to(e,{duration:1,scrambleText:{text:t,chars:'upperCase',speed:.6,tweenLength:false}})}})});
 if(fine)$$('.sva-hd .nav a').forEach(function(a){var t=a.textContent,busy=false;a.addEventListener('pointerenter',function(){if(busy)return;busy=true;gsap.to(a,{duration:.55,scrambleText:{text:t,chars:'lowerCase',speed:1,tweenLength:false},onComplete:function(){busy=false;a.textContent=t}})})});
});

/* ============ 3. Project titles: characters flip up in 3D ============ */
safe('titles',function(){
 if(!window.SplitText)return;
 var run=function(){$$('.pc h3').forEach(function(e){if(e.children.length)return;
  var sp=SplitText.create(e,{type:'words,chars',charsClass:'ch'});
  gsap.from(sp.chars,{yPercent:120,rotateX:-80,opacity:0,transformOrigin:'0% 50% -24px',duration:1.1,ease:'expo.out',stagger:.016,scrollTrigger:{trigger:e,start:'top 90%',once:true}})})};
 (d.fonts&&d.fonts.ready)?d.fonts.ready.then(run):run();
});

/* ============ 4. Filters glide with Flip ============ */
safe('flip',function(){
 if(!window.Flip)return;
 $$('[data-filters]').forEach(function(g){var box=$(g.getAttribute('data-filters'));if(!box)return;
  g.addEventListener('click',function(e){if(!e.target.closest('button'))return;
   var kids=[].slice.call(box.children),st=Flip.getState(kids),h0=box.offsetHeight;
   requestAnimationFrame(function(){var h1=box.offsetHeight;
    /* hold the old height and ease to the new one, so the sections below slide instead of jumping through the cards */
    if(Math.abs(h1-h0)>2){box.style.height=h0+'px';box.style.alignContent='start';gsap.to(box,{height:h1,duration:.85,ease:'expo.out',overwrite:true,clearProps:'height,alignContent'})}
    Flip.from(st,{duration:.85,ease:'expo.out',stagger:.05,absolute:true,
    onEnter:function(el){return gsap.fromTo(el,{opacity:0,y:40,scale:.96},{opacity:1,y:0,scale:1,duration:.8,ease:'expo.out',clearProps:'opacity,transform'})},
    onLeave:function(el){return gsap.to(el,{opacity:0,scale:.96,duration:.3,ease:'power2.in'})},
    onComplete:function(){ScrollTrigger.refresh()}})})},true)});
});

/* ============ 5. 3D tilt with moving glare on screenshots ============ */
safe('tilt',function(){
 if(!fine)return;
 $$('.shot').forEach(function(s){var a=$('a',s);if(!a)return;
  var g=d.createElement('i');g.className='glare';g.setAttribute('aria-hidden','true');a.appendChild(g);
  gsap.set(s,{transformPerspective:900});
  var rx=gsap.quickTo(s,'rotationX',{duration:.6,ease:'power3'}),ry=gsap.quickTo(s,'rotationY',{duration:.6,ease:'power3'});
  s.addEventListener('pointermove',function(e){var r=s.getBoundingClientRect(),px=(e.clientX-r.left)/r.width-.5,py=(e.clientY-r.top)/r.height-.5;
   ry(px*10);rx(-py*8);g.style.setProperty('--gx',(px+.5)*100+'%');g.style.setProperty('--gy',(py+.5)*100+'%');g.style.opacity=1},{passive:true});
  s.addEventListener('pointerleave',function(){rx(0);ry(0);g.style.opacity=0})});
});

/* ============ 6. Hero depth: crew and headline lean toward the pointer ============ */
safe('depth',function(){
 var crew=$('.crew');if(!hero||!crew||!fine)return;
 gsap.set(crew,{transformPerspective:1400});
 var cy=gsap.quickTo(crew,'rotationY',{duration:1,ease:'power3'}),cx=gsap.quickTo(crew,'rotationX',{duration:1,ease:'power3'}),
     lines=$$('.big .ln').map(function(l){return gsap.quickTo(l,'x',{duration:1.1,ease:'power3'})});
 hero.addEventListener('pointermove',function(e){var px=e.clientX/innerWidth-.5,py=e.clientY/innerHeight-.5;cy(px*5);cx(-py*3);lines.forEach(function(q,i){q(px*(i%2?-1:1)*24*(1+i*.3))})},{passive:true});
 hero.addEventListener('pointerleave',function(){cy(0);cx(0);lines.forEach(function(q){q(0)})});
});

/* ============ 7. Showreel becomes a film camera ============ */
safe('reel',function(){
 var st=$('.reel .stage');if(!st)return;
 var fr=$$('.fr',st),N=fr.length,leak=d.createElement('div'),sn=$('.sn',st),frames=$('.frames',st),last=-1;
 leak.className='leak';leak.setAttribute('aria-hidden','true');st.insertBefore(leak,$('.lb.t',st));
 ScrollTrigger.create({trigger:'.reel',start:'top top',end:'+='+(N*(innerWidth<700?60:90))+'%',onUpdate:function(s){
  var t=s.progress*N,f=0,i;for(i=1;i<N;i++)f=Math.max(f,1-Math.abs(t-(i-.15))/.22);
  leak.style.display=f>.02?'block':'none';leak.style.opacity=(f*.75).toFixed(3);
  if(frames){frames.style.transform=f>.02?'translate('+((Math.random()-.5)*f*10).toFixed(1)+'px,'+((Math.random()-.5)*f*10).toFixed(1)+'px)':''}
  var idx=Math.min(N-1,Math.floor(t+.15));
  if(sn&&idx!==last){last=idx;sn.textContent=idx?'REC · Scene 0'+idx+' / 0'+(N-1):'REC · Opening'}}});
 /* optional showreel video (Settings > SalValAn): play only while the reel is on screen */
 var vd=$('video',st);
 if(vd){vd.muted=true;ScrollTrigger.create({trigger:'.reel',start:'top bottom',end:'bottom top',onToggle:function(s){if(s.isActive){var p=vd.play();p&&p.catch&&p.catch(function(){})}else vd.pause()}})}
});

/* ============ 8. Section rail (desktop) ============ */
safe('rail',function(){
 if(!fine||innerWidth<961)return;
 var links=$$('.sva-hd .nav a[href^="#"]');if(links.length<3)return;
 var rail=d.createElement('nav');rail.className='rail';rail.setAttribute('aria-label','Section progress');
 links.forEach(function(a){var l=d.createElement('a');l.href=a.getAttribute('href');l.setAttribute('data-n',a.textContent.replace(/\s+/g,' ').trim());l.setAttribute('aria-label',l.getAttribute('data-n'));l.appendChild(d.createElement('i'));rail.appendChild(l)});
 d.body.appendChild(rail);
 function sync(){links.forEach(function(a,i){rail.children[i].classList.toggle('on',a.getAttribute('aria-current')==='true')})}
 new MutationObserver(sync).observe($('.sva-hd .nav'),{attributes:true,subtree:true,attributeFilter:['aria-current']});sync();
 addEventListener('scroll',function(){rail.classList.toggle('show',scrollY>320)},{passive:true});
});

/* ============ 9. End credits roll with the scroll ============ */
safe('credits',function(){
 var roll=$$('.credits .roll > *');if(!roll.length)return;
 roll.forEach(function(el,i){var end=i===roll.length-1,tl=gsap.timeline({defaults:{ease:'none'},scrollTrigger:{trigger:el,start:'top 105%',end:'bottom -5%',scrub:true}});
  tl.fromTo(el,{opacity:0,y:70,filter:'blur(10px)'},{opacity:1,y:0,filter:'blur(0px)',duration:.3});
  if(end)tl.to({},{duration:.7},.3);else tl.to(el,{opacity:0,y:-70,filter:'blur(10px)',duration:.3},.7)});
});

/* ============ 10. Logo: bars grow in, dance with scroll speed, bounce on hover ============ */
safe('logo',function(){
 var hl=$$('.sva-hd .logo svg,.sva-ft .logo svg');if(!hl.length)return;
 var all=[];hl.forEach(function(sv){all.push($$('rect',sv))});
 var first=all[0];if(first){gsap.set(first,{scaleY:0});var gr=function(){gsap.to(first,{scaleY:1,duration:1.1,ease:'back.out(2.2)',stagger:.12,delay:.15})};SVAMO.introDone?gr():d.addEventListener('sva:intro',gr,{once:true})}
 var amt=[.55,-.3,.45],t=0;
 ScrollTrigger.create({onUpdate:function(s){var v=clamp(Math.abs(s.getVelocity())/2600,0,1);if(!first)return;if(v>.02)gsap.to(first,{scaleY:function(i){return 1+v*amt[i]},duration:.18,overwrite:'auto'});clearTimeout(t);t=setTimeout(function(){gsap.to(first,{scaleY:1,duration:.7,ease:'elastic.out(1,.5)',overwrite:'auto'})},160)}});
 $$('.logo').forEach(function(a){var r=$$('rect',a);if(!r.length)return;a.addEventListener('pointerenter',function(){gsap.fromTo(r,{scaleY:1},{scaleY:function(i){return[1.5,.6,1.4][i]},duration:.22,yoyo:true,repeat:3,stagger:.06,ease:'sine.inOut',overwrite:'auto',onComplete:function(){gsap.set(r,{scaleY:1})}})})});
});

/* ============ 11. Descriptions reveal line by line behind a mask ============ */
safe('lines',function(){
 if(!window.SplitText)return;
 var run=function(){$$('.pc .pt > p,.pc.wide > p,#about .two > div:last-child > p,#about .two h3,.tl li p').forEach(function(e){
  SplitText.create(e,{type:'lines',mask:'lines',autoSplit:true,onSplit:function(sp){return gsap.from(sp.lines,{yPercent:110,duration:1.1,stagger:.09,ease:'expo.out',scrollTrigger:{trigger:e,start:'top 92%',once:true}})}})})};
 (d.fonts&&d.fonts.ready)?d.fonts.ready.then(run):run();
});

/* ============ 12. Features: bullets slide in, skill chips pop, years decode ============ */
safe('features',function(){
 ScrollTrigger.batch('.ck li',{start:'top 93%',once:true,onEnter:function(b){gsap.from(b,{x:-34,opacity:0,duration:.9,stagger:.09,ease:'expo.out',clearProps:'transform,opacity'})}});
 ScrollTrigger.batch('.chips span',{start:'top 95%',once:true,onEnter:function(b){gsap.from(b,{scale:.55,y:14,opacity:0,duration:.75,stagger:.045,ease:'back.out(2.2)',clearProps:'transform,opacity'})}});
 ScrollTrigger.batch('.flow span',{start:'top 95%',once:true,onEnter:function(b){gsap.from(b,{x:-30,opacity:0,duration:.9,stagger:.12,ease:'expo.out',clearProps:'transform,opacity'})}});
 if(gsap.plugins.scrambleText)$$('.tl time').forEach(function(e){var t=e.textContent,num=/^[\d\s]+$/.test(t);ScrollTrigger.create({trigger:e,start:'top 92%',once:true,onEnter:function(){gsap.to(e,{duration:1.1,scrambleText:{text:t,chars:num?'0123456789':'upperCase',speed:.6,tweenLength:false}})}})});
});

/* ============ 13. Numbers count up ============ */
safe('stats',function(){
 $$('[data-count]').forEach(function(e){var n=parseFloat(e.getAttribute('data-count')),o={v:0};e.textContent='0';
  ScrollTrigger.create({trigger:e,start:'top 92%',once:true,onEnter:function(){gsap.to(o,{v:n,duration:1.8,ease:'power3.out',onUpdate:function(){e.textContent=Math.round(o.v)}})}})});
});

/* ============ 14. Giant type band moves with the scroll ============ */
safe('band',function(){
 var bd=$('.band');if(!bd)return;var rows=$$('.bd',bd);
 rows.forEach(function(r,i){gsap.fromTo(r,{x:i?'-35%':'0%'},{x:i?'0%':'-35%',ease:'none',scrollTrigger:{trigger:bd,start:'top bottom',end:'bottom top',scrub:.5}})});
 var sk=gsap.quickTo(bd,'skewX',{duration:.4,ease:'power3'}),st;
 ScrollTrigger.create({trigger:bd,start:'top bottom',end:'bottom top',onUpdate:function(s){sk(clamp(s.getVelocity()/-400,-8,8));clearTimeout(st);st=setTimeout(function(){sk(0)},120)}});
});

/* ============ 15. Images: faces open like an iris, phone screens float at different depths ============ */
safe('images',function(){
 $$('.idr .av').forEach(function(a){gsap.fromTo(a,{clipPath:'circle(0% at 50% 50%)',scale:1.35},{clipPath:'circle(75% at 50% 50%)',scale:1,duration:1.3,ease:'expo.out',clearProps:'clipPath,transform',scrollTrigger:{trigger:a,start:'top 94%',once:true}})});
 $$('.phones .shot').forEach(function(s,i){gsap.fromTo(s,{y:i?70:20},{y:i?-30:-60,ease:'none',scrollTrigger:{trigger:s.parentNode,start:'top bottom',end:'bottom top',scrub:true}})});
});

/* ============ 16. Buttons roll their label; cards carry a spotlight that follows the cursor ============ */
safe('buttons',function(){
 if(!fine)return;
 $$('.btn').forEach(function(b){if(b.children.length||b.hasAttribute('data-motion')||b.hasAttribute('data-copy')||b.hasAttribute('data-install'))return;var t=b.textContent.trim();if(!t)return;
  var w=d.createElement('span'),i=d.createElement('span');w.className='bt';w.setAttribute('data-t',t);i.textContent=t;w.appendChild(i);b.textContent='';b.appendChild(w)});
 $$('.pc,.card').forEach(function(c){
  c.addEventListener('pointerenter',function(){c.style.setProperty('--sp',1)});
  c.addEventListener('pointermove',function(e){var r=c.getBoundingClientRect();c.style.setProperty('--mx',(e.clientX-r.left)+'px');c.style.setProperty('--my',(e.clientY-r.top)+'px')},{passive:true});
  c.addEventListener('pointerleave',function(){c.style.setProperty('--sp',0)})});
});

/* services rows: refresh once everything above has changed the layout */
addEventListener('load',function(){ScrollTrigger.refresh()});
})();
