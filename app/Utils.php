<?php

namespace App;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;

class Utils extends Model
{
    public static function arrayPaginator($array, $url, $request, $perPage = 10){
        $arrayTemp = array();

        foreach($array as $item) {
            $arrayTemp[] = $item;
        }

        $page   = (int) request()->get('page', 1);
        $offset = ($page * $perPage ) - $perPage;

        return new LengthAwarePaginator(
        array_slice($arrayTemp, $offset ,$perPage, true),
        count($arrayTemp),
        $perPage,
        $page,
        ['path' => $url, 'query' => request()->query()]);
    }
}
