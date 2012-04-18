<?php

    /**
     * glue
     *
     * Provides an easy way to map URLs to classes. URLs can be literal
     * strings or regular expressions.
     *
     * When the URLs are processed:
     *      * delimiter (/) are automatically escaped: (\/)
     *      * The beginning and end are anchored (^ $)
     *      * An optional end slash is added (/?)
     *	    * The i option is added for case-insensitive searches
     *
     * Example:
     *
     * $urls = array(
     *     '/' => 'index',
     *     '/page/(\d+)' => 'page'
     * );
     *
     * class page {
     *      function GET($pageno) {
     *          echo "Your requested page " . $pageno;
     *      }
     * }
     *
     * glue::stick($urls);
     *
     */
    class glue {

        /**
         * stick
         *
         * the main static function of the glue class.
         *
         * @param   array    	$urls  	    The regex-based url to class mapping
         * @param   string      $directory  The name of the directory that the script is in
         * @throws  Exception               Thrown if corresponding class is not found
         * @throws  Exception               Thrown if no match is found
         * @throws  BadMethodCallException  Thrown if a corresponding GET,POST is not found
         *
         */
        static function stick ($urls, $directory) {

            $method = $_SERVER['REQUEST_METHOD'];
            $path = trim(str_replace('/'.trim($directory, '/').'/', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');
            
	    //avoid head requests causing 404 errors
	    $method = ($method == 'HEAD') ? 'GET' : $method; 

            $found = false;

            krsort($urls);

            foreach ($urls as $regex => $class) {
                $regex = str_replace('/', '\/', trim($regex, '/'));
                if (preg_match("/^$regex\$/i", $path, $matches)) {
                    $found = true;
                    if (class_exists($class)) {
                        $obj = new $class;
                        if (method_exists($obj, $method)) {
                        	// The first element of matches will be the entire uri
                        	if ( isset($matches[0]) )
                        	{
                        		unset($matches[0]);
                        	}
                        	call_user_func_array(array($obj,$method), $matches);
                        } else {
                            throw new BadMethodCallException("Method, $method, not supported.");
                        }
                    } else {
                        throw new Exception("Class, $class, not found.");
                    }
                    break;
                }
            }
            if (!$found) {
                throw new Exception("URL, $path, not found.");
            }
        }
    }
