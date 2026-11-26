<?php
/**
 * Plugin Name: SalValAn Portfolio
 * Description: Cinematic motion one-page portfolio (GSAP, ScrollTrigger, Lenis, SplitText, Flip, WebGL aurora hero, film-camera showreel, end credits, project preview, self-drawing timeline, giant call to action, background music) for SalValAn. Home includes the team. Sections: Showreel, About, Skills & Services, Projects, Experience, Contact. Dark and light mode, mobile tab bar, installable PWA.
 * Version: 6.3.3
 * Author: SalValAn
 */
if(!defined('ABSPATH'))exit;
define('SVA_VER','6.4.1');define('SVA_DIR',plugin_dir_path(__FILE__));define('SVA_URL',plugin_dir_url(__FILE__));
/* every section of the long home page, in scroll order (one file each in /sections) */
const SVA_SECTIONS=['home','reel','band','about','services','projects','experience','cta','contact','credits'];
/* the old v2 separate pages: they now redirect to their section on the home page */
const SVA_LEGACY=['team','about','skills','services','projects','experience','contact'];

function sva_fields(){return [
 'jonee_email'=>['Jonee\'s email','',"Shown in the Contact section. Leave empty to hide."],
 'atms_url'=>['ATMS live link','https://alumniappv2.ethiodeploy.app','Used by every ATMS button and link on the site.'],
 'occdl_url'=>['OCCDL live link','https://antiquina-ota.ethiodeploy.app','Used by the OCCDL button in Projects.'],
 'sclf_url'=>['SCLF live link','https://sclfapp.ethiodeploy.app','Used by the SCLF button in Projects.'],
 'atms_year'=>['Year ATMS was built','',"Example: 2025. Leave empty to show 'Earlier project'."],
 'project_name'=>['Team project name','SalValAn Cloud Portfolio','Shown in the Projects section.'],
 'team_story'=>['Team story','Three graduating students from different groups joined forces for their Cloud Computing final project. Between us we cover analysis, code and documentation, so one team can take a system from first requirement to final manual.','About section.'],
 'mission'=>['Mission','Deliver systems that are well planned, well built and well documented.','About section.'],
 'reel_video'=>['Showreel video (optional)','',"Paste the link of an MP4 file (upload it in Media > Add New, then copy its URL). It plays softly behind the opening card of the Showreel. Keep it under 8 MB. Leave empty for no video."],
 'music_url'=>['Background music (optional)','',"Leave empty to use the built-in track (Sunburst by Tobu & Itro). To use your own, upload an MP3 in Media > Add New and paste its link here. Type off to remove the music button."],
 'form_shortcode'=>['Contact form shortcode','',"Optional. Paste a SureForms shortcode to replace the built-in email form."],
];}
function sva_opt($k){$o=get_option('sva_opts',[]);$f=sva_fields();return (isset($o[$k])&&$o[$k]!=='')?$o[$k]:$f[$k][1];}

function sva_tokens($h){
 $a=SVA_URL.'assets';$em=sva_opt('jonee_email');$yr=sva_opt('atms_year');$ph=$a.'/img/mariel.jpg';/* Mariel's photo is always the bundled file in assets/img, not a Media Library upload */
 $au=esc_url(sva_opt('atms_url'));$ou=esc_url(sva_opt('occdl_url'));$su=esc_url(sva_opt('sclf_url'));$ah=esc_html(preg_replace('#^https?://#','',rtrim(sva_opt('atms_url'),'/')));
 $h=str_replace(['{{assets}}','{{project_name}}','{{team_story}}','{{mission}}','{{atms_year}}','{{atms_url}}','{{atms_host}}','{{occdl_url}}','{{sclf_url}}'],[$a,esc_html(sva_opt('project_name')),esc_html(sva_opt('team_story')),esc_html(sva_opt('mission')),esc_html($yr!==''?$yr:'Earlier project'),$au,$ah,$ou,$su],$h);
 $h=preg_replace_callback('/\{\{av_mariel(?::(xl))?\}\}/',function($m)use($ph){$x=!empty($m[1])?' xl':'';
  return $ph?'<img class="av'.$x.'" data-r="analyze" src="'.esc_url($ph).'" alt="Mariel Saldivar" width="240" height="240" loading="lazy">':'<span class="av mono'.$x.'" data-r="analyze" role="img" aria-label="Mariel Saldivar">MS</span>';},$h);
 if($em){$e=esc_attr($em);
  $r1='<div><dt>Email</dt><dd><a href="mailto:'.$e.'">'.esc_html($em).'</a></dd></div>';
  $r2='<div class="row"><span>Email</span><a href="mailto:'.$e.'">'.esc_html($em).'</a><button type="button" class="btn sm alt" data-copy="'.$e.'">Copy</button></div>';
  $o='<option value="'.$e.'" data-k="jonee">Jonee John Antiquina · Programmer, Designer &amp; Developer</option>';
 }else{$r1=$r2=$o='';}
 $h=str_replace(['{{jonee_email_row}}','{{jonee_email_row2}}','{{opt_jonee}}','{{opt_mariel}}','{{opt_diane}}'],[$r1,$r2,$o,'<option value="occ.saldivar.mariel@gmail.com" data-k="mariel">Mariel Saldivar · Project Analyst</option>','<option value="occ.valle.dianejill@gmail.com" data-k="diane">Diane Valle · Technical Writer</option>'],$h);
 $rv=esc_url(sva_opt('reel_video'));
 $h=str_replace('{{reel_video}}',$rv?'<video src="'.$rv.'" muted loop playsinline preload="metadata" aria-hidden="true" tabindex="-1"></video>':'',$h);
 $sc=trim(sva_opt('form_shortcode'));
 if($sc!=='')$h=preg_replace('/<!--SVAFORM-->.*?<!--\/SVAFORM-->/s',do_shortcode($sc),$h);
 return $h;
}

