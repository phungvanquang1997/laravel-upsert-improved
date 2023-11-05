<?php

namespace App\Traits;

use Carbon\Carbon;

trait WantsUpsertQuery
{
    use SqlBulkUpdatable {
        SqlBulkUpdatable::bulkUpdate as _bulkUpdate;
    }

    /**
     *
     * $data = [
     *     [
     *         'id' => 1,
     *         'status' => 'active',
     *         'nickname' => 'Mohammad'
     *     ],
     *     [
     *         'id' => 5,
     *         'status' => 'deactive',
     *         'nickname' => 'QuangPV'
     *     ],
     * ];
     *
     * @param array $data
     * @param string $index
     * @raw
     * @param array $fieldsUpdate
     * @param array $exceptFields
     * @param bool $raw
     * @return void
     * @throws \Exception
     */
    public function wantsUpsert(array $data, string $index = 'id', array $fieldsUpdate = [], array $exceptFields = [], bool $raw = false)
    {
        $dataForCreating = [];

        $dataForUpdating = $data;

        $ids = collect($data)->pluck("$index");

        $idsInDb = $this->query()->whereIn("$index", $ids)->select("$index")->get()->pluck("$index");

        $diffIds = $ids->diff($idsInDb);

        foreach ($diffIds as $diffId) {
            foreach ($dataForUpdating as $k => $v) {
                if ($v[$index] === $diffId) {
                    $record = $v;
                    $record[$this->getCreatedAtColumn()] = $record[$this->getUpdatedAtColumn()] = Carbon::now()->format($this->getDateFormat());
                    $dataForCreating[] = $record;
                    unset($dataForUpdating[$k]);
                }
            }
        }

        if ($dataForCreating) {
            $this->query()->insert($dataForCreating);
        } if ($dataForUpdating) {
            $this->_bulkUpdate($this, $dataForUpdating, $index, $fieldsUpdate, $exceptFields, $raw);
        }
    }
}