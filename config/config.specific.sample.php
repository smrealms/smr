<?php
const URL = 'http://localhost';

const ENABLE_DEBUG = true; // This is useful for debugging on dev machines.
const ENABLE_BETA = false;

const RECAPTCHA_PUBLIC = '';
const RECAPTCHA_PRIVATE = '';

const FACEBOOK_APP_ID = '';
const FACEBOOK_APP_SECRET = '';

const TWITTER_CONSUMER_KEY = '';
const TWITTER_CONSUMER_SECRET = '';

const GOOGLE_ANALYTICS_ID = '';

const ENABLE_NPCS_CHESS = false;

// Set to empty string if using a local mailserver.
// Use the default value if using the provided docker-compose orchestration.
const SMTP_HOSTNAME = 'smtp';

// E-mail addresses to receive bug reports
const BUG_REPORT_TO_ADDRESSES = [];

//const HISTORY_DATABASES =
//	array(
//		'smr_classic_history' => 'old_account_id',
//		'smr_12_history' => 'old_account_id2',
//	);
