<?php

date_default_timezone_set('UTC');

class CNGN
{

    public $FO = [];
    public $sigma = "";
    public $condition = "";
    public $results = [];
    public $messages = [];
    public $x_of = [];
    public $fn_x = [];
    public $f = "";
    public $g = "";
    public $vars;
    public $seq = [];
    function __construct(float $index_cnt)
    {
        $this->messages[] = "Error: ";
        $this->register_vars($index_cnt);
    }

    public function string_replace_x($replacements, &$template)
    {
        $replacements = $this->vars;
        return preg_replace_callback(
            '/{x(.+?)}/',
            function ($matches) use ($replacements) {
                return $replacements[$matches[1]];
            },
            $template
        );
    }

    public function string_replace_n($replacements, &$template)
    {
        return preg_replace_callback(
            '/{z(.+?)}/',
            function ($matches) use ($replacements) {
                return $replacements[$matches[1]];
            },
            $template
        );
    }

    public function string_replace_b(string &$template, array $sequence)
    {
        $this->seq = $sequence;
        return preg_replace_callback(
            '/{c(.+?),(.+?)}/',
            function ($matches) use ($sequence) {
                $this->string_replace_x($sequence, $matches[2]);
                if (!is_numeric($matches[2])) {
                    $this->msg(0, "There must be 2 parameters to {c}. Example: {c101101,3}.<br>Yours: {c" . $matches[1] . "," . $matches[2] . "}");
                    exit(0);
                }
                if (bindec($matches[1]) > 55 && bindec($matches[1]) < 58) {
                    return $this->calculus((string) $matches[1], $this->seq);
                } else if (bindec($matches[1]) == 58) {
                    if (is_array($this->seq[0])) {
                        return $this->calculus((string) $matches[1], $this->seq);
                    } else {
                        return $this->calculus((string) $matches[1], [$this->seq]);
                    }
                }
                return $this->x((string) $matches[1], (int) trim($matches[2], " "));
            },
            $template
        );
    }

    public function load_vars(array $placements): void
    {
        foreach ($placements as $k => $v) {
            $hex = dechex($k);
            $this->vars[$hex] = $v;
        }
        return;
    }

    public function load_fn_x(array $placements): void
    {
        foreach ($placements as $k => $v) {
            $hex = dechex($k);
            $this->fn_x[$hex] = $v;
        }
        return;
    }

    public function register_vars($index_cnt)
    {
        $x = 0;
        while ($x < $index_cnt) {
            $hex = dechex($x);
            $this->vars[$hex] = false;
            $x++;
        }
    }

    public function register_fn_x($index_cnt)
    {
        $x = 0;
        while ($x < $index_cnt) {
            $hex = dechex($x);
            $this->fn_x[$hex] = false;
            $x++;
        }
    }

    public function add_vars(float $index_cnt)
    {
        $x = count($this->vars);
        $s = $x;
        do {
            $hex = dechex($s);
            $this->vars[$hex] = false;
            $s++;
        } while ($s < $x + $index_cnt);
    }

    public function add_fn_x(float $index_cnt)
    {
        $x = count($this->fn_x);
        $s = $x;
        do {
            $hex = dechex($s);
            $this->fn_x[$hex] = false;
            $s++;
        } while ($s < $x + $index_cnt);
    }

    /*
     *
     * Parse string of {xFA} x-hex values
     * and replace with $vars values 
     * 
     */
    public function mathParse(string $formula, array $sequence = [])
    {
        if (count($sequence) == 0)
            $sequence = $this->vars;
        if ($formula == "") {
            $this->msg(0, 'Empty string given, try mathParse(string)\n\tUse a valid {x00} to place the variable\n\tThese are keys in $vars');
            return false;
        }
        $string = $formula;
        $x = 0;
        $string = $this->stringParse($string);
        // Parse {x00}
        while (strpos($string, "{c") !== false) {
            $string = $this->string_replace_b($string, $sequence);
        }
        return eval ("return $string;");
    }

    /*
     *
     * Parse string of {xFA} x-hex values
     * and replace with $vars values 
     * 
     */
    public function stringParse(string $string)
    {
        if ($string == "") {
            $this->msg(0, 'Empty string given, try stringParse(string)\n\tUse a valid {x00} to place the variable\n\tThese are keys in $vars');
            return false;
        }
        while (strpos($string, "{x") !== false) {
            $string = $this->string_replace_x($this->vars, $string);
        }
        return $string;
    }

    /*
     *
     * $string .= message at $msg_id
     * 
     */
    public function msg(float $msg_id, string $arb_msg = "")
    {
        echo $this->messages[$msg_id] . $arb_msg;
        return;
    }

    /**
     * the X function. Because the other letters are dumb.
     * 
     * use a space between each binary command
     * 
     */
    private function x(string $j, int $i)
    { {
            $t = $j;
            if ($t == "000000") // s1 * s2
            {
                return cosh((float) $this->seq[$i]);
            } else if ($t == "000001") // s1 * s2 
            {
                return cos((float) $this->seq[$i]);
            } else if ($t == "000010") // s1 * s2 
            {
                return sinh((float) $this->seq[$i]);
            } else if ($t == "000011") // s1 * s2 
            {
                return sin((float) $this->seq[$i]);
            } else if ($t == "000100") // s1 * s2 
            {
                return tanh((float) $this->seq[$i]);
            } else if ($t == "000101") // s1 * s2 
            {
                return tan((float) $this->seq[$i]);
            } else if ($t == "000110") // secant
            {
                return 1 / sin((float) $this->seq[$i]);
            } else if ($t == "000111") // cosecant
            {
                return 1 / cos((float) $this->seq[$i]);
            } else if ($t == "001000") // cotangent
            {
                return 1 / tan((float) $this->seq[$i]);
            } else if ($t == "001001") // arcsine
            {
                return asin((float) $this->seq[$i]);
            } else if ($t == "001010") // arccosine
            {
                return acos((float) $this->seq[$i]);
            } else if ($t == "001011") // arctangent
            {
                return atan((float) $this->seq[$i]);
            } else if ($t == "001100") // inverse sine
            {
                return 1 / (1 / cos((float) $this->seq[$i]));
            } else if ($t == "001101") // inverse cosine
            {
                return sin((float) $this->seq[$i]) / cos((float) $this->seq[$i]);
            } else if ($t == "001110") // inverse cotangent
            {
                return cos((float) $this->seq[$i]) / sin((float) $this->seq[$i]);
            } else if ($t == "001111") // constant rule
            {
                return 0;
            } else if ($t == "010000") // s1 * s2 
            {
                return $this->sum_rule((float) $this->seq[$i]);
            } else if ($t == "010001") // s1 - s2
            {
                return $this->diff_rule((float) $this->seq[$i]);
            } else if ($t == "010010" && sizeof($this->seq) >= 2) // s1 ^ s2
            {
                return $this->power_rule(array_slice($this->seq, 0, 2));
            } else if ($t == "010011") // s1 * s2
            {
                return $this->product_rule((float) $this->seq[$i]);
            } else if ($t == "010100") // s1 / s2
            {
                return $this->quotient_rule((float) $this->seq[$i]);
            } else if ($t == "010101") // s1 * s2
            {
                return $this->chain_rule((float) $this->seq[$i]);
            } else if ($t == "010110") // ^2
            {
                return pow((float) $this->seq[$i], (float) $this->seq[$i + 1]);
            } else if ($t == "010111") // s1 + s2
            {
                return " + ";
            } else if ($t == "011000") // s1 - s2
            {
                return " - ";
            } else if ($t == "011001") // s1 * s2
            {
                return " * ";
            } else if ($t == "011010") // $s / $s2
            {
                return " / ";
            } else if ($t == "011100") // s1 > s2
            {
                return $this->condition .= ((float) $this->seq[$i] > $this->seq[$i + 1]);
            } else if ($t == "011101") // s1 < s2
            {
                return $this->condition .= ((float) $this->seq[$i] < $this->seq[$i + 1]);
            } else if ($t == "011110") // s1 >= s2
            {
                return $this->condition .= ((float) $this->seq[$i] >= $this->seq[$i + 1]);
            } else if ($t == "011111") // s1 <= s2
            {
                return $this->condition .= ((float) $this->seq[$i] <= $this->seq[$i + 1]);
            } else if ($t == "100000") // s1 != s2
            {
                return $this->condition .= ((float) $this->seq[$i] != $this->seq[$i + 1]);
            } else if ($t == "100001") // s1 == s2
            {
                return $this->condition .= ((float) $this->seq[$i] == $this->seq[$i + 1]);
            } else if ($t == "100010") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] == $this->seq[$i + 1]);
            } else if ($t == "100011") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] != $this->seq[$i + 1]);
            } else if ($t == "100100") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] > $this->seq[$i + 1]);
            } else if ($t == "100101") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] < $this->seq[$i + 1]);
            } else if ($t == "100110") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] >= $this->seq[$i + 1]);
            } else if ($t == "100111") // s1 && s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) && $this->seq[$i] <= $this->seq[$i + 1]);
            } else if ($t == "101000") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] == $this->seq[$i + 1]);
            } else if ($t == "101001") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] != $this->seq[$i + 1]);
            } else if ($t == "101010") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] > $this->seq[$i + 1]);
            } else if ($t == "101011") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] < $this->seq[$i + 1]);
            } else if ($t == "101100") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] >= $this->seq[$i + 1]);
            } else if ($t == "101101") // s1 || s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) || $this->seq[$i] <= $this->seq[$i + 1]);
            } else if ($t == "101110") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] == $this->seq[$i + 1]);
            } else if ($t == "101111") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] != $this->seq[$i + 1]);
            } else if ($t == "110000") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] > $this->seq[$i + 1]);
            } else if ($t == "110001") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] < $this->seq[$i + 1]);
            } else if ($t == "110010") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] >= $this->seq[$i + 1]);
            } else if ($t == "110011") // s1 ^ s2
            {
                return $this->condition .= ((bool) substr($this->condition, -1) ^ $this->seq[$i] <= $this->seq[$i + 1]);
            } else if ($t == "110100") // factorial
            {
                return $this->mathFact((float) $this->seq[$i]);
            } else if ($t == "110101") // ln()
            {
                return exp((float) $this->seq[$i]);
            } else if ($t == "110110") // ln()
            {
                return log((float) $this->seq[$i]);
            } else if ($t == "110111") // log_base()
            {
                return log((float) $this->seq[$i], (float) $this->seq[$i + 1]);
            } else if ($t == "111000") // integrand()
            {
                return $this->calculus("000000", $this->seq);
            } else if ($t == "111001") // integral()
            {
                return $this->calculus("000001", $this->seq);
            } else if ($t == "111010") // find_integral()
            {
                return $this->calculus("000010", $this->seq);
            } else if ($t == "111010") // find_integral()
            {
                return $this->calculus("000011", $this->seq);
            } else if ($t == "111011") // cond_prob() // uses $this->condition
            {
                return $this->cond_prob($this->seq[$i]);
            } else if ($t == "111100") // bayes_prob() // uses $this->condition as prior probability
            {
                return $this->bayes_prob($this->seq[$i], $this->seq[$i + 1]);
            } else if ($t == "111101") // is_prime
            {
                return $this->is_prime($this->seq[$i]);
            } else if ($t == "111110") // XOR
            {
                return $this->bitw_cmp($this->seq);
            }

        }
        if (strlen($this->sigma) > 0)
            return eval ("return $this->sigma;");
    }

    public function bitw_cmp(array $lr)
    {
        $aw = $lr[0];
        $lb = $lr[1];
        $rb = $lr[2];
        if (decbin($lr[1]) == $lr[1])
            $lb = bindec($lr[1]);
        if (decbin($lr[2]) == $lr[2])
            $rb = bindec($lr[2]);
        if ($aw == "00")
            return $lb ^ $rb;
        else if ($aw == "01")
            return $lb & $rb;
        else if ($aw == "10")
            return $lb | $rb;
        else if ($aw == "11")
            return $lb >> $rb;
        else if ($aw == "100")
            return $lb << $rb;
    }

    public function cond_prob(string $vals)
    {
        $PA = substr_count($this->condition, "1");
        $PB = substr_count($vals, "1");

        return (int) $PA / $PB;
    }

    public function bayes_prob(string $AB, string $A)
    {
        $PB = substr_count($this->condition, "1") / strlen($this->condition);
        $PA = substr_count($A, "1") / strlen($A);

        return ($AB * $PB) / $PA;
    }

    public function is_prime($number)
    {
        // 1 is not prime
        if ($number == 1) {
            return false;
        }
        // 2 is the only even prime number
        if ($number == 2) {
            return true;
        }
        // square root algorithm speeds up testing of bigger prime numbers
        $x = sqrt($number);
        $x = floor($x);
        for ($i = 2; $i <= $x; ++$i) {
            if ($number % $i == 0) {
                break;
            }
        }

        if ($x == $i - 1) {
            return true;
        } else {
            return false;
        }
    }

    public function calculus(string $t, array $sequence)
    { {
            if ($t == "000000") // integrand
            {
                return $this->integrand($sequence);
            } else if ($t == "000001") // integral // Make seq[$i] a subarray & seq[1] the average height of perimeter 
            {
                return $this->integral($sequence);
            } else if ($t == "000010") // integral 
            {
                return $this->find_integral($sequence);
            } else if ($t == "000011") // integral 
            {
                return $this->differential($sequence);
            }
        }
    }

    public function integral(array $sequence)
    {
        $length = array_sum($sequence);
        $avg_height = array_sum($sequence) / count($sequence);
        return ($length * $avg_height);
    }

    /**
     * 
     * Integrand ([[secant, y = base/min, height = base/max], [sec, y, high]])
     * 
     */
    public function find_integral(array $sequence)
    {
        $h = [];
        $sum = [];
        foreach ($sequence as $k => $v) {
            $midpoint = (int) $v[0] / 2;
            $incise = abs((int) $v[2] - (int) $v[1]);
            $perimeter = ($midpoint * 2) + ($incise * 2);
            $length = $perimeter / 2;
            $length += $incise / 2;
            $sum[] = $length;
            $h[] = (int) $v[2];
        }
        $integral = $this->integral($sum);
        return $integral;
    }


    public function zeta_loss(int $sub_ = 0, int $add_ = 0, int $flip_ = 0)
    {
        $pi = 3.1415926535897932384626433832795;
        $seq = [
            0.618,
            0.56418957569775374239,
            $pi,
            3
        ];
        $tr = [];
        $tf = 1;
        $cnt = 0;
        $exp = 0;
        $c = 1;
        for ($z = 0; count($tr) < 50; $z += 1) {
            $seq[3] = $this->integrand($seq);
            echo $seq[3] . " ";
            $seq[3] += pow(($z) * 0.56418957569775374239, 2);
            $tf = ceil(($seq[3] + 4) / ($tf + 1));
            $this->is_prime($tf) ? array_push($tr, $tf) : false;
            echo $this->is_prime($tf) ? '<b style="color:darkblue">' . ($tf) . '</b> ' : "!";
            $tr = array_unique($tr);
            $c++;
        }
        echo count($tr) . " $c/$tf " . $z;
    }

    /**
     * 
     * Integrand ([secant, y = base/min, height = base/max])
     * 
     */
    public function integrand(array $sequence)
    {
        $midpoint = $sequence[0] / 2;
        $incise = abs(intval($sequence[2]) - intval($sequence[1]));
        $perimeter = ($midpoint * 2) + ($incise * 2);
        $length = $perimeter / 2;
        $length += $incise / 2;
        $length--;
        return $length;
    }

    /**
     * 
     * Differential ([secant, y = base/min, height = base/max])
     * 
     */
    public function differential(array $sequence)
    {
        $integrand = (float) $this->integrand($sequence);
        $integral = (float) $this->integral([
            (float) $sequence[0],
            $integrand,
            $integrand
        ]);
        $wave_width = sqrt(max(0.0, 3 * $integral));
        $secant = $wave_width - (2 * $integrand);
        $denominator = $integrand + 1.0;

        return $denominator == 0.0
            ? 0.0
            : $secant / $denominator;
    }

    /**
     * 
     * Derive ([secant, y = base/min, height = base/max])
     * 
     */
    public function derive(array $sequence)
    {
        $midpoint = $sequence[0] / $sequence[3];
        $incise = abs(intval($sequence[2]) - intval($sequence[1]));
        $perimeter = ($midpoint * 2) + ($incise * 2);
        $length = $perimeter / 2;
        $length += $incise / 2;
        return $sequence[3] / $length;
    }
    /**
     * 
     * Factorials
     * 
     */
    function mathFact($s)
    {
        $r = (int) $s;

        if ($r < 2)
            $r = 1;
        else {
            for ($i = $r - 1; $i > 1; $i--)
                $r = $r * $i;
        }
        return $r;
    }

    /*
     *
     * get function of g() -- Use {x} wherever you need your variable
     * 
     */
    public function f(float $x)
    {
        if ($this->f_ == "") {
            $this->msg(0, "No function given, try set_f_of(string x)\n\tUse {x} to place the variable.");
            exit(0);
        }
        $v = ($this->stringParse($this->f_));
        return eval ("return $v;");
    }

    /*
     *
     * set function of f() -- Use {x} wherever you need your variable
     * 
     */
    public function set_f_of(string $ev)
    {
        $this->f_ = $ev;
    }

    /*
     *
     * get function of g() -- Use {x} wherever you need your variable
     * 
     */
    public function g(float $x)
    {
        if ($this->g_ == "") {
            $this->msg(0, "No function given, try set_g_of(string x)\n\tUse {x} to place the variable");
            exit(0);
        }
        $v = ($this->stringParse($this->g_));

        return eval ("return $v;");
    }

    /*
     *
     * set function of g()
     * 
     */
    public function set_g_of(string $ev)
    {
        $this->g_ = $ev;
    }

    /*
     *
     * Condition d/dx [f(x)+g(x)]
     * 
     */
    public function sum_rule(float $sequence)
    {
        $tmp1 = $this->f((float) $sequence);
        $tmp2 = $this->g((float) $sequence);

        return $tmp1 + $tmp2;
    }

    /*
     *
     * Condition d/dx [f(x)-g(x)]
     * 
     */
    public function diff_rule(float $sequence)
    {
        $tmp1 = $this->f((float) $sequence);
        $tmp2 = $this->g((float) $sequence);

        return $tmp1 - $tmp2;
    }

    /*
     *
     * Condition d/dx [x^n]
     * 
     */
    public function power_rule(array $sequence)
    {
        $tmp = $sequence;

        return (float) (pow((int) $tmp[0], (int) $tmp[1] - 1) * (float) $tmp[1]);
    }

    /*
     *
     * Condition d/dx [f(x)g(x)]
     * 
     */
    public function product_rule(float $sequence)
    {

        // f'(x)                // f(x)
        $tmp_f = $this->f((float) $sequence);
        // g'(x)                // g(x)
        $tmp_g = $this->g((float) $sequence);

        $tmp_ff = $this->f((float) $tmp_f);
        $tmp_gg = $this->g((float) $tmp_g);
        $final1a = $tmp_ff * $tmp_g;
        $final1b = $tmp_f * $tmp_gg;
        return $final1b + $final1a;
    }

    /*
     *
     * Condition d/dx [f(g(x))]
     * 
     */
    public function chain_rule(float $sequence)
    {

        // g'(x)                // g(x)
        $tmp_g = (float) ($this->g($this->seq[0]));

        // f'(x)                // f(x)
        $tmp_f = (float) ($this->f($tmp_g));

        $tmp_ff = ($this->f($tmp_f));
        $tmp_gg = ($this->g($tmp_f));

        return $tmp_ff * $tmp_gg;
    }

    /*
     *
     * Condition d/dx [f(x)/g(x)]
     * 
     */
    public function quotient_rule(float $sequence)
    {

        $tmp_f = (float) $this->f((float) $this->sequence);
        $tmp_g = (float) $this->g((float) $this->sequence);

        $tmp_ff = (float) $this->f($tmp_f);
        $tmp_gg = (float) $this->f($tmp_g);

        $final1a = $tmp_ff * $tmp_g;
        $final1b = $tmp_f * $tmp_gg;

        $final2 = $final1a * $final1b;
        $answer = $final2 / ($tmp_g * $tmp_g);
        return ($answer);
    }

    function bitcoin(string $btc_json, int $day_cnt = 15, $data_column = 1, $date_column = 0, bool $flip_on_boundary = false)
    {
        $first_loop_backup_rows = 30 * 24 * 12; // one month of 5-minute candles
        $projection_seed_rows = 49;
        $csvFilePath = $btc_json;
        $readCsvRows = static function (string $path): array {
            $file = @fopen($path, 'r');
            if ($file === false) return [];
            $rows = [];
            while (($line = fgetcsv($file)) !== false) {
                $rows[] = $line;
            }
            fclose($file);
            return $rows;
        };

        $buildSequence = static function (array $rows) use ($day_cnt): array {
            $seq = [];
            $day_before = 0;
            $y = 1;
            foreach ($rows as $value) {
                $t_close = $value[4] ?? null;
                $t_day = $value[0] ?? null;
                if ($t_close === null || $t_day === null) continue;
                if ($y < 2) {
                    $y += $day_cnt;
                    $day_before = $t_close;
                    continue;
                }
                $seq[] = [$y, $day_before, $day_before, $t_day];
                $day_before = $t_close;
                $y += $day_cnt;
            }
            return $seq;
        };

        $out = 1;
        $exp = 1;
        $base = 0.0;
        $computeMetrics = function (array $key) use (&$out, &$exp, &$base): array {
            $vals = $key;
            $inc_real = $vals[1];
            array_pop($vals);
            $integrand = $this->integrand($key);
            $integral = $this->integral([$key[0], $integrand, $integrand]);
            $integral_wall = sqrt(max(0, 3 * $integral));
            $wall_percent = $integral_wall == 0 ? 0 : $integrand / $integral_wall;
            $wall_bias = $wall_percent - 0.25;
            $vals[] = $integrand;
            $c = $this->differential($key);
            $derived = $this->derive($vals);
            $lo = $derived / $vals[3] / $c;
            $lo *= $derived / 2;
            while ($lo <= 0.999) {
                $lo *= 1.01;
            }
            $short_low = abs($lo);
            $short_low = (($lo * intval($vals[2]) / 10) - intval($vals[3]));
            $short_low = ($base + round($short_low / $out, 2) * 2) - (1 * $exp);
            $exp = 1;
            while ($short_low > pow(10, $exp) && $exp < 3) {
                $out = pow(10, $exp++);
            }
            return [
                'inc_real' => $inc_real,
                'integrand' => $integrand,
                'integral' => $integral,
                'wall_percent' => $wall_percent,
                'wall_bias' => $wall_bias,
                'differential' => $c,
                'derived' => $derived,
                'short_low' => $short_low,
            ];
        };

        $data = $readCsvRows($csvFilePath);
        $required_sequence_rows = max($first_loop_backup_rows, $projection_seed_rows);
        $required_csv_rows = $required_sequence_rows + 2; // prior row + header/guard row
        if (count($data) > $required_csv_rows) {
            $data = array_slice($data, -$required_csv_rows);
        }
        $seq = $buildSequence($data);
        $header_html = "<tr><td style='width:150;margin-top:5px;'>Long Form Date </td><td> Differential </td><td>Integrand</td><td> Integral </td><td>Low</td><td>Result (Boolean)</td></tr>";
        if (!$seq) {
            return [$header_html, 0.0, $csvFilePath, ['right' => 0, 'total' => 0]];
        }

        $historical_start = max(0, count($seq) - $first_loop_backup_rows);
        $historical_html = $header_html;
        $previous_short_low = null;
        $previous_wall_bias = null;
        $inc_imaginary = 0.0;
        $inc_last = 0;
        $saved = [0, 0];
        $correct = 0;
        $historical_low_seed = "+";
        $historical_rb_seed = "+";
        $historical_short_seed = null;
        $historical_wall_bias_seed = null;
        $historical_saved_seed = [0, 0];
        $historical_inc_last_seed = 0;
        $historical_last_index = count($seq) - 1;
        $short_low = 0.0;

        $historical_loop = function () use (
            &$seq,
            $historical_start,
            $historical_last_index,
            &$historical_html,
            &$previous_short_low,
            &$previous_wall_bias,
            &$inc_imaginary,
            &$inc_last,
            &$saved,
            &$correct,
            &$historical_low_seed,
            &$historical_rb_seed,
            &$historical_short_seed,
            &$historical_wall_bias_seed,
            &$historical_saved_seed,
            &$historical_inc_last_seed,
            &$short_low,
            $computeMetrics
        ): void {
            for ($i = $historical_last_index; $i >= $historical_start; $i--) {
                $key = $seq[$i];
                $metrics = $computeMetrics($key);
                $short_low = $metrics['short_low'];
                $bool1 = $previous_short_low !== null && $short_low < $previous_short_low ? "-" : "+";
                $bool2 = $previous_wall_bias !== null && $metrics['wall_bias'] < $previous_wall_bias ? "-" : "+";
                if ($bool2 == $bool1) {
                    $correct++;
                    $colored = "green";
                } else {
                    $colored = "red";
                }

                $historical_html .= "<tr><td style='width:150;'>" . $key[3] . " </td><td> "
                    . $metrics['differential'] . "  </td><td>" . $metrics['derived']
                    . " </td><td> " . $metrics['integral'] . "</td>";
                $real = "<td>" . $bool1 . $key[1] . "</td>";
                if ($i != $historical_last_index) {
                    $single_guess = ($bool1 === '-' && $bool2 === '-') ? '%' : $bool1;
                    $guess_change = $previous_wall_bias === null
                        ? 0
                        : abs(($metrics['wall_bias'] - $previous_wall_bias) * 100);
                    $historical_html .= $real . "<td data-left='" . $bool1
                        . "' data-right='" . $bool2
                        . "' data-wall-percent='" . $metrics['wall_percent']
                        . "' data-wall-bias='" . $metrics['wall_bias']
                        . "' data-change='" . $guess_change . "'>" . $single_guess . "</td></tr>";
                } else {
                    $historical_html .= $real . "<td></td></tr>";
                }

                $next_saved = [($inc_imaginary - $short_low), ($metrics['inc_real'])];
                $inc_imaginary = $short_low;
                $inc_last = $metrics['inc_real'];
                if ($i == $historical_last_index) {
                    $historical_low_seed = $bool1;
                    $historical_rb_seed = $bool2;
                    $historical_short_seed = $short_low;
                    $historical_wall_bias_seed = $metrics['wall_bias'];
                    $historical_saved_seed = $next_saved;
                    $historical_inc_last_seed = (int)$metrics['inc_real'];
                }
                $previous_short_low = $short_low;
                $previous_wall_bias = $metrics['wall_bias'];
            }
        };

        $historical_loop();

        $base = $short_low;
        $projection_html = '';
        $second_loop_boundary = (int)floor(time() / 300) * 300;
        $future_seq_start = count($seq);
        $last_seq_entry = $seq[$future_seq_start - 1] ?? [1, 0, 0, gmdate('Y-m-d\\TH:i:s\\Z', $second_loop_boundary)];
        $previous_future_short_low = $historical_short_seed;
        $previous_future_wall_bias = $historical_wall_bias_seed;
        $seeded_future_low = $historical_low_seed;
        $seeded_future_rb = $historical_rb_seed;
        $saved = $historical_saved_seed;
        $inc_last = $historical_inc_last_seed;
        $inc_imaginary = $historical_short_seed ?? 0;
        $future_seed_index = $projection_seed_rows - 1;
        $future_correct = 0;
        $future_total = 0;

        $projection_loop = function () use (
            &$seq,
            $future_seq_start,
            $last_seq_entry,
            $second_loop_boundary,
            $flip_on_boundary,
            &$projection_html,
            &$saved,
            &$inc_last,
            &$inc_imaginary,
            &$previous_future_short_low,
            &$previous_future_wall_bias,
            $seeded_future_low,
            $seeded_future_rb,
            $future_seed_index,
            &$future_correct,
            &$future_total,
            $day_cnt,
            $computeMetrics
        ): void {
            for ($x = $future_seed_index; $x >= 0; $x--) {
                $future_timestamp = gmdate('Y-m-d\\TH:i:s\\Z', $second_loop_boundary + (($x + 1) * 300));
                $key = $x == 0
                    ? $last_seq_entry
                    : $seq[$future_seq_start - $x - 1];
                $metrics = $computeMetrics($key);
                $short_low = $metrics['short_low'];
                if ($x == $future_seed_index) {
                    $bool1 = $seeded_future_low;
                    $bool2 = $seeded_future_rb;
                } else {
                    $bool1 = $previous_future_short_low !== null && $short_low < $previous_future_short_low ? "-" : "+";
                    $bool2 = $previous_future_wall_bias !== null && $metrics['wall_bias'] < $previous_future_wall_bias ? "-" : "+";
                }
                $saved_delta = is_array($saved) && array_key_exists(0, $saved) ? $saved[0] : 0;
                $real = "<td>" . $bool1 . abs(intval($inc_last) / 100 + intval($saved_delta) / 100) . "</td>";
                $projection_html .= "<tr><td style='width:150;'>" . $future_timestamp . " </td>"
                    . "<td> " . $metrics['differential'] . "  </td>" . $real;

                if ($flip_on_boundary) {
                    $bool2 = $bool2 === "+" ? "-" : "+";
                }
                $future_total++;
                if ($bool2 === $bool1) {
                    $future_correct++;
                }
                $colored = $bool2 === $bool1 ? "green" : "red";
                $future_change = $previous_future_wall_bias === null
                    ? 0
                    : abs(($metrics['wall_bias'] - $previous_future_wall_bias) * 100);
                $projection_html .= "<td style='color:black;background-color:" . $colored
                    . "' data-left='" . $bool1
                    . "' data-right='" . $bool2
                    . "' data-integrand='" . $metrics['integrand']
                    . "' data-integral='" . $metrics['integral']
                    . "' data-wall-percent='" . $metrics['wall_percent']
                    . "' data-wall-bias='" . $metrics['wall_bias']
                    . "' data-change='" . $future_change . "'>"
                    . $bool2 . $future_change . "</td></tr>";

                $saved = [($inc_imaginary - $short_low), ($metrics['inc_real'])];
                $inc_imaginary = $short_low;
                $inc_last = intval($metrics['inc_real']);
                $previous_future_short_low = $short_low;
                $previous_future_wall_bias = $metrics['wall_bias'];
                $seq[] = [((float)$key[0] + $day_cnt), $metrics['inc_real'], $short_low, $future_timestamp];
            }
        };

        $projection_loop();
        $string = $projection_html . $historical_html;
        $forward_accuracy = $future_total > 0 ? round($future_correct / $future_total * 100, 2) : 0.0;
        return [$string, $forward_accuracy, $csvFilePath, ['right' => $future_correct, 'total' => $future_total]];
    }
}


// ================================================
// Market Wave Dashboard
// ================================================

$request_params = array_merge($_GET, $_POST);
$market_type = isset($request_params['market_type']) && $request_params['market_type'] === 'stock'
    ? 'stock'
    : 'crypto';

$ticker = isset($request_params['symbol'])
    ? strtoupper(trim((string) $request_params['symbol']))
    : ($market_type === 'crypto' ? 'BTC-USD' : 'AAPL');

$percentSetting = static function (string $name, float $default): float {
    $value = isset($_GET[$name]) && is_numeric($_GET[$name])
        ? (float)$_GET[$name]
        : $default;
    return min(25.0, max(0.01, $value));
};

// Deterministic paper-trading thresholds. These control simulation state only;
// no broker, order route, or live execution endpoint is connected.
$break_buy_drop_pct = $percentSetting('break_buy', 0.50);
$break_take_gain_pct = $percentSetting('break_gain', 0.50);
$break_stop_loss_pct = $percentSetting('break_loss', 0.50);
if ($market_type === 'crypto' && !str_contains($ticker, '-')) {
    $ticker .= '-USD';
}
function symbolPresetSettings(string $marketType, string $symbol): array
{
    $normalizedMarket = strtolower(trim($marketType));
    $normalizedSymbol = strtoupper(trim($symbol));
    if ($normalizedMarket === 'crypto' && $normalizedSymbol === 'BTC-USD') {
        return [
            'buy_multiplier' => 0.90,
            'sell_multiplier' => 0.80,
            'trust_percent' => 90.0,
        ];
    }
    return [
        'buy_multiplier' => 1.10,
        'sell_multiplier' => 1.00,
        'trust_percent' => 75.0,
    ];
}
$symbol_preset = symbolPresetSettings($market_type, $ticker);
$buy_multiplier = isset($request_params['buy_multiplier']) && is_numeric($request_params['buy_multiplier'])
    ? max(0.10, min(5.00, (float)$request_params['buy_multiplier']))
    : (float)$symbol_preset['buy_multiplier'];
$sell_multiplier = isset($request_params['sell_multiplier']) && is_numeric($request_params['sell_multiplier'])
    ? max(0.10, min(5.00, (float)$request_params['sell_multiplier']))
    : (float)$symbol_preset['sell_multiplier'];
$trust_percent = isset($request_params['trust_percent']) && is_numeric($request_params['trust_percent'])
    ? max(1.0, min(100.0, (float)$request_params['trust_percent']))
    : (float)$symbol_preset['trust_percent'];

if (!preg_match('/^[A-Z0-9.\-^]{1,12}$/', $ticker)) {
    $ticker = 'TSLA';
}

function cpanelCronRegistryPath(): string
{
    return __DIR__ . '/cron_targets.json';
}

function cpanelCronCommandsPath(): string
{
    return __DIR__ . '/cpanel_cron_commands.txt';
}

function cpanelCronWriterPath(): string
{
    $preferred = __DIR__ . '/cpanel_cron_writer.php';
    if (is_file($preferred)) return $preferred;
    return __DIR__ . '/wsl_portfolio_cron.php';
}

function deleteLocalFileIfExists(string $path): void
{
    $path = trim($path);
    if ($path === '' || !is_file($path)) return;
    @unlink($path);
}

/** Read a small local .env file without exposing its contents to the page. */
function localEnvironmentValue(string $name): string
{
    $environmentValue = getenv($name);
    if (is_string($environmentValue) && trim($environmentValue) !== '') {
        return trim($environmentValue);
    }

    $envPath = __DIR__ . '/.env';
    if (!is_file($envPath) || !is_readable($envPath)) return '';
    $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) return '';
    foreach ($lines as $line) {
        $line = trim((string)$line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!preg_match('/^' . preg_quote($name, '/') . '\s*=\s*(.*)$/', $line, $match)) continue;
        $value = trim((string)$match[1]);
        if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
            $value = substr($value, 1, -1);
        }
        return $value;
    }
    return '';
}

function walletResetPasswordMatches(string $configuredPassword, string $providedPassword): bool
{
    if ($configuredPassword === '' || $providedPassword === '') return false;
    if (str_starts_with($configuredPassword, '$2y$') || str_starts_with($configuredPassword, '$argon2')) {
        return password_verify($providedPassword, $configuredPassword);
    }
    return hash_equals($configuredPassword, $providedPassword);
}

function loadLocalJsonArray(string $path): array
{
    if (!is_file($path)) return [];
    $raw = @file_get_contents($path);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    return is_array($decoded) ? $decoded : [];
}

function saveLocalJsonArray(string $path, array $data): bool
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($json) || $json === '') return false;
    return @file_put_contents($path, $json, LOCK_EX) !== false;
}

function paperWalletBootstrapPath(string $directory, string $marketType, string $symbol): string
{
    $safeMarketType = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower(trim($marketType)));
    $safeSymbol = preg_replace('/[^A-Z0-9._-]+/i', '-', strtoupper(trim($symbol)));
    $safeMarketType = is_string($safeMarketType) && $safeMarketType !== '' ? $safeMarketType : 'market';
    $safeSymbol = is_string($safeSymbol) && $safeSymbol !== '' ? $safeSymbol : 'SYMBOL';
    return rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $safeMarketType . '-' . $safeSymbol . '-wallet-bootstrap.json';
}

function loadOrCreatePaperWalletBootstrap(
    string $statePath,
    string $marketType,
    string $symbol,
    string $startedAt,
    float $entryPrice,
    float $startingPot = 10000.0
): array {
    $cashSeed = $startingPot / 2.0;
    $assetSeed = $startingPot - $cashSeed;
    $default = [
        'market_type' => strtolower(trim($marketType)),
        'symbol' => strtoupper(trim($symbol)),
        'started_at' => trim($startedAt),
        'entry_price' => $entryPrice,
        'starting_pot' => $startingPot,
        'cash_seed' => $cashSeed,
        'asset_seed' => $assetSeed,
        'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
    ];

    $raw = @file_get_contents($statePath);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    if (is_array($decoded)) {
        $bootstrap = array_merge($default, $decoded);
        $bootstrap['started_at'] = trim((string)($bootstrap['started_at'] ?? ''));
        $bootstrap['entry_price'] = is_numeric($bootstrap['entry_price'] ?? null) ? (float)$bootstrap['entry_price'] : 0.0;
        $bootstrap['starting_pot'] = is_numeric($bootstrap['starting_pot'] ?? null) ? (float)$bootstrap['starting_pot'] : $startingPot;
        $bootstrap['cash_seed'] = is_numeric($bootstrap['cash_seed'] ?? null)
            ? max(0.0, (float)$bootstrap['cash_seed'])
            : $cashSeed;
        $bootstrap['asset_seed'] = is_numeric($bootstrap['asset_seed'] ?? null)
            ? max(0.0, (float)$bootstrap['asset_seed'])
            : $assetSeed;
        if ($bootstrap['started_at'] !== '' && $bootstrap['entry_price'] > 0.0) {
            return $bootstrap;
        }
    }

    if ($default['started_at'] !== '' && $default['entry_price'] > 0.0) {
        @file_put_contents($statePath, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
    return $default;
}

function detectedIndexBaseUrl(): string
{
    $host = trim((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
    if ($host === '') return '';
    $https = strtolower((string)($_SERVER['HTTPS'] ?? ''));
    $scheme = ($https !== '' && $https !== 'off' && $https !== '0') ? 'https' : 'http';
    $scriptName = trim((string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    if ($scriptName === '') $scriptName = '/index.php';
    if ($scriptName[0] !== '/') $scriptName = '/' . $scriptName;
    return $scheme . '://' . $host . $scriptName;
}

function loadCpanelCronRegistry(string $registryPath): array
{
    if (!file_exists($registryPath)) return ['targets' => []];
    $raw = @file_get_contents($registryPath);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    $registry = is_array($decoded) ? $decoded : ['targets' => []];
    $registry['targets'] = is_array($registry['targets'] ?? null) ? $registry['targets'] : [];
    return $registry;
}

function saveCpanelCronRegistry(string $registryPath, array $registry): bool
{
    $json = json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($json) || $json === '') return false;
    return @file_put_contents($registryPath, $json, LOCK_EX) !== false;
}

function removeTrackedTargetFromRegistry(string $registryPath, string $marketType, string $symbol): array
{
    $registry = loadCpanelCronRegistry($registryPath);
    $filtered = [];
    foreach ($registry['targets'] as $target) {
        if (!is_array($target)) continue;
        $targetMarketType = strtolower(trim((string)($target['market_type'] ?? '')));
        $targetSymbol = strtoupper(trim((string)($target['symbol'] ?? '')));
        if ($targetMarketType === strtolower(trim($marketType)) && $targetSymbol === strtoupper(trim($symbol))) {
            continue;
        }
        $filtered[] = $target;
    }
    $registry['targets'] = array_values($filtered);
    saveCpanelCronRegistry($registryPath, $registry);
    return $registry['targets'];
}

function registerCpanelCronTarget(
    string $registryPath,
    string $baseUrl,
    string $marketType,
    string $symbol,
    float $buyMultiplier,
    float $sellMultiplier,
    float $trustPercent,
    float $breakBuy,
    float $breakGain,
    float $breakLoss
): array {
    if ($baseUrl === '' || ($marketType !== 'crypto' && $marketType !== 'stock') || $symbol === '') {
        return [];
    }

    $registry = loadCpanelCronRegistry($registryPath);
    $targets = [];
    foreach ($registry['targets'] as $target) {
        if (!is_array($target)) continue;
        $targetMarketType = strtolower(trim((string)($target['market_type'] ?? '')));
        $targetSymbol = strtoupper(trim((string)($target['symbol'] ?? '')));
        if ($targetMarketType === '' || $targetSymbol === '') continue;
        $targets[$targetMarketType . '|' . $targetSymbol] = [
            'market_type' => $targetMarketType,
            'symbol' => $targetSymbol,
            'base_url' => trim((string)($target['base_url'] ?? $baseUrl)),
            'buy_multiplier' => is_numeric($target['buy_multiplier'] ?? null)
                ? max(0.10, min(5.00, (float)$target['buy_multiplier']))
                : $buyMultiplier,
            'sell_multiplier' => is_numeric($target['sell_multiplier'] ?? null)
                ? max(0.10, min(5.00, (float)$target['sell_multiplier']))
                : $sellMultiplier,
            'trust_percent' => is_numeric($target['trust_percent'] ?? null)
                ? max(1.0, min(100.0, (float)$target['trust_percent']))
                : $trustPercent,
            'break_buy' => trim((string)($target['break_buy'] ?? number_format($breakBuy, 2, '.', ''))),
            'break_gain' => trim((string)($target['break_gain'] ?? number_format($breakGain, 2, '.', ''))),
            'break_loss' => trim((string)($target['break_loss'] ?? number_format($breakLoss, 2, '.', ''))),
            'updated_at' => trim((string)($target['updated_at'] ?? '')),
        ];
    }

    $key = $marketType . '|' . $symbol;
    $targets[$key] = [
        'market_type' => $marketType,
        'symbol' => $symbol,
        'base_url' => $baseUrl,
        'buy_multiplier' => max(0.10, min(5.00, $buyMultiplier)),
        'sell_multiplier' => max(0.10, min(5.00, $sellMultiplier)),
        'trust_percent' => number_format(max(1.0, min(100.0, $trustPercent)), 2, '.', ''),
        'break_buy' => number_format($breakBuy, 2, '.', ''),
        'break_gain' => number_format($breakGain, 2, '.', ''),
        'break_loss' => number_format($breakLoss, 2, '.', ''),
        'updated_at' => gmdate('Y-m-d\TH:i:s\Z'),
    ];

    uasort($targets, static function (array $a, array $b): int {
        return strcmp($a['market_type'] . '|' . $a['symbol'], $b['market_type'] . '|' . $b['symbol']);
    });
    $registry['targets'] = array_values($targets);
    saveCpanelCronRegistry($registryPath, $registry);
    return $registry['targets'];
}

function writeCpanelCronCommandsSnapshot(string $outputPath, string $writerPath, string $registryPath, array $targets): bool
{
    $resolvedWriterPath = realpath($writerPath) ?: $writerPath;
    $resolvedRegistryPath = realpath($registryPath) ?: $registryPath;
    $phpPath = '/usr/local/bin/php';
    $boundarySchedule = '5,10,15,20,25,30,35,40,45,50,55 * * * *';
    $lines = [];
    $lines[] = 'Auto-generated cPanel cron commands';
    $lines[] = 'Generated at: ' . gmdate('Y-m-d H:i:s') . ' UTC';
    $lines[] = '';
    $lines[] = 'Per-symbol cron entries';
    $lines[] = '----------------------';
    foreach ($targets as $target) {
        if (!is_array($target)) continue;
        $targetMarketType = strtolower(trim((string)($target['market_type'] ?? '')));
        $targetSymbol = strtoupper(trim((string)($target['symbol'] ?? '')));
        $targetBaseUrl = trim((string)($target['base_url'] ?? ''));
        if (($targetMarketType !== 'crypto' && $targetMarketType !== 'stock') || $targetSymbol === '' || $targetBaseUrl === '') continue;
        $lines[] = $boundarySchedule . ' ' . $phpPath . ' -q ' . "'" . str_replace("'", "'\"'\"'", $resolvedWriterPath) . "'"
            . ' --registry=' . "'" . str_replace("'", "'\"'\"'", $resolvedRegistryPath) . "'"
            . ' --market-type=' . "'" . str_replace("'", "'\"'\"'", $targetMarketType) . "'"
            . ' --symbol=' . "'" . str_replace("'", "'\"'\"'", $targetSymbol) . "'"
            . ' --base-url=' . "'" . str_replace("'", "'\"'\"'", $targetBaseUrl) . "'"
            . ' >/dev/null 2>&1';
    }
    $lines[] = '';
    $lines[] = 'Registered targets';
    $lines[] = '------------------';
    foreach ($targets as $target) {
        if (!is_array($target)) continue;
        $lines[] = '[' . strtoupper((string)($target['market_type'] ?? '')) . ' ' . strtoupper((string)($target['symbol'] ?? '')) . '] '
            . (string)($target['base_url'] ?? '')
            . ' trust=' . (string)($target['trust_percent'] ?? '')
            . ' break_buy=' . (string)($target['break_buy'] ?? '')
            . ' break_gain=' . (string)($target['break_gain'] ?? '')
            . ' break_loss=' . (string)($target['break_loss'] ?? '');
    }
    $lines[] = '';
    $lines[] = 'Notes';
    $lines[] = '-----';
    $lines[] = '- Each symbol gets its own cron line instead of one shared list-driven cron task.';
    $lines[] = '- Boundary cron runs on five-minute marks except minute 00.';
    $lines[] = '- Market cache refreshes are throttled by index.php to about every 30 seconds when the page or scheduler checks it.';
    $lines[] = '- index.php refreshes this file whenever tracked symbols change or a symbol is revisited.';
    $text = implode(PHP_EOL, $lines) . PHP_EOL;
    return @file_put_contents($outputPath, $text, LOCK_EX) !== false;
}

function loadTrackedIndexTargets(string $primaryPath, string $fallbackPath): array
{
    $sources = [$primaryPath, $fallbackPath];
    $targetsByKey = [];
    foreach ($sources as $path) {
        $decoded = loadLocalJsonArray($path);
        $targets = is_array($decoded['targets'] ?? null) ? $decoded['targets'] : [];
        foreach ($targets as $target) {
            if (!is_array($target)) continue;
            $marketType = strtolower(trim((string)($target['market_type'] ?? '')));
            $symbol = strtoupper(trim((string)($target['symbol'] ?? '')));
            if (($marketType !== 'crypto' && $marketType !== 'stock') || $symbol === '') continue;
            $targetsByKey[$marketType . '|' . $symbol] = [
                'market_type' => $marketType,
                'symbol' => $symbol,
                'base_url' => trim((string)($target['base_url'] ?? '')),
                'buy_multiplier' => is_numeric($target['buy_multiplier'] ?? null)
                    ? max(0.10, min(5.00, (float)$target['buy_multiplier']))
                    : null,
                'sell_multiplier' => is_numeric($target['sell_multiplier'] ?? null)
                    ? max(0.10, min(5.00, (float)$target['sell_multiplier']))
                    : null,
                'trust_percent' => is_numeric($target['trust_percent'] ?? null)
                    ? max(1.0, min(100.0, (float)$target['trust_percent']))
                    : null,
                'break_buy' => is_numeric($target['break_buy'] ?? null)
                    ? max(0.01, min(25.0, (float)$target['break_buy']))
                    : null,
                'break_gain' => is_numeric($target['break_gain'] ?? null)
                    ? max(0.01, min(25.0, (float)$target['break_gain']))
                    : null,
                'break_loss' => is_numeric($target['break_loss'] ?? null)
                    ? max(0.01, min(25.0, (float)$target['break_loss']))
                    : null,
                'updated_at' => trim((string)($target['updated_at'] ?? '')),
            ];
        }
    }
    uasort($targetsByKey, static function (array $a, array $b): int {
        return strcmp($a['market_type'] . '|' . $a['symbol'], $b['market_type'] . '|' . $b['symbol']);
    });
    return array_values($targetsByKey);
}

function removeTrackedIndexTargetFiles(string $primaryPath, string $fallbackPath, string $marketType, string $symbol): array
{
    $normalizedMarketType = strtolower(trim($marketType));
    $normalizedSymbol = strtoupper(trim($symbol));
    if (($normalizedMarketType !== 'crypto' && $normalizedMarketType !== 'stock') || $normalizedSymbol === '') {
        return loadTrackedIndexTargets($primaryPath, $fallbackPath);
    }

    foreach ([$primaryPath, $fallbackPath] as $path) {
        $registry = loadCpanelCronRegistry($path);
        $filtered = [];
        foreach ($registry['targets'] as $target) {
            if (!is_array($target)) continue;
            $targetMarketType = strtolower(trim((string)($target['market_type'] ?? '')));
            $targetSymbol = strtoupper(trim((string)($target['symbol'] ?? '')));
            if ($targetMarketType === $normalizedMarketType && $targetSymbol === $normalizedSymbol) {
                continue;
            }
            $filtered[] = $target;
        }
        $registry['targets'] = array_values($filtered);
        saveCpanelCronRegistry($path, $registry);
    }

    return loadTrackedIndexTargets($primaryPath, $fallbackPath);
}

function trackedTargetForSymbol(array $trackedIndexTargets, string $marketType, string $ticker): ?array
{
    $normalizedMarketType = strtolower(trim($marketType));
    $normalizedTicker = strtoupper(trim($ticker));
    foreach ($trackedIndexTargets as $trackedTarget) {
        if (!is_array($trackedTarget)) continue;
        $trackedMarketType = strtolower(trim((string)($trackedTarget['market_type'] ?? '')));
        $trackedSymbol = strtoupper(trim((string)($trackedTarget['symbol'] ?? '')));
        if ($trackedMarketType === $normalizedMarketType && $trackedSymbol === $normalizedTicker) {
            return $trackedTarget;
        }
    }
    return null;
}

function buildTrackedLinkGroups(array $trackedIndexTargets, string $currentMarketType, string $currentTicker): array
{
    $trackedCryptoLinks = [];
    $trackedStockLinks = [];
    foreach ($trackedIndexTargets as $trackedTarget) {
        if (!is_array($trackedTarget)) continue;
        $trackedMarketType = (string)($trackedTarget['market_type'] ?? '');
        $trackedSymbol = (string)($trackedTarget['symbol'] ?? '');
        if ($trackedSymbol === '') continue;
        $trackedQuery = [
            'market_type' => $trackedMarketType,
            'symbol' => $trackedSymbol,
            'run_analysis' => '1',
        ];
        if (is_numeric($trackedTarget['buy_multiplier'] ?? null)) {
            $trackedQuery['buy_multiplier'] = number_format((float)$trackedTarget['buy_multiplier'], 2, '.', '');
        }
        if (is_numeric($trackedTarget['sell_multiplier'] ?? null)) {
            $trackedQuery['sell_multiplier'] = number_format((float)$trackedTarget['sell_multiplier'], 2, '.', '');
        }
        if (is_numeric($trackedTarget['trust_percent'] ?? null)) {
            $trackedQuery['trust_percent'] = number_format((float)$trackedTarget['trust_percent'], 2, '.', '');
        }
        if (is_numeric($trackedTarget['break_buy'] ?? null)) {
            $trackedQuery['break_buy'] = number_format((float)$trackedTarget['break_buy'], 2, '.', '');
        }
        if (is_numeric($trackedTarget['break_gain'] ?? null)) {
            $trackedQuery['break_gain'] = number_format((float)$trackedTarget['break_gain'], 2, '.', '');
        }
        if (is_numeric($trackedTarget['break_loss'] ?? null)) {
            $trackedQuery['break_loss'] = number_format((float)$trackedTarget['break_loss'], 2, '.', '');
        }
        $trackedLink = './index.php?' . http_build_query($trackedQuery);
        $trackedEntry = [
            'market' => $trackedMarketType,
            'symbol' => $trackedSymbol,
            'href' => $trackedLink,
            'active' => $trackedMarketType === $currentMarketType && strtoupper($trackedSymbol) === strtoupper($currentTicker),
        ];
        $trackedEntry['role_label'] = '';
        $trackedEntry['status_label'] = '';
        $trackedEntry['aria_current'] = $trackedEntry['active'] ? 'page' : null;
        if ($trackedMarketType === 'stock') $trackedStockLinks[] = $trackedEntry;
        else $trackedCryptoLinks[] = $trackedEntry;
    }
    return [
        'crypto' => $trackedCryptoLinks,
        'stock' => $trackedStockLinks,
        'marquee' => array_merge($trackedCryptoLinks, $trackedStockLinks),
    ];
}

function applyTrackedLinkPrices(array $trackedLinkGroups, array $quotes, string $tickerDirectory): array
{
    foreach (['crypto', 'stock', 'marquee'] as $groupKey) {
        if (!is_array($trackedLinkGroups[$groupKey] ?? null)) continue;
        foreach ($trackedLinkGroups[$groupKey] as $index => $trackedLink) {
            if (!is_array($trackedLink)) continue;
            $symbol = strtoupper(trim((string)($trackedLink['symbol'] ?? '')));
            $price = null;
            if ($symbol !== '' && is_array($quotes[$symbol] ?? null) && is_numeric($quotes[$symbol]['price'] ?? null)) {
                $price = (float)$quotes[$symbol]['price'];
            } elseif ($symbol !== '') {
                $summaryPath = rtrim($tickerDirectory, '/\\') . DIRECTORY_SEPARATOR . $symbol . '-cron-summary.json';
                $summary = loadLocalJsonArray($summaryPath);
                if (is_numeric($summary['currentPrice'] ?? null)) {
                    $price = (float)$summary['currentPrice'];
                }
            }
            $trackedLink['price_value'] = $price;
            $trackedLink['price_label'] = is_numeric($price) && $price > 0
                ? '$' . number_format((float)$price, 2)
                : '—';
            $trackedLinkGroups[$groupKey][$index] = $trackedLink;
        }
    }
    return $trackedLinkGroups;
}

function buildTrackedDashboardCards(array $trackedIndexTargets, string $currentMarketType, string $currentTicker, string $tickerDirectory): array
{
    $cards = [];
    foreach ($trackedIndexTargets as $trackedTarget) {
        if (!is_array($trackedTarget)) continue;
        $trackedMarketType = strtolower(trim((string)($trackedTarget['market_type'] ?? '')));
        $trackedSymbol = strtoupper(trim((string)($trackedTarget['symbol'] ?? '')));
        if (($trackedMarketType !== 'crypto' && $trackedMarketType !== 'stock') || $trackedSymbol === '') continue;

        $trackedQuery = [
            'market_type' => $trackedMarketType,
            'symbol' => $trackedSymbol,
            'run_analysis' => '1',
        ];
        foreach ([
            'buy_multiplier' => 2,
            'sell_multiplier' => 2,
            'trust_percent' => 2,
            'break_buy' => 2,
            'break_gain' => 2,
            'break_loss' => 2,
        ] as $field => $precision) {
            if (is_numeric($trackedTarget[$field] ?? null)) {
                $trackedQuery[$field] = number_format((float)$trackedTarget[$field], $precision, '.', '');
            }
        }

        $summaryPath = rtrim($tickerDirectory, '/\\') . DIRECTORY_SEPARATOR . $trackedSymbol . '-cron-summary.json';
        $walletPath = rtrim($tickerDirectory, '/\\') . DIRECTORY_SEPARATOR . $trackedSymbol . '-model-wallet-state.json';
        $livePath = rtrim($tickerDirectory, '/\\') . DIRECTORY_SEPARATOR . $trackedSymbol . '-live-output.json';
        $summary = loadLocalJsonArray($summaryPath);
        $wallet = normalizeTraderDisplayState(loadLocalJsonArray($walletPath));
        $live = loadLocalJsonArray($livePath);
        $liveTrader = is_array($live['autoBreakTrader'] ?? null)
            ? normalizeTraderDisplayState($live['autoBreakTrader'])
            : [];

        $price = null;
        if (is_numeric($summary['currentPrice'] ?? null)) {
            $price = (float)$summary['currentPrice'];
        } elseif (is_numeric($live['currentPrice'] ?? null)) {
            $price = (float)$live['currentPrice'];
        }

        $equity = is_numeric($wallet['equity_value'] ?? null)
            ? (float)$wallet['equity_value']
            : (is_numeric($liveTrader['equity_value'] ?? null) ? (float)$liveTrader['equity_value'] : null);
        $netPnl = is_numeric($wallet['net_pnl'] ?? null)
            ? (float)$wallet['net_pnl']
            : (is_numeric($summary['paperProfit'] ?? null) ? (float)$summary['paperProfit'] : null);
        $position = strtoupper(trim((string)($wallet['position'] ?? ($liveTrader['position'] ?? 'flat'))));
        if ($position === '') $position = 'FLAT';
        $signal = trim((string)($wallet['display_action'] ?? ($liveTrader['display_action'] ?? 'WATCHING')));
        if ($signal === '') $signal = 'WATCHING';
        $updatedAt = trim((string)($summary['updatedAt'] ?? ($summary['writtenAt'] ?? 'Unavailable')));
        $lastTrade = is_array($wallet['last_trade'] ?? null) ? $wallet['last_trade'] : [];
        $lastTradeAction = strtoupper(trim((string)($lastTrade['action'] ?? '')));
        $lastTradeTime = trim((string)($lastTrade['time'] ?? ''));
        $lastTradeLabel = $lastTradeAction !== ''
            ? $lastTradeAction . ($lastTradeTime !== '' ? ' • ' . $lastTradeTime : '')
            : 'No settled trade yet';
        $accuracy = is_numeric($summary['accuracy'] ?? null) ? (float)$summary['accuracy'] : null;

        $cards[] = [
            'market' => $trackedMarketType,
            'symbol' => $trackedSymbol,
            'href' => './index.php?' . http_build_query($trackedQuery),
            'active' => $trackedMarketType === $currentMarketType && $trackedSymbol === strtoupper($currentTicker),
            'aria_current' => $trackedMarketType === $currentMarketType && $trackedSymbol === strtoupper($currentTicker) ? 'page' : null,
            'price_label' => is_numeric($price) ? '$' . number_format((float)$price, 2) : '—',
            'equity_label' => is_numeric($equity) ? '$' . number_format((float)$equity, 2) : '—',
            'net_label' => is_numeric($netPnl)
                ? (($netPnl >= 0 ? '+' : '-') . '$' . number_format(abs((float)$netPnl), 2))
                : '—',
            'net_class' => is_numeric($netPnl) ? ((float)$netPnl >= 0 ? 'good' : 'low') : 'medium',
            'position_label' => $position,
            'signal_label' => $signal,
            'updated_label' => $updatedAt !== '' ? $updatedAt : 'Unavailable',
            'last_trade_label' => $lastTradeLabel,
            'trust_label' => is_numeric($trackedTarget['trust_percent'] ?? null)
                ? number_format((float)$trackedTarget['trust_percent'], 2) . '%'
                : '—',
            'buy_label' => is_numeric($trackedTarget['buy_multiplier'] ?? null)
                ? number_format((float)$trackedTarget['buy_multiplier'], 2) . 'x'
                : '—',
            'sell_label' => is_numeric($trackedTarget['sell_multiplier'] ?? null)
                ? number_format((float)$trackedTarget['sell_multiplier'], 2) . 'x'
                : '—',
            'accuracy_label' => is_numeric($accuracy) ? number_format((float)$accuracy, 1) . '%' : '—',
        ];
    }
    return $cards;
}

function ensureTrackedTargetsHaveCronEntries(string $sourcePath, string $registryPath): array
{
    $source = loadCpanelCronRegistry($sourcePath);
    $registry = loadCpanelCronRegistry($registryPath);
    $indexed = [];

    foreach ($registry['targets'] as $target) {
        if (!is_array($target)) continue;
        $marketType = strtolower(trim((string)($target['market_type'] ?? '')));
        $symbol = strtoupper(trim((string)($target['symbol'] ?? '')));
        if (($marketType !== 'crypto' && $marketType !== 'stock') || $symbol === '') continue;
        $indexed[$marketType . '|' . $symbol] = $target;
    }

    foreach ($source['targets'] as $target) {
        if (!is_array($target)) continue;
        $marketType = strtolower(trim((string)($target['market_type'] ?? '')));
        $symbol = strtoupper(trim((string)($target['symbol'] ?? '')));
        if (($marketType !== 'crypto' && $marketType !== 'stock') || $symbol === '') continue;
        $key = $marketType . '|' . $symbol;
        $existing = is_array($indexed[$key] ?? null) ? $indexed[$key] : [];
        $indexed[$key] = array_merge($existing, $target, [
            'market_type' => $marketType,
            'symbol' => $symbol,
            'buy_multiplier' => is_numeric($target['buy_multiplier'] ?? ($existing['buy_multiplier'] ?? null))
                ? max(0.10, min(5.00, (float)($target['buy_multiplier'] ?? $existing['buy_multiplier'])))
                : null,
            'sell_multiplier' => is_numeric($target['sell_multiplier'] ?? ($existing['sell_multiplier'] ?? null))
                ? max(0.10, min(5.00, (float)($target['sell_multiplier'] ?? $existing['sell_multiplier'])))
                : null,
            'trust_percent' => is_numeric($target['trust_percent'] ?? ($existing['trust_percent'] ?? null))
                ? max(1.0, min(100.0, (float)($target['trust_percent'] ?? $existing['trust_percent'])))
                : null,
            'updated_at' => trim((string)($target['updated_at'] ?? ($existing['updated_at'] ?? gmdate('Y-m-d\TH:i:s\Z')))),
        ]);
    }

    uasort($indexed, static function (array $a, array $b): int {
        return strcmp(
            strtolower(trim((string)($a['market_type'] ?? ''))) . '|' . strtoupper(trim((string)($a['symbol'] ?? ''))),
            strtolower(trim((string)($b['market_type'] ?? ''))) . '|' . strtoupper(trim((string)($b['symbol'] ?? '')))
        );
    });

    $registry['targets'] = array_values($indexed);
    saveCpanelCronRegistry($registryPath, $registry);
    return $registry['targets'];
}

function upsertTrackedIndexTarget(
    string $primaryPath,
    string $marketType,
    string $symbol,
    string $baseUrl,
    float $buyMultiplier,
    float $sellMultiplier,
    float $trustPercent,
    float $breakBuy,
    float $breakGain,
    float $breakLoss
): array {
    $registry = loadCpanelCronRegistry($primaryPath);
    $indexed = [];
    foreach ($registry['targets'] as $target) {
        if (!is_array($target)) continue;
        $targetMarketType = strtolower(trim((string)($target['market_type'] ?? '')));
        $targetSymbol = strtoupper(trim((string)($target['symbol'] ?? '')));
        if (($targetMarketType !== 'crypto' && $targetMarketType !== 'stock') || $targetSymbol === '') continue;
        $indexed[$targetMarketType . '|' . $targetSymbol] = $target;
    }
    $key = strtolower(trim($marketType)) . '|' . strtoupper(trim($symbol));
    $indexed[$key] = array_merge(is_array($indexed[$key] ?? null) ? $indexed[$key] : [], [
        'market_type' => strtolower(trim($marketType)),
        'symbol' => strtoupper(trim($symbol)),
        'base_url' => trim($baseUrl),
        'buy_multiplier' => max(0.10, min(5.00, $buyMultiplier)),
        'sell_multiplier' => max(0.10, min(5.00, $sellMultiplier)),
        'trust_percent' => number_format(max(1.0, min(100.0, $trustPercent)), 2, '.', ''),
        'break_buy' => number_format(min(25.0, max(0.01, $breakBuy)), 2, '.', ''),
        'break_gain' => number_format(min(25.0, max(0.01, $breakGain)), 2, '.', ''),
        'break_loss' => number_format(min(25.0, max(0.01, $breakLoss)), 2, '.', ''),
        'updated_at' => gmdate('Y-m-d\TH:i:s\Z'),
    ]);
    uasort($indexed, static function (array $a, array $b): int {
        return strcmp(
            strtolower(trim((string)($a['market_type'] ?? ''))) . '|' . strtoupper(trim((string)($a['symbol'] ?? ''))),
            strtolower(trim((string)($b['market_type'] ?? ''))) . '|' . strtoupper(trim((string)($b['symbol'] ?? '')))
        );
    });
    $registry['targets'] = array_values($indexed);
    saveCpanelCronRegistry($primaryPath, $registry);
    return $registry['targets'];
}

$next = new CNGN(5);

$dir = __DIR__ . '/tickers/';
if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
}

$is_live_request = isset($_GET['live']) && $_GET['live'] === '1';
$cache_only_request = isset($_GET['cache_only']) && $_GET['cache_only'] === '1';
$loop_update_requested = isset($_GET['run_analysis']) && $_GET['run_analysis'] === '1';
$loop_update_allowed = $loop_update_requested || PHP_SAPI === 'cli';
$analysis_requested = isset($_GET['run_analysis']) && $_GET['run_analysis'] === '1'
    && !$is_live_request;
$wallet_reset_requested = isset($_POST['reset_wallet']) && $_POST['reset_wallet'] === '1'
    && !$is_live_request;
$tracked_symbol_remove_requested = isset($_POST['remove_tracked_symbol']) && $_POST['remove_tracked_symbol'] === '1'
    && !$is_live_request;
$skip_auto_register_current = isset($_GET['skip_autoregister']) && $_GET['skip_autoregister'] === '1';
$wallet_reset_done = isset($_GET['wallet_reset_done']) && $_GET['wallet_reset_done'] === '1';
$wallet_reset_error = '';
$tracked_symbol_remove_error = '';
$configured_wallet_reset_password = localEnvironmentValue('WALLET_RESET_PASSWORD');
$provided_wallet_reset_password = is_string($_POST['wallet_reset_password'] ?? null)
    ? (string)$_POST['wallet_reset_password']
    : '';
$remove_tracked_market_type = is_string($_POST['remove_market_type'] ?? null)
    ? strtolower(trim((string)$_POST['remove_market_type']))
    : '';
$remove_tracked_symbol = is_string($_POST['remove_symbol'] ?? null)
    ? strtoupper(trim((string)$_POST['remove_symbol']))
    : '';
$wallet_bootstrap_path = paperWalletBootstrapPath($dir, $market_type, $ticker);
$model_wallet_state_path = $dir . $ticker . '-model-wallet-state.json';
if ($wallet_reset_requested) {
    if (!walletResetPasswordMatches($configured_wallet_reset_password, $provided_wallet_reset_password)) {
        $wallet_reset_error = $configured_wallet_reset_password === ''
            ? 'Wallet reset is disabled until WALLET_RESET_PASSWORD is configured on the server.'
            : 'Wallet reset password was not accepted.';
        $wallet_reset_requested = false;
    }
}
if ($tracked_symbol_remove_requested) {
    if (($remove_tracked_market_type !== 'crypto' && $remove_tracked_market_type !== 'stock') || $remove_tracked_symbol === '') {
        $tracked_symbol_remove_error = 'Tracked symbol remove request was incomplete.';
        $tracked_symbol_remove_requested = false;
    }
}
if ($wallet_reset_requested) {
    deleteLocalFileIfExists($wallet_bootstrap_path);
    deleteLocalFileIfExists($model_wallet_state_path);
    if (PHP_SAPI !== 'cli') {
        $redirectParams = $_GET;
        $redirectParams['wallet_reset_done'] = '1';
        $scriptPath = (string)($_SERVER['PHP_SELF'] ?? 'index.php');
        $redirectTarget = $scriptPath . ($redirectParams ? ('?' . http_build_query($redirectParams)) : '');
        header('Location: ' . $redirectTarget);
        exit;
    }
    if ($wallet_reset_requested) {
        $wallet_reset_done = true;
    }
}

if ($tracked_symbol_remove_requested) {
    $remaining_targets = removeTrackedIndexTargetFiles(
        __DIR__ . '/wsl_portfolio_targets.json',
        cpanelCronRegistryPath(),
        $remove_tracked_market_type,
        $remove_tracked_symbol
    );
    writeCpanelCronCommandsSnapshot(
        cpanelCronCommandsPath(),
        cpanelCronWriterPath(),
        cpanelCronRegistryPath(),
        loadCpanelCronRegistry(cpanelCronRegistryPath())['targets'] ?? []
    );
    if (PHP_SAPI !== 'cli') {
        $redirectParams = $_GET;
        unset($redirectParams['run_analysis'], $redirectParams['wallet_reset_done']);
        if ($remove_tracked_market_type === $market_type && $remove_tracked_symbol === strtoupper($ticker)) {
            if (!empty($remaining_targets[0]['market_type']) && !empty($remaining_targets[0]['symbol'])) {
                $redirectParams['market_type'] = (string)$remaining_targets[0]['market_type'];
                $redirectParams['symbol'] = (string)$remaining_targets[0]['symbol'];
                $redirectParams['run_analysis'] = '1';
                unset($redirectParams['skip_autoregister']);
            } else {
                $redirectParams['market_type'] = 'stock';
                $redirectParams['symbol'] = 'TSLA';
                $redirectParams['skip_autoregister'] = '1';
            }
        }
        $scriptPath = (string)($_SERVER['PHP_SELF'] ?? 'index.php');
        $redirectTarget = $scriptPath . ($redirectParams ? ('?' . http_build_query($redirectParams)) : '');
        header('Location: ' . $redirectTarget);
        exit;
    }
}

$persisted_current_target = trackedTargetForSymbol(
    loadTrackedIndexTargets(__DIR__ . '/wsl_portfolio_targets.json', cpanelCronRegistryPath()),
    $market_type,
    $ticker
);
if (!isset($request_params['buy_multiplier']) && is_numeric($persisted_current_target['buy_multiplier'] ?? null)) {
    $buy_multiplier = max(0.10, min(5.00, (float)$persisted_current_target['buy_multiplier']));
}
if (!isset($request_params['sell_multiplier']) && is_numeric($persisted_current_target['sell_multiplier'] ?? null)) {
    $sell_multiplier = max(0.10, min(5.00, (float)$persisted_current_target['sell_multiplier']));
}
if (!isset($request_params['trust_percent']) && is_numeric($persisted_current_target['trust_percent'] ?? null)) {
    $trust_percent = max(1.0, min(100.0, (float)$persisted_current_target['trust_percent']));
}
if (!isset($_GET['break_buy']) && is_numeric($persisted_current_target['break_buy'] ?? null)) {
    $break_buy_drop_pct = min(25.0, max(0.01, (float)$persisted_current_target['break_buy']));
}
if (!isset($_GET['break_gain']) && is_numeric($persisted_current_target['break_gain'] ?? null)) {
    $break_take_gain_pct = min(25.0, max(0.01, (float)$persisted_current_target['break_gain']));
}
if (!isset($_GET['break_loss']) && is_numeric($persisted_current_target['break_loss'] ?? null)) {
    $break_stop_loss_pct = min(25.0, max(0.01, (float)$persisted_current_target['break_loss']));
}

$cpanel_cron_registry_targets = $skip_auto_register_current
    ? (loadCpanelCronRegistry(cpanelCronRegistryPath())['targets'] ?? [])
    : registerCpanelCronTarget(
        cpanelCronRegistryPath(),
        detectedIndexBaseUrl(),
        $market_type,
        $ticker,
        $buy_multiplier,
        $sell_multiplier,
        $trust_percent,
        $break_buy_drop_pct,
        $break_take_gain_pct,
        $break_stop_loss_pct
    );
if (!$skip_auto_register_current) {
    upsertTrackedIndexTarget(
        __DIR__ . '/wsl_portfolio_targets.json',
        $market_type,
        $ticker,
        detectedIndexBaseUrl(),
        $buy_multiplier,
        $sell_multiplier,
        $trust_percent,
        $break_buy_drop_pct,
        $break_take_gain_pct,
        $break_stop_loss_pct
    );
}
$cpanel_cron_registry_targets = ensureTrackedTargetsHaveCronEntries(
    __DIR__ . '/wsl_portfolio_targets.json',
    cpanelCronRegistryPath()
);
writeCpanelCronCommandsSnapshot(
    cpanelCronCommandsPath(),
    cpanelCronWriterPath(),
    cpanelCronRegistryPath(),
    $cpanel_cron_registry_targets
);
$tracked_index_targets = loadTrackedIndexTargets(
    __DIR__ . '/wsl_portfolio_targets.json',
    cpanelCronRegistryPath()
);
$current_tracked_target = trackedTargetForSymbol($tracked_index_targets, $market_type, $ticker);
if (!isset($request_params['buy_multiplier']) && is_numeric($current_tracked_target['buy_multiplier'] ?? null)) {
    $buy_multiplier = max(0.10, min(5.00, (float)$current_tracked_target['buy_multiplier']));
}
if (!isset($request_params['sell_multiplier']) && is_numeric($current_tracked_target['sell_multiplier'] ?? null)) {
    $sell_multiplier = max(0.10, min(5.00, (float)$current_tracked_target['sell_multiplier']));
}
if (!isset($request_params['trust_percent']) && is_numeric($current_tracked_target['trust_percent'] ?? null)) {
    $trust_percent = max(1.0, min(100.0, (float)$current_tracked_target['trust_percent']));
}
if (!isset($_GET['break_buy']) && is_numeric($current_tracked_target['break_buy'] ?? null)) {
    $break_buy_drop_pct = min(25.0, max(0.01, (float)$current_tracked_target['break_buy']));
}
if (!isset($_GET['break_gain']) && is_numeric($current_tracked_target['break_gain'] ?? null)) {
    $break_take_gain_pct = min(25.0, max(0.01, (float)$current_tracked_target['break_gain']));
}
if (!isset($_GET['break_loss']) && is_numeric($current_tracked_target['break_loss'] ?? null)) {
    $break_stop_loss_pct = min(25.0, max(0.01, (float)$current_tracked_target['break_loss']));
}
$tracked_crypto_symbols_for_quotes = [];
$tracked_stock_symbols_for_quotes = [];
foreach ($tracked_index_targets as $tracked_target) {
    if (!is_array($tracked_target)) continue;
    $tracked_market_type = strtolower(trim((string)($tracked_target['market_type'] ?? '')));
    $tracked_symbol = strtoupper(trim((string)($tracked_target['symbol'] ?? '')));
    if ($tracked_symbol === '') continue;
    if ($tracked_market_type === 'crypto') {
        $tracked_crypto_symbols_for_quotes[] = $tracked_symbol;
    } elseif ($tracked_market_type === 'stock') {
        $tracked_stock_symbols_for_quotes[] = $tracked_symbol;
    }
}
if ($market_type === 'crypto' && $ticker !== '') {
    $tracked_crypto_symbols_for_quotes[] = strtoupper($ticker);
} elseif ($market_type === 'stock' && $ticker !== '') {
    $tracked_stock_symbols_for_quotes[] = strtoupper($ticker);
}
$tracked_quote_symbols = array_values(array_unique(array_merge(
    $tracked_crypto_symbols_for_quotes,
    $tracked_stock_symbols_for_quotes
)));
$tracked_yahoo_quotes = fetchYahooLatestPrices($tracked_quote_symbols);
$tracked_link_groups = buildTrackedLinkGroups($tracked_index_targets, $market_type, $ticker);
$tracked_link_groups = applyTrackedLinkPrices($tracked_link_groups, $tracked_yahoo_quotes, $dir);
$tracked_crypto_links = $tracked_link_groups['crypto'];
$tracked_stock_links = $tracked_link_groups['stock'];
$tracked_marquee_links = $tracked_link_groups['marquee'];
$tracked_dashboard_cards = buildTrackedDashboardCards($tracked_index_targets, $market_type, $ticker, $dir);

$file_path = $dir . $ticker . '.csv';
$display_path = './tickers/' . $ticker . '.csv';
$cron_live_output_path = $dir . $ticker . '-live-output.json';
$cron_summary_path = $dir . $ticker . '-cron-summary.json';
$pair_rule_state_path = $dir . $ticker . '-pair-rule-state.json';
$carry_forward_reset_path = $dir . $ticker . '-carry-forward-reset.json';
$cron_summary = loadLocalJsonArray($cron_summary_path);
$cron_live_output = loadLocalJsonArray($cron_live_output_path);
$stored_pair_rule_state = loadLocalJsonArray($pair_rule_state_path);
setActivePairDirectionMap(is_array($stored_pair_rule_state['map'] ?? null) ? $stored_pair_rule_state['map'] : defaultPairDirectionMap());
$readonly_browser_mode = !$loop_update_allowed;
$scheduler_cache_note = 'Browser view is reading scheduler JSON only; loops run from the scheduler path.';
$cron_live_cache_available = is_array($cron_live_output) && !empty($cron_live_output);

if ($cache_only_request && $is_live_request && $cron_live_cache_available) {
    if (is_array($cron_live_output['autoBreakTrader'] ?? null)) {
        $cron_live_output['autoBreakTrader'] = normalizeTraderDisplayState($cron_live_output['autoBreakTrader']);
        $cron_live_output['paperProfit'] = (float)($cron_live_output['autoBreakTrader']['sim_net_move'] ?? 0.0);
        $cron_live_output['simulatedNetMove'] = (float)($cron_live_output['autoBreakTrader']['sim_net_move'] ?? 0.0);
    }
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($cron_live_output, JSON_UNESCAPED_SLASHES);
    exit;
}

$error_message = '';
$data_note = '';

if (!defined('LIVE_CANDLE_WINDOW')) define('LIVE_CANDLE_WINDOW', 15);
if (!defined('ONE_HOUR_CANDLE_COUNT')) define('ONE_HOUR_CANDLE_COUNT', 12);
if (!defined('CHART_HOURLY_WINDOW')) define('CHART_HOURLY_WINDOW', 15);
if (!defined('FUTURE_GUESS_HORIZON')) define('FUTURE_GUESS_HORIZON', 49);
if (!defined('VISIBLE_FUTURE_GUESSES')) define('VISIBLE_FUTURE_GUESSES', 9);
if (!defined('TRADE_ANALYSIS_HORIZON')) define('TRADE_ANALYSIS_HORIZON', ONE_HOUR_CANDLE_COUNT);
if (!defined('REALIZE_LOSS_TRADES')) define('REALIZE_LOSS_TRADES', false);
// Paper-sale guard: require price to clear average cost by this buffer before selling.
if (!defined('MIN_SELL_EDGE_PERCENT')) define('MIN_SELL_EDGE_PERCENT', 0.50);
if (!defined('MIN_TRADE_AMOUNT')) define('MIN_TRADE_AMOUNT', 2.00);

function coinGeckoIdForTicker(string $ticker): ?string
{
    $base = strtoupper(trim(strtok($ticker, '-')));
    $map = [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'DOGE' => 'dogecoin',
        'SOL' => 'solana',
        'XRP' => 'ripple',
        'ADA' => 'cardano',
        'BNB' => 'binancecoin',
        'LTC' => 'litecoin',
        'BCH' => 'bitcoin-cash',
        'TRX' => 'tron',
        'DOT' => 'polkadot',
        'LINK' => 'chainlink',
        'AVAX' => 'avalanche-2',
        'SHIB' => 'shiba-inu',
        'UNI' => 'uniswap',
        'PEPE' => 'pepe',
    ];
    return $map[$base] ?? null;
}

function fetchCoinGeckoUsdPrice(string $ticker): array
{
    $coinId = coinGeckoIdForTicker($ticker);
    if ($coinId === null) return [false, 'CoinGecko ID is not configured for this symbol.', null];

    $params = http_build_query([
        'ids' => $coinId,
        'vs_currencies' => 'usd',
        'include_last_updated_at' => 'true',
        'precision' => 'full',
    ]);
    $url = 'https://api.coingecko.com/api/v3/simple/price?' . $params;
    $headers = [
        'Accept: application/json',
        'Cache-Control: no-cache, no-store, must-revalidate',
        'Pragma: no-cache',
        'User-Agent: Mozilla/5.0 (compatible; CST-Market-Wave/1.0)'
    ];
    $demoKey = trim((string)(getenv('COINGECKO_DEMO_API_KEY') ?: ''));
    $proKey = trim((string)(getenv('COINGECKO_PRO_API_KEY') ?: ''));
    if ($proKey !== '') {
        $headers[] = 'x-cg-pro-api-key: ' . $proKey;
    } elseif ($demoKey !== '') {
        $headers[] = 'x-cg-demo-api-key: ' . $demoKey;
    }

    $body = false;
    $httpCode = 0;
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
        ]);
        $body = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($body === false) {
            return [false, 'CoinGecko request failed: ' . ($curlError ?: 'unknown cURL error'), null];
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 20,
                'ignore_errors' => true,
                'header' => implode("\r\n", $headers),
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
            $httpCode = (int)$match[1];
        }
    }

    if ($body === false || $body === '') {
        return [false, 'CoinGecko returned an empty response.', null];
    }
    if ($httpCode !== 0 && ($httpCode < 200 || $httpCode >= 300)) {
        return [false, "CoinGecko returned HTTP {$httpCode}.", null];
    }

    $json = json_decode($body, true);
    if (!is_array($json) || !isset($json[$coinId]) || !is_array($json[$coinId])) {
        return [false, 'CoinGecko returned invalid JSON.', null];
    }
    $record = $json[$coinId];
    $price = $record['usd'] ?? null;
    if (!is_numeric($price)) {
        return [false, 'CoinGecko did not return a USD price.', $record];
    }
    return [[
        'price' => (float)$price,
        'last_updated_at' => isset($record['last_updated_at']) && is_numeric($record['last_updated_at'])
            ? (int)$record['last_updated_at']
            : null,
        'id' => $coinId,
    ], '', $record];
}

function fetchCoinGeckoUsdPrices(array $tickers): array
{
    $idToTicker = [];
    foreach ($tickers as $ticker) {
        if (!is_string($ticker) || trim($ticker) === '') continue;
        $coinId = coinGeckoIdForTicker($ticker);
        if ($coinId === null) continue;
        $idToTicker[$coinId] = strtoupper(trim($ticker));
    }
    if (!$idToTicker) return [];

    $params = http_build_query([
        'ids' => implode(',', array_keys($idToTicker)),
        'vs_currencies' => 'usd',
        'include_last_updated_at' => 'true',
        'precision' => 'full',
    ]);
    $url = 'https://api.coingecko.com/api/v3/simple/price?' . $params;
    $headers = [
        'Accept: application/json',
        'Cache-Control: no-cache, no-store, must-revalidate',
        'Pragma: no-cache',
        'User-Agent: Mozilla/5.0 (compatible; CST-Market-Wave/1.0)'
    ];
    $demoKey = trim((string)(getenv('COINGECKO_DEMO_API_KEY') ?: ''));
    $proKey = trim((string)(getenv('COINGECKO_PRO_API_KEY') ?: ''));
    if ($proKey !== '') {
        $headers[] = 'x-cg-pro-api-key: ' . $proKey;
    } elseif ($demoKey !== '') {
        $headers[] = 'x-cg-demo-api-key: ' . $demoKey;
    }

    $body = false;
    $httpCode = 0;
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
        ]);
        $body = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 12,
                'ignore_errors' => true,
                'header' => implode("\r\n", $headers),
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
            $httpCode = (int)$match[1];
        }
    }

    if (!is_string($body) || $body === '' || ($httpCode !== 0 && ($httpCode < 200 || $httpCode >= 300))) {
        return [];
    }

    $json = json_decode($body, true);
    if (!is_array($json)) return [];

    $quotes = [];
    foreach ($json as $coinId => $record) {
        if (!isset($idToTicker[$coinId]) || !is_array($record)) continue;
        $price = $record['usd'] ?? null;
        if (!is_numeric($price)) continue;
        $quotes[$idToTicker[$coinId]] = [
            'price' => (float)$price,
            'last_updated_at' => isset($record['last_updated_at']) && is_numeric($record['last_updated_at'])
                ? (int)$record['last_updated_at']
                : null,
            'id' => $coinId,
        ];
    }
    return $quotes;
}

function fetchYahooLatestPrices(array $symbols): array
{
    $normalized = [];
    foreach ($symbols as $symbol) {
        if (!is_string($symbol) || trim($symbol) === '') continue;
        $clean = strtoupper(trim($symbol));
        if ($clean === '') continue;
        $normalized[$clean] = $clean;
    }
    if (!$normalized) return [];

    $quotes = [];
    foreach (array_chunk(array_values($normalized), 25) as $chunk) {
        $url = 'https://query1.finance.yahoo.com/v7/finance/spark?' . http_build_query([
            'symbols' => implode(',', $chunk),
            'range' => '1d',
            'interval' => '5m',
            'indicators' => 'close',
            'includeTimestamps' => 'true',
            'includePrePost' => 'false',
        ]);
        $headers = [
            'Accept: application/json',
            'Cache-Control: no-cache, no-store, must-revalidate',
            'Pragma: no-cache',
            'User-Agent: Mozilla/5.0 (compatible; CST-Market-Wave/1.0)',
        ];

        $body = false;
        $httpCode = 0;
        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            $curlOptions = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_ENCODING => '',
            ];
            curl_setopt_array($curl, $curlOptions);
            $body = curl_exec($curl);
            $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            if (($body === false || $body === '') && str_contains(strtolower($curlError), 'certificate')) {
                curl_setopt_array($curl, $curlOptions + [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                ]);
                $body = curl_exec($curl);
                $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            }
            curl_close($curl);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 12,
                    'ignore_errors' => true,
                    'header' => implode("\r\n", $headers),
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
            $body = @file_get_contents($url, false, $context);
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
                $httpCode = (int)$match[1];
            }
        }

        if (!is_string($body) || $body === '' || ($httpCode !== 0 && ($httpCode < 200 || $httpCode >= 300))) {
            continue;
        }

        $json = json_decode($body, true);
        $results = is_array($json['spark']['result'] ?? null) ? $json['spark']['result'] : [];
        foreach ($results as $result) {
            if (!is_array($result)) continue;
            $symbol = strtoupper(trim((string)($result['symbol'] ?? '')));
            $response = is_array($result['response'][0] ?? null) ? $result['response'][0] : [];
            $meta = is_array($response['meta'] ?? null) ? $response['meta'] : [];
            $timestamps = is_array($response['timestamp'] ?? null) ? $response['timestamp'] : [];
            $closeSeries = is_array($response['indicators']['quote'][0]['close'] ?? null)
                ? $response['indicators']['quote'][0]['close']
                : [];
            $price = $meta['regularMarketPrice'] ?? null;
            if (!is_numeric($price)) {
                for ($index = count($closeSeries) - 1; $index >= 0; $index--) {
                    if (is_numeric($closeSeries[$index] ?? null)) {
                        $price = (float)$closeSeries[$index];
                        break;
                    }
                }
            }
            if ($symbol === '' || !is_numeric($price)) continue;
            $lastTimestamp = null;
            for ($index = count($timestamps) - 1; $index >= 0; $index--) {
                if (is_numeric($timestamps[$index] ?? null)) {
                    $lastTimestamp = (int)$timestamps[$index];
                    break;
                }
            }
            $quotes[$symbol] = [
                'price' => (float)$price,
                'last_updated_at' => $lastTimestamp,
                'source' => 'YAHOO',
            ];
        }
    }

    return $quotes;
}

function fetchAlphaVantageBulkStockQuotes(array $symbols): array
{
    $apiKey = localEnvironmentValue('ALPHAVANTAGE_API_KEY');
    if ($apiKey === '') return [];

    $normalized = [];
    foreach ($symbols as $symbol) {
        if (!is_string($symbol) || trim($symbol) === '') continue;
        $clean = strtoupper(trim($symbol));
        $clean = preg_replace('/[^A-Z0-9.\-^]/', '', $clean);
        if (!is_string($clean) || $clean === '') continue;
        $normalized[$clean] = $clean;
    }
    if (!$normalized) return [];

    $quotes = [];
    foreach (array_chunk(array_values($normalized), 100) as $chunk) {
        $params = http_build_query([
            'function' => 'REALTIME_BULK_QUOTES',
            'symbol' => implode(',', $chunk),
            'datatype' => 'csv',
            'apikey' => $apiKey,
        ]);
        $url = 'https://www.alphavantage.co/query?' . $params;
        $headers = [
            'Accept: text/csv,application/json;q=0.9,*/*;q=0.8',
            'Cache-Control: no-cache, no-store, must-revalidate',
            'Pragma: no-cache',
            'User-Agent: Mozilla/5.0 (compatible; CST-Market-Wave/1.0)'
        ];

        $body = false;
        $httpCode = 0;
        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_ENCODING => '',
            ]);
            $body = curl_exec($curl);
            $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 12,
                    'ignore_errors' => true,
                    'header' => implode("\r\n", $headers),
                ],
            ]);
            $body = @file_get_contents($url, false, $context);
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
                $httpCode = (int)$match[1];
            }
        }

        if (!is_string($body) || trim($body) === '' || ($httpCode !== 0 && ($httpCode < 200 || $httpCode >= 300))) {
            continue;
        }

        $trimmed = ltrim($body);
        if ($trimmed !== '' && $trimmed[0] === '{') {
            $json = json_decode($body, true);
            $records = is_array($json['data'] ?? null) ? $json['data'] : [];
            foreach ($records as $record) {
                if (!is_array($record)) continue;
                $symbol = strtoupper(trim((string)($record['symbol'] ?? '')));
                $price = $record['price'] ?? ($record['close'] ?? null);
                if ($symbol === '' || !is_numeric($price)) continue;
                $quotes[$symbol] = [
                    'price' => (float)$price,
                    'source' => 'ALPHAVANTAGE',
                ];
            }
            continue;
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($body));
        if (!is_array($lines) || count($lines) < 2) continue;
        $headersRow = str_getcsv((string)array_shift($lines));
        $headerIndex = [];
        foreach ($headersRow as $index => $header) {
            $normalizedHeader = strtolower(trim((string)$header));
            $normalizedHeader = preg_replace('/[^a-z0-9]+/', '_', $normalizedHeader);
            if (!is_string($normalizedHeader) || $normalizedHeader === '') continue;
            $headerIndex[$normalizedHeader] = $index;
        }
        $symbolIndex = $headerIndex['symbol'] ?? null;
        $priceIndex = $headerIndex['price'] ?? ($headerIndex['last_price'] ?? ($headerIndex['close'] ?? null));
        if (!is_int($symbolIndex) || !is_int($priceIndex)) continue;

        foreach ($lines as $line) {
            if (trim((string)$line) === '') continue;
            $row = str_getcsv((string)$line);
            $symbol = strtoupper(trim((string)($row[$symbolIndex] ?? '')));
            $price = $row[$priceIndex] ?? null;
            if ($symbol === '' || !is_numeric($price)) continue;
            $quotes[$symbol] = [
                'price' => (float)$price,
                'source' => 'ALPHAVANTAGE',
            ];
        }
    }

    return $quotes;
}

function simpleHttpReachable(string $url, int $timeoutSeconds = 10): bool
{
    static $reachabilityCache = [];
    $cacheKey = $url . '|' . $timeoutSeconds;
    if (array_key_exists($cacheKey, $reachabilityCache)) {
        return (bool)$reachabilityCache[$cacheKey];
    }
    $headers = [
        'User-Agent: Mozilla/5.0 (compatible; CST-Market-Wave/1.0)',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
    ];
    $httpCode = 0;
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
        ]);
        curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'timeout' => $timeoutSeconds,
                'ignore_errors' => true,
                'header' => implode("\r\n", $headers),
            ],
        ]);
        @file_get_contents($url, false, $context);
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
            $httpCode = (int)$match[1];
        }
    }
    $reachable = $httpCode >= 200 && $httpCode < 500;
    $reachabilityCache[$cacheKey] = $reachable;
    return $reachable;
}

function microsoftConnectivityAvailable(): bool
{
    foreach ([
        'https://www.microsoft.com',
        'https://microsoft.com',
        'http://www.microsoft.com',
    ] as $url) {
        if (simpleHttpReachable($url, 2)) return true;
    }
    return false;
}

function googleConnectivityAvailable(): bool
{
    foreach ([
        'https://www.google.com',
        'https://google.com',
        'http://www.google.com',
    ] as $url) {
        if (simpleHttpReachable($url, 2)) return true;
    }
    return false;
}

function anyExternalConnectivityAvailable(): bool
{
    return googleConnectivityAvailable() || microsoftConnectivityAvailable();
}

function externalConnectivityLooksDown(): bool
{
    return !anyExternalConnectivityAvailable();
}

function yahooErrorImpliesMissingSymbol(string $message): bool
{
    $normalized = strtolower(trim($message));
    if ($normalized === '') return false;
    return str_contains($normalized, 'symbol does not exist')
        || str_contains($normalized, 'could not find this symbol')
        || str_contains($normalized, 'alpha vantage returned no usable global quote price')
        || str_contains($normalized, 'alpha vantage could not find this stock symbol')
        || str_contains($normalized, 'symbol likely does not exist')
        || str_contains($normalized, 'no chart data for this symbol')
        || str_contains($normalized, 'not found');
}

function alphaVantageStockQuoteStatus(string $symbol): array
{
    $apiKey = localEnvironmentValue('ALPHAVANTAGE_API_KEY');
    if ($apiKey === '') {
        return [
            'ok' => false,
            'status' => 0,
            'looks_real' => false,
            'inconclusive' => true,
            'reason' => 'ALPHAVANTAGE_API_KEY is not configured.',
        ];
    }

    $clean = strtoupper(trim($symbol));
    $clean = preg_replace('/[^A-Z0-9.\-^]/', '', $clean);
    if (!is_string($clean) || $clean === '') {
        return [
            'ok' => false,
            'status' => 0,
            'looks_real' => false,
            'inconclusive' => false,
            'reason' => 'Symbol was empty after normalization.',
        ];
    }

    $url = 'https://www.alphavantage.co/query?' . http_build_query([
        'function' => 'GLOBAL_QUOTE',
        'symbol' => $clean,
        'apikey' => $apiKey,
    ]);
    $headers = [
        'Accept: application/json',
        'Cache-Control: no-cache, no-store, must-revalidate',
        'Pragma: no-cache',
        'User-Agent: Mozilla/5.0 (compatible; CST-Market-Wave/1.0)',
    ];

    $body = false;
    $httpCode = 0;
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
        ]);
        $body = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 12,
                'ignore_errors' => true,
                'header' => implode("\r\n", $headers),
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
            $httpCode = (int)$match[1];
        }
    }

    if (!is_string($body) || $body === '') {
        return [
            'ok' => false,
            'status' => $httpCode,
            'looks_real' => false,
            'inconclusive' => true,
            'reason' => 'Alpha Vantage did not respond.',
        ];
    }

    $json = json_decode($body, true);
    if (!is_array($json)) {
        return [
            'ok' => false,
            'status' => $httpCode,
            'looks_real' => false,
            'inconclusive' => true,
            'reason' => 'Alpha Vantage returned non-JSON.',
        ];
    }
    if (isset($json['Note']) || isset($json['Information']) || isset($json['Error Message'])) {
        return [
            'ok' => false,
            'status' => $httpCode,
            'looks_real' => false,
            'inconclusive' => true,
            'reason' => 'Alpha Vantage returned a note, information message, or error message.',
        ];
    }
    $quote = is_array($json['Global Quote'] ?? null) ? $json['Global Quote'] : [];
    $price = $quote['05. price'] ?? null;
    if (is_numeric($price) && (float)$price > 0.0) {
        return [
            'ok' => true,
            'status' => $httpCode,
            'looks_real' => true,
            'inconclusive' => false,
            'reason' => 'Alpha Vantage returned a usable Global Quote price.',
        ];
    }
    return [
        'ok' => !empty($quote),
        'status' => $httpCode,
        'looks_real' => false,
        'inconclusive' => false,
        'reason' => 'Alpha Vantage returned no usable Global Quote price for this symbol.',
    ];
}

function probeYahooSymbolStatus(string $ticker): array
{
    $encoded = rawurlencode($ticker);
    $urlPath = "/v8/finance/chart/{$encoded}?range=1d&interval=5m&includePrePost=false&_=" . time();
    $headers = [
        'Accept: application/json',
        'Cache-Control: no-cache, no-store, must-revalidate',
        'Pragma: no-cache',
        'User-Agent: Mozilla/5.0 (compatible; CST-Market-Wave/1.0)',
    ];

    foreach (['query1.finance.yahoo.com', 'query2.finance.yahoo.com'] as $host) {
        $url = 'https://' . $host . $urlPath;
        $body = false;
        $httpCode = 0;
        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            $curlOptions = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_ENCODING => '',
            ];
            curl_setopt_array($curl, $curlOptions);
            $body = curl_exec($curl);
            $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            if (($body === false || $body === '') && str_contains(strtolower($curlError), 'certificate')) {
                curl_setopt_array($curl, $curlOptions + [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                ]);
                $body = curl_exec($curl);
                $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            }
            curl_close($curl);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                    'ignore_errors' => true,
                    'header' => implode("\r\n", $headers),
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
            $body = @file_get_contents($url, false, $context);
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
                $httpCode = (int)$match[1];
            }
        }

        if ($httpCode === 404) {
            return ['missing' => true, 'reachable' => true];
        }
        if (!is_string($body) || $body === '') {
            continue;
        }
        $json = json_decode($body, true);
        if (!is_array($json)) {
            continue;
        }
        $error = $json['chart']['error'] ?? null;
        if (is_array($error)) {
            $message = strtolower(trim((string)($error['description'] ?? $error['code'] ?? '')));
            if ($message !== '' && (str_contains($message, 'not found') || str_contains($message, 'no data'))) {
                return ['missing' => true, 'reachable' => true];
            }
        }
        $result = $json['chart']['result'][0] ?? null;
        if (is_array($result)) {
            $meta = $result['meta'] ?? null;
            $timestamps = $result['timestamp'] ?? null;
            if ((is_array($meta) && !empty($meta['symbol'])) || (is_array($timestamps) && count($timestamps) > 0)) {
                return ['missing' => false, 'reachable' => true];
            }
        }
    }

    return ['missing' => false, 'reachable' => false];
}

function clearTickerArtifacts(string $directory, string $marketType, string $ticker): void
{
    $directory = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR;
    $paths = [
        $directory . $ticker . '.csv',
        $directory . $ticker . '-five-minute.csv',
        $directory . $ticker . '-live-output.json',
        $directory . $ticker . '-cron-summary.json',
        $directory . $ticker . '-neutral-guesses.json',
        $directory . $ticker . '-settled-actuals.json',
        $directory . $ticker . '-settled-results.json',
        $directory . $ticker . '-model-wallet-state.json',
        paperWalletBootstrapPath($directory, $marketType, $ticker),
    ];
    foreach ($paths as $path) {
        deleteLocalFileIfExists($path);
    }
}

function csvPriceRows(string $csvFilePath): array
{
    $handle = @fopen($csvFilePath, 'r');
    if ($handle === false) return [];
    $rows = [];
    while (($row = fgetcsv($handle)) !== false) {
        if (!isset($row[0], $row[4])) continue;
        if (strcasecmp((string)$row[0], 'Date') === 0) continue;
        if (!is_numeric($row[4])) continue;
        $rows[] = $row;
    }
    fclose($handle);
    return $rows;
}

function latestCsvClose(string $csvFilePath): ?float
{
    $rows = csvPriceRows($csvFilePath);
    if (!$rows) return null;
    $last = $rows[count($rows) - 1];
    return isset($last[4]) && is_numeric($last[4]) ? (float)$last[4] : null;
}

function writeRollingObservationCsv(string $csvFilePath, array $rows): bool
{
    $temporary = $csvFilePath . '.tmp';
    $stream = fopen('php://temp', 'w+');
    if ($stream === false) return false;
    fputcsv($stream, ['Date', 'Open', 'High', 'Low', 'Close', 'Adj Close', 'Volume']);
    foreach ($rows as $row) {
        fputcsv($stream, $row);
    }
    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);
    if (!is_string($csv) || $csv === '') return false;
    if (@file_put_contents($temporary, $csv, LOCK_EX) === false) return false;
    @chmod($temporary, 0664);
    if (!@rename($temporary, $csvFilePath)) return false;
    clearstatcache(true, $csvFilePath);
    return true;
}

function appendObservationBoundaryRow(string $observationPath, string $seedPath, int $boundaryEpoch, float $price, int $window = LIVE_CANDLE_WINDOW): bool
{
    $rows = csvPriceRows($observationPath);
    if (!$rows && file_exists($seedPath)) {
        $rows = array_slice(csvPriceRows($seedPath), -$window);
    }

    $rowsByTime = [];
    foreach ($rows as $row) {
        if (!isset($row[0], $row[1], $row[2], $row[3], $row[4])) continue;
        $rowsByTime[(string)$row[0]] = $row;
    }

    $timeKey = gmdate('Y-m-d\TH:i:s\Z', $boundaryEpoch);
    if (array_key_exists($timeKey, $rowsByTime)) {
        return true;
    }

    $rowsByTime[$timeKey] = [
        $timeKey,
        $price,
        $price,
        $price,
        $price,
        $price,
        0,
    ];
    uksort($rowsByTime, static fn(string $a, string $b): int => strcmp($a, $b));
    $trimmed = array_slice(array_values($rowsByTime), -max(2, $window));
    return writeRollingObservationCsv($observationPath, $trimmed);
}

/**
 * Download fresh Yahoo Finance chart data and convert it to the CSV layout
 * expected by CNGN::bitcoin(): Date,Open,High,Low,Close,Adj Close,Volume.
 */
function fetchYahooChartCsv(string $ticker, string $range = '5d', int $minimumPoints = 100, string $marketType = 'crypto'): array
{
    $encoded = rawurlencode($ticker);
    $range = in_array($range, ['1d', '5d'], true) ? $range : '5d';
    $minimumPoints = max(2, $minimumPoints);
    $urlPath = "/v8/finance/chart/{$encoded}"
        . "?range={$range}&interval=5m&includePrePost=false&events=div%2Csplits&_=" . time();

    $headers = [
        'Accept: application/json',
        'Cache-Control: no-cache, no-store, must-revalidate',
        'Pragma: no-cache',
        'User-Agent: Mozilla/5.0 (compatible; CST-Market-Wave/1.0)'
    ];

    $body = false;
    $httpCode = 0;
    $requestError = '';
    foreach (['query1.finance.yahoo.com', 'query2.finance.yahoo.com'] as $host) {
        $url = 'https://' . $host . $urlPath;
        $body = false;
        $httpCode = 0;

        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            $curlOptions = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_ENCODING => '',
            ];
            curl_setopt_array($curl, $curlOptions);
            $body = curl_exec($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $requestError = curl_error($curl);
            if (($body === false || $body === '') && str_contains(strtolower($requestError), 'certificate')) {
                curl_setopt_array($curl, $curlOptions + [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                ]);
                $body = curl_exec($curl);
                $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $requestError = curl_error($curl);
            }
            curl_close($curl);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 25,
                    'ignore_errors' => true,
                    'header' => implode("\r\n", $headers),
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
            $body = @file_get_contents($url, false, $context);
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
                $httpCode = (int) $match[1];
            }
        }

        if (is_string($body) && $body !== '' && ($httpCode === 0 || ($httpCode >= 200 && $httpCode < 300))) {
            break;
        }
    }

    if ($body === false || $body === '') {
        $symbolProbe = probeYahooSymbolStatus($ticker);
        if (!empty($symbolProbe['missing'])) {
            return [false, 'The primary market provider could not find this symbol. It does not appear to be a real ticker there.', null];
        }
        if (!anyExternalConnectivityAvailable()) {
            return [false, 'Internet may be down or unreachable right now; the primary market provider did not respond.', null];
        }
        return [false, 'The primary market provider did not respond, but internet connectivity appears up. This looks more like a provider-side availability problem than a bad symbol.', null];
    }

    if ($httpCode !== 0 && ($httpCode < 200 || $httpCode >= 300)) {
        if ($httpCode === 404) {
            if (anyExternalConnectivityAvailable()) {
                return [false, 'The primary market provider could not find this symbol (HTTP 404). The symbol likely does not exist there.', null];
            }
            return [false, 'The primary market provider returned HTTP 404, but internet connectivity could not be confirmed. This could still be a network problem.', null];
        }
        if ($httpCode >= 500) {
            return [false, "Primary market provider server problem (HTTP {$httpCode}).", null];
        }
        return [false, "Primary market provider request failed with HTTP {$httpCode}.", null];
    }

    $json = json_decode($body, true);
    if (!is_array($json)) {
        return [false, 'The primary market provider returned invalid JSON.', null];
    }

    $error = $json['chart']['error'] ?? null;
    if ($error) {
        $message = $error['description'] ?? $error['code'] ?? 'Unknown provider error.';
        $normalizedCode = strtoupper(trim((string)($error['code'] ?? '')));
        $normalizedMessage = strtolower(trim((string)$message));
        if ($normalizedCode === 'NOT FOUND' || str_contains($normalizedMessage, 'not found')) {
            return [false, 'The primary market provider says this symbol does not exist.', null];
        }
        return [false, (string) $message, null];
    }

    $result = $json['chart']['result'][0] ?? null;
    if (!is_array($result)) {
        return [false, 'The primary market provider returned no chart data for this symbol.', null];
    }

    $timestamps = $result['timestamp'] ?? [];
    $quote = $result['indicators']['quote'][0] ?? [];
    $adjClose = $result['indicators']['adjclose'][0]['adjclose'] ?? [];
    $meta = $result['meta'] ?? [];

    if (!is_array($timestamps) || count($timestamps) < $minimumPoints) {
        return [false, 'The primary market provider returned too few price points.', $meta];
    }

    $stream = fopen('php://temp', 'w+');
    if ($stream === false) {
        return [false, 'Could not create the local CSV stream.', $meta];
    }

    fputcsv($stream, ['Date', 'Open', 'High', 'Low', 'Close', 'Adj Close', 'Volume']);
    $written = 0;

    foreach ($timestamps as $index => $timestamp) {
        $open = $quote['open'][$index] ?? null;
        $high = $quote['high'][$index] ?? null;
        $low = $quote['low'][$index] ?? null;
        $close = $quote['close'][$index] ?? null;
        $volume = $quote['volume'][$index] ?? 0;

        if (!is_numeric($timestamp) || !is_numeric($close)) {
            continue;
        }

        $adjusted = $adjClose[$index] ?? $close;
        fputcsv($stream, [
            gmdate('Y-m-d\TH:i:s\Z', (int) $timestamp),
            is_numeric($open) ? $open : $close,
            is_numeric($high) ? $high : $close,
            is_numeric($low) ? $low : $close,
            $close,
            is_numeric($adjusted) ? $adjusted : $close,
            is_numeric($volume) ? $volume : 0,
        ]);
        $written++;
    }

    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);

    if ($written < $minimumPoints || !is_string($csv) || strlen($csv) < 100) {
        return [false, 'The market data could not be converted into enough CSV rows.', $meta];
    }

    return [$csv, '', $meta];
}

/** Canonical strict OHLC candle direction used by audit, trust, and execution. */
function candleDirection(float $open, float $close): ?string
{
    if ($close > $open) return '+';
    if ($close < $open) return '-';
    return '0';
}

/** Read the newest fully completed five-minute candle direction. */
function completedCandleDirection(string $csvFilePath): ?array
{
    $handle = @fopen($csvFilePath, 'r');
    if ($handle === false) return null;

    $candles = [];
    while (($row = fgetcsv($handle)) !== false) {
        if (!isset($row[0], $row[4]) || !is_numeric($row[4])) continue;
        $candles[] = ['key' => (string)$row[0], 'close' => (float)$row[4]];
    }
    fclose($handle);

    // Yahoo's last row is only forming when it belongs to the current
    // five-minute boundary. When a market is closed, the last row is complete.
    $count = count($candles);
    if ($count < 2) return null;
    $currentBoundary = (int)floor(time() / 300) * 300;
    $lastEpoch = yahooTimestamp($candles[$count - 1]['key']);
    $lastIsForming = $lastEpoch !== null && $lastEpoch >= $currentBoundary;
    $completedIndex = $lastIsForming ? $count - 2 : $count - 1;
    if ($completedIndex < 1) return null;
    $previous = $candles[$completedIndex - 1];
    $completed = $candles[$completedIndex];

    return [
        'key' => $completed['key'],
        'direction' => candleDirection((float)$completed['open'], (float)$completed['close']),
        'close' => $completed['close'],
        'previous_close' => $previous['close'],
    ];
}

/** Use the last completed five-minute candle move as the first latent fallback. */
function previousCompletedFiveMinuteMove(array $candles): float
{
    $count = count($candles);
    if ($count < 2) return 0.0;

    $completedIndex = $count - 1;
    if (($candles[$completedIndex]['forming'] ?? false) && $count >= 3) {
        $completedIndex--;
    }
    if ($completedIndex < 1) return 0.0;

    if (!is_numeric($candles[$completedIndex]['close'] ?? null)
        || !is_numeric($candles[$completedIndex - 1]['close'] ?? null)) {
        return 0.0;
    }

    return abs((float)$candles[$completedIndex]['close'] - (float)$candles[$completedIndex - 1]['close']);
}

/** Keep one neutral CNGN guess per completed boundary, without judging it. */
function updateGuessHistory(string $statePath, ?array $completed, ?array $currentGuess): array
{
    $guess_window_limit = max(45, CHART_HOURLY_WINDOW * 12);
    $default = ['last_completed' => null, 'pending_guess' => null, 'history' => []];
    $handle = @fopen($statePath, 'c+');
    if ($handle === false) return $default;
    flock($handle, LOCK_EX);
    rewind($handle);
    $raw = stream_get_contents($handle);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    $state = is_array($decoded) ? array_merge($default, $decoded) : $default;

    if ($completed !== null && $state['last_completed'] !== $completed['key']) {
        if (is_array($state['pending_guess']) && isset($state['pending_guess']['direction'])) {
            $state['history'][] = [
                'time' => $completed['key'],
                'direction' => $state['pending_guess']['direction'],
                'pair' => $state['pending_guess']['pair'] ?? '',
                'symbol' => $state['pending_guess']['symbol'] ?? '%',
                'left' => $state['pending_guess']['left'] ?? null,
                'right' => $state['pending_guess']['right'] ?? null,
                'change' => isset($state['pending_guess']['change']) ? abs((float)$state['pending_guess']['change']) : null,
            ];
            $state['history'] = array_slice($state['history'], -$guess_window_limit);
        }
        $state['last_completed'] = $completed['key'];
    }

    if (is_array($currentGuess)) {
        $state['pending_guess'] = [
            'direction' => $currentGuess['direction'] ?? null,
            'pair' => $currentGuess['pair'] ?? '',
            'symbol' => $currentGuess['symbol'] ?? '%',
            'left' => $currentGuess['left'] ?? null,
            'right' => $currentGuess['right'] ?? null,
            'change' => isset($currentGuess['change']) ? abs((float)$currentGuess['change']) : null,
        ];
    }

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($state, JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $state;
}

/**
 * Freeze each current/future signal at its five-minute opening timestamp.
 * Later refreshes must not rewrite that timestamp's signal.
 */
function updateForecastHistory(string $statePath, int $boundaryEpoch, array $forecastRows, ?array $currentGuess = null, int $horizon = 7): array
{
    $forecast_window_limit = max(45, CHART_HOURLY_WINDOW * 12);
    $handle = @fopen($statePath, 'c+');
    if ($handle === false) return [];

    flock($handle, LOCK_EX);
    rewind($handle);
    $raw = stream_get_contents($handle);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    $state = is_array($decoded) ? $decoded : [];
    $state['forecasts'] = is_array($state['forecasts'] ?? null) ? $state['forecasts'] : [];

    $saveGuess = static function (array $guess, string $forecastTime) use (&$state): void {
        $state['forecasts'][$forecastTime] = [
            'time' => $forecastTime,
            'left' => $guess['left'] ?? null,
            'right' => $guess['right'] ?? null,
            'pair' => $guess['pair'] ?? '',
            'symbol' => $guess['symbol'] ?? '%',
            'direction' => $guess['direction'] ?? null,
            'change' => isset($guess['change']) ? abs((float)$guess['change']) : null,
        ];
    };
    $patchGuessChange = static function (array $guess, string $forecastTime) use (&$state): void {
        if (!isset($state['forecasts'][$forecastTime]) || !is_array($state['forecasts'][$forecastTime])) return;
        if (is_numeric($state['forecasts'][$forecastTime]['change'] ?? null)) return;
        if (!isset($guess['change']) || !is_numeric($guess['change'])) return;
        $state['forecasts'][$forecastTime]['change'] = abs((float)$guess['change']);
    };

    $currentTime = gmdate('Y-m-d\\TH:i:s\\Z', $boundaryEpoch);
    // Once a timestamp exists, never replace it—even after an hourly rebuild
    // or a later rule change.
    if (!array_key_exists($currentTime, $state['forecasts']) && is_array($currentGuess)) {
        $saveGuess($currentGuess, $currentTime);
    } elseif (is_array($currentGuess)) {
        $patchGuessChange($currentGuess, $currentTime);
    }

    for ($step = 1; $step <= $horizon; $step++) {
        $forecastTime = gmdate('Y-m-d\\TH:i:s\\Z', $boundaryEpoch + ($step * 300));
        // The extracted forward rows are stored nearest-first, so future step N
        // comes from zero-based row N-1.
        $row = $forecastRows[$step - 1] ?? null;
        $guess = is_string($row) ? cngnGuessFromRow($row) : null;
        if (!is_array($guess)) continue;

        if (array_key_exists($forecastTime, $state['forecasts'])) {
            $patchGuessChange($guess, $forecastTime);
            continue;
        }
        $saveGuess($guess, $forecastTime);
    }

    // Keep enough history for the visible window without allowing the state
    // file to grow forever.
    if (count($state['forecasts']) > $forecast_window_limit) {
        uksort($state['forecasts'], static fn(string $a, string $b): int => strcmp($a, $b));
        $state['forecasts'] = array_slice($state['forecasts'], -$forecast_window_limit, $forecast_window_limit, true);
    }

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($state, JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $state['forecasts'];
}

/** Freeze any visible timestamp/guess pairs that have not been saved yet. */
function freezeForecastGuesses(string $statePath, array $guessesByTime): array
{
    $forecast_window_limit = max(45, CHART_HOURLY_WINDOW * 12);
    $handle = @fopen($statePath, 'c+');
    if ($handle === false) return [];

    flock($handle, LOCK_EX);
    rewind($handle);
    $raw = stream_get_contents($handle);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    $state = is_array($decoded) ? $decoded : [];
    $state['forecasts'] = is_array($state['forecasts'] ?? null) ? $state['forecasts'] : [];

    foreach ($guessesByTime as $time => $guess) {
        if (array_key_exists($time, $state['forecasts']) || !is_array($guess)) continue;
        $state['forecasts'][$time] = [
            'time' => $time,
            'left' => $guess['left'] ?? null,
            'right' => $guess['right'] ?? null,
            'pair' => $guess['pair'] ?? '',
            'symbol' => $guess['symbol'] ?? '%',
            'direction' => $guess['direction'] ?? null,
            'change' => isset($guess['change']) ? abs((float)$guess['change']) : null,
        ];
    }

    if (count($state['forecasts']) > $forecast_window_limit) {
        uksort($state['forecasts'], static fn(string $a, string $b): int => strcmp($a, $b));
        $state['forecasts'] = array_slice($state['forecasts'], -$forecast_window_limit, $forecast_window_limit, true);
    }

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($state, JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $state['forecasts'];
}

/** Return every numeric OHLC row in CSV-candle shape. */
function csvCandles(string $csvFilePath): array
{
    $handle = @fopen($csvFilePath, 'r');
    if ($handle === false) return [];
    $candles = [];
    while (($row = fgetcsv($handle)) !== false) {
        if (!isset($row[0], $row[1], $row[2], $row[3], $row[4])) continue;
        if (!is_numeric($row[1]) || !is_numeric($row[2]) || !is_numeric($row[3]) || !is_numeric($row[4])) continue;
        $candles[] = [
            'time' => (string)$row[0],
            'open' => (float)$row[1],
            'high' => (float)$row[2],
            'low' => (float)$row[3],
            'close' => (float)$row[4],
        ];
    }
    fclose($handle);
    return $candles;
}

/** Mark which five-minute candles belong to the current forming boundary. */
function markFiveMinuteCandles(array $candles): array
{
    $currentBoundary = (int)floor(time() / 300) * 300;
    foreach ($candles as &$candle) {
        $epoch = yahooTimestamp((string)$candle['time']);
        $candle['forming'] = $epoch !== null && $epoch >= $currentBoundary;
    }
    unset($candle);
    return $candles;
}

/** Return the latest visible five-minute OHLC marks for the live window. */
function latestFiveMinuteCandles(string $csvFilePath, int $window = LIVE_CANDLE_WINDOW): array
{
    return markFiveMinuteCandles(array_slice(csvCandles($csvFilePath), -$window));
}

/** Merge multiple five-minute CSV sources by timestamp, preferring later files. */
function mergedFiveMinuteCandles(array $csvFilePaths): array
{
    $candlesByTime = [];
    foreach (array_values(array_unique($csvFilePaths)) as $csvFilePath) {
        if (!is_string($csvFilePath) || $csvFilePath === '' || !file_exists($csvFilePath)) continue;
        foreach (csvCandles($csvFilePath) as $candle) {
            $candlesByTime[(string)$candle['time']] = $candle;
        }
    }
    uksort($candlesByTime, static fn(string $a, string $b): int => strcmp($a, $b));
    return markFiveMinuteCandles(array_values($candlesByTime));
}

/** Aggregate five-minute candles into hourly OHLC buckets for the chart. */
function hourlyChartCandles(array $fiveMinuteCandles, int $windowHours = CHART_HOURLY_WINDOW): array
{
    if (!$fiveMinuteCandles) return [];
    $hourBuckets = [];
    $currentHourEpoch = (int)floor(time() / 3600) * 3600;

    foreach ($fiveMinuteCandles as $candle) {
        $epoch = yahooTimestamp((string)($candle['time'] ?? ''));
        if ($epoch === null) continue;
        $hourEpoch = (int)floor($epoch / 3600) * 3600;

        if (!isset($hourBuckets[$hourEpoch])) {
            $hourBuckets[$hourEpoch] = [
                'time' => gmdate('Y-m-d\TH:i:s\Z', $hourEpoch),
                'open' => (float)$candle['open'],
                'high' => (float)$candle['high'],
                'low' => (float)$candle['low'],
                'close' => (float)$candle['close'],
                'forming' => $hourEpoch >= $currentHourEpoch,
                'combinedMarks' => 0,
                'rows' => [],
            ];
        }

        $hourBuckets[$hourEpoch]['high'] = max((float)$hourBuckets[$hourEpoch]['high'], (float)$candle['high']);
        $hourBuckets[$hourEpoch]['low'] = min((float)$hourBuckets[$hourEpoch]['low'], (float)$candle['low']);
        $hourBuckets[$hourEpoch]['close'] = (float)$candle['close'];
        $hourBuckets[$hourEpoch]['forming'] = $hourEpoch >= $currentHourEpoch;
        $hourBuckets[$hourEpoch]['combinedMarks']++;
        $hourBuckets[$hourEpoch]['rows'][] = $candle;
    }

    ksort($hourBuckets, SORT_NUMERIC);
    return array_slice(array_values($hourBuckets), -max(1, $windowHours));
}

/** Resolve the real market direction at every CSV timestamp. */
function actualDirectionsByTime(string $csvFilePath): array
{
    $handle = @fopen($csvFilePath, 'r');
    if ($handle === false) return [];
    $directions = [];
    $previousClose = null;
    while (($row = fgetcsv($handle)) !== false) {
        if (!isset($row[0], $row[1], $row[4]) || !is_numeric($row[1]) || !is_numeric($row[4])) continue;
        $open = (float)$row[1];
        $close = (float)$row[4];
        $directions[(string)$row[0]] = candleDirection($open, $close);
        $previousClose = $close;
    }
    fclose($handle);
    return $directions;
}

/**
 * Freeze market direction only after one extra five-minute candle has fully
 * passed. Once a timestamp is stored, later provider revisions cannot replace
 * its observed direction.
 */
function finalizedActualDirectionsByTime(array $csvFilePaths, string $statePath, int $settlementSeconds = 600): array
{
    $handle = @fopen($statePath, 'c+');
    if ($handle === false) return [];

    flock($handle, LOCK_EX);
    rewind($handle);
    $raw = stream_get_contents($handle);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    $state = is_array($decoded) ? $decoded : [];
    $state['directions'] = is_array($state['directions'] ?? null) ? $state['directions'] : [];

    $currentBoundary = (int)floor(time() / 300) * 300;
    $settlementCutoff = $currentBoundary - max(600, $settlementSeconds);

    foreach (array_values(array_unique($csvFilePaths)) as $csvFilePath) {
        if (!is_string($csvFilePath) || $csvFilePath === '') continue;
        $csvHandle = @fopen($csvFilePath, 'r');
        if ($csvHandle === false) continue;

        $candlesByEpoch = [];
        while (($row = fgetcsv($csvHandle)) !== false) {
            if (!isset($row[0], $row[1], $row[4]) || !is_numeric($row[1]) || !is_numeric($row[4])) continue;
            $epoch = yahooTimestamp((string)$row[0]);
            if ($epoch === null) continue;
            $candlesByEpoch[$epoch] = [
                'time' => (string)$row[0],
                'epoch' => $epoch,
                'open' => (float)$row[1],
                'close' => (float)$row[4],
            ];
        }
        fclose($csvHandle);
        if (count($candlesByEpoch) < 2) continue;

        ksort($candlesByEpoch, SORT_NUMERIC);
        $candles = array_values($candlesByEpoch);
        for ($index = 1; $index < count($candles); $index++) {
            $candle = $candles[$index];
            if ($candle['epoch'] > $settlementCutoff) continue;
            $timeKey = $candle['time'];
            if (array_key_exists($timeKey, $state['directions'])) continue;

            $previous = $candles[$index - 1];
            $state['directions'][$timeKey] = [
                'time' => $timeKey,
                'direction' => candleDirection((float)$candle['open'], (float)$candle['close']),
                'close' => $candle['close'],
                'previous_close' => $previous['close'],
                'settled_at' => gmdate('Y-m-d\TH:i:s\Z'),
            ];
        }
    }

    if (count($state['directions']) > 2000) {
        uksort($state['directions'], static fn(string $a, string $b): int => strcmp($a, $b));
        $state['directions'] = array_slice($state['directions'], -2000, 2000, true);
    }

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($state, JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    $directions = [];
    foreach ($state['directions'] as $time => $record) {
        $direction = is_array($record) ? ($record['direction'] ?? null) : null;
        if ($direction === '+' || $direction === '-') $directions[$time] = $direction;
    }
    return $directions;
}

/** Return the newest completed five-minute candles, excluding the current forming row. */
function latestCompletedFiveMinuteCandles(array $candles, int $count = 12): array
{
    if (!$candles) return [];
    $completed = array_values(array_filter($candles, static fn(array $candle): bool => !($candle['forming'] ?? false)));
    if (!$completed) return [];
    return array_slice($completed, -max(1, $count));
}

/** Prefer frozen forecast guesses, then historical locked guesses, for a given candle window. */
function cachedGuessesForHour(array $candles, array $forecastByTime, array $guessByTime): array
{
    $guesses = [];
    foreach ($candles as $candle) {
        $time = (string)($candle['time'] ?? '');
        if ($time === '') continue;
        $guess = $forecastByTime[$time] ?? $guessByTime[$time] ?? null;
        if (is_array($guess)) $guesses[$time] = normalizeCngnGuess($guess);
    }
    return $guesses;
}

/** Score one completed five-minute candle for guessed action, forced long, forced short, and hindsight best. */
function scoreHourAuditRow(array $candle, ?array $guess): array
{
    $open = is_numeric($candle['open'] ?? null) ? (float)$candle['open'] : 0.0;
    $close = is_numeric($candle['close'] ?? null) ? (float)$candle['close'] : 0.0;
    $move = $close - $open;
    $actualDirection = $move > 0.0 ? '+' : ($move < 0.0 ? '-' : '0');
    $pair = guessPairLabel($guess);
    $storedDirection = is_array($guess) ? ($guess['direction'] ?? null) : null;
    $guessDirection = ($storedDirection === '+' || $storedDirection === '-')
        ? $storedDirection
        : newGuessDirectionFromPair($pair);
    // Audit action and RIGHT/WRONG must use the same locked direction.
    // Never derive the label through a newly flipped map while scoring the old row.
    $action = $guessDirection === '+' ? 'BUY' : ($guessDirection === '-' ? 'SELL' : 'NO TRADE');

    $longPnl = $move;
    $shortPnl = -$move;
    $strategyPnl = $action === 'BUY' ? $longPnl : ($action === 'SELL' ? $shortPnl : 0.0);
    $bestSide = $move > 0.0 ? 'LONG' : ($move < 0.0 ? 'SHORT' : 'FLAT');
    $bestPnl = abs($move);

    return [
        'time' => (string)($candle['time'] ?? ''),
        'guess' => [
            'pair' => $pair,
            'action' => $action,
            'direction' => $guessDirection,
            'change' => guessStoredChange($guess, 0.0),
        ],
        'actual' => [
            'open' => $open,
            'close' => $close,
            'move' => $move,
            'direction' => $actualDirection,
            'settled' => true,
        ],
        'guess_right' => ($guessDirection === '+' || $guessDirection === '-') && ($actualDirection === '+' || $actualDirection === '-')
            ? ($guessDirection === $actualDirection)
            : null,
        'strategy' => [
            'executed' => $action === 'BUY' || $action === 'SELL',
            'pnl' => $strategyPnl,
            'won' => $action === 'BUY' ? $longPnl > 0.0 : ($action === 'SELL' ? $shortPnl > 0.0 : null),
        ],
        'long' => [
            'pnl' => $longPnl,
            'won' => $longPnl > 0.0,
        ],
        'short' => [
            'pnl' => $shortPnl,
            'won' => $shortPnl > 0.0,
        ],
        'best_side' => [
            'side' => $bestSide,
            'pnl' => $bestPnl,
            'won' => $move !== 0.0 ? true : null,
        ],
    ];
}

/** Build the one-hour audit summary from locked guesses and completed five-minute candles. */
function buildHourAuditSummary(string $symbol, array $candles, array $cachedGuessesByTime): array
{
    $rows = [];
    $guessRight = 0;
    $guessWrong = 0;
    $strategyWins = 0;
    $strategyLosses = 0;
    $strategyPnl = 0.0;
    $longWins = 0;
    $longLosses = 0;
    $longPnl = 0.0;
    $shortWins = 0;
    $shortLosses = 0;
    $shortPnl = 0.0;
    $bestWins = 0;
    $bestLosses = 0;
    $bestPnl = 0.0;
    $sellSignalStreak = 0;
    $downCandleStreak = 0;
    $maxSellSignalStreak = 0;
    $maxDownCandleStreak = 0;

    foreach ($candles as $candle) {
        $time = (string)($candle['time'] ?? '');
        $row = scoreHourAuditRow($candle, $cachedGuessesByTime[$time] ?? null);

        $signalAction = strtoupper(trim((string)($row['guess']['action'] ?? 'NO TRADE')));
        $actualDirection = (string)($row['actual']['direction'] ?? '?');
        $sellSignalStreak = $signalAction === 'SELL' ? $sellSignalStreak + 1 : 0;
        $downCandleStreak = $actualDirection === '-' ? $downCandleStreak + 1 : 0;
        $maxSellSignalStreak = max($maxSellSignalStreak, $sellSignalStreak);
        $maxDownCandleStreak = max($maxDownCandleStreak, $downCandleStreak);
        $row['sell_signal_streak'] = $sellSignalStreak;
        $row['down_candle_streak'] = $downCandleStreak;
        $rows[] = $row;

        if ($row['guess_right'] === true) $guessRight++;
        elseif ($row['guess_right'] === false) $guessWrong++;

        if (!empty($row['strategy']['executed'])) {
            $strategyPnl += (float)$row['strategy']['pnl'];
            if ($row['strategy']['won'] === true) $strategyWins++;
            elseif ($row['strategy']['won'] === false) $strategyLosses++;
        }

        $longPnl += (float)$row['long']['pnl'];
        $shortPnl += (float)$row['short']['pnl'];
        $bestPnl += (float)$row['best_side']['pnl'];

        if ($row['long']['won']) $longWins++; else $longLosses++;
        if ($row['short']['won']) $shortWins++; else $shortLosses++;
        if ($row['best_side']['won']) $bestWins++; else $bestLosses++;
    }

    $guessTotal = $guessRight + $guessWrong;
    return [
        'symbol' => $symbol,
        'window_start' => $rows ? (string)$rows[0]['time'] : '',
        'window_end' => $rows ? (string)$rows[count($rows) - 1]['time'] : '',
        'rows' => $rows,
        'guess_accuracy' => [
            'right' => $guessRight,
            'wrong' => $guessWrong,
            'percent' => $guessTotal > 0 ? round(($guessRight / $guessTotal) * 100, 2) : 0.0,
        ],
        'strategy' => [
            'wins' => $strategyWins,
            'losses' => $strategyLosses,
            'net_pnl' => $strategyPnl,
        ],
        'long' => [
            'wins' => $longWins,
            'losses' => $longLosses,
            'net_pnl' => $longPnl,
        ],
        'short' => [
            'wins' => $shortWins,
            'losses' => $shortLosses,
            'net_pnl' => $shortPnl,
        ],
        'best_side' => [
            'wins' => $bestWins,
            'losses' => $bestLosses,
            'net_pnl' => $bestPnl,
        ],
        'sequences' => [
            'current_sell_signal_streak' => $sellSignalStreak,
            'current_down_candle_streak' => $downCandleStreak,
            'max_sell_signal_streak' => $maxSellSignalStreak,
            'max_down_candle_streak' => $maxDownCandleStreak,
        ],
    ];
}

/** Render the one-hour audit as a compact table. */
function renderHourAuditTable(array $auditSummary): string
{
    $rows = $auditSummary['rows'] ?? [];
    $html = '<tr><td>Time</td><td>Predicted candle</td><td>What candle did</td><td>Result</td><td>What happened</td><td>SELL run</td><td>DOWN run</td><td>Strategy</td><td>Long</td><td>Short</td><td>Best</td></tr>';
    foreach ($rows as $row) {
        if (!is_array($row)) continue;
        $time = (string)($row['time'] ?? '');
        $epoch = yahooTimestamp($time);
        $timeLabel = $epoch !== null ? gmdate('m/d H:i', $epoch) : $time;
        $guess = is_array($row['guess'] ?? null) ? $row['guess'] : [];
        $actual = is_array($row['actual'] ?? null) ? $row['actual'] : [];
        $strategy = is_array($row['strategy'] ?? null) ? $row['strategy'] : [];
        $long = is_array($row['long'] ?? null) ? $row['long'] : [];
        $short = is_array($row['short'] ?? null) ? $row['short'] : [];
        $best = is_array($row['best_side'] ?? null) ? $row['best_side'] : [];

        $move = (float)($actual['move'] ?? 0.0);
        $actualOpen = (float)($actual['open'] ?? 0.0);
        $movePercent = $actualOpen != 0.0 ? ($move / $actualOpen) * 100.0 : 0.0;
        $moveLabel = ($move >= 0.0 ? '+' : '-') . '$' . number_format(abs($move), 8)
            . ' (' . ($movePercent >= 0.0 ? '+' : '-') . number_format(abs($movePercent), 4) . '%)';
        $guessAction = strtoupper(trim((string)($guess['action'] ?? 'NO TRADE')));
        $predictedDirection = (string)($guess['direction'] ?? '');
        $predictedLabel = $guessAction === 'BUY' ? 'UP / BUY' : ($guessAction === 'SELL' ? 'DOWN / SELL' : 'UNKNOWN');
        if ($predictedDirection === '+' || $predictedDirection === '-') {
            $predictedLabel .= ' · ' . ($predictedDirection === '+' ? 'green' : 'red') . ' candle';
        }
        $actualDirection = (string)($actual['direction'] ?? '?');
        $actualLabel = ($actualDirection === '+' ? 'UP / green' : ($actualDirection === '-' ? 'DOWN / red' : 'FLAT / gray')) . ' · ' . $moveLabel;
        $guessResult = ($row['guess_right'] ?? null) === true
            ? 'RIGHT'
            : (($row['guess_right'] ?? null) === false ? 'WRONG' : 'UNRESOLVED');
        $strategyPnl = (float)($strategy['pnl'] ?? 0.0);
        $longPnl = (float)($long['pnl'] ?? 0.0);
        $shortPnl = (float)($short['pnl'] ?? 0.0);
        $bestPnl = (float)($best['pnl'] ?? 0.0);

        $guessClass = ($row['guess_right'] ?? null) === true
            ? 'result-gain-cell'
            : (($row['guess_right'] ?? null) === false ? 'result-loss-cell' : 'result-neutral-cell');
        $strategyClass = !empty($strategy['executed'])
            ? ($strategyPnl >= 0.0 ? 'result-gain-cell' : 'result-loss-cell')
            : 'result-neutral-cell';
        $longClass = $longPnl >= 0.0 ? 'result-gain-cell' : 'result-loss-cell';
        $shortClass = $shortPnl >= 0.0 ? 'result-gain-cell' : 'result-loss-cell';
        $resultClass = ($row['guess_right'] ?? null) === true
            ? 'result-gain-cell'
            : (($row['guess_right'] ?? null) === false ? 'result-loss-cell' : 'result-neutral-cell');
        $sellRun = (int)($row['sell_signal_streak'] ?? 0);
        $downRun = (int)($row['down_candle_streak'] ?? 0);
        $happenedAction = strtoupper(trim((string)($guess['action'] ?? 'NO TRADE')));
        $happenedLabel = !empty($strategy['executed'])
            ? $happenedAction . ' EXECUTED · ' . $moveLabel . ' · P&L ' . formatSignedMoney($strategyPnl, 8)
            : ($happenedAction === 'NO TRADE' ? 'NO TRADE · ' : $happenedAction . ' HELD · ') . $moveLabel;
        $happenedClass = !empty($strategy['executed'])
            ? $strategyClass
            : 'result-neutral-cell';

        $html .= '<tr>'
            . '<td' . ($epoch !== null ? ' data-epoch="' . ($epoch * 1000) . '"' : '') . '>' . htmlspecialchars($timeLabel) . '</td>'
            . '<td class="' . $guessClass . '">' . htmlspecialchars($predictedLabel) . '</td>'
            . '<td>' . htmlspecialchars($actualLabel) . '</td>'
            . '<td class="' . $resultClass . '">' . htmlspecialchars($guessResult) . '</td>'
            . '<td class="' . $happenedClass . '">' . htmlspecialchars($happenedLabel) . '</td>'
            . '<td>' . ($sellRun > 0 ? htmlspecialchars((string)$sellRun . ' SELL' . ($sellRun === 1 ? '' : 'S')) : '—') . '</td>'
            . '<td>' . ($downRun > 0 ? htmlspecialchars((string)$downRun . ' DOWN' . ($downRun === 1 ? '' : 'S')) : '—') . '</td>'
            . '<td class="' . $strategyClass . '">' . htmlspecialchars(formatSignedMoney($strategyPnl, 8)) . '</td>'
            . '<td class="' . $longClass . '">' . htmlspecialchars(formatSignedMoney($longPnl, 8)) . '</td>'
            . '<td class="' . $shortClass . '">' . htmlspecialchars(formatSignedMoney($shortPnl, 8)) . '</td>'
            . '<td class="result-gain-cell">' . htmlspecialchars((string)($best['side'] ?? '—') . ' ' . formatSignedMoney($bestPnl, 8)) . '</td>'
            . '</tr>';
    }
    return $html;
}

/** Convert a Yahoo UTC timestamp to an unambiguous Unix epoch. */
function yahooTimestamp(string $value): ?int
{
    $value = trim($value);
    if ($value === '') return null;
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d\\TH:i:s\\Z', $value, new DateTimeZone('UTC'));
    if ($parsed instanceof DateTimeImmutable) return $parsed->getTimestamp();
    $fallback = strtotime($value);
    return $fallback === false ? null : $fallback;
}

/** Pull the left/right sign pair from the first projected CNGN row. */
function currentCngnGuess(string $resultHtml): ?array
{
    if (!preg_match_all('/<tr\b[^>]*>.*?<\/tr>/is', $resultHtml, $rows)) return null;
    foreach ($rows[0] as $htmlRow) {
        $guess = cngnGuessFromRow($htmlRow);
        if (is_array($guess)) return $guess;
    }
    return null;
}

function defaultPairDirectionMap(): array
{
    return [
        '++' => '+',
        '--' => '+',
        '+-' => '-',
        '-+' => '-',
    ];
}

function normalizePairDirectionMap(array $map): array
{
    $normalized = defaultPairDirectionMap();
    foreach ($normalized as $pair => $direction) {
        $candidate = (string)($map[$pair] ?? '');
        if ($candidate === '+' || $candidate === '-') {
            $normalized[$pair] = $candidate;
        }
    }
    return $normalized;
}

function setActivePairDirectionMap(array $map): void
{
    $GLOBALS['active_pair_direction_map'] = normalizePairDirectionMap($map);
}

function activePairDirectionMap(): array
{
    $map = $GLOBALS['active_pair_direction_map'] ?? null;
    return is_array($map) ? normalizePairDirectionMap($map) : defaultPairDirectionMap();
}

function newGuessDirectionFromPair(string $pair): ?string
{
    $pair = trim($pair);
    if (!preg_match('/^[+-]{2}$/', $pair)) return null;
    $map = activePairDirectionMap();
    $direction = (string)($map[$pair] ?? '');
    return ($direction === '+' || $direction === '-') ? $direction : null;
}

function shannonEntropyFromCounts(array $counts): float
{
    $total = 0.0;
    foreach ($counts as $count) {
        if (is_numeric($count) && (float)$count > 0.0) {
            $total += (float)$count;
        }
    }
    if ($total <= 0.0) return 0.0;
    $entropy = 0.0;
    foreach ($counts as $count) {
        $value = is_numeric($count) ? (float)$count : 0.0;
        if ($value <= 0.0) continue;
        $p = $value / $total;
        $entropy -= $p * (log($p, 2));
    }
    return $entropy;
}

function buildEndCompressionState(array $resolvedResultsByTime, array $directionMap, int $windowSize = ONE_HOUR_CANDLE_COUNT): array
{
    $ordered = [];
    foreach ($resolvedResultsByTime as $resolved) {
        if (!is_array($resolved)) continue;
        $time = trim((string)($resolved['time'] ?? ''));
        $pair = trim((string)($resolved['pair'] ?? ''));
        $epoch = $time !== '' ? yahooTimestamp($time) : null;
        if ($epoch === null || !preg_match('/^[+-]{2}$/', $pair)) continue;
        $direction = (string)($directionMap[$pair] ?? '');
        if ($direction !== '+' && $direction !== '-') continue;
        $ordered[] = [
            'time' => $time,
            'epoch' => $epoch,
            'pair' => $pair,
            'direction' => $direction,
        ];
    }
    usort($ordered, static fn(array $a, array $b): int => ($a['epoch'] ?? 0) <=> ($b['epoch'] ?? 0));
    $window = $windowSize > 0 ? array_slice($ordered, -$windowSize) : $ordered;
    $up = 0;
    $down = 0;
    $tailDirection = '';
    $tailStreak = 0;
    $rlePhases = [];
    $currentPhase = null;
    foreach ($window as $entry) {
        $direction = (string)($entry['direction'] ?? '');
        if ($direction !== '+' && $direction !== '-') continue;
        if ($currentPhase === null || $currentPhase['direction'] !== $direction) {
            if ($currentPhase !== null) $rlePhases[] = $currentPhase;
            $currentPhase = [
                'direction' => $direction,
                'start' => (string)($entry['time'] ?? ''),
                'end' => (string)($entry['time'] ?? ''),
                'length' => 1,
                'compression' => 100.0,
            ];
        } else {
            $currentPhase['end'] = (string)($entry['time'] ?? '');
            $currentPhase['length']++;
        }
    }
    if ($currentPhase !== null) $rlePhases[] = $currentPhase;
    $perfectLengths = array_map(static fn(array $phase): int => (int)($phase['length'] ?? 0), $rlePhases);
    $perfectLengths = array_values(array_filter($perfectLengths, static fn(int $length): bool => $length > 0));
    for ($index = count($window) - 1; $index >= 0; $index--) {
        $entry = $window[$index] ?? null;
        if (!is_array($entry)) continue;
        $direction = (string)($entry['direction'] ?? '');
        if ($direction === '+') $up++;
        elseif ($direction === '-') $down++;
        if ($tailDirection === '') {
            $tailDirection = $direction;
            $tailStreak = 1;
        } elseif ($direction === $tailDirection) {
            $tailStreak++;
        } else {
            break;
        }
    }
    $sampleCount = count($window);
    $entropy = shannonEntropyFromCounts(['up' => $up, 'down' => $down]);
    $normalizedEntropy = $sampleCount > 1 ? min(1.0, $entropy / 1.0) : 0.0;
    $compressionScore = max(0.0, min(100.0, (1.0 - $normalizedEntropy) * 100.0));
    $dominantDirection = $up === $down
        ? 'MIXED'
        : ($up > $down ? 'BUY FAMILY' : 'SELL FAMILY');

    return [
        'window_size' => $windowSize,
        'sample_count' => $sampleCount,
        'up_count' => $up,
        'down_count' => $down,
        'entropy' => round($normalizedEntropy * 100.0, 2),
        'compression_score' => round($compressionScore, 2),
        'tail_direction' => $tailDirection,
        'tail_streak' => $tailStreak,
        'phase_count' => count($rlePhases),
        'phase_changes' => max(0, count($rlePhases) - 1),
        'rle_phases' => $rlePhases,
        'perfect_compression_min_parts' => $perfectLengths ? min($perfectLengths) : 0,
        'perfect_compression_max_parts' => $perfectLengths ? min(max($perfectLengths), max(1, $windowSize)) : 0,
        'dominant_direction' => $dominantDirection,
        'updated_at' => gmdate('Y-m-d\TH:i:s\Z'),
    ];
}

/** Score the first resolved candle of each saved BUY/SELL phase. */
function buildPhaseActionWinStats(array $resolvedResultsByTime): array
{
    $stats = [
        'BUY' => ['action' => 'BUY', 'right' => 0, 'wrong' => 0, 'total' => 0, 'percentage' => 0.0],
        'SELL' => ['action' => 'SELL', 'right' => 0, 'wrong' => 0, 'total' => 0, 'percentage' => 0.0],
    ];
    $ordered = array_values(array_filter($resolvedResultsByTime, static fn($row): bool => is_array($row)));
    usort($ordered, static function (array $left, array $right): int {
        return ((int)(yahooTimestamp((string)($left['time'] ?? '')) ?? 0))
            <=> ((int)(yahooTimestamp((string)($right['time'] ?? '')) ?? 0));
    });
    $previousAction = '';
    foreach ($ordered as $resolved) {
        $predicted = (string)($resolved['predicted'] ?? '');
        $action = $predicted === '+' ? 'BUY' : ($predicted === '-' ? 'SELL' : '');
        if ($action === '' || $action === $previousAction) {
            if ($action !== '') $previousAction = $action;
            continue;
        }
        $actual = (string)($resolved['actual'] ?? ($resolved['actual_direction'] ?? ''));
        $right = ($action === 'BUY' && $actual === '+') || ($action === 'SELL' && $actual === '-');
        $stats[$action]['total']++;
        if ($right) $stats[$action]['right']++; else $stats[$action]['wrong']++;
        $previousAction = $action;
    }
    foreach ($stats as &$stat) {
        $stat['percentage'] = $stat['total'] > 0
            ? round(((int)$stat['right'] / (int)$stat['total']) * 100, 1)
            : 0.0;
    }
    unset($stat);
    return $stats;
}

/** Describe the current action phase and its forecasted distance to a change. */
function buildCurrentPhaseStatus(array $timeline): array
{
    $currentIndex = null;
    foreach ($timeline as $index => $record) {
        if (is_array($record) && (($record['phase'] ?? '') === 'current')) {
            $currentIndex = $index;
            break;
        }
    }
    if ($currentIndex === null) {
        return ['action' => 'NO TRADE', 'steps_in' => 0, 'steps_until_change' => null, 'horizon_steps' => 0];
    }
    $current = $timeline[$currentIndex] ?? [];
    $action = strtoupper(trim((string)($current['guessAction'] ?? 'NO TRADE')));
    if ($action !== 'BUY' && $action !== 'SELL') $action = 'HOLD';
    $stepsIn = 1;
    $stepsUntilChange = null;
    for ($index = $currentIndex + 1; $index < count($timeline); $index++) {
        $nextAction = strtoupper(trim((string)($timeline[$index]['guessAction'] ?? 'NO TRADE')));
        if ($nextAction !== $action) {
            $stepsUntilChange = $index - $currentIndex;
            break;
        }
        $stepsIn++;
    }
    return [
        'action' => $action,
        'steps_in' => $stepsIn,
        'steps_until_change' => $stepsUntilChange,
        'horizon_steps' => max(0, count($timeline) - $currentIndex - 1),
    ];
}

/** Build resolved BUY/SELL truth buckets by calendar quarter and regime branch. */
function buildQuarterRegimeStats(array $resolvedResultsByTime, array $baseMap): array
{
    $stats = [];
    foreach ($resolvedResultsByTime as $resolved) {
        if (!is_array($resolved)) continue;
        $time = trim((string)($resolved['time'] ?? ''));
        $epoch = $time !== '' ? yahooTimestamp($time) : null;
        $pair = trim((string)($resolved['pair'] ?? ''));
        if ($epoch === null || !preg_match('/^[+-]{2}$/', $pair)) continue;
        $direction = (string)($baseMap[$pair] ?? '');
        if ($direction !== '+' && $direction !== '-') continue;
        $family = $direction === '+' ? 'BUY' : 'SELL';
        $agreement = $pair[0] === $pair[1] ? 'AGREE' : 'DISAGREE';
        $quarter = (string)date('Y', $epoch) . '-Q' . (string)ceil((int)date('n', $epoch) / 3);
        $key = $quarter . '|' . $family . '|' . $agreement;
        if (!isset($stats[$key])) {
            $stats[$key] = [
                'quarter' => $quarter,
                'family' => $family,
                'agreement' => $agreement,
                'right' => 0,
                'wrong' => 0,
                'total' => 0,
                'percentage' => 0.0,
            ];
        }
        $stats[$key]['total']++;
        if (($resolved['right'] ?? null) === true) $stats[$key]['right']++;
        else $stats[$key]['wrong']++;
    }
    foreach ($stats as &$stat) {
        $stat['percentage'] = $stat['total'] > 0
            ? round(((int)$stat['right'] / (int)$stat['total']) * 100.0, 1)
            : 0.0;
    }
    unset($stat);
    return $stats;
}

function buildHourlyPairDirectionState(
    array $resolvedResultsByTime,
    string $symbol,
    int $currentBoundaryEpoch,
    int $lookbackHours = 12,
    int $minimumSamples = 3
): array {
    $fallbackMap = activePairDirectionMap();
    $latestResolvedEpoch = null;
    $compressionState = buildEndCompressionState($resolvedResultsByTime, $fallbackMap, ONE_HOUR_CANDLE_COUNT);
    $pairSamples = array(
        '++' => array('up' => 0, 'down' => 0, 'total' => 0),
        '--' => array('up' => 0, 'down' => 0, 'total' => 0),
        '+-' => array('up' => 0, 'down' => 0, 'total' => 0),
        '-+' => array('up' => 0, 'down' => 0, 'total' => 0),
    );

    foreach ($resolvedResultsByTime as $resolved) {
        if (!is_array($resolved)) continue;
        $resolvedTime = trim((string)($resolved['time'] ?? ''));
        $resolvedEpoch = $resolvedTime !== '' ? yahooTimestamp($resolvedTime) : null;
        if ($resolvedEpoch !== null && ($latestResolvedEpoch === null || $resolvedEpoch > $latestResolvedEpoch)) {
            $latestResolvedEpoch = $resolvedEpoch;
        }
    }

    if ($latestResolvedEpoch === null) {
        return array(
            'symbol' => strtoupper(trim($symbol)),
            'hour_bucket' => gmdate('Y-m-d\TH:00:00\Z', (int)floor($currentBoundaryEpoch / 3600) * 3600),
            'window_start' => '',
            'lookback_hours' => $lookbackHours,
            'minimum_samples' => $minimumSamples,
            'map' => $fallbackMap,
            'pair_samples' => $pairSamples,
            'compression' => $compressionState,
            'updated_at' => gmdate('Y-m-d\TH:i:s\Z'),
        );
    }

    $latestHourEpoch = (int)floor($latestResolvedEpoch / 3600) * 3600;
    $windowStartEpoch = $latestHourEpoch - (max(1, $lookbackHours) - 1) * 3600;

    foreach ($resolvedResultsByTime as $resolved) {
        if (!is_array($resolved)) continue;
        $pair = trim((string)($resolved['pair'] ?? ''));
        if (!isset($pairSamples[$pair])) continue;
        $resolvedTime = trim((string)($resolved['time'] ?? ''));
        $resolvedEpoch = $resolvedTime !== '' ? yahooTimestamp($resolvedTime) : null;
        if ($resolvedEpoch === null || $resolvedEpoch < $windowStartEpoch || $resolvedEpoch > $latestResolvedEpoch) continue;
        $actual = (string)($resolved['actual'] ?? ($resolved['actual_direction'] ?? ''));
        if ($actual !== '+' && $actual !== '-') continue;
        $bucketKey = $actual === '+' ? 'up' : 'down';
        $pairSamples[$pair][$bucketKey]++;
        $pairSamples[$pair]['total']++;
    }

    $map = $fallbackMap;
    $compressionScore = (float)($compressionState['compression_score'] ?? 0.0);
    foreach ($pairSamples as $pair => $sample) {
        if ((int)$sample['total'] < max(1, $minimumSamples)) continue;
        if ($compressionScore < 35.0) continue;
        if ((int)$sample['up'] > (int)$sample['down']) {
            $map[$pair] = '+';
        } elseif ((int)$sample['down'] > (int)$sample['up']) {
            $map[$pair] = '-';
        }
    }

    return array(
        'symbol' => strtoupper(trim($symbol)),
        'hour_bucket' => gmdate('Y-m-d\TH:00:00\Z', $latestHourEpoch),
        'window_start' => gmdate('Y-m-d\TH:00:00\Z', $windowStartEpoch),
        'lookback_hours' => $lookbackHours,
        'minimum_samples' => $minimumSamples,
        'map' => $map,
        'pair_samples' => $pairSamples,
        'compression' => $compressionState,
        'updated_at' => gmdate('Y-m-d\TH:i:s\Z'),
    );
}

/** Map a full sign pair to the action used for newly generated guesses. */
function signalAction(?string $symbol): string
{
    if (!is_string($symbol) || $symbol === '' || $symbol === '%') return 'NO TRADE';
    $direction = strlen($symbol) === 2
        ? newGuessDirectionFromPair($symbol)
        : substr($symbol, 0, 1);
    return $direction === '+' ? 'BUY' : ($direction === '-' ? 'SELL' : 'NO TRADE');
}

/** Return a guess unchanged; saved answers must never be repaired. */
function normalizeCngnGuess(?array $guess): ?array
{
    return is_array($guess) ? $guess : null;
}

/** Display the original saved pair without changing the saved answer. */
function guessPairLabel(?array $guess): string
{
    if (!is_array($guess)) return '%';
    $pair = (string)($guess['pair'] ?? '');
    return preg_match('/^[+-]{2}$/', $pair) ? $pair : (string)($guess['symbol'] ?? '%');
}

/** Keep a locked direction; apply the current rule only to a new/unresolved pair. */
function guessStoredAction(?array $guess): string
{
    if (!is_array($guess)) return 'NO TRADE';
    $pair = guessPairLabel($guess);
    $storedDirection = $guess['direction'] ?? null;
    // A timestamp-locked table guess owns its action. The active flip map is
    // only for new/unresolved pairs that do not already carry a direction.
    $direction = ($storedDirection === '+' || $storedDirection === '-')
        ? $storedDirection
        : newGuessDirectionFromPair($pair);
    return $direction === '+' ? 'BUY' : ($direction === '-' ? 'SELL' : 'NO TRADE');
}

/**
 * Find a high-confidence transition after a run of identical pair calls.
 * Example: -+, -+, -+, ++.  The transition is eligible when the same
 * run-length/pair transition has at least twenty resolved examples and clears
 * the symbol's configured trust threshold.
 */
function sequenceTradeSignal(
    array $guessesByTime,
    array $resolvedResultsByTime,
    ?array $currentGuess = null,
    float $minimumAccuracy = 75.0
): array
{
    $ordered = [];
    foreach ($guessesByTime as $time => $guess) {
        if (!is_array($guess)) continue;
        $pair = guessPairLabel($guess);
        if (!preg_match('/^[+-]{2}$/', $pair)) continue;
        $ordered[] = ['time' => (string)$time, 'pair' => $pair];
    }
    if (is_array($currentGuess)) {
        $currentPair = guessPairLabel($currentGuess);
        if (preg_match('/^[+-]{2}$/', $currentPair)) {
            $currentTime = trim((string)($currentGuess['time'] ?? ''));
            if ($currentTime === '') $currentTime = '__current__';
            $found = false;
            foreach ($ordered as &$item) {
                if ($item['time'] === $currentTime) {
                    $item['pair'] = $currentPair;
                    $found = true;
                    break;
                }
            }
            unset($item);
            if (!$found) $ordered[] = ['time' => $currentTime, 'pair' => $currentPair];
        }
    }

    $empty = [
        'enabled' => false,
        'action' => 'NO TRADE',
        'run_length' => 0,
        'trade_count' => 0,
        'pair' => '',
        'run_pair' => '',
        'accuracy' => 0.0,
        'samples' => 0,
    ];
    $count = count($ordered);
    if ($count < 3) return $empty;
    $currentIndex = $count - 1;
    $newPair = $ordered[$currentIndex]['pair'];
    $runPair = $ordered[$currentIndex - 1]['pair'];
    if ($newPair === $runPair) return $empty;

    $runLength = 0;
    for ($index = $currentIndex - 1; $index >= 0 && $ordered[$index]['pair'] === $runPair; $index--) {
        $runLength++;
    }
    if ($runLength < 1) return $empty;

    $samples = 0;
    $right = 0;
    for ($index = $runLength; $index < $count; $index++) {
        if ($ordered[$index]['pair'] !== $newPair) continue;
        $priorPair = $ordered[$index - 1]['pair'] ?? '';
        if ($priorPair === $newPair) continue;
        $historicalRun = 0;
        for ($prior = $index - 1; $prior >= 0 && $ordered[$prior]['pair'] === $priorPair; $prior--) {
            $historicalRun++;
        }
        if ($historicalRun !== $runLength) continue;
        $result = $resolvedResultsByTime[$ordered[$index]['time']] ?? null;
        if (!is_array($result) || !array_key_exists('right', $result)) continue;
        $samples++;
        if (($result['right'] ?? false) === true) $right++;
    }
    $accuracy = $samples > 0 ? ($right / $samples) * 100.0 : 0.0;
    // Three wins out of three is not enough evidence for a trading rule.
    if ($samples < 20 || $accuracy < max(1.0, min(100.0, $minimumAccuracy))) return $empty;

    return [
        'enabled' => true,
        'action' => signalAction($newPair),
        'run_length' => $runLength,
        'trade_count' => $runLength + 1,
        'pair' => $newPair,
        'run_pair' => $runPair,
        'accuracy' => $accuracy,
        'samples' => $samples,
    ];
}

/** Reuse the last actual sell size as the base for ahead-of-time scale-outs. */
function latestExecutedSellAmount(array $trades, float $fallbackAmount = 0.0): float
{
    for ($index = count($trades) - 1; $index >= 0; $index--) {
        $trade = $trades[$index] ?? null;
        if (!is_array($trade)) continue;
        $action = strtoupper(trim((string)($trade['action'] ?? '')));
        if (strpos($action, 'SELL') !== 0) continue;
        $amount = is_numeric($trade['amount'] ?? null) ? (float)$trade['amount'] : 0.0;
        if ($amount > 0.0) return $amount;
    }
    return max(0.0, $fallbackAmount);
}

/** Build normalized Gaussian weights across the next twelve 5-minute slots. */
function gaussianBellCurveWeights(int $count): array
{
    $count = max(1, $count);
    $center = ($count - 1) / 2.0;
    $sigma = max(1.0, $count / 4.0);
    $weights = [];
    $sum = 0.0;
    for ($index = 0; $index < $count; $index++) {
        $distance = ($index - $center) / $sigma;
        $weight = exp(-0.5 * $distance * $distance);
        $weights[] = $weight;
        $sum += $weight;
    }
    if ($sum <= 0.0) {
        return array_fill(0, $count, 1.0 / $count);
    }
    foreach ($weights as $index => $weight) {
        $weights[$index] = $weight / $sum;
    }
    return $weights;
}

/** Sum realized P&L only from closed trade records in the current wallet ledger. */
function recomputeRealizedPnlFromTrades(array $trades): float
{
    $realized = 0.0;
    foreach ($trades as $trade) {
        if (!is_array($trade)) continue;
        if (!is_numeric($trade['realized_pnl'] ?? null)) continue;
        $action = strtoupper(trim((string)($trade['action'] ?? '')));
        if (!str_starts_with($action, 'SELL')) continue;
        $realized += (float)$trade['realized_pnl'];
    }
    return $realized;
}

/** Normalize wallet display state fields that should always be derivable from trades. */
function normalizeTraderDisplayState(array $state): array
{
    $state['trades'] = is_array($state['trades'] ?? null) ? $state['trades'] : [];
    if (is_numeric($state['equity_value'] ?? null) && is_numeric($state['starting_pot'] ?? null)) {
        $state['net_pnl'] = (float)$state['equity_value'] - (float)$state['starting_pot'];
        $state['sim_net_move'] = (float)$state['net_pnl'];
    }
    $openPnl = is_numeric($state['open_pnl'] ?? null) ? (float)$state['open_pnl'] : 0.0;
    $netPnl = is_numeric($state['net_pnl'] ?? null) ? (float)$state['net_pnl'] : 0.0;
    $state['realized_move'] = $netPnl - $openPnl;
    return $state;
}

/** Turn trust, carry-forward, and the last-hour audit into a bounded attack multiplier. */
function buildAttackProfile(float $trustPercent, float $carryForwardAccuracy, float $hourAuditPercent): array
{
    $trust = max(0.0, min(100.0, $trustPercent));
    $carry = max(0.0, min(100.0, $carryForwardAccuracy));
    $audit = max(0.0, min(100.0, $hourAuditPercent));
    $cappedCarry = min(80.0, $carry);
    $score = ($trust * 0.50) + ($audit * 0.40) + ($cappedCarry * 0.10);

    $factor = 1.00;
    $label = 'BASE';
    if ($audit < 60.0) {
        $factor = 0.85;
        $label = 'RESTRAINT';
    } elseif ($trust >= 95.0 && $audit >= 90.0 && $score >= 90.0) {
        $factor = 1.60;
        $label = 'FULL ATTACK';
    } elseif ($score >= 92.0) {
        $factor = 1.45;
        $label = 'STRONG ATTACK';
    } elseif ($score >= 85.0) {
        $factor = 1.30;
        $label = 'ATTACK';
    } elseif ($score >= 75.0) {
        $factor = 1.15;
        $label = 'PRESS';
    }

    return [
        'active' => $factor > 1.0,
        'factor' => $factor,
        'label' => $label,
        'score' => $score,
        'reason' => 'trust ' . number_format($trust, 1) . '% • audit ' . number_format($audit, 1) . '% • carry ref ' . number_format($carry, 1) . '%',
    ];
}

function buildSneakProfile(?array $currentGuess, array $compressionState, array $internalAgreement): array
{
    $pair = guessPairLabel($currentGuess);
    $left = is_array($currentGuess) ? trim((string)($currentGuess['left'] ?? '')) : '';
    $right = is_array($currentGuess) ? trim((string)($currentGuess['right'] ?? '')) : '';
    $pairMixed = $pair === '+-' || $pair === '-+';
    $leftRightDisagree = ($left === '+' || $left === '-') && ($right === '+' || $right === '-') && $left !== $right;
    $compressionScore = max(0.0, min(100.0, (float)($compressionState['compression_score'] ?? 0.0)));
    $recentAgreement = max(0.0, min(100.0, (float)($internalAgreement['recent_percent'] ?? 0.0)));
    $tailStreak = max(0, (int)($compressionState['tail_streak'] ?? 0));
    $confidence = ($compressionScore * 0.55) + ($recentAgreement * 0.45);
    $factor = 0.45;
    if ($confidence >= 70.0 && $tailStreak >= 2) {
        $factor = 0.60;
    } elseif ($confidence < 45.0) {
        $factor = 0.30;
    }

    return [
        'pair' => $pair,
        'pair_mixed' => $pairMixed,
        'left_right_disagree' => $leftRightDisagree,
        'compression_score' => round($compressionScore, 2),
        'recent_agreement' => round($recentAgreement, 2),
        'tail_streak' => $tailStreak,
        'confidence' => round($confidence, 2),
        'factor' => round($factor, 4),
        'eligible' => $pairMixed || $leftRightDisagree,
    ];
}

function chooseHourAuditExecutionWinner(array $hourAuditStrategy, array $hourAuditLong, array $hourAuditShort): array
{
    $candidates = [
        [
            'key' => 'strategy',
            'label' => 'STRATEGY',
            'action' => 'FORMULA',
            'wins' => (int)($hourAuditStrategy['wins'] ?? 0),
            'losses' => (int)($hourAuditStrategy['losses'] ?? 0),
            'net_pnl' => (float)($hourAuditStrategy['net_pnl'] ?? 0.0),
        ],
        [
            'key' => 'long',
            'label' => 'LONG',
            'action' => 'BUY',
            'wins' => (int)($hourAuditLong['wins'] ?? 0),
            'losses' => (int)($hourAuditLong['losses'] ?? 0),
            'net_pnl' => (float)($hourAuditLong['net_pnl'] ?? 0.0),
        ],
        [
            'key' => 'short',
            'label' => 'SHORT',
            'action' => 'SELL',
            'wins' => (int)($hourAuditShort['wins'] ?? 0),
            'losses' => (int)($hourAuditShort['losses'] ?? 0),
            'net_pnl' => (float)($hourAuditShort['net_pnl'] ?? 0.0),
        ],
    ];
    usort($candidates, static function (array $left, array $right): int {
        $pnlCompare = ($right['net_pnl'] ?? 0) <=> ($left['net_pnl'] ?? 0);
        if ($pnlCompare !== 0) return $pnlCompare;
        $winCompare = ($right['wins'] ?? 0) <=> ($left['wins'] ?? 0);
        if ($winCompare !== 0) return $winCompare;
        return ($left['losses'] ?? 0) <=> ($right['losses'] ?? 0);
    });
    return $candidates[0] ?? [
        'key' => 'strategy',
        'label' => 'STRATEGY',
        'action' => 'FORMULA',
        'wins' => 0,
        'losses' => 0,
        'net_pnl' => 0.0,
    ];
}

function compressHourlyPlanToSingleTrade(
    array $plan,
    array $hourAuditWinner,
    string $formulaAction,
    float $hourAuditPercent,
    float $carryForwardAccuracy,
    float $trustPercent
): array {
    $formulaAction = strtoupper(trim($formulaAction));
    if (($formulaAction !== 'BUY' && $formulaAction !== 'SELL') || !is_array($plan['slots'] ?? null)) {
        return $plan;
    }

    $winnerAction = strtoupper(trim((string)($hourAuditWinner['action'] ?? 'FORMULA')));
    $winnerPnl = (float)($hourAuditWinner['net_pnl'] ?? 0.0);
    $winnerWins = (int)($hourAuditWinner['wins'] ?? 0);
    $winnerLosses = (int)($hourAuditWinner['losses'] ?? 0);
    $auditPercent = max(0.0, min(100.0, $hourAuditPercent));
    $carryPercent = max(0.0, min(100.0, $carryForwardAccuracy));
    $trustPercent = max(0.0, min(100.0, $trustPercent));
    $agreement = $winnerAction === 'FORMULA' || $winnerAction === $formulaAction;

    $formulaSideAmounts = [];
    foreach ($plan['slots'] as $slot) {
        if (strtoupper(trim((string)($slot['action'] ?? 'NO TRADE'))) !== $formulaAction) continue;
        $formulaSideAmounts[] = max(0.0, (float)($slot['amount'] ?? 0.0));
    }
    $singleTradeAmount = array_sum($formulaSideAmounts);
    if ($singleTradeAmount <= 0.0) return $plan;

    $directionConfidence = max($carryPercent, $auditPercent, min(100.0, max(90.0, $trustPercent)));
    $winnerBoost = 1.0;
    if ($agreement && $winnerPnl > 0.0) {
        $winnerBoost += min(0.45, ($directionConfidence / 100.0) * 0.30 + min(0.15, $winnerPnl / 1000.0));
    } elseif (!$agreement) {
        $winnerBoost -= min(0.30, max(0.0, 1.0 - ($directionConfidence / 100.0)) * 0.30);
    }
    $winnerBoost = max(0.65, min(1.50, $winnerBoost));
    $singleTradeAmount *= $winnerBoost;

    $plan['single_trade_mode'] = true;
    $plan['single_trade_action'] = $formulaAction;
    $plan['single_trade_amount'] = round($singleTradeAmount, 8);
    $plan['single_trade_multiplier'] = round($winnerBoost, 4);
    $plan['single_trade_confidence'] = round($directionConfidence, 2);
    $plan['single_trade_winner'] = $hourAuditWinner['label'] ?? 'STRATEGY';
    $plan['single_trade_winner_pnl'] = round($winnerPnl, 4);
    $plan['single_trade_winner_record'] = $winnerWins . '/' . $winnerLosses;
    $plan['single_trade_agreement'] = $agreement;
    $plan['dominant_action'] = $formulaAction;
    $plan['actionable_slots'] = $singleTradeAmount > 0.0 ? 1 : 0;
    $plan['total_requested_amount'] = round($singleTradeAmount, 8);
    $plan['total_buy_requested'] = $formulaAction === 'BUY' ? round($singleTradeAmount, 8) : 0.0;
    $plan['total_sell_requested'] = $formulaAction === 'SELL' ? round($singleTradeAmount, 8) : 0.0;
    $plan['buy_calls'] = $formulaAction === 'BUY' && $singleTradeAmount > 0.0 ? 1 : 0;
    $plan['sell_calls'] = $formulaAction === 'SELL' && $singleTradeAmount > 0.0 ? 1 : 0;
    $plan['slots'] = [[
        'slot_index' => 0,
        'time' => (string)($plan['slots'][0]['time'] ?? gmdate('Y-m-d\TH:i:s\Z')),
        'action' => $formulaAction,
        'weight' => 1.0,
        'normalized_weight' => 1.0,
        'amount' => round($singleTradeAmount, 8),
        'single_trade' => true,
    ]];
    return $plan;
}

/**
 * Plan the next hour of 5-minute calls as one bell-curve bundle.
 * The total requested size is trust% × side multiplier × count of actionable calls,
 * distributed by normalized Gaussian weights across the 12 upcoming slots.
 */
function buildHourlyBellCurvePlan(
    array $forecastByTime,
    string $boundaryTime,
    ?array $currentGuess,
    float $baseTradeAmount,
    float $buyMultiplier,
    float $sellMultiplier,
    float $trustPercent,
    int $slotCount = ONE_HOUR_CANDLE_COUNT
): array {
    $boundaryEpoch = yahooTimestamp($boundaryTime);
    if ($boundaryEpoch === null) {
        $boundaryEpoch = (int)floor(time() / 300) * 300;
    }
    $slotCount = max(1, $slotCount);
    $weights = gaussianBellCurveWeights($slotCount);
    $slots = [];
    $actionableWeight = 0.0;
    $buyWeight = 0.0;
    $sellWeight = 0.0;
    $buyCalls = 0;
    $sellCalls = 0;
    $totalBuyRequested = 0.0;
    $totalSellRequested = 0.0;
    for ($slotIndex = 0; $slotIndex < $slotCount; $slotIndex++) {
        $slotTime = gmdate('Y-m-d\TH:i:s\Z', $boundaryEpoch + ($slotIndex * 300));
        $guess = $slotIndex === 0
            ? $currentGuess
            : (is_array($forecastByTime[$slotTime] ?? null) ? $forecastByTime[$slotTime] : null);
        $action = guessStoredAction($guess);
        if ($action !== 'BUY' && $action !== 'SELL') {
            $slots[] = [
                'slot_index' => $slotIndex,
                'time' => $slotTime,
                'action' => 'NO TRADE',
                'weight' => $weights[$slotIndex],
                'normalized_weight' => 0.0,
                'amount' => 0.0,
            ];
            continue;
        }
        $actionableWeight += $weights[$slotIndex];
        if ($action === 'BUY') {
            $buyWeight += $weights[$slotIndex];
            $buyCalls++;
        } else {
            $sellWeight += $weights[$slotIndex];
            $sellCalls++;
        }
        $slots[] = [
            'slot_index' => $slotIndex,
            'time' => $slotTime,
            'action' => $action,
            'weight' => $weights[$slotIndex],
            'normalized_weight' => 0.0,
            'amount' => 0.0,
        ];
    }

    $trustFraction = max(0.0, min(1.0, $trustPercent / 100.0));
    $buyAllocationFraction = max(0.0, min(1.0, $trustFraction * max(0.0, $buyMultiplier)));
    $sellAllocationFraction = max(0.0, min(1.0, $trustFraction * max(0.0, $sellMultiplier)));

    foreach ($slots as $index => $slot) {
        if (($slot['action'] ?? 'NO TRADE') !== 'BUY' && ($slot['action'] ?? 'NO TRADE') !== 'SELL') {
            continue;
        }
        $normalizedWeight = $actionableWeight > 0.0 ? ((float)$slot['weight'] / $actionableWeight) : 0.0;
        $callCount = (($slot['action'] ?? 'NO TRADE') === 'BUY') ? max(1, $buyCalls) : max(1, $sellCalls);
        $allocationFraction = (($slot['action'] ?? 'NO TRADE') === 'BUY') ? $buyAllocationFraction : $sellAllocationFraction;
        $slots[$index]['normalized_weight'] = $normalizedWeight;
        $slots[$index]['amount'] = max(0.0, $baseTradeAmount) * $allocationFraction * $callCount * $normalizedWeight;
        if (($slot['action'] ?? 'NO TRADE') === 'BUY') $totalBuyRequested += $slots[$index]['amount'];
        if (($slot['action'] ?? 'NO TRADE') === 'SELL') $totalSellRequested += $slots[$index]['amount'];
    }

    $dominantAction = 'NO TRADE';
    if ($buyWeight > $sellWeight) $dominantAction = 'BUY';
    elseif ($sellWeight > $buyWeight) $dominantAction = 'SELL';
    elseif ($buyWeight > 0.0 || $sellWeight > 0.0) $dominantAction = 'MIXED';

    $dominance = ($buyWeight + $sellWeight) > 0.0
        ? (max($buyWeight, $sellWeight) / ($buyWeight + $sellWeight))
        : 0.0;
    $effectiveTrust = min(100.0, max(0.0, $trustPercent * (0.5 + $dominance)));
    $totalRequestedAmount = 0.0;
    foreach ($slots as $slot) {
        $totalRequestedAmount += (float)($slot['amount'] ?? 0.0);
    }

    return [
        'width_slots' => $slotCount,
        'trust_percent' => max(0.0, min(100.0, $trustPercent)),
        'effective_trust_percent' => $effectiveTrust,
        'dominant_action' => $dominantAction,
        'buy_calls' => $buyCalls,
        'sell_calls' => $sellCalls,
        'total_buy_requested' => $totalBuyRequested,
        'total_sell_requested' => $totalSellRequested,
        'actionable_slots' => $buyCalls + $sellCalls,
        'buy_weight' => $buyWeight,
        'sell_weight' => $sellWeight,
        'total_requested_amount' => $totalRequestedAmount,
        'slots' => $slots,
    ];
}

/** Repair legacy wallet states that zeroed cash after partial buys. */
function normalizePaperWalletCash(array $state): array
{
    $bootstrapCash = max(0.0, (float)($state['bootstrap_cash_amount'] ?? 0.0));
    $bootstrapAsset = max(0.0, (float)($state['bootstrap_asset_amount'] ?? 0.0));
    $totalBoughtAmount = max(0.0, (float)($state['total_bought_amount'] ?? 0.0));
    $totalSoldAmount = max(0.0, (float)($state['total_sold_amount'] ?? 0.0));
    $currentCash = max(0.0, (float)($state['cash_left'] ?? 0.0));
    $incrementalBuys = max(0.0, $totalBoughtAmount - $bootstrapAsset);
    $expectedCash = max(0.0, $bootstrapCash + $totalSoldAmount - $incrementalBuys);

    if ($bootstrapCash <= 0.0) return $state;
    if (abs($expectedCash - $currentCash) < 0.01) return $state;
    if ($expectedCash <= $currentCash) return $state;

    // Only repair when the old bug clearly collapsed cash far below what the
    // bootstrap + recorded buys/sells imply it should be.
    $state['cash_left'] = $expectedCash;
    return $state;
}

/** Color gain/loss sign marks for the table output. */
function renderSignedSymbolHtml(string $value): string
{
    if ($value === '') return '';
    $output = '';
    $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($chars)) return htmlspecialchars($value);
    foreach ($chars as $char) {
        if ($char === '+') {
            $output .= '<span class="signal-sign gain-sign">+</span>';
        } elseif ($char === '-') {
            $output .= '<span class="signal-sign loss-sign">-</span>';
        } else {
            $output .= htmlspecialchars($char);
        }
    }
    return $output;
}

/** Convert a resolved predicted/actual move into an intuitive trade outcome. */
function resolvedOutcomeMeta(?string $predictedDirection, ?string $actualDirection): array
{
    $predicted = ($predictedDirection === '+' || $predictedDirection === '-') ? $predictedDirection : null;
    $actual = ($actualDirection === '+' || $actualDirection === '-') ? $actualDirection : null;
    $action = $predicted === '+'
        ? 'BUY'
        : ($predicted === '-' ? 'SELL' : 'NO TRADE');
    if ($action === 'NO TRADE' || $actual === null) {
        return [
            'action' => $action,
            'outcome' => 'NEUTRAL',
            'label' => $action,
            'class' => 'result-neutral-cell',
            'actual' => $actual,
            'gain' => null,
        ];
    }
    $gain = $predicted === $actual;
    return [
        'action' => $action,
        'outcome' => $gain ? 'GAIN' : 'LOSS',
        'label' => $action . ' ' . ($gain ? 'GAIN' : 'LOSS'),
        'class' => $gain ? 'result-gain-cell' : 'result-loss-cell',
        'actual' => $actual,
        'gain' => $gain,
    ];
}

/** Format a signed dollar amount for the table. */
function formatSignedMoney(float $amount, int $precision = 4): string
{
    return ($amount >= 0.0 ? '+' : '-') . '$' . number_format(abs($amount), $precision, '.', ',');
}

/** Mark a partially realized phase with #fraction or #count. */
function formatPhaseRealizationLabel(string $label): string
{
    return preg_replace('/\bx(?=\d)/i', '#', $label) ?? $label;
}

/** Canonical requested/available/executable sizing for one paper trade. */
function canonicalTradeSizing(string $action, float $requestedAmount, float $availableAmount): array
{
    $action = strtoupper(trim($action));
    $requested = max(0.0, $requestedAmount);
    $available = max(0.0, $availableAmount);
    $eligible = ($action === 'BUY' || $action === 'SELL')
        && $requested >= MIN_TRADE_AMOUNT
        && $available >= MIN_TRADE_AMOUNT;
    $executable = $eligible ? min($requested, $available) : 0.0;
    return [
        'action' => $action,
        'requested_amount' => round($requested, 8),
        'available_amount' => round($available, 8),
        'executable_amount' => round($executable, 8),
        'shortfall' => round(max(0.0, $requested - $executable), 8),
        'phase_step_multiplier' => 1,
        'eligible' => $eligible,
    ];
}

/** Convert a candle move into per-one-unit trade P/L for BUY or SELL. */
function tradePnlForAction(string $action, ?array $actualCandle = null): float
{
    if ($action !== 'BUY' && $action !== 'SELL') return 0.0;
    if (is_array($actualCandle)
        && is_numeric($actualCandle['open'] ?? null)
        && is_numeric($actualCandle['close'] ?? null)) {
        $assetMove = (float)$actualCandle['close'] - (float)$actualCandle['open'];
        return $action === 'BUY' ? $assetMove : -$assetMove;
    }
    // Missing actual data is unresolved, not a profitable trade.
    return 0.0;
}

/** Display the model's unsigned target move without calling it realized P/L. */
function targetMoveForAction(string $action, float $change): float
{
    return ($action === 'BUY' || $action === 'SELL') ? abs($change) : 0.0;
}

function displayCommitmentAmountForAction(
    string $action,
    ?array $hourlyPlan,
    float $fixedTradeAmount,
    float $sellMultiplier = 1.0
): float {
    $action = strtoupper(trim($action));
    if ($action !== 'BUY' && $action !== 'SELL') return 0.0;
    if (is_array($hourlyPlan)) {
        $singleTradeMode = (($hourlyPlan['single_trade_mode'] ?? false) === true);
        $planAction = strtoupper(trim((string)($hourlyPlan['single_trade_action'] ?? $hourlyPlan['dominant_action'] ?? 'NO TRADE')));
        if ($singleTradeMode && $planAction === $action && is_numeric($hourlyPlan['single_trade_amount'] ?? null)) {
            return max(0.0, (float)$hourlyPlan['single_trade_amount']);
        }
        if ($action === 'BUY' && is_numeric($hourlyPlan['total_buy_requested'] ?? null)) {
            return max(0.0, (float)$hourlyPlan['total_buy_requested']);
        }
        if ($action === 'SELL' && is_numeric($hourlyPlan['total_sell_requested'] ?? null)) {
            return max(0.0, (float)$hourlyPlan['total_sell_requested']);
        }
    }
    return $action === 'BUY'
        ? max(0.0, $fixedTradeAmount)
        : max(0.0, $fixedTradeAmount * max(0.0, $sellMultiplier));
}

/** Prefer the exact stored move size when it exists; otherwise fall back. */
function guessStoredChange(?array $guess, float $fallback): float
{
    $fallback = max(.00000001, abs($fallback));
    if (!is_array($guess)) return $fallback;
    $storedChange = $guess['change'] ?? null;
    if (!is_numeric($storedChange)) return $fallback;
    $change = abs((float)$storedChange);
    return $change > 0.0 ? $change : $fallback;
}

/**
 * Freeze the official RIGHT/WRONG judgment for each timestamp from the saved
 * pair and frozen actual move. The pair and actual remain locked; the current
 * pair-to-direction rule is applied consistently across the saved horizon.
 */
function freezeResolvedResults(string $statePath, array $guessesByTime, array $actualDirectionsByTime): array
{
    $handle = @fopen($statePath, 'c+');
    if ($handle === false) return [];

    flock($handle, LOCK_EX);
    rewind($handle);
    $raw = stream_get_contents($handle);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    $state = is_array($decoded) ? $decoded : [];
    $state['results'] = is_array($state['results'] ?? null) ? $state['results'] : [];

    foreach ($guessesByTime as $time => $guess) {
        $actual = $actualDirectionsByTime[$time] ?? null;
        if ($actual !== '+' && $actual !== '-') continue;
        $normalized = normalizeCngnGuess(is_array($guess) ? $guess : null);
        if (!is_array($normalized)) continue;
        $pair = guessPairLabel($normalized);
        $storedDirection = (string)($normalized['direction'] ?? '');
        $predicted = ($storedDirection === '+' || $storedDirection === '-')
            ? $storedDirection
            : newGuessDirectionFromPair($pair);
        if ($predicted !== '+' && $predicted !== '-') continue;
        $existing = is_array($state['results'][$time] ?? null) ? $state['results'][$time] : [];

        $state['results'][$time] = [
            'time' => $time,
            'pair' => $pair,
            'predicted' => $predicted,
            'actual' => $actual,
            'right' => $predicted === $actual,
            'resolved_at' => (string)($existing['resolved_at'] ?? gmdate('Y-m-d\TH:i:s\Z')),
        ];
    }

    if (count($state['results']) > 2000) {
        uksort($state['results'], static fn(string $a, string $b): int => strcmp($a, $b));
        $state['results'] = array_slice($state['results'], -2000, 2000, true);
    }

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($state, JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $state['results'];
}

function filterGuessMapSinceTime(array $guessesByTime, ?string $startTime): array
{
    if (!is_string($startTime) || trim($startTime) === '') return $guessesByTime;
    $filtered = [];
    foreach ($guessesByTime as $time => $guess) {
        if (!is_string($time) || strcmp($time, $startTime) < 0) continue;
        $filtered[$time] = $guess;
    }
    return $filtered;
}

function centeredTimelineWindow(array $records, string $focusAction, string $boundaryTime, int $windowSize = 16): array
{
    $count = count($records);
    if ($count <= $windowSize) return $records;
    $focusAction = strtoupper(trim($focusAction));
    $boundaryEpoch = yahooTimestamp($boundaryTime);
    $focusIndex = null;
    $bestDistance = null;

    foreach ($records as $index => $record) {
        if (!is_array($record)) continue;
        $action = strtoupper(trim((string)($record['guessAction'] ?? 'NO TRADE')));
        if ($action !== $focusAction) continue;
        $time = (string)($record['displayTime'] ?? $record['time'] ?? '');
        $epoch = yahooTimestamp($time);
        $distance = ($boundaryEpoch !== null && $epoch !== null)
            ? abs($epoch - $boundaryEpoch)
            : abs($index - (int)floor($count / 2));
        if ($focusIndex === null || $bestDistance === null || $distance < $bestDistance) {
            $focusIndex = $index;
            $bestDistance = $distance;
        }
    }

    if ($focusIndex === null) {
        return $records;
    }

    $half = (int)floor($windowSize / 2);
    $start = max(0, $focusIndex - $half);
    $end = min($count, $start + $windowSize);
    $start = max(0, $end - $windowSize);
    return array_slice($records, $start, $windowSize);
}

/**
 * Run a deterministic paper break trader with a fixed cash pot.
 *
 * Flat: keep a rolling high-water anchor and paper-buy 100% of remaining cash
 * when price falls by the configured break percentage. Long: paper-sell 100%
 * of the current position at either the configured gain or loss percentage.
 * This function records simulations only.
 */
function updatePaperBreakTrader(
    string $statePath,
    float $currentPrice,
    string $observedTime,
    float $buyDropPercent,
    float $takeGainPercent,
    float $stopLossPercent
): array {
    $startingPot = 10000.0;
    $buyAllocationPercent = 100.0;
    $sellAllocationPercent = 50.0;
    $default = [
        'position' => 'flat',
        'anchor_price' => $currentPrice,
        'entry_price' => null,
        'entry_time' => null,
        'display_action' => 'WATCHING',
        'current_move_percent' => 0.0,
        'drop_from_high_percent' => 0.0,
        'realized_move' => 0.0,
        'wins' => 0,
        'losses' => 0,
        'last_trade' => null,
        'trades' => [],
        'starting_pot' => $startingPot,
        'cash_left' => $startingPot,
        'asset_units' => 0.0,
        'asset_cost_basis' => 0.0,
        'holding_value' => 0.0,
        'equity_value' => $startingPot,
        'net_pnl' => 0.0,
        'open_pnl' => 0.0,
        'first_buy_amount' => 0.0,
        'first_buy_units' => 0.0,
        'first_buy_price' => null,
        'total_bought_units' => 0.0,
        'total_bought_amount' => 0.0,
        'total_sold_units' => 0.0,
        'total_sold_amount' => 0.0,
        'right_percent' => 0.0,
        'last_trade_result' => null,
        'last_trade_pnl' => 0.0,
    ];
    if ($currentPrice <= 0.0) return $default;

    $handle = @fopen($statePath, 'c+');
    if ($handle === false) return $default;

    flock($handle, LOCK_EX);
    rewind($handle);
    $raw = stream_get_contents($handle);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    $state = is_array($decoded) ? array_merge($default, $decoded) : $default;
    $state['trades'] = is_array($state['trades'] ?? null) ? $state['trades'] : [];
    $state['cash_left'] = max(0.0, (float)($state['cash_left'] ?? $startingPot));
    $state['asset_units'] = max(0.0, (float)($state['asset_units'] ?? 0.0));
    $state['asset_cost_basis'] = max(0.0, (float)($state['asset_cost_basis'] ?? 0.0));
    $state['first_buy_amount'] = max(0.0, (float)($state['first_buy_amount'] ?? 0.0));
    $state['first_buy_units'] = max(0.0, (float)($state['first_buy_units'] ?? 0.0));
    $state['total_bought_units'] = max(0.0, (float)($state['total_bought_units'] ?? 0.0));
    $state['total_bought_amount'] = max(0.0, (float)($state['total_bought_amount'] ?? 0.0));
    $state['total_sold_units'] = max(0.0, (float)($state['total_sold_units'] ?? 0.0));
    $state['total_sold_amount'] = max(0.0, (float)($state['total_sold_amount'] ?? 0.0));
    $state['starting_pot'] = max(0.0, (float)($state['starting_pot'] ?? $startingPot));
    $state['position'] = ($state['position'] ?? 'flat') === 'long' ? 'long' : 'flat';
    if ($state['asset_units'] <= 0.0) {
        $state['asset_units'] = 0.0;
        $state['asset_cost_basis'] = 0.0;
        $state['position'] = 'flat';
    }
    if ($state['asset_units'] > 0.0) {
        $state['entry_price'] = $state['asset_cost_basis'] > 0.0
            ? $state['asset_cost_basis'] / $state['asset_units']
            : (float)($state['entry_price'] ?? $currentPrice);
        $state['position'] = 'long';
    }
    $hasNeverEntered = (float)$state['first_buy_amount'] <= 0.0
        && (float)$state['total_bought_units'] <= 0.0
        && count($state['trades']) === 0;
    $looksLikeUntouchedCashStart = $state['position'] === 'flat'
        && (float)$state['asset_units'] <= 0.0
        && abs((float)$state['cash_left'] - (float)$state['starting_pot']) < 0.000001;
    if ($currentPrice > 0.0 && $hasNeverEntered && $looksLikeUntouchedCashStart) {
        $seedUnits = $startingPot / $currentPrice;
        if ($seedUnits > 0.0) {
            $state['position'] = 'long';
            $state['anchor_price'] = $currentPrice;
            $state['entry_price'] = $currentPrice;
            $state['entry_time'] = $observedTime;
            $state['cash_left'] = 0.0;
            $state['asset_units'] = $seedUnits;
            $state['asset_cost_basis'] = $startingPot;
            $state['holding_value'] = $startingPot;
            $state['equity_value'] = $startingPot;
            $state['first_buy_amount'] = $startingPot;
            $state['first_buy_units'] = $seedUnits;
            $state['first_buy_price'] = $currentPrice;
            $state['total_bought_units'] = $seedUnits;
            $state['total_bought_amount'] = $startingPot;
        }
    }
    $state['display_action'] = 'WATCHING';
    $state['current_move_percent'] = 0.0;
    $state['drop_from_high_percent'] = 0.0;

    $buyDropPercent = min(25.0, max(0.01, $buyDropPercent));
    $takeGainPercent = min(25.0, max(0.01, $takeGainPercent));
    $stopLossPercent = min(25.0, max(0.01, $stopLossPercent));
    $state['settings'] = [
        'buy_drop_percent' => $buyDropPercent,
        'take_gain_percent' => $takeGainPercent,
        'stop_loss_percent' => $stopLossPercent,
        'buy_allocation_percent' => $buyAllocationPercent,
        'sell_allocation_percent' => $sellAllocationPercent,
    ];

    $lastTrade = is_array($state['last_trade'] ?? null) ? $state['last_trade'] : null;
    $sameBarAction = static function (?array $trade, string $observedTime, string $action): bool {
        if (!is_array($trade)) return false;
        return (string)($trade['time'] ?? '') === $observedTime
            && (string)($trade['action'] ?? '') === $action;
    };

    if ($state['position'] === 'long' && $state['asset_units'] > 0.0 && is_numeric($state['entry_price'] ?? null)) {
        $entryPrice = max(0.00000001, (float)$state['entry_price']);
        $movePercent = $entryPrice != 0.0
            ? (($currentPrice - $entryPrice) / $entryPrice) * 100
            : 0.0;
        $state['current_move_percent'] = $movePercent;
        $state['display_action'] = 'HOLD LONG';

        $exitAction = null;
        if ($movePercent >= $takeGainPercent) {
            $exitAction = 'SELL GAIN';
        } elseif ($movePercent <= -$stopLossPercent) {
            $exitAction = 'SELL LOSS';
        }

        if ($exitAction !== null && !$sameBarAction($lastTrade, $observedTime, $exitAction)) {
            $unitsHeldBefore = max(0.0, (float)$state['asset_units']);
            $costBasisBefore = max(0.0, (float)$state['asset_cost_basis']);
            $sellUnits = $unitsHeldBefore * ($sellAllocationPercent / 100);
            $sellAmount = $sellUnits * $currentPrice;
            $costPortion = $unitsHeldBefore > 0.0
                ? $costBasisBefore * ($sellUnits / $unitsHeldBefore)
                : 0.0;
            $realizedPnl = $sellAmount - $costPortion;
            if (!REALIZE_LOSS_TRADES && $realizedPnl < 0.0) {
                $state['display_action'] = 'HOLD LONG';
            } else {
            $trade = [
                'action' => $exitAction,
                'time' => $observedTime,
                'price' => $currentPrice,
                'entry_price' => $entryPrice,
                'move' => $currentPrice - $entryPrice,
                'percentage' => $movePercent,
                'units' => $sellUnits,
                'amount' => $sellAmount,
                'realized_pnl' => $realizedPnl,
            ];
            $state['trades'][] = $trade;
            $state['trades'] = array_slice($state['trades'], -200);
            $state['last_trade'] = $trade;
            $state['realized_move'] = (float)$state['realized_move'] + $realizedPnl;
            $state['cash_left'] = (float)$state['cash_left'] + $sellAmount;
            $state['asset_units'] = max(0.0, $unitsHeldBefore - $sellUnits);
            $state['asset_cost_basis'] = max(0.0, $costBasisBefore - $costPortion);
            $state['total_sold_units'] = (float)$state['total_sold_units'] + $sellUnits;
            $state['total_sold_amount'] = (float)$state['total_sold_amount'] + $sellAmount;
            if ($exitAction === 'SELL GAIN') {
                $state['wins'] = (int)$state['wins'] + 1;
                $state['last_trade_result'] = 'RIGHT';
            } else {
                $state['losses'] = (int)$state['losses'] + 1;
                $state['last_trade_result'] = 'WRONG';
            }
            $state['last_trade_pnl'] = $realizedPnl;
            if ($state['asset_units'] <= 0.00000001) {
                $state['position'] = 'flat';
                $state['asset_units'] = 0.0;
                $state['asset_cost_basis'] = 0.0;
                $state['anchor_price'] = $currentPrice;
                $state['entry_price'] = null;
                $state['entry_time'] = null;
            } else {
                $state['position'] = 'long';
                $state['entry_price'] = $state['asset_cost_basis'] / $state['asset_units'];
            }
            $state['display_action'] = $exitAction;
            }
        }
    } else {
        $anchorPrice = is_numeric($state['anchor_price'] ?? null)
            ? (float)$state['anchor_price']
            : $currentPrice;
        if ($anchorPrice <= 0.0 || $currentPrice > $anchorPrice) {
            $anchorPrice = $currentPrice;
        }
        $state['anchor_price'] = $anchorPrice;
        $dropPercent = $anchorPrice != 0.0
            ? (($anchorPrice - $currentPrice) / $anchorPrice) * 100
            : 0.0;
        $state['drop_from_high_percent'] = max(0.0, $dropPercent);

        if ($dropPercent >= $buyDropPercent && !$sameBarAction($lastTrade, $observedTime, 'BUY BREAK')) {
            $buyAmount = min(
                (float)$state['cash_left'],
                (float)$state['cash_left'] * ($buyAllocationPercent / 100)
            );
            $buyUnits = $currentPrice > 0.0 ? ($buyAmount / $currentPrice) : 0.0;
            if ($buyAmount > 0.0 && $buyUnits > 0.0) {
            $trade = [
                'action' => 'BUY BREAK',
                'time' => $observedTime,
                'price' => $currentPrice,
                'anchor_price' => $anchorPrice,
                'drop_percentage' => $dropPercent,
                'amount' => $buyAmount,
                'units' => $buyUnits,
            ];
            $state['trades'][] = $trade;
            $state['trades'] = array_slice($state['trades'], -200);
            $state['last_trade'] = $trade;
            $state['position'] = 'long';
            $state['cash_left'] = max(0.0, (float)$state['cash_left'] - $buyAmount);
            $state['asset_units'] = (float)$state['asset_units'] + $buyUnits;
            $state['asset_cost_basis'] = (float)$state['asset_cost_basis'] + $buyAmount;
            $state['entry_price'] = $state['asset_cost_basis'] / max(0.00000001, $state['asset_units']);
            $state['entry_time'] = $observedTime;
            $state['current_move_percent'] = 0.0;
            $state['display_action'] = 'BUY BREAK';
                $state['total_bought_units'] = (float)$state['total_bought_units'] + $buyUnits;
                $state['total_bought_amount'] = (float)$state['total_bought_amount'] + $buyAmount;
                if ((float)$state['first_buy_amount'] <= 0.0) {
                    $state['first_buy_amount'] = $buyAmount;
                    $state['first_buy_units'] = $buyUnits;
                    $state['first_buy_price'] = $currentPrice;
                }
            }
        }
    }

    $state['holding_value'] = (float)$state['asset_units'] * $currentPrice;
    $state['open_pnl'] = (float)$state['holding_value'] - (float)$state['asset_cost_basis'];
    $state['equity_value'] = (float)$state['cash_left'] + (float)$state['holding_value'];
    $state['net_pnl'] = (float)$state['equity_value'] - (float)$state['starting_pot'];
    $decisionCount = (int)$state['wins'] + (int)$state['losses'];
    $state['right_percent'] = $decisionCount > 0
        ? ((float)$state['wins'] / $decisionCount) * 100
        : 0.0;
    $state['current_price'] = $currentPrice;
    $state['observed_time'] = $observedTime;
    $state['paper_only'] = true;
    $state['live_orders'] = false;

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($state, JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $state;
}

/** Simulate the POT directly from saved BUY/SELL model actions across real candles. */
function buildModelPaperTraderState(
    array $candles,
    array $guessesByTime,
    ?array $currentGuess,
    float $currentPrice,
    string $observedTime,
    ?array $bootstrap = null,
    float $initialTradeAmount = 0.0,
    float $sellMultiplier = 1.0,
    float $trustPercent = 75.0,
    array $resolvedResultsByTime = [],
    ?array $sneakProfile = null
): array {
    $startingPot = 10000.0;
    $cashSeed = is_array($bootstrap) && is_numeric($bootstrap['cash_seed'] ?? null)
        ? max(0.0, (float)$bootstrap['cash_seed'])
        : ($startingPot / 2.0);
    $assetSeed = is_array($bootstrap) && is_numeric($bootstrap['asset_seed'] ?? null)
        ? max(0.0, (float)$bootstrap['asset_seed'])
        : ($startingPot - $cashSeed);
    if ($cashSeed + $assetSeed <= 0.0) {
        $cashSeed = $startingPot / 2.0;
        $assetSeed = $startingPot - $cashSeed;
    }
    $state = [
        'position' => 'flat',
        'anchor_price' => $currentPrice,
        'entry_price' => null,
        'entry_time' => null,
        'display_action' => 'WATCHING',
        'current_move_percent' => 0.0,
        'drop_from_high_percent' => 0.0,
        'realized_move' => 0.0,
        'wins' => 0,
        'losses' => 0,
        'last_trade' => null,
        'trades' => [],
        'starting_pot' => $startingPot,
        'cash_left' => $cashSeed,
        'asset_units' => 0.0,
        'asset_cost_basis' => 0.0,
        'holding_value' => 0.0,
        'equity_value' => $startingPot,
        'net_pnl' => 0.0,
        'open_pnl' => 0.0,
        'first_buy_amount' => 0.0,
        'first_buy_units' => 0.0,
        'first_buy_price' => null,
        'total_bought_units' => 0.0,
        'total_bought_amount' => 0.0,
        'total_sold_units' => 0.0,
        'total_sold_amount' => 0.0,
        'right_percent' => 0.0,
        'last_trade_result' => null,
        'last_trade_pnl' => 0.0,
        'events_by_time' => [],
        'active_trade_start_wallet' => null,
        'bootstrap_started_at' => null,
        'bootstrap_entry_price' => null,
        'bootstrap_cash_amount' => $cashSeed,
        'bootstrap_asset_amount' => $assetSeed,
        'fixed_trade_amount' => max(0.0, $initialTradeAmount),
        'settings' => [
            'buy_drop_percent' => 100.0,
            'take_gain_percent' => 100.0,
            'stop_loss_percent' => 100.0,
            'buy_allocation_percent' => 100.0,
            'sell_allocation_percent' => 100.0,
            'fixed_trade_amount' => max(0.0, $initialTradeAmount),
        ],
        'paper_only' => true,
        'live_orders' => false,
    ];
    if (!$candles || $currentPrice <= 0.0) {
        $state['current_price'] = $currentPrice;
        $state['observed_time'] = $observedTime;
        return $state;
    }

    $bootstrapTime = is_array($bootstrap) ? trim((string)($bootstrap['started_at'] ?? '')) : '';
    $bootstrapPrice = is_array($bootstrap) && is_numeric($bootstrap['entry_price'] ?? null)
        ? (float)$bootstrap['entry_price']
        : 0.0;
    $seedTime = $bootstrapTime;
    $seedPrice = $bootstrapPrice;
    if ($seedTime === '' || $seedPrice <= 0.0) {
        foreach ($candles as $seedCandle) {
            if (!is_array($seedCandle)) continue;
            $seedTime = (string)($seedCandle['time'] ?? '');
            $seedPrice = (float)($seedCandle['open'] ?? 0.0);
            if ($seedTime === '' || $seedPrice <= 0.0) continue;
            break;
        }
    }
    if ($seedTime !== '' && $seedPrice > 0.0) {
        $seedUnits = $assetSeed / $seedPrice;
        if ($seedUnits > 0.0) {
            $state['position'] = 'long';
            $state['cash_left'] = $cashSeed;
            $state['asset_units'] = $seedUnits;
            $state['asset_cost_basis'] = $assetSeed;
            $state['entry_price'] = $seedPrice;
            $state['entry_time'] = $seedTime;
            $state['first_buy_amount'] = $assetSeed;
            $state['first_buy_units'] = $seedUnits;
            $state['first_buy_price'] = $seedPrice;
            $state['total_bought_units'] = $seedUnits;
            $state['total_bought_amount'] = $assetSeed;
            $state['active_trade_start_wallet'] = $startingPot;
            $state['bootstrap_started_at'] = $seedTime;
            $state['bootstrap_entry_price'] = $seedPrice;
        }
    }

    $currentGuessTime = null;
    if (is_array($currentGuess)) {
        $currentGuessTime = trim((string)($currentGuess['time'] ?? ''));
    }
    $lastAction = 'WATCHING';
    $processedGuesses = [];

    foreach ($candles as $candle) {
        if (!is_array($candle)) continue;
        $time = (string)($candle['time'] ?? '');
        $tradePrice = (float)($candle['open'] ?? 0.0);
        if ($time === '' || $tradePrice <= 0.0) continue;
        if ($bootstrapTime !== '' && strcmp($time, $bootstrapTime) <= 0) continue;

        $guess = is_array($guessesByTime[$time] ?? null) ? $guessesByTime[$time] : null;
        if (!is_array($guess) && is_array($currentGuess) && (($candle['forming'] ?? false) || ($currentGuessTime !== '' && $currentGuessTime === $time))) {
            $guess = $currentGuess;
        }
        if (!is_array($guess)) continue;

        $action = guessStoredAction($guess);
        if ($action !== 'BUY' && $action !== 'SELL') continue;
        $processedGuesses[$time] = $guess;
        $sequence = sequenceTradeSignal($processedGuesses, $resolvedResultsByTime, null, $trustPercent);
        if (($sequence['enabled'] ?? false) === true) {
            $action = (string)$sequence['action'];
        }
        $previousSignalAction = $lastAction;
        $sneakEligible = is_array($sneakProfile)
            && (($sneakProfile['eligible'] ?? false) === true)
            && ($previousSignalAction === 'BUY' || $previousSignalAction === 'SELL')
            && $action !== $previousSignalAction;
        $sneakFactor = $sneakEligible && is_numeric($sneakProfile['factor'] ?? null)
            ? max(0.10, min(1.00, (float)$sneakProfile['factor']))
            : 1.0;
        $lastAction = $action;

        if ($action === 'BUY') {
            $alreadyLong = ($state['position'] ?? 'flat') === 'long' && (float)($state['asset_units'] ?? 0.0) > 0.00000001;
            if ($alreadyLong) {
                $state['display_action'] = 'HOLD LONG';
                $state['events_by_time'][$time] = [
                    'action' => 'BUY',
                    'label' => 'BUY HOLDING',
                    'class' => 'result-neutral-cell',
                    'executed' => false,
                    'amount' => 0.0,
                    'realized_pnl' => null,
                    'entry_price' => (float)($state['entry_price'] ?? 0.0),
                    'exit_price' => null,
                ];
            } elseif ($state['cash_left'] > 0.0) {
                $entryWallet = (float)$state['cash_left'] + ((float)$state['asset_units'] * $tradePrice);
                $fixedTradeAmount = max(0.0, (float)($state['fixed_trade_amount'] ?? $initialTradeAmount));
                $buyAmount = $fixedTradeAmount > 0.0
                    ? min((float)$state['cash_left'], $fixedTradeAmount)
                    : (float)$state['cash_left'];
                $buySequenceMultiplier = ($sequence['enabled'] ?? false) === true
                    ? max(1, (int)($sequence['trade_count'] ?? 1))
                    : 1;
                $buyAmount *= $buySequenceMultiplier;
                if ($sneakEligible) {
                    $buyAmount *= $sneakFactor;
                }
                $buySizing = canonicalTradeSizing('BUY', max(MIN_TRADE_AMOUNT, $buyAmount), (float)$state['cash_left']);
                $buyAmount = $buySizing['executable_amount'];
                $buyUnits = $buyAmount / $tradePrice;
                if ($buyUnits > 0.0) {
                    $trade = [
                        'action' => ($sneakEligible ? 'SNEAK BUY' : 'BUY') . (($sequence['enabled'] ?? false) ? ' SEQUENCE' : ''),
                        'time' => $time,
                        'price' => $tradePrice,
                        'amount' => $buyAmount,
                        'units' => $buyUnits,
                        'requested_amount' => $buySizing['requested_amount'],
                        'available_amount' => $buySizing['available_amount'],
                        'shortfall' => $buySizing['shortfall'],
                        'sequence_trade_count' => (int)($sequence['trade_count'] ?? 1),
                        'sequence_accuracy' => (float)($sequence['accuracy'] ?? 0.0),
                    ];
                    $state['trades'][] = $trade;
                    $state['trades'] = array_slice($state['trades'], -200);
                    $state['last_trade'] = $trade;
                    $state['position'] = 'long';
                    $state['cash_left'] = max(0.0, (float)$state['cash_left'] - $buyAmount);
                    $state['asset_units'] += $buyUnits;
                    $state['asset_cost_basis'] += $buyAmount;
                    if (!is_numeric($state['active_trade_start_wallet'] ?? null)) {
                        $state['active_trade_start_wallet'] = $entryWallet;
                    }
                    $state['entry_price'] = $state['asset_units'] > 0.0
                        ? ($state['asset_cost_basis'] / $state['asset_units'])
                        : $tradePrice;
                    $state['entry_time'] = $time;
                    $state['total_bought_units'] += $buyUnits;
                    $state['total_bought_amount'] += $buyAmount;
                    if ($state['first_buy_amount'] <= 0.0) {
                        $state['first_buy_amount'] = $buyAmount;
                        $state['first_buy_units'] = $buyUnits;
                        $state['first_buy_price'] = $tradePrice;
                    }
                    $state['events_by_time'][$time] = [
                        'action' => 'BUY',
                        'label' => $sneakEligible
                            ? 'SNEAK BUY x' . number_format($sneakFactor, 2)
                            : (($sequence['enabled'] ?? false)
                                ? 'BUY SEQUENCE x' . (int)$sequence['trade_count'] . ' (' . number_format((float)$sequence['accuracy'], 1) . '%)'
                                : 'BUY ENTERED'),
                        'class' => 'result-neutral-cell',
                        'executed' => true,
                        'amount' => $buyAmount,
                        'realized_pnl' => null,
                        'requested_amount' => $buySizing['requested_amount'],
                        'available_amount' => $buySizing['available_amount'],
                        'shortfall' => $buySizing['shortfall'],
                        'entry_price' => $tradePrice,
                        'exit_price' => null,
                    ];
                }
            } else {
                $state['display_action'] = 'HOLD LONG';
                $state['events_by_time'][$time] = [
                    'action' => 'BUY',
                    'label' => 'BUY HOLDING',
                    'class' => 'result-neutral-cell',
                    'executed' => false,
                    'amount' => 0.0,
                    'realized_pnl' => null,
                    'entry_price' => (float)($state['entry_price'] ?? 0.0),
                    'exit_price' => null,
                ];
            }
            continue;
        }

        if ($state['position'] === 'long' && $state['asset_units'] > 0.0) {
            $unitsHeldBefore = (float)$state['asset_units'];
            $costBasisBefore = (float)$state['asset_cost_basis'];
            $fixedTradeAmount = max(0.0, (float)($state['fixed_trade_amount'] ?? $initialTradeAmount));
            $sellBaseAmount = $fixedTradeAmount * $sellMultiplier;
            if (($sequence['enabled'] ?? false) === true) {
                $aheadSigns = max(1, (int)($sequence['trade_count'] ?? 1));
                $sellBaseAmount *= $aheadSigns;
            }
            if ($sneakEligible) {
                $sellBaseAmount *= $sneakFactor;
            }
            $sellAvailableAmount = $unitsHeldBefore * $tradePrice;
            $sellSizing = canonicalTradeSizing('SELL', max(MIN_TRADE_AMOUNT, $sellBaseAmount), $sellAvailableAmount);
            $sellUnits = $sellSizing['executable_amount'] >= $sellAvailableAmount
                ? $unitsHeldBefore
                : $sellSizing['executable_amount'] / max(0.00000001, $tradePrice);
            $sellAmount = $sellUnits * $tradePrice;
            $costPortion = $unitsHeldBefore > 0.0
                ? $costBasisBefore * ($sellUnits / $unitsHeldBefore)
                : 0.0;
            $realizedPnl = $sellAmount - $costPortion;
            if (!REALIZE_LOSS_TRADES && $realizedPnl < 0.0) {
                $trade = [
                    'action' => 'SELL LOSS' . (($sequence['enabled'] ?? false) ? ' SEQUENCE' : ''),
                    'time' => $time,
                    'price' => $tradePrice,
                    'amount' => $sellAmount,
                    'units' => $sellUnits,
                    'requested_amount' => $sellSizing['requested_amount'],
                    'available_amount' => $sellSizing['available_amount'],
                    'shortfall' => $sellSizing['shortfall'],
                    'realized_pnl' => $realizedPnl,
                ];
                $state['trades'][] = $trade;
                $state['trades'] = array_slice($state['trades'], -200);
                $state['last_trade'] = $trade;
                $state['realized_move'] += $realizedPnl;
                $state['cash_left'] += $sellAmount;
                $state['asset_units'] = max(0.0, $unitsHeldBefore - $sellUnits);
                $state['asset_cost_basis'] = max(0.0, $costBasisBefore - $costPortion);
                $state['total_sold_units'] += $sellUnits;
                $state['total_sold_amount'] += $sellAmount;
                if ($state['asset_units'] <= 0.00000001) {
                    $state['position'] = 'flat';
                    $state['asset_units'] = 0.0;
                    $state['asset_cost_basis'] = 0.0;
                    $state['entry_price'] = null;
                    $state['entry_time'] = null;
                    $state['active_trade_start_wallet'] = null;
                } else {
                    $state['position'] = 'long';
                    $state['entry_price'] = $state['asset_cost_basis'] / $state['asset_units'];
                }
                $state['last_trade_result'] = 'WRONG';
                $state['last_trade_pnl'] = $realizedPnl;
                $state['losses']++;
                $state['events_by_time'][$time] = [
                    'action' => 'SELL',
                    'label' => $sneakEligible
                        ? 'SNEAK SELL x' . number_format($sneakFactor, 2)
                        : (($sequence['enabled'] ?? false)
                            ? 'SELL LOSS SEQUENCE x' . (int)$sequence['trade_count'] . ' (' . number_format((float)$sequence['accuracy'], 1) . '%)'
                            : 'SELL LOSS'),
                    'class' => 'result-loss-cell',
                    'executed' => true,
                    'amount' => $sellAmount,
                    'realized_pnl' => $realizedPnl,
                    'requested_amount' => $sellSizing['requested_amount'],
                    'available_amount' => $sellSizing['available_amount'],
                    'shortfall' => $sellSizing['shortfall'],
                    'entry_price' => $sellUnits > 0.0 ? ($costPortion / $sellUnits) : null,
                    'exit_price' => $tradePrice,
                ];
            } else {
                $trade = [
                    'action' => ($realizedPnl >= 0.0 ? 'SELL GAIN' : 'SELL LOSS')
                        . (($sequence['enabled'] ?? false) ? ' SEQUENCE' : ''),
                    'time' => $time,
                    'price' => $tradePrice,
                    'amount' => $sellAmount,
                    'units' => $sellUnits,
                    'requested_amount' => $sellSizing['requested_amount'],
                    'available_amount' => $sellSizing['available_amount'],
                    'shortfall' => $sellSizing['shortfall'],
                    'realized_pnl' => $realizedPnl,
                ];
                $state['trades'][] = $trade;
                $state['trades'] = array_slice($state['trades'], -200);
                $state['last_trade'] = $trade;
                $state['realized_move'] += $realizedPnl;
                $state['cash_left'] += $sellAmount;
                $state['asset_units'] = max(0.0, $unitsHeldBefore - $sellUnits);
                $state['asset_cost_basis'] = max(0.0, $costBasisBefore - $costPortion);
                $state['total_sold_units'] += $sellUnits;
                $state['total_sold_amount'] += $sellAmount;
                if ($state['asset_units'] <= 0.00000001) {
                    $state['position'] = 'flat';
                    $state['asset_units'] = 0.0;
                    $state['asset_cost_basis'] = 0.0;
                    $state['entry_price'] = null;
                    $state['entry_time'] = null;
                    $state['active_trade_start_wallet'] = null;
                } else {
                    $state['position'] = 'long';
                    $state['entry_price'] = $state['asset_cost_basis'] / $state['asset_units'];
                }
                $state['last_trade_result'] = $realizedPnl >= 0.0 ? 'RIGHT' : 'WRONG';
                $state['last_trade_pnl'] = $realizedPnl;
                if ($realizedPnl >= 0.0) $state['wins']++;
                else $state['losses']++;
                $state['events_by_time'][$time] = [
                    'action' => 'SELL',
                    'label' => $sneakEligible
                        ? 'SNEAK SELL x' . number_format($sneakFactor, 2)
                        : (($sequence['enabled'] ?? false)
                            ? 'SELL SEQUENCE x' . (int)$sequence['trade_count'] . ' (' . number_format((float)$sequence['accuracy'], 1) . '%)'
                            : ($realizedPnl >= 0.0 ? 'SELL GAIN' : 'SELL LOSS')),
                    'class' => $realizedPnl >= 0.0 ? 'result-gain-cell' : 'result-loss-cell',
                    'executed' => true,
                    'amount' => $sellAmount,
                    'realized_pnl' => $realizedPnl,
                    'requested_amount' => $sellSizing['requested_amount'],
                    'available_amount' => $sellSizing['available_amount'],
                    'shortfall' => $sellSizing['shortfall'],
                    'entry_price' => $sellUnits > 0.0 ? ($costPortion / $sellUnits) : null,
                    'exit_price' => $tradePrice,
                ];
            }
        } else {
            $carryPnl = is_numeric($state['last_trade_pnl'] ?? null)
                ? (float)$state['last_trade_pnl']
                : ((float)$state['cash_left'] - (float)$state['starting_pot']);
            $state['events_by_time'][$time] = [
                'action' => 'SELL',
                'label' => $carryPnl >= 0.0 ? 'SELL GAIN' : 'SELL LOSS',
                'class' => $carryPnl >= 0.0 ? 'result-gain-cell' : 'result-loss-cell',
                'executed' => false,
                'amount' => 0.0,
                'realized_pnl' => $carryPnl,
                'entry_price' => null,
                'exit_price' => $tradePrice,
            ];
        }
    }

    if ($state['position'] === 'long' && $state['asset_units'] > 0.0) {
        $entryPrice = max(0.00000001, (float)($state['entry_price'] ?? $currentPrice));
        $state['current_move_percent'] = (($currentPrice - $entryPrice) / $entryPrice) * 100;
    }

    $currentAction = is_array($currentGuess) ? guessStoredAction($currentGuess) : 'NO TRADE';
    if ($currentAction === 'BUY' || $currentAction === 'SELL') {
        $state['display_action'] = $currentAction;
    } elseif ($state['position'] === 'long') {
        $state['display_action'] = 'HOLD LONG';
    } else {
        $state['display_action'] = $lastAction === 'WATCHING' ? 'WATCHING' : $lastAction;
    }

    $state['holding_value'] = (float)$state['asset_units'] * $currentPrice;
    $state['open_pnl'] = (float)$state['holding_value'] - (float)$state['asset_cost_basis'];
    $state['equity_value'] = (float)$state['cash_left'] + (float)$state['holding_value'];
    $state['net_pnl'] = (float)$state['equity_value'] - (float)$state['starting_pot'];
    $decisionCount = (int)$state['wins'] + (int)$state['losses'];
    $state['right_percent'] = $decisionCount > 0
        ? ((float)$state['wins'] / $decisionCount) * 100
        : 0.0;
    $state['current_price'] = $currentPrice;
    $state['observed_time'] = $observedTime;
    return $state;
}

/** Persist one live BUY/SELL decision per five-minute boundary for the wallet card. */
function updateBoundaryModelTraderState(
    string $statePath,
    string $boundaryTime,
    float $currentPrice,
    ?array $currentGuess,
    ?array $bootstrap = null,
    ?array $sequenceSignal = null,
    ?array $hourlyBellCurvePlan = null,
    float $initialTradeAmount = 0.0,
    float $sellMultiplier = 1.0,
    ?array $sneakProfile = null,
    bool $immediateRegimeEntry = false
): array {
    $startingPot = 10000.0;
    $cashSeed = is_array($bootstrap) && is_numeric($bootstrap['cash_seed'] ?? null)
        ? max(0.0, (float)$bootstrap['cash_seed'])
        : ($startingPot / 2.0);
    $assetSeed = is_array($bootstrap) && is_numeric($bootstrap['asset_seed'] ?? null)
        ? max(0.0, (float)$bootstrap['asset_seed'])
        : ($startingPot - $cashSeed);
    if ($cashSeed + $assetSeed <= 0.0) {
        $cashSeed = $startingPot / 2.0;
        $assetSeed = $startingPot - $cashSeed;
    }
    $default = [
        'position' => 'flat',
        'entry_price' => null,
        'entry_time' => null,
        'display_action' => 'WATCHING',
        'current_move_percent' => 0.0,
        'realized_move' => 0.0,
        'wins' => 0,
        'losses' => 0,
        'last_trade' => null,
        'trades' => [],
        'starting_pot' => $startingPot,
        'cash_left' => $cashSeed,
        'asset_units' => 0.0,
        'asset_cost_basis' => 0.0,
        'holding_value' => 0.0,
        'equity_value' => $startingPot,
        'net_pnl' => 0.0,
        'open_pnl' => 0.0,
        'first_buy_amount' => 0.0,
        'first_buy_units' => 0.0,
        'first_buy_price' => null,
        'total_bought_units' => 0.0,
        'total_bought_amount' => 0.0,
        'total_sold_units' => 0.0,
        'total_sold_amount' => 0.0,
        'right_percent' => 0.0,
        'last_trade_result' => null,
        'last_trade_pnl' => 0.0,
        'active_trade_start_wallet' => null,
        'bootstrap_started_at' => null,
        'bootstrap_entry_price' => null,
        'bootstrap_cash_amount' => $cashSeed,
        'bootstrap_asset_amount' => $assetSeed,
        'fixed_trade_amount' => max(0.0, $initialTradeAmount),
        'last_processed_boundary' => null,
        'last_regime_trade_hour' => null,
        'last_signal_action' => 'NO TRADE',
        'signal_history' => [],
        'rebuy_pending' => false,
        'sequence_active' => false,
        'sequence_trade_count' => 0,
        'sequence_accuracy' => 0.0,
        'hourly_bell_curve_active' => false,
        'hourly_bell_curve_effective_trust' => 0.0,
        'hourly_bell_curve_action' => 'NO TRADE',
        'hourly_bell_curve_slots' => 0,
        'hourly_bell_curve_buy_calls' => 0,
        'hourly_bell_curve_sell_calls' => 0,
        'hourly_bell_curve_total_requested' => 0.0,
        'sim_net_move' => 0.0,
        'paper_only' => true,
        'live_orders' => false,
    ];

    $handle = @fopen($statePath, 'c+');
    if ($handle === false) {
        $fallback = $default;
        $fallback['current_price'] = $currentPrice;
        $fallback['observed_time'] = $boundaryTime;
        return $fallback;
    }

    flock($handle, LOCK_EX);
    rewind($handle);
    $raw = stream_get_contents($handle);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    $state = is_array($decoded) ? array_merge($default, $decoded) : $default;

    $state['trades'] = is_array($state['trades'] ?? null) ? $state['trades'] : [];
    $state['signal_history'] = is_array($state['signal_history'] ?? null) ? array_values($state['signal_history']) : [];
    $state['signal_history'] = array_values(array_filter(
        array_slice($state['signal_history'], -8),
        static fn($value): bool => $value === 'BUY' || $value === 'SELL'
    ));
    $state['rebuy_pending'] = (bool)($state['rebuy_pending'] ?? false);
    $state['starting_pot'] = max(0.0, (float)($state['starting_pot'] ?? $startingPot));
    $state['cash_left'] = max(0.0, (float)($state['cash_left'] ?? $state['starting_pot']));
    $state['asset_units'] = max(0.0, (float)($state['asset_units'] ?? 0.0));
    $state['asset_cost_basis'] = max(0.0, (float)($state['asset_cost_basis'] ?? 0.0));
    $state['first_buy_amount'] = max(0.0, (float)($state['first_buy_amount'] ?? 0.0));
    $state['first_buy_units'] = max(0.0, (float)($state['first_buy_units'] ?? 0.0));
    $state['total_bought_units'] = max(0.0, (float)($state['total_bought_units'] ?? 0.0));
    $state['total_bought_amount'] = max(0.0, (float)($state['total_bought_amount'] ?? 0.0));
    $state['total_sold_units'] = max(0.0, (float)($state['total_sold_units'] ?? 0.0));
    $state['total_sold_amount'] = max(0.0, (float)($state['total_sold_amount'] ?? 0.0));
    $state['fixed_trade_amount'] = max(0.0, (float)($state['fixed_trade_amount'] ?? $initialTradeAmount));
    // Refresh persisted sizing so changed BUY multiplier settings are used;
    // SELL applies its own multiplier at execution time below.
    if ($initialTradeAmount > 0.0) {
        $state['fixed_trade_amount'] = max(0.0, $initialTradeAmount);
    }
    $state['position'] = ($state['position'] ?? 'flat') === 'long' ? 'long' : 'flat';
    $state = normalizePaperWalletCash($state);

    if ($currentPrice > 0.0) {
        $bootstrapTime = trim((string)($state['bootstrap_started_at'] ?? ''));
        $bootstrapPrice = is_numeric($state['bootstrap_entry_price'] ?? null)
            ? (float)$state['bootstrap_entry_price']
            : 0.0;
        $isFreshState = $bootstrapTime === ''
            && (float)$state['total_bought_amount'] <= 0.0
            && count($state['trades']) === 0;
        if ($isFreshState) {
            $seedTime = trim((string)(is_array($bootstrap) ? ($bootstrap['started_at'] ?? '') : ''));
            $seedPrice = is_numeric(is_array($bootstrap) ? ($bootstrap['entry_price'] ?? null) : null)
                ? (float)$bootstrap['entry_price']
                : 0.0;
            if ($seedTime === '') $seedTime = $boundaryTime;
            if ($seedPrice <= 0.0) $seedPrice = $currentPrice;
            $seedUnits = $seedPrice > 0.0 ? ($assetSeed / $seedPrice) : 0.0;
            if ($seedTime !== '' && $seedUnits > 0.0) {
                $state['position'] = 'long';
                $state['cash_left'] = $cashSeed;
                $state['asset_units'] = $seedUnits;
                $state['asset_cost_basis'] = $assetSeed;
                $state['entry_price'] = $seedPrice;
                $state['entry_time'] = $seedTime;
                $state['display_action'] = 'SEEDED 50/50';
                $state['first_buy_amount'] = $assetSeed;
                $state['first_buy_units'] = $seedUnits;
                $state['first_buy_price'] = $seedPrice;
                $state['total_bought_units'] = $seedUnits;
                $state['total_bought_amount'] = $assetSeed;
                $state['active_trade_start_wallet'] = $state['starting_pot'];
                $state['bootstrap_started_at'] = $seedTime;
                $state['bootstrap_entry_price'] = $seedPrice;
                $state['bootstrap_cash_amount'] = $cashSeed;
                $state['bootstrap_asset_amount'] = $assetSeed;
                // A new symbol starts invested, but the first page hit must not
                // also spend that same boundary selling the fresh position.
                $state['last_processed_boundary'] = $boundaryTime !== '' ? $boundaryTime : $seedTime;
                $state['last_signal_action'] = 'NO TRADE';
            }
        }

        $boundaryChanged = $boundaryTime !== ''
            && trim((string)($state['last_processed_boundary'] ?? '')) !== $boundaryTime;
        $processRegimeEntryNow = $immediateRegimeEntry && $boundaryTime !== '';
        if ($boundaryChanged || $processRegimeEntryNow) {
            $action = guessStoredAction($currentGuess);
            $previousSignalAction = strtoupper(trim((string)($state['last_signal_action'] ?? 'NO TRADE')));
            $forcedRebuy = $state['rebuy_pending'] === true
                && (float)$state['asset_units'] <= 0.00000001
                && (float)$state['cash_left'] >= 5000.0;
            $rebuyAmount = $forcedRebuy ? min(5000.0, (float)$state['cash_left']) : 0.0;
            $sequenceEnabled = is_array($sequenceSignal) && (($sequenceSignal['enabled'] ?? false) === true);
            if ($sequenceEnabled) $action = (string)($sequenceSignal['action'] ?? $action);
            $bellCurveActive = is_array($hourlyBellCurvePlan)
                && is_array($hourlyBellCurvePlan['slots'] ?? null)
                && (int)($hourlyBellCurvePlan['actionable_slots'] ?? 0) > 0;
            $bellCurveAction = $bellCurveActive ? strtoupper(trim((string)($hourlyBellCurvePlan['dominant_action'] ?? 'NO TRADE'))) : 'NO TRADE';
            $bellCurveBuyAmount = $bellCurveActive ? max(0.0, (float)($hourlyBellCurvePlan['total_buy_requested'] ?? 0.0)) : 0.0;
            $bellCurveSellAmount = $bellCurveActive ? max(0.0, (float)($hourlyBellCurvePlan['total_sell_requested'] ?? 0.0)) : 0.0;
            if ($bellCurveActive && ($bellCurveAction === 'BUY' || $bellCurveAction === 'SELL')) {
                $action = $bellCurveAction;
            }
            if ($forcedRebuy) $action = 'BUY';
            $phaseAction = ($action === 'BUY' || $action === 'SELL') ? $action : 'NO TRADE';
            $regimeActive = is_array($sequenceSignal) && (($sequenceSignal['regime_active'] ?? false) === true);
            $regimeHourKey = $boundaryTime !== '' ? substr($boundaryTime, 0, 13) : '';
            $newRegimeHour = $regimeActive
                && $regimeHourKey !== ''
                && (string)($state['last_regime_trade_hour'] ?? '') !== $regimeHourKey;
            $phaseEntry = ($phaseAction !== 'NO TRADE' && $phaseAction !== $previousSignalAction) || $newRegimeHour;
            if ($forcedRebuy) $phaseEntry = true;
            if ($phaseAction !== 'NO TRADE' && $phaseAction !== $previousSignalAction) {
                $state['signal_history'][] = $phaseAction;
                $state['signal_history'] = array_slice($state['signal_history'], -8);
            }
            $recentSignals = array_slice($state['signal_history'], -4);
            $unstableAlternation = count($recentSignals) === 4
                && $recentSignals[0] !== $recentSignals[1]
                && $recentSignals[1] !== $recentSignals[2]
                && $recentSignals[2] !== $recentSignals[3]
                && $recentSignals[0] === $recentSignals[2]
                && $recentSignals[1] === $recentSignals[3];
            // A run is one phase: execute only on its first BUY/SELL call.
            // Later identical calls are informational HOLDs, not duplicate trades.
            if (!$forcedRebuy && ($unstableAlternation || (!$phaseEntry && ($action === 'BUY' || $action === 'SELL')))) {
                $action = 'NO TRADE';
            }
            if ($regimeActive && $newRegimeHour && ($phaseAction === 'BUY' || $phaseAction === 'SELL')) {
                $state['last_regime_trade_hour'] = $regimeHourKey;
            }
            $sneakEligible = is_array($sneakProfile)
                && (($sneakProfile['eligible'] ?? false) === true)
                && ($previousSignalAction === 'BUY' || $previousSignalAction === 'SELL')
                && ($phaseAction === 'BUY' || $phaseAction === 'SELL')
                && $phaseEntry;
            if ($forcedRebuy) $sneakEligible = false;
            $sneakFactor = $sneakEligible && is_numeric($sneakProfile['factor'] ?? null)
                ? max(0.10, min(1.00, (float)$sneakProfile['factor']))
                : 1.0;
            $saleBlockedByEdge = false;
            if ($action === 'SELL' && $state['position'] === 'long' && (float)$state['asset_units'] > 0.0) {
                $averageCost = (float)$state['asset_cost_basis'] / max(0.00000001, (float)$state['asset_units']);
                $minimumSellPrice = $averageCost * (1.0 + (MIN_SELL_EDGE_PERCENT / 100.0));
                if ($currentPrice < $minimumSellPrice) {
                    $saleBlockedByEdge = true;
                    $action = 'NO TRADE';
                }
            }
            $state['last_signal_action'] = $phaseAction;
            $state['sequence_active'] = $sequenceEnabled;
            $state['sequence_trade_count'] = $sequenceEnabled ? (int)($sequenceSignal['trade_count'] ?? 1) : 0;
            $state['sequence_accuracy'] = $sequenceEnabled ? (float)($sequenceSignal['accuracy'] ?? 0.0) : 0.0;
            $state['hourly_bell_curve_active'] = $bellCurveActive;
            $state['hourly_bell_curve_effective_trust'] = $bellCurveActive ? (float)($hourlyBellCurvePlan['effective_trust_percent'] ?? 0.0) : 0.0;
            $state['hourly_bell_curve_action'] = $bellCurveAction;
            $state['hourly_bell_curve_slots'] = $bellCurveActive ? (int)($hourlyBellCurvePlan['actionable_slots'] ?? 0) : 0;
            $state['hourly_bell_curve_buy_calls'] = $bellCurveActive ? (int)($hourlyBellCurvePlan['buy_calls'] ?? 0) : 0;
            $state['hourly_bell_curve_sell_calls'] = $bellCurveActive ? (int)($hourlyBellCurvePlan['sell_calls'] ?? 0) : 0;
            $state['hourly_bell_curve_total_requested'] = $bellCurveActive ? (float)($hourlyBellCurvePlan['total_requested_amount'] ?? 0.0) : 0.0;
            $state['display_action'] = $action === 'NO TRADE'
                ? ($saleBlockedByEdge ? 'HOLD LONG · SALE BELOW EDGE' : ($state['position'] === 'long' ? 'HOLD LONG' : 'WATCHING'))
                : ($sneakEligible
                    ? ('SNEAK ' . $action . ' x' . number_format($sneakFactor, 2))
                    : ($sequenceEnabled
                    ? $action . ' SEQUENCE x' . (int)($sequenceSignal['trade_count'] ?? 1)
                    : $action));

            if ($action === 'SELL'
                && $state['position'] === 'long'
                && $state['asset_units'] > 0.0
                && ((float)$state['asset_units'] * $currentPrice) >= MIN_TRADE_AMOUNT
            ) {
                $unitsHeldBefore = (float)$state['asset_units'];
                $costBasisBefore = (float)$state['asset_cost_basis'];
                $sellBaseAmount = $bellCurveActive && $bellCurveSellAmount > 0.0
                    ? $bellCurveSellAmount
                    : ((float)$state['fixed_trade_amount'] * $sellMultiplier);
                $aheadSigns = $regimeActive ? 1 : ($sequenceEnabled ? max(1, (int)($sequenceSignal['trade_count'] ?? 1)) : 1);
                $sellBaseAmount *= $aheadSigns;
                if ($sneakEligible) {
                    $sellBaseAmount *= $sneakFactor;
                }
                $sellBaseAmount = max(MIN_TRADE_AMOUNT, $sellBaseAmount);
                $sellAvailableAmount = $unitsHeldBefore * $currentPrice;
                $sellSizing = canonicalTradeSizing('SELL', $sellBaseAmount, $sellAvailableAmount);
                $sellSizing['phase_step_multiplier'] = $aheadSigns;
                $sellUnits = $sellSizing['executable_amount'] >= $sellAvailableAmount
                    ? $unitsHeldBefore
                    : $sellSizing['executable_amount'] / max(0.00000001, $currentPrice);
                $sellAmount = $sellUnits * $currentPrice;
                if ($sellUnits >= $unitsHeldBefore - 0.00000001) {
                    $state['rebuy_pending'] = true;
                }
                $costPortion = $unitsHeldBefore > 0.0
                    ? $costBasisBefore * ($sellUnits / $unitsHeldBefore)
                    : 0.0;
                $realizedPnl = $sellAmount - $costPortion;
                if (!REALIZE_LOSS_TRADES && $realizedPnl < 0.0) {
                    $trade = [
                        'action' => 'SELL LOSS' . ($sequenceEnabled ? ' SEQUENCE' : ''),
                        'time' => $boundaryTime,
                        'price' => $currentPrice,
                        'amount' => $sellAmount,
                        'units' => $sellUnits,
                        'realized_pnl' => $realizedPnl,
                        'requested_amount' => $sellSizing['requested_amount'],
                        'available_amount' => $sellSizing['available_amount'],
                        'shortfall' => $sellSizing['shortfall'],
                        'sequence_trade_count' => $sequenceEnabled ? (int)($sequenceSignal['trade_count'] ?? 1) : 1,
                        'sequence_accuracy' => $sequenceEnabled ? (float)($sequenceSignal['accuracy'] ?? 0.0) : 0.0,
                    ];
                    $state['trades'][] = $trade;
                    $state['trades'] = array_slice($state['trades'], -200);
                    $state['last_trade'] = $trade;
                    $state['realized_move'] += $realizedPnl;
                    $state['cash_left'] += $sellAmount;
                    $state['asset_units'] = max(0.0, $unitsHeldBefore - $sellUnits);
                    $state['asset_cost_basis'] = max(0.0, $costBasisBefore - $costPortion);
                    $state['total_sold_units'] += $sellUnits;
                    $state['total_sold_amount'] += $sellAmount;
                    if ($state['asset_units'] <= 0.00000001) {
                        $state['position'] = 'flat';
                        $state['asset_units'] = 0.0;
                        $state['asset_cost_basis'] = 0.0;
                        $state['entry_price'] = null;
                        $state['entry_time'] = null;
                        $state['active_trade_start_wallet'] = null;
                    } else {
                        $state['position'] = 'long';
                        $state['entry_price'] = $state['asset_cost_basis'] / $state['asset_units'];
                    }
                    $state['last_trade_result'] = 'WRONG';
                    $state['last_trade_pnl'] = $realizedPnl;
                    $state['display_action'] = $bellCurveActive
                        ? 'BELL SELL LOSS'
                        : ($sneakEligible
                            ? 'SNEAK SELL LOSS x' . number_format($sneakFactor, 2)
                            : ($sequenceEnabled ? 'SELL LOSS SEQUENCE x' . (int)($sequenceSignal['trade_count'] ?? 1) : 'SELL LOSS'));
                    $state['losses']++;
                    $state['events_by_time'][$boundaryTime] = [
                        'action' => 'SELL',
                        'label' => $sneakEligible
                            ? 'SNEAK SELL x' . number_format($sneakFactor, 2)
                            : (($sequenceEnabled
                                ? 'SELL LOSS SEQUENCE x' . (int)($sequenceSignal['trade_count'] ?? 1) . ' (' . number_format((float)($sequenceSignal['accuracy'] ?? 0.0), 1) . '%)'
                                : 'SELL LOSS')),
                        'class' => 'result-loss-cell',
                        'executed' => true,
                        'amount' => $sellAmount,
                        'realized_pnl' => $realizedPnl,
                        'requested_amount' => $sellSizing['requested_amount'],
                        'available_amount' => $sellSizing['available_amount'],
                        'shortfall' => $sellSizing['shortfall'],
                        'entry_price' => $sellUnits > 0.0 ? ($costPortion / $sellUnits) : null,
                        'exit_price' => $currentPrice,
                    ];
                } else {
                    $trade = [
                        'action' => ($realizedPnl >= 0.0 ? 'SELL GAIN' : 'SELL LOSS')
                            . ($sequenceEnabled ? ' SEQUENCE' : ''),
                        'time' => $boundaryTime,
                        'price' => $currentPrice,
                        'amount' => $sellAmount,
                        'units' => $sellUnits,
                        'realized_pnl' => $realizedPnl,
                        'sequence_trade_count' => $sequenceEnabled ? (int)($sequenceSignal['trade_count'] ?? 1) : 1,
                        'sequence_accuracy' => $sequenceEnabled ? (float)($sequenceSignal['accuracy'] ?? 0.0) : 0.0,
                    ];
                    $state['trades'][] = $trade;
                    $state['trades'] = array_slice($state['trades'], -200);
                    $state['last_trade'] = $trade;
                    $state['realized_move'] += $realizedPnl;
                    $state['cash_left'] += $sellAmount;
                    $state['asset_units'] = max(0.0, $unitsHeldBefore - $sellUnits);
                    $state['asset_cost_basis'] = max(0.0, $costBasisBefore - $costPortion);
                    $state['total_sold_units'] += $sellUnits;
                    $state['total_sold_amount'] += $sellAmount;
                    if ($state['asset_units'] <= 0.00000001) {
                        $state['position'] = 'flat';
                        $state['asset_units'] = 0.0;
                        $state['asset_cost_basis'] = 0.0;
                        $state['entry_price'] = null;
                        $state['entry_time'] = null;
                        $state['active_trade_start_wallet'] = null;
                    } else {
                        $state['position'] = 'long';
                        $state['entry_price'] = $state['asset_cost_basis'] / $state['asset_units'];
                    }
                    $state['last_trade_result'] = $realizedPnl >= 0.0 ? 'RIGHT' : 'WRONG';
                    $state['last_trade_pnl'] = $realizedPnl;
                    $state['display_action'] = $bellCurveActive
                        ? 'BELL SELL x' . (int)($hourlyBellCurvePlan['actionable_slots'] ?? 0)
                        : ($sneakEligible
                            ? 'SNEAK SELL x' . number_format($sneakFactor, 2)
                            : ($sequenceEnabled
                                ? 'SELL SEQUENCE x' . (int)($sequenceSignal['trade_count'] ?? 1)
                                : 'SELL'));
                    if ($realizedPnl >= 0.0) $state['wins']++;
                    else $state['losses']++;
                }
            } elseif ($action === 'BUY'
                && $state['cash_left'] >= MIN_TRADE_AMOUNT
                && !(($state['position'] ?? 'flat') === 'long' && (float)($state['asset_units'] ?? 0.0) > 0.00000001)
            ) {
                $entryWallet = (float)$state['cash_left'] + ((float)$state['asset_units'] * $currentPrice);
                $buyAmount = $bellCurveActive && $bellCurveBuyAmount > 0.0
                    ? min((float)$state['cash_left'], $bellCurveBuyAmount)
                    : ($forcedRebuy
                    ? min((float)$state['cash_left'], $rebuyAmount)
                    : ($state['fixed_trade_amount'] > 0.0
                    ? min((float)$state['cash_left'], (float)$state['fixed_trade_amount'])
                    : (float)$state['cash_left']));
                $buySequenceMultiplier = $regimeActive ? 1 : ($sequenceEnabled ? max(1, (int)($sequenceSignal['trade_count'] ?? 1)) : 1);
                $buyAmount *= $buySequenceMultiplier;
                if ($sneakEligible) {
                    $buyAmount *= $sneakFactor;
                }
                $buySizing = canonicalTradeSizing('BUY', max(MIN_TRADE_AMOUNT, $buyAmount), (float)$state['cash_left']);
                $buySizing['phase_step_multiplier'] = $buySequenceMultiplier;
                $buyAmount = $buySizing['executable_amount'];
                $buyUnits = $buyAmount / $currentPrice;
                if ($buyUnits > 0.0) {
                    $trade = [
                        'action' => ($sneakEligible ? 'SNEAK BUY' : 'BUY ENTERED') . ($sequenceEnabled ? ' SEQUENCE' : ''),
                        'time' => $boundaryTime,
                        'price' => $currentPrice,
                        'amount' => $buyAmount,
                        'units' => $buyUnits,
                        'requested_amount' => $buySizing['requested_amount'],
                        'available_amount' => $buySizing['available_amount'],
                        'shortfall' => $buySizing['shortfall'],
                        'sequence_trade_count' => $sequenceEnabled ? (int)($sequenceSignal['trade_count'] ?? 1) : 1,
                        'sequence_accuracy' => $sequenceEnabled ? (float)($sequenceSignal['accuracy'] ?? 0.0) : 0.0,
                    ];
                    $state['trades'][] = $trade;
                    $state['trades'] = array_slice($state['trades'], -200);
                    $state['last_trade'] = $trade;
                    $state['position'] = 'long';
                    $state['cash_left'] = max(0.0, (float)$state['cash_left'] - $buyAmount);
                    $state['asset_units'] += $buyUnits;
                    $state['asset_cost_basis'] += $buyAmount;
                    if (!is_numeric($state['active_trade_start_wallet'] ?? null)) {
                        $state['active_trade_start_wallet'] = $entryWallet;
                    }
                    $state['entry_price'] = $state['asset_units'] > 0.0
                        ? ($state['asset_cost_basis'] / $state['asset_units'])
                        : $currentPrice;
                    $state['entry_time'] = $boundaryTime;
                    $state['total_bought_units'] += $buyUnits;
                    $state['total_bought_amount'] += $buyAmount;
                    if ($forcedRebuy) $state['rebuy_pending'] = false;
                    if ($state['first_buy_amount'] <= 0.0) {
                        $state['first_buy_amount'] = $buyAmount;
                        $state['first_buy_units'] = $buyUnits;
                        $state['first_buy_price'] = $currentPrice;
                    }
                    $state['events_by_time'][$boundaryTime] = [
                        'action' => 'BUY',
                        'label' => $sneakEligible
                            ? 'SNEAK BUY x' . number_format($sneakFactor, 2)
                            : (($sequenceEnabled
                                ? 'BUY SEQUENCE x' . (int)($sequenceSignal['trade_count'] ?? 1) . ' (' . number_format((float)($sequenceSignal['accuracy'] ?? 0.0), 1) . '%)'
                                : 'BUY ENTERED')),
                        'class' => 'result-neutral-cell',
                        'executed' => true,
                        'amount' => $buyAmount,
                        'realized_pnl' => null,
                        'requested_amount' => $buySizing['requested_amount'],
                        'available_amount' => $buySizing['available_amount'],
                        'shortfall' => $buySizing['shortfall'],
                        'entry_price' => $currentPrice,
                        'exit_price' => null,
                    ];
                    $state['display_action'] = $bellCurveActive
                        ? 'BELL BUY x' . (int)($hourlyBellCurvePlan['actionable_slots'] ?? 0)
                        : ($sneakEligible
                            ? 'SNEAK BUY x' . number_format($sneakFactor, 2)
                            : ($sequenceEnabled
                                ? 'BUY SEQUENCE x' . (int)($sequenceSignal['trade_count'] ?? 1)
                                : 'BUY'));
                }
            } elseif ($action === 'BUY') {
                $state['events_by_time'][$boundaryTime] = [
                    'action' => 'BUY',
                    'label' => 'BUY HOLDING',
                    'class' => 'result-neutral-cell',
                    'executed' => false,
                    'amount' => 0.0,
                    'realized_pnl' => null,
                    'entry_price' => (float)($state['entry_price'] ?? 0.0),
                    'exit_price' => null,
                ];
                $state['display_action'] = 'HOLD LONG';
            }

            $state['last_processed_boundary'] = $boundaryTime;
        }

        if ($state['position'] === 'long' && is_numeric($state['entry_price'] ?? null) && (float)$state['entry_price'] > 0.0) {
            $entryPrice = (float)$state['entry_price'];
            $state['current_move_percent'] = (($currentPrice - $entryPrice) / $entryPrice) * 100;
        } else {
            $state['current_move_percent'] = 0.0;
        }
    }

    $state['holding_value'] = (float)$state['asset_units'] * max(0.0, $currentPrice);
    $state['open_pnl'] = (float)$state['holding_value'] - (float)$state['asset_cost_basis'];
    $state['equity_value'] = (float)$state['cash_left'] + (float)$state['holding_value'];
    $state['net_pnl'] = (float)$state['equity_value'] - (float)$state['starting_pot'];
    $state['realized_move'] = recomputeRealizedPnlFromTrades($state['trades']);
    $decisionCount = (int)$state['wins'] + (int)$state['losses'];
    $state['right_percent'] = $decisionCount > 0
        ? ((float)$state['wins'] / $decisionCount) * 100
        : 0.0;
    $state['sim_net_move'] = (float)$state['net_pnl'];
    $state['current_price'] = $currentPrice;
    $state['observed_time'] = $boundaryTime;
    $state['paper_only'] = true;
    $state['live_orders'] = false;

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($state, JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $state;
}

/** Extract the single displayed CNGN guess and exact change from one output row. */
function cngnGuessFromRow(string $htmlRow): ?array
{
    if (!preg_match("/data-left=['\"]([+-])['\"]/i", $htmlRow, $leftMatch)) return null;
    if (!preg_match("/data-right=['\"]([+-])['\"]/i", $htmlRow, $rightMatch)) return null;
    if (!preg_match("/data-change=['\"]([0-9.eE+-]+)['\"]/i", $htmlRow, $changeMatch)) return null;
    preg_match_all('/<td\b[^>]*>(.*?)<\/td>/is', $htmlRow, $cells);
    $cellValues = array_map(
        static fn(string $value): string => trim(html_entity_decode(strip_tags($value))),
        $cells[1] ?? []
    );
    $pair = $leftMatch[1] . $rightMatch[1];
    $guess = [
        'left' => $leftMatch[1],
        'right' => $rightMatch[1],
        'pair' => $pair,
        'symbol' => $pair,
        'direction' => newGuessDirectionFromPair($pair),
        'change' => abs((float)$changeMatch[1]),
    ];
    $attributeMap = [
        'integrand' => 'integrand',
        'integral' => 'integral',
        'wall-percent' => 'wall_percent',
        'wall-bias' => 'wall_bias',
        'differential' => 'differential',
        'derived' => 'derived',
    ];
    foreach ($attributeMap as $attribute => $key) {
        if (preg_match("/data-" . preg_quote($attribute, '/') . "=['\"]([0-9.eE+-]+)['\"]/i", $htmlRow, $metricMatch)) {
            $guess[$key] = (float)$metricMatch[1];
        }
    }
    if (!isset($guess['differential']) && isset($cellValues[1]) && is_numeric($cellValues[1])) {
        $guess['differential'] = (float)$cellValues[1];
    }
    if (!isset($guess['derived']) && isset($cellValues[2]) && is_numeric($cellValues[2])) {
        $guess['derived'] = (float)$cellValues[2];
    }
    if (!isset($guess['integral']) && isset($cellValues[3]) && is_numeric($cellValues[3])) {
        $guess['integral'] = (float)$cellValues[3];
    }
    if (!isset($guess['integrand']) && isset($guess['differential']) && is_numeric($guess['differential'])) {
        $guess['integrand'] = (float)$guess['differential'];
    }
    return $guess;
}

/** Extract newest-first historical left/right pairs for immediate chart backfill. */
function historicalCngnGuesses(string $resultHtml): array
{
    $guesses = [];
    if (!preg_match_all("/data-left=['\"]([+-])['\"]\s+data-right=['\"]([+-])['\"]/i", $resultHtml, $matches, PREG_SET_ORDER)) return $guesses;
    foreach ($matches as $match) {
        $pair = $match[1] . $match[2];
        $guesses[] = [
            'left' => $match[1],
            'right' => $match[2],
            'pair' => $pair,
            'symbol' => $pair,
            'direction' => newGuessDirectionFromPair($pair),
        ];
    }
    return $guesses;
}

function internalAgreementStatsFromTableHtml(string $resultHtml, int $windowSize = ONE_HOUR_CANDLE_COUNT): array
{
    $rows = historicalCngnGuesses($resultHtml);
    if (!$rows) {
        return [
            'right' => 0,
            'total' => 0,
            'percent' => 0.0,
            'window' => max(1, $windowSize),
            'recent_right' => 0,
            'recent_total' => 0,
            'recent_percent' => 0.0,
        ];
    }

    $total = count($rows);
    $right = 0;
    foreach ($rows as $row) {
        if ((string)($row['left'] ?? '') === (string)($row['right'] ?? '')) {
            $right++;
        }
    }

    $recentRows = array_slice($rows, 0, max(1, $windowSize));
    $recentTotal = count($recentRows);
    $recentRight = 0;
    foreach ($recentRows as $row) {
        if ((string)($row['left'] ?? '') === (string)($row['right'] ?? '')) {
            $recentRight++;
        }
    }

    return [
        'right' => $right,
        'total' => $total,
        'percent' => $total > 0 ? round(($right / $total) * 100.0, 2) : 0.0,
        'window' => max(1, $windowSize),
        'recent_right' => $recentRight,
        'recent_total' => $recentTotal,
        'recent_percent' => $recentTotal > 0 ? round(($recentRight / $recentTotal) * 100.0, 2) : 0.0,
    ];
}

/** Calculate internal agreement from the resolved table truth, not raw sign matching. */
function internalAgreementStatsFromResolvedTable(array $resolvedResultsByTime, int $windowSize = ONE_HOUR_CANDLE_COUNT): array
{
    $rows = array_values(array_filter($resolvedResultsByTime, static fn($row): bool => is_array($row)));
    usort($rows, static function (array $left, array $right): int {
        return ((int)(yahooTimestamp((string)($right['time'] ?? '')) ?? 0))
            <=> ((int)(yahooTimestamp((string)($left['time'] ?? '')) ?? 0));
    });
    $right = 0;
    foreach ($rows as $row) {
        if (($row['right'] ?? null) === true) $right++;
    }
    $recentRows = array_slice($rows, 0, max(1, $windowSize));
    $recentRight = 0;
    foreach ($recentRows as $row) {
        if (($row['right'] ?? null) === true) $recentRight++;
    }
    $total = count($rows);
    $recentTotal = count($recentRows);
    return [
        'right' => $right,
        'total' => $total,
        'percent' => $total > 0 ? round(($right / $total) * 100.0, 2) : 0.0,
        'window' => max(1, $windowSize),
        'recent_right' => $recentRight,
        'recent_total' => $recentTotal,
        'recent_percent' => $recentTotal > 0 ? round(($recentRight / $recentTotal) * 100.0, 2) : 0.0,
    ];
}

/** Extract historical timestamp-locked guesses directly from the bitcoin() table HTML. */
function historicalTimedCngnGuesses(string $resultHtml): array
{
    $guessesByTime = [];
    if (!preg_match_all('/<tr\b[^>]*>.*?<\/tr>/is', $resultHtml, $rows)) return $guessesByTime;
    foreach ($rows[0] as $htmlRow) {
        if (stripos($htmlRow, 'Long Form Date') !== false) continue;
        if (!preg_match_all('/<td\b[^>]*>(.*?)<\/td>/is', $htmlRow, $cells) || count($cells[0]) < 6) continue;
        $time = trim(html_entity_decode(strip_tags($cells[1][0] ?? '')));
        if ($time === '' || yahooTimestamp($time) === null) continue;
        $guess = cngnGuessFromRow($htmlRow);
        if (!is_array($guess)) continue;
        $guess['time'] = $time;
        $guessesByTime[$time] = $guess;
    }
    uksort($guessesByTime, static fn(string $a, string $b): int => strcmp($a, $b));
    return $guessesByTime;
}

/** Resolve one pending forecast per completed candle and persist its totals. */
function updateAccumulatingScore(string $statePath, ?array $completed, ?array $currentGuess): array
{
    $default = [
        'true' => 0,
        'false' => 0,
        'last_resolved' => null,
        'pending_direction' => null,
        'pending_pair' => null,
        'last_result' => null,
        'last_predicted' => null,
        'last_actual' => null,
        'last_pair' => null,
        'history' => [],
    ];
    $handle = @fopen($statePath, 'c+');
    if ($handle === false) return $default;

    flock($handle, LOCK_EX);
    rewind($handle);
    $raw = stream_get_contents($handle);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    $state = is_array($decoded) ? array_merge($default, $decoded) : $default;

    if ($completed !== null && $state['last_resolved'] !== $completed['key']) {
        if ($state['pending_direction'] === '+' || $state['pending_direction'] === '-') {
            // The direction was locked when this timestamp first appeared.
            // A later pair-rule change must not repair its answer.
            $resolvedDirection = $state['pending_direction'];
            $isCorrect = ($resolvedDirection === '+' || $resolvedDirection === '-')
                && $resolvedDirection === $completed['direction'];
            if ($isCorrect) $state['true']++;
            else $state['false']++;
            $state['last_result'] = $isCorrect ? 'RIGHT' : 'WRONG';
            $state['last_predicted'] = $state['pending_direction'];
            $state['last_actual'] = $completed['direction'];
            $state['last_pair'] = $state['pending_pair'];
            $state['history'][] = [
                'time' => $completed['key'],
                'correct' => $isCorrect,
                'true' => (int)$state['true'],
                'false' => (int)$state['false'],
            ];
            $state['history'] = array_slice($state['history'], -100);
        }
        $state['last_resolved'] = $completed['key'];
    }

    if (is_array($currentGuess) && ($currentGuess['direction'] === '+' || $currentGuess['direction'] === '-')) {
        $state['pending_direction'] = $currentGuess['direction'];
        $state['pending_pair'] = $currentGuess['pair'];
    }

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($state, JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $state;
}

$cache_seconds = 30; // check/rebuild the market cache file about every 30 seconds
$force_refresh = isset($_GET['full_refresh']) && $_GET['full_refresh'] === '1';
$file_mtime = file_exists($file_path) ? (int)@filemtime($file_path) : 0;
$cache_is_stale = $file_mtime === 0 || (time() - $file_mtime) >= $cache_seconds;
$full_file_refreshed = false;

if (($force_refresh || $cache_is_stale) && $loop_update_allowed) {
    [$csv, $download_error, $yahoo_meta] = fetchYahooChartCsv($ticker, '5d', 100, $market_type);

    if ($csv !== false) {
        $temporary = $file_path . '.tmp';
        if (@file_put_contents($temporary, $csv, LOCK_EX) !== false) {
            @chmod($temporary, 0664);
            @rename($temporary, $file_path);
            clearstatcache(true, $file_path);
            $full_file_refreshed = true;
            $data_note = 'Full market cache file rebuilt for the 30-second check.';
        } else {
            $download_error = 'Fresh data was received but could not be saved.';
        }
    }

    if ($csv === false || $download_error !== '') {
        if ($csv === false && $download_error !== '' && yahooErrorImpliesMissingSymbol($download_error) && !externalConnectivityLooksDown()) {
            clearTickerArtifacts($dir, $market_type, $ticker);
            removeTrackedIndexTargetFiles(
                __DIR__ . '/wsl_portfolio_targets.json',
                cpanelCronRegistryPath(),
                $market_type,
                $ticker
            );
            $cpanel_cron_registry_targets = loadCpanelCronRegistry(cpanelCronRegistryPath())['targets'] ?? [];
            writeCpanelCronCommandsSnapshot(
                cpanelCronCommandsPath(),
                cpanelCronWriterPath(),
                cpanelCronRegistryPath(),
                $cpanel_cron_registry_targets
            );
            $tracked_index_targets = loadTrackedIndexTargets(
                __DIR__ . '/wsl_portfolio_targets.json',
                cpanelCronRegistryPath()
            );
            $tracked_link_groups = buildTrackedLinkGroups($tracked_index_targets, $market_type, $ticker);
            $tracked_link_groups = applyTrackedLinkPrices($tracked_link_groups, $tracked_yahoo_quotes, $dir);
            $tracked_crypto_links = $tracked_link_groups['crypto'];
            $tracked_stock_links = $tracked_link_groups['stock'];
            $tracked_marquee_links = $tracked_link_groups['marquee'];
        }
        if (file_exists($file_path) && filesize($file_path) >= 1000) {
            $data_note = 'Market refresh failed; displaying the most recent cached data.';
        } else {
            $error_message = $download_error ?: 'Market data could not be downloaded for this symbol.';
        }
    }
} elseif ($readonly_browser_mode) {
    $data_note = $scheduler_cache_note;
} else {
    $data_note = 'Using the current market cache file; next rebuild check is throttled to 30 seconds.';
}

// Refresh the complete ticker file frequently, but refresh a small
// observation file only once per exact five-minute boundary. This file supplies
// actual candles only; it never recalculates a saved guess.
$observation_path = $dir . $ticker . '-five-minute.csv';
$last_observation_rows = csvPriceRows($observation_path);
$last_observation_row = $last_observation_rows ? $last_observation_rows[count($last_observation_rows) - 1] : null;
$last_observation_time = isset($last_observation_row[0]) ? (string)$last_observation_row[0] : null;
$current_boundary_epoch = effectiveCurrentBoundaryEpoch(time(), $last_observation_time);
$five_minute_boundary = (int)floor($current_boundary_epoch / 300);
$last_observation_epoch = isset($last_observation_row[0]) ? yahooTimestamp((string)$last_observation_row[0]) : null;
$observation_needs_boundary = $last_observation_epoch === null || $last_observation_epoch < $current_boundary_epoch;
if ($error_message === '' && $observation_needs_boundary && $loop_update_allowed) {
    $boundary_price = null;
    $boundary_note = '';
    $yahoo_quote = $tracked_yahoo_quotes[strtoupper($ticker)] ?? null;
    if (!is_array($yahoo_quote)) {
        $single_quote = fetchYahooLatestPrices([strtoupper($ticker)]);
        $yahoo_quote = $single_quote[strtoupper($ticker)] ?? null;
    }
    if (is_array($yahoo_quote) && isset($yahoo_quote['price']) && is_numeric($yahoo_quote['price'])) {
        $boundary_price = (float)$yahoo_quote['price'];
        $boundary_note = 'Yahoo latest price row appended at the five-minute boundary.';
    } else {
        $boundary_price = latestCsvClose($file_path);
        if ($boundary_price !== null) {
            $boundary_note = 'Yahoo boundary update missed; appended the latest cached close instead.';
        }
    }

    if (is_numeric($boundary_price) && (float)$boundary_price > 0.0) {
        if (appendObservationBoundaryRow($observation_path, $file_path, $current_boundary_epoch, (float)$boundary_price)) {
            $data_note = $boundary_note !== '' ? $boundary_note : $data_note;
        }
    }
}

$actual_data_path = file_exists($observation_path) && filesize($observation_path) >= 100
    ? $observation_path
    : $file_path;

$rets_sofar = ['', 0, $display_path];
$flip_on_boundary = ($five_minute_boundary % 2) === 1;

if ($error_message === '' && file_exists($file_path)) {
    try {
        $rets_sofar = $next->bitcoin($file_path, 15, 1, 0, $flip_on_boundary);
    } catch (Throwable $exception) {
        $error_message = $exception->getMessage();
    }
}

$projected_accuracy = is_numeric($rets_sofar[1]) ? (float) $rets_sofar[1] : 0.0;
$projected_accuracy_counts = is_array($rets_sofar[3] ?? null) ? $rets_sofar[3] : ['right' => 0, 'total' => 0];
$projected_accuracy_right = (int) ($projected_accuracy_counts['right'] ?? 0);
$projected_accuracy_total = (int) ($projected_accuracy_counts['total'] ?? 0);
$accuracy = 100.0;
$accuracy_right = 0;
$accuracy_total = 0;
$accuracy_wrong = 0;
$accuracy_passed = 0;
$accuracy_class = 'good';
$accuracy_note = '0 MISSES • 0 PASSED • LOOP 2 CARRY-FORWARD TRUST 100.00%';
$completed_candle = completedCandleDirection($actual_data_path);
$current_cngn_guess = currentCngnGuess((string)$rets_sofar[0]);
$internal_agreement = internalAgreementStatsFromTableHtml((string)$rets_sofar[0], ONE_HOUR_CANDLE_COUNT);
$candle_chart = latestFiveMinuteCandles($actual_data_path);
$chart_source_candles = mergedFiveMinuteCandles([$file_path, $actual_data_path]);
$chart_hourly_candles = hourlyChartCandles($chart_source_candles, CHART_HOURLY_WINDOW);
$hour_metric_candles = array_slice($candle_chart, -ONE_HOUR_CANDLE_COUNT);
$hour_high = $hour_metric_candles ? max(array_column($hour_metric_candles, 'high')) : 0.0;
$hour_low = $hour_metric_candles ? min(array_column($hour_metric_candles, 'low')) : 0.0;
$current_price = 0.0;
$last_price_change = 0.0;
$current_price_percentage = 0.0;
$hour_reference_price = 0.0;
$hour_price_change = 0.0;
$hour_price_percentage = 0.0;
if ($candle_chart) {
    $latest_price_index = count($candle_chart) - 1;
    $current_price = (float)$candle_chart[$latest_price_index]['close'];
    $previous_price = $latest_price_index > 0
        ? (float)$candle_chart[$latest_price_index - 1]['close']
        : (float)$candle_chart[$latest_price_index]['open'];
    $last_price_change = $current_price - $previous_price;
    $current_price_percentage = $previous_price != 0.0
        ? ($last_price_change / $previous_price) * 100
        : 0.0;
    $hour_reference_price = $hour_metric_candles
        ? (float)$hour_metric_candles[0]['open']
        : (float)$candle_chart[0]['open'];
    $hour_price_change = $current_price - $hour_reference_price;
    $hour_price_percentage = $hour_reference_price != 0.0
        ? ($hour_price_change / $hour_reference_price) * 100
        : 0.0;
}
$current_price_source = 'OBSERVED';
    $yahoo_quote = $tracked_yahoo_quotes[strtoupper($ticker)] ?? null;
if (!is_array($yahoo_quote)) {
    $single_quote = fetchYahooLatestPrices([strtoupper($ticker)]);
    $yahoo_quote = $single_quote[strtoupper($ticker)] ?? null;
}
if (is_array($yahoo_quote) && isset($yahoo_quote['price']) && is_numeric($yahoo_quote['price'])) {
    $current_price = (float)$yahoo_quote['price'];
    $current_price_source = 'YAHOO';
    $previous_price = count($candle_chart) > 1
        ? (float)$candle_chart[count($candle_chart) - 2]['close']
        : (count($candle_chart) ? (float)$candle_chart[count($candle_chart) - 1]['open'] : $current_price);
    $last_price_change = $current_price - $previous_price;
    $current_price_percentage = $previous_price != 0.0
        ? ($last_price_change / $previous_price) * 100
        : 0.0;
    $hour_price_change = $current_price - $hour_reference_price;
    $hour_price_percentage = $hour_reference_price != 0.0
        ? ($hour_price_change / $hour_reference_price) * 100
        : 0.0;
}
if ($current_price <= 0.0 && is_numeric($cron_summary['currentPrice'] ?? null)) {
    $current_price = (float)$cron_summary['currentPrice'];
    $current_price_source = 'CRON-CACHE';
}
$current_price_class = $hour_price_change > 0.0
    ? 'good'
    : ($hour_price_change < 0.0 ? 'low' : 'medium');
$current_price_direction = $hour_price_change > 0.0
    ? 'UP'
    : ($hour_price_change < 0.0 ? 'DOWN' : 'FLAT');
$latest_market_time = $candle_chart
    ? (string)$candle_chart[count($candle_chart) - 1]['time']
    : gmdate('Y-m-d\TH:i:s\Z', $five_minute_boundary * 300);
$paper_break_state = [];
$paper_break_action = 'WATCHING';
$paper_break_class = 'medium';
$average_change = 0.0;
if (count($candle_chart) > 1) {
    $spot_changes = [];
    for ($spotIndex = 1; $spotIndex < count($candle_chart); $spotIndex++) {
        $spot_changes[] = abs((float)$candle_chart[$spotIndex]['close'] - (float)$candle_chart[$spotIndex - 1]['close']);
    }
    $average_change = array_sum($spot_changes) / count($spot_changes);
}
$previous_completed_change = previousCompletedFiveMinuteMove($candle_chart);
$latent_guess_change = $previous_completed_change > 0.0
    ? $previous_completed_change
    : ($average_change > 0.0 ? $average_change : abs($last_price_change));
$trade_capture_ratio = 0.90;
$first_trade_amount = $average_change > 0.0
    ? abs($average_change) * $trade_capture_ratio * $buy_multiplier
    : abs($latent_guess_change) * $trade_capture_ratio * $buy_multiplier;
if (is_array($current_cngn_guess)
    && (!isset($current_cngn_guess['change']) || !is_numeric($current_cngn_guess['change']) || abs((float)$current_cngn_guess['change']) <= 0.0)
) {
    $current_cngn_guess['change'] = $latent_guess_change;
}
$guess_history_path = $dir . $ticker . '-neutral-guesses.json';
$early_boundary_epoch = $five_minute_boundary * 300;
$early_boundary_key = gmdate('Y-m-d\\TH:i:s\\Z', $early_boundary_epoch);
$carry_forward_reset_state = loadLocalJsonArray($carry_forward_reset_path);
$carry_forward_reset_time = trim((string)($carry_forward_reset_state['started_at'] ?? ''));
if ($carry_forward_reset_time === '' && !file_exists($dir . $ticker . '-settled-results.json')) {
    $carry_forward_reset_time = $early_boundary_key;
    if ($loop_update_allowed) {
        saveLocalJsonArray($carry_forward_reset_path, [
            'symbol' => $ticker,
            'market_type' => $market_type,
            'started_at' => $carry_forward_reset_time,
            'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ]);
    }
}
$guess_history_state = loadLocalJsonArray($guess_history_path);
$early_locked_forecasts = $loop_update_allowed
    ? updateForecastHistory(
        $guess_history_path,
        $early_boundary_epoch,
        [],
        $current_cngn_guess,
        0
    )
    : (is_array($guess_history_state['forecasts'] ?? null) ? $guess_history_state['forecasts'] : []);
$locked_current_guess = normalizeCngnGuess(
    is_array($early_locked_forecasts[$early_boundary_key] ?? null)
        ? $early_locked_forecasts[$early_boundary_key]
        : $current_cngn_guess
);
$guess_state = $loop_update_allowed
    ? updateGuessHistory($guess_history_path, $completed_candle, $locked_current_guess)
    : (is_array($guess_history_state) ? $guess_history_state : []);
$guess_by_time = [];
foreach (($guess_state['history'] ?? []) as $saved_guess) {
    $normalized_guess = normalizeCngnGuess(is_array($saved_guess) ? $saved_guess : null);
    if (isset($saved_guess['time']) && is_array($normalized_guess)) {
        $guess_by_time[$saved_guess['time']] = $normalized_guess;
    }
}
$html_historical_guesses = historicalTimedCngnGuesses((string)$rets_sofar[0]);
foreach ($html_historical_guesses as $time => $html_guess) {
    if (!isset($guess_by_time[$time]) && is_array($html_guess)) {
        $guess_by_time[$time] = $html_guess;
    }
}
uksort($guess_by_time, static fn(string $a, string $b): int => strcmp($a, $b));
$paper_wallet_bootstrap = loadOrCreatePaperWalletBootstrap(
    $wallet_bootstrap_path,
    $market_type,
    $ticker,
    $latest_market_time,
    (float)$current_price,
    10000.0
);
$guess_candles = [];
$effective_guess_by_time = [];
$paper_profit = 0.0;
$paper_trades = 0;
foreach ($candle_chart as $candle) {
    $saved_guess = $guess_by_time[$candle['time']] ?? null;
    if (($candle['forming'] ?? false) && is_array($locked_current_guess)) $saved_guess = $locked_current_guess;
    if (!is_array($saved_guess)) continue;
    $stored_direction = $saved_guess['direction'] ?? null;
    $direction = ($stored_direction === '+' || $stored_direction === '-')
        ? $stored_direction
        : newGuessDirectionFromPair(guessPairLabel($saved_guess));
    if ($direction !== '+' && $direction !== '-') continue;
    $change = guessStoredChange($saved_guess, $latent_guess_change);
    $pair = guessPairLabel($saved_guess);
    $effective_guess_by_time[$candle['time']] = $saved_guess;
    $predicted_close = $candle['open'] + ($direction === '+' ? $change : -$change);
    $guess_candles[] = [
        'time' => $candle['time'],
        'open' => $candle['open'],
        'high' => max($candle['open'], $predicted_close) + ($change * .12),
        'low' => min($candle['open'], $predicted_close) - ($change * .12),
        'close' => $predicted_close,
        'direction' => $direction,
        'symbol' => $pair,
        'pair' => $pair,
        'action' => guessStoredAction($saved_guess),
        'change' => $change,
        'forming' => (bool)($candle['forming'] ?? false),
    ];
    if (!($candle['forming'] ?? false) && $candle['open'] != 0.0) {
        $return = ($candle['close'] - $candle['open']) / $candle['open'];
        $paper_profit += $direction === '+' ? $change : -$change;
        $paper_trades++;
    }
}
$settled_actuals_path = $dir . $ticker . '-settled-actuals.json';
$actual_direction_by_time = finalizedActualDirectionsByTime(
    [$actual_data_path, $file_path],
    $settled_actuals_path,
    600
);
/** Reduce a resolved CNGN row to Date, Low/value, and Boolean result. */
function compactResolvedRow(string $htmlRow, array $actualDirectionByTime = []): string
{
    if (!preg_match_all('/<td\b[^>]*>(.*?)<\/td>/is', $htmlRow, $cells) || count($cells[0]) < 6) return '';
    $rawTime = trim(html_entity_decode(strip_tags($cells[1][0])));
    $timestamp = yahooTimestamp($rawTime);
    $shortTime = $timestamp !== false ? gmdate('m/d H:i', $timestamp) : substr($rawTime, 0, 11);
    $resultCell = '<td>UNRESOLVED</td>';
    if (array_key_exists($rawTime, $actualDirectionByTime)
        && preg_match("/data-left=['\"]([+-])['\"]\s+data-right=['\"]([+-])['\"]/i", $cells[0][5], $pairMatch)) {
        $pair = $pairMatch[1] . $pairMatch[2];
        $guessSymbol = $pair;
        $guessDirection = newGuessDirectionFromPair($pair);
        $actualSymbol = $actualDirectionByTime[$rawTime];
        $outcome = resolvedOutcomeMeta($guessDirection, $actualSymbol);
        $resultCell = '<td class="' . htmlspecialchars((string)$outcome['class']) . '">'
            . htmlspecialchars((string)$outcome['label']) . '</td>';
    }
    $epochAttribute = $timestamp !== false ? ' data-epoch="' . ((int)$timestamp * 1000) . '"' : '';
    return '<tr><td' . $epochAttribute . ' title="' . htmlspecialchars($rawTime) . '">' . htmlspecialchars($shortTime) . '</td>'
        . $cells[0][4] . $resultCell . '</tr>';
}

/** Reduce one second-loop row to a future timestamp and its left-winning pair. */
function compactForecastRow(string $htmlRow, int $epoch): string
{
    if (!preg_match_all('/<td\b[^>]*>(.*?)<\/td>/is', $htmlRow, $cells) || count($cells[0]) < 4) return '';
    $seqTimestamp = trim(html_entity_decode(strip_tags($cells[1][0])));
    $seqEpoch = yahooTimestamp($seqTimestamp);
    if ($seqEpoch !== null) $epoch = $seqEpoch;
    $guess = cngnGuessFromRow($htmlRow);
    if (!is_array($guess)) return '';
    $pair = guessPairLabel($guess);
    $direction = guessStoredAction($guess);
    return '<tr class="forward-guess-row"><td data-epoch="' . ($epoch * 1000) . '">' . gmdate('m/d H:i', $epoch) . '</td>'
        . '<td>' . htmlspecialchars($pair) . ' · ' . $direction . '</td>'
        . '<td>HYPOTHETICAL</td></tr>';
}

function effectiveCurrentBoundaryEpoch(int $wallClockEpoch, ?string $latestMarketTime): int
{
    $wallBoundary = (int)(floor($wallClockEpoch / 300) * 300);
    $marketEpoch = is_string($latestMarketTime) ? yahooTimestamp($latestMarketTime) : null;
    if (!is_int($marketEpoch)) return $wallBoundary;
    $marketBoundary = (int)(floor($marketEpoch / 300) * 300);
    if ($marketBoundary > $wallBoundary) return $marketBoundary;
    if (($wallBoundary - $marketBoundary) > 300) return $marketBoundary + 300;
    return $wallBoundary;
}

$visible_rows_html = '';
$forecast_rows = [];
$history_rows = [];
$current_boundary_epoch = (int)floor(time() / 300) * 300;
if (preg_match_all('/<tr\b[^>]*>.*?<\/tr>/is', (string)$rets_sofar[0], $table_rows)) {
    $header_index = 0;
    foreach ($table_rows[0] as $index => $html_row) {
        if (stripos($html_row, 'Long Form Date') !== false) { $header_index = $index; break; }
    }
    $forecast_rows = array_reverse(array_slice($table_rows[0], 0, $header_index));
    $history_rows = array_slice($table_rows[0], $header_index + 1);
    $left_sign = $current_cngn_guess['left'] ?? '?';
    $right_sign = $current_cngn_guess['right'] ?? '?';
    $winning_symbol = guessPairLabel($current_cngn_guess);
    $current_action = guessStoredAction($current_cngn_guess);
    $current_action_change = guessStoredChange($current_cngn_guess, $latent_guess_change);
    $current_row = "<tr class='current-guess-row'>"
        . "<td>CURRENT 5-MINUTE</td>"
        . "<td>CURRENT: " . htmlspecialchars($current_action)
        . ($current_action === 'NO TRADE' ? '' : ' · TARGET +$' . number_format($current_action_change, 4, '.', ','))
        . "</td>"
        . "<td>" . ($winning_symbol === '%' ? 'UNKNOWN' : 'UNRESOLVED') . "</td></tr>";
    $above_rows = [];
    for ($step = 9; $step >= 1; $step--) {
        $source_row = $forecast_rows[$step - 1] ?? null;
        if ($source_row !== null) $above_rows[] = compactForecastRow($source_row, $current_boundary_epoch + ($step * 300));
    }
    $above_rows = array_filter($above_rows);
    $below_rows = array_filter(array_map(static fn(string $row): string => compactResolvedRow($row, $actual_direction_by_time), array_slice($history_rows, 0, 9)));
    $visible_rows_html = '<tr><td>Time</td><td>Model Signal</td><td>Observed Result</td></tr>'
        . implode('', $above_rows)
        . $current_row
        . implode('', $below_rows);
}

// Freeze the full chart horizon, while the visible table remains short.
$forecast_by_time = $loop_update_allowed
    ? updateForecastHistory(
        $guess_history_path,
        $current_boundary_epoch,
        $forecast_rows,
        $current_cngn_guess,
        FUTURE_GUESS_HORIZON
    )
    : (is_array($guess_history_state['forecasts'] ?? null) ? $guess_history_state['forecasts'] : []);
foreach ($forecast_by_time as $forecast_time => $saved_forecast) {
    $normalized_forecast = normalizeCngnGuess(is_array($saved_forecast) ? $saved_forecast : null);
    if (is_array($normalized_forecast)) $forecast_by_time[$forecast_time] = $normalized_forecast;
}
$base_hourly_bell_curve_plan = buildHourlyBellCurvePlan(
    $forecast_by_time,
    $early_boundary_key,
    $locked_current_guess,
    $first_trade_amount,
    $buy_multiplier,
    $sell_multiplier,
    $trust_percent,
    ONE_HOUR_CANDLE_COUNT
);
$hour_audit_candles = latestCompletedFiveMinuteCandles($chart_source_candles, 12);
$hour_audit_guesses = cachedGuessesForHour($hour_audit_candles, $forecast_by_time, $guess_by_time);
$hour_audit_summary = buildHourAuditSummary($ticker, $hour_audit_candles, $hour_audit_guesses);
$hour_audit_table_html = renderHourAuditTable($hour_audit_summary);
$hour_audit_guess = $hour_audit_summary['guess_accuracy'] ?? ['right' => 0, 'wrong' => 0, 'percent' => 0.0];
$hour_audit_strategy = $hour_audit_summary['strategy'] ?? ['wins' => 0, 'losses' => 0, 'net_pnl' => 0.0];
$hour_audit_long = $hour_audit_summary['long'] ?? ['wins' => 0, 'losses' => 0, 'net_pnl' => 0.0];
$hour_audit_short = $hour_audit_summary['short'] ?? ['wins' => 0, 'losses' => 0, 'net_pnl' => 0.0];
$hour_audit_best = $hour_audit_summary['best_side'] ?? ['wins' => 0, 'losses' => 0, 'net_pnl' => 0.0];
$hour_audit_sequences = $hour_audit_summary['sequences'] ?? [
    'current_sell_signal_streak' => 0,
    'current_down_candle_streak' => 0,
    'max_sell_signal_streak' => 0,
    'max_down_candle_streak' => 0,
];
$selloff_tip = (int)($hour_audit_sequences['current_sell_signal_streak'] ?? 0) >= 2;
$hour_audit_execution_winner = chooseHourAuditExecutionWinner(
    $hour_audit_strategy,
    $hour_audit_long,
    $hour_audit_short
);
$current_forecast_key = gmdate('Y-m-d\\TH:i:s\\Z', $current_boundary_epoch);
$display_current_guess = $forecast_by_time[$current_forecast_key] ?? $current_cngn_guess;
$attack_profile = buildAttackProfile(
    $trust_percent,
    $accuracy,
    (float)($hour_audit_guess['percent'] ?? 0.0)
);
$attack_trade_amount = $first_trade_amount * (float)($attack_profile['factor'] ?? 1.0);
$hourly_bell_curve_plan = $base_hourly_bell_curve_plan;

$make_guess_candle = static function (string $time, float $open, ?array $guess, bool $forming = false) use ($latent_guess_change): ?array {
    if (!is_array($guess)) return null;
    $symbol = guessPairLabel($guess);
    $action = guessStoredAction($guess);
    $stored_direction = $guess['direction'] ?? null;
    $direction = ($stored_direction === '+' || $stored_direction === '-')
        ? $stored_direction
        : newGuessDirectionFromPair($symbol);
    if ($direction !== '+' && $direction !== '-') {
        if ($symbol !== '%') return null;
        return [
            'time' => $time,
            'open' => $open,
            'high' => $open,
            'low' => $open,
            'close' => $open,
            'direction' => null,
            'symbol' => '%',
            'pair' => '%',
            'action' => $action,
            'change' => 0.0,
            'forming' => $forming,
        ];
    }
    $change = guessStoredChange($guess, $latent_guess_change);
    $close = $open + ($direction === '+' ? $change : -$change);
    return [
        'time' => $time,
        'open' => $open,
        'high' => max($open, $close) + ($change * .12),
        'low' => min($open, $close) - ($change * .12),
        'close' => $close,
        'direction' => $direction,
        'symbol' => $symbol,
        'pair' => $symbol,
        'action' => $action,
        'change' => $change,
        'forming' => $forming,
    ];
};

$timeline = [];
$recent_actual = array_slice($candle_chart, -6);
$actual_by_time = [];
foreach ($candle_chart as $candle) $actual_by_time[(string)$candle['time']] = $candle;
$guess_anchor_close = $recent_actual ? (float)$recent_actual[0]['open'] : 0.0;
$current_guess_close = null;
for ($slotIndex = 0; $slotIndex < 6; $slotIndex++) {
    $isCurrent = $slotIndex === 5;
    $display_time = gmdate('Y-m-d\\TH:i:s\\Z', $current_boundary_epoch - ((5 - $slotIndex) * 300));
    $actual = $actual_by_time[$display_time] ?? null;
    $static_guess = $forecast_by_time[$display_time]
        ?? (is_array($actual) ? ($forecast_by_time[$actual['time']] ?? null) : null);
    $guess = $static_guess
        ?? ($isCurrent
            ? $current_cngn_guess
            : ($guess_by_time[$display_time] ?? null));
    if (is_array($guess)
        && (!array_key_exists('change', $guess) || !is_numeric($guess['change']) || abs((float)$guess['change']) <= 0.0)
    ) {
        $guess['change'] = $latent_guess_change;
        $guess['symbol'] = guessPairLabel($guess);
    }
    $guess_time = $display_time;
    $connected_guess = $make_guess_candle($guess_time, $guess_anchor_close, $guess, $isCurrent);
    $timeline[] = [
        'time' => is_array($actual) ? $actual['time'] : $display_time,
        'displayTime' => $display_time,
        'phase' => $isCurrent ? 'current' : 'elapsed',
        'actual' => $actual,
        'guess' => $connected_guess,
        'guessSymbol' => guessPairLabel(is_array($guess) ? $guess : null),
        'guessPair' => guessPairLabel(is_array($guess) ? $guess : null),
        'guessAction' => guessStoredAction(is_array($guess) ? $guess : null),
        'guessChange' => is_array($connected_guess) ? (float)($connected_guess['change'] ?? 0.0) : 0.0,
    ];
    if ($isCurrent && is_array($connected_guess)) $current_guess_close = (float)$connected_guess['close'];
    if (is_array($actual)) {
        $guess_anchor_close = (float)$actual['close'];
    } elseif (is_array($connected_guess)) {
        $guess_anchor_close = (float)$connected_guess['close'];
    }
}

$future_open = $current_guess_close
    ?? ($recent_actual ? (float)$recent_actual[count($recent_actual) - 1]['close'] : 0.0);
$boundary_epoch = $current_boundary_epoch;
for ($step = 1; $step <= TRADE_ANALYSIS_HORIZON; $step++) {
    $future_time = gmdate('Y-m-d\TH:i:s\Z', $boundary_epoch + ($step * 300));
    $future_guess = $forecast_by_time[$future_time]
        ?? (isset($forecast_rows[$step - 1]) ? cngnGuessFromRow($forecast_rows[$step - 1]) : null);
    $guess_candle = $make_guess_candle($future_time, $future_open, $future_guess);
    $timeline[] = [
        'time' => $future_time,
        'displayTime' => $future_time,
        'phase' => 'future',
        'actual' => null,
        'guess' => $guess_candle,
        'guessSymbol' => guessPairLabel(is_array($future_guess) ? $future_guess : null),
        'guessPair' => guessPairLabel(is_array($future_guess) ? $future_guess : null),
        'guessAction' => guessStoredAction(is_array($future_guess) ? $future_guess : null),
        'guessChange' => is_array($guess_candle) ? (float)($guess_candle['change'] ?? 0.0) : 0.0,
    ];
    if (is_array($guess_candle)) $future_open = (float)$guess_candle['close'];
}
$current_phase_status = buildCurrentPhaseStatus($timeline);

// Seed any missing timestamp-locked guesses around the visible live area so the
// current handoff remains stable while the chart renders in hourly candles.
$chart_guess_seeds = [];
foreach ($candle_chart as $chart_candle) {
    $chart_time = (string)$chart_candle['time'];
    if (isset($forecast_by_time[$chart_time])) continue;
    $seed_guess = $guess_by_time[$chart_time] ?? null;
    if (($chart_candle['forming'] ?? false) && !is_array($seed_guess)) {
        $seed_guess = $display_current_guess;
    }
    if (is_array($seed_guess)) $chart_guess_seeds[$chart_time] = $seed_guess;
}
if ($chart_guess_seeds && $loop_update_allowed) {
    $forecast_by_time = freezeForecastGuesses($guess_history_path, $chart_guess_seeds);
    foreach ($forecast_by_time as $forecast_time => $saved_forecast) {
        $normalized_forecast = normalizeCngnGuess(is_array($saved_forecast) ? $saved_forecast : null);
        if (is_array($normalized_forecast)) $forecast_by_time[$forecast_time] = $normalized_forecast;
    }
}

$chart_timeline = [];
foreach ($chart_hourly_candles as $hour_candle) {
    $chart_time = (string)$hour_candle['time'];
    $hour_rows = is_array($hour_candle['rows'] ?? null) ? $hour_candle['rows'] : [];
    $hour_guess_open = null;
    $hour_guess_high = null;
    $hour_guess_low = null;
    $hour_guess_close = null;
    $hour_guess_forming = false;
    $hour_pairs = [];
    $hour_has_guess = false;

    foreach ($hour_rows as $five_minute_candle) {
        $five_minute_time = (string)$five_minute_candle['time'];
        $hour_guess = $forecast_by_time[$five_minute_time]
            ?? ($guess_by_time[$five_minute_time] ?? null);
        if (($five_minute_candle['forming'] ?? false) && !is_array($hour_guess)) {
            $hour_guess = $display_current_guess;
        }
        if (is_array($hour_guess)
            && (!array_key_exists('change', $hour_guess) || !is_numeric($hour_guess['change']) || abs((float)$hour_guess['change']) <= 0.0)
        ) {
            $hour_guess['change'] = $latent_guess_change;
            $hour_guess['symbol'] = guessPairLabel($hour_guess);
        }
        $guess_mark = $make_guess_candle(
            $five_minute_time,
            (float)$five_minute_candle['open'],
            is_array($hour_guess) ? $hour_guess : null,
            (bool)($five_minute_candle['forming'] ?? false)
        );
        if (!is_array($guess_mark)) continue;

        $hour_has_guess = true;
        $hour_guess_forming = $hour_guess_forming || (bool)($guess_mark['forming'] ?? false);
        if ($hour_guess_open === null) {
            $hour_guess_open = (float)$guess_mark['open'];
            $hour_guess_high = (float)$guess_mark['high'];
            $hour_guess_low = (float)$guess_mark['low'];
            $hour_guess_close = (float)$guess_mark['close'];
        } else {
            $hour_guess_high = max((float)$hour_guess_high, (float)$guess_mark['high']);
            $hour_guess_low = min((float)$hour_guess_low, (float)$guess_mark['low']);
            $hour_guess_close = (float)$guess_mark['close'];
        }

        $guess_pair = (string)($guess_mark['pair'] ?? '');
        if (preg_match('/^[+-]{2}$/', $guess_pair)) $hour_pairs[] = $guess_pair;
    }

    $hour_guess_candle = null;
    $hour_guess_symbol = '%';
    $hour_guess_pair = '%';
    $hour_guess_action = 'NO TRADE';
    $hour_guess_change = 0.0;
    if ($hour_has_guess && $hour_guess_open !== null && $hour_guess_high !== null && $hour_guess_low !== null && $hour_guess_close !== null) {
        $hour_guess_direction = $hour_guess_close > $hour_guess_open
            ? '+'
            : ($hour_guess_close < $hour_guess_open ? '-' : null);
        $hour_unique_pairs = array_values(array_unique($hour_pairs));
        $hour_guess_pair = count($hour_unique_pairs) === 1 ? $hour_unique_pairs[0] : ($hour_guess_direction ?? '%');
        $hour_guess_symbol = $hour_guess_pair;
        $hour_guess_action = $hour_guess_direction === '+'
            ? 'BUY'
            : ($hour_guess_direction === '-'
                ? 'SELL'
                : (preg_match('/^[+-]{2}$/', $hour_guess_pair)
                    ? signalAction($hour_guess_pair)
                    : 'NO TRADE'));
        $hour_guess_change = abs($hour_guess_close - $hour_guess_open);
        $hour_guess_candle = [
            'time' => $chart_time,
            'open' => $hour_guess_open,
            'high' => $hour_guess_high,
            'low' => $hour_guess_low,
            'close' => $hour_guess_close,
            'direction' => $hour_guess_direction,
            'symbol' => $hour_guess_symbol,
            'pair' => $hour_guess_pair,
            'action' => $hour_guess_action,
            'change' => $hour_guess_change,
            'forming' => $hour_guess_forming,
            'combinedMarks' => count($hour_rows),
        ];
    }

    $chart_actual_candle = $hour_candle;
    unset($chart_actual_candle['rows']);
    $chart_timeline[] = [
        'time' => $chart_time,
        'displayTime' => $chart_time,
        'phase' => ($hour_candle['forming'] ?? false) ? 'current' : 'resolved',
        'actual' => $chart_actual_candle,
        'guess' => $hour_guess_candle,
        'guessSymbol' => $hour_guess_symbol,
        'guessPair' => $hour_guess_pair,
        'guessAction' => $hour_guess_action,
        'guessChange' => $hour_guess_change,
        'combinedMarks' => (int)($hour_candle['combinedMarks'] ?? count($hour_rows)),
    ];
}

// The chart renders real candles as hourly OHLC buckets. The forecast side
// must also stay aligned to true hour openings such as 06:00, 07:00, 08:00,
// never to rolling five-minute boundaries like 05:20 or 06:20.
$chart_future_open = $chart_hourly_candles
    ? (float)$chart_hourly_candles[count($chart_hourly_candles) - 1]['close']
    : $future_open;
// Once the current hour is already present on the chart, every future candle
// starts at the next top-of-hour bucket.
$next_chart_hour_epoch = ((int)floor($current_boundary_epoch / 3600) * 3600) + 3600;
for ($forecast_hour = 1; $forecast_hour <= 4; $forecast_hour++) {
    $hour_start_epoch = $next_chart_hour_epoch + (($forecast_hour - 1) * 3600);
    $hour_end_epoch = $hour_start_epoch + 3600;
    $combined_open = $chart_future_open;
    $combined_high = $combined_open;
    $combined_low = $combined_open;
    $combined_close = $combined_open;
    $combined_has_guess = false;
    $combined_pairs = [];
    $rolling_mark_open = $chart_future_open;

    for ($mark_epoch = $hour_start_epoch; $mark_epoch < $hour_end_epoch; $mark_epoch += 300) {
        $mark_time = gmdate('Y-m-d\TH:i:s\Z', $mark_epoch);
        $mark_guess = $forecast_by_time[$mark_time] ?? null;
        $mark_candle = $make_guess_candle(
            $mark_time,
            $rolling_mark_open,
            is_array($mark_guess) ? $mark_guess : null
        );
        if (!is_array($mark_candle)) continue;

        $combined_has_guess = true;
        $mark_pair = (string)($mark_candle['pair'] ?? '');
        if (preg_match('/^[+-]{2}$/', $mark_pair)) $combined_pairs[] = $mark_pair;
        $combined_high = max($combined_high, (float)$mark_candle['high']);
        $combined_low = min($combined_low, (float)$mark_candle['low']);
        $combined_close = (float)$mark_candle['close'];
        $rolling_mark_open = $combined_close;
    }
    if (!$combined_has_guess) break;
    $chart_future_open = $combined_close;

    $chart_future_time = gmdate('Y-m-d\TH:i:s\Z', $hour_start_epoch);
    $combined_direction = $combined_close > $combined_open
        ? '+'
        : ($combined_close < $combined_open ? '-' : null);
    $combined_unique_pairs = array_values(array_unique($combined_pairs));
    $combined_pair = count($combined_unique_pairs) === 1 ? $combined_unique_pairs[0] : null;
    $combined_symbol = $combined_pair ?? ($combined_direction ?? '%');
    $combined_action = $combined_pair !== null
        ? signalAction($combined_pair)
        : ($combined_direction === '+'
            ? 'BUY'
            : ($combined_direction === '-' ? 'SELL' : 'NO TRADE'));
    $chart_future_candle = $combined_has_guess ? [
        'time' => $chart_future_time,
        'open' => $combined_open,
        'high' => $combined_high,
        'low' => $combined_low,
        'close' => $combined_close,
        'direction' => $combined_direction,
        'symbol' => $combined_symbol,
        'pair' => $combined_pair ?? $combined_symbol,
        'action' => $combined_action,
        'change' => abs($combined_close - $combined_open),
        'forming' => false,
        'combinedMarks' => 12,
    ] : null;
    $chart_timeline[] = [
        'time' => $chart_future_time,
        'displayTime' => $chart_future_time,
        'phase' => 'future',
        'actual' => null,
        'guess' => $chart_future_candle,
        'guessSymbol' => $combined_symbol,
        'guessPair' => $combined_pair ?? $combined_symbol,
        'guessAction' => $combined_action,
        'guessChange' => abs($combined_close - $combined_open),
        'combinedMarks' => 12,
    ];
}

// Score every completed, timestamp-locked pair against the real market move.
// The saved pair and actual move stay locked, while the current reversed rule
// after opposite-family remap, ++ and -- buy; +- and -+ sell.
$pair_stats = [
    '--' => ['pair' => '--', 'right' => 0, 'total' => 0, 'percentage' => 0.0],
    '++' => ['pair' => '++', 'right' => 0, 'total' => 0, 'percentage' => 0.0],
    '-+' => ['pair' => '-+', 'right' => 0, 'total' => 0, 'percentage' => 0.0],
    '+-' => ['pair' => '+-', 'right' => 0, 'total' => 0, 'percentage' => 0.0],
];
$resolved_pair_guesses = $guess_by_time;
foreach ($forecast_by_time as $forecast_time => $forecast_guess) {
    $resolved_pair_guesses[$forecast_time] = $forecast_guess;
}
$settled_results_path = $dir . $ticker . '-settled-results.json';
$settled_results_state = loadLocalJsonArray($settled_results_path);
$carry_forward_guess_pool = filterGuessMapSinceTime($resolved_pair_guesses, $carry_forward_reset_time);
$resolved_results_by_time = $loop_update_allowed
    ? freezeResolvedResults(
        $settled_results_path,
        $carry_forward_guess_pool,
        $actual_direction_by_time
    )
    : (is_array($settled_results_state['results'] ?? null) ? $settled_results_state['results'] : []);
foreach ($resolved_results_by_time as $settled_result) {
    if (!is_array($settled_result)) continue;
    $pair = (string)($settled_result['pair'] ?? '');
    if (!isset($pair_stats[$pair])) continue;
    $pair_stats[$pair]['total']++;
    if (($settled_result['right'] ?? false) === true) {
        $pair_stats[$pair]['right']++;
    }
}
foreach ($pair_stats as &$pair_stat) {
    $pair_stat['percentage'] = $pair_stat['total'] > 0
        ? round(($pair_stat['right'] / $pair_stat['total']) * 100, 1)
        : 0.0;
}
unset($pair_stat);
$accuracy_total = count($resolved_results_by_time);
$accuracy_right = 0;
foreach ($resolved_results_by_time as $settled_result) {
    if (($settled_result['right'] ?? false) === true) {
        $accuracy_right++;
    }
}
$internal_agreement = internalAgreementStatsFromResolvedTable($resolved_results_by_time, ONE_HOUR_CANDLE_COUNT);
$pair_rule_state = buildHourlyPairDirectionState(
    $resolved_results_by_time,
    $ticker,
    $current_boundary_epoch
);
setActivePairDirectionMap((array)($pair_rule_state['map'] ?? defaultPairDirectionMap()));
$adaptive_base_pair_map = is_array($stored_pair_rule_state['base_map'] ?? null)
    ? normalizePairDirectionMap($stored_pair_rule_state['base_map'])
    : activePairDirectionMap();
if ($loop_update_allowed) {
    saveLocalJsonArray($pair_rule_state_path, $pair_rule_state);
}
$accuracy_wrong = max(0, $accuracy_total - $accuracy_right);
$accuracy_passed = $accuracy_total;
$accuracy = $accuracy_total > 0
    ? max(0.0, min(100.0, ($accuracy_right / $accuracy_total) * 100.0))
    : 100.0;
$resolved_result_by_time = [];
$resolved_result_by_hour = [];
foreach ($resolved_results_by_time as $resolved_result) {
    if (!is_array($resolved_result)) continue;
    $resolved_time = trim((string)($resolved_result['time'] ?? ''));
    if ($resolved_time !== '') $resolved_result_by_time[$resolved_time] = $resolved_result;
    $resolved_epoch = $resolved_time !== '' ? yahooTimestamp($resolved_time) : null;
    if ($resolved_epoch === null) continue;
    $resolved_hour = (string)floor($resolved_epoch / 3600);
    if (!isset($resolved_result_by_hour[$resolved_hour])) $resolved_result_by_hour[$resolved_hour] = ['right' => 0, 'wrong' => 0];
    if (($resolved_result['right'] ?? null) === true) $resolved_result_by_hour[$resolved_hour]['right']++;
    elseif (($resolved_result['right'] ?? null) === false) $resolved_result_by_hour[$resolved_hour]['wrong']++;
}
foreach ($chart_timeline as &$chart_record) {
    if (!is_array($chart_record)) continue;
    $chart_actual = is_array($chart_record['actual'] ?? null) ? $chart_record['actual'] : null;
    $chart_guess = is_array($chart_record['guess'] ?? null) ? $chart_record['guess'] : null;
    $chart_predicted_direction = (string)($chart_guess['direction'] ?? '');
    if ($chart_actual !== null
        && $chart_guess !== null
        && ($chart_predicted_direction === '+' || $chart_predicted_direction === '-')
        && is_numeric($chart_actual['open'] ?? null)
        && is_numeric($chart_actual['close'] ?? null)
    ) {
        $chart_actual_direction = candleDirection((float)$chart_actual['open'], (float)$chart_actual['close']);
        $chart_record['guessActualDirection'] = (string)$chart_actual_direction;
        $chart_record['guessResult'] = $chart_actual_direction === $chart_predicted_direction
            ? 'RIGHT'
            : 'WRONG';
        continue;
    }
    $resolved_time = trim((string)($chart_record['time'] ?? ''));
    $resolved_result = $resolved_result_by_time[$resolved_time] ?? null;
    if (is_array($resolved_result)) {
        $chart_record['guessResult'] = ($resolved_result['right'] ?? null) === true
            ? 'RIGHT'
            : (($resolved_result['right'] ?? null) === false ? 'WRONG' : 'UNRESOLVED');
        $chart_record['guessActualDirection'] = (string)($resolved_result['actual'] ?? '');
        continue;
    }
    $chart_epoch = $resolved_time !== '' ? yahooTimestamp($resolved_time) : null;
    $hour_stats = $chart_epoch !== null ? ($resolved_result_by_hour[(string)floor($chart_epoch / 3600)] ?? null) : null;
    if (is_array($hour_stats) && ((int)$hour_stats['right'] + (int)$hour_stats['wrong']) > 0) {
        $chart_record['guessResult'] = ((int)$hour_stats['wrong'] > 0 && (int)$hour_stats['right'] > 0)
            ? 'MIXED'
            : ((int)$hour_stats['right'] > 0 ? 'RIGHT' : 'WRONG');
    }
}
unset($chart_record);
$historical_confidence_threshold = 85.0;
$historical_confidence_passed = $accuracy_total >= 20
    && $accuracy >= $historical_confidence_threshold;
$trade_guess = $historical_confidence_passed ? $locked_current_guess : null;
$adaptive_complete_flip = $accuracy_total >= 20 && $accuracy < 45.0;
if ($adaptive_complete_flip) {
    $flipped_map = $adaptive_base_pair_map;
    foreach ($flipped_map as $pair => $direction) {
        $flipped_map[$pair] = $direction === '+' ? '-' : '+';
    }
    setActivePairDirectionMap($flipped_map);
    $pair_rule_state['map'] = activePairDirectionMap();
    if ($loop_update_allowed) saveLocalJsonArray($pair_rule_state_path, $pair_rule_state);
    foreach ($pair_stats as &$pair_stat) {
        $pair_stat['right'] = max(0, (int)$pair_stat['total'] - (int)$pair_stat['right']);
        $pair_stat['percentage'] = $pair_stat['total'] > 0
            ? round(($pair_stat['right'] / $pair_stat['total']) * 100, 1)
            : 0.0;
    }
    unset($pair_stat);
}
$accuracy_class = $accuracy >= 65 ? 'good' : ($accuracy >= 50 ? 'medium' : 'low');
$accuracy_note = $accuracy_total > 0
    ? $accuracy_right . ' / ' . $accuracy_total . ' RIGHT • ' . $accuracy_wrong . ' MISSES • ' . $accuracy_passed . ' PASSED • LOOP 2 CARRY-FORWARD TRUST ' . number_format($accuracy, 2) . '%'
    : '0 MISSES • 0 PASSED • LOOP 2 CARRY-FORWARD TRUST 100.00%';
$compression_state = is_array($pair_rule_state['compression'] ?? null) ? $pair_rule_state['compression'] : [];
$first_loop_compression_rows = [];
foreach (historicalTimedCngnGuesses((string)$rets_so_far[0]) as $first_loop_guess) {
    if (!is_array($first_loop_guess)) continue;
    $first_loop_compression_rows[] = [
        'time' => (string)($first_loop_guess['time'] ?? ''),
        'pair' => (string)($first_loop_guess['pair'] ?? ''),
    ];
}
$first_loop_compression_state = buildEndCompressionState(
    $first_loop_compression_rows,
    $adaptive_base_pair_map,
    ONE_HOUR_CANDLE_COUNT
);
$compression_score = (float)($compression_state['compression_score'] ?? 0.0);
$first_loop_compression_score = (float)($first_loop_compression_state['compression_score'] ?? 0.0);
$primary_compression_score = ($compression_score > 0.0 || $first_loop_compression_score > 0.0)
    ? (($compression_score * 0.50) + ($first_loop_compression_score * 0.50))
    : 0.0;
$compression_score = $primary_compression_score;
$compression_state['compression_score'] = $primary_compression_score;
$compression_entropy = (float)($compression_state['entropy'] ?? 100.0);
$compression_samples = (int)($compression_state['sample_count'] ?? 0);
$compression_tail_streak = (int)($compression_state['tail_streak'] ?? 0);
$compression_phase_count = (int)($compression_state['phase_count'] ?? 0);
$compression_phase_changes = (int)($compression_state['phase_changes'] ?? 0);
$compression_perfect_min = (int)($compression_state['perfect_compression_min_parts'] ?? 0);
$compression_perfect_max = (int)($compression_state['perfect_compression_max_parts'] ?? 0);
$compression_dominant_direction = trim((string)($compression_state['dominant_direction'] ?? 'MIXED'));
$compression_class = $compression_score >= 65.0 ? 'good' : ($compression_score >= 45.0 ? 'medium' : 'low');
$compression_value_label = number_format($primary_compression_score, 1) . '%';
$compression_note = $compression_samples > 0
    ? $compression_dominant_direction . ' • entropy ' . number_format($compression_entropy, 1) . '% • ' . $compression_phase_count . ' RLE phases / ' . $compression_phase_changes . ' changes • 100% runs ' . $compression_perfect_min . '–' . $compression_perfect_max . ' parts'
    : 'Waiting for resolved family samples';
$internal_agreement_percent = (float)($internal_agreement['percent'] ?? 0.0);
$internal_agreement_recent_percent = (float)($internal_agreement['recent_percent'] ?? 0.0);
$internal_agreement_right = (int)($internal_agreement['right'] ?? 0);
$internal_agreement_total = (int)($internal_agreement['total'] ?? 0);
$internal_agreement_recent_right = (int)($internal_agreement['recent_right'] ?? 0);
$internal_agreement_recent_total = (int)($internal_agreement['recent_total'] ?? 0);
$internal_agreement_window = (int)($internal_agreement['window'] ?? ONE_HOUR_CANDLE_COUNT);
$internal_agreement_class = $internal_agreement_recent_percent >= 65.0 ? 'good' : ($internal_agreement_recent_percent >= 50.0 ? 'medium' : 'low');
$internal_agreement_value_label = ($internal_agreement_recent_total > 0 ? number_format($internal_agreement_recent_percent, 1) : '0.0') . '%';
$secondary_compression_score = $internal_agreement_recent_total > 0
    ? max($internal_agreement_recent_percent, 100.0 - $internal_agreement_recent_percent)
    : 0.0;
$combined_compression_score = ($compression_samples > 0 || $internal_agreement_recent_total > 0)
    ? (($compression_score * 0.70) + ($secondary_compression_score * 0.30))
    : 0.0;
$secondary_compression_state = $internal_agreement_recent_percent >= 50.0 ? 'AGREE' : 'DISAGREE';
$compression_note = $compression_samples > 0
    ? $compression_note . ' • secondary ' . $secondary_compression_state . ' ' . number_format($secondary_compression_score, 1) . '% • combined ' . number_format($combined_compression_score, 1) . '%'
    : 'Secondary ' . $secondary_compression_state . ' ' . number_format($secondary_compression_score, 1) . '% • combined ' . number_format($combined_compression_score, 1) . '%';
$compression_note .= ' • first loop ' . number_format($first_loop_compression_score, 1) . '%';
$internal_agreement_note = $internal_agreement_recent_total > 0
    ? 'Diagnostic only • last hour ' . $internal_agreement_recent_right . ' / ' . $internal_agreement_recent_total . ' • all ' . $internal_agreement_right . ' / ' . $internal_agreement_total
    : 'Waiting for left/right agreement samples';
$family_agreement_stats = [
    'BUY|AGREE' => ['family' => 'BUY', 'agreement' => 'AGREE', 'right' => 0, 'total' => 0, 'percentage' => 0.0],
    'BUY|DISAGREE' => ['family' => 'BUY', 'agreement' => 'DISAGREE', 'right' => 0, 'total' => 0, 'percentage' => 0.0],
    'SELL|AGREE' => ['family' => 'SELL', 'agreement' => 'AGREE', 'right' => 0, 'total' => 0, 'percentage' => 0.0],
    'SELL|DISAGREE' => ['family' => 'SELL', 'agreement' => 'DISAGREE', 'right' => 0, 'total' => 0, 'percentage' => 0.0],
];
$active_map_for_confidence = $adaptive_base_pair_map;
foreach ($resolved_results_by_time as $resolved_result) {
    if (!is_array($resolved_result)) continue;
    $pair = trim((string)($resolved_result['pair'] ?? ''));
    if (!preg_match('/^[+-]{2}$/', $pair)) continue;
    $saved_direction = (string)($resolved_result['predicted'] ?? '');
    $historical_direction = ($saved_direction === '+' || $saved_direction === '-')
        ? $saved_direction
        : (string)($active_map_for_confidence[$pair] ?? '');
    if ($historical_direction !== '+' && $historical_direction !== '-') continue;
    $family = $historical_direction === '+' ? 'BUY' : 'SELL';
    $agreement = $pair[0] === $pair[1] ? 'AGREE' : 'DISAGREE';
    $key = $family . '|' . $agreement;
    if (!isset($family_agreement_stats[$key])) continue;
    $family_agreement_stats[$key]['total']++;
    $actual = (string)($resolved_result['actual'] ?? ($resolved_result['actual_direction'] ?? ''));
    if (($family === 'BUY' && $actual === '+') || ($family === 'SELL' && $actual === '-')) {
        $family_agreement_stats[$key]['right']++;
    }
}
foreach ($family_agreement_stats as &$family_stat) {
    $family_stat['percentage'] = $family_stat['total'] > 0
        ? round(($family_stat['right'] / $family_stat['total']) * 100, 1)
        : 0.0;
}
unset($family_stat);
$agreement_branch_flips = [];
$agreement_branch_minimum_samples = 3;
foreach ($family_agreement_stats as $branch_key => $branch_stat) {
    if ((int)($branch_stat['total'] ?? 0) >= $agreement_branch_minimum_samples
        && (float)($branch_stat['percentage'] ?? 0.0) < 45.0
    ) {
        $agreement_branch_flips[] = $branch_key;
    }
}
$execution_pair = guessPairLabel($display_current_guess);
$execution_base_direction = (string)($adaptive_base_pair_map[$execution_pair] ?? '');
$execution_family = $execution_base_direction === '+'
    ? 'BUY'
    : ($execution_base_direction === '-' ? 'SELL' : 'NO TRADE');
$execution_agreement = $internal_agreement_recent_total > 0
    ? ($internal_agreement_recent_percent < 50.0 ? 'DISAGREE' : 'AGREE')
    : 'DISAGREE';
$execution_branch_key = $execution_family . '|' . $execution_agreement;
$execution_inversion_active = $adaptive_complete_flip
    || in_array($execution_branch_key, $agreement_branch_flips, true);
$effective_execution_guess = null;
if ($historical_confidence_passed || $execution_inversion_active) {
    $effective_execution_guess = $locked_current_guess;
    if ($execution_inversion_active && is_array($effective_execution_guess)) {
        $stored_execution_direction = (string)($effective_execution_guess['direction'] ?? '');
        if ($stored_execution_direction === '+' || $stored_execution_direction === '-') {
            $effective_execution_guess['direction'] = $stored_execution_direction === '+' ? '-' : '+';
            $effective_execution_guess['action'] = $effective_execution_guess['direction'] === '+' ? 'BUY' : 'SELL';
        }
    }
}
$trade_guess = $effective_execution_guess;
$quarter_regime_stats = buildQuarterRegimeStats($resolved_results_by_time, $adaptive_base_pair_map);
$current_pair_for_quarter = guessPairLabel($display_current_guess);
$current_base_direction_for_quarter = (string)($adaptive_base_pair_map[$current_pair_for_quarter] ?? '');
$current_family_for_quarter = $current_base_direction_for_quarter === '+'
    ? 'BUY'
    : ($current_base_direction_for_quarter === '-' ? 'SELL' : 'NO TRADE');
$current_agreement_for_quarter = $internal_agreement_recent_percent >= 50.0 ? 'AGREE' : 'DISAGREE';
$current_quarter = (string)date('Y', $current_boundary_epoch) . '-Q' . (string)ceil((int)date('n', $current_boundary_epoch) / 3);
$current_quarter_regime_key = $current_quarter . '|' . $current_family_for_quarter . '|' . $current_agreement_for_quarter;
$current_quarter_regime = $quarter_regime_stats[$current_quarter_regime_key] ?? [
    'quarter' => $current_quarter,
    'family' => $current_family_for_quarter,
    'agreement' => $current_agreement_for_quarter,
    'right' => 0,
    'wrong' => 0,
    'total' => 0,
    'percentage' => 0.0,
];
$quarter_regime_inverted = (int)($current_quarter_regime['total'] ?? 0) >= 3
    && (float)($current_quarter_regime['percentage'] ?? 0.0) < 45.0;
$quarter_regime_trade_blocked = $quarter_regime_inverted;
$latest_candle_for_gate = $candle_chart ? $candle_chart[count($candle_chart) - 1] : null;
$current_candle_is_down = is_array($latest_candle_for_gate)
    && is_numeric($latest_candle_for_gate['open'] ?? null)
    && is_numeric($latest_candle_for_gate['close'] ?? null)
    && (float)$latest_candle_for_gate['close'] < (float)$latest_candle_for_gate['open'];
$quarter_regime_buy_allowed = $current_family_for_quarter === 'BUY'
    && $current_candle_is_down
    && !$quarter_regime_trade_blocked
    && (int)($current_quarter_regime['total'] ?? 0) >= 3
    && (float)($current_quarter_regime['percentage'] ?? 0.0) >= 50.0;
$quarter_buy_gate_blocked = false;
if (is_array($trade_guess)
    && (guessStoredAction($trade_guess) === 'BUY' || guessStoredAction($trade_guess) === 'SELL')
    && ($quarter_regime_trade_blocked || (guessStoredAction($trade_guess) === 'BUY' && !$quarter_regime_buy_allowed))
) {
    $trade_guess = null;
    $quarter_buy_gate_blocked = true;
}
$execution_current_guess = $trade_guess;
if (is_array($execution_current_guess)
    && (guessStoredAction($execution_current_guess) === 'BUY' || guessStoredAction($execution_current_guess) === 'SELL')
    && ($quarter_regime_trade_blocked || (guessStoredAction($execution_current_guess) === 'BUY' && !$quarter_regime_buy_allowed))
) {
    $execution_current_guess = null;
    $quarter_buy_gate_blocked = true;
}
$current_pair_for_confidence = guessPairLabel($display_current_guess);
$current_base_direction_for_confidence = (string)($adaptive_base_pair_map[$current_pair_for_confidence] ?? '');
$current_family_for_confidence = $current_base_direction_for_confidence === '+'
    ? 'BUY'
    : ($current_base_direction_for_confidence === '-' ? 'SELL' : 'NO TRADE');
$current_agreement_for_confidence = $internal_agreement_recent_total > 0
    ? ($internal_agreement_recent_percent < 50.0 ? 'DISAGREE' : 'AGREE')
    : 'DISAGREE';
$current_family_confidence_key = $current_family_for_confidence . '|' . $current_agreement_for_confidence;
$current_family_confidence = $family_agreement_stats[$current_family_confidence_key] ?? ['right' => 0, 'total' => 0, 'percentage' => 0.0];
$current_family_confidence_label = $current_family_confidence['total'] > 0
    ? number_format((float)$current_family_confidence['percentage'], 1) . '%'
    : '—';
$current_family_confidence_class = $current_family_confidence['percentage'] >= 65.0
    ? 'good'
    : ($current_family_confidence['percentage'] >= 50.0 ? 'medium' : 'low');
$sneak_profile = buildSneakProfile($display_current_guess, $compression_state, $internal_agreement);

$attack_trade_amount = $first_trade_amount * (float)($attack_profile['factor'] ?? 1.0);
$hourly_bell_curve_plan = buildHourlyBellCurvePlan(
    $forecast_by_time,
    $early_boundary_key,
    $locked_current_guess,
    $attack_trade_amount,
    $buy_multiplier,
    $sell_multiplier,
    $trust_percent,
    ONE_HOUR_CANDLE_COUNT
);
$formula_execution_action = guessStoredAction($trade_guess);
$hourly_bell_curve_plan = compressHourlyPlanToSingleTrade(
    $hourly_bell_curve_plan,
    $hour_audit_execution_winner,
    $formula_execution_action,
    max((float)($hour_audit_guess['percent'] ?? 0.0), 90.0),
    $accuracy,
    $trust_percent
);
$regime_five_minute_steps = max(1, (int)($current_phase_status['steps_in'] ?? 1));
$regime_step_count = max(1, min(12, (int)ceil($regime_five_minute_steps / 12)));
$regime_base_commitment = max(
    0.0,
    ($average_change > 0.0 ? $average_change : $latent_guess_change) * $trade_capture_ratio
);
$regime_multiplier = $formula_execution_action === 'SELL' ? $sell_multiplier : $buy_multiplier;
$regime_requested_amount = ($formula_execution_action === 'BUY' || $formula_execution_action === 'SELL')
    ? round($regime_base_commitment * $regime_step_count * max(0.10, (float)$regime_multiplier), 8)
    : 0.0;
if (($formula_execution_action === 'BUY' || $formula_execution_action === 'SELL') && $regime_requested_amount > 0.0) {
    $hourly_bell_curve_plan['single_trade_mode'] = true;
    $hourly_bell_curve_plan['single_trade_action'] = $formula_execution_action;
    $hourly_bell_curve_plan['single_trade_amount'] = $regime_requested_amount;
    $hourly_bell_curve_plan['single_trade_multiplier'] = max(0.10, (float)$regime_multiplier);
    $hourly_bell_curve_plan['regime_step_count'] = $regime_step_count;
    $hourly_bell_curve_plan['regime_base_commitment'] = round($regime_base_commitment, 8);
    $hourly_bell_curve_plan['regime_requested_amount'] = $regime_requested_amount;
    $hourly_bell_curve_plan['dominant_action'] = $formula_execution_action;
    $hourly_bell_curve_plan['actionable_slots'] = 1;
    $hourly_bell_curve_plan['total_requested_amount'] = $regime_requested_amount;
    $hourly_bell_curve_plan['total_buy_requested'] = $formula_execution_action === 'BUY' ? $regime_requested_amount : 0.0;
    $hourly_bell_curve_plan['total_sell_requested'] = $formula_execution_action === 'SELL' ? $regime_requested_amount : 0.0;
    $hourly_bell_curve_plan['buy_calls'] = $formula_execution_action === 'BUY' ? 1 : 0;
    $hourly_bell_curve_plan['sell_calls'] = $formula_execution_action === 'SELL' ? 1 : 0;
    $hourly_bell_curve_plan['slots'] = [[
        'slot_index' => 0,
        'action' => $formula_execution_action,
        'amount' => $regime_requested_amount,
        'confidence' => $hourly_bell_curve_plan['single_trade_confidence'] ?? $accuracy,
        'reason' => 'FULL REGIME RUN x' . $regime_step_count,
    ]];
}
$regime_plan_active = isset($hourly_bell_curve_plan['regime_requested_amount']);
$phase_step_multiplier = 1;
if (($hourly_bell_curve_plan['single_trade_mode'] ?? false) === true
    && strtoupper(trim((string)($hourly_bell_curve_plan['single_trade_action'] ?? ''))) === strtoupper(trim((string)($current_phase_status['action'] ?? '')))
) {
    $phase_step_multiplier = max(1, (int)($current_phase_status['steps_in'] ?? 1));
    if (isset($hourly_bell_curve_plan['regime_requested_amount'])) {
        $phase_step_multiplier = (int)($hourly_bell_curve_plan['regime_step_count'] ?? $phase_step_multiplier);
    } elseif ($phase_step_multiplier > 1) {
        $scaledPhaseAmount = (float)($hourly_bell_curve_plan['single_trade_amount'] ?? 0.0) * $phase_step_multiplier;
        $hourly_bell_curve_plan['single_trade_amount'] = round($scaledPhaseAmount, 8);
        $hourly_bell_curve_plan['single_trade_multiplier'] = round(
            (float)($hourly_bell_curve_plan['single_trade_multiplier'] ?? 1.0) * $phase_step_multiplier,
            4
        );
        $hourly_bell_curve_plan['phase_step_multiplier'] = $phase_step_multiplier;
        $hourly_bell_curve_plan['total_requested_amount'] = round($scaledPhaseAmount, 8);
        $hourly_bell_curve_plan['total_buy_requested'] = $formula_execution_action === 'BUY' ? round($scaledPhaseAmount, 8) : 0.0;
        $hourly_bell_curve_plan['total_sell_requested'] = $formula_execution_action === 'SELL' ? round($scaledPhaseAmount, 8) : 0.0;
        if (!empty($hourly_bell_curve_plan['slots'][0])) {
            $hourly_bell_curve_plan['slots'][0]['amount'] = round($scaledPhaseAmount, 8);
        }
    }
}
if ($regime_plan_active) {
    $phase_step_multiplier = $regime_step_count;
    $hourly_bell_curve_plan['phase_step_multiplier'] = $regime_step_count;
}

// Apply sequence trading only after the resolved result ledger is available.
$sequence_guesses = $guess_by_time;
$sequence_guesses[$early_boundary_key] = is_array($trade_guess) ? $trade_guess : [];
$sequence_signal = $historical_confidence_passed
    ? sequenceTradeSignal($sequence_guesses, $resolved_results_by_time, null, $trust_percent)
    : [
        'enabled' => false,
        'action' => 'NO TRADE',
        'run_length' => 0,
        'trade_count' => 0,
        'pair' => '',
        'run_pair' => '',
        'accuracy' => 0.0,
        'samples' => 0,
    ];
if ($regime_plan_active && ($formula_execution_action === 'BUY' || $formula_execution_action === 'SELL')) {
    $sequence_signal = [
        'enabled' => true,
        'regime_active' => true,
        'action' => $formula_execution_action,
        'run_length' => max(0, $regime_step_count - 1),
        'trade_count' => $regime_step_count,
        'pair' => guessPairLabel($trade_guess),
        'run_pair' => guessPairLabel($trade_guess),
        'accuracy' => $historical_confidence_passed ? $accuracy : (100.0 - $accuracy),
        'samples' => $accuracy_total,
    ];
}
$paper_break_replay_state = buildModelPaperTraderState(
    $chart_source_candles,
    $guess_by_time,
    $execution_current_guess,
    $current_price,
    $latest_market_time,
    $paper_wallet_bootstrap,
    $attack_trade_amount,
    $sell_multiplier,
    $trust_percent,
    $resolved_results_by_time,
    $sneak_profile
);
$stored_model_wallet_state = loadLocalJsonArray($model_wallet_state_path);
$paper_break_state = $loop_update_allowed
    ? updateBoundaryModelTraderState(
        $model_wallet_state_path,
        $early_boundary_key,
        (float)$current_price,
        $execution_current_guess,
        $paper_wallet_bootstrap,
        $sequence_signal,
        $hourly_bell_curve_plan,
        $attack_trade_amount,
        $sell_multiplier,
        $sneak_profile,
        $regime_plan_active
    )
    : (is_array($stored_model_wallet_state) && !empty($stored_model_wallet_state)
        ? $stored_model_wallet_state
        : $paper_break_replay_state);
$paper_break_state = normalizeTraderDisplayState($paper_break_state);
$paper_break_state['attack_active'] = (($attack_profile['active'] ?? false) === true);
$paper_break_state['attack_factor'] = (float)($attack_profile['factor'] ?? 1.0);
$paper_break_state['attack_label'] = (string)($attack_profile['label'] ?? 'BASE');
$paper_break_state['attack_reason'] = (string)($attack_profile['reason'] ?? '');
$paper_break_state['attack_score'] = (float)($attack_profile['score'] ?? 0.0);
$paper_break_action = (string)($paper_break_state['display_action'] ?? 'WATCHING');
$paper_break_class = (float)($paper_break_state['sim_net_move'] ?? 0.0) > 0.0
    ? 'good'
    : ((float)($paper_break_state['sim_net_move'] ?? 0.0) < 0.0 ? 'low' : 'medium');

$timeline_price_values = [];
foreach ($chart_timeline as $record) {
    foreach (['actual', 'guess'] as $kind) {
        if (!is_array($record[$kind] ?? null)) continue;
        foreach (['high', 'low', 'open', 'close'] as $field) {
            $value = (float)($record[$kind][$field] ?? 0.0);
            if (is_finite($value) && $value > 0.0) $timeline_price_values[] = $value;
        }
    }
}
if (is_finite($current_price) && $current_price > 0.0) $timeline_price_values[] = $current_price;
$raw_chart_min = $timeline_price_values ? min($timeline_price_values) : 0.0;
$raw_chart_max = $timeline_price_values ? max($timeline_price_values) : 1.0;
$raw_chart_span = max(.00000001, $raw_chart_max - $raw_chart_min);
$rough_step = $raw_chart_span / 5;
$step_power = pow(10, floor(log10($rough_step)));
$step_ratio = $rough_step / $step_power;
$nice_multiplier = $step_ratio <= 1 ? 1 : ($step_ratio <= 2 ? 2 : ($step_ratio <= 5 ? 5 : 10));
$chart_price_step = $nice_multiplier * $step_power;
$chart_price_min = floor($raw_chart_min / $chart_price_step) * $chart_price_step;
$chart_price_max = ceil($raw_chart_max / $chart_price_step) * $chart_price_step;
if ($chart_price_max <= $chart_price_min) $chart_price_max = $chart_price_min + $chart_price_step;

$timeline_elapsed = array_values(array_filter($timeline, static fn(array $record): bool => $record['phase'] === 'elapsed'));
$timeline_current = array_values(array_filter($timeline, static fn(array $record): bool => $record['phase'] === 'current'));
$timeline_future = array_values(array_filter($timeline, static fn(array $record): bool => $record['phase'] === 'future'));
$timeline_future_ready = count(array_filter($timeline_future, static fn(array $record): bool => is_array($record['guess'] ?? null)));
$visible_timeline_future = array_slice($timeline_future, 0, min(VISIBLE_FUTURE_GUESSES, TRADE_ANALYSIS_HORIZON));
$display_timeline = array_merge(array_reverse($visible_timeline_future), $timeline_current, array_reverse($timeline_elapsed));
$display_timeline = centeredTimelineWindow(
    $display_timeline,
    'BUY',
    gmdate('Y-m-d\TH:i:s\Z', $current_boundary_epoch),
    16
);
$current_guess_pair_label = guessPairLabel($display_current_guess);
$current_guess_action = guessStoredAction(is_array($trade_guess) ? $trade_guess : null);
$current_guess_action_class = $current_guess_action === 'BUY'
    ? 'good'
    : ($current_guess_action === 'SELL' ? 'low' : 'medium');
$current_guess_note = $current_guess_pair_label === '%'
    ? 'Model signal unavailable for the current boundary'
    : 'MODEL ' . $current_guess_pair_label . ' • ' . $current_guess_action . ' • unresolved';
$current_price_note = '1H ' . $current_price_direction
    . ' ' . ($hour_price_percentage >= 0.0 ? '+' : '') . number_format($hour_price_percentage, 2) . '%'
    . ' • MOVE ' . ($hour_price_change >= 0.0 ? '+' : '-') . '$' . number_format(abs($hour_price_change), 2)
    . ($hour_reference_price > 0.0 ? ' • FROM $' . number_format($hour_reference_price, 2) : '');
$paper_break_position = (($paper_break_state['position'] ?? 'flat') === 'long') ? 'LONG' : 'FLAT';
$paper_break_equity = (float)($paper_break_state['equity_value'] ?? 0.0);
$paper_break_cash_left = (float)($paper_break_state['cash_left'] ?? 0.0);
$paper_break_holding_value = (float)($paper_break_state['holding_value'] ?? 0.0);
$paper_break_held_units = (float)($paper_break_state['asset_units'] ?? 0.0);
$paper_break_first_buy = (float)($paper_break_state['first_buy_amount'] ?? 0.0);
$paper_break_right_percent = (float)($paper_break_state['right_percent'] ?? 0.0);
$paper_break_bought_units = (float)($paper_break_state['total_bought_units'] ?? 0.0);
$paper_break_bought_amount = (float)($paper_break_state['total_bought_amount'] ?? 0.0);
$paper_break_sold_units = (float)($paper_break_state['total_sold_units'] ?? 0.0);
$paper_break_sold_amount = (float)($paper_break_state['total_sold_amount'] ?? 0.0);
$paper_break_net_pnl = (float)($paper_break_state['net_pnl'] ?? 0.0);
$paper_break_realized_move = (float)($paper_break_state['realized_move'] ?? 0.0);
$paper_break_open_pnl = (float)($paper_break_state['open_pnl'] ?? 0.0);
$paper_break_sim_net_move = $paper_break_net_pnl;
$paper_break_last_trade_result = (string)($paper_break_state['last_trade_result'] ?? '');
$paper_break_last_trade_pnl = (float)($paper_break_state['last_trade_pnl'] ?? 0.0);
$paper_break_bell_curve_active = (($paper_break_state['hourly_bell_curve_active'] ?? false) === true);
$paper_break_bell_curve_action = strtoupper(trim((string)($paper_break_state['hourly_bell_curve_action'] ?? 'NO TRADE')));
$paper_break_bell_curve_trust = (float)($paper_break_state['hourly_bell_curve_effective_trust'] ?? 0.0);
$paper_break_bell_curve_slots = (int)($paper_break_state['hourly_bell_curve_slots'] ?? 0);
$paper_break_bell_curve_buy_calls = (int)($paper_break_state['hourly_bell_curve_buy_calls'] ?? 0);
$paper_break_bell_curve_sell_calls = (int)($paper_break_state['hourly_bell_curve_sell_calls'] ?? 0);
$paper_break_bell_curve_total_requested = (float)($paper_break_state['hourly_bell_curve_total_requested'] ?? 0.0);
$paper_break_events_by_time = is_array($paper_break_replay_state['events_by_time'] ?? null)
    ? $paper_break_replay_state['events_by_time']
    : [];
$paper_break_asset_code = $market_type === 'crypto'
    ? preg_replace('/-USD$/i', '', $ticker)
    : $ticker;
$paper_break_asset_code = is_string($paper_break_asset_code) ? trim($paper_break_asset_code) : '';
$paper_break_asset_left_label = $paper_break_asset_code !== ''
    ? $paper_break_asset_code . ' LEFT'
    : ($market_type === 'crypto' ? 'COIN LEFT' : 'UNITS LEFT');
$paper_break_asset_bought_label = $paper_break_asset_code !== ''
    ? $paper_break_asset_code . ' BOUGHT'
    : ($market_type === 'crypto' ? 'COIN BOUGHT' : 'UNITS BOUGHT');
$paper_break_asset_sold_label = $paper_break_asset_code !== ''
    ? $paper_break_asset_code . ' SOLD'
    : ($market_type === 'crypto' ? 'COIN SOLD' : 'UNITS SOLD');
$paper_break_asset_left_amount = $market_type === 'crypto'
    ? number_format($paper_break_held_units, 8, '.', '')
    : number_format($paper_break_held_units, 4, '.', '');
$paper_break_asset_bought_amount = $market_type === 'crypto'
    ? number_format($paper_break_bought_units, 8, '.', '')
    : number_format($paper_break_bought_units, 4, '.', '');
$paper_break_asset_sold_amount = $market_type === 'crypto'
    ? number_format($paper_break_sold_units, 8, '.', '')
    : number_format($paper_break_sold_units, 4, '.', '');
$display_commitment_amount = displayCommitmentAmountForAction(
    $current_guess_action,
    $hourly_bell_curve_plan,
    $attack_trade_amount,
    $sell_multiplier
);
$phase_sizing_action = strtoupper(trim((string)$current_guess_action));
$phase_available_amount = $phase_sizing_action === 'SELL'
    ? (float)$paper_break_held_units * (float)$current_price
    : (float)$paper_break_cash_left;
$current_phase_sizing = canonicalTradeSizing($phase_sizing_action, $display_commitment_amount, $phase_available_amount);
$current_phase_sizing['phase_step_multiplier'] = $phase_step_multiplier;
if ($quarter_buy_gate_blocked && $phase_sizing_action === 'BUY') {
    $current_phase_sizing['eligible'] = false;
    $current_phase_sizing['executable_amount'] = 0.0;
    $current_phase_sizing['shortfall'] = $current_phase_sizing['requested_amount'];
}
$paper_break_value_label = 'SIM NET MOVE '
    . ($paper_break_sim_net_move >= 0.0 ? '+' : '-')
    . '$' . number_format(abs($paper_break_sim_net_move), 2);
$paper_break_note = 'POT $' . number_format($paper_break_equity, 2)
    . ' • ' . $paper_break_asset_left_label . ' ' . $paper_break_asset_left_amount
    . ' • ' . $paper_break_asset_bought_label . ' ' . $paper_break_asset_bought_amount . ' ($' . number_format($paper_break_bought_amount, 2) . ')'
    . ' • ' . $paper_break_asset_sold_label . ' ' . $paper_break_asset_sold_amount . ' ($' . number_format($paper_break_sold_amount, 2) . ')'
    . ' • HOLDING $' . number_format($paper_break_holding_value, 2)
    . ' • LEFT $' . number_format($paper_break_cash_left, 2)
    . ' • POSITION ' . $paper_break_position
    . ' • SIGNAL ' . $paper_break_action
    . ' • OPEN ' . ($paper_break_position === 'LONG'
        ? (($paper_break_open_pnl >= 0.0 ? '+' : '-') . '$' . number_format(abs($paper_break_open_pnl), 2))
        : '—')
    . ' • LAST ' . ($paper_break_last_trade_result !== '' ? $paper_break_last_trade_result : '—')
    . ' ' . ($paper_break_last_trade_result !== '' ? (($paper_break_last_trade_pnl >= 0.0 ? '+' : '-') . '$' . number_format(abs($paper_break_last_trade_pnl), 2)) : '—')
    . ' • W/L ' . (int)($paper_break_state['wins'] ?? 0) . '/' . (int)($paper_break_state['losses'] ?? 0)
    . ' • PAPER ONLY';
$chart_actual_count = count($chart_hourly_candles);
$chart_forecast_count = max(0, count($chart_timeline) - $chart_actual_count);

$visible_rows_html = '<tr><td>Time</td><td>Model Signal</td><td>Observed Result</td></tr>';
foreach ($display_timeline as $record) {
    $display_time = (string)($record['displayTime'] ?? $record['time']);
    $epoch = yahooTimestamp($display_time);
    $timeCell = $epoch === null
        ? '<td>' . htmlspecialchars($display_time) . '</td>'
        : '<td data-epoch="' . ($epoch * 1000) . '">' . gmdate('m/d H:i', $epoch) . '</td>';
    $symbol = (string)($record['guessSymbol'] ?? '%');
    $frozenResult = $record['phase'] === 'elapsed'
        ? ($resolved_results_by_time[$record['time']] ?? null)
        : null;
    if (is_array($frozenResult)) {
        $symbol = (string)($frozenResult['pair'] ?? $symbol);
    }
    $lockedPredicted = is_array($frozenResult) ? ($frozenResult['predicted'] ?? null) : null;
    $predictedDirection = ($lockedPredicted === '+' || $lockedPredicted === '-')
        ? $lockedPredicted
        : (newGuessDirectionFromPair($symbol) ?? '');
    $change = (float)($record['guessChange'] ?? 0.0);
    $explicitAction = strtoupper(trim((string)($record['guessAction'] ?? '')));
    $action = $predictedDirection === '+'
        ? 'BUY'
        : ($predictedDirection === '-'
            ? 'SELL'
            : (($explicitAction === 'BUY' || $explicitAction === 'SELL') ? $explicitAction : 'NO TRADE'));
    $targetPnl = targetMoveForAction($action, $change);
    $commitmentAmount = 0.0;
    if (is_array($tradeEvent ?? null) && is_numeric($tradeEvent['amount'] ?? null)) {
        $commitmentAmount = max(0.0, (float)$tradeEvent['amount']);
    } else {
        $commitmentAmount = displayCommitmentAmountForAction(
            $action,
            $hourly_bell_curve_plan,
            $attack_trade_amount,
            $sell_multiplier
        );
    }
    $estimatedProfit = $action === 'NO TRADE' ? 0.0 : abs($targetPnl) * 0.90;
    // The execution unit is one decision per hour; do not display a
    // fractional sneak multiplier or an optimistic dollar estimate here.
    $estimatedProfitLabel = $action === 'NO TRADE'
        ? ''
        : ' · 1 TRADE/HOUR';
    $guessCell = '<td>' . htmlspecialchars($action)
        . ($action === 'NO TRADE' ? '' : ' · COMMIT $' . htmlspecialchars(number_format($commitmentAmount, 2, '.', ',')))
        . '</td>';
    $class = '';
    $resultCell = '<td>HYPOTHETICAL</td>';
    if ($record['phase'] === 'current') {
        $class = ' class="current-guess-row"';
        $tradeEvent = is_array($paper_break_events_by_time[$record['time']] ?? null)
            ? $paper_break_events_by_time[$record['time']]
            : null;
        if (is_array($tradeEvent) && (($tradeEvent['executed'] ?? false) === true)) {
            $eventLabel = formatPhaseRealizationLabel((string)($tradeEvent['label'] ?? $action));
            $eventAmount = is_numeric($tradeEvent['amount'] ?? null) ? (float)$tradeEvent['amount'] : 0.0;
            $eventRequested = is_numeric($tradeEvent['requested_amount'] ?? null) ? (float)$tradeEvent['requested_amount'] : $commitmentAmount;
            $eventShortfall = is_numeric($tradeEvent['shortfall'] ?? null) ? (float)$tradeEvent['shortfall'] : max(0.0, $eventRequested - $eventAmount);
            $amountLabel = $eventAmount > 0.0
                ? ' · REQUESTED $' . number_format($eventRequested, 2) . ' · ' . ($action === 'SELL' ? 'SOLD' : 'BOUGHT') . ' $' . number_format($eventAmount, 2)
                    . ($eventShortfall > 0.00000001 ? ' · SHORT $' . number_format($eventShortfall, 2) : '')
                : '';
            $resultCell = '<td class="result-neutral-cell">' . htmlspecialchars('CURRENT · EXECUTED ' . $eventLabel . $amountLabel . ' · SETTLING' . $estimatedProfitLabel) . '</td>';
        } else {
            $resultCell = '<td>' . ($symbol === '%' ? 'UNKNOWN' : 'CURRENT · UNRESOLVED' . htmlspecialchars($estimatedProfitLabel)) . '</td>';
        }
    } elseif ($record['phase'] === 'future') {
        $class = ' class="forward-guess-row"';
        $resultCell = '<td>' . htmlspecialchars('HYPOTHETICAL' . $estimatedProfitLabel) . '</td>';
    } else {
        $tradeEvent = is_array($paper_break_events_by_time[$record['time']] ?? null)
            ? $paper_break_events_by_time[$record['time']]
            : null;
        if (is_array($tradeEvent)) {
            $eventLabel = formatPhaseRealizationLabel((string)($tradeEvent['label'] ?? 'NO TRADE'));
            $eventClass = (string)($tradeEvent['class'] ?? 'result-neutral-cell');
            $eventPnl = $tradeEvent['realized_pnl'] ?? null;
            $eventAmount = is_numeric($tradeEvent['amount'] ?? null) ? (float)$tradeEvent['amount'] : 0.0;
            $eventRequested = is_numeric($tradeEvent['requested_amount'] ?? null) ? (float)$tradeEvent['requested_amount'] : $commitmentAmount;
            $eventShortfall = is_numeric($tradeEvent['shortfall'] ?? null) ? (float)$tradeEvent['shortfall'] : max(0.0, $eventRequested - $eventAmount);
            $amountLabel = $eventAmount > 0.0
                ? ' REQUESTED $' . number_format($eventRequested, 2) . ' · ' . ($action === 'SELL' ? 'SOLD' : 'BOUGHT') . ' $' . number_format($eventAmount, 2)
                    . ($eventShortfall > 0.00000001 ? ' · SHORT $' . number_format($eventShortfall, 2) : '')
                : '';
            $suffix = is_numeric($eventPnl) ? $amountLabel . ' P&L ' . formatSignedMoney((float)$eventPnl, 2) : $amountLabel;
            $resultCell = '<td class="' . htmlspecialchars($eventClass) . '">'
                . htmlspecialchars($eventLabel . $suffix . $estimatedProfitLabel) . '</td>';
        } elseif (!is_array($frozenResult)) {
            $resultCell = '<td>' . htmlspecialchars('UNRESOLVED · SETTLING' . $estimatedProfitLabel) . '</td>';
        } else {
            $actualSymbol = (string)($frozenResult['actual'] ?? '');
            $outcome = resolvedOutcomeMeta($predictedDirection, $actualSymbol);
            $actualPnl = tradePnlForAction($action, is_array($record['actual'] ?? null) ? $record['actual'] : null);
            $resultCell = '<td class="' . htmlspecialchars((string)$outcome['class']) . '">'
                . htmlspecialchars((string)$outcome['label'] . ' REAL ' . formatSignedMoney($actualPnl, 4) . $estimatedProfitLabel) . '</td>';
        }
    }
    $visible_rows_html .= '<tr' . $class . '>' . $timeCell . $guessCell . $resultCell . '</tr>';
}

// The wallet card now follows the live persistent five-minute trader state,
// so its net move comes from that state instead of a fresh historical replay.
$paper_profit = $paper_break_net_pnl;
$paper_trades = is_array($paper_break_state['trades'] ?? null)
    ? count($paper_break_state['trades'])
    : 0;
$paper_break_state['sim_net_move'] = (float)$paper_profit;
$paper_break_class = $paper_profit > 0.0 ? 'good' : ($paper_profit < 0.0 ? 'low' : 'medium');
$paper_break_value_label = 'SIM NET MOVE '
    . ($paper_profit >= 0.0 ? '+' : '-')
    . '$' . number_format(abs($paper_profit), 2);
$paper_break_note = $paper_break_position
    . ' • ' . $paper_break_action
    . ' • OPEN ' . ($paper_break_position === 'LONG'
        ? (($paper_break_open_pnl >= 0.0 ? '+' : '-') . '$' . number_format(abs($paper_break_open_pnl), 2))
        : '—')
    . ' • LAST ' . ($paper_break_last_trade_result !== '' ? $paper_break_last_trade_result : '—')
    . ' ' . ($paper_break_last_trade_result !== '' ? (($paper_break_last_trade_pnl >= 0.0 ? '+' : '-') . '$' . number_format(abs($paper_break_last_trade_pnl), 2)) : '—')
    . ' • W/L ' . (int)($paper_break_state['wins'] ?? 0) . '/' . (int)($paper_break_state['losses'] ?? 0)
    . ' • Paper only';
$updated_at = file_exists($file_path) ? date('M j, Y g:i A', filemtime($file_path)) : 'Unavailable';
$market_source_chip_label = $current_price_source === 'YAHOO'
    ? 'Yahoo • live'
    : ($current_price_source === 'CRON-CACHE' ? 'Cron cache' : 'Observed feed • 30s');
$market_feed_label = $current_price_source === 'YAHOO'
    ? 'Yahoo Finance'
    : ($current_price_source === 'CRON-CACHE' ? 'Cron JSON' : 'Observed feed 30s');
$market_reference_label = $hour_reference_price > 0.0 ? '$' . number_format($hour_reference_price, 2) : '—';
$market_update_note = trim(
    ($data_note !== '' ? $data_note : 'Using the current 30-second market feed.')
    . ($updated_at !== 'Unavailable' ? ' • Feed file ' . $updated_at : '')
);
$cron_summary_written_at = trim((string)($cron_summary['writtenAt'] ?? ''));
if ($cron_summary_written_at !== '') {
    $future_ready_note = is_numeric($cron_summary['futureGuessCount'] ?? null) && is_numeric($cron_summary['futureGuessTarget'] ?? null)
        ? ' • futures ' . (int)$cron_summary['futureGuessCount'] . '/' . (int)$cron_summary['futureGuessTarget']
        : '';
    $market_update_note .= ' • Cron cache ' . $cron_summary_written_at . $future_ready_note;
}
if ($wallet_reset_done || $analysis_requested) {
    $market_update_note = ($analysis_requested ? 'New analysis started' : 'Wallet reset')
        . ' with $5,000 cash and $5,000 in '
        . ($paper_break_asset_code !== '' ? $paper_break_asset_code : 'the asset')
        . ($market_update_note !== '' ? ' • ' . $market_update_note : '');
} elseif ($readonly_browser_mode) {
    $market_update_note = trim($scheduler_cache_note . ($market_update_note !== '' ? ' • ' . $market_update_note : ''));
}
$wallet_seed_label = '50/50 ' . ($paper_break_asset_code !== '' ? $paper_break_asset_code : 'asset') . ' + cash';
$wallet_seed_started_at = trim((string)($paper_wallet_bootstrap['started_at'] ?? ''));
$wallet_seed_started_epoch = $wallet_seed_started_at !== '' ? yahooTimestamp($wallet_seed_started_at) : null;
$wallet_seed_started_label = $wallet_seed_started_epoch !== null
    ? gmdate('M j, Y g:i A', $wallet_seed_started_epoch) . ' UTC'
    : 'start unavailable';
$wallet_seed_entry_price = is_numeric($paper_wallet_bootstrap['entry_price'] ?? null)
    ? (float)$paper_wallet_bootstrap['entry_price']
    : 0.0;
$wallet_seed_detail_label = 'Started with $5,000 cash + $5,000 in '
    . ($paper_break_asset_code !== '' ? $paper_break_asset_code : 'the asset')
    . ($wallet_seed_entry_price > 0.0 ? ' at $' . number_format($wallet_seed_entry_price, 2) : '')
    . ' • ' . $wallet_seed_started_label;
$paper_break_last_trade_action = strtoupper(trim((string)($paper_break_state['last_trade']['action'] ?? '')));
$wallet_last_prefix = $paper_break_last_trade_action !== ''
    ? $paper_break_last_trade_action
    : $paper_break_last_trade_result;
$wallet_last_label = $paper_break_last_trade_result !== ''
    ? $wallet_last_prefix . ' ' . (($paper_break_last_trade_pnl >= 0.0 ? '+' : '-') . '$' . number_format(abs($paper_break_last_trade_pnl), 2))
    : 'No closed trade yet';
$model_resolution_label = $adaptive_complete_flip
    ? 'COMPLETE FLIP ACTIVE'
    : ($current_guess_pair_label === '%' ? 'Signal unavailable' : '1-hour ahead unresolved');
$model_pair_label = $current_guess_pair_label === '%' ? 'Unavailable' : $current_guess_pair_label;
$model_wl_label = (int)($paper_break_state['wins'] ?? 0) . ' / ' . (int)($paper_break_state['losses'] ?? 0);
$model_carry_value = number_format($accuracy, 2) . '%';
$family_base_stats = [
    'BUY' => ['right' => 0, 'total' => 0, 'percentage' => 0.0],
    'SELL' => ['right' => 0, 'total' => 0, 'percentage' => 0.0],
];
foreach ($resolved_results_by_time as $resolved_result) {
    if (!is_array($resolved_result)) continue;
    $pair = trim((string)($resolved_result['pair'] ?? ''));
    $direction = $adaptive_base_pair_map[$pair] ?? '';
    if (!isset($family_base_stats[$direction === '+' ? 'BUY' : 'SELL'])) continue;
    $family = $direction === '+' ? 'BUY' : 'SELL';
    $family_base_stats[$family]['total']++;
    $actual = (string)($resolved_result['actual'] ?? ($resolved_result['actual_direction'] ?? ''));
    if (($family === 'BUY' && $actual === '+') || ($family === 'SELL' && $actual === '-')) {
        $family_base_stats[$family]['right']++;
    }
}
foreach ($family_base_stats as &$family_stat) {
    $family_stat['percentage'] = $family_stat['total'] > 0
        ? round(($family_stat['right'] / $family_stat['total']) * 100, 1)
        : 0.0;
}
unset($family_stat);
$family_flips = [];
$agreement_branch_flips = [];
$agreement_branch_minimum_samples = 3;
foreach ($family_agreement_stats as $branch_key => $branch_stat) {
    if ((int)($branch_stat['total'] ?? 0) >= $agreement_branch_minimum_samples && (float)($branch_stat['percentage'] ?? 0.0) < 45.0) {
        $agreement_branch_flips[] = $branch_key;
    }
}
$current_family_branch_flipped = in_array($current_family_confidence_key, $agreement_branch_flips, true);
$current_family_effective_flip = $adaptive_complete_flip || $current_family_branch_flipped;
$current_family_historical_percentage = (float)($current_family_confidence['percentage'] ?? 0.0);
$current_family_effective_percentage = $current_family_effective_flip
    ? round(100.0 - $current_family_historical_percentage, 1)
    : $current_family_historical_percentage;
$current_family_confidence['historical_percentage'] = $current_family_historical_percentage;
$current_family_confidence['effective_percentage'] = $current_family_effective_percentage;
$current_family_confidence_label = $current_family_confidence['total'] > 0
    ? number_format($current_family_effective_percentage, 1) . '%'
    : '—';
$current_family_confidence_class = $current_family_effective_percentage >= 65.0
    ? 'good'
    : ($current_family_effective_percentage >= 50.0 ? 'medium' : 'low');
$final_pair_map = $adaptive_base_pair_map;
foreach ($final_pair_map as $pair => $direction) {
    $base_family = $direction === '+' ? 'BUY' : 'SELL';
    $agreement = $pair[0] === $pair[1] ? 'AGREE' : 'DISAGREE';
    $branch_key = $base_family . '|' . $agreement;
    $should_flip = $adaptive_complete_flip || in_array($branch_key, $agreement_branch_flips, true);
    if ($should_flip) $final_pair_map[$pair] = $direction === '+' ? '-' : '+';
}
setActivePairDirectionMap($final_pair_map);
$pair_rule_state['base_map'] = $adaptive_base_pair_map;
$pair_rule_state['map'] = activePairDirectionMap();
$pair_rule_state['family_flips'] = $family_flips;
$pair_rule_state['agreement_branch_flips'] = $agreement_branch_flips;
if ($loop_update_allowed) saveLocalJsonArray($pair_rule_state_path, $pair_rule_state);
$phase_action_stats = buildPhaseActionWinStats($resolved_results_by_time);
$action_stats = [
    'BUY' => ['pair' => 'BUY', 'right' => 0, 'total' => 0, 'percentage' => 0.0],
    'SELL' => ['pair' => 'SELL', 'right' => 0, 'total' => 0, 'percentage' => 0.0],
];
foreach ($resolved_results_by_time as $resolved_result) {
    if (!is_array($resolved_result)) continue;
    $pair = trim((string)($resolved_result['pair'] ?? ''));
    // Historical trust must use the direction that was saved in the table.
    // The current flip map is for future signals only and must not rewrite old rows.
    $direction = (string)($resolved_result['predicted'] ?? '');
    if ($direction !== '+' && $direction !== '-') {
        $direction = activePairDirectionMap()[$pair] ?? '';
    }
    if ($direction !== '+' && $direction !== '-') continue;
    $action = $direction === '+' ? 'BUY' : 'SELL';
    $action_stats[$action]['total']++;
    if (($resolved_result['right'] ?? null) === true) {
        $action_stats[$action]['right']++;
    }
}
foreach ($action_stats as &$action_stat) {
    $action_stat['wrong'] = max(0, (int)$action_stat['total'] - (int)$action_stat['right']);
    $action_stat['percentage'] = $action_stat['total'] > 0
        ? round(($action_stat['right'] / $action_stat['total']) * 100, 1)
        : 0.0;
}
unset($action_stat);
$adaptive_flip_active = $adaptive_complete_flip || !empty($agreement_branch_flips);
$model_resolution_label = $adaptive_flip_active
    ? ($adaptive_complete_flip ? 'COMPLETE FLIP ACTIVE' : 'BRANCH FLIP ACTIVE')
    : ($current_guess_pair_label === '%' ? 'Signal unavailable' : '1-hour ahead unresolved');
$pair_card_ids = [
    'BUY' => 'buy',
    'SELL' => 'sell',
];
$pair_card_titles = [
    'BUY' => 'BUY',
    'SELL' => 'SELL',
];
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (isset($_GET['live']) && $_GET['live'] === '1') {
    $live_payload = [
        'ok' => $error_message === '',
        'error' => $error_message,
        'updatedAt' => $updated_at,
        'dataNote' => $data_note,
        'accuracy' => $accuracy,
        'accuracyClass' => $accuracy_class,
        'accuracyRight' => $accuracy_right,
        'accuracyTotal' => $accuracy_total,
        'accuracyNote' => $accuracy_note,
        'tableHtml' => $visible_rows_html,
        'hourAuditTableHtml' => $hour_audit_table_html,
        'currentPhaseStatus' => $current_phase_status,
        'phaseStakeAmount' => $display_commitment_amount,
        'phaseSizing' => $current_phase_sizing,
        'regimeStepCount' => $regime_step_count,
        'regimeRequestedAmount' => $regime_requested_amount,
        'quarterRegimeKey' => $current_quarter_regime_key,
        'quarterRegime' => $current_quarter_regime,
        'quarterBuyAllowed' => $quarter_regime_buy_allowed,
        'quarterBuyGateBlocked' => $quarter_buy_gate_blocked,
        'quarterRegimeInverted' => $quarter_regime_inverted,
        'quarterRegimeTradeBlocked' => $quarter_regime_trade_blocked,
        'currentCandleDown' => $current_candle_is_down,
        'phaseStepMultiplier' => $phase_step_multiplier,
        'selloffTip' => (int)($hour_audit_sequences['current_sell_signal_streak'] ?? 0) >= 2,
        'sellSignalStreak' => (int)($hour_audit_sequences['current_sell_signal_streak'] ?? 0),
        'maxSellSignalStreak' => (int)($hour_audit_sequences['max_sell_signal_streak'] ?? 0),
        'downCandleStreak' => (int)($hour_audit_sequences['current_down_candle_streak'] ?? 0),
        'candles' => $candle_chart,
        'guessCandles' => $guess_candles,
        'timeline' => $timeline,
        'futureGuessTarget' => TRADE_ANALYSIS_HORIZON,
        'futureGuessCount' => $timeline_future_ready,
        'visibleFutureGuessCount' => count($visible_timeline_future),
        'chartTimeline' => $chart_timeline,
        'pairStats' => array_values($action_stats),
        'phaseActionStats' => $phase_action_stats,
        'adaptiveCompleteFlip' => $adaptive_complete_flip,
        'branchFlipActive' => !empty($agreement_branch_flips),
        'executionInversionActive' => $execution_inversion_active,
        'familyFlips' => $family_flips,
        'agreementBranchFlips' => $agreement_branch_flips,
        'agreementBranchMinimumSamples' => $agreement_branch_minimum_samples,
        'pairDirectionMap' => activePairDirectionMap(),
        'compressionScore' => $compression_score,
        'firstLoopCompressionScore' => $first_loop_compression_score,
        'primaryCompressionScore' => $primary_compression_score,
        'secondaryCompressionScore' => $secondary_compression_score,
        'secondaryCompressionState' => $secondary_compression_state,
        'combinedCompressionScore' => $combined_compression_score,
        'compressionEntropy' => $compression_entropy,
        'compressionSamples' => $compression_samples,
        'compressionTailStreak' => $compression_tail_streak,
        'compressionPhaseCount' => $compression_phase_count,
        'compressionPhaseChanges' => $compression_phase_changes,
        'compressionPerfectMinParts' => $compression_perfect_min,
        'compressionPerfectMaxParts' => $compression_perfect_max,
        'compressionDominantDirection' => $compression_dominant_direction,
        'compressionNote' => $compression_note,
        'internalAgreement' => $internal_agreement_percent,
        'internalAgreementRecent' => $internal_agreement_recent_percent,
        'internalAgreementRight' => $internal_agreement_right,
        'internalAgreementTotal' => $internal_agreement_total,
        'internalAgreementRecentRight' => $internal_agreement_recent_right,
        'internalAgreementRecentTotal' => $internal_agreement_recent_total,
        'internalAgreementWindow' => $internal_agreement_window,
        'familyAgreementStats' => $family_agreement_stats,
        'currentFamilyConfidence' => $current_family_confidence,
        'currentFamilyConfidenceKey' => $current_family_confidence_key,
        'currentFamilyBranchFlipped' => $current_family_branch_flipped,
        'currentFamilyEffectiveFlip' => $current_family_effective_flip,
        'chartPriceMin' => $chart_price_min,
        'chartPriceMax' => $chart_price_max,
        'chartPriceStep' => $chart_price_step,
        'hourHigh' => $hour_high,
        'hourLow' => $hour_low,
        'currentPrice' => $current_price,
        'currentPriceSource' => $current_price_source,
        'lastPriceChange' => $last_price_change,
        'currentPricePercentage' => $current_price_percentage,
        'currentPriceDirection' => $current_price_direction,
        'hourReferencePrice' => $hour_reference_price,
        'hourChange' => $hour_price_change,
        'hourChangePercentage' => $hour_price_percentage,
        'hourPriceDirection' => $current_price_direction,
        'marketType' => $market_type,
        'autoBreakTrader' => $paper_break_state,
        'averageChange' => $average_change,
        'paperProfit' => $paper_profit,
        'simulatedNetMove' => $paper_profit,
        'simulationOnly' => true,
        'liveOrders' => false,
        'paperTrades' => $paper_trades,
        'currentGuess' => $display_current_guess,
        'currentCommitmentAmount' => $display_commitment_amount,
        'cronSummary' => $cron_summary,
    ];
    if ($loop_update_allowed) {
        $cron_summary = [
            'writtenAt' => gmdate('Y-m-d\TH:i:s\Z'),
            'symbol' => $ticker,
            'marketType' => $market_type,
            'currentPrice' => $current_price,
            'currentPriceSource' => $current_price_source,
            'futureGuessCount' => $timeline_future_ready,
            'futureGuessTarget' => TRADE_ANALYSIS_HORIZON,
            'paperProfit' => $paper_profit,
            'paperTrades' => $paper_trades,
            'accuracy' => $accuracy,
            'accuracyRight' => $accuracy_right,
            'accuracyTotal' => $accuracy_total,
            'updatedAt' => $updated_at,
        ];
        $live_payload['cronSummary'] = $cron_summary;
        saveLocalJsonArray($cron_live_output_path, $live_payload);
        saveLocalJsonArray($cron_summary_path, $cron_summary);
    } elseif ($cache_only_request && $cron_live_cache_available) {
        $live_payload = $cron_live_output;
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($live_payload, JSON_UNESCAPED_SLASHES);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($ticker) ?> Educational Market Simulation</title>
    <script>
        window.MathJax = {
            tex: { inlineMath: [['\\(', '\\)']], displayMath: [['\\[', '\\]']] },
            chtml: { scale: 0.92 }
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/mathjax@3.2.2/es5/tex-mml-chtml.js"></script>
    <style>
        :root {
            color-scheme: dark;
            --bg: #07111f;
            --panel: #0d1a2b;
            --panel-2: #122238;
            --border: #223752;
            --text: #eef5ff;
            --muted: #8fa4bd;
            --accent: #4ade80;
            --accent-soft: rgba(74, 222, 128, .14);
            --warning: #fbbf24;
            --danger: #fb7185;
            --shadow: 0 20px 55px rgba(0, 0, 0, .28);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 10% 0%, rgba(38, 99, 235, .16), transparent 34rem),
                radial-gradient(circle at 100% 10%, rgba(74, 222, 128, .10), transparent 28rem),
                var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .shell {
            width: min(1440px, calc(100% - 32px));
            margin: 0 auto;
            padding: 30px 0 48px;
        }

        .hero {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            margin-bottom: 20px;
        }

        .eyebrow {
            margin: 0 0 8px;
            color: var(--accent);
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3.5rem);
            line-height: 1;
            letter-spacing: -.045em;
        }

        .subtitle {
            max-width: 720px;
            margin: 14px 0 0;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.6;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: rgba(13, 26, 43, .78);
            color: var(--muted);
            white-space: nowrap;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 5px var(--accent-soft);
        }

        .simulation-notice {
            display: grid;
            gap: 4px;
            margin: 0 0 10px;
            padding: 10px 13px;
            border: 1px solid rgba(251, 191, 36, .55);
            border-left: 4px solid var(--warning);
            border-radius: 10px;
            background: rgba(251, 191, 36, .09);
            color: #fef3c7;
            font-size: .76rem;
            line-height: 1.4;
        }

        .simulation-notice strong {
            color: var(--warning);
            letter-spacing: .05em;
        }

        .simulation-notice span { color: #d9e4f2; }

        .assumptions {
            margin: 0 0 10px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: rgba(13, 26, 43, .72);
            color: var(--muted);
            font-size: .72rem;
            line-height: 1.45;
        }

        .assumptions summary {
            padding: 8px 12px;
            color: #bcd0e8;
            cursor: pointer;
            font-weight: 800;
        }

        .assumptions ul { margin: 0; padding: 0 28px 10px; }
        .assumptions li { margin: 3px 0; }

        .control-panel,
        .metric,
        .results-panel,
        .error-panel {
            border: 1px solid var(--border);
            background: rgba(13, 26, 43, .88);
            box-shadow: var(--shadow);
        }

        .control-panel {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 14px;
            align-items: end;
            margin-bottom: 18px;
            padding: 18px;
            border-radius: 18px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .input-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 52px;
            padding: 0 16px;
            border: 1px solid #315071;
            border-radius: 12px;
            background: #081423;
        }

        .input-prefix {
            color: var(--accent);
            font-weight: 900;
        }

        input[type="text"] {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--text);
            font: inherit;
            font-size: 1.1rem;
            font-weight: 750;
            text-transform: uppercase;
        }

        input[type="number"] {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--text);
            font: inherit;
            font-size: 1rem;
            font-weight: 750;
            font-variant-numeric: tabular-nums;
        }

        .threshold-field { width: 104px; }
        .threshold-field .input-wrap { padding: 0 10px; }

        button {
            min-height: 52px;
            padding: 0 24px;
            border: 0;
            border-radius: 12px;
            background: var(--accent);
            color: #05210f;
            font: inherit;
            font-weight: 900;
            cursor: pointer;
            transition: transform .18s ease, filter .18s ease;
        }

        button:hover { transform: translateY(-1px); filter: brightness(1.05); }

        .secondary-action {
            background: rgba(15, 23, 42, .9);
            color: #dbe8f6;
            border: 1px solid rgba(61, 82, 111, .88);
            box-shadow: none;
        }

        .secondary-action:hover {
            filter: brightness(1.08);
        }

        .market-types {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .market-choice {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 44px;
            padding: 0 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--muted);
            background: rgba(18, 34, 56, .72);
            cursor: pointer;
            font-weight: 800;
        }

        .market-choice:has(input:checked) {
            color: var(--text);
            border-color: var(--accent);
            background: var(--accent-soft);
        }

        .market-choice input { accent-color: var(--accent); }

        .metrics {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .pair-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 8px;
        }

        .pair-card .metric-value { font-variant-numeric: tabular-nums; }
        .pair-card .metric-note { color: #bcd0e8; }

        .metric {
            min-height: 132px;
            padding: 18px;
            border-radius: 16px;
        }

        .metric-label {
            color: var(--muted);
            font-size: .78rem;
            font-weight: 750;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .metric-value {
            margin-top: 12px;
            font-size: clamp(1.25rem, 2vw, 2rem);
            font-weight: 850;
            letter-spacing: -.035em;
            overflow-wrap: anywhere;
        }

        .metric-value.good { color: var(--accent); }
        .metric-value.medium { color: var(--warning); }
        .metric-value.low { color: var(--danger); }

        .metric-note {
            margin-top: 8px;
            color: var(--muted);
            font-size: .82rem;
        }

        .market-pulse-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 10px;
            margin-bottom: 8px;
        }

        .market-pulse-card {
            display: grid;
            gap: 6px;
            min-height: auto;
        }

        .market-pulse-card .metric-value {
            margin-top: 0;
        }

        .market-pulse-card .metric-note {
            margin-top: 0;
            line-height: 1.45;
        }

        .market-pulse-card .metric-label {
            display: block;
            margin-bottom: 8px;
        }

        .market-pulse-card .signal-value,
        .market-pulse-card .strategy-value {
            font-size: 1.02rem;
            letter-spacing: -.015em;
        }

        .metric-divider {
            height: 1px;
            margin: 4px 0 6px;
            background: var(--border);
        }

        .results-panel {
            overflow: hidden;
            border-radius: 18px;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
            background: rgba(18, 34, 56, .75);
        }

        .results-header h2 {
            margin: 0;
            font-size: 1.05rem;
        }

        .results-header span {
            color: var(--muted);
            font-size: .85rem;
        }

        .table-scroll {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 820px;
        }

        td, th {
            padding: 12px 14px !important;
            border-bottom: 1px solid rgba(34, 55, 82, .72);
            color: var(--text) !important;
            background: transparent !important;
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        tr:first-child td {
            position: sticky;
            top: 0;
            z-index: 2;
            color: #bcd0e8 !important;
            background: #122238 !important;
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        td:first-child { text-align: left; }
        tr:hover td { background: rgba(74, 222, 128, .045) !important; }

        td[style*="background-color:green"] {
            color: var(--accent) !important;
            background: rgba(74, 222, 128, .10) !important;
            font-weight: 800;
        }

        td[style*="background-color:red"] {
            color: var(--danger) !important;
            background: rgba(251, 113, 133, .10) !important;
            font-weight: 800;
        }

        .error-panel {
            margin-bottom: 18px;
            padding: 18px 20px;
            border-color: rgba(251, 113, 133, .42);
            border-radius: 16px;
            color: #fecdd3;
        }

        .footer-note {
            margin: 16px 2px 0;
            color: var(--muted);
            font-size: .8rem;
            line-height: 1.55;
        }

        .compact-dashboard .shell { width: min(1880px, calc(100% - 20px)); padding: 10px 0; }
        .symbol-marquee {
            overflow-x: auto;
            overflow-y: hidden;
            border: 1px solid rgba(53, 79, 112, .86);
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(11, 19, 31, .98), rgba(8, 14, 24, .92));
            padding: 8px 0;
            margin-bottom: 8px;
            scroll-snap-type: x proximity;
            scrollbar-width: none;
            -ms-overflow-style: none;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-x;
            cursor: grab;
        }
        .symbol-marquee.is-dragging {
            cursor: grabbing;
            user-select: none;
        }
        .symbol-marquee::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
            background: transparent;
        }
        .symbol-marquee-track {
            display: flex;
            gap: 8px;
            width: max-content;
            padding: 0 12px;
            align-items: stretch;
        }
        .symbol-marquee-link,
        .nav-symbol-link,
        .symbol-slide-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 7px 11px;
            border-radius: 8px;
            text-decoration: none;
            color: #dce8f5;
            border: 1px solid rgba(255, 255, 255, .08);
            background: rgba(255, 255, 255, .04);
            white-space: nowrap;
        }
        .symbol-marquee-link {
            scroll-snap-align: start;
        }
        .symbol-link-stack {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
            min-width: 0;
        }
        .symbol-link-head {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }
        .symbol-link-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,.10);
            background: rgba(255,255,255,.05);
            color: #8fa4bd;
            font-size: .58rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            line-height: 1.2;
            white-space: nowrap;
        }
        .symbol-link-badge.is-active {
            color: #0b1725;
            background: rgba(140, 240, 191, .94);
            border-color: transparent;
        }
        .symbol-link-note {
            color: #8fa4bd;
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .04em;
            line-height: 1.2;
            white-space: nowrap;
        }
        .symbol-link-price {
            color: #eef5ff;
            font-size: .72rem;
            font-weight: 800;
            line-height: 1.15;
            white-space: nowrap;
        }
        .symbol-marquee-spacer {
            flex: 0 0 22px;
            min-width: 22px;
            min-height: 1px;
            background: transparent;
            border: 0;
            opacity: 0;
            pointer-events: none;
        }
        .symbol-item {
            display: grid;
            grid-template-columns: minmax(0, 95fr) minmax(10px, 5fr);
            align-items: stretch;
            gap: 4px;
            position: relative;
            flex: 0 0 auto;
        }
        .symbol-item--rail {
            min-width: 0;
        }
        .symbol-item form {
            margin: 0;
        }
        .symbol-remove-text {
            margin: 0;
            color: rgba(255, 213, 218, .88);
            cursor: pointer;
            font-weight: 900;
            line-height: 1;
            padding: 0;
            font-size: 10px;
            user-select: none;
        }
        .symbol-item .symbol-remove-form {
            position: static;
            z-index: 3;
            display: flex;
            align-items: flex-start;
            justify-content: flex-end;
        }
        .symbol-marquee-link,
        .symbol-slide-link {
            position: relative;
            padding-right: 11px;
            overflow: hidden;
            min-width: 0;
        }
        .symbol-remove-text:hover {
            background: transparent;
            color: rgba(251, 113, 133, .98);
        }
        .symbol-marquee-link.is-active,
        .nav-symbol-link.is-active,
        .symbol-slide-link.is-active {
            color: #07111d;
            background: linear-gradient(135deg, #8cf0bf, #5db3ff);
            border-color: transparent;
            font-weight: 700;
        }
        .hero-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        .hero-page-status {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        .hero-page-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.05);
            color: #dce8f5;
            font-size: .74rem;
            font-weight: 760;
        }
        .hero-page-chip.is-front {
            color: #07111d;
            background: linear-gradient(135deg, #8cf0bf, #5db3ff);
            border-color: transparent;
        }
        .hero-page-note {
            color: #9fb2c6;
            font-size: .74rem;
            line-height: 1.4;
            margin: 0;
        }
        .tracked-dashboard {
            margin: 0 0 18px;
        }
        .tracked-dashboard-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }
        .tracked-dashboard-title {
            margin: 0;
            font-size: .96rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #dce8f5;
        }
        .tracked-dashboard-note {
            margin: 0;
            color: #8fa4bd;
            font-size: .76rem;
        }
        .tracked-dashboard-controls {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .tracked-dashboard-arrow {
            width: 34px;
            min-width: 34px;
            height: 34px;
            min-height: 34px;
            padding: 0;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.10);
            background: rgba(255,255,255,.06);
            color: #eef5ff;
            font-size: 1rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .tracked-dashboard-arrow:hover {
            background: rgba(93, 179, 255, .14);
            border-color: rgba(93, 179, 255, .3);
        }
        .tracked-dashboard-window {
            overflow: hidden;
        }
        .tracked-dashboard-grid {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(260px, 320px);
            gap: 10px;
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: 4px;
        }
        .tracked-dashboard-grid::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }
        .tracked-dashboard-card {
            display: block;
            text-decoration: none;
            color: inherit;
            border: 1px solid rgba(53, 79, 112, .86);
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(11, 19, 31, .98), rgba(8, 14, 24, .92));
            padding: 12px;
            min-width: 0;
        }
        .tracked-dashboard-card.is-active {
            border-color: rgba(140, 240, 191, .76);
            box-shadow: 0 0 0 1px rgba(140, 240, 191, .18) inset;
        }
        .tracked-dashboard-top,
        .tracked-dashboard-bottom {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .tracked-dashboard-top {
            margin-bottom: 10px;
        }
        .tracked-dashboard-symbol {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }
        .tracked-dashboard-market {
            color: #8fa4bd;
            font-size: .66rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .tracked-dashboard-name {
            color: #eef5ff;
            font-size: .95rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .tracked-dashboard-meta {
            color: #8fa4bd;
            font-size: .68rem;
            font-weight: 700;
            line-height: 1.25;
        }
        .tracked-dashboard-price {
            text-align: right;
            color: #eef5ff;
            font-size: 1rem;
            font-weight: 900;
            white-space: nowrap;
        }
        .tracked-dashboard-gridline {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 10px;
            margin-bottom: 10px;
        }
        .tracked-dashboard-stat span {
            display: block;
            color: #8fa4bd;
            font-size: .64rem;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 2px;
        }
        .tracked-dashboard-stat strong {
            display: block;
            color: #eef5ff;
            font-size: .8rem;
            line-height: 1.2;
            word-break: break-word;
        }
        .tracked-dashboard-bottom {
            border-top: 1px solid rgba(255,255,255,.06);
            padding-top: 8px;
            color: #8fa4bd;
            font-size: .68rem;
            line-height: 1.3;
        }
        .tracked-dashboard-active-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px;
            border-radius: 999px;
            background: rgba(140, 240, 191, .12);
            color: #8cf0bf;
            font-size: .64rem;
            font-weight: 800;
            letter-spacing: .07em;
            text-transform: uppercase;
        }
        .hero-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            color: #dce8f5;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.05);
            font-size: .76rem;
            font-weight: 760;
        }
        .hero-link:hover {
            background: rgba(93, 179, 255, .14);
            border-color: rgba(93, 179, 255, .3);
        }
        .compact-dashboard .hero { align-items:center; margin-bottom:8px; }
        .compact-dashboard h1 { font-size:clamp(1.45rem,2.2vw,2.25rem); }
        .compact-dashboard .subtitle, .compact-dashboard .eyebrow { display:none; }
        .compact-dashboard .control-panel { display:flex; flex-wrap:wrap; align-items:end; gap:8px; padding:8px; margin-bottom:8px; border-radius:12px; }
        .compact-dashboard .field label { margin-bottom:4px; font-size:.65rem; }
        .compact-dashboard .market-types { gap:5px; }
        .compact-dashboard .market-choice { min-height:32px; padding:0 9px; font-size:.75rem; }
        .compact-dashboard .input-wrap { width:180px; min-height:32px; padding:0 9px; border-radius:8px; }
        .compact-dashboard input[type="text"] { font-size:.85rem; }
        .compact-dashboard button { min-height:32px; padding:0 12px; border-radius:8px; font-size:.75rem; }
        .compact-dashboard .metrics { grid-template-columns:repeat(3,minmax(0,280px)); gap:8px; margin-bottom:8px; }
        .compact-dashboard .pair-stats { grid-template-columns:repeat(4,minmax(0,1fr)); }
        .compact-dashboard .metric { min-height:74px; padding:9px 12px; border-radius:10px; }
        .compact-dashboard .metric-value { margin-top:3px; font-size:1.6rem; }
        .compact-dashboard .metric-note { margin-top:2px; font-size:.7rem; }
        .analysis-grid { display:grid; grid-template-columns:minmax(0,1fr); gap:8px; }
        .chart-panel { margin:0; border:1px solid var(--border); border-radius:16px; background:rgba(13,26,43,.88); overflow:hidden; }
        .chart-stage { position:relative; }
        #candleChart { display:block; width:100%; height:520px; }
        .chart-stage .trade-overlay {
            position: static;
            margin: 10px 10px 0;
        }
        .chart-timestamp {
            padding: 8px 12px 12px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: .72rem;
            line-height: 1.4;
        }
        .trade-overlay { display:flex; flex-wrap:wrap; gap:12px; padding:7px 10px; border:1px solid var(--border); border-radius:9px; background:rgba(7,17,31,.92); font-size:.72rem; font-weight:850; }
        .compact-dashboard .results-panel { margin:0; }
        .compact-dashboard .table-scroll { max-height:520px; overflow:hidden; }
        .compact-dashboard table { min-width:0; width:100%; table-layout:fixed; }
        .compact-dashboard td { padding:5px 7px !important; font-size:.72rem; overflow:hidden; text-overflow:ellipsis; }
        .compact-dashboard td:nth-child(1) { width:23%; text-align:left; }
        .compact-dashboard td:nth-child(2) { width:39%; text-align:center; }
        .compact-dashboard td:nth-child(3) { width:38%; text-align:center; overflow:visible; text-overflow:clip; font-weight:850; }
        #liveGuessTable .signal-sign { font-weight:900; }
        #liveGuessTable .gain-sign { color:#4ade80; }
        #liveGuessTable .loss-sign { color:#fb7185; }
        #liveGuessTable td:nth-child(3).result-gain-cell { background:#14532d !important; color:#f0fdf4 !important; }
        #liveGuessTable td:nth-child(3).result-loss-cell { background:#7f1d1d !important; color:#fef2f2 !important; }
        #liveGuessTable td:nth-child(3).result-neutral-cell { background:#334155 !important; color:#e2e8f0 !important; }
        .table-disclaimer { margin:0; padding:8px 12px; border-top:1px solid var(--border); background:rgba(7,17,31,.72); color:var(--muted); font-size:.68rem; line-height:1.35; }
        .table-disclaimer strong { color:#d9e4f2; }
        .current-guess-row td { color:#eef5ff !important; background:rgba(251,191,36,.16) !important; border-top:2px solid var(--warning); border-bottom:2px solid var(--warning); font-weight:900; }
        .forward-guess-row td { color:#9fc8ff !important; background:rgba(98,168,255,.035) !important; }

        .browser-width-section {
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
            margin-top: 18px;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            background: rgba(7, 17, 31, .96);
        }

        .browser-width-inner {
            width: min(1880px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px 0;
        }

        .review-heading {
            margin: 0 0 8px;
            font-size: clamp(1.35rem, 2.5vw, 2.15rem);
            letter-spacing: -.025em;
        }

        .review-intro {
            max-width: 1100px;
            margin: 0 0 22px;
            color: var(--muted);
            line-height: 1.65;
        }

        .math-review-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .math-card {
            min-width: 0;
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: rgba(13, 26, 43, .82);
            overflow-x: auto;
        }

        .math-card.wide { grid-column: 1 / -1; }
        .math-card h3 { margin: 0 0 10px; color: #d9e4f2; font-size: 1rem; }
        .math-card p, .math-card li { color: #b8c8da; font-size: .84rem; line-height: 1.6; }
        .math-card ul { margin: 10px 0 0; padding-left: 20px; }
        .math-card mjx-container[display="true"] { margin: 1em 0 !important; overflow-x: auto; overflow-y: hidden; }

        .latex-source {
            margin-top: 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #050d18;
        }

        .latex-source summary {
            padding: 12px 14px;
            cursor: pointer;
            color: #bcd0e8;
            font-size: .82rem;
            font-weight: 850;
        }

        .latex-source pre {
            margin: 0;
            padding: 16px;
            border-top: 1px solid var(--border);
            color: #c8f7d8;
            font: .76rem/1.55 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .reference-drawer {
            border: 1px solid rgba(40, 57, 80, .94);
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(12, 20, 33, .96), rgba(8, 14, 23, .93));
            box-shadow: 0 22px 48px rgba(0, 0, 0, .22);
            overflow: hidden;
        }
        .reference-drawer summary {
            list-style: none;
            cursor: pointer;
            padding: 18px 20px;
            font-weight: 820;
            color: #eef5ff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .reference-drawer summary::-webkit-details-marker { display: none; }
        .reference-drawer summary::after {
            content: '+';
            font-size: 1.2rem;
            color: #8cf0bf;
        }
        .reference-drawer[open] summary::after { content: '–'; }
        .reference-drawer-body { padding: 0 20px 20px; }
        .reference-drawer-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 14px;
        }
        .reference-drawer-head h2 {
            margin: 0;
            font-size: 1.02rem;
            color: #eef5ff;
        }
        .reference-close-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            min-height: 36px;
            border-radius: 999px;
            color: #eef5ff;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.04);
            font-weight: 800;
            cursor: pointer;
        }

        .disclosure-section { background: #050b14; }
        .disclosure-section h2 { margin: 0 0 18px; color: var(--warning); font-size: 1.1rem; letter-spacing: .06em; }
        .disclosure-section h3 { margin: 24px 0 10px; color: #fef3c7; font-size: 1rem; letter-spacing: .04em; }
        .disclosure-section p {
            margin: 0 0 15px;
            color: #c7d3e1;
            font-size: .78rem;
            line-height: 1.65;
            letter-spacing: .015em;
        }
        .disclosure-section .regulatory-copy { text-transform: uppercase; }
        .disclosure-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: flex-start;
            justify-content: center;
            padding: 26px 16px;
            background: rgba(4, 8, 14, .76);
            backdrop-filter: blur(10px);
            z-index: 60;
        }
        .disclosure-modal:target { display: flex; }
        .disclosure-dialog {
            width: min(980px, 100%);
            max-height: calc(100vh - 52px);
            overflow: auto;
            border: 1px solid rgba(44, 63, 89, .92);
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(13, 23, 37, .98), rgba(8, 14, 23, .96));
            box-shadow: 0 28px 60px rgba(0, 0, 0, .42);
            padding: 20px;
        }
        .disclosure-modal-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 14px;
        }
        .disclosure-modal-head h2 { margin: 0; font-size: 1.05rem; }
        .modal-link-shell {
            margin: 8px 0 0;
            color: #8fa4bd;
            font-size: .82rem;
        }
        .modal-close-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            min-height: 36px;
            border-radius: 999px;
            color: #eef5ff;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.04);
            font-weight: 800;
        }
        .signal-key {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin: 12px 0 18px;
        }
        .signal-key span {
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: #d9e4f2;
            background: rgba(13, 26, 43, .72);
            font-size: .8rem;
            font-weight: 800;
        }

        body.compact-dashboard {
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .15), transparent 28rem),
                radial-gradient(circle at 88% 0%, rgba(16, 185, 129, .11), transparent 24rem),
                linear-gradient(180deg, #040a12 0%, #09111d 100%);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .compact-dashboard .shell {
            width: min(1680px, calc(100% - 32px));
            padding: 24px 0 44px;
        }

        .compact-dashboard .hero {
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 18px;
        }

        .hero-copy {
            display: grid;
            gap: 12px;
            max-width: 980px;
        }

        .compact-dashboard .eyebrow {
            display: block;
            margin: 0;
            color: #7dd3fc;
        }

        .compact-dashboard h1 {
            font-size: clamp(1.9rem, 4vw, 3.3rem);
            letter-spacing: -.05em;
        }

        .compact-dashboard .subtitle {
            display: block;
            max-width: 860px;
            margin: 0;
            color: #a9bdd4;
            font-size: .98rem;
            line-height: 1.55;
        }

        .hero-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .hero-tags span,
        .status-meta span,
        .summary-ribbon span,
        .panel-chip,
        .card-chip {
            border: 1px solid rgba(61, 82, 111, .82);
            background: rgba(7, 13, 22, .74);
            color: #dbe8f6;
        }

        .hero-tags span {
            padding: 9px 12px;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .hero-status {
            display: grid;
            gap: 10px;
            justify-items: end;
            min-width: 320px;
        }

        .compact-dashboard .status-pill {
            padding: 12px 16px;
            border-radius: 16px;
            background: rgba(10, 17, 28, .9);
        }

        .status-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .status-meta span {
            padding: 8px 10px;
            border-radius: 999px;
            font-size: .74rem;
            font-weight: 760;
        }

        .compact-dashboard .control-panel {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            gap: 12px 14px;
            margin-bottom: 16px;
            padding: 16px 18px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(13, 22, 35, .96), rgba(8, 14, 23, .9));
        }

        .compact-dashboard .field {
            display: grid;
            gap: 6px;
            min-width: 0;
        }

        .compact-dashboard .field label {
            margin: 0;
            font-size: .7rem;
            letter-spacing: .09em;
        }

        .compact-dashboard .market-choice,
        .compact-dashboard .input-wrap,
        .compact-dashboard button {
            min-height: 44px;
            border-radius: 14px;
        }

        .compact-dashboard .input-wrap {
            padding: 0 12px;
            background: rgba(5, 10, 18, .92);
        }

        .compact-dashboard input[type="text"],
        .compact-dashboard input[type="number"] {
            font-size: .95rem;
        }

        .compact-dashboard .threshold-field {
            width: 120px;
        }

        .compact-dashboard button {
            min-width: 160px;
            padding: 0 18px;
            box-shadow: 0 12px 28px rgba(74, 222, 128, .15);
        }

        .compact-dashboard .market-pulse-metrics {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 12px;
        }

        .summary-card {
            position: relative;
            display: grid;
            gap: 14px;
            min-height: 252px;
            padding: 20px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(12, 20, 33, .96), rgba(8, 14, 23, .93));
            border: 1px solid rgba(45, 63, 87, .92);
            box-shadow: 0 22px 48px rgba(0, 0, 0, .28);
            overflow: hidden;
        }

        .summary-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(125, 211, 252, .35), transparent);
        }

        .summary-card--wallet {
            border-color: rgba(34, 197, 94, .35);
            box-shadow: 0 24px 54px rgba(0, 0, 0, .33), 0 0 0 1px rgba(74, 222, 128, .06) inset;
        }

        .summary-card--signal {
            border-color: rgba(251, 191, 36, .24);
        }

        .card-topline {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .card-kicker {
            margin: 6px 0 0;
            color: #dce8f5;
            font-size: .9rem;
            font-weight: 760;
            letter-spacing: -.01em;
        }

        .card-chip,
        .panel-chip,
        .summary-ribbon span {
            padding: 8px 10px;
            border-radius: 999px;
            font-size: .74rem;
            font-weight: 820;
            line-height: 1.3;
        }

        .card-chip.is-live {
            color: #b8f7ca;
            border-color: rgba(34, 197, 94, .35);
            background: rgba(16, 185, 129, .12);
        }

        .card-chip.is-file {
            color: #bfdbfe;
            border-color: rgba(96, 165, 250, .34);
            background: rgba(37, 99, 235, .14);
        }

        .card-chip.is-warning {
            color: #fde68a;
            border-color: rgba(251, 191, 36, .38);
            background: rgba(251, 191, 36, .14);
        }

        .card-chip.is-neutral {
            color: #d9e4f2;
            border-color: rgba(148, 163, 184, .32);
            background: rgba(71, 85, 105, .28);
        }

        .card-chip.is-accent {
            color: #dcfce7;
            border-color: rgba(34, 197, 94, .32);
            background: rgba(22, 163, 74, .13);
        }

        .card-main {
            display: grid;
            gap: 8px;
        }

        .summary-card .metric-value {
            margin: 0;
            font-size: clamp(1.5rem, 2.6vw, 2.5rem);
            line-height: .98;
        }

        .summary-card--signal .metric-value {
            font-size: clamp(1.25rem, 2vw, 2rem);
        }

        .metric-note--strong {
            margin: 0;
            color: #dce8f5;
            font-size: .88rem;
            line-height: 1.5;
        }

        .card-grid {
            display: grid;
            gap: 10px;
        }

        .card-grid--market,
        .card-grid--wallet,
        .card-grid--signal {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .summary-stat {
            min-width: 0;
            padding: 12px 14px;
            border: 1px solid rgba(52, 71, 99, .75);
            border-radius: 15px;
            background: rgba(8, 15, 26, .76);
        }

        .summary-stat span {
            display: block;
            margin-bottom: 7px;
            color: #86a0bc;
            font-size: .71rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .summary-stat strong {
            display: block;
            color: #f1f7ff;
            font-size: 1.02rem;
            font-weight: 820;
            line-height: 1.28;
            overflow-wrap: anywhere;
        }

        .summary-ribbon {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .card-footnote {
            margin-top: auto;
            color: #9eb4cb;
            font-size: .76rem;
            line-height: 1.45;
        }

        .card-footnote--seed {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(34, 197, 94, .14);
            color: #bfe9cb;
        }

        .compact-dashboard .pair-stats {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .pair-card {
            min-height: auto;
            padding: 16px;
            border-radius: 18px;
            background: rgba(9, 16, 27, .82);
        }

        .pair-card .metric-value {
            margin-top: 10px;
            font-size: 1.45rem;
        }

        .pair-card .metric-note {
            margin-top: 8px;
            line-height: 1.4;
        }

        .pair-card--carry {
            border-color: rgba(96, 165, 250, .3);
            background: linear-gradient(180deg, rgba(13, 22, 35, .95), rgba(9, 16, 27, .9));
        }

        .compact-dashboard .analysis-grid {
            gap: 12px;
        }

        .results-panel,
        .chart-panel {
            border-radius: 22px;
            border: 1px solid rgba(40, 57, 80, .94);
            background: linear-gradient(180deg, rgba(12, 20, 33, .96), rgba(8, 14, 23, .93));
            box-shadow: 0 22px 48px rgba(0, 0, 0, .26);
        }

        .results-header {
            padding: 18px 20px 16px;
            background: linear-gradient(180deg, rgba(15, 25, 40, .96), rgba(12, 20, 32, .84));
        }

        .results-header h2 {
            margin: 0;
            font-size: 1.08rem;
        }

        .results-header p {
            margin: 6px 0 0;
            color: #92a8c0;
            font-size: .82rem;
            line-height: 1.45;
        }

        .header-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .panel-chip {
            color: #dce8f5;
        }

        .compact-dashboard .table-scroll {
            max-height: none;
            overflow: auto;
            background: linear-gradient(180deg, rgba(8, 15, 25, .52), rgba(5, 10, 18, .66));
        }

        .compact-dashboard table {
            min-width: 760px;
            width: 100%;
            table-layout: auto;
        }

        .compact-dashboard td,
        .compact-dashboard th {
            padding: 10px 12px !important;
            font-size: .82rem;
            white-space: nowrap;
        }

        .compact-dashboard tr:first-child td {
            top: 0;
            background: #0f1c2d !important;
            color: #c9d9eb !important;
            font-size: .73rem;
        }

        .compact-dashboard td:nth-child(1),
        .compact-dashboard td:nth-child(2),
        .compact-dashboard td:nth-child(3) {
            text-align: left;
        }

        .compact-dashboard tr:hover td {
            background: rgba(59, 130, 246, .06) !important;
        }

        .current-guess-row td {
            background: rgba(251, 191, 36, .14) !important;
            border-top: 2px solid var(--warning);
            border-bottom: 2px solid var(--warning);
        }

        .forward-guess-row td {
            background: rgba(36, 54, 84, .35) !important;
            color: #bdd4ef !important;
        }

        .table-disclaimer {
            padding: 12px 16px;
            border-top: 1px solid rgba(35, 50, 73, .92);
            background: rgba(5, 10, 18, .84);
            color: #8fa4bd;
            font-size: .76rem;
            line-height: 1.45;
        }

        .table-disclaimer strong {
            color: #eef5ff;
        }

        .compact-dashboard .chart-panel {
            overflow: hidden;
        }

        .results-header--chart {
            align-items: flex-start;
        }

        .chart-stage {
            padding-bottom: 8px;
            background: linear-gradient(180deg, rgba(6, 11, 19, .56), rgba(8, 14, 23, .12));
        }

        .compact-dashboard #candleChart {
            height: 500px;
        }

        .trade-overlay {
            margin: 0 16px 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(6, 12, 20, .78);
            backdrop-filter: blur(8px);
        }

        .chart-timestamp {
            padding: 12px 16px 16px;
            font-size: .76rem;
        }

        @media (max-width: 1220px) {
            .compact-dashboard .market-pulse-metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .summary-card--wallet {
                grid-column: 1 / -1;
            }

            .compact-dashboard .pair-stats {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 920px) {
            .compact-dashboard .hero {
                flex-direction: column;
            }

            .hero-status {
                justify-items: start;
                min-width: 0;
            }

            .status-meta {
                justify-content: flex-start;
            }

            .math-review-grid {
                grid-template-columns: 1fr;
            }

            .math-card.wide {
                grid-column: auto;
            }

            .compact-dashboard .pair-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .compact-dashboard #candleChart {
                height: 430px;
            }
        }

        @media (max-width: 720px) {
            .compact-dashboard .shell {
                width: min(100% - 20px, 1680px);
                padding-top: 20px;
            }

            .compact-dashboard .control-panel {
                padding: 14px;
            }

            .compact-dashboard .field,
            .compact-dashboard .threshold-field,
            .compact-dashboard button {
                width: 100%;
            }

            .compact-dashboard .market-pulse-metrics,
            .compact-dashboard .pair-stats {
                grid-template-columns: 1fr;
            }

            .summary-card--wallet {
                grid-column: auto;
            }

            .card-grid--market,
            .card-grid--wallet,
            .card-grid--signal {
                grid-template-columns: 1fr;
            }

            .results-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .header-chips {
                justify-content: flex-start;
            }

            .browser-width-inner {
                width: min(100% - 20px, 1880px);
                padding: 22px 0;
            }

            .signal-key {
                grid-template-columns: 1fr;
            }

            .compact-dashboard #candleChart {
                height: 340px;
            }
        }
    </style>
</head>
<body class="compact-dashboard">
<main class="shell">
    <?php if (!empty($tracked_marquee_links)): ?>
        <section class="symbol-marquee" aria-label="Tracked market symbols">
            <div class="symbol-marquee-track">
                <span class="symbol-marquee-spacer" aria-hidden="true"></span>
                <?php foreach ($tracked_marquee_links as $tracked_link): ?>
                    <div class="symbol-item">
                        <a class="symbol-marquee-link<?= $tracked_link['active'] ? ' is-active' : '' ?>" href="<?= htmlspecialchars($tracked_link['href']) ?>"<?= ($tracked_link['aria_current']) ? ' aria-current="' . htmlspecialchars((string)$tracked_link['aria_current']) . '"' : '' ?>>
                            <span class="symbol-link-stack">
                                <span class="symbol-link-head">
                                    <span><?= htmlspecialchars($tracked_link['market'] === 'stock' ? 'STOCK' : 'CRYPTO') ?></span>
                                    <strong><?= htmlspecialchars($tracked_link['symbol']) ?></strong>
                                </span>
                                <span class="symbol-link-price"><?= htmlspecialchars((string)($tracked_link['price_label'] ?? '—')) ?></span>
                                <?php if (!empty($tracked_link['role_label'])): ?>
                                    <span class="symbol-link-badge<?= $tracked_link['active'] ? ' is-active' : '' ?>"><?= htmlspecialchars($tracked_link['role_label']) ?></span>
                                <?php endif; ?>
                            </span>
                        </a>
                        <form method="post" action="" class="symbol-remove-form">
                            <input type="hidden" name="remove_tracked_symbol" value="1">
                            <input type="hidden" name="remove_market_type" value="<?= htmlspecialchars($tracked_link['market']) ?>">
                            <input type="hidden" name="remove_symbol" value="<?= htmlspecialchars($tracked_link['symbol']) ?>">
                            <p
                                class="symbol-remove-text"
                                role="button"
                                tabindex="0"
                                aria-label="Remove <?= htmlspecialchars($tracked_link['symbol']) ?> from tracked symbols"
                                onclick="this.closest('form').submit()"
                                onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.closest('form').submit();}"
                            >×</p>
                        </form>
                    </div>
                <?php endforeach; ?>
                <span class="symbol-marquee-spacer" aria-hidden="true"></span>
            </div>
        </section>
    <?php endif; ?>

    <header class="hero">
        <div class="hero-copy">
            <p class="eyebrow">CNGN Educational Simulation</p>
            <h1><?= htmlspecialchars($ticker) ?> 1-Hour Ahead Market Console</h1>
            <p class="subtitle">
                Hourly candlesticks only, timestamp-locked model signals, and a paper wallet that
                starts every Analysis click with $5,000 cash and $5,000 invested in the asset. This page does
                not place trades or manage accounts.
            </p>
            <div class="hero-tags" aria-label="Console focus">
                <span>Hourly candlesticks</span>
                <span>50/50 $10K wallet</span>
                <span>Resolved trade ledger</span>
            </div>
            <div class="hero-page-status" aria-label="Page role">
                <span class="hero-page-chip is-front">ACTIVE PAGE · <?= htmlspecialchars($ticker) ?></span>
                <span class="hero-page-chip"><?= htmlspecialchars(strtoupper($market_type === 'stock' ? 'STOCK PAGE' : 'CRYPTO PAGE')) ?></span>
            </div>
            <p class="hero-page-note">Other tracked symbols remain in background view as supporting pages.</p>
            <div class="hero-links">
                <a class="hero-link" href="#site-disclaimer">Disclaimer</a>
                <a class="hero-link" href="#model-reference">Model notes</a>
                <a class="hero-link" href="./latex-formula.php" target="_blank" rel="noopener">LaTeX formula</a>
            </div>
        </div>
        <div class="hero-status">
            <div class="status-pill" title="The model advances at five-minute boundaries; the market cache is checked about every 30 seconds">
                <span class="status-dot"></span>
                <span>Next model boundary: <strong id="retrieveCountdown">--:--</strong> at <strong id="retrieveTime">--:--:--</strong></span>
            </div>
            <div class="status-meta">
                <span id="dataStatusNote"><?= htmlspecialchars($data_note !== '' ? $data_note : 'Using the current 30-second market feed.') ?></span>
                <span id="dataStatusUpdated"><?= htmlspecialchars($updated_at !== 'Unavailable' ? 'Feed file ' . $updated_at : 'Feed file unavailable') ?></span>
            </div>
        </div>
    </header>

    <?php if (!empty($tracked_dashboard_cards)): ?>
        <section class="tracked-dashboard panel" aria-label="Tracked symbol dashboard">
            <div class="tracked-dashboard-header">
                <div>
                    <h2 class="tracked-dashboard-title">Tracked symbol dashboard</h2>
                    <p class="tracked-dashboard-note">Scheduler-backed price, wallet, signal, trust, and last-trade view for every tracked page.</p>
                </div>
                <div class="tracked-dashboard-controls" aria-label="Tracked symbol carousel controls">
                    <button type="button" class="tracked-dashboard-arrow" data-dashboard-prev aria-label="Previous tracked symbols">‹</button>
                    <button type="button" class="tracked-dashboard-arrow" data-dashboard-next aria-label="Next tracked symbols">›</button>
                </div>
            </div>
            <div class="tracked-dashboard-window">
                <div class="tracked-dashboard-grid">
                    <?php foreach ($tracked_dashboard_cards as $dashboard_card): ?>
                        <a
                            class="tracked-dashboard-card<?= $dashboard_card['active'] ? ' is-active' : '' ?>"
                            href="<?= htmlspecialchars($dashboard_card['href']) ?>"
                            <?= ($dashboard_card['aria_current']) ? ' aria-current="' . htmlspecialchars((string)$dashboard_card['aria_current']) . '"' : '' ?>
                        >
                            <div class="tracked-dashboard-top">
                                <div class="tracked-dashboard-symbol">
                                    <span class="tracked-dashboard-market"><?= htmlspecialchars($dashboard_card['market'] === 'stock' ? 'STOCK' : 'CRYPTO') ?></span>
                                    <strong class="tracked-dashboard-name"><?= htmlspecialchars($dashboard_card['symbol']) ?></strong>
                                    <span class="tracked-dashboard-meta"><?= htmlspecialchars($dashboard_card['updated_label']) ?></span>
                                </div>
                                <div class="tracked-dashboard-price"><?= htmlspecialchars($dashboard_card['price_label']) ?></div>
                            </div>
                            <div class="tracked-dashboard-gridline">
                                <div class="tracked-dashboard-stat">
                                    <span>Position</span>
                                    <strong><?= htmlspecialchars($dashboard_card['position_label']) ?></strong>
                                </div>
                                <div class="tracked-dashboard-stat">
                                    <span>Signal</span>
                                    <strong><?= htmlspecialchars($dashboard_card['signal_label']) ?></strong>
                                </div>
                                <div class="tracked-dashboard-stat">
                                    <span>Pot</span>
                                    <strong><?= htmlspecialchars($dashboard_card['equity_label']) ?></strong>
                                </div>
                                <div class="tracked-dashboard-stat">
                                    <span>Sim net</span>
                                    <strong class="<?= htmlspecialchars($dashboard_card['net_class']) ?>"><?= htmlspecialchars($dashboard_card['net_label']) ?></strong>
                                </div>
                                <div class="tracked-dashboard-stat">
                                    <span>Trust</span>
                                    <strong><?= htmlspecialchars($dashboard_card['trust_label']) ?></strong>
                                </div>
                                <div class="tracked-dashboard-stat">
                                    <span>Accuracy</span>
                                    <strong><?= htmlspecialchars($dashboard_card['accuracy_label']) ?></strong>
                                </div>
                                <div class="tracked-dashboard-stat">
                                    <span>Buy x avg</span>
                                    <strong><?= htmlspecialchars($dashboard_card['buy_label']) ?></strong>
                                </div>
                                <div class="tracked-dashboard-stat">
                                    <span>Sell x avg</span>
                                    <strong><?= htmlspecialchars($dashboard_card['sell_label']) ?></strong>
                                </div>
                            </div>
                            <div class="tracked-dashboard-bottom">
                                <span><?= htmlspecialchars($dashboard_card['last_trade_label']) ?></span>
                                <?php if ($dashboard_card['active']): ?>
                                    <span class="tracked-dashboard-active-chip">Open page</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <form class="control-panel" method="get" action="">
        <div class="field">
            <label>Market type</label>
            <div class="market-types">
                <label class="market-choice">
                    <input type="radio" name="market_type" value="crypto" <?= $market_type === 'crypto' ? 'checked' : '' ?>>
                    Crypto
                </label>
                <label class="market-choice">
                    <input type="radio" name="market_type" value="stock" <?= $market_type === 'stock' ? 'checked' : '' ?>>
                    Stock
                </label>
            </div>
        </div>
        <div class="field">
            <label for="symbol">Market symbol for this study</label>
            <div class="input-wrap">
                <span class="input-prefix">$</span>
                <input id="symbol" name="symbol" type="text" value="<?= htmlspecialchars($ticker) ?>" maxlength="12" autocomplete="off" spellcheck="false">
            </div>
        </div>
        <div class="field threshold-field">
            <label for="buyMultiplier">Buy multiplier</label>
            <div class="input-wrap">
                <input id="buyMultiplier" name="buy_multiplier" type="number" min="0.10" max="5.00" step="0.05" value="<?= number_format($buy_multiplier, 2, '.', '') ?>">
                <span class="input-prefix">x avg</span>
            </div>
        </div>
        <div class="field threshold-field">
            <label for="sellMultiplier">Sell multiplier</label>
            <div class="input-wrap">
                <input id="sellMultiplier" name="sell_multiplier" type="number" min="0.10" max="5.00" step="0.05" value="<?= number_format($sell_multiplier, 2, '.', '') ?>">
                <span class="input-prefix">x avg</span>
            </div>
        </div>
        <div class="field threshold-field">
            <label for="trustPercent">Trust percent</label>
            <div class="input-wrap">
                <input id="trustPercent" name="trust_percent" type="number" min="1" max="100" step="0.5" value="<?= number_format($trust_percent, 2, '.', '') ?>">
                <span class="input-prefix">% conf</span>
            </div>
        </div>
        <input type="hidden" name="break_buy" value="<?= number_format($break_buy_drop_pct, 2, '.', '') ?>">
        <input type="hidden" name="break_gain" value="<?= number_format($break_take_gain_pct, 2, '.', '') ?>">
        <input type="hidden" name="break_loss" value="<?= number_format($break_stop_loss_pct, 2, '.', '') ?>">
        <button type="submit" name="run_analysis" value="1">Analyze ticker</button>
    </form>
    <form class="control-panel" method="post" action="">
        <input type="hidden" name="market_type" value="<?= htmlspecialchars($market_type) ?>">
        <input type="hidden" name="symbol" value="<?= htmlspecialchars($ticker) ?>">
        <label for="walletResetPassword">Wallet reset password</label>
        <input id="walletResetPassword" name="wallet_reset_password" type="password" autocomplete="current-password" required>
        <button type="submit" name="reset_wallet" value="1" class="secondary-action">Reset paper wallet</button>
    </form>

    <?php if ($error_message !== ''): ?>
        <section class="error-panel">
            <strong>Unable to load <?= htmlspecialchars($ticker) ?>.</strong>
            <?= htmlspecialchars($error_message) ?>
        </section>
        <?php endif; ?>
    <?php if ($wallet_reset_error !== ''): ?>
        <section class="error-panel">
            <strong>Wallet was not reset.</strong>
            <?= htmlspecialchars($wallet_reset_error) ?>
        </section>
    <?php endif; ?>

    <section class="market-pulse-metrics">
        <article class="metric market-pulse-card summary-card summary-card--market">
            <div class="card-topline">
                <div>
                    <span class="metric-label">Market now</span>
                    <p class="card-kicker">Hourly spot pulse</p>
                </div>
                <span id="marketSourceChip" class="card-chip <?= $current_price_source === 'YAHOO' ? 'is-live' : 'is-file' ?>"><?= htmlspecialchars($market_source_chip_label) ?></span>
            </div>
            <div class="card-main">
                <div id="currentPriceValue" class="metric-value <?= $current_price_class ?>">$<?= number_format($current_price, 2) ?></div>
                <div id="currentPriceNote" class="metric-note metric-note--strong"><?= htmlspecialchars($current_price_note) ?></div>
            </div>
            <div class="card-grid card-grid--market">
                <div class="summary-stat">
                    <span>1H reference</span>
                    <strong id="marketReferenceValue"><?= htmlspecialchars($market_reference_label) ?></strong>
                </div>
                <div class="summary-stat">
                    <span>Latest source</span>
                    <strong id="marketFeedValue"><?= htmlspecialchars($market_feed_label) ?></strong>
                </div>
            </div>
            <div id="marketDataNote" class="card-footnote"><?= htmlspecialchars($market_update_note) ?></div>
        </article>

        <article class="metric market-pulse-card summary-card summary-card--wallet">
            <div class="card-topline">
                <div>
                    <span class="metric-label">Wallet now</span>
                    <p class="card-kicker">50/50 paper position</p>
                </div>
                <span id="walletSeedChip" class="card-chip is-accent"><?= htmlspecialchars($wallet_seed_label) ?></span>
            </div>
            <div class="card-main">
                <div id="autoBreakValue" class="metric-value strategy-value <?= $paper_break_class ?>"><?= htmlspecialchars($paper_break_value_label) ?></div>
                <div id="autoBreakNote" class="metric-note metric-note--strong"><?= htmlspecialchars($paper_break_note) ?></div>
            </div>
            <div class="card-grid card-grid--wallet">
                <div class="summary-stat">
                    <span>POT</span>
                    <strong id="walletPotValue">$<?= number_format($paper_break_equity, 2) ?></strong>
                </div>
                <div class="summary-stat">
                    <span><?= htmlspecialchars($paper_break_asset_left_label) ?></span>
                    <strong id="walletAssetValue"><?= htmlspecialchars($paper_break_asset_left_amount) ?></strong>
                </div>
                <div class="summary-stat">
                    <span>Holding</span>
                    <strong id="walletHoldingValue">$<?= number_format($paper_break_holding_value, 2) ?></strong>
                </div>
                <div class="summary-stat">
                    <span>Cash left</span>
                    <strong id="walletCashValue">$<?= number_format($paper_break_cash_left, 2) ?></strong>
                </div>
                <div class="summary-stat">
                    <span>Net equity move</span>
                    <strong id="walletNetMoveValue"><?= ($paper_break_sim_net_move >= 0 ? '+' : '-') . '$' . number_format(abs($paper_break_sim_net_move), 2) ?></strong>
                </div>
                <div class="summary-stat">
                    <span>Realized P&amp;L</span>
                    <strong id="walletRealizedValue"><?= ($paper_break_realized_move >= 0 ? '+' : '-') . '$' . number_format(abs($paper_break_realized_move), 2) ?></strong>
                </div>
                <div class="summary-stat">
                    <span>Open P&amp;L</span>
                    <strong id="walletOpenPnlValue"><?= ($paper_break_open_pnl >= 0 ? '+' : '-') . '$' . number_format(abs($paper_break_open_pnl), 2) ?></strong>
                </div>
                <div class="summary-stat">
                    <span>Bell-curve hour</span>
                    <strong id="walletBellCurveValue"><?= htmlspecialchars($paper_break_bell_curve_active
                        ? ($paper_break_bell_curve_action . ' • ' . number_format($paper_break_bell_curve_trust, 1) . '% • $' . number_format($paper_break_bell_curve_total_requested, 2))
                        : 'Inactive') ?></strong>
                </div>
            </div>
            <div class="summary-ribbon">
                <span id="walletBoughtValue"><?= htmlspecialchars($paper_break_asset_bought_label . ' ' . $paper_break_asset_bought_amount . ' ($' . number_format($paper_break_bought_amount, 2) . ')') ?></span>
                <span id="walletSoldValue"><?= htmlspecialchars($paper_break_asset_sold_label . ' ' . $paper_break_asset_sold_amount . ' ($' . number_format($paper_break_sold_amount, 2) . ')') ?></span>
                <span id="walletPositionValue"><?= htmlspecialchars('POSITION ' . $paper_break_position) ?></span>
                <span id="walletLastTradeValue"><?= htmlspecialchars($wallet_last_label) ?></span>
            </div>
            <div id="walletSeedDetail" class="card-footnote card-footnote--seed"><?= htmlspecialchars($wallet_seed_detail_label) ?></div>
        </article>

        <article class="metric market-pulse-card summary-card summary-card--signal">
            <div class="card-topline">
                <div>
                    <span class="metric-label">Model now</span>
                    <p class="card-kicker">Current 1-hour-ahead stance</p>
                </div>
                <span id="modelResolutionChip" class="card-chip <?= $current_guess_pair_label === '%' ? 'is-neutral' : 'is-warning' ?>"><?= htmlspecialchars($model_resolution_label) ?></span>
            </div>
            <div class="card-main">
                <div id="currentGuessValue" class="metric-value signal-value <?= $current_guess_action_class ?>"><?= htmlspecialchars($current_guess_action) ?></div>
                <div id="currentGuessNote" class="metric-note metric-note--strong"><?= htmlspecialchars($current_guess_note) ?></div>
            </div>
            <div class="card-grid card-grid--signal">
                <div class="summary-stat">
                    <span>Pair</span>
                    <strong id="modelPairValue"><?= htmlspecialchars($model_pair_label) ?></strong>
                </div>
                <div class="summary-stat">
                    <span>Carry-forward</span>
                    <strong id="modelCarryValue" class="<?= $accuracy_class ?>"><?= htmlspecialchars($model_carry_value) ?></strong>
                </div>
                <div class="summary-stat">
                    <span>W / L</span>
                    <strong id="modelWinLossValue"><?= htmlspecialchars($model_wl_label) ?></strong>
                </div>
                <div class="summary-stat">
                    <span>Last result</span>
                    <strong id="modelLastValue"><?= htmlspecialchars($wallet_last_label) ?></strong>
                </div>
                <div class="summary-stat">
                    <span>1h audit</span>
                    <strong id="modelHourAuditValue"><?= (int)$hour_audit_guess['right'] ?>/<?= (int)$hour_audit_guess['right'] + (int)$hour_audit_guess['wrong'] ?> • <?= number_format((float)$hour_audit_guess['percent'], 1) ?>%</strong>
                </div>
                <div class="summary-stat">
                    <span>Phase status</span>
                    <strong id="phaseStatusValue"><?= htmlspecialchars((string)($current_phase_status['action'] ?? 'HOLD')) ?> • REQUESTED $<?= number_format((float)$current_phase_sizing['requested_amount'], 2) ?> • EXEC $<?= number_format((float)$current_phase_sizing['executable_amount'], 2) ?><?= (float)$current_phase_sizing['shortfall'] > 0.00000001 ? ' • SHORT $' . number_format((float)$current_phase_sizing['shortfall'], 2) : '' ?> • REGIME HOUR <?= (int)$regime_step_count ?> / 12 • <?= $current_phase_status['steps_until_change'] === null ? 'NO CHANGE IN HORIZON' : (int)$current_phase_status['steps_until_change'] . ' STEPS LEFT' ?></strong>
                </div>
                <div class="summary-stat">
                    <span>Quarter/regime BUY gate</span>
                    <strong id="quarterRegimeGateValue"><?= $quarter_regime_inverted ? 'INVERTED / HOLD' : ($quarter_regime_buy_allowed ? 'OPEN' : 'HOLD') ?> • <?= htmlspecialchars($current_quarter_regime_key) ?> • <?= (int)($current_quarter_regime['right'] ?? 0) ?>/<?= (int)($current_quarter_regime['total'] ?? 0) ?></strong>
                </div>
            </div>
            <div id="modelCarryNote" class="card-footnote"><?= htmlspecialchars($accuracy_note . ' • last hour audit ' . (int)$hour_audit_guess['right'] . '/' . ((int)$hour_audit_guess['right'] + (int)$hour_audit_guess['wrong']) . ' • ' . number_format((float)$hour_audit_guess['percent'], 1) . '%') ?></div>
        </article>
    </section>

    <section class="pair-stats" aria-label="Secondary analytics">
        <article class="metric pair-card pair-card--carry">
            <span class="metric-label">Loop 2 carry-forward</span>
            <div id="accuracyValue" class="metric-value <?= $accuracy_class ?>"><?= htmlspecialchars($model_carry_value) ?></div>
            <div id="accuracyNote" class="metric-note"><?= htmlspecialchars($accuracy_note) ?></div>
        </article>
        <article class="metric pair-card">
            <span class="metric-label">Internal agreement</span>
            <div id="internalAgreementValue" class="metric-value <?= $internal_agreement_class ?>"><?= htmlspecialchars($internal_agreement_value_label) ?></div>
            <div id="internalAgreementNote" class="metric-note"><?= htmlspecialchars($internal_agreement_note . ' • ' . ((int)$internal_agreement_total - (int)$internal_agreement_right) . ' WRONG / ' . (int)$internal_agreement_total . ' TOTAL') ?></div>
        </article>
        <article class="metric pair-card">
            <span class="metric-label">Historical family confidence</span>
            <div id="familyConfidenceValue" class="metric-value <?= $current_family_confidence_class ?>"><?= htmlspecialchars($current_family_confidence_label) ?></div>
            <div id="familyConfidenceNote" class="metric-note"><?= htmlspecialchars($current_family_confidence_key . ' • HISTORICAL ' . number_format($current_family_historical_percentage, 1) . '% • ' . (int)$current_family_confidence['right'] . ' RIGHT / ' . ((int)$current_family_confidence['total'] - (int)$current_family_confidence['right']) . ' WRONG / ' . (int)$current_family_confidence['total'] . ' TOTAL' . ($current_family_effective_flip ? ' • EFFECTIVE ' . number_format($current_family_effective_percentage, 1) . '% • FLIPPED' : '')) ?></div>
        </article>
        <article class="metric pair-card">
            <span class="metric-label">Pattern compression</span>
            <div id="compressionValue" class="metric-value <?= $compression_class ?>"><?= htmlspecialchars($compression_value_label) ?></div>
            <div id="compressionNote" class="metric-note"><?= htmlspecialchars($compression_note) ?></div>
        </article>
        <article class="metric pair-card">
            <span class="metric-label">Phase-change wins</span>
            <div id="phaseChangeValue" class="metric-value medium">BUY <?= number_format((float)($phase_action_stats['BUY']['percentage'] ?? 0.0), 1) ?>% · SELL <?= number_format((float)($phase_action_stats['SELL']['percentage'] ?? 0.0), 1) ?>%</div>
            <div id="phaseChangeNote" class="metric-note">BUY <?= (int)($phase_action_stats['BUY']['right'] ?? 0) ?> RIGHT / <?= (int)($phase_action_stats['BUY']['total'] ?? 0) ?> TOTAL • SELL <?= (int)($phase_action_stats['SELL']['right'] ?? 0) ?> RIGHT / <?= (int)($phase_action_stats['SELL']['total'] ?? 0) ?> TOTAL</div>
        </article>
        <?php foreach ($pair_card_ids as $pair_symbol => $pair_card_id): ?>
            <?php $pair_stat = $action_stats[$pair_symbol] ?? ['percentage' => 0.0, 'right' => 0, 'total' => 0]; ?>
            <?php $pair_class = $pair_stat['percentage'] >= 65 ? 'good' : ($pair_stat['percentage'] >= 50 ? 'medium' : 'low'); ?>
            <article id="pairCard<?= htmlspecialchars($pair_card_id) ?>" class="metric pair-card">
                <span class="metric-label"><?= htmlspecialchars($pair_card_titles[$pair_symbol] ?? $pair_symbol) ?></span>
                <div class="metric-value <?= $pair_class ?>" data-pair-percentage><?= (int)$pair_stat['total'] > 0 ? number_format((float)$pair_stat['percentage'], 1) . '%' : '—' ?></div>
                <div class="metric-note" data-pair-count><?= (int)$pair_stat['right'] ?> RIGHT / <?= (int)($pair_stat['wrong'] ?? ((int)$pair_stat['total'] - (int)$pair_stat['right'])) ?> WRONG / <?= (int)$pair_stat['total'] ?> TOTAL</div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="analysis-grid">
        <section class="results-panel">
            <div class="results-header">
                <div>
                    <h2>Trade ledger</h2>
                    <p>1-hour-ahead row unresolved • forward rows hypothetical • resolved rows scored against observed closes</p>
                </div>
                <div class="header-chips">
                    <span class="panel-chip">Hourly console</span>
                    <span class="panel-chip">Wallet-aware outcomes</span>
                </div>
            </div>
            <div class="table-scroll">
                <table id="liveGuessTable" aria-label="Timestamp-locked model signals and observed trade outcomes"><?= $visible_rows_html ?></table>
            </div>
            <p class="table-disclaimer"><strong>Result column:</strong> REAL shows the wallet's simulated outcome. Each BUY/SELL signal represents one decision per hour; no EST dollar projection is shown.</p>
        </section>

        <section class="results-panel">
            <div class="results-header">
                <div>
                    <h2>Last hour audit</h2>
                    <p>Locked guesses versus the last 12 completed five-minute candles, shown in America/New_York time.</p>
                </div>
                <div class="header-chips">
                    <span class="panel-chip">Guess <?= (int)$hour_audit_guess['right'] ?>/<?= (int)$hour_audit_guess['right'] + (int)$hour_audit_guess['wrong'] ?> • <?= number_format((float)$hour_audit_guess['percent'], 1) ?>%</span>
                    <span class="panel-chip">Strategy <?= ($hour_audit_strategy['net_pnl'] >= 0 ? '+' : '-') . '$' . number_format(abs((float)$hour_audit_strategy['net_pnl']), 2) ?></span>
                    <span class="panel-chip">Long <?= ($hour_audit_long['net_pnl'] >= 0 ? '+' : '-') . '$' . number_format(abs((float)$hour_audit_long['net_pnl']), 2) ?></span>
                    <span class="panel-chip">Short <?= ($hour_audit_short['net_pnl'] >= 0 ? '+' : '-') . '$' . number_format(abs((float)$hour_audit_short['net_pnl']), 2) ?></span>
                    <span class="panel-chip">Best <?= ($hour_audit_best['net_pnl'] >= 0 ? '+' : '-') . '$' . number_format(abs((float)$hour_audit_best['net_pnl']), 2) ?></span>
                    <span id="selloffTipChip" class="panel-chip">Selloff tip <?= $selloff_tip ? 'ON' : 'OFF' ?> • SELL run <?= (int)($hour_audit_sequences['current_sell_signal_streak'] ?? 0) ?></span>
                </div>
            </div>
            <div class="table-scroll">
                <table id="hourAuditTable" aria-label="Last hour audit of locked guesses against completed five-minute candles"><?= $hour_audit_table_html ?></table>
            </div>
            <p class="table-disclaimer"><strong>Audit convention:</strong> Strategy uses the saved guess action only. LONG and SHORT are forced-side comparisons. BEST is hindsight only and should not be treated as real strategy performance.</p>
        </section>

        <section class="chart-panel">
            <div class="results-header results-header--chart">
                <div>
                    <h2 id="chartHeading"><?= $chart_actual_count ?> real hourly candles • <?= $chart_forecast_count ?> combined forecast candles</h2>
                    <p>Hourly candlesticks only • forecast overlays stay locked to the saved model timeline</p>
                </div>
                <div class="header-chips">
                    <span id="chartStats" class="panel-chip">Price range <?= number_format($chart_price_min, 2) ?>–<?= number_format($chart_price_max, 2) ?> • Average move <?= number_format($average_change, 2) ?></span>
                </div>
            </div>
            <div class="chart-stage">
                <canvas id="candleChart" aria-label="Chart with <?= $chart_actual_count ?> real hourly candles and <?= $chart_forecast_count ?> combined forecast candlesticks"></canvas>
                <div class="trade-overlay">
                    <span id="averageMove">AVG MOVE <?= $average_change >= 0 ? '+' : '' ?>$<?= number_format($average_change, 2) ?></span>
                    <span id="paperProfit" style="color:<?= $paper_profit >= 0 ? 'var(--accent)' : 'var(--danger)' ?>">SIM NET MOVE <?= $paper_profit >= 0 ? '+' : '' ?>$<?= number_format($paper_profit, 2) ?></span>
                </div>
            </div>
            <div id="chartTimeStamp" class="chart-timestamp">Local hourly chart window loading…</div>
        </section>
    </section>

    <section class="browser-width-section math-review-section" id="model-reference" aria-labelledby="model-mathematics-title">
        <div class="browser-width-inner">
            <details class="reference-drawer">
                <summary><span id="model-mathematics-title">CNGN model formula reference</span></summary>
                <div class="reference-drawer-body">
                    <div class="reference-drawer-head">
                        <div>
                            <h2>CNGN model formula reference</h2>
                        </div>
                        <button type="button" class="reference-close-btn" data-close-reference aria-label="Close model notes">×</button>
                    </div>
                    <p class="review-intro">
                        This keeps the raw formula and recurrence notes available without leaving the
                        main console cluttered.
                    </p>
                    <div class="math-review-grid">
                <article class="math-card">
                    <h3>1. Sequence row and timestamp</h3>
                    <p>
                        Let one working row be \(\mathbf q_n=(s_n,u_n,v_n,T_n)\). The CSV builder
                        copies the prior close into both price slots:
                    </p>
                    \[
                    \mathbf q_n=
                    \bigl(1+n\Delta s,\;C_{n-1},\;C_{n-1},\;T_n\bigr),
                    \qquad \Delta s=\texttt{\$day\_cnt},
                    \qquad n\ge 1.
                    \]
                    <p>
                        So on the rows consumed directly from the CSV, \(u_n=v_n\). Loop 2 stamps
                        projected rows with the next five-minute boundaries:
                    </p>
                    \[
                    \tau_x=
                    300\left\lfloor\frac{t_{\mathrm{now}}}{300}\right\rfloor
                    +300(x+1),
                    \qquad x=0,\ldots,48.
                    \]
                    \[
                    \mathbf q_x^{+}=(s_x+\Delta s,\;u_x,\;L_x,\;\tau_x).
                    \]
                    <p>
                        The code appends \(\mathbf q_x^{+}\) to <code>$seq</code>, but
                        <code>$future_seq_start</code> is frozen before loop 2 begins, so that
                        same 49-step pass still scores the seeded slice while appending those rows.
                    </p>
                </article>

                <article class="math-card">
                    <h3>2. Integrand, integral, and differential</h3>
                    \[
                    d(\mathbf q)=\left|
                    \operatorname{trunc}(v)-\operatorname{trunc}(u)
                    \right|
                    \]
                    \[
                    G(\mathbf q)=\frac{s}{2}+\frac{3}{2}d(\mathbf q)-1
                    \qquad\text{(integrand)}
                    \]
                    \[
                    I(a,b,c)=
                    \bigl(a+b+c\bigr)\left(\frac{a+b+c}{3}\right)
                    =\frac{(a+b+c)^2}{3}
                    \qquad\text{(the actual three-term integral call)}
                    \]
                    <p>
                        Inside <code>differential()</code> the code integrates the three-term vector
                        \((s,G,G)\), not the raw four-field row:
                    </p>
                    \[
                    J(\mathbf q)=I\bigl(s,G(\mathbf q),G(\mathbf q)\bigr)
                    =\frac{\bigl(s+2G(\mathbf q)\bigr)^2}{3}.
                    \]
                    \[
                    W(\mathbf q)=\sqrt{3J(\mathbf q)},\qquad
                    S(\mathbf q)=W(\mathbf q)-2G(\mathbf q),
                    \]
                    \[
                    D(\mathbf q)=\frac{S(\mathbf q)}{G(\mathbf q)+1}.
                    \]
                    <p>
                        On the positive rows used here, \(W(\mathbf q)=s+2G(\mathbf q)\), so
                    </p>
                    \[
                    S(\mathbf q)=s,\qquad
                    D(\mathbf q)=\frac{s}{G(\mathbf q)+1}
                    =\frac{2s}{s+3d(\mathbf q)}.
                    \]
                    <p>
                        Because the seeded CSV rows satisfy \(u=v\), they satisfy \(d(\mathbf q)=0\),
                        so the implemented differential really becomes
                    </p>
                    \[
                    G(\mathbf q)=\frac{s}{2}-1,\qquad
                    D(\mathbf q)=\frac{s}{s/2}=2.
                    \]
                    <p>
                        The same integral also drives the wall comparison:
                    </p>
                    \[
                    P(\mathbf q)=\frac{G(\mathbf q)}{W(\mathbf q)},\qquad
                    \beta(\mathbf q)=P(\mathbf q)-\frac14.
                    \]
                    <p>
                        On duplicated-price rows this simplifies to
                    </p>
                    \[
                    P(\mathbf q)=\frac{s-2}{4s-4},
                    \qquad
                    \beta(\mathbf q)=-\frac{1}{4s-4}.
                    \]
                </article>

                <article class="math-card">
                    <h3>3. Implemented derive function</h3>
                    <p>For a working vector \(\mathbf w=(a,b,c,r)\), define</p>
                    \[
                    e(\mathbf w)=
                    \left|\operatorname{trunc}(c)-\operatorname{trunc}(b)\right|.
                    \]
                    <p>The PHP <code>derive()</code> result is</p>
                    \[
                    H(\mathbf w)=
                    \frac{r}{\,a/r+(3/2)e(\mathbf w)\,}.
                    \]
                    <p>Both loops call it with the same working vector:</p>
                    \[
                    \mathbf w_x=
                    \bigl(s_x,u_x,v_x,G(\mathbf q_x)\bigr).
                    \]
                    <p>
                        On the duplicated historical rows, \(e(\mathbf w_x)=0\), hence
                    </p>
                    \[
                    H(\mathbf w_x)=\frac{G(\mathbf q_x)^2}{s_x}.
                    \]
                </article>

                <article class="math-card">
                    <h3>4. Low normalization and carry state</h3>
                    <p>The quantity named <code>$lo</code> is computed exactly as</p>
                    \[
                    \lambda(\mathbf q)=
                    \frac{H(\mathbf w)^2}
                    {2\,G(\mathbf q)\,D(\mathbf q)}.
                    \]
                    <p>The code then normalizes it by repeated \(1.01\) multiplication until it exceeds \(0.999\):</p>
                    \[
                    \widehat{\lambda}(\mathbf q)=1.01^{k}\lambda(\mathbf q),
                    \qquad
                    k=\min\{m\in\mathbb N_0:1.01^m\lambda(\mathbf q)>0.999\}.
                    \]
                    <p>The intermediate price expression is</p>
                    \[
                    M(\mathbf q)=
                    \widehat{\lambda}(\mathbf q)\frac{\operatorname{trunc}(v)}{10}
                    -\operatorname{trunc}\!\bigl(G(\mathbf q)\bigr).
                    \]
                    <p>
                        Let the live carry state at that row be \(B=\texttt{\$base}\),
                        \(O=\texttt{\$out}\), and \(E=\texttt{\$exp}\). Then the emitted low is
                    </p>
                    \[
                    L(\mathbf q)=
                    B+2\,\operatorname{round}\!\left(\frac{M(\mathbf q)}{O},\,2\right)-E.
                    \]
                    <p>
                        In loop 1, \(B=0\). Before loop 2 starts, the code sets \(B\) once to the
                        final historical low and then keeps it fixed. After each row it resets
                        \(E\leftarrow 1\) and updates the magnitude bucket while
                        \(L(\mathbf q)>10^E\) and \(E&lt;3\), setting \(O\leftarrow 10^E\) before
                        incrementing \(E\).
                    </p>
                </article>

                <article class="math-card wide">
                    <h3>5. Historical seeds, loop-2 signs, and scoring</h3>
                    <p>
                        Loop 1 iterates from the newest stored row down to the oldest. On the very
                        first historical iteration it stores the future seeds exactly once:
                    </p>
                    \[
                    (\ell_\star,r_\star,L_\star,\beta_\star)
                    =
                    (\texttt{\$bool1},\texttt{\$bool2},\texttt{\$short\_low},\texttt{\$wall\_bias}).
                    \]
                    <p>
                        Loop 2 begins with those seeds. At \(x=48\) it uses
                        \((\ell_\star,r_\star)\). On later loop-2 rows, the signs are the direct
                        row-to-row comparisons used in the PHP:
                    </p>
                    \[
                    \ell_x=
                    \begin{cases}
                    -,&L_x<L_{\mathrm{prev}},\\
                    +,&\text{otherwise},
                    \end{cases}
                    \qquad
                    r_x^{(0)}=
                    \begin{cases}
                    -,&\beta_x<\beta_{\mathrm{prev}},\\
                    +,&\text{otherwise}.
                    \end{cases}
                    \]
                    <p>
                        Only loop 2 applies the odd-boundary right-side flip:
                    </p>
                    \[
                    r_x=
                    \begin{cases}
                    -\,r_x^{(0)},&
                    \left\lfloor t_{\mathrm{now}}/300\right\rfloor\bmod 2=1,\\
                    r_x^{(0)},&\text{otherwise}.
                    \end{cases}
                    \]
                    <p>The pair stored in the row metadata is</p>
                    \[
                    p_x=\ell_x r_x.
                    \]
                    <p>
                        The loop-2 percentage returned by <code>bitcoin()</code> is not a market
                        P/L statistic; it is the direct equality test used in the code:
                    </p>
                    \[
                    \mathrm{score}_x=\mathbf 1[r_x=\ell_x].
                    \]
                </article>

                <article class="math-card">
                    <h3>6. Displayed cell versus stored pair</h3>
                    <p>
                        The historical HTML cell does not print the full pair. It prints a single
                        character derived from the already computed pair:
                    </p>
                    \[
                    g_x=
                    \begin{cases}
                    \%,&(\ell_x,r_x)=(-,-),\\
                    \ell_x,&\text{otherwise}.
                    \end{cases}
                    \]
                    <p>
                        So the visible symbol and the stored pair are different objects: the chart
                        and the later locking logic use \(p_x=\ell_x r_x\), while the original row
                        text shows only \(g_x\).
                    </p>
                </article>

                <article class="math-card">
                    <h3>7. Signal interpretation and immutable locking</h3>
                    \[
                    A(p)=
                    \begin{cases}
                    \mathrm{BUY},&p\in\{++,--\},\\
                    \mathrm{SELL},&p\in\{-+,+-\},\\
                    \mathrm{NO\ TRADE},&p=\%.
                    \end{cases}
                    \]
                    <p>
                        This is the active opposite-family mapping: \(++\mapsto\mathrm{BUY}\),
                        \(--\mapsto\mathrm{BUY}\), while \(-+\) and \(+-\) map to \(\mathrm{SELL}\). Every newly seen
                        timestamp is written once. Existing timestamp keys are returned unchanged,
                        so later hourly ticker rebuilds do not repair or replace an already locked pair:
                    </p>
                    \[
                    F(T)=
                    \begin{cases}
                    p_T,&T\notin\operatorname{dom}(F),\\
                    F(T),&T\in\operatorname{dom}(F).
                    \end{cases}
                    \]
                    \[
                    \mathrm{RIGHT}(T)=
                    \mathbf 1\!\left[
                    A(F(T))=\operatorname{direction}_{\mathrm{observed}}(T)
                    \right].
                    \]
                </article>
            </div>

            <details class="latex-source" id="latex-formula">
                <summary>Copy the complete LaTeX review source</summary>
                <pre>\[
\mathbf q_n=(s_n,u_n,v_n,T_n)
\]
\[
\mathbf q_n=
\bigl(1+n\Delta s,\;C_{n-1},\;C_{n-1},\;T_n\bigr),
\qquad \Delta s=\texttt{\$day\_cnt},
\qquad n\ge 1
\]
\[
\tau_x=
300\left\lfloor\frac{t_{\mathrm{now}}}{300}\right\rfloor+300(x+1),
\qquad x=0,\ldots,48
\]
\[
\mathbf q_x^{+}=(s_x+\Delta s,\;u_x,\;L_x,\;\tau_x)
\]
\[
d(\mathbf q)=\left|\operatorname{trunc}(v)-\operatorname{trunc}(u)\right|
\]
\[
G(\mathbf q)=\frac{s}{2}+\frac{3}{2}d(\mathbf q)-1
\]
\[
I(a,b,c)=\frac{(a+b+c)^2}{3}
\]
\[
J(\mathbf q)=I\bigl(s,G(\mathbf q),G(\mathbf q)\bigr)
=\frac{\bigl(s+2G(\mathbf q)\bigr)^2}{3}
\]
\[
W(\mathbf q)=\sqrt{3J(\mathbf q)},
\qquad
S(\mathbf q)=W(\mathbf q)-2G(\mathbf q)
\]
\[
D(\mathbf q)=\frac{S(\mathbf q)}{G(\mathbf q)+1}
\]
\[
W(\mathbf q)=s+2G(\mathbf q)\Longrightarrow
S(\mathbf q)=s\Longrightarrow
D(\mathbf q)=\frac{s}{G(\mathbf q)+1}
=\frac{2s}{s+3d(\mathbf q)}
\]
\[
u=v\Longrightarrow
G(\mathbf q)=\frac{s}{2}-1,
\qquad
D(\mathbf q)=\frac{s}{s/2}=2
\]
\[
P(\mathbf q)=\frac{G(\mathbf q)}{W(\mathbf q)},
\qquad
\beta(\mathbf q)=P(\mathbf q)-\frac14
\]
\[
u=v\Longrightarrow
P(\mathbf q)=\frac{s-2}{4s-4},
\qquad
\beta(\mathbf q)=-\frac{1}{4s-4}
\]
\[
e(\mathbf w)=\left|\operatorname{trunc}(c)-\operatorname{trunc}(b)\right|,
\qquad
H(\mathbf w)=\frac{r}{a/r+(3/2)e(\mathbf w)}
\]
\[
\mathbf w_x=(s_x,u_x,v_x,G(\mathbf q_x))
\]
\[
u=v\Longrightarrow
H(\mathbf w_x)=\frac{G(\mathbf q_x)^2}{s_x}
\]
\[
\lambda(\mathbf q)=
\frac{H(\mathbf w)^2}{2G(\mathbf q)D(\mathbf q)},
\qquad
\widehat{\lambda}(\mathbf q)=1.01^k\lambda(\mathbf q)
\]
\[
M(\mathbf q)=
\widehat{\lambda}(\mathbf q)\frac{\operatorname{trunc}(v)}{10}
-\operatorname{trunc}(G(\mathbf q))
\]
\[
L(\mathbf q)=
B+2\operatorname{round}\left(\frac{M(\mathbf q)}{O},2\right)-E
\]
\[
(\ell_\star,r_\star,L_\star,\beta_\star)
=(\texttt{\$bool1},\texttt{\$bool2},\texttt{\$short\_low},\texttt{\$wall\_bias})
\]
\[
\ell_x=
\begin{cases}
-,&L_x<L_{\mathrm{prev}},\\
+,&\text{otherwise},
\end{cases}
\qquad
r_x^{(0)}=
\begin{cases}
-,&\beta_x<\beta_{\mathrm{prev}},\\
+,&\text{otherwise}.
\end{cases}
\]
\[
r_x=
\begin{cases}
-\,r_x^{(0)},&\lfloor t_{\mathrm{now}}/300\rfloor\bmod2=1,\\
r_x^{(0)},&\text{otherwise},
\end{cases}
\qquad
p_x=\ell_x r_x
\]
\[
g_x=
\begin{cases}
\%,&(\ell_x,r_x)=(-,-),\\
\ell_x,&\text{otherwise}
\end{cases}
\]
\[
\mathrm{score}_x=\mathbf 1[r_x=\ell_x]
\]
\[
A(p)=
\begin{cases}
\mathrm{BUY},&p\in\{--,-+,+-\},\\
\mathrm{SELL},&p=++,\\
\mathrm{NO\ TRADE},&p=\%.
\end{cases}
\]
\[
F(T)=
\begin{cases}
p_T,&T\notin\operatorname{dom}(F),\\
F(T),&T\in\operatorname{dom}(F).
\end{cases}
\]
\[
\mathrm{RIGHT}(T)=
\mathbf 1\!\left[
A(F(T))=\operatorname{direction}_{\mathrm{observed}}(T)
\right]
\]</pre>
            </details>
                    </div>
                </div>
            </details>
        </div>
    </section>

    <section class="disclosure-modal disclosure-section" id="site-disclaimer" aria-labelledby="disclosures-title">
        <div class="disclosure-dialog">
            <div class="disclosure-modal-head">
                <div>
                    <h2 id="disclosures-title">Hypothetical performance and educational simulation disclosures</h2>
                    <p class="modal-link-shell">Research and paper-trading only.</p>
                </div>
                <a class="modal-close-link" href="#" aria-label="Close disclaimer">×</a>
            </div>

            <div class="regulatory-copy">
                <p>THIS COMPOSITE PERFORMANCE RECORD IS HYPOTHETICAL AND THESE TRADING ADVISORS HAVE NOT TRADED TOGETHER IN THE MANNER SHOWN IN THE COMPOSITE. HYPOTHETICAL PERFORMANCE RESULTS HAVE MANY INHERENT LIMITATIONS, SOME OF WHICH ARE DESCRIBED BELOW. NO REPRESENTATION IS BEING MADE THAT ANY MULTI-ADVISOR MANAGED ACCOUNT OR POOL WILL OR IS LIKELY TO ACHIEVE A COMPOSITE PERFORMANCE RECORD SIMILAR TO THAT SHOWN. IN FACT, THERE ARE FREQUENTLY SHARP DIFFERENCES BETWEEN A HYPOTHETICAL COMPOSITE PERFORMANCE RECORD AND THE ACTUAL RECORD SUBSEQUENTLY ACHIEVED.</p>

                <p>ONE OF THE LIMITATIONS OF A HYPOTHETICAL COMPOSITE PERFORMANCE RECORD IS THAT DECISIONS RELATING TO THE SELECTION OF TRADING ADVISORS AND THE ALLOCATION OF ASSETS AMONG THOSE TRADING ADVISORS WERE MADE WITH THE BENEFIT OF HINDSIGHT BASED UPON THE HISTORICAL RATES OF RETURN OF THE SELECTED TRADING ADVISORS. THEREFORE, COMPOSITE PERFORMANCE RECORDS INVARIABLY SHOW POSITIVE RATES OF RETURN. ANOTHER INHERENT LIMITATION ON THESE RESULTS IS THAT THE ALLOCATION DECISIONS REFLECTED IN THE PERFORMANCE RECORD WERE NOT MADE UNDER ACTUAL MARKET CONDITIONS AND, THEREFORE, CANNOT COMPLETELY ACCOUNT FOR THE IMPACT OF FINANCIAL RISK IN ACTUAL TRADING. FURTHERMORE, THE COMPOSITE PERFORMANCE RECORD MAY BE DISTORTED BECAUSE THE ALLOCATION OF ASSETS CHANGES FROM TIME TO TIME AND THESE ADJUSTMENTS ARE NOT REFLECTED IN THE COMPOSITE.</p>

                <p>HYPOTHETICAL PERFORMANCE RESULTS HAVE MANY INHERENT LIMITATIONS, SOME OF WHICH ARE DESCRIBED BELOW. NO REPRESENTATION IS BEING MADE THAT ANY ACCOUNT WILL OR IS LIKELY TO ACHIEVE PROFITS OR LOSSES SIMILAR TO THOSE SHOWN. IN FACT, THERE ARE FREQUENTLY SHARP DIFFERENCES BETWEEN HYPOTHETICAL PERFORMANCE RESULTS AND THE ACTUAL RESULTS SUBSEQUENTLY ACHIEVED BY ANY PARTICULAR TRADING PROGRAM.</p>

                <p>ONE OF THE LIMITATIONS OF HYPOTHETICAL PERFORMANCE RESULTS IS THAT THEY ARE GENERALLY PREPARED WITH THE BENEFIT OF HINDSIGHT. IN ADDITION, HYPOTHETICAL TRADING DOES NOT INVOLVE FINANCIAL RISK, AND NO HYPOTHETICAL TRADING RECORD CAN COMPLETELY ACCOUNT FOR THE IMPACT OF FINANCIAL RISK IN ACTUAL TRADING. FOR EXAMPLE, THE ABILITY TO WITHSTAND LOSSES OR TO ADHERE TO A PARTICULAR TRADING PROGRAM IN SPITE OF TRADING LOSSES ARE MATERIAL POINTS WHICH CAN ALSO ADVERSELY AFFECT ACTUAL TRADING RESULTS. THERE ARE NUMEROUS OTHER FACTORS RELATED TO THE MARKETS IN GENERAL OR TO THE IMPLEMENTATION OF ANY SPECIFIC TRADING PROGRAM WHICH CANNOT BE FULLY ACCOUNTED FOR IN THE PREPARATION OF HYPOTHETICAL PERFORMANCE RESULTS AND ALL OF WHICH CAN ADVERSELY AFFECT ACTUAL TRADING RESULTS.</p>
            </div>

            <h3>EDUCATIONAL SIMULATION ONLY — NO LIVE TRADING</h3>
            <p>This livestream displays the CNGN model’s hypothetical five-minute signals for stocks and cryptocurrencies. No brokerage account is connected, no orders are sent, and no trades are executed.</p>

            <h3>SIGNAL KEY</h3>
            <div class="signal-key" aria-label="Simulation signal key">
                <span>++, -- = BUY signal family for the simulation</span>
                <span>-+, +- = SELL signal family for the simulation</span>
                <span>% = NO TRADE / unknown signal</span>
            </div>

            <p>The displayed simulated net move represents the average five-minute price movement per one asset unit. It is not actual account P/L, income, or a promise of profit. No fixed trade amount, account size, leverage, or position size is assumed.</p>

            <p>Hypothetical results have significant limitations. They do not include commissions, fees, spread, slippage, liquidity, latency, taxes, market impact, order-entry errors, borrowing costs, or execution risk. Forward and current signals are unresolved until the relevant market candle is complete.</p>

            <p>Past performance and simulated performance do not guarantee future results. This content is for education and research only. It is not investment advice, financial advice, personalized advice, a recommendation, an offer, or a solicitation to buy or sell any security, cryptocurrency, or other financial product.</p>

            <p>Stocks and cryptocurrencies involve risk, including the possible loss of principal. Do your own research and consult a qualified, licensed financial professional about your individual circumstances.</p>

            <p>Market data is sourced from the configured feed stack and scheduler cache, and may be delayed, incomplete, interrupted, or unavailable. Chart values and model outputs may contain errors. Times shown are converted to the viewer’s local time.</p>

            <p>This channel does not manage money, accept trading funds, provide brokerage services, or guarantee any result. Disclosure: I may receive compensation from links, sponsors, memberships, subscriptions, courses, or other services mentioned in this livestream.</p>
        </div>
    </section>

</main>
<script>
let candles = <?= json_encode($candle_chart, JSON_UNESCAPED_SLASHES) ?>;
const dashboardTimeZone = 'America/New_York';
let guessCandles = <?= json_encode($guess_candles, JSON_UNESCAPED_SLASHES) ?>;
let timeline = <?= json_encode($timeline, JSON_UNESCAPED_SLASHES) ?>;
let chartTimeline = <?= json_encode($chart_timeline, JSON_UNESCAPED_SLASHES) ?>;
let pairStats = <?= json_encode(array_values($action_stats), JSON_UNESCAPED_SLASHES) ?>;
let pairDirectionMap = <?= json_encode(activePairDirectionMap(), JSON_UNESCAPED_SLASHES) ?>;
let chartPriceMin = <?= json_encode($chart_price_min) ?>;
let chartPriceMax = <?= json_encode($chart_price_max) ?>;
let currentMarketType = <?= json_encode($market_type) ?>;
let currentTicker = <?= json_encode($ticker) ?>;
let liveSpotPrice = <?= json_encode($current_price) ?>;
let adaptiveCompleteFlip = <?= json_encode($adaptive_complete_flip) ?>;

function pairForGuess(guess) {
    const pair = String(guess?.pair || '');
    return /^[+-]{2}$/.test(pair) ? pair : String(guess?.symbol || '%');
}

function pairForRecord(record) {
    const explicit = String(record?.guessPair || '');
    if (/^[+-]{2}$/.test(explicit)) return explicit;
    const fromGuess = pairForGuess(record?.guess || {});
    return /^[+-]{2}$/.test(fromGuess) ? fromGuess : '';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function renderSignedSymbolHtmlClient(value) {
    const chars = Array.from(String(value ?? ''));
    return chars.map(char => {
        if (char === '+') return '<span class="signal-sign gain-sign">+</span>';
        if (char === '-') return '<span class="signal-sign loss-sign">-</span>';
        return escapeHtml(char);
    }).join('');
}

function actionForGuess(guess) {
    const pair = pairForGuess(guess);
    const explicit = String(guess?.action || guess?.guessAction || '').toUpperCase();
    const derivedDirection = /^[+-]{2}$/.test(pair)
        ? String(pairDirectionMap?.[pair] || '')
        : '';
    const lockedDirection = String(guess?.direction || '');
    // Historical/timestamp-locked guesses own their direction. The active map
    // is only a fallback for new unresolved guesses and must not change the
    // chart action without changing the scored prediction.
    let direction = lockedDirection === '+' || lockedDirection === '-'
        ? lockedDirection
        : (derivedDirection === '+' || derivedDirection === '-' ? derivedDirection : '');
    if (direction === '+') return 'BUY';
    if (direction === '-') return 'SELL';
    return /^(BUY|SELL)$/.test(explicit) ? explicit : 'NO TRADE';
}

function actionForRecord(record) {
    const explicit = String(record?.guessAction || record?.guess?.action || '');
    if (/^(BUY|SELL|NO TRADE)$/.test(explicit)) return explicit;
    return actionForGuess(record?.guess || {});
}

function colorForGuessAction(action) {
    return action === 'BUY' ? '#62a8ff' : (action === 'SELL' ? '#f59e0b' : '#8fa4bd');
}

function normalizeBrowserGuess(candidate) {
    if (!candidate || typeof candidate !== 'object') return {};
    return {...candidate};
}

function setNodeText(id, text) {
    const node = document.getElementById(id);
    if (node) node.textContent = text;
    return node;
}

function setToneClass(node, tone) {
    if (!node) return;
    node.classList.remove('good', 'medium', 'low');
    if (tone) node.classList.add(tone);
}

function renderPairStats(stats) {
    (Array.isArray(stats) ? stats : []).forEach(stat => {
        const pair = String(stat.pair || stat.action || '');
        if (!/^(BUY|SELL)$/.test(pair)) return;
        const card = document.getElementById(`pairCard${pair.toLowerCase()}`);
        if (!card) return;
        const total = Number(stat.total || 0);
        const right = Number(stat.right || 0);
        const wrong = Number(stat.wrong ?? Math.max(0, total - right));
        const percentage = Number(stat.percentage || 0);
        const value = card.querySelector('[data-pair-percentage]');
        const count = card.querySelector('[data-pair-count]');
        if (value) {
            value.textContent = total > 0 ? `${percentage.toFixed(1)}%` : '—';
            value.classList.remove('good', 'medium', 'low');
            value.classList.add(percentage >= 65 ? 'good' : (percentage >= 50 ? 'medium' : 'low'));
        }
        if (count) count.textContent = `${right} RIGHT / ${wrong} WRONG / ${total} TOTAL`;
    });
}

function renderForwardAccuracy(data) {
    const accuracy = Number(data.accuracy || 0);
    const total = Number(data.accuracyTotal || 0);
    const right = Number(data.accuracyRight || 0);
    const accuracyText = total > 0 ? `${accuracy.toFixed(2)}%` : '—';
    const accuracyNote = total > 0
        ? `${right} RIGHT / ${Math.max(0, total - right)} WRONG / ${total} TOTAL • LOOP 2 CARRY-FORWARD`
        : 'No forward rows available';
    const tone = total <= 0 ? 'medium' : (accuracy >= 65 ? 'good' : (accuracy >= 50 ? 'medium' : 'low'));
    const valueNode = setNodeText('accuracyValue', accuracyText);
    const noteNode = setNodeText('accuracyNote', accuracyNote);
    const carryValueNode = setNodeText('modelCarryValue', accuracyText);
    const carryNoteNode = setNodeText('modelCarryNote', accuracyNote);
    setToneClass(valueNode, tone);
    setToneClass(carryValueNode, tone);
    if (noteNode) noteNode.textContent = accuracyNote;
    if (carryNoteNode) carryNoteNode.textContent = accuracyNote;
}

function renderStatusMeta(data) {
    const note = String(data.dataNote || 'Using the current 30-second market feed.');
    const updated = String(data.updatedAt || '');
    setNodeText('dataStatusNote', note);
    setNodeText('dataStatusUpdated', updated ? `Feed file ${updated}` : 'Feed file unavailable');
}

function renderModelStance(guess, traderState, data) {
    adaptiveCompleteFlip = Boolean(data?.adaptiveCompleteFlip);
    const action = actionForGuess(guess);
    const pair = pairForGuess(guess);
    const pairLabel = pair === '%' ? 'Unavailable' : pair;
    const resolutionText = pair === '%'
        ? 'Signal unavailable'
        : (adaptiveCompleteFlip ? 'COMPLETE FLIP ACTIVE' : (Boolean(data?.branchFlipActive) ? 'BRANCH FLIP ACTIVE' : 'Current hour unresolved'));
    const currentGuessValueNode = document.getElementById('currentGuessValue');
    const currentGuessNoteNode = document.getElementById('currentGuessNote');
    if (currentGuessValueNode) {
        currentGuessValueNode.textContent = action;
        setToneClass(currentGuessValueNode, action === 'BUY' ? 'good' : (action === 'SELL' ? 'low' : 'medium'));
    }
    if (currentGuessNoteNode) {
        currentGuessNoteNode.textContent = pair === '%'
            ? 'Model signal unavailable for the current hour'
            : `MODEL ${pair} • ${action} • current hour unresolved`;
    }
    setNodeText('modelPairValue', pairLabel);
    const resolutionChip = setNodeText('modelResolutionChip', resolutionText);
    if (resolutionChip) {
        resolutionChip.classList.remove('is-warning', 'is-neutral');
        resolutionChip.classList.add(pair === '%' ? 'is-neutral' : 'is-warning');
    }
    const wins = Number(traderState?.wins || 0);
    const losses = Number(traderState?.losses || 0);
    setNodeText('modelWinLossValue', `${wins} / ${losses}`);
    const lastTradeResult = String(traderState?.last_trade_result || '');
    const lastTradeAction = String(traderState?.last_trade?.action || '').trim();
    const lastTradePnl = Number(traderState?.last_trade_pnl || 0);
    const lastText = lastTradeResult
        ? `${(lastTradeAction || lastTradeResult)} ${(lastTradePnl >= 0 ? '+' : '-')}$${number2(Math.abs(lastTradePnl))}`
        : 'No closed trade yet';
    setNodeText('modelLastValue', lastText);
}

function syncChartMeta() {
    const headingNode = document.getElementById('chartHeading');
    const canvasNode = document.getElementById('candleChart');
    const stampNode = document.getElementById('chartTimeStamp');
    const actualCount = chartTimeline.filter(record => record && record.actual).length;
    const forecastCount = chartTimeline.filter(record => record && !record.actual && record.guess).length;
    if (headingNode) headingNode.textContent = `${actualCount} real hourly candles • ${forecastCount} combined forecast candles`;
    if (canvasNode) {
        canvasNode.setAttribute(
            'aria-label',
            `Chart with ${actualCount} real hourly candles and ${forecastCount} combined forecast candlesticks`
        );
    }
    const visibleRecords = chartTimeline.filter(record => record && (record.actual || record.guess));
    if (stampNode) {
        if (!visibleRecords.length) {
            stampNode.textContent = 'Local hourly chart window unavailable';
        } else {
            const firstTime = visibleRecords[0].displayTime || visibleRecords[0].time;
            const lastTime = visibleRecords[visibleRecords.length - 1].displayTime || visibleRecords[visibleRecords.length - 1].time;
            const currentRecord = visibleRecords.find(record => record && record.phase === 'current') || null;
            const timeZone = dashboardTimeZone;
            const formatStamp = value => {
                const parsed = new Date(value);
                if (Number.isNaN(parsed.getTime())) return String(value || '');
                return parsed.toLocaleString([], {
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    timeZone: dashboardTimeZone
                });
            };
            let stampText = `Local hourly chart window ${formatStamp(firstTime)} → ${formatStamp(lastTime)} • ${timeZone}`;
            if (currentRecord) {
                const currentTime = currentRecord.displayTime || currentRecord.time;
                stampText += ` • current boundary ${formatStamp(currentTime)}`;
            }
            stampNode.textContent = stampText;
        }
    }
}

function formatChartAxisTime(value) {
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) return String(value || '').slice(-5);
    return parsed.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit', timeZone: dashboardTimeZone});
}

function formatChartAxisDate(value) {
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) return '';
    return parsed.toLocaleDateString([], {month:'2-digit', day:'2-digit', timeZone: dashboardTimeZone});
}

function localizeTableTimes() {
    document.querySelectorAll('#liveGuessTable td[data-epoch], #hourAuditTable td[data-epoch]').forEach(cell => {
        const localDate = new Date(Number(cell.dataset.epoch));
        const compact = localDate.toLocaleString([], {
            month:'2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit', timeZone: dashboardTimeZone
        });
        const detailed = localDate.toLocaleString([], {
            year:'numeric', month:'2-digit', day:'2-digit',
            hour:'2-digit', minute:'2-digit', second:'2-digit', timeZoneName:'short', timeZone: dashboardTimeZone
        });
        cell.textContent = compact;
        cell.title = detailed;
        cell.setAttribute('aria-label', detailed);
    });
}

function drawCandleChart() {
    const canvas = document.getElementById('candleChart');
    if (!canvas) return;
    const rect = canvas.getBoundingClientRect();
    const dpr = Math.max(1, window.devicePixelRatio || 1);
    canvas.width = Math.floor(rect.width * dpr);
    canvas.height = Math.floor(rect.height * dpr);
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, rect.width, rect.height);

    const pad = {l:72, r:18, t:20, b:56};
    const width = rect.width - pad.l - pad.r;
    const height = rect.height - pad.t - pad.b;

    if (!chartTimeline.length) {
        ctx.fillStyle = '#8fa4bd';
        ctx.font = '14px system-ui';
        ctx.fillText('Waiting for hourly candlesticks.', pad.l, pad.t + 35);
        return;
    }

    const high = Number(chartPriceMax);
    const low = Number(chartPriceMin);
    const y = price => pad.t + height * (1 - (price - low) / Math.max(.000001, high - low));
    const slot = width / chartTimeline.length;
    const actualCount = chartTimeline.filter(record => record && record.actual).length;
    const lastActualIndex = actualCount > 0 ? actualCount - 1 : -1;
    const firstForecastIndex = actualCount;
    const lastForecastIndex = chartTimeline.length - 1;
    const actualPriceLabelIndexes = new Set();
    const forecastPriceLabelIndexes = new Set();
    const addPriceLabelIndexes = (target, start, end, spacing) => {
        if (start > end || start < 0 || end < 0) return;
        const step = Math.max(1, spacing);
        target.add(start);
        target.add(end);
        for (let index = start; index <= end; index += step) target.add(index);
    };
    if (actualCount > 0) {
        const actualSpacing = slot >= 72 ? 1 : (slot >= 52 ? 2 : (slot >= 38 ? 3 : Math.max(1, Math.ceil(actualCount / 4))));
        addPriceLabelIndexes(actualPriceLabelIndexes, 0, lastActualIndex, actualSpacing);
        actualPriceLabelIndexes.add(lastActualIndex);
    }
    if (firstForecastIndex <= lastForecastIndex) {
        const forecastCount = Math.max(0, chartTimeline.length - actualCount);
        const forecastSpacing = slot >= 70 ? 1 : (slot >= 50 ? 2 : Math.max(1, Math.ceil(Math.max(1, forecastCount) / 3)));
        addPriceLabelIndexes(forecastPriceLabelIndexes, firstForecastIndex, lastForecastIndex, forecastSpacing);
        forecastPriceLabelIndexes.add(firstForecastIndex);
        forecastPriceLabelIndexes.add(lastForecastIndex);
    }
    const drawCandleCloseLabel = (candle, center, color, side = 'center') => {
        if (!candle || !Number.isFinite(Number(candle.close))) return;
        const close = Number(candle.close);
        const open = Number(candle.open);
        const closeY = y(close);
        const labelCenterY = close >= open ? closeY - 12 : closeY + 12;
        const offset = Math.max(6, slot * 0.18);
        const labelX = side === 'left'
            ? center - offset
            : (side === 'right' ? center + offset : center);
        const labelAlign = side === 'left'
            ? 'right'
            : (side === 'right' ? 'left' : 'center');
        drawChartPriceTag(
            ctx,
            rect,
            pad,
            labelX,
            labelCenterY,
            '$' + formatChartPrice(close),
            {
                align: labelAlign,
                background: 'rgba(15,23,42,.94)',
                border: color,
                color: '#eef5ff'
            }
        );
    };

    ctx.font = '11px system-ui';
    ctx.strokeStyle = '#223752';
    ctx.fillStyle = '#8fa4bd';
    for (let i = 0; i <= 5; i++) {
        const y = pad.t + height * i / 5;
        ctx.beginPath(); ctx.moveTo(pad.l, y); ctx.lineTo(rect.width - pad.r, y); ctx.stroke();
        const price = high - (high - low) * i / 5;
        ctx.fillText(price.toLocaleString(undefined, {maximumFractionDigits: 2}), 5, y + 4);
    }

    chartTimeline.forEach((record, index) => {
        const candle = record.actual;
        if (!candle) return;
        const center = pad.l + slot * (index + .5) - slot * .15;
        const rising = candle.close > candle.open;
        const falling = candle.close < candle.open;
        const color = rising ? '#4ade80' : (falling ? '#fb7185' : '#94a3b8');
        ctx.strokeStyle = color;
        ctx.fillStyle = color;
        ctx.lineWidth = 1.5;
        ctx.beginPath(); ctx.moveTo(center, y(candle.high)); ctx.lineTo(center, y(candle.low)); ctx.stroke();
        const top = y(Math.max(candle.open, candle.close));
        const bottom = y(Math.min(candle.open, candle.close));
        const bodyHeight = Math.max(2, bottom - top);
        ctx.strokeRect(center - slot * .11, top, slot * .22, bodyHeight);
        if (actualPriceLabelIndexes.has(index)) {
            drawCandleCloseLabel(candle, center, color, 'right');
        }
    });

    chartTimeline.forEach((record, index) => {
        const guess = record.guess;
        if (!guess) return;
        const center = pad.l + slot * (index + .5) + slot * .15;
        const action = actionForRecord(record);
        const pair = pairForRecord(record);
        const color = colorForGuessAction(action);
        ctx.strokeStyle = color;
        ctx.fillStyle = color;
        ctx.lineWidth = 1;
        ctx.beginPath(); ctx.moveTo(center, y(guess.high)); ctx.lineTo(center, y(guess.low)); ctx.stroke();
        const top = y(Math.max(guess.open, guess.close));
        const bottom = y(Math.min(guess.open, guess.close));
        ctx.strokeRect(center - slot * .11, top, slot * .22, Math.max(2, bottom - top));
        if (pair && slot >= 28) {
            const labelY = Math.max(pad.t + 12, y(guess.high) - 6);
            ctx.font = '10px system-ui';
            ctx.fillStyle = '#eef5ff';
            ctx.fillText(pair, center - (ctx.measureText(pair).width / 2), labelY);
            ctx.fillStyle = color;
            ctx.font = '11px system-ui';
        }
        if (forecastPriceLabelIndexes.has(index)) {
            drawCandleCloseLabel(guess, center, color, 'left');
        }
        const result = String(record.guessResult || '');
        if (result === 'RIGHT' || result === 'WRONG' || result === 'MIXED') {
            ctx.font = '10px system-ui';
            ctx.fillStyle = result === 'RIGHT' ? '#4ade80' : (result === 'WRONG' ? '#fb7185' : '#fbbf24');
            ctx.textAlign = 'center';
            ctx.fillText(result, center, Math.min(rect.height - pad.b - 4, y(guess.low) + 18));
            ctx.textAlign = 'left';
        }
    });

    const currentPrice = Number(liveSpotPrice || 0);
    if (Number.isFinite(currentPrice) && currentPrice > 0 && currentPrice >= low && currentPrice <= high) {
        const currentY = y(currentPrice);
        ctx.save();
        ctx.strokeStyle = 'rgba(238,245,255,.28)';
        ctx.lineWidth = 1;
        ctx.setLineDash([5, 4]);
        ctx.beginPath();
        ctx.moveTo(pad.l, currentY);
        ctx.lineTo(rect.width - pad.r, currentY);
        ctx.stroke();
        ctx.restore();
        drawChartPriceTag(
            ctx,
            rect,
            pad,
            rect.width - pad.r - 2,
            currentY,
            'NOW $' + formatChartPrice(currentPrice),
            {
                align: 'right',
                background: 'rgba(11,18,32,.97)',
                border: 'rgba(238,245,255,.55)',
                color: '#eef5ff',
                height: 20
            }
        );
    }

    ctx.font = '10.5px system-ui';
    let legendX = pad.l;
    [
        {label:'Real', color:'#4ade80'},
        {label:'BUY (++)', color:'#62a8ff'},
        {label:'SELL (-- / -+ / +-)', color:'#f59e0b'},
        {label:'NO TRADE (%)', color:'#8fa4bd'}
    ].forEach(item => {
        ctx.strokeStyle = item.color;
        ctx.strokeRect(legendX, 8, 18, 5);
        ctx.fillStyle = '#eef5ff';
        ctx.fillText(item.label, legendX + 24, 14);
        legendX += 24 + ctx.measureText(item.label).width + 18;
    });
    ctx.font = '11px system-ui';

    ctx.fillStyle = '#8fa4bd';
    const axisLastActualIndex = actualCount > 0 ? actualCount - 1 : 0;
    const labelIndexes = [0, axisLastActualIndex];
    const actualLabelStep = actualCount > 0
        ? Math.max(1, Math.ceil(actualCount / (slot < 40 ? 4 : 6)))
        : 1;
    for (let index = actualLabelStep; index < axisLastActualIndex; index += actualLabelStep) {
        labelIndexes.push(index);
    }
    for (let index = actualCount; index < chartTimeline.length; index++) labelIndexes.push(index);
    let previousDateKey = '';
    [...new Set(labelIndexes)].forEach(index => {
        if (!chartTimeline[index]) return;
        const labelTime = chartTimeline[index].displayTime || chartTimeline[index].time;
        const timeLabel = formatChartAxisTime(labelTime);
        const dateLabel = formatChartAxisDate(labelTime);
        const labelX = pad.l + slot * (index + .5);
        const dateChanged = dateLabel !== '' && dateLabel !== previousDateKey;
        previousDateKey = dateLabel || previousDateKey;
        ctx.strokeStyle = 'rgba(143,164,189,.18)';
        ctx.beginPath();
        ctx.moveTo(labelX, rect.height - pad.b + 6);
        ctx.lineTo(labelX, rect.height - pad.b + 14);
        ctx.stroke();
        ctx.fillStyle = '#8fa4bd';
        ctx.textAlign = 'center';
        ctx.fillText(timeLabel, labelX, rect.height - 22);
        if (dateChanged || index === 0 || index === axisLastActualIndex || index === chartTimeline.length - 1) {
            ctx.fillStyle = '#5f7899';
            ctx.fillText(dateLabel, labelX, rect.height - 8);
        }
    });
    ctx.textAlign = 'start';
}

window.addEventListener('resize', drawCandleChart);
localizeTableTimes();
renderPairStats(pairStats);
renderForwardAccuracy({
    accuracy: <?= json_encode($accuracy) ?>,
    accuracyRight: <?= json_encode($accuracy_right) ?>,
    accuracyTotal: <?= json_encode($accuracy_total) ?>
});
syncChartMeta();
drawCandleChart();

const fiveMinutes = 5 * 60 * 1000;
let nextBoundary = (Math.floor(Date.now() / fiveMinutes) + 1) * fiveMinutes;
let boundaryTimer = null;

function lockedCurrentGuess(candidate) {
    const boundary = Math.floor(Date.now() / fiveMinutes);
    const pageKey = new URL(window.location.href);
    pageKey.searchParams.delete('live');
    pageKey.searchParams.delete('refresh');
    pageKey.searchParams.delete('_');
    const storageKey = `cngn-current:v2:${pageKey.toString()}:${boundary}`;
    try {
        const normalizedCandidate = normalizeBrowserGuess(candidate);
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            const normalizedSaved = normalizeBrowserGuess(JSON.parse(saved));
            const savedChange = Math.abs(Number(normalizedSaved.change || 0));
            const candidateChange = Math.abs(Number(normalizedCandidate.change || 0));
            if (!(savedChange > 0) && candidateChange > 0) {
                normalizedSaved.change = candidateChange;
                localStorage.setItem(storageKey, JSON.stringify(normalizedSaved));
            }
            return normalizedSaved;
        }
        if (/^[+-]{2}$/.test(String(normalizedCandidate.pair || '')) || normalizedCandidate.symbol === '%') {
            localStorage.setItem(storageKey, JSON.stringify(normalizedCandidate));
        }
    } catch (error) {
        // Continue with the server value if browser storage is unavailable.
    }
    return normalizeBrowserGuess(candidate);
}

function renderLockedCurrentRow(guess, commitmentAmount = 0, currentStateText = 'CURRENT · UNRESOLVED') {
    const row = document.querySelector('#liveGuessTable tr.current-guess-row');
    if (!row || !row.cells || row.cells.length < 3) return;
    const action = actionForGuess(guess);
    row.cells[1].textContent = action === 'NO TRADE'
        ? `CURRENT: ${action}`
        : `CURRENT: ${action} · COMMIT $${number2(commitmentAmount)}`;
    row.cells[2].textContent = action === 'NO TRADE' ? 'UNKNOWN' : `${currentStateText} · 1 TRADE/HOUR`;
}

function number2(value) {
    return Number(value || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
}

function number4(value) {
    return Number(value || 0).toLocaleString(undefined, {minimumFractionDigits:4, maximumFractionDigits:4});
}

function number8(value) {
    return Number(value || 0).toLocaleString(undefined, {minimumFractionDigits:8, maximumFractionDigits:8});
}

function formatChartPrice(value) {
    return Number(value || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
}

function drawChartPriceTag(ctx, rect, pad, x, y, text, options = {}) {
    const font = String(options.font || '10.5px system-ui');
    const align = String(options.align || 'center');
    const background = String(options.background || 'rgba(15,23,42,.92)');
    const border = String(options.border || 'rgba(143,164,189,.45)');
    const color = String(options.color || '#eef5ff');
    const height = Number(options.height || 18);
    const horizontalPadding = Number(options.horizontalPadding || 5);
    ctx.save();
    ctx.font = font;
    ctx.textAlign = 'left';
    ctx.textBaseline = 'middle';
    const width = Math.ceil(ctx.measureText(text).width + (horizontalPadding * 2));
    let left = align === 'right'
        ? x - width
        : (align === 'left' ? x : x - (width / 2));
    left = Math.max(pad.l, Math.min(rect.width - pad.r - width, left));
    const top = Math.max(pad.t, Math.min(rect.height - pad.b - height, y - (height / 2)));
    ctx.fillStyle = background;
    ctx.strokeStyle = border;
    ctx.lineWidth = 1;
    ctx.fillRect(left, top, width, height);
    ctx.strokeRect(left, top, width, height);
    ctx.fillStyle = color;
    ctx.fillText(text, left + horizontalPadding, top + (height / 2) + 0.5);
    ctx.restore();
}

function renderCurrentPrice(data) {
    const price = Number(data.currentPrice || 0);
    const change = Number(data.hourChange ?? data.lastPriceChange ?? 0);
    const percentage = Number(data.hourChangePercentage ?? data.currentPricePercentage ?? 0);
    const reference = Number(data.hourReferencePrice || 0);
    const direction = String(data.hourPriceDirection || (change > 0 ? 'UP' : (change < 0 ? 'DOWN' : 'FLAT')));
    const source = String(data.currentPriceSource || '');
    const valueNode = document.getElementById('currentPriceValue');
    const noteNode = document.getElementById('currentPriceNote');
    if (!valueNode || !noteNode) return;

    valueNode.textContent = '$' + number2(price);
    setToneClass(valueNode, change > 0 ? 'good' : (change < 0 ? 'low' : 'medium'));
    noteNode.textContent = '1H ' + direction
        + ' ' + (percentage >= 0 ? '+' : '') + percentage.toFixed(2) + '%'
        + ' • MOVE ' + (change >= 0 ? '+' : '-') + '$' + number2(Math.abs(change))
        + (reference > 0 ? ' • FROM $' + number2(reference) : '');
    setNodeText('marketReferenceValue', reference > 0 ? '$' + number2(reference) : '—');
    const feedLabel = source === 'YAHOO'
        ? 'Yahoo Finance'
        : (source === 'CRON-CACHE' ? 'Cron JSON' : 'Observed feed 30s');
    const chipLabel = source === 'YAHOO'
        ? 'Yahoo • live'
        : (source === 'CRON-CACHE' ? 'Cron cache' : 'Observed feed • 30s');
    setNodeText('marketFeedValue', feedLabel);
    const chipNode = setNodeText('marketSourceChip', chipLabel);
    if (chipNode) {
        chipNode.classList.remove('is-live', 'is-file');
        chipNode.classList.add(source === 'YAHOO' ? 'is-live' : 'is-file');
    }
    const note = String(data.dataNote || '');
    const updated = String(data.updatedAt || '');
    setNodeText(
        'marketDataNote',
        (note || updated)
            ? `${note || 'Using the current 30-second market feed.'}${updated ? ' • Feed file ' + updated : ''}`
            : 'Using the current 30-second market feed.'
    );
}

function renderAutoBreakTrader(state) {
    state = state && typeof state === 'object' ? state : {};
    const action = String(state.display_action || 'WATCHING');
    const position = state.position === 'long' ? 'long' : 'flat';
    const wins = Number(state.wins || 0);
    const losses = Number(state.losses || 0);
    const realizedMove = Number(state.realized_move || 0);
    const openPnl = Number(state.open_pnl || 0);
    const simNetMove = Number(state.sim_net_move || 0);
    const lastTradeResult = String(state.last_trade_result || '');
    const lastTradePnl = Number(state.last_trade_pnl || 0);
    const bellCurveActive = state.hourly_bell_curve_active === true;
    const bellCurveAction = String(state.hourly_bell_curve_action || 'NO TRADE').toUpperCase();
    const bellCurveTrust = Number(state.hourly_bell_curve_effective_trust || 0);
    const bellCurveSlots = Number(state.hourly_bell_curve_slots || 0);
    const bellCurveBuyCalls = Number(state.hourly_bell_curve_buy_calls || 0);
    const bellCurveSellCalls = Number(state.hourly_bell_curve_sell_calls || 0);
    const bellCurveRequested = Number(state.hourly_bell_curve_total_requested || 0);
    const isCryptoPage = String(currentMarketType || '').toLowerCase() === 'crypto';
    const assetCode = isCryptoPage
        ? String(currentTicker || '').replace(/-USD$/i, '').trim()
        : String(currentTicker || '').trim();
    const assetLeftLabel = assetCode ? `${assetCode} LEFT` : (isCryptoPage ? 'COIN LEFT' : 'UNITS LEFT');
    const assetBoughtLabel = assetCode ? `${assetCode} BOUGHT` : (isCryptoPage ? 'COIN BOUGHT' : 'UNITS BOUGHT');
    const assetSoldLabel = assetCode ? `${assetCode} SOLD` : (isCryptoPage ? 'COIN SOLD' : 'UNITS SOLD');
    const assetLeftAmount = isCryptoPage ? number8(state.asset_units) : number4(state.asset_units);
    const assetBoughtAmount = isCryptoPage ? number8(state.total_bought_units) : number4(state.total_bought_units);
    const assetSoldAmount = isCryptoPage ? number8(state.total_sold_units) : number4(state.total_sold_units);
    const totalBoughtAmount = Number(state.total_bought_amount || 0);
    const totalSoldAmount = Number(state.total_sold_amount || 0);
    const bootstrapEntryPrice = Number(state.bootstrap_entry_price || 0);
    const bootstrapStartedAt = String(state.bootstrap_started_at || '');
    const valueNode = document.getElementById('autoBreakValue');
    const noteNode = document.getElementById('autoBreakNote');
    if (!valueNode || !noteNode) return;

    valueNode.textContent = 'SIM NET MOVE ' + (simNetMove >= 0 ? '+' : '-') + '$' + number2(Math.abs(simNetMove));
    setToneClass(valueNode, simNetMove > 0 ? 'good' : (simNetMove < 0 ? 'low' : 'medium'));

    let noteText = position.toUpperCase()
        + ' • ' + action
        + ' • OPEN ' + (position === 'long' ? ((openPnl >= 0 ? '+' : '-') + '$' + number2(Math.abs(openPnl))) : '—')
        + ' • LAST ' + (lastTradeResult || '—')
        + ' ' + (lastTradeResult ? ((lastTradePnl >= 0 ? '+' : '-') + '$' + number2(Math.abs(lastTradePnl))) : '—')
        + ' • W/L ' + wins + '/' + losses
        + ' • Paper only';
    noteNode.textContent = noteText;
    setNodeText('walletPotValue', '$' + number2(state.equity_value));
    setNodeText('walletAssetValue', assetLeftAmount);
    setNodeText('walletHoldingValue', '$' + number2(state.holding_value));
    setNodeText('walletCashValue', '$' + number2(state.cash_left));
    setNodeText('walletNetMoveValue', `${simNetMove >= 0 ? '+' : '-'}$${number2(Math.abs(simNetMove))}`);
    setNodeText('walletRealizedValue', `${realizedMove >= 0 ? '+' : '-'}$${number2(Math.abs(realizedMove))}`);
    setNodeText('walletOpenPnlValue', `${openPnl >= 0 ? '+' : '-'}$${number2(Math.abs(openPnl))}`);
    setNodeText(
        'walletBellCurveValue',
        bellCurveActive
            ? `${bellCurveAction} • ${number2(bellCurveTrust)}% • $${number2(bellCurveRequested)} • ${bellCurveBuyCalls}/${bellCurveSellCalls}/${bellCurveSlots}`
            : 'Inactive'
    );
    setNodeText('walletBoughtValue', `${assetBoughtLabel} ${assetBoughtAmount} ($${number2(totalBoughtAmount)})`);
    setNodeText('walletSoldValue', `${assetSoldLabel} ${assetSoldAmount} ($${number2(totalSoldAmount)})`);
    setNodeText('walletPositionValue', `POSITION ${position.toUpperCase()}`);
    const lastWalletText = lastTradeResult
        ? `${lastTradeResult} ${(lastTradePnl >= 0 ? '+' : '-')}$${number2(Math.abs(lastTradePnl))}`
        : 'No closed trade yet';
    setNodeText('walletLastTradeValue', lastWalletText);
    setNodeText('walletSeedChip', `50/50 ${assetCode || 'asset'} + cash`);
    const seedDate = (() => {
        if (!bootstrapStartedAt) return 'start unavailable';
        const parsed = new Date(bootstrapStartedAt);
        if (Number.isNaN(parsed.getTime())) return bootstrapStartedAt;
        return parsed.toLocaleString(undefined, {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            timeZone: 'UTC',
            timeZoneName: 'short',
        });
    })();
    const seedDetail = `Started with $5,000 cash + $5,000 in ${assetCode || 'the asset'}`
        + (bootstrapEntryPrice > 0 ? ` at $${number2(bootstrapEntryPrice)}` : '')
        + ` • ${seedDate}`;
    setNodeText('walletSeedDetail', seedDetail);
    setNodeText('modelWinLossValue', `${wins} / ${losses}`);
    setNodeText('modelLastValue', lastWalletText);
}

function renderCompression(data) {
    const score = Number(data.compressionScore || 0);
    const entropy = Number(data.compressionEntropy || 100);
    const samples = Number(data.compressionSamples || 0);
    const tailStreak = Number(data.compressionTailStreak || 0);
    const phaseCount = Number(data.compressionPhaseCount || 0);
    const phaseChanges = Number(data.compressionPhaseChanges || 0);
    const perfectMin = Number(data.compressionPerfectMinParts || 0);
    const perfectMax = Number(data.compressionPerfectMaxParts || 0);
    const secondaryScore = Number(data.secondaryCompressionScore || 0);
    const firstLoopScore = Number(data.firstLoopCompressionScore || 0);
    const primaryScore = Number(data.primaryCompressionScore || score);
    const combinedScore = Number(data.combinedCompressionScore || ((primaryScore * 0.70) + (secondaryScore * 0.30)));
    const secondaryState = String(data.secondaryCompressionState || 'DISAGREE');
    const dominantDirection = String(data.compressionDominantDirection || 'MIXED');
    const valueNode = setNodeText('compressionValue', `${number2(primaryScore)}%`);
    if (valueNode) {
        setToneClass(valueNode, primaryScore >= 65 ? 'good' : (primaryScore >= 45 ? 'medium' : 'low'));
    }
    setNodeText(
        'compressionNote',
        (samples > 0 || firstLoopScore > 0)
            ? `${dominantDirection} • entropy ${number2(entropy)}% • ${phaseCount} RLE phases / ${phaseChanges} changes • 100% runs ${perfectMin}–${perfectMax} parts • first loop ${number2(firstLoopScore)}% • secondary ${secondaryState} ${number2(secondaryScore)}% • combined ${number2(combinedScore)}%`
            : 'Waiting for resolved family samples'
    );
}

function renderPhaseActionStats(data) {
    const stats = data.phaseActionStats || {};
    const buy = stats.BUY || {};
    const sell = stats.SELL || {};
    const buyRight = Number(buy.right || 0);
    const buyTotal = Number(buy.total || 0);
    const sellRight = Number(sell.right || 0);
    const sellTotal = Number(sell.total || 0);
    const buyPct = Number(buy.percentage || 0);
    const sellPct = Number(sell.percentage || 0);
    setNodeText('phaseChangeValue', `BUY ${buyPct.toFixed(1)}% · SELL ${sellPct.toFixed(1)}%`);
    setNodeText('phaseChangeNote', `BUY ${buyRight} RIGHT / ${Math.max(0, buyTotal - buyRight)} WRONG / ${buyTotal} TOTAL • SELL ${sellRight} RIGHT / ${Math.max(0, sellTotal - sellRight)} WRONG / ${sellTotal} TOTAL`);
}

function renderCurrentPhaseStatus(data) {
    const phase = data.currentPhaseStatus || {};
    const action = String(phase.action || 'HOLD');
    const stepsIn = Number(data.regimeStepCount || phase.steps_in || 0);
    const stepsUntilChange = phase.steps_until_change === null || phase.steps_until_change === undefined
        ? null
        : Number(phase.steps_until_change);
    const stake = Number(data.phaseStakeAmount || 0);
    const sizing = data.phaseSizing || {};
    const requested = Number(sizing.requested_amount ?? stake);
    const executable = Number(sizing.executable_amount || 0);
    const shortfall = Number(sizing.shortfall || 0);
    const suffix = stepsUntilChange === null ? 'NO CHANGE IN HORIZON' : `${stepsUntilChange} STEPS LEFT`;
    const sizingText = `${action} • REQUESTED $${number2(requested)} • EXEC $${number2(executable)}`
        + (shortfall > 0.00000001 ? ` • SHORT $${number2(shortfall)}` : '');
    setNodeText('phaseStatusValue', `${sizingText} • REGIME HOUR ${stepsIn} / 12 • ${suffix}`);
    const quarter = data.quarterRegime || {};
    const quarterRight = Number(quarter.right || 0);
    const quarterTotal = Number(quarter.total || 0);
    const quarterGateLabel = data.quarterRegimeInverted
        ? 'INVERTED / HOLD'
        : (data.quarterBuyAllowed ? 'OPEN' : 'HOLD');
    setNodeText('quarterRegimeGateValue', `${quarterGateLabel} • ${String(data.quarterRegimeKey || 'NO REGIME')} • ${quarterRight}/${quarterTotal}`);
}

function renderInternalAgreement(data) {
    const recentPercent = Number(data.internalAgreementRecent || 0);
    const right = Number(data.internalAgreementRight || 0);
    const total = Number(data.internalAgreementTotal || 0);
    const recentRight = Number(data.internalAgreementRecentRight || 0);
    const recentTotal = Number(data.internalAgreementRecentTotal || 0);
    const valueNode = setNodeText('internalAgreementValue', `${number2(recentPercent)}%`);
    if (valueNode) {
        setToneClass(valueNode, recentPercent >= 65 ? 'good' : (recentPercent >= 50 ? 'medium' : 'low'));
    }
    setNodeText(
        'internalAgreementNote',
        recentTotal > 0
            ? `Last hour ${recentRight} RIGHT / ${Math.max(0, recentTotal - recentRight)} WRONG / ${recentTotal} TOTAL • all ${right} RIGHT / ${Math.max(0, total - right)} WRONG / ${total} TOTAL`
            : 'Waiting for left/right agreement samples'
    );
}

function renderFamilyConfidence(data) {
    const stat = data.currentFamilyConfidence || {};
    const total = Number(stat.total || 0);
    const right = Number(stat.right || 0);
    const historicalPercentage = Number(stat.historical_percentage ?? stat.percentage ?? 0);
    const flipped = Boolean(data.currentFamilyEffectiveFlip || data.currentFamilyBranchFlipped);
    const percentage = total > 0 && flipped ? (100 - historicalPercentage) : historicalPercentage;
    const valueNode = setNodeText('familyConfidenceValue', total > 0 ? `${number2(percentage)}%` : '—');
    if (valueNode) setToneClass(valueNode, percentage >= 65 ? 'good' : (percentage >= 50 ? 'medium' : 'low'));
    const flipNote = flipped ? ` • HISTORICAL ${number2(historicalPercentage)}% • EFFECTIVE ${number2(percentage)}% • FLIPPED` : '';
    setNodeText('familyConfidenceNote', `${String(data.currentFamilyConfidenceKey || 'NO TRADE')} • ${right} RIGHT / ${Math.max(0, total - right)} WRONG / ${total} TOTAL${flipNote}`);
}

async function loadLiveData(options = {}) {
    const priceOnly = options && options.priceOnly === true;
    const url = new URL(window.location.href);
    url.searchParams.set('live', '1');
    url.searchParams.set('cache_only', '1');
    url.searchParams.set('_', Date.now().toString());
    try {
        const response = await fetch(url.toString(), {cache:'no-store'});
        if (!response.ok) return;
        const data = await response.json();
        if (!data.ok) return;
        currentMarketType = String(data.marketType || currentMarketType || '');
        liveSpotPrice = Number(data.currentPrice || liveSpotPrice || 0);
        renderCurrentPrice(data);
        renderStatusMeta(data);
        const traderState = data.autoBreakTrader || {};
        renderAutoBreakTrader(traderState);
        renderInternalAgreement(data);
        renderFamilyConfidence(data);
        renderCompression(data);
        const profit = Number(data.simulatedNetMove ?? data.paperProfit ?? traderState.sim_net_move ?? 0);
        const profitNode = document.getElementById('paperProfit');
        if (profitNode) {
            profitNode.textContent = `SIM NET MOVE ${profit >= 0 ? '+' : ''}$${number2(profit)}`;
            profitNode.style.color = profit >= 0 ? 'var(--accent)' : 'var(--danger)';
        }
        if (priceOnly) {
            drawCandleChart();
            return;
        }
        candles = Array.isArray(data.candles) ? data.candles : [];
        guessCandles = Array.isArray(data.guessCandles) ? data.guessCandles : [];
        timeline = Array.isArray(data.timeline) ? data.timeline : [];
        chartTimeline = Array.isArray(data.chartTimeline) ? data.chartTimeline : [];
        pairStats = Array.isArray(data.pairStats) ? data.pairStats : [];
        pairDirectionMap = (data.pairDirectionMap && typeof data.pairDirectionMap === 'object')
            ? data.pairDirectionMap
            : pairDirectionMap;
        renderPairStats(pairStats);
        renderForwardAccuracy(data);
        renderPhaseActionStats(data);
        renderCurrentPhaseStatus(data);
        chartPriceMin = Number(data.chartPriceMin || 0);
        chartPriceMax = Number(data.chartPriceMax || 1);
        document.getElementById('liveGuessTable').innerHTML = data.tableHtml || '';
        const hourAuditTableNode = document.getElementById('hourAuditTable');
        if (hourAuditTableNode && data.hourAuditTableHtml) hourAuditTableNode.innerHTML = data.hourAuditTableHtml;
        const selloffTipChip = document.getElementById('selloffTipChip');
        if (selloffTipChip) {
            const sellSignalStreak = Number(data.sellSignalStreak || 0);
            selloffTipChip.textContent = `Selloff tip ${data.selloffTip ? 'ON' : 'OFF'} • SELL run ${sellSignalStreak}`;
        }
        localizeTableTimes();
        const guess = lockedCurrentGuess(data.currentGuess || {});
        const traderStateText = (() => {
            const action = String(traderState.display_action || '').trim();
            if (!action || action === 'WATCHING') return 'CURRENT · UNRESOLVED';
            return `CURRENT · EXECUTED ${action} · SETTLING`;
        })();
        renderLockedCurrentRow(guess, Number(data.currentCommitmentAmount || 0), traderStateText);
        renderModelStance(guess, traderState, data);
        document.getElementById('chartStats').textContent = `Price range ${number2(chartPriceMin)}–${number2(chartPriceMax)} • Average move ${number2(data.averageChange)}`;
        document.getElementById('averageMove').textContent = `AVG MOVE ${Number(data.averageChange || 0) >= 0 ? '+' : ''}$${number2(data.averageChange)}`;
        syncChartMeta();
        drawCandleChart();
    } catch (error) {
        // Keep the last good live frame visible during a temporary data failure.
    }
}

function scheduleBoundaryRequest() {
    clearTimeout(boundaryTimer);
    const delay = Math.max(250, nextBoundary - Date.now() + 1500);
    boundaryTimer = setTimeout(async () => {
        window.location.reload();
        while (nextBoundary <= Date.now()) nextBoundary += fiveMinutes;
        scheduleBoundaryRequest();
    }, delay);
}

function updateRetrieveTimer() {
    const remaining = Math.max(0, nextBoundary - Date.now());
    const totalSeconds = Math.ceil(remaining / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    document.getElementById('retrieveCountdown').textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    document.getElementById('retrieveTime').textContent = new Date(nextBoundary).toLocaleTimeString([], {
        hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: dashboardTimeZone
    });
}

function setupMarqueeCarousel() {
    const marquee = document.querySelector('.symbol-marquee');
    if (!marquee) return;
    const track = marquee.querySelector('.symbol-marquee-track');
    if (!track) return;
    const spacers = Array.from(track.querySelectorAll('.symbol-marquee-spacer'));
    const tailSpacer = spacers[spacers.length - 1] || null;
    const getSlides = () => Array.from(track.querySelectorAll('.symbol-item'));
    if (getSlides().length < 2) return;

    let timer = null;
    let resumeTimer = null;
    let moveTimer = null;

    const slideStep = () => {
        const firstSlide = getSlides()[0];
        if (!firstSlide) return 0;
        const gapValue = window.getComputedStyle(track).columnGap || window.getComputedStyle(track).gap || '0';
        const gap = Number.parseFloat(gapValue) || 0;
        return firstSlide.getBoundingClientRect().width + gap;
    };

    const queueAdvance = () => {
        const step = slideStep();
        if (step <= 0) return;
        marquee.scrollBy({ left: step, behavior: 'smooth' });
        if (moveTimer) clearTimeout(moveTimer);
        moveTimer = window.setTimeout(() => {
            const firstSlide = getSlides()[0];
            if (!firstSlide || !tailSpacer) return;
            track.insertBefore(firstSlide, tailSpacer);
            marquee.scrollLeft = Math.max(0, marquee.scrollLeft - step);
        }, 560);
    };

    const start = () => {
        if (timer) return;
        timer = window.setInterval(() => {
            queueAdvance();
        }, 2200);
    };

    const stop = () => {
        if (!timer) return;
        clearInterval(timer);
        timer = null;
        if (moveTimer) clearTimeout(moveTimer);
        moveTimer = null;
    };

    const queueResume = () => {
        if (resumeTimer) {
            clearTimeout(resumeTimer);
        }
        resumeTimer = window.setTimeout(() => {
            start();
        }, 600);
    };

    marquee.addEventListener('mouseenter', stop);
    marquee.addEventListener('mouseleave', queueResume);
    marquee.addEventListener('pointerdown', stop);
    marquee.addEventListener('focusin', stop);
    marquee.addEventListener('focusout', queueResume);
    start();
}

function setupMarqueeScroll() {
    const marquee = document.querySelector('.symbol-marquee');
    if (!marquee) return;

    let autoTimer = null;
    const autoStep = 1;

    const autoScrollStep = () => {
        const maxScrollLeft = Math.max(0, marquee.scrollWidth - marquee.clientWidth);
        if (maxScrollLeft > 0 && !document.hidden) {
            marquee.scrollLeft += autoStep;
            if (marquee.scrollLeft >= maxScrollLeft - 1) {
                marquee.scrollLeft = 0;
            }
        }
    };

    marquee.addEventListener('wheel', (event) => {
        if (Math.abs(event.deltaY) <= Math.abs(event.deltaX)) return;
        event.preventDefault();
        marquee.scrollLeft += event.deltaY;
    }, { passive: false });

    let isDragging = false;
    let startX = 0;
    let startScrollLeft = 0;

    marquee.addEventListener('pointerdown', (event) => {
        if (event.target.closest('.symbol-remove-text, .symbol-remove-form')) return;
        isDragging = true;
        startX = event.clientX;
        startScrollLeft = marquee.scrollLeft;
        marquee.classList.add('is-dragging');
        marquee.setPointerCapture?.(event.pointerId);
        if (autoTimer) {
            clearInterval(autoTimer);
            autoTimer = null;
        }
    });

    marquee.addEventListener('pointermove', (event) => {
        if (!isDragging) return;
        const delta = event.clientX - startX;
        marquee.scrollLeft = startScrollLeft - delta;
    });

    const stopDragging = (event) => {
        if (!isDragging) return;
        isDragging = false;
        marquee.classList.remove('is-dragging');
        if (event && marquee.hasPointerCapture?.(event.pointerId)) {
            marquee.releasePointerCapture(event.pointerId);
        }
        if (!autoTimer) {
            autoTimer = window.setInterval(autoScrollStep, 18);
        }
    };

    marquee.addEventListener('pointerup', stopDragging);
    marquee.addEventListener('pointercancel', stopDragging);
    marquee.addEventListener('mouseleave', () => {
        isDragging = false;
        marquee.classList.remove('is-dragging');
        if (!autoTimer) {
            autoTimer = window.setInterval(autoScrollStep, 18);
        }
    });
    autoTimer = window.setInterval(autoScrollStep, 18);
    window.setTimeout(autoScrollStep, 150);
}

function setupTrackedDashboardCarousel() {
    const track = document.querySelector('.tracked-dashboard-grid');
    if (!track) return;
    const prev = document.querySelector('[data-dashboard-prev]');
    const next = document.querySelector('[data-dashboard-next]');
    if (!prev || !next) return;

    const cardStep = () => {
        const firstCard = track.querySelector('.tracked-dashboard-card');
        if (!firstCard) return 280;
        const gapValue = window.getComputedStyle(track).columnGap || window.getComputedStyle(track).gap || '0';
        const gap = Number.parseFloat(gapValue) || 0;
        return firstCard.getBoundingClientRect().width + gap;
    };

    prev.addEventListener('click', () => {
        track.scrollBy({ left: -cardStep(), behavior: 'smooth' });
    });

    next.addEventListener('click', () => {
        track.scrollBy({ left: cardStep(), behavior: 'smooth' });
    });
}

function setupReferenceCloseButtons() {
    document.querySelectorAll('[data-close-reference]').forEach((button) => {
        button.addEventListener('click', () => {
            const drawer = button.closest('.reference-drawer');
            if (drawer) drawer.open = false;
        });
    });
}

setupMarqueeCarousel();
setupTrackedDashboardCarousel();
setupReferenceCloseButtons();
updateRetrieveTimer();
setInterval(updateRetrieveTimer, 250);
setInterval(() => {
    loadLiveData({priceOnly: true});
}, 30000);
// Rebuild the complete dashboard independently of five-minute boundary changes.
// The live endpoint updates selected nodes; the periodic page reload is the
// authoritative innerText refresh for tracked assets, cards, tables, and PHP
// rendered status that is not safely patchable from the live payload.
setInterval(() => {
    window.location.reload();
}, 60000);
scheduleBoundaryRequest();
</script>
</body>
</html>
