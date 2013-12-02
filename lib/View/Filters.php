<?php

class View_Filters {
    
    public $view;

    public function __construct($view) {
        $this->view = $view;
    }
    
    public function inclfrag($src) {
        return (new View(PROJECT_ROOT . "/views/_fragments/" . $src . ".html", $this->view->params))->render();
    }

    /**
     * By default escape all output using htmlspecialchars
     */
    public function escape($code) {
        return '<?php echo htmlspecialchars(' . $code . '); ?>';
    }

    /**
     * Allow HTML code to be explicitly included inline in the template
     */
    public function h($code) {
        return '<?php echo ' . $code . '; ?>';
    }

    /**
     * Execute the code silently, without returning it
     */
    public function s($code) {
        return '<?php ' . $code . '; ?>';
    }

    /**
     * Urlencode the variable
     */
    public function u($code) {
        return '<?php echo rawurlencode(' . $code . '); ?>';
    }

    /**
     * JSON encode the variable
     */
    public function js($code) {
        return '<?php echo str_replace(array(\'":"\', \'","\'), array(\'": "\', \'", "\'), json_encode(' . $code . ')); ?>';
    }

    /**
     * JSON encode a variable and pass the result through htmlspecialchars, to make it suitable
     * for inclusion in html (for example, as a data attribute).
     */
    public function escapejs($code) {
        return '<?php echo htmlspecialchars(json_encode(' . $code . ')); ?>';
    }

    public function lower($code) {
        return '<?php echo strtolower(' . $code . '); ?>';
    }

    public function upper($code) {
        return '<?php echo strtoupper(' . $code . '); ?>';
    }

    public function title($code) {
        return '<?php echo ucwords(' . $code . '); ?>';
    }

    public function text($code) {
        return '<?php echo nl2br(htmlspecialchars(' . $code . ')); ?>';
    }

    public function date($code, $format='S') {
        switch($format) {
            case 'S':
                $config = 'format_date';
                $noconfig = 'm/d/y';
                break;
            case 'L':
            default:
                $config = 'format_longdate';
                $noconfig = 'F d, Y';
                break;
        }

        $pattern = (isset($this->tpl->config->$config)) ? $this->tpl->config->$config : $noconfig;

        $lines = array(
            '$_fw_tmp_date = ' . $code . ';',
            'if(!empty($_fw_tmp_date)) {',
            'if(is_string($_fw_tmp_date)) {$_fw_tmp_date = strtotime($_fw_tmp_date);}',
            'if($_fw_tmp_date !== false && $_fw_tmp_date >= 0) {',
            'echo date("' . $pattern . '", $_fw_tmp_date);',
            '}}',
            'unset($_fw_tmp_date);'
        );

        return '<?php ' . implode(' ', $lines) . ' ?>';
    }
    
    public function dateago($code) {
        return '<?php echo View_Filters::howLongAgo(' . $code . ') ?>';
    }
    
    public static function howLongAgo($code) {
        $now = time();
        $secs_ago = $now - strtotime($code);
        
        $mins = $secs_ago / 60;
        if ($mins < 1) {
            $strval = View_Filters::getPluralAgoString($secs_ago, "second");
        } else {
            $hrs = $mins / 60;
            if ($hrs < 1) {
                $strval = View_Filters::getPluralAgoString($mins, "minute");
            } else {
                $days = $hrs / 24;
                if ($days < 1) {
                    $strval = View_Filters::getPluralAgoString($hrs, "hour");
                } else {
                    $weeks = $days / 7;
                    if ($weeks < 1) {
                        $strval = View_Filters::getPluralAgoString($days, "day");
                    } else {
                        $months = $weeks / 4;
                        if ($months < 1) {
                            $strval = View_Filters::getPluralAgoString($weeks, "week");
                        } else {
                            $years = $months / 12;
                            if ($years < 1) {
                                $strval = View_Filters::getPluralAgoString($months, "month");
                            } else {
                                $strval = View_Filters::getPluralAgoString($years, "year");
                            }
                        }
                    }
                }
            }
        }
        
        return $strval . " ago";
    }
    
    public static function getPluralAgoString($val, $label) {
        return floor($val) . " " . $label . (floor($val) > 1 ? "s" : "");
    }

    public function longdate($code) {
        return $this->date($code, 'L');
    }

    public function datetime($code, $format='S') {
        switch($format) {
            case 'S':
                $config = 'format_datetime';
                $noconfig = 'm/d/y h:i a';
                break;
            case 'L':
            default:
                $config = 'format_longdatetime';
                $noconfig = 'F d, Y h:i a';
                break;
        }

        $pattern = (isset($this->tpl->config->$config)) ? $this->tpl->config->$config : $noconfig;

        $lines = array(
            '$_fw_tmp_date = ' . $code . ';',
            'if(!empty($_fw_tmp_date)) {',
            'if(is_string($_fw_tmp_date)) {$_fw_tmp_date = strtotime($_fw_tmp_date);}',
            'if($_fw_tmp_date !== false && $_fw_tmp_date >= 0) {',
            'echo date("' . $pattern . '", $_fw_tmp_date);',
            '}}',
            'unset($_fw_tmp_date);'
        );

        return '<?php ' . implode(' ', $lines) . ' ?>';
    }

    public function longdatetime($code) {
        return $this->datetime($code, 'L');
    }