/* ---- the long home page is assembled from /sections/*.html ---- */
function sva_build_home(){
 $o='';foreach(SVA_SECTIONS as $s){$f=SVA_DIR."sections/$s.html";if(is_readable($f))$o.=trim(file_get_contents($f))."\n";}
 return '<main id="main">'.$o.'</main>';
}
function sva_url($slug){return $slug==='home'?home_url('/'):home_url('/#'.$slug);}
function sva_is_page(){
 if(!is_page())return false;$id=get_queried_object_id();
 if(get_post_meta($id,'_salvalan',true))return true;
 $po=get_post($id);return $po&&in_array($po->post_name,SVA_SECTIONS,true)&&strpos($po->post_content,'<main id="main">')!==false;
}
function sva_part($n){return sva_tokens(file_get_contents(SVA_DIR."parts/$n.html"));}

/* old separate pages (/team/, /about/ ...) land on their section of the home page */
add_action('template_redirect',function(){
 if(!is_page())return;$po=get_queried_object();
 if(!$po||$po->post_name==='home'||!in_array($po->post_name,SVA_LEGACY,true)||!get_post_meta($po->ID,'_salvalan',true))return;
 $q=[];foreach(['to','service'] as $k){if(isset($_GET[$k]))$q[$k]=sanitize_text_field(wp_unslash($_GET[$k]));}
 $u=home_url('/');if($q)$u=add_query_arg($q,$u);
 wp_safe_redirect($u.'#'.($po->post_name==='team'?'home':($po->post_name==='skills'?'services':$po->post_name)),301);exit;
},1);

