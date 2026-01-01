<?php

namespace App\Requests\DashboardRequest;

use App\Exceptions\ValidationException;
use App\Requests\RequestInterface;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

class IndexDashboardRequest implements RequestInterface
{
    public function validate(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody() ?? [];

        if (empty($body)) {
            $rawBody = (string) $request->getBody();
            if (! empty($rawBody)) {
                $body = json_decode($rawBody, true) ?? [];
            }
        }

        $from = $body['from'] ?? null;
        $to = $body['to'] ?? null;

        if (! $from || ! $to) {
            throw new ValidationException([
                'dates' => 'Missing required fields: from and to dates. Received: '.json_encode($body),
            ]);
        }

        $fromDate = DateTime::createFromFormat('Y-m-d', $from);
        $toDate = DateTime::createFromFormat('Y-m-d', $to);

        $fromErrors = DateTime::getLastErrors();
        $toErrors = DateTime::getLastErrors();

        $fromHasErrors = $fromErrors !== false && ($fromErrors['warning_count'] > 0 || $fromErrors['error_count'] > 0);
        $toHasErrors = $toErrors !== false && ($toErrors['warning_count'] > 0 || $toErrors['error_count'] > 0);

        if (! $fromDate || ! $toDate || $fromHasErrors || $toHasErrors) {
            throw new ValidationException([
                'format' => 'Dates must follow Y-m-d format (e.g., 2025-01-15)',
            ]);
        }

        if ($fromDate > $toDate) {
            throw new ValidationException([
                'range' => 'From date must be earlier than or equal to To date',
            ]);
        }

        return [
            'from' => $fromDate,
            'to' => $toDate,
        ];
    }
}
