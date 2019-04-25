<?php
/**
* @package     Yoast_Coauthor_Adapter
* @author      Harry J.E Day
* @copyright   2019 Harry J.E Day
* @license     GPL-2.0+
*
* @wordpress-plugin
* Plugin Name: Yoast Coauthor Adapter
* Plugin URI:
* Description: Makes Yoast SEO schema.org meta data play nicely with the co-author plus plugin
* Version: 0.0.1
* Author: Harry J.E Day
* Author URI: https://github.com/hjed
* Depends: Yoast SEO, Co-Authors Plus
*/
add_filter( 'wpseo_schema_article', 'yoastcoauthor_add_coauthors_to_article' );
add_filter( 'wpseo_schema_graph_pieces', 'yoastcoauthor_add_coauthors_to_graph', 10, 2 );

function yoastcoauthor_add_coauthors_to_graph( $pieces, $context ) {

  // don't change author pages
  if(is_author()) {
    return $pieces;
  }

  if( function_exists("get_coauthors") && !is_author()) {
    $authors = get_the_coauthor_meta('ID');
    $wp_author = get_the_author_meta('ID');
    foreach ($authors as $author) {
      // don't re add the author that wordpress recognises
      if($author != $wp_author) {
        $pieces[] = new YoastCoauthor_Schema_Coauthor($context, $author);
      }
    }
  }
  return $pieces;
}

function yoastcoauthor_add_coauthors_to_article($data) {

  // don't change author pages
  if(is_author()) {
    return $data;
  }

  if( function_exists("get_coauthors") && !is_author()) {
    $data['author'] = array();
    $authors = get_the_coauthor_meta('ID');
    foreach ($authors as $author) {
      $data['author'][] = yoastcoauthor_get_author_article_json($author);
    }
  }

  return $data;
}

function yoastcoauthor_get_author_article_json($authorId) {
  return array(
    '@id'  => get_author_posts_url( $authorId ) . WPSEO_Schema_IDs::AUTHOR_HASH,
    'name' => get_the_author_meta( 'display_name', $authorId ),
  );
}

require_once dirname(__FILE__) . '/schema/class-schema-coauthor.php';