/* ---- render with our own template (bypasses theme header/footer) ---- */
add_filter('template_include',function($t){return sva_is_page()?SVA_DIR.'template.php':$t;},99);
add_action('wp_enqueue_scripts',function(){
 if(!sva_is_page())return;
 wp_enqueue_style('sva',SVA_URL.'assets/css/sva.css',[],SVA_VER.'.'.filemtime(SVA_DIR.'assets/css/sva.css'));
 wp_enqueue_style('sva-motion',SVA_URL.'assets/css/sva-motion.css',['sva'],SVA_VER.'.'.filemtime(SVA_DIR.'assets/css/sva-motion.css'));
 wp_enqueue_style('sva-fx',SVA_URL.'assets/css/sva-fx.css',['sva-motion'],SVA_VER.'.'.filemtime(SVA_DIR.'assets/css/sva-fx.css'));
 wp_enqueue_style('sva-fx2',SVA_URL.'assets/css/sva-fx2.css',['sva-fx'],SVA_VER.'.'.filemtime(SVA_DIR.'assets/css/sva-fx2.css'));
 wp_enqueue_style('sva-fix',SVA_URL.'assets/css/sva-fix.css',['sva-fx2'],SVA_VER.'.'.filemtime(SVA_DIR.'assets/css/sva-fix.css'));
 foreach(['gsap'=>'gsap.min.js','sva-st'=>'ScrollTrigger.min.js','sva-lenis'=>'lenis.min.js'] as $k=>$f)wp_enqueue_script($k,SVA_URL.'assets/js/vendor/'.$f,$k==='sva-st'?['gsap']:[],'vendor',true);
 wp_enqueue_script('sva-motion',SVA_URL.'assets/js/sva-motion.js',['gsap','sva-st','sva-lenis'],SVA_VER.'.'.filemtime(SVA_DIR.'assets/js/sva-motion.js'),true);
 foreach(['sva-split'=>'SplitText.min.js','sva-scr'=>'ScrambleTextPlugin.min.js','sva-flip'=>'Flip.min.js'] as $k=>$f)wp_enqueue_script($k,SVA_URL.'assets/js/vendor/'.$f,['gsap'],'vendor',true);
 wp_enqueue_script('sva-fx',SVA_URL.'assets/js/sva-fx.js',['sva-motion','sva-split','sva-scr','sva-flip'],SVA_VER.'.'.filemtime(SVA_DIR.'assets/js/sva-fx.js'),true);
 wp_enqueue_script('sva-fx2',SVA_URL.'assets/js/sva-fx2.js',['sva-fx'],SVA_VER.'.'.filemtime(SVA_DIR.'assets/js/sva-fx2.js'),true);
 wp_enqueue_script('sva',SVA_URL.'assets/js/sva.js',['sva-fx2'],SVA_VER.'.'.filemtime(SVA_DIR.'assets/js/sva.js'),true);
 wp_enqueue_script('sva-sound',SVA_URL.'assets/js/sva-sound.js',SVA_URL.'assets/js/sva-fix.js',SVA_URL.'assets/css/sva-fix.css',['sva'],SVA_VER.'.'.filemtime(SVA_DIR.'assets/js/sva-sound.js'),true);
 wp_enqueue_script('sva-fix',SVA_URL.'assets/js/sva-fix.js',['sva-sound'],SVA_VER.'.'.filemtime(SVA_DIR.'assets/js/sva-fix.js'),true);
 $mu=sva_opt('music_url');$mu=$mu===''?SVA_URL.'assets/audio/sunburst.mp3':($mu==='off'?'':$mu);
 $hp=wp_parse_url(home_url('/'),PHP_URL_PATH);$hp=$hp?$hp:'/';wp_add_inline_script('sva','window.SVA={sw:'.wp_json_encode($hp.'salvalan-sw').',scope:'.wp_json_encode($hp).',music:'.wp_json_encode($mu).'};','before');
},99);
/* hyphenation needs the right language: the page text is English, so declare it */
add_filter('language_attributes',function($o){return sva_is_page()?'lang="en"':$o;},99);
add_action('after_setup_theme',function(){add_theme_support('title-tag');});
add_filter('pre_get_document_title',function($t){return sva_is_page()?'SalValAn Portfolio · Build. Analyze. Document.':$t;},99);
const SVA_DESC='SalValAn: Jonee Antiquina (developer), Mariel Saldivar (project analyst) and Diane Valle (technical writer). Build. Analyze. Document.';
add_action('wp_head',function(){
 if(!sva_is_page())return;
 echo '<meta name="description" content="'.esc_attr(SVA_DESC).'"><meta property="og:title" content="SalValAn Portfolio"><meta property="og:description" content="'.esc_attr(SVA_DESC).'"><meta property="og:type" content="website"><meta property="og:site_name" content="SalValAn">';
},2);
add_action('wp_head',function(){
 if(!sva_is_page())return;
 echo '<meta name="theme-color" content="#0B0B0F"><link rel="manifest" href="'.esc_url(home_url('/salvalan-manifest')).'"><link rel="apple-touch-icon" href="'.esc_url(SVA_URL.'assets/img/apple-touch-icon.png').'"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"><meta name="color-scheme" content="dark light"><meta name="apple-mobile-web-app-title" content="SVA"><link rel="icon" type="image/png" href="'.esc_url(SVA_URL.'assets/img/icon-192.png').'"><link rel="preload" as="font" type="font/woff2" crossorigin href="'.esc_url(SVA_URL.'assets/fonts/space-grotesk-latin-wght-normal.woff2').'"><link rel="preload" as="font" type="font/woff2" crossorigin href="'.esc_url(SVA_URL.'assets/fonts/manrope-latin-wght-normal.woff2').'">';
 echo '<script>(function(){try{var t=localStorage.getItem("sva-theme");if(!t)t="dark";document.documentElement.setAttribute("data-theme",t)}catch(e){}try{var h=document.documentElement;if(!matchMedia("(prefers-reduced-motion:reduce)").matches){h.classList.add("mo");if(!sessionStorage.getItem("sva-pre"))h.classList.add("mo-pre")}}catch(e){}'.(sva_opt('music_url')==='off'?'':'try{if(localStorage.getItem("sva-music")!=="off"){var g=document.documentElement;g.classList.add("sva-gate");setTimeout(function(){if(g.classList.contains("sva-gate")){g.classList.remove("sva-gate");document.dispatchEvent(new Event("sva:enter"))}},15000)}}catch(e){}').'})()</script>';
},1);

