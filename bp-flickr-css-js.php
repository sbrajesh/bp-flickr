<?php

//include css
add_action("wp_print_styles","bp_flickr_enqueue_css");
//include js
add_action("wp_print_scripts","bp_flickr_enqueue_js");//for lightbox,pretty photo


function bp_flickr_enqueue_js(){

    wp_enqueue_script("jquery");
    if(!is_admin()){
    wp_enqueue_script("prettyphoto",BP_FLICKR_PLUGIN_URL."js/jquery.prettyPhoto.js");
	if(function_exists("pg_load_script"))
	return;
    wp_enqueue_script("bp-flickr-js",BP_FLICKR_PLUGIN_URL."js/general.js",array("jquery"));
}
}


//load css
function bp_flickr_enqueue_css(){
    global $bp;
    if(!bp_is_current_component($bp->flickr->slug))
            return;
    $template_name='/flickr/style.css';
    wp_enqueue_style("pgcss",BP_FLICKR_PLUGIN_URL."css/prettyPhoto.css");
     if ( file_exists(STYLESHEETPATH .$template_name))
            $theme_uri=get_stylesheet_directory_uri();//child theme
    else if ( file_exists(TEMPLATEPATH . $template_name) )
	    $theme_uri=get_template_directory_uri();//parent theme

    if(!empty($theme_uri)){
       
        $stylesheet_uri=$theme_uri.$template_name;
        wp_enqueue_style("flickrcss", $stylesheet_uri);
    }
}
?>