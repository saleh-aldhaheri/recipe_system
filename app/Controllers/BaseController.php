<?php

namespace App\Controllers;

abstract class BaseController
{
    protected function Paginate($query, int $page, int $perPage, ?string $orderBy = null): array
    {
        $total = $query->count();

        if (! empty($orderBy) && empty($query->getQuery()->orders)) {
            $query = $query->orderBy($orderBy);
        }

        $data = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ];
    }
}