/* ---- PWA: manifest, service worker, offline page (extension-less so any host serves them) ---- */
add_action('init',function(){
 $p=parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH);$b=rtrim(parse_url(home_url('/'),PHP_URL_PATH),'/');$p='/'.ltrim(substr($p,strlen($b)),'/');$p=rtrim($p,'/');
 if($p==='/salvalan-manifest'){
  header('Content-Type: application/manifest+json; charset=utf-8');header('Cache-Control: public, max-age=3600');
  $i=SVA_URL.'assets/img/';$sp=$b?$b.'/':'/';
  echo wp_json_encode(['id'=>$sp,'name'=>'SVA Portfolio','short_name'=>'SVA','description'=>'Build. Analyze. Document. The SalValAn team portfolio.','start_url'=>$sp,'scope'=>$sp,'display'=>'standalone','display_override'=>['standalone','minimal-ui'],'orientation'=>'any','categories'=>['education','productivity'],'background_color'=>'#0B0B0F','theme_color'=>'#0B0B0F','lang'=>'en',
   'icons'=>[['src'=>$i.'icon-192.png','sizes'=>'192x192','type'=>'image/png','purpose'=>'any'],['src'=>$i.'icon-512.png','sizes'=>'512x512','type'=>'image/png','purpose'=>'any'],['src'=>$i.'icon-maskable-512.png','sizes'=>'512x512','type'=>'image/png','purpose'=>'maskable']],
   'shortcuts'=>[['name'=>'Home','url'=>sva_url('home')],['name'=>'Projects','url'=>sva_url('projects')],['name'=>'Contact','url'=>sva_url('contact')]]],JSON_UNESCAPED_SLASHES);exit;}
 if($p==='/salvalan-sw'){
  header('Content-Type: application/javascript; charset=utf-8');header('Service-Worker-Allowed: /');header('Cache-Control: no-cache');
  $v=SVA_VER.'-'.filemtime(SVA_DIR.'assets/css/sva.css').filemtime(SVA_DIR.'assets/js/sva.js').filemtime(SVA_DIR.'assets/js/sva-motion.js').filemtime(SVA_DIR.'assets/css/sva-motion.css').filemtime(SVA_DIR.'assets/js/sva-fx.js').filemtime(SVA_DIR.'assets/css/sva-fx.css').filemtime(SVA_DIR.'assets/js/sva-fx2.js').filemtime(SVA_DIR.'assets/css/sva-fx2.css').filemtime(SVA_DIR.'assets/js/sva-sound.js').filemtime(SVA_DIR.'assets/js/sva-fix.js').filemtime(SVA_DIR.'assets/css/sva-fix.css').filemtime(SVA_DIR.'assets/img/mariel.jpg').filemtime(SVA_DIR.'assets/img/diane.jpg').filemtime(SVA_DIR.'assets/img/jonee.jpg');
  $as=[SVA_URL.'assets/css/sva.css',SVA_URL.'assets/js/sva.js',SVA_URL.'assets/js/sva-motion.js',SVA_URL.'assets/css/sva-motion.css',SVA_URL.'assets/js/sva-fx.js',SVA_URL.'assets/css/sva-fx.css',SVA_URL.'assets/js/sva-fx2.js',SVA_URL.'assets/css/sva-fx2.css',SVA_URL.'assets/js/sva-sound.js',SVA_URL.'assets/js/vendor/SplitText.min.js',SVA_URL.'assets/js/vendor/ScrambleTextPlugin.min.js',SVA_URL.'assets/js/vendor/Flip.min.js',SVA_URL.'assets/js/vendor/gsap.min.js',SVA_URL.'assets/js/vendor/ScrollTrigger.min.js',SVA_URL.'assets/js/vendor/lenis.min.js',SVA_URL.'assets/img/icon-192.png',SVA_URL.'assets/fonts/space-grotesk-latin-wght-normal.woff2',SVA_URL.'assets/fonts/manrope-latin-wght-normal.woff2',SVA_URL.'assets/img/jonee.jpg',SVA_URL.'assets/img/diane.jpg',SVA_URL.'assets/img/mariel.jpg',SVA_URL.'assets/img/ak-admin-web.jpg',SVA_URL.'assets/img/ak-mobile-home.jpg',SVA_URL.'assets/img/ak-mobile-login.jpg',SVA_URL.'assets/img/atms-home.jpg',SVA_URL.'assets/img/occdl-home.jpg',SVA_URL.'assets/img/sclf-home.jpg'];
  $hp=wp_parse_url(home_url('/'),PHP_URL_PATH);$core=[$hp?$hp:'/'];
  echo "const V='sva-$v',CORE=".wp_json_encode(array_values(array_unique($core))).",ASSETS=".wp_json_encode($as).",OFF=".wp_json_encode(($hp?$hp:'/').'salvalan-offline').";\n";
  echo <<<'JS'
self.addEventListener('install',e=>{e.waitUntil(caches.open(V).then(c=>Promise.all(CORE.concat(ASSETS,[OFF]).map(u=>c.add(u).catch(()=>{})))).then(()=>self.skipWaiting()))});
self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(k=>Promise.all(k.filter(x=>x!==V).map(x=>caches.delete(x)))).then(()=>self.clients.claim()))});
self.addEventListener('fetch',e=>{const r=e.request;if(r.method!=='GET')return;const u=new URL(r.url);const path=u.pathname.replace(/\/+$/,'')||'/';
if(/^\/(wp-admin|wp-login|wp-json)/.test(u.pathname))return;
if(r.mode==='navigate'){const ok=u.origin===location.origin&&CORE.indexOf(u.pathname.endsWith('/')?u.pathname:u.pathname+'/')>-1||path==='/';
e.respondWith(fetch(r).then(res=>{if(ok&&res.ok&&res.status===200&&!u.search){const cp=res.clone();caches.open(V).then(c=>c.put(u.pathname,cp)).catch(()=>{})}return res}).catch(()=>caches.match(u.pathname).then(m=>m||caches.match(OFF))));return}
if(r.headers.has('range')||!/^https?:$/.test(u.protocol))return;
if(u.origin===location.origin||/fonts\.(gstatic|googleapis)\.com$/.test(u.host)){e.respondWith(caches.match(r).then(m=>{const n=fetch(r).then(res=>{if(res.ok&&res.status===200){const cp=res.clone();caches.open(V).then(c=>c.put(r,cp)).catch(()=>{})}return res}).catch(()=>m||Response.error());return m||n}).catch(()=>fetch(r)))}});
JS;
  exit;}
 if($p==='/salvalan-offline'){header('Content-Type: text/html; charset=utf-8');
  echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline · SalValAn</title><body style="font:18px/1.6 Manrope,Segoe UI,system-ui,sans-serif;background:#0B0B0F;color:#F3EEE4;display:grid;place-items:center;min-height:100vh;margin:0;text-align:center;padding:24px"><div><h1 style="margin:0 0 8px">You are offline</h1><p>Pages you have opened before still work. Reconnect to see the rest.</p><button onclick="location.reload()" style="font:700 1rem system-ui;padding:12px 24px;border:0;border-radius:10px;background:#F3EEE4;color:#0B0B0F">Try again</button></div>';exit;}
},1);

