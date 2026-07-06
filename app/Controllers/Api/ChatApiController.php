<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\ChatService;

/**
 * Storefront live-chat endpoints. The conversation is resolved server-side
 * from the visitor's session, so no ids are trusted from the client.
 */
final class ChatApiController extends Controller
{
    public function poll(Request $request): Response
    {
        $svc = new ChatService();
        if (!$svc->enabled()) {
            return $this->json(['ok' => false, 'enabled' => false], 404);
        }
        $res = $svc->poll((int) $request->query('after', 0));
        return $this->json(['ok' => true, 'enabled' => true] + $res);
    }

    public function send(Request $request): Response
    {
        $svc = new ChatService();
        if (!$svc->enabled()) {
            return $this->json(['ok' => false, 'enabled' => false], 404);
        }
        $res = $svc->send(
            (string) $request->input('message', ''),
            (string) $request->input('name', '')
        );
        return $this->json($res, $res['ok'] ? 200 : 422);
    }
}
