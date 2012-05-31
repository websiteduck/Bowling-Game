<?php
error_reporting(E_ALL ^ E_NOTICE);
require 'bowlinggame.php';

$game = new BowlingGame();

while ($game->getFinished() === false) {
	
	$game->displayScoreSheet();
	echo 'F:' . $game->getFrame();
	echo ' R:' . $game->getRoll();
	echo ' Pins: ';
	$pins = trim(fgets( STDIN ));
	
	try {
		$game->roll($pins);
	}
	catch (Exception $e) {
        $game->resetFrame();
		echo '<< ' . $e->getMessage() . ' >>' . "\n\n";
	}	
}

$game->displayScoreSheet();
echo 'Final Score: ' . $game->getScore() . "\n\n";