/* ---- create / refresh the Home page ---- */
function sva_seed(){
 $past=gmdate('Y-m-d H:i:s',time()-86400);
 $html=sva_build_home();
 $post=['post_title'=>'Home','post_name'=>'home','post_type'=>'page','post_status'=>'publish','post_content'=>"<!-- wp:html -->\n$html\n<!-- /wp:html -->",'post_date'=>get_date_from_gmt($past),'post_date_gmt'=>$past,'comment_status'=>'closed'];
 $ex=get_page_by_path('home',OBJECT,'page');
 if($ex){$post['ID']=$ex->ID;$id=wp_update_post($post);}else{$id=wp_insert_post($post);}
 if($id&&!is_wp_error($id)){
  update_post_meta($id,'_salvalan',1);update_post_meta($id,'_sva_ver',SVA_VER);delete_post_meta($id,'_elementor_data');delete_post_meta($id,'_elementor_edit_mode');
  update_option('show_on_front','page');update_option('page_on_front',(int)$id);
 }
 /* plain permalinks make every /page/ URL show the front page. Switch to pretty ones. */
 if(get_option('permalink_structure')===''){global $wp_rewrite;$wp_rewrite->set_permalink_structure('/%postname%/');}
 flush_rewrite_rules(false);
 update_option('sva_ver',SVA_VER);
}
/* upload a newer version of the plugin and the home page refreshes itself once */
add_action('admin_init',function(){if(get_option('sva_ver')!==SVA_VER&&current_user_can('manage_options')){sva_seed();set_transient('sva_notice',1,300);}});
add_action('admin_notices',function(){if(!current_user_can('manage_options'))return;if(get_option('permalink_structure')==='')echo '<div class="notice notice-warning"><p><b>SalValAn:</b> permalinks are set to Plain. Go to Settings &gt; Permalinks and choose Post name. The site still works, but the address bar will show ?page_id=.</p></div>';});
register_activation_hook(__FILE__,function(){sva_seed();set_transient('sva_notice',1,300);});
add_action('admin_notices',function(){if(get_transient('sva_notice')){delete_transient('sva_notice');echo '<div class="notice notice-success is-dismissible"><p><b>SalValAn Portfolio is active.</b> Your long-scroll Home page is ready and set as the front page. Next: <a href="'.esc_url(admin_url('options-general.php?page=salvalan')).'">fill in your details</a>, then clear the Pantheon cache.</p></div>';}});
add_action('admin_post_sva_reseed',function(){if(!current_user_can('manage_options')||!check_admin_referer('sva_reseed'))wp_die('Not allowed');sva_seed();wp_safe_redirect(admin_url('options-general.php?page=salvalan&reseeded=1'));exit;});

