<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Block non-Admin staff from legacy CI pages (bookmarked URLs).
 * Admin keeps full legacy access. APIs / login / IPN stay exempt.
 */
class Pos_legacy_gate
{
	public function enforce()
	{
		pos_force_non_admin_to_spa();
	}
}
