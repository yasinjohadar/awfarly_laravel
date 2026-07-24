<?php

namespace App\Http\Controllers\Admins\Countries\Cities;

use App\Http\Controllers\Controller;
use App\Models\Countries\Cities\City;
use App\Models\Countries\Country;
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
     * Display a listing of the resource.
     *
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
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|void
     */
    public function create()
    {
        if (!Auth::guard('admin')->user()->can('cities.inquiry')) {
            return abort(404);
        }
        return view('admin.pages.countries.cities.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    /*public function getCitiesByCountryCode(Request $request): JsonResponse
    {
        $country_column = Auth::guard('admin')->user()->language_code;
        if ($country_column === 'ar') {
            $name_column = 'name_ar';
        } else {
            $name_column = 'name_en';
        }
        if ($request->has('country_code') && !empty($request->get('country_code'))) {
            $country_code = $request->get('country_code');
        } else {
            $country = Country::first();
            $country_code = $country->code;
        }
        $data = City::select("id", "$name_column");

        if ($request->has('search')) {
            $search = $request->get('search');
            $data = $data->where($name_column, 'LIKE', "%$search%");
        }
        $data = $data->where('country_code', $country_code)
            ->get()
            ->map(function ($city) use ($name_column){
                return [
                    'id' => $city->id,
                    'text' => $city->{$name_column}
                ];
            });
        return response()->json($data);
    }*/

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCitiesByCountryCode(Request $request): JsonResponse
    {
        $country_column = Auth::guard('admin')->user()->language_code;
        if ($country_column === 'ar') {
            $name_column = 'name_ar';
        } else {
            $name_column = 'name_en';
        }
        if ($request->has('country_code') && !empty($request->get('country_code'))) {
            $country_code = $request->get('country_code');
        } else {
            $country = Country::first();
            $country_code = $country->code;
        }
        $data = City::select("id", "$name_column")
            ->where('country_code', $country_code)
            ->get()
            ->map(function ($city) use ($name_column){
                return [
                    'id' => $city->id,
                    'text' => $city->{$name_column}
                ];
            });
        return response()->json($data);
    }
}
