<?php if(!defined('ABSPATH'))exit;
the_post(); $sva=get_post_field('post_content',get_the_ID());
/* if the stored page is from an older plugin version, show the current design until wp-admin refreshes it */
if(get_post_meta(get_the_ID(),'_sva_ver',true)!==SVA_VER)$sva=sva_build_home();
$sva=preg_replace('/<!--\s*\/?wp:html\s*-->/','',$sva);
?><!doctype html>
<html <?php language_attributes();?>>
<head><meta charset="<?php bloginfo('charset');?>"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><?php wp_head();?></head>
<body <?php body_class('sva');?>>
<?php wp_body_open(); echo sva_part('header'); echo sva_tokens($sva); echo sva_part('footer'); wp_footer();?>
</body></html>
