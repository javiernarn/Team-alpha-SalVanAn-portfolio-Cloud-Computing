<?php
/**
 * Plugin Name: SalValAn Portfolio
 * Description: Responsive portfolio for SalValAn: 8 pages, working navigation on any permalink setting, dark mode, mobile tab bar and installable PWA.
 * Version: 2.2.0
 * Author: SalValAn
 */
if(!defined('ABSPATH'))exit;
define('SVA_VER','2.2.0');define('SVA_DIR',plugin_dir_path(__FILE__));define('SVA_URL',plugin_dir_url(__FILE__));
const SVA_SLUGS=['home'=>'Home','team'=>'Team','about'=>'About','skills'=>'Skills','services'=>'Services','projects'=>'Projects','experience'=>'Experience','contact'=>'Contact'];

function sva_fields(){return [
 'jonee_email'=>['Jonee\'s email','',"Shown on the Team and Contact pages. Leave empty to hide."],
 'atms_url'=>['ATMS live link','https://alumniappv2.ethiodeploy.app','Used by every ATMS button and link on the site.'],
 'atms_year'=>['Year ATMS was built','',"Example: 2025. Leave empty to show 'Earlier project'."],
 'project_name'=>['Team project name','SalValAn Cloud Portfolio','Shown on the Projects page.'],
 'team_story'=>['Team story','Three graduating students from different groups joined forces for their Cloud Computing final project. Between us we cover analysis, code and documentation, so one team can take a system from first requirement to final manual.','About page.'],
 'mission'=>['Mission','Deliver systems that are well planned, well built and well documented.','About page.'],
 'mariel_photo'=>['Mariel\'s photo (URL)','',"Upload to Media Library, then use the button. Empty shows her initials."],
 'form_shortcode'=>['Contact form shortcode','',"Optional. Paste a SureForms shortcode to replace the built-in email form."],
];}
function sva_opt($k){$o=get_option('sva_opts',[]);$f=sva_fields();return (isset($o[$k])&&$o[$k]!=='')?$o[$k]:$f[$k][1];}

function sva_tokens($h){
 $a=SVA_URL.'assets';$em=sva_opt('jonee_email');$yr=sva_opt('atms_year');$ph=sva_opt('mariel_photo');
 $au=esc_url(sva_opt('atms_url'));$ah=esc_html(preg_replace('#^https?://#','',rtrim(sva_opt('atms_url'),'/')));
 $h=str_replace(['{{assets}}','{{project_name}}','{{team_story}}','{{mission}}','{{atms_year}}','{{atms_url}}','{{atms_host}}'],[$a,esc_html(sva_opt('project_name')),esc_html(sva_opt('team_story')),esc_html(sva_opt('mission')),esc_html($yr!==''?$yr:'Earlier project'),$au,$ah],$h);
 $h=preg_replace_callback('/\{\{av_mariel(?::(xl))?\}\}/',function($m)use($ph){$x=!empty($m[1])?' xl':'';
  return $ph?'<img class="av'.$x.'" data-r="analyze" src="'.esc_url($ph).'" alt="Mariel Saldivar" width="240" height="240" loading="lazy">':'<span class="av mono'.$x.'" data-r="analyze" role="img" aria-label="Mariel Saldivar">MS</span>';},$h);
 if($em){$e=esc_attr($em);
  $r1='<div><dt>Email</dt><dd><a href="mailto:'.$e.'">'.esc_html($em).'</a></dd></div>';
  $r2='<div class="row"><span>Email</span><a href="mailto:'.$e.'">'.esc_html($em).'</a><button type="button" class="btn sm alt" data-copy="'.$e.'">Copy</button></div>';
  $o='<option value="'.$e.'" data-k="jonee">Jonee John Antiquina · Programmer, Designer &amp; Developer</option>';
 }else{$r1=$r2=$o='';}
 $h=str_replace(['{{jonee_email_row}}','{{jonee_email_row2}}','{{opt_jonee}}','{{opt_mariel}}','{{opt_diane}}'],[$r1,$r2,$o,'<option value="occ.saldivar.mariel@gmail.com" data-k="mariel">Mariel Saldivar · Project Analyst</option>','<option value="dianejvalle@gmail.com" data-k="diane">Diane Valle · Technical Writer</option>'],$h);
 $sc=trim(sva_opt('form_shortcode'));
 if($sc!=='')$h=preg_replace('/<!--SVAFORM-->.*?<!--\/SVAFORM-->/s',do_shortcode($sc),$h);
 return sva_links($h);
}

