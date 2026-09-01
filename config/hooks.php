<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| Non-Admin staff are forced onto the new POS portal for all legacy UI routes.
| See application/hooks/Pos_legacy_gate.php
|
*/

$hook['post_controller_constructor'][] = array(
	'class'    => 'Pos_legacy_gate',
	'function' => 'enforce',
	'filename' => 'Pos_legacy_gate.php',
	'filepath' => 'hooks',
	'params'   => array(),
);
