<?php

namespace App\Controllers;

use App\Models\Item;
use App\Requests\ItemsRequests\ShowItemsRequest;
use App\Requests\ItemsRequests\StoreItemsRequest;
use App\Requests\ItemsRequests\UpdateItemsRequest;
use App\Services\ItemsService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ItemsController extends BaseController
{
    public function __construct(
        private ItemsService $itemsService
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int) ($queryParams['page'] ?? 1);
        $perPage = (int) ($queryParams['per_page'] ?? 10);
        $search = $queryParams['search'] ?? '';
        $status = $queryParams['status'] ?? '';

        $query = Item::query();

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_name', 'like', "%{$search}%");
            });
        }

        if ($status === 'in_stock') {
            $query->where('balance', '>', 0);
        } elseif ($status === 'out_of_stock') {
            $query->where('balance', '=', 0);
        }

        $items = $this->Paginate($query, $page, $perPage, 'name');

        if (isAjaxRequest($request)) {
            return jsonResponse($response, [
                'success' => true,
                'data' => $items['data'],
                'pagination' => $items['pagination'],
                'search' => $search,
            ]);
        }

        $html = view('items.index', [
            'title' => 'Manager Items',
            'currentPage' => 'items',
            'items' => $items['data'],
            'pagination' => $items['pagination'],
            'search' => $search,
            'scripts' => '<script src="'.asset('js/items.js').'"></script>',
        ]);

        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $validated = (new ShowItemsRequest($args))->validate($request);
        $id = $validated['id'];

        $item = Item::findOrFail($id);

        return jsonResponse($response, [
            'success' => true,
            'data' => $item,
        ]);
    }

    public function store(Request $request, Response $response): Response
    {
        $validatedData = (new StoreItemsRequest)->validate($request);
        $item = Item::create($validatedData);

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Item created successfully',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        $item = Item::findOrFail($id);

        $validatedData = (new UpdateItemsRequest($id))->validate($request);

        $this->itemsService->updateItem($item, $validatedData);

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Item updated successfully',
            'data' => $item,
        ]);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $item = Item::findOrFail($id);
        $item->delete();

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Item deleted successfully',
        ]);
    }
}
