<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\BlogCategoryRepository;
use App\Repositories\BlogCommentRepository;
use App\Repositories\BlogPostRepository;
use App\Services\MediaService;

final class BlogController extends AdminController
{
    private BlogPostRepository $posts;

    public function __construct()
    {
        $this->posts = new BlogPostRepository();
    }

    /* ───────────────────────── Posts ───────────────────────── */

    public function index(Request $request): Response
    {
        if ($r = $this->guard('blog')) {
            return $r;
        }
        $search  = trim((string) $request->query('q', ''));
        $perPage = 20;
        $page    = max(1, (int) $request->query('page', 1));
        $total   = $this->posts->adminCount($search);

        return $this->adminView('admin/blog/index', [
            'items'   => $this->posts->adminList($search, $perPage, ($page - 1) * $perPage),
            'total'   => $total,
            'page'    => $page,
            'pages'   => (int) ceil($total / $perPage),
            'search'  => $search,
            'pending' => (new BlogCommentRepository())->pendingCount(),
        ], 'مجله');
    }

    public function create(Request $request): Response
    {
        if ($r = $this->guard('blog')) {
            return $r;
        }
        return $this->form(null);
    }

    public function edit(Request $request): Response
    {
        if ($r = $this->guard('blog')) {
            return $r;
        }
        $post = $this->posts->find((int) $request->param('id'));
        if ($post === null) {
            return $this->notFound();
        }
        return $this->form($post);
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guard('blog')) {
            return $r;
        }
        $data = $this->collect($request);
        if ($data['title'] === '') {
            Session::flash('error', 'عنوان مطلب الزامی است.');
            return $this->redirect(url('/admin/blog/create'));
        }
        $data['slug']        = $this->uniqueSlug($data['slug'] !== '' ? $data['slug'] : $data['title'], 0);
        $data['cover_image'] = $this->handleCover() ?? null;

