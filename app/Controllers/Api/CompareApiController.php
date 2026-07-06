<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\CompareService;

final class CompareApiController extends Controller
{
    public function toggle(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        if ($id <= 0) {
            return $this->json(['ok' => false, 'error' => 'شناسه محصول نامعتبر است.'], 422);
        }
        $res = (new CompareService())->toggle($id);
        return $this->json([
            'ok'    => $res['error'] === null,
            'in'    => $res['in'],
            'count' => $res['count'],
            'ids'   => $res['ids'],
            'error' => $res['error'],
        ]);
    }

    public function remove(Request $request): Response
    {
        (new CompareService())->remove((int) $request->input('id', 0));
        $svc = new CompareService();
        return $this->json(['ok' => true, 'count' => $svc->count(), 'ids' => $svc->ids()]);
    }

    public function clear(Request $request): Response
    {
        (new CompareService())->clear();
        return $this->json(['ok' => true, 'count' => 0, 'ids' => []]);
    }
}