/* ---- settings page ---- */
add_action('admin_menu',function(){add_options_page('SalValAn Portfolio','SalValAn','manage_options','salvalan','sva_settings');});
add_action('admin_init',function(){register_setting('sva','sva_opts',['sanitize_callback'=>function($i){$o=[];foreach(sva_fields() as $k=>$f){$v=isset($i[$k])?wp_unslash($i[$k]):'';$o[$k]=$k==='music_url'?(strtolower(trim($v))==='off'?'off':esc_url_raw($v)):(in_array($k,['team_story','mission'])?sanitize_textarea_field($v):($k==='jonee_email'?sanitize_email($v):(in_array($k,['atms_url','reel_video'],true)?esc_url_raw($v):sanitize_text_field($v))));}return $o;}]);});
function sva_settings(){$o=get_option('sva_opts',[]);?>
<div class="wrap"><h1>SalValAn Portfolio</h1>
<?php if(isset($_GET['reseeded']))echo '<div class="notice notice-success"><p>The Home page was rebuilt with the original design.</p></div>';?>
<p>Your whole portfolio lives on one long Home page. The header links scroll to these sections: <?php echo esc_html(implode(', ',array_map('ucfirst',SVA_SECTIONS)));?>.</p>
<form method="post" action="options.php"><?php settings_fields('sva');?><table class="form-table">
<?php foreach(sva_fields() as $k=>$f):$v=$o[$k]??'';?>
<tr><th><label for="sva_<?php echo esc_attr($k);?>"><?php echo esc_html($f[0]);?></label></th><td>
<?php if(in_array($k,['team_story','mission'])):?><textarea class="large-text" rows="4" id="sva_<?php echo $k;?>" name="sva_opts[<?php echo $k;?>]" placeholder="<?php echo esc_attr($f[1]);?>"><?php echo esc_textarea($v);?></textarea>
<?php else:?><input class="regular-text" id="sva_<?php echo $k;?>" name="sva_opts[<?php echo $k;?>]" value="<?php echo esc_attr($v);?>" placeholder="<?php echo esc_attr($f[1]);?>">
<?php endif;?>
<p class="description"><?php echo esc_html($f[2]);?></p></td></tr><?php endforeach;?></table><?php submit_button();?></form>
<hr><h2>Rebuild the Home page</h2><p>Restores the Home page to the original long-scroll design (your own edits to that page will be replaced).</p>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="sva_reseed"><?php wp_nonce_field('sva_reseed');?><?php submit_button('Rebuild Home page','secondary','',false);?></form></div>
<?php }
