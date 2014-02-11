<?php
class Chord {
	public $root;
	public $quality;
	public $origRoot;

	// constructor for Chord, takes root note and quality
	public function __construct($root, $quality) {
		$this->root = $root;
		$this->quality = $quality;
		$this->origRoot = $root;
	}
	public function getRoot() {
		return $this->root;
	}
	public function getQuality() {
		return $this->quality;
	}
	public function toArray() {
		return array("root"=>$this->root, "quality"=>$this->quality);
	}
	public function __toString() {
		return $this->root . $this->quality;
	}
	static $rootListSharps = array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B");
	static $rootListFlats = array("C", "Db", "D", "Eb", "E", "F", "Gb", "G", "Ab", "A", "Bb", "B");
	static $qualityList = array("7", "m", "m7", "maj7", "sus2", "sus4", "dim", "");
	// associative array: Maps Root notes to their corresponding index positions in the rootList array
	static $rootToIdx = array("C"=>0, "C#"=>1, "Db"=>1,"D"=>2, "D#"=>3, "Eb"=>3,"E"=>4, "F"=>5, "F#"=>6, "Gb"=>6,"G"=>7, "G#"=>8, "Ab"=>8,"A"=>9, "A#"=>10, "Bb"=>10,"B"=>11);
	static $openChords = array("C", "C7", "Cmaj7", "D", "D7", "Dsus2", "Dsus4", "Dmaj7", "Ddim", "Dm", "Dm7", "E", "E7", "Esus4", "Emaj7", "Edim", "Em", "Em7", "F", "Fmaj7", "G", "G7", "Gsus2", "Gsus4", "Gmaj7", "A", "A7", "Asus2", "Asus4", "Amaj7", "Adim", "Am", "Am7", "B7");
	public function getRootIdx() {
		return Chord::$rootToIdx[$this->root];
	}
	public function chordToString() {
		return $this->root . $this->quality;
	}
	/**
    * Change the root note of this chord by the given amt of halfsteps
    * @param integer between -12 and 12
    * @return void
    */
	public function transpose($steps) {
		$this->root = Chord::$rootListSharps[$this->calcTransposed($steps)];
	}
	
	/**
    * Calculate the new index of root note based on given number of half steps to transpose by
    * @param integer between -12 and 12
    * @return integer
    */
	public function calcTransposed($steps) {
		$currIdx = $this->getRootIdx();
		$newIdx = $currIdx + $steps;
		if ($newIdx > 11) {
			$stepsDone = 11 - $currIdx;
			$newIdx = $steps - $stepsDone - 1;
		}
		else if ($newIdx < 0) {
			$stepsDone = $currIdx + 1;
			$newIdx = 11 + $steps + $stepsDone;
		}
		return $newIdx;
	}
	/**
    * Returns string representing the transposed (by given no. of half steps) version of the chord
    * @param integer between -12 and 12
    * @return String
    */
	public function transposedString($steps) {
		$newIdx = $this->calcTransposed($steps);
		return Chord::$rootListSharps[$newIdx] . $this->getQuality();
	}
	/**
    * Is the string an open chord?
    * @param String
    * @return boolean
    */
	public static function isOpenChord($string) {
		return in_array($string, Chord::$openChords);
	}
	/**
    * Parses string for a Chord, but return false if it isn't valid
    * @param String
    * @return Chord | false
    */
	public static function parseChord($string) {
		$rootNote;
		$qualityVal = "";
		// check if chord is of form 'A', 'B', etc.
		if (strlen($string) == 1) {
			$rootNote = $string;
		}
		else {
			$rootNote = substr($string, 0, 2);
			$checkAccidental = substr($rootNote, 1, 1);
			if ($checkAccidental == "#" || $checkAccidental == "b") {
				$qualityVal .= substr($string, 2);
			}
			else {
				$rootNote = substr($string, 0, 1);
				$qualityVal .= substr($string, 1);
			}	
		}
		
		if (in_array($rootNote, Chord::$rootListSharps) && in_array($qualityVal, Chord::$qualityList)) {
			return new Chord($rootNote, $qualityVal);
		}
		else {
			return false;
		}
	}

}
?>