<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\BlogCategoryRepository;
use App\Repositories\BlogCommentRepository;
use App\Repositories\BlogPostRepository;
use App\Services\AuthService;

final class BlogController extends Controller
{
    private BlogPostRepository $posts;

    public function __construct()
    {
        $this->posts = new BlogPostRepository();
    }

    public function index(Request $request): Response
    {
        $perPage    = 9;
        $page       = max(1, (int) $request->query('page', 1));
        $categories = new BlogCategoryRepository();

        $catSlug  = trim((string) $request->param('slug', ''));
        $category = $catSlug !== '' ? $categories->findBySlug($catSlug) : null;
        if ($catSlug !== '' && $category === null) {
            return $this->notFound();
        }
        $categoryId = $category !== null ? (int) $category['id'] : null;
        $total      = $this->posts->publishedCount($categoryId);

        return $this->view('storefront/blog/index', [
            'posts'      => $this->posts->published($categoryId, $perPage, ($page - 1) * $perPage),
            'categories' => $categories->activeWithCounts(),
            'category'   => $category,
            'page'       => $page,
            'pages'      => (int) ceil($total / $perPage),
            'total'      => $total,
        ]);
    }

    public function show(Request $request): Response
    {
        $post = $this->posts->findPublishedBySlug((string) $request->param('slug'));
        if ($post === null) {
            return $this->notFound();
        }
        $this->posts->incrementViews((int) $post['id']);

        return $this->view('storefront/blog/show', [
            'post'     => $post,
            'comments' => (new BlogCommentRepository())->approvedForPost((int) $post['id']),
            'related'  => $this->posts->published($post['category_id'] ? (int) $post['category_id'] : null, 3, 0),
        ]);
    }

    public function comment(Request $request): Response
    {
        $post = $this->posts->findPublishedBySlug((string) $request->param('slug'));
        if ($post === null) {
            return $this->notFound();
        }

        $body = trim((string) $request->input('body', ''));
        $user = AuthService::user();
        $name = $user !== null
            ? trim((string) $user['first_name'] . ' ' . (string) $user['last_name'])
            : trim((string) $request->input('author_name', ''));

        if ($name === '' || mb_strlen($body) < 3) {
            Session::flash('error', 'نام و متن دیدگاه را کامل وارد کنید.');
            return $this->redirect(url('/blog/' . $post['slug']) . '#comments');
        }

        (new BlogCommentRepository())->create([
            'post_id'     => (int) $post['id'],
            'user_id'     => $user !== null ? (int) $user['id'] : null,
            'author_name' => $name !== '' ? $name : 'کاربر مهمان',
            'body'        => mb_substr($body, 0, 1500),
            'status'      => 'pending',
        ]);

        Session::flash('success', 'دیدگاه شما ثبت شد و پس از تأیید نمایش داده می‌شود.');
        return $this->redirect(url('/blog/' . $post['slug']) . '#comments');
    }
}
