<?php

/**
 * Default object for rendering the search results. Extend this object 
 * to create other renderers.
 */
class ConceptSearchResultsRenderer
{
	public $mode            =  MCL_VIEW_DEFAULT;
	public $debug           =  false;
	public $display_header  =  false;

	public function render($r)
	{
		$this->start($r);
		$this->displayHeader($r);
		$this->startBody($r);
		$this->displayResults($r);
		$this->endBody($r);
		$this->end($r);
	}

	public function start($r) 
	{
		echo '<table id="results_table" ' . 
				'class="table table-striped table-condensed results-table">';
	}
	public function end($r) 
	{
		echo '</table>';
	}
	public function displayHeader($r) 
	{
		if ($this->display_header)
		{
			echo '<thead>';
			echo '<tr>';
			echo '<th></th>';	// checkbox
			echo '<th></th>';	// star
			echo '<th></th>';	// id
			echo '<th>Details</th>';	// details
			//echo '<th>Source</th>';		// source
			echo '</tr>';
			echo '</thead>';
		}
	}
	public function startBody($r) 
	{
		echo '<tbody>';
	}
	public function endBody($r) 
	{
		echo '</tbody>';
	}
	public function displayResults($r) 
	{
		foreach ($r->response->docs as $c) {
			$this->startConcept($r, $c);
			$this->renderConcept($r, $c);
			$this->endConcept($r, $c);
		}
	}
	public function startConcept($r, $c) 
	{
		echo '<tr>';
	}
	public function renderConcept($r, $c) 
	{
		// Checkbox
		echo '<td class="col-checkbox"><input type="checkbox" /></td>';

		// Star
		echo '<td class="col-star"><img src="images/star4.png" alt="Star" /></td>';

		// Concept ID
		$tooltip_content = 
				'<table>' .
				'<tr><td>UUID:</td><td>' . 
					substr($c->uuid, 0, floor(strlen($c->uuid) / 2) ) . 
					'<br />' .
					substr($c->uuid, floor(strlen($c->uuid) / 2), strlen($c->uuid) ) . 
					'</td></tr>' .
				'<tr><td>Created:</td><td>' . $c->timestamp . '</td></tr>' .
				'<tr><td>Retired:</td><td>' . 
				(  (isset($c->retired) && $c->retired == 'retired')  ?  'true'  :  'false'  ) . 
				'</td></tr>' .
				'</table>';
		echo '<td class="col-conceptid">';
		echo '<a href="#" rel="popover" data-html="true" data-trigger="hover" data-placement="top" data-content="' . htmlentities($tooltip_content) . '" title="' . (isset($c->full_id) ? str_replace('_', '-', $c->full_id) : '[full_id]') . ' Details">';
		echo '<span class="' . (isset($c->retired) && $c->retired == 'retired' ? 'retired ' : '') . 'concept-id">';
		echo (isset($c->full_id) ? str_replace('_', '-', $c->full_id) : '[full_id]');
		echo '</span>';
		echo '</a>';
		echo '</td>';

		// Concept details
		echo '<td class="col-name">';

			// Name and summary
			echo '<div class="overflow-ellipsis">';
			echo '<span class="' . 
					(isset($c->retired) && $c->retired == 'retired' ? 'retired' : '') . 
					' preferred-concept-name">' . 
					(isset($c->pname) ? $c->pname : '[pname]') . 
					'</span> ';
			echo '<span class="concept-class">[ ' . (isset($c->class) ? $c->class : '<em>class</em>') . ' / ' . 
					(isset($c->datatype) ? $c->datatype : '<em>datatype</em>') . ' ]</span> ';
			echo '<span class="concept-description">' . (isset($c->description) ? ' - ' . $c->description : '') . '</span>';
			echo '</div>';

			// Synonyms
			if (  $arr_elements = explode(' | ', $c->name)  ) {
				echo '<div class="overflow-ellipsis">';
				echo '<span class="subheading">Synonyms:&nbsp;&nbsp; </span>';
				foreach ($arr_elements as $element) {
					echo '<span class="bubble-mapping">' . htmlentities(($element)) . '</span>';
				}
				echo '</div>';
			}

			// Mappings
			/*
			if (  $arr_elements = explode(' | ', $c->name)  ) {
				echo '<div class="overflow-ellipsis">';
				echo '<span class="subheading">Mappings:&nbsp;&nbsp; </span>';
				foreach ($arr_elements as $element) {
					echo '<span class="bubble-mapping">' . htmlentities($element) . '</span>';
				}
				echo '</div>';
			}
			*/

			// Collections
			if (  isset($c->collections) && is_array($c->collections) && count($c->collections)  ) 
			{
				echo '<div class="overflow-ellipsis">';
				echo '<span class="subheading">Collections:&nbsp;&nbsp; </span>';
				foreach ($c->collections as $coll)
				{
					echo '<span class="bubble-collection">' . htmlentities($coll->list_name) . '</span>';
				}
				echo '</div>';
			}

			// Concept Sets
			if (  isset($c->set_parent) && 
				  $arr_elements = explode(' | ', $c->set_parent)  ) 
			{
				echo '<div class="overflow-ellipsis">';
				echo '<span class="subheading">Concept Sets:&nbsp;&nbsp; </span>';
				foreach ($arr_elements as $element) {
					echo '<span class="bubble-conceptset">' . htmlentities(($element)) . '</span>';
				}
				echo '</div>';
			}

			// Questions
			if (  isset($c->question) && 
				  $arr_elements = explode(' | ', $c->question)  ) 
			{
				echo '<div class="overflow-ellipsis">';
				echo '<span class="subheading">Questions:&nbsp;&nbsp; </span>';
				foreach ($arr_elements as $element) {
					$arr_el   =  explode(' : ', $element);
					$el_id    =  (isset($arr_el[0]) ? $arr_el[0] : '????');
					$el_name  =  (isset($arr_el[1]) ? $arr_el[1] : '????');
					echo '<a href="' . 
						ConceptSearchResultsRenderer::buildSearchUrl(array('q'=>'+dict:'.$c->dict.' +id:'.$el_id)) .
						'" class="bubble-answer">' . htmlentities($c->dict.'-'.$el_id).': '.htmlentities(($el_name)) . '</a>';
						//'" class="bubble-question">' . htmlentities($element) . '</a>';
				}
				echo '</div>';
			}

			// Answers
			if (  isset($c->answer) && 
				  $arr_elements = explode(' | ', $c->answer)  ) 
			{
				echo '<div class="overflow-ellipsis">';
				echo '<span class="subheading">Answers:&nbsp;&nbsp; </span>';
				foreach ($arr_elements as $element) {
					$arr_el   =  explode(' : ', $element);
					$el_id    =  $arr_el[0];
					$el_name  =  (isset($arr_el[1]) ? $arr_el[1] : 'NO NAME');
					echo '<a href="' . 
						ConceptSearchResultsRenderer::buildSearchUrl(array('q'=>'+dict:'.$c->dict.' +id:'.$el_id)) .
						'" class="bubble-answer">' . htmlentities($c->dict.'-'.$el_id.': '.($el_name)) . '</a>';
						//'" class="bubble-answer">' . htmlentities($element) . '</a>';
				}
				echo '</div>';
			}

		echo '</td>';
	}

	public function endConcept($r, $c) 
	{
		echo '</tr>';

		if ($this->debug) {
			echo '<tr><td colspan="4" class="debug">';
			print_r($c);
			echo '</td></tr>';
		}
	}

	public static function buildSearchUrl($arr_params, $arr_default_params=null) 
	{
		if (!$arr_default_params) $arr_default_params = array();
		$url = 'search.php?';
		$i = 0;
		foreach ($arr_params as $k => $v) {
			if (isset($arr_default_params[$k]) && $arr_default_params[$k] == $v) continue;
			if ($i) $url .= '&amp;';
			$url .= urlencode($k) . '=' . urlencode($v);
			$i++;
		}
		return $url;
	}
}


?>