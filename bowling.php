<?php
class BowlingGame {

	private $frame = 1;
	public function getFrame() { return $this->frame; }
	
	private $finished = false;
	public function getFinished() { return $this->finished; }
	
	private $roll_list = array();
	private $frame_scores = array();
	private $wait_frames = array();
	
	public function __construct() {
		for ($i = 1; $i <= 10; $i++) $this->wait_frames[$i] = false;
	}
	
	public function roll($pins) {
		
		//If $pins is a string, make sure the string contains only an int, then convert it to int
		if (!is_int($pins) && preg_match("/^[0-9]+$/", $pins)) $pins = (int)$pins;
		else throw new Exception('Invalid Roll');
		
		if ($pins > 10 || $pins < 0) 
			throw new Exception('Invalid Roll');
			
		$this->roll_list[ $this->frame ][] = $pins;
		
		//First Roll
		if (count($this->roll_list[ $this->frame ]) == 1) {
			if ($pins == 10) {
				$this->wait_frames[ $this->frame ] = true;
				if ($this->frame < 10) $this->frame++;
			}
		}
		
		//Second Roll
		elseif (count($this->roll_list[ $this->frame ]) == 2) {
			$frame_pins = $this->origFrameScore($this->frame);
			if ($frame_pins > 10 && $this->frame < 10) {
				unset($this->roll_list[ $this->frame ]);
				throw new Exception('Invalid Frame');
			}
			elseif ($frame_pins == 10 && $this->frame < 10) {
				$this->wait_frames[ $this->frame ] = true;
			}
			elseif ($this->isSpare($this->frame) && $this->frame == 10) {
				$this->wait_frames[ $this->frame ] = true;
			}
			else {
				$this->frame_scores[ $this->frame ] = $frame_pins;	
			}
			
			if ($this->frame == 10 && $frame_pins < 10) {
				$this->calculateWaitFrames();
				$this->finished = true;
			}
			elseif ($this->frame < 10) {
				$this->frame++;
			}
		}
		
		//Third Roll, Tenth Frame
		elseif (count($this->roll_list[ $this->frame ]) == 3) {
			$this->calculateWaitFrames();
			$this->finished = true;
		}
		
		$this->calculateWaitFrames();
	}
	
	private function calculateWaitFrames() {
		foreach ($this->wait_frames as $frame_num => $val) {
			if ($val === false) continue;
			if ($frame_num < 9) {
				if ($this->isStrike($frame_num)) {
					if ($this->roll_list[$frame_num+1][0] == 10 && isset($this->roll_list[$frame_num+2][0])) {
						$this->frame_scores[ $frame_num ] = 10 + 10 + $this->roll_list[$frame_num+2][0];
						$this->wait_frames[$frame_num] = false;
					}
					if (isset($this->roll_list[$frame_num+1][0]) && isset($this->roll_list[$frame_num+1][1])) {
						$this->frame_scores[ $frame_num ] = 10 + $this->roll_list[$frame_num+1][0] + $this->roll_list[$frame_num+1][1];
						$this->wait_frames[$frame_num] = false;
					}
				}
				if ($this->isSpare($frame_num) && isset($this->roll_list[$frame_num+1][0])) {
					$this->frame_scores[ $frame_num ] = 10 + $this->roll_list[$frame_num+1][0];
					$this->wait_frames[$frame_num] = false;
				}
			}
			elseif ($frame_num == 9) {
				if ($this->isStrike($frame_num)) {
					if (isset($this->roll_list[$frame_num+1][0]) && isset($this->roll_list[$frame_num+1][1])) {
						$this->frame_scores[ $frame_num ] = 10 + $this->roll_list[$frame_num+1][0] + $this->roll_list[$frame_num+1][1];
						$this->wait_frames[$frame_num] = false;
					}
				}
				if ($this->isSpare($frame_num) && isset($this->roll_list[$frame_num+1][0])) {
					$this->frame_scores[ $frame_num ] = 10 + $this->roll_list[$frame_num+1][0];
					$this->wait_frames[$frame_num] = false;
				}
			}
			elseif ($frame_num == 10) {
				if ($this->isStrike($frame_num)) {
					if (isset($this->roll_list[10][1]) && isset($this->roll_list[10][2])) {
						$this->frame_scores[ $frame_num ] = 10 + $this->roll_list[10][1] + $this->roll_list[10][2];
						$this->wait_frames[$frame_num] = false;
					}
				}
				if ($this->isSpare($frame_num) && isset($this->roll_list[10][2])) {
					$this->frame_scores[ $frame_num ] = 10 + $this->roll_list[10][2];
					$this->wait_frames[$frame_num] = false;
				}
			}
		}
	}
	
