<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Traits\ResponseTrait;
use App\Traits\UploadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    use ResponseTrait, UploadImage;

    public function store(Request $request)
    {
        if (Gate::denies('is_admin')) {
            return $this->returnError(__('words.Unauthorized'), 403);
        }
        
        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'translations.*.title' => 'required|string|max:255',
            'translations.*.content' => 'nullable|string',
            'translations.*.locale' => 'required|string',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:11',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ])->setAttributeNames([
            'translations.*.title' => __('words.title'),
            'translations.*.content' => __('words.content'),
            'translations.*.locale' => __('words.locale'),
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        $data = [
            'email' => $request->email,
            'phone' => $request->phone,
            'facebook' => $request->facebook,
            'instagram' => $request->instagram,
            'twitter' => $request->twitter,
            'linkedin' => $request->linkedin,
        ];

        if ($request->hasFile('logo')) {
            $filename = $this->uploadImage($request->logo, 'settings');
            $data['logo'] = $filename;
        }

        if ($request->hasFile('favicon')) {
            $filename = $this->uploadImage($request->favicon, 'settings');
            $data['favicon'] = $filename;
        }

        $existingSetting = Setting::find(1);
        if ($existingSetting) {
            $existingSetting->update($data);
        } else {
            $existingSetting = Setting::create($data);
        }

        $existingSetting->translations()->delete();

        foreach ($request->translations as $translation) {
            $existingSetting->translations()->create([
                'title' => $translation['title'],
                'content' => $translation['content'],
                'locale' => $translation['locale'],
            ]);
        }

        return $this->returnSuccess(__('words.save_success'));
        
    }
}
