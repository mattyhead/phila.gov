<?php
/**
 * Register templates for use on the front-end
 *
 */

if ( class_exists( "Phila_Gov_Program_Templates" ) ){
  $admin_menu_labels = new Phila_Gov_Program_Templates();
}

class Phila_Gov_Program_Templates {

  public function __construct(){

    add_filter( 'rwmb_meta_boxes', array( $this, 'register_template_selection_metabox_programs'), 10, 1 );

  }

  function register_template_selection_metabox_programs( $meta_boxes ){

   $meta_boxes[] = array(
     'id'       => 'template_selection',
     'title'    => 'Select Template',
     'pages'    => array( 'programs' ),
     'context'  => 'advanced',
     'priority' => 'high',

     'fields' => array(
       array(
         'desc'  => '',
         'id'    => 'phila_template_select',
         'type'  => 'select',
         'class' => 'template-select',
         'clone' => false,
         'placeholder'  => 'Select a template',
         'required' => true,

         'options' => array(
           'prog_landing_page'  => 'Homepage',
           'prog_subpage'    => 'Subpage',
           ),
           'admin_columns' => array(
             'position' => 'after date',
             'title'    => __( 'Template' ),
             'sort'     => true,
           ),
        ),
     ),
   );

   $meta_boxes[] = array(
     'id'       => 'phila_program',
     'title'    => 'Page Content',
     'pages' => array( 'programs' ),
     'priority' => 'high',
     'revision' => true,

     'visible'  => array('phila_template_select', '=', 'prog_landing_page'),

     'fields' => array(
       Phila_Gov_Row_Metaboxes::phila_metabox_grid_row(),
     )
   );

    return $meta_boxes;
  }



 }
