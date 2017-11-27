<?php 
function DrieNaarTwee ($regel) {
	if (strpos($regel,'<coordinates>')!== false) {
		$regel = strip_tags($regel);
		$effetot = '<LineString><coordinates>';
		$punten = preg_split('/ /', $regel, -1 ,PREG_SPLIT_NO_EMPTY);
		foreach ($punten as $punt){
			$effe = preg_split('/,/',$punt, -1 ,PREG_SPLIT_NO_EMPTY);
			$effetot = $effetot . $effe[0]. ',' .$effe[1] . ' '; 
		}
		return $effetot.'</coordinates></LineString>';
	}
	else {
		return '';
	}
}
?>