/* ---- internal links: always the page's real URL, so navigation works with plain OR pretty permalinks ---- */
function sva_page_id($slug){
 $m=get_option('sva_pages',[]);
 if(!empty($m[$slug])&&get_post_status($m[$slug])==='publish')return (int)$m[$slug];
 $x=get_page_by_path($slug,OBJECT,'page');return $x?(int)$x->ID:0;
}
function sva_url($slug){
 $id=sva_page_id($slug);
 if($slug==='home')return home_url('/');
 return $id?get_permalink($id):home_url('/');
}
function sva_links($h){
 $cur=is_page()?(int)get_queried_object_id():0;
 return preg_replace_callback('#href="/(home|team|about|skills|services|projects|experience|contact)?/?(\?[^"\#]*)?(\#[^"]*)?"#',function($m)use($cur){
  $slug=($m[1]??'')!==''?$m[1]:'home';$q=$m[2]??'';$hash=$m[3]??'';
  $u=esc_url(sva_url($slug));
  if($q!=='')$u.=(strpos($u,'?')!==false?'&amp;':'?').substr($q,1);
  $u.=$hash;
  $on=($q===''&&$hash===''&&$cur&&sva_page_id($slug)===$cur)?' aria-current="page"':'';
  return 'href="'.$u.'"'.$on;
 },$h);
}
function sva_is_page(){
 if(!is_page())return false;$id=get_queried_object_id();
 if(get_post_meta($id,'_salvalan',true))return true;
 $po=get_post($id);return $po&&isset(SVA_SLUGS[$po->post_name])&&strpos($po->post_content,'<main id="main">')!==false;
}
function sva_part($n){return sva_tokens(file_get_contents(SVA_DIR."parts/$n.html"));}

/* ---- render with our own template (bypasses theme header/footer) ---- */
add_filter('template_include',function($t){return sva_is_page()?SVA_DIR.'template.php':$t;},99);
add_action('wp_enqueue_scripts',function(){
 if(!sva_is_page())return;
 wp_enqueue_style('sva-fonts','https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap',[],null);
 wp_enqueue_style('sva',SVA_URL.'assets/css/sva.css',[],SVA_VER.'.'.filemtime(SVA_DIR.'assets/css/sva.css'));
 wp_enqueue_script('sva',SVA_URL.'assets/js/sva.js',[],SVA_VER.'.'.filemtime(SVA_DIR.'assets/js/sva.js'),true);
 wp_add_inline_script('sva','window.SVA={sw:"/salvalan-sw"};','before');
},99);
add_action('after_setup_theme',function(){add_theme_support('title-tag');});
const SVA_DESC=['home'=>'SalValAn: three graduating BSIT students covering analysis, development and documentation for one full project cycle.','team'=>'Meet Jonee Antiquina (developer), Mariel Saldivar (project analyst) and Diane Valle (technical writer).','about'=>'About the SalValAn team: our story, our mission and how we work.','skills'=>'Skills of the SalValAn team: Laravel, MySQL, system analysis, technical writing and more.','services'=>'Services: web application development, UI/UX design, system analysis, process analysis, technical documentation and proofreading.','projects'=>'ATMS (Alumni Tracing System) and the A & K Car Tint and Accessories Service system with OTP authentication.','experience'=>'Recent experience and education of the SalValAn team.','contact'=>'Contact Jonee, Mariel or Diane by email, phone or Facebook.'];
add_action('wp_head',function(){
 if(!sva_is_page())return;
 $po=get_post(get_queried_object_id());$d=$po&&isset(SVA_DESC[$po->post_name])?SVA_DESC[$po->post_name]:'';
 if($d)echo '<meta name="description" content="'.esc_attr($d).'"><meta property="og:description" content="'.esc_attr($d).'"><meta property="og:type" content="website"><meta property="og:site_name" content="SalValAn">';
},2);
add_action('wp_head',function(){
 if(!sva_is_page())return;
 echo '<meta name="theme-color" content="#F1F3F8"><link rel="manifest" href="/salvalan-manifest"><link rel="apple-touch-icon" href="'.esc_url(SVA_URL.'assets/img/apple-touch-icon.png').'"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-title" content="SalValAn"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
 echo '<script>(function(){try{var t=localStorage.getItem("sva-theme");if(!t)t=matchMedia("(prefers-color-scheme:dark)").matches?"dark":"light";document.documentElement.setAttribute("data-theme",t)}catch(e){}})()</script>';
},1);

