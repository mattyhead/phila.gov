<?php


if ( class_exists( "Phila_Gov_Publish_Webhook" ) ){
  $phila_publish_webhook = new Phila_Gov_Publish_Webhook();
}

class Phila_Gov_Publish_Webhook {

  public function __construct(){
    add_action( 'publish_future_post', 'send_published_url' );
    add_action( 'post_updated', 'send_published_url', 10, 3 );
  }

  function send_published_url( $post_id ) {
    

  }


}

?>
