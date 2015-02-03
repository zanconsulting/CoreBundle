<?php

namespace Zan\CoreBundle\Logging;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\QueryBuilder;

/**
 * Utility class to write log messages to a file
 */
class QuickLogger
{
    private static $numEntriesWritten = 0;
    private static $numLinesWritten = 0;

    private static $cachedLogfile = null;

    public static function log() {
        $args = func_get_args();
        $stackOffset = 0;

        $logStr = "";

        $logParts = array();
        foreach ($args as $arg) {
            if ($arg instanceof \DateTime) {
                $logMsg = date_format($arg, "Y-m-d h:i:sa");
            }
            elseif (is_array($arg)) {
                $logMsg = "\n" . print_r(Debug::export($arg, 2), true);
            }
            elseif ($arg instanceof QueryBuilder) {
                QuickLogger::logDql($arg);
                continue;
            }
            elseif ($arg instanceof QueryBuilder) {
                QuickLogger::logDql($arg);
                continue;
            }
            elseif (is_object($arg)) {
                $logMsg = print_r(Debug::export($arg, 2), true);
            }
            else {
                $logMsg = $arg;
            }

            // Put a limit on how much data will be logged
            if (strlen($logMsg) > 32000) {
                $logMsg = substr($logMsg, 0, 32000);
                $logMsg .= "\n[TRUNCATED]";
            }

            $logParts[] = $logMsg;
        }

        $logStr = join(" ", $logParts);

        // Determine how many lines are going to be logged to the file
        $lines = explode("\n", $logStr);
        $numLines = count($lines);

        // ---------------------------------
        // Generate a prefix for the log entry so it looks better
        $logPrefix = "";

        // Add in the current date
        $logPrefix .= "[" . date("h:i:sA") . "] ";

        // Figure out what file called us
        $stackInfo = debug_backtrace();
        $stackStr = basename($stackInfo[$stackOffset]['file']) . ":" . $stackInfo[$stackOffset]['line'];
        $logPrefix .= "[" . $stackStr . "] ";

        // Write to the log file and update our stats
        file_put_contents(self::getLogFile(), $logPrefix . $logStr . "\n", FILE_APPEND);
        self::$numEntriesWritten++;
        self::$numLinesWritten += $numLines;
    }

    public static function logDql(QueryBuilder $qb)
    {
        $dql = $qb->getDQL();
        $dql = "\n" . $dql;
        $dql = str_replace(",", ",\n", $dql);
        $dql = str_replace(" FROM ", "\n\nFROM ", $dql);
        $dql = str_replace(" INNER JOIN ", "\nINNER JOIN ", $dql);
        $dql = str_replace(" LEFT JOIN ", "\nLEFT JOIN ", $dql);
        $dql = str_replace(" WHERE ", "\n\nWHERE ", $dql);
        $dql = str_replace(" ORDER BY ", "\n\nORDER BY ", $dql);

        QuickLogger::log("builder: ", $dql);
        QuickLogger::log($qb->getParameters());
    }

    /**
     * Logs the output of var_dump() for the specified object
     * @param $object
     */
    public static function var_dump($object)
    {
        // Disable html errors
        $oldHtmlErrors = ini_get("html_errors");
        ini_set("html_errors", false);
        ob_start();
        var_dump($object);
        $output = ob_get_contents();
        ob_end_clean();
        ini_set("html_errors", $oldHtmlErrors);

        self::log($output);
    }

    /**
     * @access private
     * @internal
     */
    public static function zz_flushLogs($requestId)
    {
        $postRequestStr = "";

        // If we've written a lot of entries or a lot of lines, clearly mark the end of the request
        if (self::$numEntriesWritten > 5 || self::$numLinesWritten > 10) {
            $postRequestStr .= "------------------------------------ End of Request $requestId ------------------------------------\n";
        }

        // If we've written at least one thing, put a line break in
        if (self::$numEntriesWritten > 0) {
            $postRequestStr .= "\n";
        }

        if ($postRequestStr) {
            file_put_contents(self::getLogFile(), $postRequestStr, FILE_APPEND);
        }

        self::$numEntriesWritten = 0;
        self::$numLinesWritten = 0;
    }

    private static function getLogFile()
    {
        if (null === self::$cachedLogfile) {
            // Log to a file based on what our host name is
            $hostname = null;

            $parametersPath = __DIR__ . '/../../../../app/config/parameters.yml';
            if (file_exists($parametersPath)) {
                $parametersContents = file_get_contents($parametersPath);
                $matches = array();
                $foundMatches = preg_match('/router.request_context.host:\s?(.*)$/m', $parametersContents, $matches);
                if ($foundMatches) {
                    $hostname = $matches[1];
                }
            }

            // If that didn't work fallback tousing servername
            if (!$hostname) {
                if (isset($_SERVER['SERVER_NAME'])) {
                    $hostname = $_SERVER['SERVER_NAME'];
                }
                if (!$hostname) {
                    $hostname = "unknown";
                }
            }

            $hostname = str_replace(".", "-", $hostname);

            self::$cachedLogfile = "/tmp/" . "zanlog-" . $hostname . ".log";
        }

        return self::$cachedLogfile;
    }
}