        $id = $this->posts->insert($data);
        $this->audit($request, 'create', 'blog_post', $id, $data['title']);
        Session::flash('success', 'مطلب ایجاد شد.');
        return $this->redirect(url('/admin/blog/' . $id . '/edit'));
    }

    public function update(Request $request): Response
    {
        if ($r = $this->guard('blog')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $post = $this->posts->find($id);
        if ($post === null) {
            return $this->notFound();
        }
        $data = $this->collect($request);
        if ($data['title'] === '') {
            Session::flash('error', 'عنوان مطلب الزامی است.');
            return $this->redirect(url('/admin/blog/' . $id . '/edit'));
        }
        $data['slug']        = $this->uniqueSlug($data['slug'] !== '' ? $data['slug'] : $data['title'], $id);
        $data['cover_image'] = $this->handleCover() ?? ($post['cover_image'] ?: null);

        $this->posts->update($id, $data);
        $this->audit($request, 'update', 'blog_post', $id, $data['title']);
        Session::flash('success', 'تغییرات ذخیره شد.');
        return $this->redirect(url('/admin/blog/' . $id . '/edit'));
    }

    public function destroy(Request $request): Response
    {
        if ($r = $this->guard('blog')) {
            return $r;
        }
        $id   = (int) $request->param('id');
        $post = $this->posts->find($id);
        if ($post !== null && !empty($post['cover_image'])) {
            (new MediaService())->delete((string) $post['cover_image']);
        }
        $this->posts->delete($id);
        $this->audit($request, 'delete', 'blog_post', $id);
        Session::flash('success', 'مطلب حذف شد.');
        return $this->redirect(url('/admin/blog'));
    }

    /* ───────────────────────── Categories ───────────────────────── */

    public function categories(Request $request): Response
    {
        if ($r = $this->guard('blog')) {
            return $r;
        }
        return $this->adminView('admin/blog/categories', [
            'items' => (new BlogCategoryRepository())->all(),
        ], 'دسته‌های مجله');
    }

    public function categoryStore(Request $request): Response
    {
        if ($r = $this->guard('blog')) {
            return $r;
        }
        $repo = new BlogCategoryRepository();
        $id   = (int) $request->param('id');
        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            Session::flash('error', 'نام دسته الزامی است.');
            return $this->redirect(url('/admin/blog/categories'));
        }
        $slug = slugify((string) $request->input('slug', '') ?: $name);
        $n = 2; $base = $slug;
        while ($repo->slugExists($slug, $id)) { $slug = $base . '-' . $n++; }
        $data = [
            'name'      => $name,
            'slug'      => $slug,
            'sort'      => (int) en_num((string) $request->input('sort', 0)),
            'is_active' => $request->input('is_active') ? 1 : 0,
        ];
        if ($id > 0) {
            $repo->update($id, $data);
        } else {
            $id = $repo->insert($data);
        }
        $this->audit($request, $id ? 'update' : 'create', 'blog_category', $id, $name);
        Session::flash('success', 'دسته ذخیره شد.');
        return $this->redirect(url('/admin/blog/categories'));
    }

    public function categoryDelete(Request $request): Response
    {
        if ($r = $this->guard('blog')) {
            return $r;
        }
        $id = (int) $request->param('id');
        (new BlogCategoryRepository())->delete($id);
        $this->audit($request, 'delete', 'blog_category', $id);
        Session::flash('success', 'دسته حذف شد.');
        return $this->redirect(url('/admin/blog/categories'));
    }

    /* ───────────────────────── Comments ───────────────────────── */

    public function comments(Request $request): Response
    {
        if ($r = $this->guard('blog')) {
            return $r;
        }
        $status  = (string) $request->query('status', 'pending');
        $perPage = 30;
        $page    = max(1, (int) $request->query('page', 1));
        $repo    = new BlogCommentRepository();
        $total   = $repo->adminCount($status);

        return $this->adminView('admin/blog/comments', [
            'items'  => $repo->adminList($status, $perPage, ($page - 1) * $perPage),
            'status' => $status,
            'total'  => $total,
            'page'   => $page,
            'pages'  => (int) ceil($total / $perPage),
        ], 'دیدگاه‌های مجله');
    }

    public function commentModerate(Request $request): Response
    {
        if ($r = $this->guard('blog')) {
            return $r;
        }
        $id     = (int) $request->param('id');
        $action = (string) $request->input('action', '');
        $repo   = new BlogCommentRepository();
        if ($action === 'delete') {
            $repo->delete($id);
        } elseif (in_array($action, ['approved', 'rejected', 'pending'], true)) {
            $repo->setStatus($id, $action);
        }
        $this->audit($request, 'moderate', 'blog_comment', $id, $action);
        Session::flash('success', 'دیدگاه به‌روزرسانی شد.');
        return $this->redirect(url('/admin/blog/comments?status=' . urlencode((string) $request->input('return_status', 'pending'))));
    }

    /* ───────────────────────── helpers ───────────────────────── */

    private function form(?array $post): Response
    {
        return $this->adminView('admin/blog/form', [
            'post'       => $post,
            'categories' => (new BlogCategoryRepository())->all(),
        ], $post ? 'ویرایش مطلب' : 'مطلب جدید');
    }

    /** @return array<string,mixed> */
    private function collect(Request $request): array
    {
        $status    = $request->input('status') === 'published' ? 'published' : 'draft';
        $publishAt = trim((string) $request->input('published_at', ''));
        return [
            'category_id'     => ($c = (int) en_num((string) $request->input('category_id', 0))) > 0 ? $c : null,
            'title'           => trim((string) $request->input('title', '')),
            'slug'            => slugify((string) $request->input('slug', '')),
            'excerpt'         => trim((string) $request->input('excerpt', '')) ?: null,
            'body'            => html_clean((string) $request->input('body', '')),
            'author_name'     => trim((string) $request->input('author_name', '')) ?: null,
            'status'          => $status,
            'is_featured'     => $request->input('is_featured') ? 1 : 0,
            'seo_title'       => trim((string) $request->input('seo_title', '')) ?: null,
            'seo_description' => trim((string) $request->input('seo_description', '')) ?: null,
            'published_at'    => $status === 'published'
                ? (preg_match('/^\d{4}-\d{2}-\d{2}/', $publishAt) ? $publishAt : date('Y-m-d H:i:s'))
                : null,
        ];
    }

    private function handleCover(): ?string
    {
        if (empty($_FILES['cover']) || ($_FILES['cover']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        return (new MediaService())->store($_FILES['cover'], 'blog');
    }

    private function uniqueSlug(string $base, int $exceptId): string
    {
        $slug = slugify($base) ?: 'post';
        $candidate = $slug;
        $n = 2;
        while ($this->posts->slugExists($candidate, $exceptId)) {
            $candidate = $slug . '-' . $n++;
        }
        return $candidate;
    }
}
