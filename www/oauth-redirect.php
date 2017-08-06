<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ORCID Access Approved</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Styles -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="bootstrap/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="https://orcid.org/sites/default/files/images/orcid_16x16.png" />
</head>

<body>

<?php
    require_once('../config.php');

    if ($log_debug) {
        error_log( 'ORCID DEBUG: REQUEST ' . $_SERVER["REQUEST_URI"] );
    }

    // if user denies authorization send them to that page
    if (isset($_GET['error']) && $_GET['error'] == 'access_denied') {
        # error=access_denied&error_description=User%20denied%20access
        header( 'Location: ' . OAUTH_REDIRECT_URI . 'oauth-deny.html' );

    // If an authorization code exists, fetch the access token
    } else if (isset($_GET['code'])) {
        $code = $_GET['code'];

        if ($log_debug) {
            error_log( "ORCID DEBUG: Auth code $code received by OAuth Client ".OAUTH_CLIENT_ID );
        }

        // Build request parameter string
        $params = "client_id=" . OAUTH_CLIENT_ID
                . "&client_secret=" . OAUTH_CLIENT_SECRET
                . "&grant_type=authorization_code&code=" . $code
                . "&redirect_uri=" . OAUTH_REDIRECT_URI . "oauth-redirect.php";

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, OAUTH_TOKEN_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        // Turn off SSL certificate check for testing - remove this for production version!
        //curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // Turn off SSL certificate check for testing - remove this for production version!
        //curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt( $ch, CURLOPT_HEADER, TRUE );

        // Execute cURL command
        if ($log_debug) {
            error_log( "ORCID DEBUG: POST ".OAUTH_TOKEN_URL."?$params" );
        }
        $result = curl_exec($ch);
        list ($hdr, $body) = explode( "\r\n\r\n", $result, 2 );
        $code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

        # get the HTTP response code based on cURL error or API response code
        $errno = curl_errno( $ch );
        if ($errno) {
            $message = curl_error( $ch ) . " ($errno)";
            error_log( "ORCID ERROR: $message" );
            $body = '{"status":500,"error":"'. $message . '"}';
            echo "--- AUTHORIZATION FAILED: $message";
        }

        if ($log_debug) {
            error_log( "ORCID DEBUG: RESPONSE ($code) $body" );
        }

        if ($code == 200) {
            // Transform cURL response from json string to php array
            $response = json_decode($body, true);
            # TBD: check for JSON decode error?

            # Use response scope to display human-readable authorizations
            $scope_list = '<ul class="list-group">';
            $scopes = explode(" ", $response['scope']);
            foreach ($scopes as $scope) {
                $scope_list .= '<li class="list-group-item">'.$scope_desc[$scope].'</li>';
            }
            $scope_list .= "</ul>\n";

        # For now, store responses in a json file
        if ($json = file_get_contents( $JSONDB )) {
            $dbarray = json_decode( $json );
            if (is_null( $dbarray )) {
                $message = "$JSONDB decode error (".json_last_error().")";
                error_log( "ORCID ERROR: $message" );
                echo "--- AUTHSTORE FAILED: $message";
            } else {
                # update $JSONDB
                $dbarray->{$response['orcid']} = $response;
                if (! file_put_contents( $JSONDB, json_encode( $dbarray, JSON_PRETTY_PRINT )."\n" ))
                {
                    $message = "$JSONDB write error (".json_last_error().")";
                    error_log( "ORCID ERROR: $message" );
                    echo "--- AUTHSTORE FAILED: $message";
                }
            }
        } else {
            $message = "$JSONDB read error";
            error_log( "ORCID ERROR: $message" );
            echo "--- AUTHSTORE FAILED: $message";
        }
    } else {
        error_log( "ORCID ERROR: ".OAUTH_TOKEN_URL." returned $code" );
        echo "--- AUTHORIZATION FAILED: HTTP Response Code $code";
    }

    // Close cURL session
    curl_close($ch);

    // If an authorization code doesn't exist, throw an error
    } else {
        $message = "Unable to connect to ORCID";
        error_log( "ORCID WARNING: $message" );
        echo "--- AUTHORIZATION FAILED: $message";
    }

?>

<div class="container">

    <div class="masthead">
      <ul class="nav nav-pills pull-right">
        <li><a href="https://dra.american.edu/orcid/">Pilot Home</a></li>
        <li><a href="https://orcid.org" target="_blank">About ORCID</a></li>
        <li><a href="http://www.american.edu/library/">AU Library</a></li>
      </ul>
      <h4 class="muted">ORCID @ American University Library</h4>
    </div>

    <hr>

    <div class="jumbotron">
        <h2>ORCiD Confirmation</h2>
        <br>
<?php if (isset( $response )) { ?>
        <p class="lead">Thanks, <?php echo $response['name']; ?>. You have authorized AU Library to:
    <?php echo $scope_list; ?>
        </p>
        <br> <br>
        <p class="lead">Please keep track of your ORCID <img src="https://orcid.org/sites/default/files/images/orcid_16x16.png" class="logo" width='16' height='16' alt="iD"/>: <?php echo $response['orcid']; ?></p>
        <a class="btn btn-large"  href="https://sandbox.orcid.org/my-orcid" target="_blank">Go to your ORCID record</a>
<?php } else { ?>
        <p class="lead">Sorry, it appears some problem has ocurred.</p>
<?php } ?>
    </div>

    <hr>

    <div class="footer">
        <a href="https://docs.google.com/document/d/1HygRQ6hqoElILQvGjxkgZ4cSS_Y4B4vbx59Ex0ApFB0/edit?usp=sharing" target="_blank">AU/WRLC ORCID Create-on-demand Pilot Project</a>
    </div>

</div> <!-- /container -->

<!-- Javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="bootstrap/js/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>

</body></html>
