<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\MediaLibraryService;

final class MediaController extends AdminController
{
    private MediaLibraryService $media;

    public function __construct()
    {
        $this->media = new MediaLibraryService();
    }

    public function index(Request $request): Response
    {
        if ($r = $this->guard('media')) {
            return $r;
        }
        $folder = preg_replace('/[^a-z0-9_-]/i', '', (string) $request->query('folder', '')) ?? '';
        return $this->adminView('admin/media/index', [
            'items'   => $this->media->list($folder),
            'folders' => $this->media->folders(),
            'folder'  => $folder,
            'stats'   => $this->media->stats(),
        ], 'کتابخانه رسانه');
    }

    public function upload(Request $request): Response
    {
        if ($r = $this->guard('media')) {
            return $r;
        }
        $folder = trim((string) $request->input('folder', 'library')) ?: 'library';
        $ok = 0;
        $failed = 0;

        // Support single or multiple file inputs (name="files[]").
        $files = $this->normalizeFiles();
        foreach ($files as $file) {
            if ($this->media->store($file, $folder) !== null) {
                $ok++;
            } else {
                $failed++;
            }
        }

        if ($ok > 0) {
            $this->audit($request, 'upload', 'media', null, $folder . ' ×' . $ok);
            Session::flash('success', fa($ok) . ' فایل بارگذاری شد.' . ($failed ? ' ' . fa($failed) . ' فایل نامعتبر بود.' : ''));
        } else {
            Session::flash('error', 'هیچ فایل معتبری بارگذاری نشد (فرمت یا حجم نامعتبر).');
        }
        return $this->redirect(url('/admin/media' . ($folder !== 'library' ? '?folder=' . urlencode($folder) : '')));
    }

    public function delete(Request $request): Response
    {
        if ($r = $this->guard('media')) {
            return $r;
        }
        $path   = (string) $request->input('path', '');
        $folder = (string) $request->input('return_folder', '');
        if ($this->media->delete($path)) {
            $this->audit($request, 'delete', 'media', null, $path);
            Session::flash('success', 'فایل حذف شد.');
        } else {
            Session::flash('error', 'حذف فایل ممکن نشد.');
        }
        return $this->redirect(url('/admin/media' . ($folder !== '' ? '?folder=' . urlencode($folder) : '')));
    }

    /**
     * Flatten $_FILES['files'] (multi) into a list of single-file arrays.
     * @return list<array{name:string,type:string,tmp_name:string,error:int,size:int}>
     */
    private function normalizeFiles(): array
    {
        if (empty($_FILES['files']) || !isset($_FILES['files']['name'])) {
            return [];
        }
        $f = $_FILES['files'];
        $out = [];
        if (is_array($f['name'])) {
            $count = count($f['name']);
            for ($i = 0; $i < $count; $i++) {
                if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $out[] = [
                    'name'     => (string) $f['name'][$i],
                    'type'     => (string) $f['type'][$i],
                    'tmp_name' => (string) $f['tmp_name'][$i],
                    'error'    => (int) $f['error'][$i],
                    'size'     => (int) $f['size'][$i],
                ];
            }
        } else {
            $out[] = [
                'name' => (string) $f['name'], 'type' => (string) $f['type'],
                'tmp_name' => (string) $f['tmp_name'], 'error' => (int) $f['error'], 'size' => (int) $f['size'],
            ];
        }
        return $out;
    }
}
