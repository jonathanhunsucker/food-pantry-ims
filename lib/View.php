<?php

class View_Filter_InvalidException extends Exception {};

class View {
    protected $filename;
    public $params;
    protected $filters;

    function __construct($filename, $params) {
        $this->filename = $filename;
        $this->params = $params;
        $this->filters = new View_Filters($this);
    }

    public function render($params=null) {
        if (!$params) $params = $this->params;
        $params["breadcrumbs"] = $this->generateBreadcrumbs();
        $block_regex = array(
            "(?<!#)\{((if|elseif|switch|case|foreach|for|while)\s*(\(.+?\)))\}",
            "(?<!#)\{(else|default)\}"
        );
        
        $endblock_regex = "(?<!#)\{(endif|endswitch|break|endforeach|endfor|endwhile|continue)\}";

        $filter_regex = array(
            '(?<!#)\{(\w+\|)?(\w+(\:{2}\w+)?\s*\(.*?\))\}',
            '(?<!#)\{(\w+\|)?(\$.+?)\}',
            '(?<!#)\{(\w+\|)(.+?)\}'
        );
        
        if (!$this->filename || !file_exists($this->filename)) throw new Exception("No such view file {$this->filename}");
        $src = file_get_contents($this->filename);

        foreach ($block_regex as $find) {//First, replace the blocks
            $src = preg_replace_callback("/$find/", array($this, "replaceBlock"), $src);
        }

        $src = preg_replace_callback("/$endblock_regex/", array($this, "replaceBlockEnd"), $src);//Then replace the block ends
        
        foreach ($filter_regex as $find) {
            $src = preg_replace_callback("/$find/", array($this, "applyFilters"), $src);
        }

        $temp_store = tempnam(sys_get_temp_dir(), "AVVIEW");
        file_put_contents($temp_store, $src);
        extract($params);
        ob_start();
        include $temp_store;
        $buffer = ob_get_clean();
        unlink($temp_store);

        return $buffer;
    }
    
    public function generateBreadcrumbs() {
        $crumbs = array();
        
        $url = $_SERVER["SCRIPT_URL"];
        $pages = explode("/", $url);
        if ($pages[count($pages)-1] === "") array_pop($pages);
        $page_names = array_map("ucfirst", array_map(function ($page) {return str_replace("-", " ", $page);}, $pages));
        if (count($page_names) == 1) return;
        $page_names[0] = "Home";
        
        for ($i=count($page_names)-1 ; $i >= 0 ; $i--) {
            $page_name = $page_names[$i];
            $crumb = array(
                "text" => $page_name
            );
            if ($i < count($page_names)-1) {
                $crumb["href"] = $url;
            } else {
                $crumb["active"] = true;
            }
            
            $url = rtrim(implode("/", array_slice($pages, 0, $i)), "/") . "/";
            
            array_unshift($crumbs, $crumb);
        }
        
        return $crumbs;
    }

    public function replaceBlock($matches) {
        return "<?php " . htmlspecialchars_decode($matches[1]) . ": ?>";
    }

    public function replaceBlockEnd($matches) {
        return "<?php " . htmlspecialchars_decode($matches[1]) . "; ?>";
    }
    
    public function dispatch() {
        echo $this->render($this->params);
    }
    
    public function dispatchDirect() {
        dump($this->params);
    }
    
             
    public function applyFilters($matches) {
        $filter = $matches[1];
        $subject = htmlspecialchars_decode($matches[2]);

        if ($filter) {
            $filter = substr($filter, 0, -1);
        } else {
            $filter = 'escape';
        }

        if (is_callable(array($this->filters, $filter))) {
            $return = call_user_func(array($this->filters, $filter), $subject);
            if ($return !== false) {
                return $return;
            }
        }

        throw new View_Filter_InvalidException('Invalid view filter used: ' . $filter);
    }
}

?>
