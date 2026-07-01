<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\FaqRepository;

final class FaqController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('storefront/faq', [
            'groups' => (new FaqRepository())->activeGrouped(),
        ]);
    }
}
