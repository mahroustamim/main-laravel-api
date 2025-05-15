<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use ResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with(['translation'])->get();
        return $this->returnData($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validate request
        $validator = Validator::make($request->all(), [
            'translations.*.name' => 'required|string',
            'translations.*.locale' => 'required|string|in:en,ar',
        ])->setAttributeNames([
            'translations.*.name' => __('words.category_name'),
            'translations.*.locale' => __('words.locale'),
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        //save data
        $user = Auth::user();
        $category = Category::create([
            'user_id' => $user->id,
        ]);
        foreach ($request->translations as $translation) {
            CategoryTranslation::create([
                'name' => $translation['name'],
                'locale' => $translation['locale'],
                'category_id' => $category->id,
            ]);
        }

        return $this->returnSuccess(__('words.store_success'));
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //validate request
        $validator = Validator::make($request->all(), [
            'translations.*.name' => 'required|string',
            'translations.*.locale' => 'required|string|in:en,ar',
        ])->setAttributeNames([
            'translations.*.name' => __('words.category_name'),
            'translations.*.locale' => __('words.category_locale'),
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        
        foreach ($request->translations as $translation) {
            $categoryTranslation = CategoryTranslation::where('category_id', $id)
                ->where('locale', $translation['locale'])->first();
            
            if ($categoryTranslation) {
                $categoryTranslation->update([
                    'name' => $translation['name'],
                ]);
            } else {
                CategoryTranslation::create([
                    'name' => $translation['name'],
                    'locale' => $translation['locale'],
                    'category_id' => $id
                ]);
            }
        }

        return $this->returnSuccess(__('words.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Category::find($id)->delete();
        return $this->returnSuccess(__('words.delete_success'));
    }
}
