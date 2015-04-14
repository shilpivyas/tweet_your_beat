<?php
require_once ('lib/codebird.php');
//\Codebird\Codebird::setConsumerKey('kyPWcav2wP1l3QG8aostdOSeH','jkMzkIScIWmvA6XgERy7XkwNEH73HyoUPymRwr0sWELzEtFY5o');
// static, see 'Using multiple Codebird instances'

//$cb = \Codebird\Codebird::getInstance();


$codebird = new Codebird();
$codebird->setConsumerKey('kyPWcav2wP1l3QG8aostdOSeH','jkMzkIScIWmvA6XgERy7XkwNEH73HyoUPymRwr0sWELzEtFY5o');

$cb = $codebird->getInstance();