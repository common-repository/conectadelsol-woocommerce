<?php

require_once( '../../../'  .  'wp-blog-header.php' );

require_once( ABSPATH . WPINC . '/rest-api/fields/class-wp-rest-meta-fields.php' );


echo "Nonce 'wp_rest':" . wp_create_nonce('wp_rest') ;
echo "<br>";
echo "CDS_FSOL_FIELD: " .constant("CDS_FSOL_FIELD") ;
echo "<br>";
echo "<br>";
echo "<br>";

//SERVER
echo "SERVER_NAME: " . $_SERVER['SERVER_NAME'];
echo "<br>";
echo "SERVER : " . json_encode($_SERVER);
echo "<br>";

/*
//GLOBALS
echo "<br>";
echo "GLOBALS : " . json_encode($GLOBALS);
echo "<br>";
*/

echo json_encode( get_registered_meta_keys('term') );
echo "<br>";
echo "<br>";
echo json_encode( get_registered_meta_keys('product_cat') );


echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
// Muestra toda la información, por defecto INFO_ALL
phpinfo();