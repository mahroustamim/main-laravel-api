<?php

namespace App\Http\Controllers;

use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class LocalizatoinController extends Controller
{
    use ResponseTrait;

    public function setlocale(Request $request) {

        $locale = $request->input('locale', 'en'); 

       if (!in_array($locale, ['en', 'ar'])) {
            return $this->returnError(__('words.invalid_locale'));
        }

       // Secure session handling
        session()->put([
            'locale' => $locale,
            'locale_last_updated' => now()->timestamp
        ]);
        
        app()->setLocale($locale);

        return $this->returnData([
            'locale' => app()->getLocale(),
            'expires' => now()->addMinutes(config('session.lifetime'))->format('c')
        ]);

   }

}
