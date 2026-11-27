/* SalValAn background music (6.5). Plain HTML audio element, started INSIDE the tap on "Enter".
   Why: iPhone/Safari only allows sound that is started directly by a tap, Web Audio is muted by the iPhone silent switch,
   and decoding a 3-minute track through Web Audio is slow on phones. An <audio> element streams at once and ignores the switch.
   The intro starts right after the tap, so opening never waits for the audio. "Enter without sound" and the header button
   remember the choice. Pauses when the tab is hidden. */
(function(){var d=document,btns=[].slice.call(d.querySelectorAll('[data-sound]'));if(!btns.length)return;
var src=(window.SVA&&SVA.music)||'';if(!src){btns.forEach(function(b){b.hidden=true});return}
var VOL=.38,el=null,want=true,run=false,iv=0,canVol=true,h=d.documentElement,gate=d.getElementById('sva-gate');
window.__svaGate=1; /* the Enter screen is only ever closed by the visitor's own button */
/* iOS 16.4+: play through the speaker even when the silent switch is on */
try{if(navigator.audioSession)navigator.audioSession.type='playback'}catch(e){}
function save(v){try{localStorage.setItem('sva-music',v)}catch(e){}}
function ui(){btns.forEach(function(b){b.classList.toggle('on',run);b.setAttribute('aria-label',run?'Pause background music':'Play background music');b.title=run?'Music: playing (tap to pause)':'Music: paused (tap to play)'})}
function enter(){
 if(h.classList.contains('sva-gate')){h.classList.remove('sva-gate');d.dispatchEvent(new Event('sva:enter'))}
 if(gate&&!gate.classList.contains('out')){gate.classList.add('out');setTimeout(function(){gate.remove()},600)}}
function sync(){run=!!(want&&el&&!el.paused);ui()}
function make(){
 if(el)return el;
 el=new Audio();el.loop=true;el.preload='auto';el.setAttribute('playsinline','');el.setAttribute('webkit-playsinline','');
 el.src=src;try{el.volume=.5;canVol=Math.abs(el.volume-.5)<.01;el.volume=VOL}catch(e){canVol=false}
 ['play','playing','pause','ended'].forEach(function(n){el.addEventListener(n,sync)});
 return el}
function fade(v,sec,done){
 clearInterval(iv);if(!el)return;
 if(!canVol){done&&done();return}
 var s=(v-el.volume)/(sec*20);if(!s){done&&done();return}
 iv=setInterval(function(){var x=el.volume+s;if((s>0&&x>=v)||(s<0&&x<=v)){x=v;clearInterval(iv)}el.volume=Math.max(0,Math.min(1,x));if(x===v&&done)done()},50)}
/* must run synchronously inside a tap/click so iOS allows it */
function start(){
 if(!want)return;
 make();if(canVol&&el.paused)el.volume=0;
 var p=el.play();
 (p&&p.then?p:Promise.resolve()).then(function(){if(!want)return;sync();fade(VOL,2)},function(){sync()})}
function pause(){
 want=false;save('off');run=false;ui();
 fade(0,.6,function(){if(!want&&el)el.pause()});if(!canVol&&el)el.pause()}
function resume(){want=true;save('on');start()}
btns.forEach(function(b){b.addEventListener('click',function(){want&&run?pause():resume()})});
/* if the browser refused earlier, begin on the first tap, click or key press anywhere */
var evs=['touchend','click','keydown'];
function first(e){
 if(h.classList.contains('sva-gate'))return;
 if(run){evs.forEach(function(n){d.removeEventListener(n,first,true)});return}
 if(e&&e.target&&e.target.closest&&e.target.closest('[data-sound],[data-nosound]'))return;
 if(want)start()}
if(want)evs.forEach(function(n){d.addEventListener(n,first,true)});
/* tab hidden: pause; back again: continue */
d.addEventListener('visibilitychange',function(){
 if(!want||!el)return;
 if(d.hidden)el.pause();else if(run||!h.classList.contains('sva-gate')){var p=el.play();p&&p.catch&&p.catch(function(){})}});
/* the Enter screen: sound starts first (inside the tap), then the page opens at once */
var go=d.querySelector('[data-enter]'),no=d.querySelector('[data-nosound]');
go&&go.addEventListener('click',function(){resume();enter()});
no&&no.addEventListener('click',function(){pause();enter()});
if(gate&&go&&h.classList.contains('sva-gate')){try{go.focus({preventScroll:true})}catch(e){}}
/* start downloading the track while the visitor reads the Enter screen (it plays on the tap) */
make();try{el.load()}catch(e){}
ui();
})();
