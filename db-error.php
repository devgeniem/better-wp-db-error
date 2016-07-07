<?php
/**
 * Custom error page rather than database connection error
 * source: http://alexphelps.github.io/server-error-pages/
 * modifications: Onni Hakala / Geniem Oy
 */

/**
 * Few functions to give better db errors
 */
function check_port($host,$port) {
    $connection = @fsockopen($host, $port);
    if (is_resource($connection)) {
        debug_msg($host . ':' . $port . ' ' . '(' . getservbyport($port, 'tcp') . ') is open.');
        fclose($connection);
    } else {
        debug_msg($host . ':' . $port . ' is not responding.');
    }
}

// Colorful output to cli
function error_msg($message)                { echo "\033[31mError: \033[0m{$message} \n"; }
function info_msg($message,  $type='Info'  ){ echo "\033[33m{$type}: \033[0m{$message} \n"; }
function debug_msg($message, $type='DEBUG' ){ echo "\033[36m{$type}: \033[0m{$message} \n"; }

// Allow better debugging if this is run from command line
if ( defined( 'WP_CLI' ) ) :
    error_msg( 'Error establishing a database connection' );

    if ( defined('DB_HOST') ) :
        /*
         * Check if dns to database is resolving correctly
         */
        if (filter_var(DB_HOST, FILTER_VALIDATE_IP) === false) {
            $ips = gethostbynamel(DB_HOST);
            if ( empty($ips) ) {
                debug_msg(DB_HOST . ' dns query doesn\'t resolve to any ip address.');
            } else {
                debug_msg(DB_HOST . ' dns query resolves to: ' . implode(',', $ips) );
            }
        } else {
            // If database is just plain ip address dns doesn't matter
            $ips = array(DB_HOST);
        }
        /**
         * Check if port to database is open
         * Check all databases in case this resolves to multiple ones
         */
        $port = ( defined('DB_PORT') ? DB_PORT : 3306 );
        foreach ($ips as $ip) {
            check_port($ip,$port);
        }
    else :
        error_msg( 'DB_HOST is not defined' );
    endif;

else :
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 300'); // 5 minutes = 300 seconds
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="503 Palvelu ei ole saatavilla">
    <meta name="author" content="">
    <title>503 Palvelu ei ole saatavilla</title>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <style>
    /* Error Page Inline Styles */
    body {
      padding-top: 20px;
    }
    /* Layout */
    .jumbotron {
      font-size: 21px;
      font-weight: 200;
      line-height: 2.1428571435;
      color: inherit;
      padding: 10px 0px;
    }
    /* Everything but the jumbotron gets side spacing for mobile-first views */
    .masthead, .body-content, {
      padding-left: 15px;
      padding-right: 15px;
    }
    /* Main marketing message and sign up button */
    .jumbotron {
      text-align: center;
      background-color: transparent;
    }
    .jumbotron .btn {
      font-size: 21px;
      padding: 14px 24px;
    }
    /* Colors */
    .green {color:#5cb85c;}
    .orange {color:#f0ad4e;}
    .red {color:#d9534f;}
    </style>
    <script type="text/javascript">
      function loadDomain() {
        var display = document.getElementById("display-domain");
        display.innerHTML = document.domain;
      }
    </script>
    </head>
    <body onload="javascript:loadDomain();">
    <!-- Error Page Content -->
    <div class="container">
      <!-- Jumbotron -->
      <div class="jumbotron">
        <h1><i class="fa fa-exclamation-triangle orange"></i> 503 Palvelu ei ole saatavilla</h1>
        <p class="lead">Sivustolla <em><span id="display-domain"></span></em> tapahtui väliaikainen virhe.</p>
        <a href="javascript:document.location.reload(true);" class="btn btn-default btn-lg text-center"><span class="green">Lataa sivu uudelleen tästä</span></a>
      </div>
    </div>
    <div class="container">
      <div class="body-content">
        <div class="row">
          <div class="col-md-6">
            <h2>Mitä tapahtui?</h2>
            <p class="lead">503 virhekoodi tarkoittaa, että palvelimella on väliaikainen lyhytkestoinen ongelma, tai että sitä ylläpidetään parhaillaan. Virhe häviää normaalisti nopeasti.</p>
          </div>
          <div class="col-md-6">
            <h2>Mitä voin tehdä?</h2>
            <p class="lead">Jos olet sivuston käyttäjä</p>
            <p>Pahoittelemme käyttökatkosta, valvontamme on havainnut ongelman ja sitä selvitetään parhaillaan.</p>
            <p class="lead">Jos olet sivuston omistaja</p>
             <p>Ongelma on todennäköisesti väliaikainen, mutta voit varmuudeksi tarkistaa sivuston tilankäytön ja tietokantayhteyden.</p>
         </div>
        </div>
      </div>
    </div>
    <!-- End Error Page Content -->
    <!--Scripts-->
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </body>
    </html>
<?php endif;
