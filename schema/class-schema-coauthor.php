<?php
/**
 * Returns schema Person data representing a co-author
 *
 * Extends Person rather than Author to avoid weird side effects with author pages, etc
 *
 * @property WPSEO_Schema_Context $context A value object with context variables.
 */
class YoastCoauthor_Schema_Coauthor extends WPSEO_Schema_Person implements WPSEO_Graph_Piece {
	/**
	 * A value object with context variables.
	 *
	 * @var WPSEO_Schema_Context
	 */
	private $context;

	private $coauthor_user_id;

	/**
	 * WPSEO_Schema_Breadcrumb constructor.
	 *
	 * @param WPSEO_Schema_Context $context A value object with context variables.
	 */
	public function __construct( WPSEO_Schema_Context $context, $userId ) {
		parent::__construct( $context );
		$this->context   = $context;
		$this->logo_hash = $userId + '_' . WPSEO_Schema_IDs::AUTHOR_LOGO_HASH;
		$this->coauthor_user_id = $userId;
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
		$data = parent::build_person_data( $user_id );
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
		/**
		 * Filter: 'wpseo_schema_person_user_id' - Allows filtering of user ID used for person output.
		 *
		 * @api int|bool $user_id The user ID currently determined.
		 */
		return apply_filters( 'wpseo_schema_person_user_id', $this->coauthor_user_id );
	}

	/**
	 * Returns the string to use in Schema's `@id`.
	 *
	 * @param int $user_id The user ID if we're on a user page.
	 *
	 * @return string The `@id` string value.
	 */
	protected function determine_schema_id( $user_id ) {
		return get_author_posts_url( $user_id ) . WPSEO_Schema_IDs::AUTHOR_HASH;
	}

  /**
   * Returns an ImageObject for the persons avatar.
   * We overide this to ensure the url is unique
   *
   * @param array    $data      The Person schema.
   * @param \WP_User $user_data User data.
   *
   * @return array $data The Person schema.
   */
  protected function add_image( $data, $user_data ) {
    if ( ! get_avatar_url( $user_data->user_email ) ) {
      return $data;
    }

    $data['image'] = array(
      '@type'   => 'ImageObject',
      '@id'     => get_author_posts_url( $user_data->ID ) .  WPSEO_Schema_IDs::PERSON_LOGO_HASH,
      'url'     => get_avatar_url( $user_data->user_email ),
      'caption' => $user_data->display_name,
    );

    return $data;
  }
}
