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

// Retrieve or create a new game
$game = isset($_SESSION['game']) ? $_SESSION['game'] : new Game();

// Check if an action was made
if (!empty($_POST)) {
    // An action was taken, either attack or restart/start, do the thing!
    switch($_POST['action']) {
        case 'start':
        case 'restart':
            $attackResult = 'Click the button below to hit a wasp!';
            $game->start();
            break;
        case 'attack':
            $attackResult = $game->attackAWasp();
            break;
    }
}

// Show the game screen and available options
if (!$game->isStarted || count($game->wasps) == 0) {
    // Game hasn't been started, or has finished, offer the option to start a new game
    $action = 'start';
    $buttonText = 'Start New Game';
} else {
    // Game is in play, offer the attacking option and print the game board
    $action = 'attack';
    $buttonText = 'Hit A Wasp';
}

// Store the current game in the session
$_SESSION['game'] = $game;

?>

<html>
    <head>
        <title>Whack a Wasp!</title>

        <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/css/styles.css" />
    </head>

    <body>
        <div class="app-wrapper">

            <div class="cloud-1">
                <div class="cloud"></div>
            </div>
            <div class="cloud-2">
                <div class="cloud"></div>
            </div>
            <div class="cloud-3">
                <div class="cloud"></div>
            </div>
            <div class="cloud-4">
                <div class="cloud"></div>
            </div>

            <div class="game-title-wrapper">
                &nbsp;
                <h1 class="game-title">Whack A Wasp!</h1>
                <span class="game-title-shadow">Whack A Wasp!</span>
            </div>

            <?php if (isset($attackResult) && $attackResult !== ''): ?>
                <p class="attack-result"><?php echo $attackResult; ?></p>
            <?php endif; ?>

            <div class="wasps-wrapper">
                <?php
                    if ($game->isStarted): foreach ($game->wasps as $wasp):

                        $healthPercentage = ($wasp->hitPoints / $wasp->maxHitPoints) * 100;

                        if ($healthPercentage > 70) {
                            $healthBarType = 'good';
                        } else if ($healthPercentage > 30) {
                            $healthBarType = 'medium';
                        } else {
                            $healthBarType = 'bad';
                        }

                        $waspWasHit = ($wasp->lastRoundHit == $game->rounds && $game->rounds !== 0)
                            ? 'just-hit' : '';

                ?>
                    <div class="cell">
                        <div class="wasp <?php echo strtolower($wasp->type) . ' ' . $waspWasHit; ?>"
                             style="animation-direction: <?php echo rand(0, 1) == 1 ? 'normal' : 'reverse'; ?>;">
                            <div class="wings"></div>
                            <div class="sting"></div>
                            <div class="body"></div>
                            <div class="head">
                                <div class="left eye"></div>
                                <div class="right eye"></div>
                            </div>
                            <div class="speech-bubble">
                                Yowch!
                            </div>
                            <div class="health-bar-wrapper">
                                <div class="health-bar">
                                    <div class="health <?php echo $healthBarType; ?>" style="width: <?php echo $healthPercentage ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <div class="action-wrapper">
                <form action="" method="post">
                    <input type="hidden" name="action" value="<?php echo $action; ?>" />
                    <button type="submit"><?php echo $buttonText; ?></button>
                </form>
            </div>

            <div class="ground"></div>
        </div> <!-- End .app-wrapper -->
    </body>
</html>