/* ---- PWA: manifest, service worker, offline page (extension-less so any host serves them) ---- */
add_action('init',function(){
 $p=parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH);$b=rtrim(parse_url(home_url('/'),PHP_URL_PATH),'/');$p='/'.ltrim(substr($p,strlen($b)),'/');$p=rtrim($p,'/');
 if($p==='/salvalan-manifest'){
  header('Content-Type: application/manifest+json; charset=utf-8');header('Cache-Control: public, max-age=3600');
  $i=SVA_URL.'assets/img/';
  echo wp_json_encode(['id'=>'/','name'=>'SalValAn Portfolio','short_name'=>'SalValAn','description'=>'Build. Analyze. Document. The SalValAn team portfolio.','start_url'=>'/','scope'=>'/','display'=>'standalone','orientation'=>'any','background_color'=>'#F1F3F8','theme_color'=>'#F1F3F8','lang'=>'en',
   'icons'=>[['src'=>$i.'icon-192.png','sizes'=>'192x192','type'=>'image/png','purpose'=>'any'],['src'=>$i.'icon-512.png','sizes'=>'512x512','type'=>'image/png','purpose'=>'any'],['src'=>$i.'icon-maskable-512.png','sizes'=>'512x512','type'=>'image/png','purpose'=>'maskable']],
   'shortcuts'=>[['name'=>'Team','url'=>sva_url('team')],['name'=>'Projects','url'=>sva_url('projects')],['name'=>'Contact','url'=>sva_url('contact')]]],JSON_UNESCAPED_SLASHES);exit;}
 if($p==='/salvalan-sw'){
  header('Content-Type: application/javascript; charset=utf-8');header('Service-Worker-Allowed: /');header('Cache-Control: no-cache');
  $v=SVA_VER.'-'.filemtime(SVA_DIR.'assets/css/sva.css').filemtime(SVA_DIR.'assets/js/sva.js');
  $as=[SVA_URL.'assets/css/sva.css',SVA_URL.'assets/js/sva.js',SVA_URL.'assets/img/icon-192.png',SVA_URL.'assets/img/jonee.jpg',SVA_URL.'assets/img/diane.jpg'];
  $core=[];foreach(SVA_SLUGS as $s=>$t){$u=wp_parse_url(sva_url($s),PHP_URL_PATH);if($u&&strpos(sva_url($s),'?')===false)$core[]=$u;}
  echo "const V='sva-$v',CORE=".wp_json_encode(array_values(array_unique($core))).",ASSETS=".wp_json_encode($as).";\n";
  echo <<<'JS'
self.addEventListener('install',e=>{e.waitUntil(caches.open(V).then(c=>Promise.all(CORE.concat(ASSETS,['/salvalan-offline']).map(u=>c.add(u).catch(()=>{})))).then(()=>self.skipWaiting()))});
self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(k=>Promise.all(k.filter(x=>x!==V).map(x=>caches.delete(x)))).then(()=>self.clients.claim()))});
self.addEventListener('fetch',e=>{const r=e.request;if(r.method!=='GET')return;const u=new URL(r.url);const path=u.pathname.replace(/\/+$/,'')||'/';
if(/^\/(wp-admin|wp-login|wp-json)/.test(u.pathname))return;
if(r.mode==='navigate'){const ok=u.origin===location.origin&&CORE.indexOf(u.pathname.endsWith('/')?u.pathname:u.pathname+'/')>-1||path==='/';
e.respondWith(fetch(r).then(res=>{if(ok&&res.ok&&!u.search){const cp=res.clone();caches.open(V).then(c=>c.put(u.pathname,cp))}return res}).catch(()=>caches.match(u.pathname).then(m=>m||caches.match('/salvalan-offline'))));return}
if(u.origin===location.origin||/fonts\.(gstatic|googleapis)\.com$/.test(u.host)){e.respondWith(caches.match(r).then(m=>{const n=fetch(r).then(res=>{if(res.ok){const cp=res.clone();caches.open(V).then(c=>c.put(r,cp))}return res}).catch(()=>m);return m||n}))}});
JS;
  exit;}
 if($p==='/salvalan-offline'){header('Content-Type: text/html; charset=utf-8');
  echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline · SalValAn</title><body style="font:18px/1.6 DM Sans,Segoe UI,system-ui,sans-serif;background:#F1F3F8;color:#0E1A3A;display:grid;place-items:center;min-height:100vh;margin:0;text-align:center;padding:24px"><div><h1 style="margin:0 0 8px">You are offline</h1><p>Pages you have opened before still work. Reconnect to see the rest.</p><button onclick="location.reload()" style="font:700 1rem system-ui;padding:12px 24px;border:0;border-radius:10px;background:#0E1A3A;color:#fff">Try again</button></div>';exit;}
},1);

