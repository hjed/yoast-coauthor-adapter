
<?php

use Yoast\WP\SEO\Generators\Schema\Author;
use Yoast\WP\SEO\Generators\Schema\Organization;

/**
 * Returns schema Organization data representing a co-author
 *
 */
class YoastCoauthor_Schema_Coauthor_Organization extends Organization {

	/**
	 * Determines whether an Organization graph piece should be added.
	 *
	 * @return bool
	 */
	public function is_needed() {
		return $this->context->site_represents !== 'company';
	}

}