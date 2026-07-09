<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\SettingsRepository;

final class SettingController extends AdminController
{
    /** Editable settings: key => type. */
    private const FIELDS = [
        'brand_name'              => 'string',
        'announcement_text'       => 'string',
        'show_announcement'       => 'bool',
        'free_shipping_threshold' => 'int',
        'low_stock_threshold'     => 'int',
        'flash_sale_ends_at'      => 'string',
        'chat_enabled'            => 'bool',
    ];

    public function index(Request $request): Response
    {
        if ($r = $this->guard('settings')) {
            return $r;
        }
        return $this->adminView('admin/settings/index', ['fields' => self::FIELDS], 'تنظیمات');
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('settings')) {
            return $r;
        }
        $repo = new SettingsRepository();
        foreach (self::FIELDS as $key => $type) {
            if ($type === 'bool') {
                $value = $request->input($key) ? '1' : '0';
            } elseif ($type === 'int') {
                $value = (string) (int) en_num((string) $request->input($key, '0'));
            } else {
                $value = trim((string) $request->input($key, ''));
            }
            $repo->set($key, $value, $type);
        }
        $this->audit($request, 'update', 'settings', null, 'general');
        Session::flash('success', 'تنظیمات ذخیره شد.');
        return $this->redirect(url('/admin/settings'));
    }
}
