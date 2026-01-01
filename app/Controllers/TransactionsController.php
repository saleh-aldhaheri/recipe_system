<?php

namespace App\Controllers;

use App\Models\Transaction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TransactionsController extends BaseController
{
    public function index(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();
        $page = (int) ($queryParams['page'] ?? 1);
        $perPage = (int) ($queryParams['per_page'] ?? 10);
        $search = $queryParams['search'] ?? '';
        $query = Transaction::with(['item', 'recipe']);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('type', 'LIKE', "%{$search}%")
                    ->orWhere('operation', 'LIKE', "%{$search}%")
                    ->orWhereHas('item', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('short_name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('recipe', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $query->orderByDesc('created_at');

        $transactions = $this->Paginate($query, $page, $perPage);

        if (isAjaxRequest($request)) {
            return jsonResponse($response, [
                'success' => true,
                'data' => $transactions['data'],
                'pagination' => $transactions['pagination'],
                'search' => $search,
            ]);
        }

        $html = view('transactions.index', [
            'title' => 'Transactions',
            'currentPage' => 'transactions',
            'transactions' => $transactions['data'],
            'pagination' => $transactions['pagination'],
            'search' => $search,
            'scripts' => '<script src="'.asset('js/transactions.js').'"></script>',
        ]);

        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
