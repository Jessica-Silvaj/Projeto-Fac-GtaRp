<?php

namespace App;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

class Utils extends Model
{
    public static function arrayPaginator($array, $url, $request, $perPage = 10, $pageName = 'page')
    {
        $arrayTemp = array_values(is_array($array) ? $array : iterator_to_array($array));

        $page = max((int) ($request->get($pageName, 1)), 1);
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(
            array_slice($arrayTemp, $offset, $perPage, true),
            count($arrayTemp),
            $perPage,
            $page,
            [
                'path' => $url,
                'query' => $request->query(),
                'pageName' => $pageName,
            ]
        );
    }

    public static function getSequence($seq)
    {
        DB::table($seq)->insert([]);
        return DB::getPdo()->lastInsertId();
    }

    public static function formatarData($data)
    {
        if (!empty($data)) {
            return date("d/m/Y", strtotime($data));
        } else {
            return null;
        }
    }
}
