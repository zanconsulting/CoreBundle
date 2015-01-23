<?php


namespace Zan\CoreBundle\Util;


class ZanPath {

    /**
     * Takes a variable number of arguments or an array as the first argument and returns a path
     * on the local filesystem.
     *
     * @returns string path on the local filesystem
     */
    public static function make() {
        $args = func_get_args();

        // Special case: $args is 1 argument, an array
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }

        $finStr = "";

        for ($i =0; $i < count($args); $i++) {
            if ($i != (count($args) - 1) && substr($args[$i], strlen($args[$i]) - 1, 1) != DIRECTORY_SEPARATOR) {
                $finStr .= $args[$i] . DIRECTORY_SEPARATOR;
            } else {
                $finStr .= $args[$i];
            }
        }

        /*
         * Replace double slashes, except:
         *  - stream wrappers like vfs://path/to/file.txt
         */
        $finStr = preg_replace("#([^:])//#", '${1}/', $finStr);

        return $finStr;
    }

    /**
     * Takes a variable number of arguments or an array as the first argument and returns a path
     * using the url path separator character (/)
     *
     * @returns string url path
     */
    public static function makeUrl() {
        $args = func_get_args();

        // Special case: $args is 1 argument, an array
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }

        $finStr = "";

        for ($i =0; $i < count($args); $i++) {
            if ($i != (count($args) - 1) && substr($args[$i], strlen($args[$i]) - 1, 1) != '/') {
                $finStr .= $args[$i] . '/';
            } else {
                $finStr .= $args[$i];
            }
        }

        // Clean up multiple slashes, accounting for protocol://
        do {
            $finStr = preg_replace("/(?<!:)\/\//", "/", $finStr);
        } while (preg_match("/(?<!:)\/\//", $finStr));

        return $finStr;
    }

    /**
     * Takes a variable number of arguments or an array as the first argument and returns a path
     * using the namespace path separator character (\)
     *
     * @returns string namespace path
     */
    public static function makeNamespace() {
        $args = func_get_args();

        // Special case: $args is 1 argument, an array
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }

        $finStr = "";

        for ($i =0; $i < count($args); $i++) {
            if ($i != (count($args) - 1) && substr($args[$i], strlen($args[$i]) - 1, 1) != '\\') {
                $finStr .= $args[$i] . '\\';
            } else {
                $finStr .= $args[$i];
            }
        }

        // Clean up multiple backslashes
        do {
            $finStr = str_replace("\\\\", "\\", $finStr);
        } while (strstr($finStr, "\\\\"));

        return $finStr;
    }

    /**
     * Given a fully qualified path to a file, it returns the files namespace (including the
     * class name)
     *
     * NOTE: This only works for files that are part of the src/ directory tree.
     *
     * @param $path
     * @return string
     */
    public static function getNamespaceFromPath($path)
    {
        // Remove the part of the path that is common to all source files
        $currDirRelPath = self::make("Zan", "CoreBundle", "Util");
        $commonPath = ZanString::removePostfix(__DIR__, $currDirRelPath);

        $fileNs = ZanString::removePrefix($path, $commonPath);

        // Ensure delimiter is a backslash
        $fileNs = str_replace("/", "\\", $fileNs);

        // Remove .php ending
        $fileNs = ZanString::removePostfix($fileNs, ".php");

        return $fileNs;
    }
}