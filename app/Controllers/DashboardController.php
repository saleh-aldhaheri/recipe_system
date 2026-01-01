<?php

namespace App\Controllers;

use App\Requests\DashboardRequest\IndexDashboardRequest;
use App\Services\DashboardService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController
{
    public function __construct(
        private DashboardService $dashboard
    ) {}

    public function index(Request $request, Response $response): Response
    {
        if (isAjaxRequest($request)) {
            return jsonResponse($response, [
                'success' => true,
            ]);
        }

        $html = view('dashboard.index', [
            'title' => 'Dashboard',
            'currentPage' => 'dashboard',
            'scripts' => '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script><script src="'.asset('js/dashboard.js').'"></script>',
        ]);

        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    public function getItemsSummary(Request $request, Response $response): Response
    {
        $validated = (new IndexDashboardRequest)->validate($request);

        $itemsSummary = $this->dashboard->getItemsSummary(
            $validated['from'],
            $validated['to']
        );

        return jsonResponse($response, [
            'success' => true,
            'data' => $itemsSummary,
        ]);
    }

    public function getStats(Request $request, Response $response): Response
    {
        $validated = (new IndexDashboardRequest)->validate($request);

        $stats = $this->dashboard->getDashboardStats(
            $validated['from'],
            $validated['to']
        );

        return jsonResponse($response, [
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getTransactionsByType(Request $request, Response $response): Response
    {
        $validated = (new IndexDashboardRequest)->validate($request);

        $data = $this->dashboard->getTransactionsByType(
            $validated['from'],
            $validated['to']
        );

        return jsonResponse($response, [
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getTopItems(Request $request, Response $response): Response
    {
        $validated = (new IndexDashboardRequest)->validate($request);
        $body = $request->getParsedBody() ?? [];
        $limit = (int) ($body['limit'] ?? 10);

        $data = $this->dashboard->getTopItemsByUsage(
            $validated['from'],
            $validated['to'],
            $limit
        );

        return jsonResponse($response, [
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getDailyTrend(Request $request, Response $response): Response
    {
        $validated = (new IndexDashboardRequest)->validate($request);

        $data = $this->dashboard->getDailyUsageTrend(
            $validated['from'],
            $validated['to']
        );

        return jsonResponse($response, [
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getUsageByRecipes(Request $request, Response $response): Response
    {
        $validated = (new IndexDashboardRequest)->validate($request);
        $body = $request->getParsedBody() ?? [];
        $limit = (int) ($body['limit'] ?? 10);

        $data = $this->dashboard->getUsageByRecipes(
            $validated['from'],
            $validated['to'],
            $limit
        );

        return jsonResponse($response, [
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getItemRecipes(Request $request, Response $response, array $args): Response
    {
        $itemId = (int) ($args['id'] ?? 0);

        if ($itemId <= 0) {
            return jsonResponse($response, [
                'success' => false,
                'message' => 'Invalid item ID',
            ], 400);
        }

        $validated = (new IndexDashboardRequest)->validate($request);

        $data = $this->dashboard->getItemRecipes(
            $itemId,
            $validated['from'],
            $validated['to']
        );

        return jsonResponse($response, [
            'success' => true,
            'data' => $data,
        ]);
    }
}
