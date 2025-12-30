<?php

namespace App\Controllers;

use App\Models\Transaction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TransactionsController extends BaseController
{
    public function index(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        $perPage = $params['per_page'] ?? 15;
        $page = $params['page'] ?? 1;
        $search = $params['search'] ?? null;
        $query = Transaction::with(['item', 'recipe']);

        if ($search) {
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

        // Order by created_at descending (newest first) before pagination
        $query->orderBy('created_at', 'desc');

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
