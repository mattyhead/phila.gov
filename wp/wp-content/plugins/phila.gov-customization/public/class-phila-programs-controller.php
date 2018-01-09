<?php

class Phila_Programs_Controller {

  // Initialize the namespace and resource name.
  public function __construct() {
    $this->namespace     = 'programs/v1';
    $this->resource_name = 'archives';
    //$this->category_resource = 'categories';
    $this->audience_resource = 'audience';
    $this->service_resource = 'service';
  }

  // Register our routes.
  public function register_routes() {
  // Register the endpoint for collections.
    register_rest_route( $this->namespace, '/' . $this->resource_name, array(
      array(
        'methods'   => WP_REST_Server::READABLE,
        'callback'  => array( $this, 'get_items' ),
      ),
      'schema' => array( $this, 'get_item_schema' ),
    ) );

    //Register individual items
    register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
      array(
        'methods'   => WP_REST_Server::READABLE,
        'callback'  => array( $this, 'get_item' ),
      ),
      'schema' => array( $this, 'get_item_schema' ),
    ) );

    //Register individual items
    // register_rest_route( $this->namespace, '/' . $this->category_resource, array(
    //   array(
    //     'methods'   => WP_REST_Server::READABLE,
    //     'callback'  => array( $this, 'get_categories' ),
    //   ),
    //   'schema' => array( $this, 'get_category_schema' ),
    // ) );
  }

  public function set_query_defaults($request){

    $search = sanitize_text_field( $request['s'] );

    $args = array(
      'count_total' => false,
      'search' => sprintf( '*%s*', $search ),
      'search_fields' => array(
        'display_name',
        'user_login',
      ),
      'fields' => 'ID',
    );

    $matching_users = get_users( $args );

    // Don't modify the query if there aren't any matching users
    if ( empty( $matching_users ) ) {
      $query_defaults = array(
        'posts_per_page' => $request['count'],
        's' => $request['s'],
        'order' => 'desc',
        'orderby' => 'date',
        'category' => $request['category'],
      );
    }else {
      $query_defaults = array(
        'posts_per_page' => $request['count'],
        'author__in' => $matching_users,
        'order' => 'desc',
        'orderby' => 'date',
        'category' => $request['category'],
      );
    }

    return $query_defaults;
  }

  /**
   * Get the 40 latest posts within the "archives" umbrella
   *
   * @param WP_REST_Request $request Current request.
  */
  public function get_items( $request ) {
    $post_type = isset( $request['post_type'] ) ? array( $request['post_type']) : array('programs');

    $count = isset( $request['count'] ) ? $request['count'] : '40';

    $args = array(
      'post_type' => $post_type,
    );
    $query_defaults = $this->set_query_defaults($request);
    $args = array_merge($query_defaults, $args);

    $posts = get_posts( $args );


    $data = array();

    if ( empty( $posts ) ) {
      return rest_ensure_response( $data );
    }

    foreach ( $posts as $post ) {
      $response = $this->prepare_item_for_response( $post, $request );

      $data[] = $this ->prepare_response_for_collection( $response );
    }

    // Return all response data.
    return rest_ensure_response( $data );
  }

  /**
   * Outputs category data
   *
   * @param WP_REST_Request $request Current request.
   */
   // public function get_categories( $request ){
   //
   //  $categories = get_categories( array( 'parent' => 0 ) );
   //
   //  $data = array();
   //
   //  if ( empty( $categories ) ) {
   //    return rest_ensure_response( $array() );
   //  }
   //
   //  foreach ( $categories as $category ) {
   //    $response = $this->prepare_category_for_response( $category, $request );
   //
   //    $data[] = $this ->prepare_response_for_collection( $response );
   //  }
   //
   //  // Return all response data.
   //  return rest_ensure_response( $data );
   //
   // }

  /**
   * Outputs an individual item's data
   *
   * @param WP_REST_Request $request Current request.
   */
  public function get_item( $request ) {
    $id = (int) $request['id'];
    $post = get_post( $id );

    if ( empty( $post ) ) {
      return rest_ensure_response( array() );
    }

    $response = $this->prepare_item_for_response( $post, $request );

    return $response;
  }

  /**
   * Matches the post data to the schema. Also, rename the fields to nicer names.
   *
   * @param WP_Post $post The comment object whose response is being prepared.
   */

  public function prepare_item_for_response( $post, $request ) {
    $post_data = array();

    $schema = $this->get_item_schema( $request );

    if ( isset( $schema['properties']['id'] ) ) {
        $post_data['id'] = (int) $post->ID;
    }

    if (isset( $schema['properties']['title'] )) {
      $post_data['title']  =  (string) html_entity_decode($post->post_title);
    }

    if (isset( $schema['properties']['template'] )) {
      $post_data['template']  = (string) phila_get_selected_template($post->ID);
    }

    if (isset( $schema['properties']['link'] )) {
        $post_data['link']  = (string) get_permalink($post->ID);
    }

    if (isset( $schema['properties']['categories'] )) {
      $categories = get_the_category($post->ID);

      foreach ($categories as $category){
          $trimmed_name = phila_get_department_homepage_typography( null, $return_stripped = true, $page_title = $category->name );

          $category->slang_name = html_entity_decode(trim($trimmed_name));
      }

      $post_data['categories']  = (array) $categories;
    }

    if (isset( $schema['properties']['audiences'] )) {
      $audiences = get_the_terms($post->ID, 'audience');

      $post_data['audiences']  = (array) $audiences;
    }

    if (isset( $schema['properties']['services'] )) {
      $services = get_the_terms($post->ID, 'service_type');

      $post_data['services']  = (array) $services;
    }

    if (isset( $schema['properties']['image'] )) {
      $img = rwmb_meta( 'prog_header_img', array( 'limit' => 1 ), $post->ID );
      $img = reset($img);
      $medium_image = substr_replace($img['full_url'], '-768x432.jpg', -4, 4);

      $post_data['image']  = (string) $medium_image;
    }

    if (isset( $schema['properties']['external_link'] )) {
      $link = rwmb_meta( 'prog_off_site_link', array(), $post->ID );

      $post_data['external_link']  = (string) $link;
    }

    return rest_ensure_response( $post_data );
}

  /**
   * Prepare a response for inserting into a collection of responses.
   *
   * This is copied from WP_REST_Controller class in the WP REST API v2 plugin.
   *
   * @param WP_REST_Response $response Response object.
   * @return array Response data, ready for insertion into collection data.
   */
  public function prepare_response_for_collection( $response ) {
    if ( ! ( $response instanceof WP_REST_Response ) ) {
      return $response;
    }

    $data = (array) $response->get_data();
    $server = rest_get_server();

    if ( method_exists( $server, 'get_compact_response_links' ) ) {
      $links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
    } else {
      $links = call_user_func( array( $server, 'get_response_links' ), $response );
    }

    if ( ! empty( $links ) ) {
      $data['_links'] = $links;
    }

    return $data;
  }

  /**
   * Get sample schema for a collection.
   *
   * @param WP_REST_Request $request Current request.
   */
  public function get_item_schema( $request ) {
    $schema = array(
      // This tells the spec of JSON Schema we are using which is draft 4.
      '$schema'              => 'http://json-schema.org/draft-04/schema#',
      // The title property marks the identity of the resource.
      'title'                => 'post',
      'type'                 => 'object',
      // Specify object properties in the properties attribute.
      'properties'           => array(
        'id' => array(
          'description'  => esc_html__( 'Unique identifier for the object.', 'phila-gov' ),
          'type'         => 'integer',
          'context'      => array( 'view', 'edit', 'embed' ),
          'readonly'     => true,
        ),
        'title'=> array(
          'description'  => esc_html__( 'Title of the object.', 'phila-gov' ),
          'type'         => 'string',
          'readonly'     => true,
        ),
        'template'  => array(
          'description' => esc_html__('The template this object is using.', 'phila-gov'),
          'type'  => 'string',
        ),
        'link'  => array(
          'description' => esc_html__('The permalink for this object.', 'phila-gov'),
          'type'  => 'string',
        ),
        'categories'  => array(
          'description' => esc_html__('The categories assigned to this object.', 'phila-gov'),
          'type'  => 'array',
        ),
        'audiences'  => array(
          'description' => esc_html__('The audience taxonomy assigned to this object.', 'phila-gov'),
          'type'  => 'array',
        ),
        'services'  => array(
          'description' => esc_html__('The service category assigned to this object.', 'phila-gov'),
          'type'  => 'array',
        ),
        'image'  => array(
          'description' => esc_html__('The medium size image associated with this program.', 'phila-gov'),
          'type'  => 'string',
        ),
        'external_link'  => array(
          'description' => esc_html__('Link to this program if it is not part of this website.', 'phila-gov'),
          'type'  => 'string',
        ),
      ),
    );

    return $schema;
  }

  /**
   * Matches the post data to the schema. Also, rename the fields to nicer names.
   *
   * @param WP_Post $post The comment object whose response is being prepared.
   */

  // public function prepare_category_for_response( $category, $request ) {
  //
  //   $post_data = array();
  //
  //   $schema = $this->get_category_schema( $request );
  //
  //   if ( isset( $schema['properties']['id'] ) ) {
  //       $post_data['id'] = (int) $category->term_id;
  //   }
  //
  //   if (isset( $schema['properties']['name'] )) {
  //     $post_data['name']  =  (string) html_entity_decode($category->name);
  //   }
  //
  //   if (isset( $schema['properties']['slug'] )) {
  //     $post_data['slug']  =  (string) $category->slug;
  //   }
  //
  //   if (isset( $schema['properties']['slang_name'] )) {
  //
  //     $trimmed_name = phila_get_department_homepage_typography( null, $return_stripped = true, $page_title = $category->name );
  //
  //     $post_data['slang_name']  = (string) html_entity_decode(trim($trimmed_name));
  //   }
  //
  //   return rest_ensure_response( $post_data );
  //
  // }

  /**
   * Get sample schema for a category.
   *
   * @param WP_REST_Request $request Current request.
   */
  // public function get_category_schema( $request ) {
  //
  //   $schema = array(
  //     // This tells the spec of JSON Schema we are using which is draft 4.
  //     '$schema'              => 'http://json-schema.org/draft-04/schema#',
  //     // The title property marks the identity of the resource.
  //     'title'                => 'post',
  //     'type'                 => 'object',
  //     // Specify object properties in the properties attribute.
  //     'properties'           => array(
  //       'id' => array(
  //         'description'  => esc_html__( 'Unique identifier for the object.', 'phila-gov' ),
  //         'type'         => 'integer',
  //         'context'      => array( 'view', 'edit', 'embed' ),
  //         'readonly'     => true,
  //       ),
  //       'name'=> array(
  //         'description'  => esc_html__( 'Name of the object.', 'phila-gov' ),
  //         'type'         => 'string',
  //         'readonly'     => true,
  //       ),
  //       'slug'=> array(
  //         'description'  => esc_html__( 'Slug of the object.', 'phila-gov' ),
  //         'type'         => 'string',
  //         'readonly'     => true,
  //       ),
  //       'slang_name'=> array(
  //         'description'  => esc_html__( 'Slang name of the object.', 'phila-gov' ),
  //         'type'         => 'string',
  //         'readonly'     => true,
  //       ),
  //     ),
  //   );
  //
  //   return $schema;
  //
  // }

}

// Function to register our new routes from the controller.
function phila_register_programs_rest_routes() {
  $controller = new Phila_Programs_Controller();
  $controller->register_routes();
}

add_action( 'rest_api_init', 'phila_register_programs_rest_routes' );
