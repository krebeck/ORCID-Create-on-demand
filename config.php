<?php
/*
 *
 */

// Local app configuration
$log_debug = true; // where will this go on an Apache server?
$JSONDB = '../db.json';
# Use response scope to display human-readable authorizations
$scope_desc['/authenticate'] = "Get your ORCID iD";
$scope_desc['/read-limited'] = "Read your limited-access information";
$scope_desc['/activities/update'] = "Add or update your research activities";
$scope_desc['/person/update'] = "Add or update your personal information";
# TBD:  convert HTML to PHP files and define here:
#       home URL, lib URL, buttons, footer, etc


// ORCID API CREDENTIALS
////////////////////////////////////////////////////////////////////////

define('OAUTH_CLIENT_ID', 'APP-XXXXXXXXXXXXXXXX');
define('OAUTH_REDIRECT_URI', 'https://dra.american.edu/orcid/');
// define OAUTH_CLIENT_SECRET in this file:
require_once('oauth-client-secret.php');

// ORCID API ENDPOINTS
////////////////////////////////////////////////////////////////////////

// Sandbox - Member API
define('OAUTH_AUTHORIZATION_URL', 'https://sandbox.orcid.org/oauth/authorize');//authorization endpoint
define('OAUTH_TOKEN_URL', 'https://api.sandbox.orcid.org/oauth/token'); //token endpoint

// Sandbox - Public API
//define('OAUTH_AUTHORIZATION_URL', 'https://sandbox.orcid.org/oauth/authorize');//authorization endpoint
//define('OAUTH_TOKEN_URL', 'https://pub.sandbox.orcid.org/oauth/token');//token endpoint

// Production - Member API
//define('OAUTH_AUTHORIZATION_URL', 'https://orcid.org/oauth/authorize');//authorization endpoint
//define('OAUTH_TOKEN_URL', 'https://api.orcid.org/oauth/token'); //token endpoint

// Production - Public API
//define('OAUTH_AUTHORIZATION_URL', 'https://orcid.org/oauth/authorize');//authorization endpoint
//define('OAUTH_TOKEN_URL', 'https://orcid.org/oauth/token');//token endpoint

?>
