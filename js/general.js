 jQuery(document).ready(function(){
    var jq=jQuery;
    jq("a[rel^='prettyPhoto']").prettyPhoto(); //incase you want to use pretty photo for other images too, otherwise pass anything as selecter, we need this call to make PrettyPhoto available for later call
    /*get the gallery Id from the dom element eg. <div id='gallery_xyz_32'> gives 32'*/
   
   
  });