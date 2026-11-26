/* SalValAn background music. Browsers refuse to play sound until the visitor does something, so the page opens with an
   "Enter" screen: the tap on it is the permission, and the music starts at that same moment (the intro animation also waits
   for it). If the browser already allows sound (a returning visitor), the screen is skipped and the music just starts.
   "Enter without sound" or the header pause button turns it off and that choice is remembered. Gapless loop through Web Audio,
   HTMLAudio fallback, fades, pauses when the tab is hidden. */
(function(){var d=document,btns=[].slice.call(d.querySelectorAll('[data-sound]'));if(!btns.length)return;
var src=(window.SVA&&SVA.music)||'';if(!src){btns.forEach(function(b){b.hidden=true});return}
var AC=window.AudioContext||window.webkitAudioContext,VOL=.38,ctx=null,gain=null,el=null,want=true,run=false,ready=null,tm=0,iv=0,pref=null;
try{pref=localStorage.getItem('sva-music')}catch(e){}
if(pref==='off')want=false;
function save(v){try{localStorage.setItem('sva-music',v)}catch(e){}}
function ui(){btns.forEach(function(b){b.classList.toggle('on',run);b.setAttribute('aria-label',run?'Pause background music':'Play background music');b.title=run?'Music: playing (tap to pause)':'Music: paused (tap to play)'})}
var h=d.documentElement,gate=d.getElementById('sva-gate');
function enter(){
 if(h.classList.contains('sva-gate')){h.classList.remove('sva-gate');d.dispatchEvent(new Event('sva:enter'))}
 if(gate&&!gate.classList.contains('out')){gate.classList.add('out');setTimeout(function(){gate.remove()},900)}}
function sync(){run=!!(want&&((ctx&&ctx.state==='running')||(!ctx&&el&&!el.paused)));ui();if(run)enter()}
/* decode once, then loop the buffer with no gap */
function load(){
 if(ready)return ready;
 ready=(ctx?fetch(src).then(function(r){if(!r.ok)throw 0;return r.arrayBuffer()}).then(function(a){return new Promise(function(ok,no){ctx.decodeAudioData(a,ok,no)})}).then(function(buf){
   gain=ctx.createGain();gain.gain.value=0;gain.connect(ctx.destination);
   var n=ctx.createBufferSource();n.buffer=buf;n.loop=true;n.connect(gain);n.start(0)}):Promise.reject()).catch(function(){
   try{if(ctx)ctx.close()}catch(e){}ctx=null;gain=null;el=new Audio(src);el.loop=true;el.volume=0;el.preload='auto'});
 return ready}
function fade(v,sec){
 clearInterval(iv);
 if(gain&&ctx){var t=ctx.currentTime;gain.gain.cancelScheduledValues(t);gain.gain.setValueAtTime(gain.gain.value,t);gain.gain.linearRampToValueAtTime(v,t+sec)}
 else if(el){var s=(v-el.volume)/(sec*20);iv=setInterval(function(){var x=el.volume+s;if((s>0&&x>=v)||(s<0&&x<=v)){x=v;clearInterval(iv)}el.volume=Math.max(0,Math.min(1,x))},50)}}
function start(){
 if(!want)return;clearTimeout(tm);
 if(!ctx&&!el&&AC){try{ctx=new AC();ctx.onstatechange=sync}catch(e){ctx=null}}
 if(ctx&&ctx.state==='suspended'){try{ctx.resume().catch(function(){})}catch(e){}}
 load().then(function(){
  if(!want)return;
  if(ctx){ctx.resume().then(function(){if(!want)return;fade(VOL,2.2);sync()},function(){})}
  else if(el){var p=el.play();(p&&p.then?p:Promise.resolve()).then(function(){if(!want)return;fade(VOL,2.2);sync()},function(){sync()})}})}
function pause(){
 want=false;run=false;save('off');ui();fade(0,.9);
 tm=setTimeout(function(){if(want)return;if(ctx&&ctx.state==='running')ctx.suspend();if(el)el.pause()},1000)}
function resume(){want=true;run=true;save('on');ui();start()}
btns.forEach(function(b){b.addEventListener('click',function(){want&&run?pause():resume()})});
/* the browser may refuse sound before any interaction: begin on the first tap, click or key press anywhere */
var evs=['pointerdown','touchend','click','keydown'];
function first(e){
 if(run){evs.forEach(function(n){d.removeEventListener(n,first,true)});return}
 if(e&&e.target&&e.target.closest&&e.target.closest('[data-sound],[data-nosound]'))return;
 if(want)start()}
if(want)evs.forEach(function(n){d.addEventListener(n,first,true)});
/* tab hidden: pause; back again: continue */
d.addEventListener('visibilitychange',function(){
 if(!want)return;
 if(d.hidden){if(ctx&&ctx.state==='running')ctx.suspend();if(el)el.pause();clearInterval(iv)}
 else{if(ctx)ctx.resume();if(el){var p=el.play();p&&p.then&&p.then(function(){fade(VOL,1)},function(){})}}});
/* the Enter screen: its buttons are the tap the browser wants */
var go=d.querySelector('[data-enter]'),no=d.querySelector('[data-nosound]');
go&&go.addEventListener('click',function(){resume();enter()});
no&&no.addEventListener('click',function(){pause();enter()});
if(gate&&go&&h.classList.contains('sva-gate')){try{go.focus({preventScroll:true})}catch(e){}}
/* try to start now (this also downloads the track while the visitor reads the Enter screen); if the browser allows sound, the screen is skipped */
if(want)start();
ui();
})();