	private function origFrameScore($frame_num) {
		return $this->roll_list[ $frame_num ][0] + $this->roll_list[ $frame_num ][1];
	}
	private function frameCompleted($frame_num) {
		if ($frame_num < 10) {
			if (count($this->roll_list[$frame_num]) == 2) return true;
			if ($this->isStrike($frame_num)) return true;
		}
		else {
			if (count($this->roll_list[$frame_num]) == 3) return true;
			if (count($this->roll_list[$frame_num]) == 2 && $this->origFrameScore($frame_num) < 10) return true;
		}
		return false;
	}
	private function isStrike($frame_num) {
		if ($this->roll_list[ $frame_num ][0] == 10) return true;
		else return false;
	}
	private function isSpare($frame_num) {
		if ($this->roll_list[ $frame_num ][0] != 10 && $this->origFrameScore($frame_num) == 10) return true;
	}
	
	public function rollOutLoud($pins) {
		echo 'F' . $this->frame . ',R' . (count($this->roll_list[ $this->frame ])+1) . ' Rolling ' . $pins . "\n\n";
		$this->roll($pins);
	}
	
	public function getScore() {
		$score = 0;
		for ($i = 1; $i <= 10; $i++) {
			if ($this->wait_frames[$i] === true) return $score;
			$score += $this->frame_scores[$i];
		}
		return $score;
	}
	
	public function displayScoreSheet() {
		$stop_scoring = false;
		$score_increment = 0;
		//+-----+--------+
		//| 5| 4| X| X| X|
		//|  +--|  +--+--+
		//|    9|        |
		//+-----+--------+
		echo '+-----+-----+-----+-----+-----+-----+-----+-----+-----+--------+' . "\n";
		for ($i = 1; $i <= 10; $i++) {
			$frame = $this->roll_list[$i];
			echo '| ';
			if (!isset($frame[0])) {
				if ($i == 1 || isset($this->roll_list[$i-1][1]) || $this->roll_list[$i-1][0]==10) echo '#';
				else echo ' ';
			}
			elseif ($frame[0] == 10 && $i < 10) echo ' ';
			elseif ($frame[0] == 10 && $i == 10) echo 'X';
			elseif ($frame[0] == 0) echo '-';
			else echo $frame[0];
			echo '| ';
			if ($frame[0] == 10 && $i < 10) echo 'X';
			elseif ($frame[1] == 10 && $i == 10) echo 'X';
			elseif (!isset($frame[1])) {
				if (isset($frame[0])) echo '#';
				else echo ' ';
			}
			elseif (($frame[0]+$frame[1])==10 && $frame[0]!=10) echo '/';
			elseif ($frame[1] == 0) echo '-';
			else echo $frame[1];
			if ($i == 10) {
				echo '| ';
				if (isset($frame[2])) {
					if ($frame[2] == 10) echo 'X';
					else echo $frame[2];
				}
				elseif (isset($frame[1]) && !$this->frameCompleted(10)) {
					echo '#';
				}
				else {
					echo ' ';
				}
			}
		}
		echo '|' . "\n";
		echo '|  +--|  +--|  +--|  +--|  +--|  +--|  +--|  +--|  +--|  +--+--|' . "\n";
		for ($i = 1; $i <= 10; $i++) {
			if ($this->wait_frames[$i] === true) $stop_scoring = true;
			$frame_score = $this->frame_scores[$i];
			$score_increment += $frame_score;
			echo '|  ';
			if ($i == 10) echo '   ';
			if (!isset($frame_score)) echo '   ';
			elseif ($stop_scoring) echo '   ';
			else echo str_pad($score_increment, 3, ' ', STR_PAD_LEFT);
		}
		echo '|' . "\n";
		echo '+-----+-----+-----+-----+-----+-----+-----+-----+-----+--------+' . "\n";
		echo 'Score: ' . $this->getScore() . "\n\n";
	}
	
}

$game = new BowlingGame();

while ($game->getFinished() === false) {
	
	$game->displayScoreSheet();
	echo 'Roll: ';
	$pins = trim(fgets( STDIN ));
	
	try {
		$game->rollOutLoud($pins);
	}
	catch (Exception $e) {
		echo '<< ' . $e->getMessage() . ' >>' . "\n\n";
	}	
}

$game->displayScoreSheet();
echo 'Final Score: ' . $game->getScore();