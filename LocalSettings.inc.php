<?php

// Set error reporting state: Recommend using E_ALL for development environment
	error_reporting(E_ALL);
	ini_set('display_errors',true);

// Set the root directory
	define ('OCL_ROOT', '/Users/paynejd/Sites/openconceptlab/');

// DO NOT EDIT - Include the default settings
	require_once(OCL_ROOT . 'fw/DefaultSettings.inc.php');

// Default database names. Should be modified in LocalSettings.inc.php if different.
	$ocl_default_concept_dict_db    =  'openmrs19'                  ;
	$ocl_default_concept_dict_name  =  'CIEL/MVP Dictionary V1.9'   ;
	$ocl_default_concept_dict_short_name    =  'CIEL16'             ;
	$ocl_db_name           =  'mcl'                          ;

// Database connection
	$ocl_db_host  =  'localhost'   ;
	$ocl_db_uid   =  'mcl_search'  ;
	$ocl_db_pwd   =  'mcl_pwd'     ;

?>