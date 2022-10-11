<?php

namespace Hametuha\Sitemap\Utility;


use Hametuha\Sitemap\Setting;

/**
 * Utilityt for option getter.
 */
trait OptionAccessor {

	/**
	 * Get option.
	 *
	 * @return Setting
	 */
	public function option() {
		return Setting::get_instance();
	}
}
