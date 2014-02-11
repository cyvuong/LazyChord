<?php
class LazyChordApp {
	public $intro;
	public $verse;
	public $chorus;
	public $bridge;
	public $restrict; // This is if user restricts capo placement

	public function getIntro() {
		return $this->intro;
	}
	public function getVerse() {
		return $this->intro;
	}
	public function getChorus() {
		return $this->intro;
	}
	public function getBridge() {
		return $this->intro;
	}
	/**
    * Makes a list of Chord objects from a list of strings representing chords
    * @param array of Strings
    * @return array of Chords | false
    */
	public static function makeChords($list) {
		$resultList = array();
		$valid = true;
		foreach ($list as $idx => $chordString) {
			$currChord = Chord::parseChord($chordString);
			if ($currChord) {
				$resultList[$idx] = $currChord;
			}
			else {
				$valid = false;
				break;
			}
		}

		if ($valid) {
			return $resultList;
		}
		else {
			return array();
		}
	}
	/**
    * Computes how many open chords there are if the list is transposed up by given num. halfsteps
    * @param array of Chords, integer
    * @return integer
    */
	public static function howManyOpenChords($chords, $steps) {
		$numOpenChords = 0;
		// iterate over each Chord object in the list
		foreach ($chords as $idx => $chord) {
			if (Chord::isOpenChord($chord->transposedString($steps))) {
				$numOpenChords++;
			}
		}
		return $numOpenChords;
	}
	/**
    * Calculates which transposition gives the maximum number of open chords
    * @param array of Chords, integer, integer
    * @return integer
    */
    public static function calcBestTransposition($chords, $capoRestrict) {
    	$bestTransposition = 0;
    	$mostOpenChords = 0;
    	$startIdx = 12 - $capoRestrict;
    	for ($i = $startIdx; $i < 13; $i++) {
    		$currNumOpenChords = LazyChordApp::howManyOpenChords($chords, $i);
    		// TODO: if same number of open chords, we need to keep both on record to show to user
    		if ($currNumOpenChords >= $mostOpenChords) {
    			$mostOpenChords = $currNumOpenChords;
    			$bestTransposition = $i;
    		}
    	}
    	return $bestTransposition;

    }
    public static function calcCapoPosn($steps) {
    	$result = 12 - $steps;
    	return $result;
    }
    public function transposeSong($steps) {
    	$parts = array("intro", "verse", "chorus", "bridge");
    	foreach ($parts as $part) {
    		if ($this->$part) {
	    		foreach ($this->$part as $chord) {
	    			$chord->transpose($steps);
	    		}
    		}
    	}
    }
    public function makeChordList() {
    	$chordList = array();
    	$parts = array("intro", "verse", "chorus", "bridge");
    	foreach ($parts as $part) {
    		$chordList[$part] = array();
    		if ($this->$part) {
    			foreach ($this->$part as $chord) {
    				$chordList[$part][] = $chord->chordToString();
    			}
    		}
    	}
    	return $chordList;
    }

	public function initializeChords($intro, $verse, $chorus, $bridge) {
		$this->intro = LazyChordApp::makeChords($intro);
		$this->verse = LazyChordApp::makeChords($verse);
		$this->chorus = LazyChordApp::makeChords($chorus);
		$this->bridge = LazyChordApp::makeChords($bridge);
	}
	public function calcAndPkgResult() {
		// calculate best transposition (taking capo restriction into account)
		$transposition = LazyChordApp::calcBestTransposition(array_merge($this->getIntro(), $this->getVerse(), $this->getChorus(), $this->getBridge()), $this->restrict);
		// transpose the song
		$this->transposeSong($transposition);
		// make array of chord strings
		$toReturn = $this->makeChordList();
		// attach the transposition number
		$toReturn["transposition"] = $transposition;
		// attach the appropriate capo position
		$toReturn["capoPosn"] = LazyChordApp::calcCapoPosn($transposition);

		return $toReturn;
	}
	public function clientHandler() {
		if ($_SERVER["REQUEST_METHOD"] == "POST") {

			if ( !empty($_POST['intro'])    ) { //  && !empty($_POST['verse']) && !empty($_POST['chorus']) && !empty($_POST['bridge']) && !empty($_POST['restrict'])) {
				//echo json_encode(array("chord" => $_POST["restrict"]));
				//echo json_encode(array("hello" => "pluto"));
				$this->initializeChords($_POST["intro"], $_POST["verse"], $_POST["chorus"], $_POST["bridge"]);
				// $this->initializeChords(json_decode($_POST["intro"]), json_decode($_POST["verse"]), json_decode($_POST["chorus"]), json_decode($_POST["bridge"]));
				$this->restrict = $_POST["restrict"];

				$response = $this->calcAndPkgResult();
				echo json_encode($response);
			}
		}
	}
}
?>