<?php

class Board {
    private $figures = [];
    private $lastMoveWasBlack = null; // Track the color of the last move

    public function __construct() {
        $this->set_position();
    }

    public function move($move) {
        if (!preg_match('/^([a-h])(\d)-([a-h])(\d)$/', $move, $match)) {
            throw new \Exception("Incorrect move");
        }

        $xFrom = $match[1];
        $yFrom = $match[2];
        $xTo   = $match[3];
        $yTo   = $match[4];

        if (!isset($this->figures[$xFrom][$yFrom])) {
            throw new \Exception("Invalid move $move");
        }

        $figure = $this->figures[$xFrom][$yFrom];
        $isBlack = $figure->isBlack();

        // Check if it's the correct player's turn
        if ($this->lastMoveWasBlack === $isBlack) {
            throw new \Exception("Invalid move $move");
        }

        // Validate pawn moves
        if ($figure instanceof Pawn) {
            $this->validatePawnMove($xFrom, $yFrom, $xTo, $yTo, $isBlack, $move);
        }

        $this->lastMoveWasBlack = $isBlack;
        
        if (isset($this->figures[$xFrom][$yFrom])) {
            $this->figures[$xTo][$yTo] = $this->figures[$xFrom][$yFrom];
        }
        unset($this->figures[$xFrom][$yFrom]);
    }

    /**
     * Validates pawn moves according to chess rules
     * 
     * @param string $xFrom Source column (a-h)
     * @param int $yFrom Source row (1-8)
     * @param string $xTo Destination column (a-h)
     * @param int $yTo Destination row (1-8)
     * @param bool $isBlack Whether the pawn is black or white
     * @param string $move Original move notation for error messages
     * @throws \Exception If the move violates pawn movement rules
     */
    private function validatePawnMove($xFrom, $yFrom, $xTo, $yTo, $isBlack, $move) {
        // Convert columns from letters to numbers for easier calculations
        $xFromNum = ord($xFrom) - ord('a') + 1;
        $xToNum = ord($xTo) - ord('a') + 1;
        
        // Direction of movement depends on pawn color
        $direction = $isBlack ? -1 : 1;
        
        // Get the expected start position for determining if it's the first move
        $startRow = $isBlack ? 7 : 2;
        
        // Check if target position has a figure (for capturing)
        $hasTargetFigure = isset($this->figures[$xTo][$yTo]);
        
        // Calculate horizontal and vertical differences
        $verticalDiff = $yTo - $yFrom;
        $horizontalDiff = abs($xToNum - $xFromNum);
        
        // Validate forward movement (non-capturing)
        if ($xFrom === $xTo) {
            // Moving forward - can't capture vertically
            if ($hasTargetFigure) {
                throw new \Exception("Invalid move $move");
            }
            
            // Check if the pawn is moving in the correct direction
            if ($verticalDiff != $direction && ($verticalDiff != 2 * $direction || $yFrom != $startRow)) {
                throw new \Exception("Invalid move $move");
            }
            
            // Moving two squares - must be first move and path must be clear
            if ($verticalDiff == 2 * $direction) {
                $middleY = $yFrom + $direction;
                if (isset($this->figures[$xFrom][$middleY])) {
                    throw new \Exception("Invalid move $move");
                }
            }
            
            // Maximum forward movement is 2 squares (on first move) or 1 square (subsequently)
            if (abs($verticalDiff) > 2) {
                throw new \Exception("Invalid move $move");
            }
        }
        // Validate diagonal movement (capturing)
        else if ($horizontalDiff == 1 && $verticalDiff == $direction) {
            // Diagonal move - must be capturing
            if (!$hasTargetFigure) {
                throw new \Exception("Invalid move $move");
            }
        }
        // Any other move pattern is invalid
        else {
            throw new \Exception("Invalid move $move");
        }
    }

    public function dump() {
        for ($y = 8; $y >= 1; $y--) {
            echo "$y ";
            for ($x = 'a'; $x <= 'h'; $x++) {
                if (isset($this->figures[$x][$y])) {
                    echo $this->figures[$x][$y];
                } else {
                    echo '-';
                }
            }
            echo "\n";
        }
        echo "  abcdefgh\n";
    }

    /**
     * @return void
     */
    public function set_position(): void
    {
        $this->figures['a'][1] = new Rook(false);
        $this->figures['b'][1] = new Knight(false);
        $this->figures['c'][1] = new Bishop(false);
        $this->figures['d'][1] = new Queen(false);
        $this->figures['e'][1] = new King(false);
        $this->figures['f'][1] = new Bishop(false);
        $this->figures['g'][1] = new Knight(false);
        $this->figures['h'][1] = new Rook(false);

        $this->figures['a'][2] = new Pawn(false);
        $this->figures['b'][2] = new Pawn(false);
        $this->figures['c'][2] = new Pawn(false);
        $this->figures['d'][2] = new Pawn(false);
        $this->figures['e'][2] = new Pawn(false);
        $this->figures['f'][2] = new Pawn(false);
        $this->figures['g'][2] = new Pawn(false);
        $this->figures['h'][2] = new Pawn(false);

        $this->figures['a'][7] = new Pawn(true);
        $this->figures['b'][7] = new Pawn(true);
        $this->figures['c'][7] = new Pawn(true);
        $this->figures['d'][7] = new Pawn(true);
        $this->figures['e'][7] = new Pawn(true);
        $this->figures['f'][7] = new Pawn(true);
        $this->figures['g'][7] = new Pawn(true);
        $this->figures['h'][7] = new Pawn(true);

        $this->figures['a'][8] = new Rook(true);
        $this->figures['b'][8] = new Knight(true);
        $this->figures['c'][8] = new Bishop(true);
        $this->figures['d'][8] = new Queen(true);
        $this->figures['e'][8] = new King(true);
        $this->figures['f'][8] = new Bishop(true);
        $this->figures['g'][8] = new Knight(true);
        $this->figures['h'][8] = new Rook(true);
    }
}
