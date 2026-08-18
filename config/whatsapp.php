<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| WhatsApp Cloud API (Meta) — webhook + messaging config.
| Verify token must match Meta Developer → WhatsApp → Webhook settings.
*/
$config['verify_token'] = 'ShahbazWA2026Verify';

// Fill after Meta API Setup (optional — for sending messages later)
$config['access_token'] = '';
$config['phone_number_id'] = '';
$config['whatsapp_business_account_id'] = '';
