<?php
class BP_Flickr_Template{

    var $user_id;
    var $current_photo=-1;
    var $photos_count;
    var $photos;
    var $photo;

    var $in_the_loop;
    var $pag_page;
    var $pag_num;
    var $pag_links;
    var $total_photo_count;

    var $flickr_url;
    var $count;
    //for feed
    var $title;
    var $description;
    var $url;
    var $image;
    var $guid;
    
   
 function  BP_Flickr_Template($user_nsid,$page=null, $per_page=null,$max=null){
   
     $this->pag_page = isset( $_REQUEST['frpage'] ) ? intval( $_REQUEST['frpage'] ) : $page;
     $this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;
    
     //flickr feed url
      $url="http://api.flickr.com/services/feeds/photos_public.gne?id=".$user_nsid."&lang=en-us&format=php_serial&jsoncallback=?";
           
       $resp=wp_remote_get($url);
        if(!is_wp_error($resp)){
                 $resp=wp_remote_retrieve_body($resp);   //
                 $flickr_info=maybe_unserialize($resp);
                 $this->photos=$flickr_info["items"];//array of photos
                 $this->title=$flickr_info["title"];//title of the feed
                 $this->url=$flickr_info["url"];
                 $this->description=$flickr_info["description"];
                 $this->total_photo_count=count($this->photos);
                  //handle pseudo pagination in the feed
                 $this->photos=array_slice($this->photos,($this->pag_page-1)*$this->pag_num,$this->pag_num);
               
                

                 if ( $max ) {
                     if ( $max >= count($this->photos) )
                            $this->photos_count = count($this->photos);
                    else
                            $this->photos_count = (int)$max;
                } else
                    $this->photos_count = count($this->photos);

             //handle pseudo pagination in the feed
                
                $this->pag_links = paginate_links( array(
                                'base' => add_query_arg( array( 'frpage' => '%#%', 'num' => $this->pag_num, 's' => $_REQUEST['s'] ) ),
                                'format' => '',
                                'total' => ceil($this->total_photo_count / $this->pag_num),
                                'current' => $this->pag_page,
                                'prev_text' => '&larr;',
                                'next_text' => '&rarr;',
                                'mid_size' => 1
                        ));
    }
    else
        $this->photos_count=0;///if error, let us not get inside the loop, make has_photos false
 }

    function has_photos(){
        if($this->photos_count)
                return true;
        return false;
    }

    function next_photo() {
                    $this->current_photo++;
                    $this->photo = $this->photos[$this->current_photo];

                    return $this->photo;
            }

    function rewind_photos() {
		$this->current_photo = -1;
		if ( $this->photos_count > 0 ) {
			$this->photo = $this->photos[0];
		}
	}

    function photos() {
		if ( $this->current_photo + 1 < $this->photos_count ) {
			return true;
		} elseif ( $this->current_photo + 1 == $this->photos_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_photos();
		}

		$this->in_the_loop = false;
		return false;
	}

    function the_photo() {
            $this->in_the_loop = true;
            $this->photo = $this->next_photo();

            if ( 0 == $this->current_photo ) // loop has just started
			do_action('loop_start');
	}



}
function bp_flickr_has_photos( $args = ''){
//initialize
  global $flickr_template;
   $defaults = array(
		'page' => 1,
		'per_page' => 9,
		'max' => false,
                'flickr_nsid'=>'',
                'user_id'=>''
		);

$r = wp_parse_args( $args, $defaults );
extract( $r );
if(empty($flickr_nsid))
   $flickr_nsid=bp_flickr_get_user_account($user_id);

if(!empty($flickr_nsid)){
    $flickr_template=new BP_Flickr_Template($flickr_nsid,$page,$per_page,$max);
    return apply_filters( 'bp_flickr_has_photos', $flickr_template->has_photos(), &$flickr_template );
}
else
    return false;

}

function bp_flickr_photos(){
    global $flickr_template;
	return $flickr_template->photos();
}

function bp_flickr_the_photo(){
     global $flickr_template;
	return $flickr_template->the_photo();
}

//pagination
function bp_flickr_pagination_links() {
	echo bp_flickr_get_pagination_links();
}
	function bp_flickr_get_pagination_links() {
		global $flickr_template;

		return apply_filters( 'bp_flickr_get_pagination_links', $flickr_template->pag_links );
	}

function bp_flickr_pagination_count() {
	global $bp, $flickr_template;

	$from_num = bp_core_number_format( intval( ( $flickr_template->pag_page - 1 ) * $flickr_template->pag_num ) + 1 );
	$to_num = bp_core_number_format( ( $from_num + ( $flickr_template->pag_num - 1 ) > $flickr_template->total_photo_count ) ? $flickr_template->total_photo_count : $from_num + ( $flickr_template->pag_num - 1 ) );
	$total = bp_core_number_format( $flickr_template->total_photo_count );

	echo sprintf( __( 'Viewing photo %s to %s (of %s photos)', 'bp-flickr' ), $from_num, $to_num, $total ); ?> &nbsp;
	<span class="ajax-loader"></span><?php
}
/** for feed */
function bp_flickr_account_link(){
   echo bp_flickr_get_account_link();
}
    function bp_flickr_get_account_link(){
        global $flickr_template;
        return $flickr_template->url;
    }
