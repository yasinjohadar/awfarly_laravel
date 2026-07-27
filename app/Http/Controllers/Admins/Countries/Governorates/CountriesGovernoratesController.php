<?php

namespace App\Http\Controllers\Admins\Countries\Governorates;

use App\Http\Controllers\Controller;
use App\Models\Countries\Country;
use App\Models\Countries\Governorates\Governorate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CountriesGovernoratesController extends Controller
{
    /**
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('governorates.inquiry')) {
            return abort(404);
        }

        return view('admin.pages.countries.governorates.index');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function create()
    {
        if (!Auth::guard('admin')->user()->can('governorates.add')) {
            return abort(404);
        }

        return view('admin.pages.countries.governorates.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getGovernoratesByCountryCode(Request $request): JsonResponse
    {
        $countryColumn = Auth::guard('admin')->user()->language_code;
        $nameColumn = $countryColumn === 'ar' ? 'name_ar' : 'name_en';

        if ($request->has('country_code') && !empty($request->get('country_code'))) {
            $countryCode = $request->get('country_code');
        } else {
            $country = Country::first();
            $countryCode = $country->code;
        }

        $data = Governorate::select('id', $nameColumn)
            ->where('country_code', $countryCode);

        if ($request->has('search')) {
            $search = $request->get('search');
            $data = $data->where($nameColumn, 'LIKE', "%$search%");
        }

        $data = $data->orderBy('order')
            ->get()
            ->map(function ($governorate) use ($nameColumn) {
                return [
                    'id' => $governorate->id,
                    'text' => $governorate->{$nameColumn},
                ];
            });

        return response()->json($data);
    }
}
