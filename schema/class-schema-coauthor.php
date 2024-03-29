<?php

use Civi\Test\Schema;
use Yoast\WP\SEO\Config\Schema_IDs;
use Yoast\WP\SEO\Generators\Schema\Author;

/**
 * Returns schema Person data representing a co-author
 *
 * Extends Person rather than Author to avoid weird side effects with author pages, etc
 *
 * @property WPSEO_Schema_Context $context A value object with context variables.
 */
class YoastCoauthor_Schema_Coauthor extends Author {
	/**
	 * A value object with context variables.
	 *
	 * @var WPSEO_Schema_Context
	 */
	public $context;

	private $coauthor_user_id;
	private $coauthor;

	/**
	 * WPSEO_Schema_Breadcrumb constructor.
	 *
	 * @param WPSEO_Schema_Context $context A value object with context variables.
	 */
	public function __construct( WPSEO_Schema_Context $context, $userId, $coauthor ) {
		$this->context   = $context;
		$this->coauthor_user_id = $userId;
		$this->coauthor = $coauthor;
	}

	/**
	 * Determine whether we should return Person schema.
	 *
	 * @return bool
	 */
	public function is_needed() {
		if ( $this->is_post_author() ) {
			return true;
		}

		return false;
	}


	/**
	 * Builds our array of Schema Person data for a given user ID.
	 *
	 * @param int $user_id The user ID to use.
	 *
	 * @return array An array of Schema Person data.
	 */
	protected function build_person_data( $user_id ) {
		$data['@id'] = $this->context->site_url . "#/schema/coauthor/" . \wp_hash( $user_id );
		$data['@type'] = 'Person';
		

		$data['name'] = $this->helpers->schema->html->smart_strip_tags( $this->coauthor->display_name );
		$data         = $this->add_image( $data, $this->coauthor );

		if ( ! empty( $this->coauthor->description ) ) {
			$data['description'] = $this->helpers->schema->html->smart_strip_tags( $this->coauthor->description );
		}

		$data['url'] = get_author_posts_url( $user_id, $this->coauthor->user_nicename );

		if( $this->coauthor->website) {
			$data['sameAs'] = array(
				$this->coauthor->website 
			);
		}

		/**
		 * Filter: 'wpseo_schema_person_data' - Allows filtering of schema data per user.
		 *
		 * @param array $data    The schema data we have for this person.
		 * @param int   $user_id The current user we're collecting schema data for.
		 */
		$data = \apply_filters( 'wpseo_schema_person_data', $data, $user_id );
		return $data;
	}

	/**
	 * Determine whether the current URL is worthy of Article schema.
	 *
	 * @return bool
	 */
	protected function is_post_author() {
		/**
		 * Filter: 'wpseo_schema_article_post_type' - Allow changing for which post types we output Article schema.
		 *
		 * @api array $post_types The post types for which we output Article.
		 */
		$post_types = apply_filters( 'wpseo_schema_article_post_type', array( 'post' ) );
		if ( is_singular( $post_types ) ) {
			return true;
		}

		return false;
	}



	/**
	 * Determines a User ID for the Person data.
	 *
	 * @return bool|int User ID or false upon return.
	 */
	protected function determine_user_id() {
		return $this->coauthor_user_id;
	}

}
