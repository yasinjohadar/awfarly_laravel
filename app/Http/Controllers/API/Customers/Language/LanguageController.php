<?php

namespace App\Http\Controllers\API\Customers\Language;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //Set data
        $data = $request->only([
            'language',
        ]);

        //Validate course id
        $this->apiValidate($data, [
            'language' => ['required', 'in:ar,en'],
        ]);

        auth('customer-api')->user()->update(['notify_language' => $request->language]);

        return $this->apiResponse(['message' => 'language updated to: ' . $request->language, 'data' => ['language' => auth('customer-api')->user()->notify_language]]);
    }
}
