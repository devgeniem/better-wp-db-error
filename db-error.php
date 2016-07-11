<?php
/**
 * Custom error page rather than database connection error
 * source: http://alexphelps.github.io/server-error-pages/
 * modifications: Onni Hakala / Geniem Oy
 */

// Prevent direct access
defined('ABSPATH') or die();

/**
 * Check dns resolving for DB_HOST
 */
function check_dns() {
    if (filter_var(DB_HOST, FILTER_VALIDATE_IP) === false) {
        $ips = gethostbynamel(DB_HOST);
        if ( empty($ips) ) {
            debug_msg('\''.DB_HOST . '\' dns query doesn\'t resolve to any ip address.');
        } else {
            debug_msg(DB_HOST . ' dns query resolves to: ' . implode(',', $ips) );
        }
    } else {
        // If database is just plain ip address dns doesn't matter
        $ips = array(DB_HOST);
    }
    return $ips;
}

/**
 * Check if DB_PORT is open for DB_HOST
 * This is needed in case you have multiple dns load balanced databases
 */
function check_open_ports($hosts) {
    $result = false;
    if ( ! empty($hosts) ) {
        $result = true;
        $port = ( defined('DB_PORT') ? DB_PORT : 3306 );
        foreach ( $hosts as $host ) {

            if ( ! check_open_port( $host,$port ) ) {
                $result = false;
            }
        }
    }
    return $result;
}

/**
 * Check if DB_PORT is open for DB_HOST
 */
function check_open_port($host,$port) {
    $connection = @fsockopen($host, $port);
    if (is_resource($connection)) {
        debug_msg($host . ':' . $port . ' ' . '(' . getservbyport($port, 'tcp') . ') port is open.');
        fclose($connection);
        return true;
    } else {
        debug_msg($host . ':' . $port . ' port is not responding.');
        return false;
    }
}

/**
 * Checks if password is correct or database exists
 */
function check_mysql_connection($host,$user,$password,$database) {
    $link = mysqli_connect($host, $user, $password, $database);

    if (!$link) {
        debug_msg('Unable to connect to MySQL, errorno: '.mysqli_connect_errno());
        debug_msg(mysqli_connect_error());
        return false;
    }

    mysqli_close($link);
    return true;
}

/**
 * int main() for debugging
 */
function debug_connection() {
    // Don't show warnings here since then we would just have the same messages two times
    error_reporting(0);

    /*
     * Check if dns to database is resolving correctly
     */
    $db_ips = check_dns();

    /**
     * Check if port to database is open
     * Check all databases in case this resolves to multiple ones
     */
    if ( ! empty($db_ips) ) {
        $result = check_open_ports($db_ips);
    }

    /**
     * If mysql is running try to determine what went wrong
     */
    if ($result) {
        check_mysql_connection($db_ips[0], DB_USER, DB_PASSWORD, DB_NAME);
    }
}

/**
 * Ensure the same debugging headers
 */
function send_db_error_headers() {
    if (!headers_sent()) { // Only send headers if we can, We don't to create more errors
        header( 'Content-Type: text/html; charset=utf-8' );
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 300'); // 5 minutes = 300 seconds
        nocache_headers();
    }
}

/**
 * Helpers to output into browser and to the command line
 * - Use colorful output to cli
 */

// This outputs into commandline
if ( defined( 'WP_CLI' ) ) {
    function error_msg($message)                { echo "\033[31mError: \033[0m{$message} \n"; }
    function info_msg($message,  $type='INFO'  ){ echo "\033[33m{$type}: \033[0m{$message} \n"; }
    function debug_msg($message, $type='DEBUG' ){ echo "\033[36m{$type}: \033[0m{$message} \n"; }

// This outputs to browser
} elseif ( defined('WP_DEBUG') && WP_DEBUG ) {
    function error_msg($message)                { echo "ERROR: {$message} \n"; }
    function info_msg($message,  $type='INFO'  ){ echo "{$type}: {$message} \n"; }
    function debug_msg($message, $type='DEBUG' ){ echo "{$type}: {$message} \n"; }

// This outputs to error logs
} else {
    function error_msg($message)                { error_log("DB ERROR: {$message}", 0); }
    function info_msg($message,  $type='INFO'  ){ error_log("DB ERROR {$type}: {$message}", 0); }
    function debug_msg($message, $type='DEBUG' ){ error_log("DB ERROR {$type}: {$message}", 0); }
}

// Allow better debugging if this is run from command line
if ( defined( 'WP_CLI' )  ) {
    error_msg( 'Error establishing a database connection' );

    if ( defined('DB_HOST') ) {
        debug_connection();
    } else {
        error_msg( 'DB_HOST is not defined' );
    }

// Also show errors if WP_DEBUG is enabled
} else {
    send_db_error_headers();

    if ( defined('WP_DEBUG') && WP_DEBUG ) { ?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml"<?php if ( is_rtl() ) echo ' dir="rtl"'; ?>>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php _e( 'Database Error' ); ?></title>
            <style>
                pre {
                    background: lightgrey;
                    padding: 20px;
                    font-size: 16px;
                }
            </style>
        </head>
        <body>

            <h1><?php _e( 'Error establishing a database connection' ); ?></h1>

            <pre><?php debug_connection(); ?></pre>

        </body>
        </html>
        <?php
    } else {

        // Debug connection to logs
        debug_connection();
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
            <p class="lead">Sivustolla <em><span id="display-domain"><?php echo $_SERVER['HTTP_HOST']; ?></span></em> tapahtui väliaikainen virhe.</p>
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
    <?php }
}
