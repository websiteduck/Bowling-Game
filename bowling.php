<?php
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
		echo '<< ' . $e->getMessage() . ' >>' . "\n\n";
	}	
}

$game->displayScoreSheet();
echo 'Final Score: ' . $game->getScore() . "\n\n";