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
* Version: 0.0.3
* Author: Harry J.E Day
* Author URI: https://github.com/hjed
* Depends: Yoast SEO, Co-Authors Plus
*/

use Yoast\WP\SEO\Config\Schema_IDs;
use Yoast\WP\SEO\Generators\Schema\Author;
use Yoast\WP\SEO\Generators\Schema\Organization;

add_filter( 'wpseo_schema_article', 'yoastcoauthor_add_coauthors_to_article' );
add_filter( 'wpseo_schema_webpage', 'yoastcoauthor_add_coauthors_to_article' );
add_filter( 'wpseo_schema_graph_pieces', 'yoastcoauthor_add_coauthors_to_graph', 10, 2 );

function yoastcoauthor_add_coauthors_to_graph( $pieces, $context ) {

  // don't change author pages
  if(is_author()) {
    return $pieces;
  }

  if( function_exists("get_coauthors") && !is_author()) {
    $authors = get_coauthors();
    $wp_author = get_the_author_meta('ID');
    foreach ($authors as $author) {
      // don't re add the author that wordpress recognises
      if($author->ID != $wp_author) {
        if ($author->type === 'guest-author') {
          if ( $author->website === get_site_url() ) {
            // checky hack to support organisations
            $pieces[] = new YoastCoauthor_Schema_Coauthor_Organization();
          } else {
            $pieces[] = new YoastCoauthor_Schema_Coauthor($context, $author->ID, $author);
          }
        } else {
            $pieces[] = new YoastCoauthor_Schema_Real_Coauthor($context, $author->ID);
        }
        
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

    $authors = get_coauthors();
    foreach ($authors as $author) {
      if ($author->type === 'guest-author') {
        if ( $author->website === get_site_url() ) {
          // checky hack to support organisations
          $data['author'][] = array(
             '@id' => get_site_url() . '/' . Schema_IDs::ORGANIZATION_HASH
          );
        } else {
          $data['author'][] = yoastcoauthor_get_author_article_json($author->ID);
        }
      } else {
        $data['author'][] = array(
          '@id' => get_site_url() . '/' . Schema_IDs::PERSON_HASH . \wp_hash( $author->user_login . $author->ID )
        );
      }
    }
  }

  return $data;
}

function yoastcoauthor_get_author_article_json($authorId) {
  return array(
    '@id'  => get_site_url() . "/#/schema/coauthor/" . \wp_hash( $authorId )
  );
}

require_once dirname(__FILE__) . '/schema/class-schema-coauthor.php';
require_once dirname(__FILE__) . '/schema/class-schema-real-coauthor.php';
require_once dirname(__FILE__) . '/schema/class-schema-coauthor-org.php';

