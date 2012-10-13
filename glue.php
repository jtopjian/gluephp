<?php
    /**
     * glue
     *
     * Provides an easy way to map URLs to controller files. URLs can be literal
     * strings or regular expressions.
     *
     *
     *    
     * When the URLs are processed:
     *      * deliminators (/) are automatically escaped: (\/)
     *      * The beginning and end are anchored (^ $)
     *      * An optional end slash is added (/?)
     *	    * The i option is added for case-insensitive searches
     *	    * Ignores get variables at the end of the URI
     *
     * Example:
     *
     * $urls = array(
     *     '/' => 'index',
     *     '/page/(\d+) => 'page'
     * );
     *
     * FILE: ./controllers/page.php
     * <?php
     *     echo "Your requested page " . $matches[1];
     * 
     *
     * glue::stick($urls, './controllers/');
     *
     */
    class glue {
        /**
         * stick
         *
         * the main static function of the glue class.
         *
         * @param   array    	$urls  	    The regex-based url to class mapping
         * @throws  Exception               Thrown if corresponding class is not found
         * @throws  Exception               Thrown if no match is found
         * @throws  BadMethodCallException  Thrown if a corresponding GET,POST is not found
         *
         */
        static function stick ($urls, $controller_path, $smarty=null, $pagenotfound=null ) {
            $path = $_SERVER['REQUEST_URI'];
            $found = false;
            krsort($urls);
            
            if (!is_dir($controller_path)) {
                throw new Exception("Path, $controller_path, is not a valid directory!");
            }
            
            foreach ($urls as $regex => $method) {
                $regex = str_replace('/', '\/', $regex);
                $regex = '^' . $regex . '(\/)?' . '(\?[a-zA-Z0-9]+=.*)?' . '$';
                if (preg_match("/$regex/i", $path, $matches)) {
                    $found = true;
                    $file_path = $controller_path . $method . ".php";
                    if(substr($method, -4) == ".tpl") { // Quick load template
                        $smarty->display($method);
                        break; 
                    }
                    if (file_exists($file_path)) {
                        include($file_path);
                    } else {
                        if ($pagenotfound == null) {
                            throw new Exception("Controller file, $file_path, not found!");
                        } else {
                            include($controller_path . $pagenotfound . ".php");
                        }
                    }
                    break;
                }
            }
            if (!$found) {
                if ($pagenotfound == null) {
                    throw new Exception("URL, $path, not found.");
                }
                else {
                    include($controller_path . $pagenotfound . ".php");
                }
            }
        }
    }