/* ---- create / refresh the 8 pages ---- */
function sva_seed(){
 $past=gmdate('Y-m-d H:i:s',time()-86400);$home=0;$map=[];
 foreach(SVA_SLUGS as $slug=>$title){
  $html=trim(file_get_contents(SVA_DIR."pages/$slug.html"));
  $post=['post_title'=>$title,'post_name'=>$slug,'post_type'=>'page','post_status'=>'publish','post_content'=>"<!-- wp:html -->\n$html\n<!-- /wp:html -->",'post_date'=>get_date_from_gmt($past),'post_date_gmt'=>$past,'comment_status'=>'closed'];
  $ex=get_page_by_path($slug,OBJECT,'page');
  if($ex){$post['ID']=$ex->ID;$id=wp_update_post($post);}else{$id=wp_insert_post($post);}
  if($id&&!is_wp_error($id)){$map[$slug]=(int)$id;update_post_meta($id,'_salvalan',1);delete_post_meta($id,'_elementor_data');delete_post_meta($id,'_elementor_edit_mode');if($slug==='home')$home=$id;}
 }
 update_option('sva_pages',$map);
 if($home){update_option('show_on_front','page');update_option('page_on_front',$home);}
 /* Plain permalinks make every /page/ URL show the front page. Switch to pretty ones. */
 if(get_option('permalink_structure')===''){global $wp_rewrite;$wp_rewrite->set_permalink_structure('/%postname%/');}
 flush_rewrite_rules(false);
 update_option('sva_ver',SVA_VER);
}
/* upload a newer version of the plugin and the pages refresh themselves once */
add_action('admin_init',function(){if(get_option('sva_ver')!==SVA_VER&&current_user_can('manage_options')){sva_seed();set_transient('sva_notice',1,300);}});
add_action('admin_notices',function(){if(!current_user_can('manage_options'))return;if(get_option('permalink_structure')==='')echo '<div class="notice notice-warning"><p><b>SalValAn:</b> permalinks are set to Plain. Go to Settings &gt; Permalinks and choose Post name. The site still works, but the address bar will show ?page_id=.</p></div>';});
register_activation_hook(__FILE__,function(){sva_seed();set_transient('sva_notice',1,300);});
add_action('admin_notices',function(){if(get_transient('sva_notice')){delete_transient('sva_notice');echo '<div class="notice notice-success is-dismissible"><p><b>SalValAn Portfolio is active.</b> All 8 pages were created and Home is your front page. Next: <a href="'.esc_url(admin_url('options-general.php?page=salvalan')).'">fill in your details</a>, then clear the Pantheon cache.</p></div>';}});
add_action('admin_post_sva_reseed',function(){if(!current_user_can('manage_options')||!check_admin_referer('sva_reseed'))wp_die('Not allowed');sva_seed();wp_safe_redirect(admin_url('options-general.php?page=salvalan&reseeded=1'));exit;});

