<?php

namespace FlightInfo\Service;


trait DatabaseService
{

	/**
	 * This is a simple utility function that creates
	 * a SQL INSERT string bases on the name of the table
	 * (1st parameter) and a associated array (2nd param).
	 *
	 * The INSERT string does not inject the actual values
	 * of the array but places a placeholder (:value_name)
	 * so this this string can be used in `prepare / execute`
	 * operation.
	 *
	 *
	 * @param $table
	 * @param array $data
	 * @return string valid MySQL insert string
	 */
	protected function insertString($table, array $data)
	{
        $data = array_keys($data);
        $columns = implode(',', array_map(function ($i) {
            return " `{$i}`";
        }, $data));
        $values = implode(',', array_map(function ($i) {
            return " :{$i}";
        }, $data));

        return "INSERT INTO `{$table}` ({$columns}) VALUES ({$values});";
    }

	/**
	 * This is a simple utility function that creates
	 * a SQL UPDATE string bases on the name of the table
	 * (1st parameter) and a associated array (2nd param)
	 * as well as a condition.
	 *
	 * The UPDATE string does not inject the actual values
	 * of the array but places a placeholder (:value_name)
	 * so this this string can be used in `prepare / execute`
	 * operation.
	 *
	 * @param $table
	 * @param $data
	 * @param $condition
	 * @return string
	 */
	protected function updateString($table, $data, $condition)
	{
        $data = array_keys($data);
        $columns = implode(',', array_map(function ($i) {
            return " `{$i}` = :{$i}";
        }, $data));

        return "UPDATE `{$table}` SET {$columns} WHERE {$condition};";
    }
}
