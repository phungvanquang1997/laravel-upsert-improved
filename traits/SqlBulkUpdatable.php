<?php

namespace App\Traits;

use App\Common\SqlCommon;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

trait SqlBulkUpdatable
{
    /**
     * <h2>Update multiple rows.</h2>
     *
     * Example:<br>
     * ```
     * $userInstance = new \App\Models\User;
     * $value = [
     *     [
     *         'id' => 1,
     *         'name' => 'active',
     *         'email' => 'Mohammad@gmail.com'
     *     ],
     *     [
     *         'id' => 5,
     *         'name' => 'deactive',
     *         'email' => 'quangpv@gmail.com'
     *     ],
     * ];
     *
     * $index = 'id';
     * ```
     *
     * Note:
     * example record:
     *      ['id' => ... ,
     *       'name' => ...,
     *       'email' => ...,
     *       'created_at' => ...,
     *      ...]
     *
     * fieldsUpdate = ['name', 'email']
     * => We just update 2 fields `name` and `email`
     *
     * exceptFields = ['name']
     * => We will update all fields except `name` field
     *
     *  - the fieldsUpdate has a higher priority than the exceptFields
     *  - the fieldsUpdate is empty array ([]), which indicates that all keys should be updated
     *
     * @param Model $table
     * @param array $values
     * @param string|null $index
     * @param array $fieldsUpdate
     * @param array $exceptFields
     * @param bool $raw
     * @return bool
     * @throws \Exception
     */
    public function bulkUpdate(Model $table, array $values, string $index = null, array $fieldsUpdate = [], array $exceptFields = [], bool $raw = false)
    {
        $final = [];
        $ids = [];

        if (!count($values)) {
            return false;
        }

        if (!isset($index) || empty($index)) {
            $index = $table->getKeyName();
        }

        array_map(function ($v, $k) use ($index, &$fieldsUpdate) {
            if ($v === $index) {
                unset($fieldsUpdate[$k]);
            }
        }, $fieldsUpdate, array_keys($fieldsUpdate));

        array_map(function ($v, $k) use ($index, &$exceptFields) {
            if ($v === $index) {
                unset($exceptFields[$k]);
            }
        }, $exceptFields, array_keys($exceptFields));

        $allFieldCase = false;

        if (!$fieldsUpdate && !$exceptFields) {
            $allFieldCase = true;
        }

        foreach ($values as $key => $val) {
            if ($val[$index] === null) {
                throw new \Exception("[$index] is not set in some data");
            }
            $ids[] = $val[$index];

            if ($table->usesTimestamps()) {
                $updatedAtColumn = $table->getUpdatedAtColumn();

                if (!isset($val[$updatedAtColumn])) {
                    $val[$updatedAtColumn] = Carbon::now()->format($table->getDateFormat());
                }
            }

            $fields = array_keys($val);
            if (!$allFieldCase && $fieldsUpdate) {
                $fields = array_intersect($fields, $fieldsUpdate);
            } else if (!$allFieldCase && $exceptFields) {
                $fields = array_diff($fields, $exceptFields);
            }

            foreach ($fields as $field) {
                if ($field !== $index) {

                    $finalField = $raw ? SqlCommon::mysql_escape($val[$field]) : "'" . SqlCommon::mysql_escape($val[$field]) . "'";
                    $value = (is_null($val[$field]) ? 'NULL' : $finalField);

                    $final[$field][] = 'WHEN `' . $index . '` = \'' . $val[$index] . '\' THEN ' . $value . ' ';
                }
            }
        }


        $cases = '';
        foreach ($final as $k => $v) {
            $cases .= '`' . $k . '` = (CASE ' . implode("\n", $v) . "\n"
                . 'ELSE `' . $k . '` END), ';
        }

        $query = "UPDATE `" . $table->getTable() . "` SET " . substr($cases, 0, -2) . " WHERE `$index` IN(" . '"' . implode('","', $ids) . '"' . ");";

        return \DB::statement($query);
    }
}