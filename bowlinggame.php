<?php
require 'frame.php';

class BowlingGame {

	private $iframe = 1;
	public function getFrame() { return $this->iframe; }
	public function getRoll() { return count($this->frames[$this->iframe]->rolls)+1; }
	
	private $finished = false;
	public function getFinished() { return $this->finished; }
	
	private $frames = array();
	
	public function __construct() {
		for ($i = 1; $i <= 10; $i++) $this->frames[$i] = new Frame;
        $this->frames[10]->tenth = true;
	}
	
	public function roll($pins) {
        $frame = &$this->frames[$this->iframe];

		//If $pins is a string, make sure the string contains only an int, then convert it to int
        if ($pins === 'x' || $pins === 'X') $pins = 10;
		if (!is_int($pins) && preg_match("/^[0-9]+$/", $pins)) $pins = (int)$pins;
		if (!is_int($pins)) throw new Exception('Invalid Roll');
		
		if ($pins > 10 || $pins < 0) 
			throw new Exception('Invalid Roll');
			
		$frame->addRoll($pins);
		
		if ($frame->tenth && $frame->completed) {
			$this->finished = true;
            return;
		}
		
		$this->calculateWaitFrames();
        if ($frame->completed) $this->iframe++;
	}
    
    public function resetFrame() {
        $this->frames[$this->iframe]->reset();
    }
	
	private function calculateWaitFrames() {
        $frames = &$this->frames;
        for ($i = $this->iframe - 2; $i <= 10; $i++) {
            $frame = &$frames[$i];
            if ($frame->wait === false) continue;
            
            if ($frame->strike) {
                if ($i <= 8 && $frames[$i+1]->strike && isset($frames[$i+2]->rolls[0])) {
                    $frame->score = 10 + 10 + $frames[$i+2]->rolls[0];
                    $frame->wait = false;
                }
                elseif ($frames[$i+1]->strike === false && isset($frames[$i+1]->rolls[0]) && isset($frames[$i+1]->rolls[1])) {
                    $frame->score = 10 + $frames[$i+1]->rolls[0] + $frames[$i+1]->rolls[1];
                    $frame->wait = false;
                }
            }
            elseif ($frame->spare) {
                if (isset($frames[$i+1]->rolls[0])) {
                    $frame->score = 10 + $frames[$i+1]->rolls[0];
                    $frame->wait = false;
                }
            }
		}
	}
	
	public function getScore() {
		$score = 0;
		for ($i = 1; $i <= 10; $i++) {
            if ($this->frames[$i]->completed === false) return $score;
			if ($this->frames[$i]->wait) return $score;
			$score += $this->frames[$i]->score;
		}
		return $score;
	}
	
	public function displayScoreSheet() {
        $frames = &$this->frames;
		//+-----+--------+
		//| 5| 4| X| X| X|
		//|  +--|  +--+--|
		//|    9|        |
		//+-----+--------+
		echo '+-----+-----+-----+-----+-----+-----+-----+-----+-----+--------+' . "\n";
		for ($i = 1; $i <= 10; $i++) {
			$frame = &$frames[$i];
			echo '| ';
            
            if ($frame->strike) echo ' ';
            elseif (isset($frame->rolls[0])) {
                if ($frame->rolls[0] == 0) echo '-';
                elseif ($frame->tenth && $frame->rolls[0] == 10) echo 'X';
                else echo $frame->rolls[0];
            }
            elseif ($i == $this->iframe && ($i == 1 || $frames[$i-1]->completed)) {
                echo '#';
            }
            else echo ' ';
            
			echo '| ';
            
            if ($frame->spare) echo '/';
            elseif (isset($frame->rolls[1])) {
                if ($frame->rolls[1] == 0) echo '-';
                elseif ($frame->tenth && $frame->rolls[1] == 10) echo 'X';
                else echo $frame->rolls[1];
            }
            elseif ($frame->strike) {
                echo 'X';
            }
            elseif ($i == $this->iframe && isset($frame->rolls[0])) {
                echo '#';
            }
            else echo ' ';
            
            if ($frame->tenth) {
				echo '| ';
                if (isset($frame->rolls[2])) {
                    if ($frame->rolls[2] == 10) echo 'X';
                    elseif ($frame->rolls[2] == 0) echo '-';
                    else echo $frame->rolls[2];
                }
                elseif ($i == $this->iframe && $frame->completed === false && isset($frame->rolls[0]) && isset($frame->rolls[1])) {
                    echo '#';
                }
                else echo ' ';
			}
		}
		echo '|' . "\n";
		echo '|  +--|  +--|  +--|  +--|  +--|  +--|  +--|  +--|  +--|  +--+--|' . "\n";
        $stop_scoring = false;
		$score_increment = 0;
		for ($i = 1; $i <= 10; $i++) {
            $frame = &$frames[$i];
			if ($frame->wait || $frame->completed === false) $stop_scoring = true;
			$score_increment += $frame->score;
			echo '|  ';
			if ($frame->tenth) echo '   ';
			if ($stop_scoring) echo '   ';
			else echo str_pad($score_increment, 3, ' ', STR_PAD_LEFT);
		}
		echo '|' . "\n";
		echo '+-----+-----+-----+-----+-----+-----+-----+-----+-----+--------+' . "\n";
		echo 'Score: ' . $this->getScore() . "\n\n";
	}
	
}