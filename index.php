<?php 
if(!defined('ROOT')) exit('No direct script access allowed');

include_once __DIR__."/api.php";

$slug=_slug("?/src/type");

if(strlen($slug['src'])<=0) {
	echo "<h3 class='errormsg text-center'>Sorry, Form Source not defined</h3>";
	return;
}

printMultiform($slug['src']);

// printArray($slug);
?>
