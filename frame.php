<?php
class Frame {
    public $rolls = array();
    public $wait = false;
    public $score = 0;
    public $pins = 0;
    
    public $strike = false;
    public $spare = false;
    public $completed = false;
    public $tenth = false;
    
    public function addRoll($pins) {        
        $this->rolls[] = $pins;
        $this->pins += $pins;
        
        if (count($this->rolls) == 1 && $this->pins == 10 && $this->tenth === false) {
            $this->strike = true;
            $this->wait = true;
            $this->completed = true;
        }
        
        elseif (count($this->rolls) == 2) {
            if ($this->pins > 10) {
                if (!$this->tenth) throw new Exception('Invalid Frame');
                elseif ($this->tenth && $this->rolls[0] < 10 && $this->pins > 10) throw new Exception('Invalid Frame');
            }
            
            if ($this->tenth === false) {
                if ($this->pins == 10) {
                    $this->spare = true;
                    $this->wait = true;
                }
                $this->completed = true;
            }
            else {
                if ($this->rolls[0] < 10) {
                    if ($this->pins == 10) $this->spare = true;
                    elseif ($this->pins < 10) $this->completed = true;
                }
            }
        }
        
        elseif (count($this->rolls) == 3) {
            if ($this->rolls[0] == 10 && $this->rolls[1] < 10 && ($this->rolls[1] + $this->rolls[2]) > 10) throw new Exception('Invalid Frame');
            $this->completed = true;
        }
        
        if ($this->completed && ($this->wait === false || $this->tenth)) $this->calcScore();
    }
    
    private function calcScore() {
        foreach ($this->rolls as $roll) $this->score += $roll;
    }
    
    public function reset() {
        $this->rolls = array();
        $this->strike = false;
        $this->spare = false;
        $this->completed = false;
        $this->wait = false;
        $this->pins = 0;
        $this->score = 0;
    }
}