    public function number($code, $convert_null=false) {
        $lines = array(
            '$_fw_tmp_num = ' . $code . ';',
            'if($_fw_tmp_num !== null || ' . intval($convert_null) . ') {',
            'echo number_format($_fw_tmp_num, 0); }',
            'unset($_fw_tmp_num);'
        );

        return '<?php ' . implode(' ', $lines) . ' ?>';
    }

    public function coercenumber($code) {
        return $this->number($code, true);
    }

    public function groupnumber($code) {
        return '<?php echo View_Filters::_groupNumber(' . $code . ') ?>';
    }

    /**
     * Find out the appropriate unit (in thousands) to use for the given number, and round it to that nearest unit
     * @param integer $number the number to group and round
     * @return string
     */
    public static function _groupNumber($number) {
        if($number < 1000) return intval($number);
        if($number < 1000000) return intval($number / 1000) . 'k';
        return intval($number / 1000000) . 'm';
    }

    /**
     * Same as the number filter, but format out to two decimal places instead of 0
     */
    public function float($code) {
        $lines = array(
            '$_fw_tmp_num = ' . $code . ';',
            'if($_fw_tmp_num !== null) {',
            'echo number_format($_fw_tmp_num, 2); }',
            'unset($_fw_tmp_num);'
        );

        return '<?php ' . implode(' ', $lines) . ' ?>';
    }

    public function percent($code) {
        $lines = array(
            '$_fw_tmp_percent = ' . $code . ';',
            'if($_fw_tmp_percent !== null) {',
            'echo number_format($_fw_tmp_percent*100, 1); }',
            'unset($_fw_tmp_percent);'
        );

        return '<?php ' . implode(' ', $lines) . ' ?>';
    }

    public function currency($code) {
        $lines = array(
            '$_fw_tmp_cur = ' . $code . ';',
            'if($_fw_tmp_cur !== null) {',
            'echo "$" . number_format($_fw_tmp_cur, 2); }',
            'unset($_fw_tmp_cur);'
        );

        return '<?php ' . implode(' ', $lines) . ' ?>';
    }

    public function bool($code) {
        return '<?php if(' . $code .') {echo "true";} else {echo "false";} ?>';
    }

    public function ord($code) {
        return '<?php echo ' . $code . ' . View_Filters::getOrdinal(' . $code . '); ?>';
    }
    
    public function textnum($code) {
        return '<?php echo View_Filters::getTextNum(' . $code . '); ?>';
    }
    
    public static function getTextNum($val) {
        if(!is_numeric($val)) return $val;
        $val = intval($val);
        
        switch($val) {
            case 0:
                return 'zero';
            case 1:
                return 'one';
            case 2:
                return 'two';
            case 3:
                return 'three';
            case 4:
                return 'four';
            case 5:
                return 'five';
            case 6:
                return 'six';
            case 7:
                return 'seven';
            case 8:
                return 'eight';
            case 9:
                return 'nine';
            case 10:
                return 'ten';
            case 11:
                return 'eleven';
            case 12:
                return 'twelve';
            case 13:
                return 'thirteen';
            case 14:
                return 'fourteen';
            case 15:
                return 'fifteen';
            case 16:
                return 'sixteen';
            case 17:
                return 'seventeen';
            case 18:
                return 'eighteen';
            case 19:
                return 'nineteen';
            case 20:
                return 'twenty';
            default:
                return $val;
        }
    }

    public static function getOrdinal($val) {
        if(strlen($val) > 2) {
            $val = substr($val, -2);
        }

        $val = intval($val);

        if($val >= 21 || $val < 10) {
            switch(substr($val, -1)) {
                case '1':
                    return 'st';
                case '2':
                    return 'nd';
                case '3':
                    return 'rd';
                default:
                    return 'th';
            }
        } else {
            return 'th';
        }
    }

    public function stub($code) {
        return '<?php echo strtolower(preg_replace("/\s+/", "-", trim(' . $code . '))); ?>';
    }

    public function auto($code) {
        return '<?php echo View_Filters::formatAuto(' . $code . '); ?>';
    }

    public static function formatAuto($val) {
        if(is_bool($val)) {
            return ($val) ? 'true' : 'false';
        }

        if(strtotime($val) !== false) {
            if(strlen($val) <= 10) {
                return date('F d, Y', strtotime($val));
            } else {
                return date ('F d, Y h:i a', strtotime($val));
            }
        }

        if(is_numeric($val)) {
            return number_format($val, 0);
        }

        return $val;
    }
    
	public static function truncate($code) {
    	list($code, $length, $suffix) = explode('|', $code);
    	if (!$length) $length = 30;
    	if (!$suffix) $suffix = '...';
    	    	
        $lines = array(
            '$_fw_tmp_str = ' . $code . ';',
            'if(strlen($_fw_tmp_str) > ' . $length . ') {',
            '$_fw_tmp_str = substr($_fw_tmp_str, 0, ' . $length . ') . "' . $suffix . '"; }',
        	'echo nl2br(htmlspecialchars($_fw_tmp_str));',
            'unset($_fw_tmp_str);'
        );

        return '<?php ' . implode(' ', $lines) . ' ?>';
    	
    }

    /**
     * Echo a string on all cases where an iteration variable (specified with |$var_name after the string to print - defaults to $i) is greater than 0
     */
    public static function notfirsti($code) {
        $ivar = '$i';
        if(strpos($code, '|') !== false) list($code, $ivar) = explode('|', $code, 2);
        return "<?php if($ivar>0): ?>$code<?php endif; ?>";
    }
}
?>
