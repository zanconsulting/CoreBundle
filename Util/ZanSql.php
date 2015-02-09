<?php


namespace Zan\CoreBundle\Util;

/**
 * Helper methods for raw sql queries
 */
class ZanSql 
{
    /**
     * @param $con \Doctrine\DBAL\Connection
     * @param $query string
     * @param array $params
     *
     * @return array
     */
    public static function toArray($con, $query, $params = array())
    {
        $r = self::query($con, $query, $params);
        $rows = array();

        while ($row = $r->fetch()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param $con \Doctrine\DBAL\Connection
     * @param $query string
     * @param array $params
     * @return null
     */
    public static function singleValue($con, $query, $params = array())
    {
        $r = self::query($con, $query, $params);
        $rows = array();

        while ($row = $r->fetch()) {
            $rows[] = $row;
        }

        if (count($rows) == 0) return null;

        $row = $rows[0];
        foreach ($row as $key => $value) {
            return $value;
        }

        return null;
    }

    /**
     * @param $con \Doctrine\DBAL\Connection
     * @param $query string
     * @param array $params
     * @return mixed
     */
    public static function query($con, $query, $params = array())
    {
        $stmt = $con->prepare($query);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * Escapes the value used in a LIKE query.
     *
     * This method ensures that characters with special meanings in LIKE queries
     * are correctly escaped.
     *
     * The default values for $prefix and $postfix result in the value being
     * allowed to appear anywhere in the string. To query for records that
     * with $value, set $prefix to an empty string.
     *
     * @param        $value
     * @param string $prefix
     * @param string $postfix
     * @return string
     */
    public static function escapeLikeParameter($value, $prefix = '%', $postfix = '%')
    {
        // ensure special characters % and _ are escaped
        $escapedValue = addcslashes($value, '%_');

        return sprintf('%s%s%s', $prefix, $escapedValue, $postfix);
    }
}