/* ---- settings page ---- */
add_action('admin_menu',function(){add_options_page('SalValAn Portfolio','SalValAn','manage_options','salvalan','sva_settings');});
add_action('admin_init',function(){register_setting('sva','sva_opts',['sanitize_callback'=>function($i){$o=[];foreach(sva_fields() as $k=>$f){$v=isset($i[$k])?wp_unslash($i[$k]):'';$o[$k]=in_array($k,['team_story','mission'])?sanitize_textarea_field($v):($k==='mariel_photo'?esc_url_raw($v):($k==='jonee_email'?sanitize_email($v):($k==='atms_url'?esc_url_raw($v):sanitize_text_field($v))));}return $o;}]);});
add_action('admin_enqueue_scripts',function($h){if($h==='settings_page_salvalan')wp_enqueue_media();});
function sva_settings(){$o=get_option('sva_opts',[]);?>
<div class="wrap"><h1>SalValAn Portfolio</h1>
<?php if(isset($_GET['reseeded']))echo '<div class="notice notice-success"><p>Pages were reset to the original design.</p></div>';?>
<form method="post" action="options.php"><?php settings_fields('sva');?><table class="form-table">
<?php foreach(sva_fields() as $k=>$f):$v=$o[$k]??'';?>
<tr><th><label for="sva_<?php echo esc_attr($k);?>"><?php echo esc_html($f[0]);?></label></th><td>
<?php if(in_array($k,['team_story','mission'])):?><textarea class="large-text" rows="4" id="sva_<?php echo $k;?>" name="sva_opts[<?php echo $k;?>]" placeholder="<?php echo esc_attr($f[1]);?>"><?php echo esc_textarea($v);?></textarea>
<?php else:?><input class="regular-text" id="sva_<?php echo $k;?>" name="sva_opts[<?php echo $k;?>]" value="<?php echo esc_attr($v);?>" placeholder="<?php echo esc_attr($f[1]);?>">
<?php if($k==='mariel_photo'):?> <button type="button" class="button" id="sva_pick">Choose photo</button><?php endif;endif;?>
<p class="description"><?php echo esc_html($f[2]);?></p></td></tr><?php endforeach;?></table><?php submit_button();?></form>
<hr><h2>Reset pages</h2><p>Restores all 8 pages to the original design (your own edits to those pages will be replaced).</p>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php'));?>"><input type="hidden" name="action" value="sva_reseed"><?php wp_nonce_field('sva_reseed');?><?php submit_button('Reset all 8 pages','secondary','',false);?></form></div>
<script>document.getElementById('sva_pick')&&document.getElementById('sva_pick').addEventListener('click',function(){var f=wp.media({title:'Choose photo',multiple:false,library:{type:'image'}});f.on('select',function(){document.getElementById('sva_mariel_photo').value=f.state().get('selection').first().toJSON().url});f.open()});</script>
<?php }