function bp_flickr_feed_title(){
    echo  bp_flickr_get_feed_title();
}
    function bp_flickr_get_feed_title(){
        global $flickr_template;
        return $flickr_template->title;
    }
function bp_flickr_feed_description(){
    echo bp_flickr_get_feed_description();
}
    function bp_flickr_get_feed_description(){
        global $flickr_template;
        return $flickr_template->description;
    }
function bp_flickr_feed_pub_date(){
    echo bp_flickr_get_feed_pub_date();
}
    function bp_flickr_get_feed_pub_date(){
        global $flickr_template;
    }
/**
 * echo the src of feed owner's image/avatar
 */
function bp_flickr_feed_owner_photo(){
    
}
/*for photos in the feed template tags*/




function bp_flickr_photo_title(){
    echo bp_flickr_get_photo_title();
}
    function bp_flickr_get_photo_title(){
        return bp_flickr_get_photo_data("title");
    }
function bp_flickr_photo_description(){
    echo bp_flickr_get_photo_description();
}
    function bp_flickr_get_photo_description(){
        return bp_flickr_get_photo_data("description");
    }
function bp_flickr_photo_description_raw(){
    echo bp_flickr_get_photo_description_raw();
}
    function bp_flickr_get_photo_description_raw(){
        return bp_flickr_get_photo_data("description_raw");
    }


/*
 * print Link of Photo on Flickr
 */
function bp_flickr_photo_url(){
    echo bp_flickr_get_photo_url();
}
    function bp_flickr_get_photo_url(){
        return bp_flickr_get_photo_data("url");
    }
function bp_flickr_photo_mid_src(){
    echo bp_flickr_get_photo_mid_src();
}
    function bp_flickr_get_photo_mid_src(){
        return bp_flickr_get_photo_data("m_url");
    }

function bp_flickr_photo_thumb_src(){
    echo bp_flickr_get_photo_thumb_src();
}
    function bp_flickr_get_photo_thumb_src(){
        return bp_flickr_get_photo_data("t_url");
    }
function bp_flickr_photo_large_src(){
    echo bp_flickr_get_photo_large_src();
}
    function bp_flickr_get_photo_large_src(){
        return bp_flickr_get_photo_data("l_url");
    }
function bp_flickr_photo_date_taken(){
    echo bp_flickr_get_photo_date_taken();
}
    function bp_flickr_get_photo_date_taken(){
        return bp_flickr_get_photo_data("date_taken");
    }
function bp_flickr_photo_date_taken_nice(){
    echo bp_flickr_get_photo_date_taken_nice();
}
    function bp_flickr_get_photo_date_taken_nice(){
        return bp_flickr_get_photo_data("date_taken_nice");
    }
function bp_flickr_photo_guid(){
    echo bp_flickr_get_photo_guid();
}
    function bp_flickr_get_photo_guid(){
        return bp_flickr_get_photo_data("guid");
    }
function bp_flickr_author_name(){
    echo bp_flickr_get_author_name();
}
    function bp_flickr_get_author_name(){
        return bp_flickr_get_photo_data("author_name");
    }
function bp_flickr_photo_author_url(){
    echo bp_flickr_get_photo_author_url();
}
    function bp_flickr_get_photo_author_url(){
        return bp_flickr_get_photo_data("author_url");
    }
function bp_flickr_photo_author_nsid(){
    echo bp_flickr_get_photo_author_nsid();
}
    function bp_flickr_get_photo_author_nsid(){
        return bp_flickr_get_photo_data("nsid");
    }
function bp_flickr_photo_author_icon(){
    echo bp_flickr_get_photo_author_icon();
}
    function bp_flickr_get_photo_author_icon(){
        return bp_flickr_get_photo_data("author_icon");
    }



function bp_flickr_photo_tags(){
    echo bp_flickr_get_photo_tags();
}
    function bp_flickr_get_photo_tags(){
        return bp_flickr_get_photo_data("tags");
    }
function bp_flickr_photo_get_tags_as_array(){
        return bp_flickr_get_photo_data("tags_array");
    
}
function bp_flickr_photo_mime(){
    echo bp_flickr_get_photo_mime();
}
    function bp_flickr_get_photo_mime(){
            return bp_flickr_get_photo_data("photo_mime");

    }
function bp_flickr_photo_license(){
    echo bp_flickr_get_photo_license();
}
    function bp_flickr_get_photo_license(){
            return bp_flickr_get_photo_data("photo_license");
    
    }
//helper


function bp_flickr_get_photo_data($key){
    global $flickr_template;
    $photo=$flickr_template->photo;
    
    if(array_key_exists($key,$photo))
            return $photo[$key];
    return '';
}


//conditional tag

function bp_flickr_is_home(){
    global $bp;
    if($bp->current_component==$bp->flickr->slug&&$bp->current_action=="my-flickr")
            return true;
    return false;
}

function bp_flickr_is_settings(){
    global $bp;
    if($bp->current_component==$bp->flickr->slug&&$bp->current_action=="settings")
            return true;
    return false;
}
?>