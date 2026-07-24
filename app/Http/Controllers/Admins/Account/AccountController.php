<?php

namespace App\Http\Controllers\Admins\Account;

use App\Http\Controllers\Controller;
use App\Models\Languages\Language;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AccountController extends Controller
{

    /**
     * Show the form for editing the specified resource.
     *
     * @return Application|Factory|View
     */
    public function edit()
    {
        return view('admin.pages.account.edit');
    }

    /**
     * @param $language
     * @return RedirectResponse
     */
    public function changeLanguage($language): RedirectResponse
    {
        Language::where('code', $language)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            Auth::guard('admin')->user()
                ->update([
                    'language_code' => $language,
                ]);
            Session::put('userLocale', $language);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
        DB::commit();
        return redirect()->back();
    }
}
