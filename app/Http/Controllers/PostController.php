<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostTranslation;
use App\Traits\ResponseTrait;
use App\Traits\UploadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
{
    use ResponseTrait, UploadImage;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with(['category', 'translation'])->get();
        return $this->returnData($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|exists:categories,id',
            'image' => 'required|file|max:1000|mimes:jpg,png',
            'translations.*.title' => 'required|string|max:50',
            'translations.*.content' => 'required|string',
            'translations.*.locale' => 'required|string|in:en,ar',
        ])->setAttributeNames([
            'translations.*.title' => __('words.title'),
            'translations.*.content' => __('words.content'),
            'translations.*.locale' => __('words.locale'),
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        $data = [
            'category_id' => $request->category,
        ];

        if ($request->hasFile('image')) {
            $file = $request->image;
            $filename = $this->uploadImage($file, 'posts');

            $data['image'] = $filename;
        }

        $post = Post::create($data);

        foreach($request->translations as $translation) {
            PostTranslation::create([
                'post_id' => $post->id,
                'title' => $translation['title'],
                'content' => $translation['content'],
                'locale' => $translation['locale'],
            ]);
        }

        return $this->returnSuccess(__('words.store_success'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::with(['category', 'translations'])->find($id);

        if (!$post) {
            return $this->returnError(__('words.not_found'), 404);
        }
    
        return $this->returnData($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->returnError(__('words.not_found'), 404);
        }

        $validator = Validator::make($request->all(), [
            'category' => 'sometimes|exists:categories,id',
            'image' => 'sometimes|file|max:1000|mimes:jpg,png',
            'translations' => 'required|array',
            'translations.*.title' => 'required|string|max:50',
            'translations.*.content' => 'required|string',
            'translations.*.locale' => 'required|string|in:en,ar',
        ])->setAttributeNames([
            'translations.*.title' => __('words.title'),
            'translations.*.content' => __('words.content'),
            'translations.*.locale' => __('words.locale'),
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        if ($request->has('category')) {
            $post->category_id = $request->category;
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($post->image, 'posts');
            $filename = $this->uploadImage($request->image, 'posts');
            $post->image = $filename;
        }

        $post->save();

        // Delete old translations and insert new ones
        $post->translations()->delete();

        foreach ($request->translations as $translation) {
            PostTranslation::create([
                'post_id' => $post->id,
                'title' => $translation['title'],
                'content' => $translation['content'],
                'locale' => $translation['locale'],
            ]);
        }

        return $this->returnSuccess(__('words.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->returnError(__('words.not_found'), 404);
        }

        // Delete image from public
        $this->deleteImage($post->image, 'posts');

        // Laravel will delete translations automatically if relationship is set with cascade
        $post->delete();

        return $this->returnSuccess(__('words.delete_success'));
    }
}
