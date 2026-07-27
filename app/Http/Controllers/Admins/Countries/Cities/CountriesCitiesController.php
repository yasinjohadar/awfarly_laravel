<?php

namespace App\Http\Controllers\Admins\Countries\Cities;

use App\Http\Controllers\Controller;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Governorates\Governorate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CountriesCitiesController extends Controller
{
    /**
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('cities.inquiry')) {
            return abort(404);
        }

        return view('admin.pages.countries.cities.index');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function create()
    {
        if (!Auth::guard('admin')->user()->can('cities.add')) {
            return abort(404);
        }

        return view('admin.pages.countries.cities.create');
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
    public function getCitiesByGovernorateId(Request $request): JsonResponse
    {
        $countryColumn = Auth::guard('admin')->user()->language_code;
        $nameColumn = $countryColumn === 'ar' ? 'name_ar' : 'name_en';

        $governorateId = $request->get('governorate_id');
        if (empty($governorateId)) {
            return response()->json([]);
        }

        $data = City::select('id', $nameColumn)
            ->where('governorate_id', $governorateId);

        if ($request->has('search')) {
            $search = $request->get('search');
            $data = $data->where($nameColumn, 'LIKE', "%$search%");
        }

        $data = $data->orderBy('order')
            ->get()
            ->map(function ($city) use ($nameColumn) {
                return [
                    'id' => $city->id,
                    'text' => $city->{$nameColumn},
                ];
            });

        return response()->json($data);
    }
}
