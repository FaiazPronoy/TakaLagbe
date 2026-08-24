<?php
/* QR payload build and parse. */

final class QrCode
{
    /* data codewords available per version at EC level L */
    private const DATA_CW = [1=>19, 2=>34, 3=>55, 4=>80, 5=>108,
                             6=>136, 7=>156, 8=>194, 9=>232, 10=>274];

    /* [ec codewords per block, group1 blocks, group1 data, group2 blocks, group2 data] */
    private const BLOCKS = [
        1  => [7,  1, 19, 0, 0],
        2  => [10, 1, 34, 0, 0],
        3  => [15, 1, 55, 0, 0],
        4  => [20, 1, 80, 0, 0],
        5  => [26, 1, 108, 0, 0],
        6  => [18, 2, 68, 0, 0],
        7  => [20, 2, 78, 0, 0],
        8  => [24, 2, 97, 0, 0],
        9  => [30, 2, 116, 0, 0],
        10 => [18, 2, 68, 2, 69],
    ];

    /* centres of the alignment patterns for each version */
    private const ALIGN = [
        1 => [], 2 => [6,18], 3 => [6,22], 4 => [6,26], 5 => [6,30],
        6 => [6,34], 7 => [6,22,38], 8 => [6,24,42], 9 => [6,26,46], 10 => [6,28,50],
    ];

    /* remainder bits appended after the final codeword */
    private const REMAINDER = [1=>0, 2=>7, 3=>7, 4=>7, 5=>7, 6=>7,
                               7=>0, 8=>0, 9=>0, 10=>0];

    private int $version;
    private int $size;
    /** @var array<int,array<int,int|null>> null = free, 0/1 = module */
    private array $m = [];
    /** @var array<int,array<int,bool>> true = reserved function module */
    private array $fixed = [];

    /* public */

    /** @return array<int,array<int,int>> matrix of 0/1, [row][col] */
    public static function matrix(string $text): array
    {
        return (new self($text))->m;
    }

