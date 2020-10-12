<?php

define('APP_IN_TEST', true);

include_once 'index.php';

$tests = [
    'A wasp has a type' => function () {
        $wasp = new Wasp('Queen', 80, 7);
        return $wasp->type === 'Queen';
    },

    'A wasp sets hitpoints on init' => function () {
        $wasp = new Wasp('Queen', 80, 7);
        return $wasp->hitPoints === 80;
    },

    'A wasp sets max hitpoints on init' => function () {
        $wasp = new Wasp('Queen', 80, 7);
        return $wasp->maxHitPoints === 80;
    },

    'A wasp sets its damage per hit on init' => function () {
        $wasp = new Wasp('Queen', 80, 7);
        return $wasp->damagePerHit === 7;
    },

    'A wasp reduces its hitpoints when hit' => function () {
        $wasp = new Wasp('Queen', 80, 7);
        $wasp->hit();
        return $wasp->hitPoints === 73;
    },

    'The isDead method returns false if a wasp is has hitpoints above 0' => function () {
        $wasp = new Wasp('Queen', 80, 7);
        return $wasp->isDead() === false;
    },

    'The isDead method returns true if a wasp has hitpoints below 0' => function () {
        $wasp = new Wasp('Queen', 6, 7);
        $wasp->hit();
        return $wasp->isDead() === true;
    },

    'An unstarted game has no wasps' => function () {
        $game = new Game();
        return count($game->wasps) === 0;
    },

    'A started game has an array of wasps' => function () {
        $game = new Game();
        $game->start();
        return count($game->wasps) > 0;
    },

    'A started game can attack a wasp and return a string of the result' => function () {
        $game = new Game();
        $game->start();
        $result = $game->attackAWasp();

        return is_string($result);
    },

    'An unstarted game cannot attack a wasp' => function () {
        $game = new Game();
        $result = $game->attackAWasp();

        return is_null($result);
    },

    'Killing a wasp removes it from the list' => function () {
        $game = new Game();
        $game->wasps = [new Wasp('Drone', 10, 12)];

        $game->attackAWasp();

        return count($game->wasps) === 0;
    }
];

// Run all tests and output results to screen
echo "<table><thead><tr><th>Test</th><th>Result</th></tr></thead><tbody>";
foreach ($tests as $test => $function) {
    echo "<tr>";
    echo "<td>" . $test . "</td>";
    echo "<td>" . ($function() === true ? 'Passed' : 'Failed') . "</td>";
    echo "</tr>";
}
echo "</tbody></table>";