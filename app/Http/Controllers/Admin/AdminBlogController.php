<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\storeBlogRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
use App\Models\Blog;
use App\Models\Cat;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminBlogController extends Controller
{
    // ブログ一覧画面
    public function index()
    {
        $blogs = Blog::latest('updated_at')->simplePaginate(10);
        $user = Auth::user();
        return view('admin.blogs.index', [
            'blogs' => $blogs,
            'user' => $user,
        ]);
    }

    // ブログ投稿画面
    public function create()
    {
        return view('admin.blogs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(storeBlogRequest $request)
    {
        $savedImagePath = $request->file('image')->store('blog', 'public');
        $blog = new Blog($request->validated());
        $blog->image = $savedImagePath;
        $blog->save();

        return to_route('admin.blogs.index')->with('success', 'ブログを投稿しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        $categories = Category::all();
        $cats = Cat::all();
        return view('admin.blogs.edit', [
            'blog' => $blog,
            'categories' => $categories,
            'cats' => $cats,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlogRequest $request, string $id)
    {
        $blog = Blog::findOrFail($id);
        $updateData = $request->validated();
        // 画像を変更する場合
        if ($request->has('image')) {
            // 画像データの削除
            Storage::disk('public')->delete($blog->image);
            // 新しい画像データを取得
            $updateData['image'] = $request->file('image')->store('blog', 'public');
        }
        $blog->category()->associate($updateData['category_id']);
        $blog->cats()->sync($updateData['cats'] ?? []);
        $blog->update($updateData);

        return to_route('admin.blogs.index')->with('success', 'ブログを更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blog = Blog::findOrFail($id);
        $blog->delete();
        Storage::disk('public')->delete($blog->image);
        return to_route('admin.blogs.index')->with('success', 'ブログを削除しました');
    }
}
