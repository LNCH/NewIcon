<?php

session_start();

class Wasp
{
    public $type = '';
    public $maxHitPoints, $hitPoints, $damagePerHit = 0;
    public $lastRoundHit;

    public function __construct($type, $hitPoints, $damagePerHit)
    {
        $this->type = $type;
        $this->hitPoints = $hitPoints;
        $this->maxHitPoints = $hitPoints;
        $this->damagePerHit = $damagePerHit;
    }

    public function hit()
    {
        $this->hitPoints -= $this->damagePerHit;
    }

    public function isDead()
    {
        return $this->hitPoints <= 0;
    }
}

class Game
{
    public $wasps = [];
    public $isStarted = false;
    private $numQueens = 1;
    private $numWorkers = 5;
    private $numDrones = 8;
    public $rounds = 0;

    public function start()
    {
        $this->wasps = [];
        $this->rounds = 0;
        $this->isStarted = true;

        for ($i = 1; $i <= max($this->numQueens, $this->numWorkers, $this->numDrones); $i++) {
            if ($i <= $this->numQueens) {
                array_push($this->wasps, new Wasp('Queen', 80, 7));
            }
            if ($i <= $this->numWorkers) {
                array_push($this->wasps, new Wasp('Worker', 68, 10));
            }
            if ($i <= $this->numDrones) {
                array_push($this->wasps, new Wasp('Drone', 60, 12));
            }
        }
    }

    public function printWasps()
    {
        foreach ($this->wasps as $wasp) {
            echo "<span style='" . ($wasp->lastRoundHit == $this->rounds && $this->rounds !== 0 ? 'color: red;' : '') . "'>
                Type: {$wasp->type} - Health: {$wasp->hitPoints}</span><br />";
            echo "[".str_repeat('x', $wasp->hitPoints).str_repeat('_', $wasp->maxHitPoints - $wasp->hitPoints)."]<br /><br />";
        }
    }

    public function attackAWasp()
    {
        if (count($this->wasps) > 0) {
            $wasp = $this->wasps[$randomWaspIndex = array_rand($this->wasps)];
            $wasp->hit();
            $wasp->lastRoundHit = ++$this->rounds;

            if ($wasp->isDead() && $wasp->type == 'Queen') {
                // If we hit the Queen, and she is now dead, we won! Clear all the remaining wasps
                $result = "You killed the Queen";
                $result .= count($this->wasps) > 1 ? ', taking all the other wasps with it!' : '!';
                $result .= "<br /><br />";
                $this->wasps = [];
            } else if ($wasp->isDead()) {
                // If we hit any other wasp, and it is now dead, take it from the array
                $result = "You killed a {$wasp->type}!<br /><br />";
                unset($this->wasps[$randomWaspIndex]);
            } else {
                // Otherwise, let the player know how they did!
                $result = "You hit " . ($wasp->type == 'Queen' ? 'the Queen' : 'a ' . $wasp->type)
                    . " dealing {$wasp->damagePerHit} damage!<br /><br />";
            }
        }

        return $result ?? null;
    }

    public function offerUserOption($action, $buttonText)
    {
        echo '<form action="" method="post">
            <input type="hidden" name="action" value="'.$action.'" />
            <input type="submit" value="'.$buttonText.'" />
        </form>';
    }
}

if (!defined('APP_IN_TEST')) {
    // Retrieve or create a new game
    $game = isset($_SESSION['game']) ? $_SESSION['game'] : new Game();

    // Check if an action was made
    if (!empty($_POST)) {
        // An action was taken, either attack or restart/start, do the thing!
        switch ($_POST['action']) {
            case 'start':
                $game->start();
                break;
            case 'attack':
                echo $game->attackAWasp();
                break;
        }
    }

    // Show the game screen and available options
    if (!$game->isStarted || count($game->wasps) == 0) {
        // Game hasn't been started, or has finished, offer the option to start a new game
        $game->offerUserOption('start', 'Start New Game');
    } else {
        // Game is in play, offer the attacking option and print the game board
        $game->offerUserOption('attack', 'Hit A Wasp');
        $game->printWasps();
    }

    // Store the current game in the session
    $_SESSION['game'] = $game;
}