    /** Renders the code as a self contained SVG string. */
    public static function svg(string $text, int $scale = 5, int $quiet = 4): string
    {
        $m = self::matrix($text);
        $n = count($m);
        $dim = ($n + $quiet * 2) * $scale;

        $path = '';
        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                if ($m[$r][$c]) {
                    $x = ($c + $quiet) * $scale;
                    $y = ($r + $quiet) * $scale;
                    $path .= "M$x {$y}h{$scale}v{$scale}h-{$scale}z";
                }
            }
        }
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $dim . '" height="' . $dim
             . '" viewBox="0 0 ' . $dim . ' ' . $dim . '" shape-rendering="crispEdges" role="img">'
             . '<rect width="100%" height="100%" fill="#fff"/>'
             . '<path d="' . $path . '" fill="#000"/></svg>';
    }

    /** SVG wrapped in a data URI, ready for an <img src>. */
    public static function dataUri(string $text, int $scale = 5): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(self::svg($text, $scale));
    }

    /* building */

    private function __construct(string $text)
    {
        $bytes = array_values(unpack('C*', $text));
        $len   = count($bytes);

        $this->version = $this->pickVersion($len);
        $this->size    = 17 + 4 * $this->version;

        $bits = $this->encodeData($bytes, $len);
        $code = $this->addErrorCorrection($bits);

        $this->blank();
        $this->drawFunctionPatterns();
        $this->placeCodewords($code);
        $this->applyBestMask();
    }

    private function pickVersion(int $len): int
    {
        foreach (self::DATA_CW as $v => $cw) {
            $countBits = $v <= 9 ? 8 : 16;
            $needed = (int) ceil((4 + $countBits + $len * 8) / 8);
            if ($needed <= $cw) {
                return $v;
            }
        }
        throw new RuntimeException('Text is too long for this QR encoder.');
    }

    /** Mode indicator, length, payload, terminator and padding. */
    private function encodeData(array $bytes, int $len): array
    {
        $bits = [];
        $push = function (int $value, int $count) use (&$bits): void {
            for ($i = $count - 1; $i >= 0; $i--) {
                $bits[] = ($value >> $i) & 1;
            }
        };

        $push(0b0100, 4);                                  // byte mode
        $push($len, $this->version <= 9 ? 8 : 16);         // character count
        foreach ($bytes as $b) {
            $push($b, 8);
        }

        $capacityBits = self::DATA_CW[$this->version] * 8;
        for ($i = 0; $i < 4 && count($bits) < $capacityBits; $i++) {
            $bits[] = 0;                                   // terminator
        }
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;                                   // to a byte boundary
        }

        $pad = [0xEC, 0x11];
        $i = 0;
        while (count($bits) < $capacityBits) {
            $push($pad[$i++ % 2], 8);
        }

        $out = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $byte = 0;
            for ($j = 0; $j < 8; $j++) {
                $byte = ($byte << 1) | $bits[$i + $j];
            }
            $out[] = $byte;
        }
        return $out;
    }

    /* Reed-Solomon over GF(256) */

    private static array $exp = [];
    private static array $log = [];

    private static function initGf(): void
    {
        if (self::$exp) {
            return;
        }
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$exp[$i] = $x;
            self::$log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D;                 // the QR primitive polynomial
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$exp[$i] = self::$exp[$i - 255];
        }
    }

    private static function mul(int $a, int $b): int
    {
        if ($a === 0 || $b === 0) {
            return 0;
        }
        return self::$exp[self::$log[$a] + self::$log[$b]];
    }

    /** Generator polynomial of the given degree. */
    private static function generator(int $degree): array
    {
        $g = [1];
        for ($i = 0; $i < $degree; $i++) {
            $next = array_fill(0, count($g) + 1, 0);
            foreach ($g as $j => $coef) {
                $next[$j]     ^= $coef;                              // multiply by x
                $next[$j + 1] ^= self::mul($coef, self::$exp[$i]);   // multiply by a^i
            }
            $g = $next;
        }
        return $g;
    }

    private static function ecBytes(array $data, int $count): array
    {
        $gen = self::generator($count);
        $rem = array_fill(0, $count, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ array_shift($rem);
            $rem[] = 0;
            foreach ($gen as $i => $coef) {
                if ($i > 0) {
                    $rem[$i - 1] ^= self::mul($coef, $factor);
                }
            }
        }
        return $rem;
    }

    /** Splits into blocks, adds EC bytes, then interleaves. */
    private function addErrorCorrection(array $data): array
    {
        self::initGf();
        [$ecPer, $g1, $d1, $g2, $d2] = self::BLOCKS[$this->version];

        $blocks = [];
        $ecs    = [];
        $pos    = 0;
        foreach ([[$g1, $d1], [$g2, $d2]] as [$count, $size]) {
            for ($i = 0; $i < $count; $i++) {
                $chunk    = array_slice($data, $pos, $size);
                $pos     += $size;
                $blocks[] = $chunk;
                $ecs[]    = self::ecBytes($chunk, $ecPer);
            }
        }

        $out = [];
        $maxData = max(array_map('count', $blocks));
        for ($i = 0; $i < $maxData; $i++) {
            foreach ($blocks as $b) {
                if (isset($b[$i])) {
                    $out[] = $b[$i];
                }
            }
        }
        for ($i = 0; $i < $ecPer; $i++) {
            foreach ($ecs as $b) {
                $out[] = $b[$i];
            }
        }
        return $out;
    }

    /* the matrix */

    private function blank(): void
    {
        for ($r = 0; $r < $this->size; $r++) {
            $this->m[$r]     = array_fill(0, $this->size, null);
            $this->fixed[$r] = array_fill(0, $this->size, false);
        }
    }

    private function set(int $r, int $c, int $v, bool $isFixed = true): void
    {
        if ($r < 0 || $c < 0 || $r >= $this->size || $c >= $this->size) {
            return;
        }
        $this->m[$r][$c]     = $v;
        $this->fixed[$r][$c] = $isFixed;
    }

    private function drawFunctionPatterns(): void
    {
        $n = $this->size;

        /* three finder patterns with their separators */
        foreach ([[0, 0], [0, $n - 7], [$n - 7, 0]] as [$r0, $c0]) {
            for ($r = -1; $r <= 7; $r++) {
                for ($c = -1; $c <= 7; $c++) {
                    $inner = ($r >= 0 && $r <= 6 && $c >= 0 && $c <= 6);
                    $on = $inner && (
                        $r === 0 || $r === 6 || $c === 0 || $c === 6 ||
                        ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4)
                    );
                    $this->set($r0 + $r, $c0 + $c, $on ? 1 : 0);
                }
            }
        }

        /* timing patterns */
        for ($i = 8; $i < $n - 8; $i++) {
            $this->set(6, $i, ($i % 2 === 0) ? 1 : 0);
            $this->set($i, 6, ($i % 2 === 0) ? 1 : 0);
        }

        /* alignment patterns, skipping the finder corners */
        $centres = self::ALIGN[$this->version];
        foreach ($centres as $rc) {
            foreach ($centres as $cc) {
                $nearFinder = ($rc <= 8 && $cc <= 8)
                           || ($rc <= 8 && $cc >= $n - 9)
                           || ($rc >= $n - 9 && $cc <= 8);
                if ($nearFinder) {
                    continue;
                }
                for ($r = -2; $r <= 2; $r++) {
                    for ($c = -2; $c <= 2; $c++) {
                        $on = (max(abs($r), abs($c)) !== 1);
                        $this->set($rc + $r, $cc + $c, $on ? 1 : 0);
                    }
                }
            }
        }

        /* the dark module, always on */
        $this->set($n - 8, 8, 1);

        /* reserve the format information areas */
        for ($i = 0; $i <= 8; $i++) {
            if ($i !== 6) {
                $this->set(8, $i, 0);
                $this->set($i, 8, 0);
            }
        }
        for ($i = 0; $i < 8; $i++) {
            $this->set(8, $n - 1 - $i, 0);
            $this->set($n - 1 - $i, 8, 0);
        }

        /* version information, only from version 7 upward */
        if ($this->version >= 7) {
            $bits = $this->versionBits();
            for ($i = 0; $i < 18; $i++) {
                $bit = ($bits >> $i) & 1;
                $r = intdiv($i, 3);
                $c = $i % 3;
                $this->set($r, $n - 11 + $c, $bit);
                $this->set($n - 11 + $c, $r, $bit);
            }
        }
    }

    private function versionBits(): int
    {
        $v = $this->version << 12;
        $rem = $this->version;
        for ($i = 0; $i < 12; $i++) {
            $rem = ($rem << 1) ^ ((($rem >> 11) & 1) * 0x1F25);
        }
        return $v | ($rem & 0xFFF);
    }

    /** Walks the zigzag and drops the codeword bits into the free cells. */
    private function placeCodewords(array $codewords): void
    {
        $n = $this->size;
        $bits = [];
        foreach ($codewords as $b) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = ($b >> $i) & 1;
            }
        }
        for ($i = 0; $i < self::REMAINDER[$this->version]; $i++) {
            $bits[] = 0;
        }

        $idx = 0;
        $up  = true;
        for ($right = $n - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right = 5;                 // column 6 is the timing pattern
            }
            for ($step = 0; $step < $n; $step++) {
                $r = $up ? ($n - 1 - $step) : $step;
                foreach ([$right, $right - 1] as $c) {
                    if ($this->m[$r][$c] === null) {
                        $this->m[$r][$c]     = $bits[$idx++] ?? 0;
                        $this->fixed[$r][$c] = false;
                    }
                }
            }
            $up = !$up;
        }
    }

    private static function maskOn(int $mask, int $r, int $c): bool
    {
        switch ($mask) {
            case 0: return ($r + $c) % 2 === 0;
            case 1: return $r % 2 === 0;
            case 2: return $c % 3 === 0;
            case 3: return ($r + $c) % 3 === 0;
            case 4: return (intdiv($r, 2) + intdiv($c, 3)) % 2 === 0;
            case 5: return (($r * $c) % 2 + ($r * $c) % 3) === 0;
            case 6: return ((($r * $c) % 2 + ($r * $c) % 3) % 2) === 0;
            default: return (((($r + $c) % 2) + ($r * $c) % 3) % 2) === 0;
        }
    }

    private function applyBestMask(): void
    {
        $base = $this->m;
        $best = null;
        $bestScore = PHP_INT_MAX;

        for ($mask = 0; $mask < 8; $mask++) {
            $this->m = $base;
            for ($r = 0; $r < $this->size; $r++) {
                for ($c = 0; $c < $this->size; $c++) {
                    if (!$this->fixed[$r][$c] && self::maskOn($mask, $r, $c)) {
                        $this->m[$r][$c] ^= 1;
                    }
                }
            }
            $this->drawFormat($mask);
            $score = $this->penalty();
            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $this->m;
            }
        }
        $this->m = $best;
    }

    private function drawFormat(int $mask): void
    {
        $n = $this->size;
        $data = (0b01 << 3) | $mask;          // 01 = error correction level L
        $rem = $data;
        for ($i = 0; $i < 10; $i++) {
            $rem = ($rem << 1) ^ ((($rem >> 9) & 1) * 0x537);
        }
        $bits = (($data << 10) | ($rem & 0x3FF)) ^ 0x5412;

        /* first copy, wrapped around the top-left finder */
        for ($i = 0; $i <= 5; $i++) {
            $this->m[$i][8] = ($bits >> $i) & 1;
        }
        $this->m[7][8] = ($bits >> 6) & 1;
        $this->m[8][8] = ($bits >> 7) & 1;
        $this->m[8][7] = ($bits >> 8) & 1;
        for ($i = 9; $i < 15; $i++) {
            $this->m[8][14 - $i] = ($bits >> $i) & 1;
        }
        /* second copy, split between the other two finders */
        for ($i = 0; $i < 8; $i++) {
            $this->m[8][$n - 1 - $i] = ($bits >> $i) & 1;
        }
        for ($i = 8; $i < 15; $i++) {
            $this->m[$n - 15 + $i][8] = ($bits >> $i) & 1;
        }
        $this->m[$n - 8][8] = 1;              // the dark module
    }

    /** The four penalty rules from the specification. */
    private function penalty(): int
    {
        $n = $this->size;
        $score = 0;

        /* rule 1: runs of five or more of the same colour */
        for ($pass = 0; $pass < 2; $pass++) {
            for ($a = 0; $a < $n; $a++) {
                $run = 1;
                $prev = -1;
                for ($b = 0; $b < $n; $b++) {
                    $v = $pass === 0 ? $this->m[$a][$b] : $this->m[$b][$a];
                    if ($v === $prev) {
                        $run++;
                    } else {
                        if ($run >= 5) {
                            $score += 3 + ($run - 5);
                        }
                        $run = 1;
                        $prev = $v;
                    }
                }
                if ($run >= 5) {
                    $score += 3 + ($run - 5);
                }
            }
        }

        /* rule 2: two by two blocks of the same colour */
        for ($r = 0; $r < $n - 1; $r++) {
            for ($c = 0; $c < $n - 1; $c++) {
                $v = $this->m[$r][$c];
                if ($v === $this->m[$r][$c+1]
                 && $v === $this->m[$r+1][$c]
                 && $v === $this->m[$r+1][$c+1]) {
                    $score += 3;
                }
            }
        }

        /* rule 3: the finder-like pattern 1011101 with four light modules */
        $a = [1,0,1,1,1,0,1,0,0,0,0];
        $b = [0,0,0,0,1,0,1,1,1,0,1];
        for ($pass = 0; $pass < 2; $pass++) {
            for ($x = 0; $x < $n; $x++) {
                for ($y = 0; $y <= $n - 11; $y++) {
                    $hitA = $hitB = true;
                    for ($k = 0; $k < 11; $k++) {
                        $v = $pass === 0 ? $this->m[$x][$y+$k] : $this->m[$y+$k][$x];
                        if ($v !== $a[$k]) { $hitA = false; }
                        if ($v !== $b[$k]) { $hitB = false; }
                        if (!$hitA && !$hitB) { break; }
                    }
                    if ($hitA) { $score += 40; }
                    if ($hitB) { $score += 40; }
                }
            }
        }

        /* rule 4: how far the dark share is from half */
        $dark = 0;
        for ($r = 0; $r < $n; $r++) {
            $dark += array_sum($this->m[$r]);
        }
        $pct = $dark * 100 / ($n * $n);
        $score += (int) (abs($pct - 50) / 5) * 10;

        return $score;
    }
}
