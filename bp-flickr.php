<?php
/*
 * Plugin Name: BP-Flickr
 * Author: Brajesh Singh
 * Plugin URI:http://buddydev.com/plugins/bp-flickr/
 * Author URI:http://buddydev.com/members/sbrajesh
 * Description: allow users to show their latest flickr images on their profile
 * Version: 1.1.2
 * Tested with WordPress 3.2.1+buddypress 1.5 beta3
 * License: GPL
 * last Update:September 3, 2011
 * 
 */

define("BP_FLICKR_PLUGIN_NAME","bp-flickr");
define("BP_FLICKR_SLUG","flickr");

$bp_flickr_dir =str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
define("BP_FLICKR_DIR_NAME",$bp_flickr_dir);//the directory name of bp-flickr
define("BP_FLICKR_PLUGIN_DIR",WP_PLUGIN_DIR."/".BP_FLICKR_DIR_NAME);
define("BP_FLICKR_PLUGIN_URL",WP_PLUGIN_URL."/".BP_FLICKR_DIR_NAME);


include_once(BP_FLICKR_PLUGIN_DIR."bp-flickr-template-tags.php");
include_once(BP_FLICKR_PLUGIN_DIR."bp-flickr-css-js.php");


/*
 * Localization support
 * Put your files into
 * bp-flickr/languages/bp-flickr-your_local.mo
 */
function bp_flickr_load_textdomain() {
        $locale = apply_filters( 'bp_flickr_load_textdomain_get_locale', get_locale() );
	// if load .mo file
	if ( !empty( $locale ) ) {
		$mofile_default = sprintf( '%s/languages/%s-%s.mo', BP_FLICKR_PLUGIN_DIR, BP_FLICKR_PLUGIN_NAME, $locale );
		$mofile = apply_filters( 'bp_gallery_load_textdomain_mofile', $mofile_default );
		// make sure file exists, and load it
		if ( file_exists( $mofile ) ) {
			load_textdomain( BP_FLICKR_PLUGIN_NAME, $mofile );
		}
	}
}
add_action ( 'bp_init', 'bp_flickr_load_textdomain', 2 );

function bp_flickr_setup_globals(){
    global $bp;
    $bp->flickr->id="flickr";
    $bp->flickr->slug=BP_FLICKR_SLUG;
    $bp->active_components[$bp->flickr->slug] = $bp->flickr->id;
    do_action( 'flickr_setup_globals' );
}
add_action("bp_init","bp_flickr_setup_globals",6);
//setup user navigation

function bp_flickr_setup_nav(){
 global $bp;
 if(bp_is_current_component($bp->flickr->slug)){
 //get the displayed user info
 


 }
 bp_core_new_nav_item( array( 'name' =>  sprintf( __( 'Flickr', 'bp-flickr' )), 'slug' => $bp->flickr->slug, 'position' => 180, 'screen_function' => 'bp_flickr_screen_home', 'default_subnav_slug' => 'my-flickr', 'item_css_id' => $bp->flickr->id ) );
     $flickr_link = $bp->loggedin_user->domain . $bp->flickr->slug . '/';

    /* Add the subnav items to the gallery nav item */
    bp_core_new_subnav_item( array( 'name' => __( 'My Flickr', 'bp-flickr' ), 'slug' => 'my-flickr', 'parent_url' => $flickr_link, 'parent_slug' => $bp->flickr->slug, 'screen_function' => 'bp_flickr_screen_home', 'position' => 10, 'item_css_id' => 'flickr-my-flickr' ) );
    bp_core_new_subnav_item( array( 'name' => __( 'Settings', 'bp-flickr' ), 'slug' => 'settings', 'parent_url' => $flickr_link, 'parent_slug' => $bp->flickr->slug, 'screen_function' => 'bp_flickr_screen_settings_user', 'position' => 20, 'user_has_access' => bp_is_my_profile() ) );
   
     do_action( 'flickr_setup_nav');
    
}
add_action("bp_init","bp_flickr_setup_nav",7);
//for home screen
function bp_flickr_screen_home(){
    //catch the home screen of bp-flickr
    global $bp;
    do_action( 'bp_flickr_screen_home' );
    if(bp_is_current_component($bp->flickr->slug)){
        $bp->flickr->is_home=true;
    }
    if($bp->current_action=="my-flickr")
        bp_core_load_template( apply_filters( 'template_flickr_my_flickr', 'flickr/index' ) );
      
    
}

//for settings screen
function bp_flickr_screen_settings_user(){
global $bp;
if(bp_is_current_component($bp->flickr->slug)&&$bp->current_action=="settings"){

    //settings screen
    if(!empty($_POST['save_settings'])){
        //store in user meta
        check_admin_referer("flickr_settings");
        if(empty($_POST['flickr_account']))
            bp_core_add_message(__("You must enter your flickr account in order to display photos here.",'bp-flickr'),'error');
        else{
            update_user_meta($bp->loggedin_user->id, "flickr_account", $_POST["flickr_account"]);
       
            bp_core_add_message(__("Account updated.",'bp-flickr'));
        }
    }
bp_core_load_template(apply_filters('template_flickr_settings','flickr/index'));
}

}


/*get flickr nsid of user from user meta*/
function bp_flickr_get_user_account($user_id=null){
    global $bp;
    if(empty($user_id))
        $user_id=$bp->displayed_user->id;
    return get_user_meta($user_id,"flickr_account",true);
}



?>