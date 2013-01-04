<?php
/************************************************************************************
** One and only one of the following parameters must be used:
**   q        =  search collection names
**   id       =  get id
**   uuid     =  get uuid
**   concept  =  get collections associated with the passed CSV concept ids
************************************************************************************/


require_once('../../LocalSettings.inc.php');

// Verify that at least one required parameter was provided
	$arr_param = array_merge($_POST, $_GET);
	if (!isset($arr_param['q']) && !isset($arr_param['id']) && 
		!isset($arr_param['uuid']) && !isset($arr_param['concept'])) 
	{
		trigger_error('Must provide at least one parameter "q", "id" or "uuid"', E_USER_ERROR);
	}
	if (!isset($arr_param['debug']))  $arr_param['debug'] = false;

// Connect to OCL db
	if (!($cxn_ocl = mysql_connect($ocl_db_host, $ocl_db_uid, $ocl_db_pwd))) {
		die('Could not connect to database: ' . mysql_error($cxn_ocl));
 	}
	mysql_select_db($ocl_db_name);

// Concept
if (isset($_GET['concept'])) {
	$out = array(
			'responseHeader' => array(
					'params' => $arr_param,
				),
			'response' => array(
					'numFound' => '0',
					'docs' => array()
				)
		);
	$arr_full_id = explode(',', $_GET['concept']);
	if ($arr_full_id) 
	{
		$arr_dict_concept = array();
		foreach ($arr_full_id as $full_id) 
		{
			// Grab the dictionary and concept IDs
			$arr_parts = explode(':', $full_id);
			if (count($arr_parts) >= 2) {
				$dict_id = $arr_parts[0];
				$concept_id = $arr_parts[1];
			} elseif (count($arr_parts) == 1) {
				$dict_id = $ocl_default_concept_dict_short_name;
				$concept_id = $arr_parts[0];
			} else {
				continue;
			}

			$arr_dict_concept[$dict_id][$concept_id] = $concept_id;
		}

		$sql_criteria = '';
		foreach ($arr_dict_concept as $dict_id => $arr_concept_id)
		{
			if ($sql_criteria) $sql_criteria .= ' OR ';
			$sql_criteria .= "((LOWER(cd.short_name) = '" . mysql_escape_string(strtolower($dict_id)) . "') AND clm.concept_id IN (" . implode(',', $arr_concept_id) . "))";
		}
		$sql = 
			'select clm.*, cd.short_name, cl.list_name from ' . mysql_escape_string($ocl_db_name) . '.concept_list_map clm ' . 
			'left join ' . mysql_escape_string($ocl_db_name) . '.concept_dict cd on cd.dict_id = clm.dict_id ' . 
			'left join ' . mysql_escape_string($ocl_db_name) . '.concept_list cl on cl.concept_list_id = clm.concept_list_id ' .
			'where ' . $sql_criteria;
		if ($arr_param['debug']) var_dump($sql);

		$result = mysql_query($sql, $cxn_ocl);
		$out['response']['numFound'] = mysql_num_rows($result);
		if (!$result) {
			echo '<p>', $sql, '</p>';
			exit(mysql_error($cxn_ocl));
		}
		while ($row = mysql_fetch_assoc($result)) {
			$out['response']['docs'][] = $row;
		}
	}
}

echo json_encode( $out );

?>