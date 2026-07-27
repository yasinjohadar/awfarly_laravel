<?php

namespace App\Http\Controllers\Admins\Advertisements;

use App\Helpers\Admins\AdminLogs;
use App\Helpers\Files;
use App\Http\Controllers\Controller;
use App\Models\Advertisements\Advertisement;
use App\Models\Categories\Category;
use App\Models\Countries\Country;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Image\Image;

class AdvertisementsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|void
     */
    public function index()
    {
        if (!Auth::guard('admin')->user()->can('advertisements.inquiry')) {
            return abort(404);
        }
        //Log Action
        AdminLogs::log('inquiry', 'Advertisements');

        return view('admin.pages.advertisements.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|void
     */
    public function create()
    {
        if (!Auth::guard('admin')->user()->can('advertisements.add')) {
            return abort(404);
        }

        //get language column to show
        $name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        //Get the groups with permissions within it
        $countries = Country::with('governorates')
            ->orderBy('order')
            ->get()
            ->map(function ($country) use ($name_column) {
                return [
                    'CountryName' => $country->{$name_column},
                    'CountryData' => $country->governorates()
                        ->orderBy('order')
                        ->get()
                        ->map(function ($governorate) use ($name_column) {
                            return [
                                'city' => $governorate->{$name_column},
                                'value' => $governorate->id,
                            ];
                        }),
                ];
            });

        //Get the groups with permissions within it
        $categories = Category::whereNull('parent_category_id')
            ->orderBy('order')
            ->get()
            ->map(function ($category) use ($name_column) {
                return [
                    'CategoryName' => $category->{$name_column},
                    'CategoryData' => ($category->has('childCategories') && count($category->childCategories) > 0) ?
                        $category->childCategories()
                            ->orderBy('order')
                            ->get()
                            ->map(function ($children) use ($name_column) {
                                return [
                                    'category' => $children->{$name_column},
                                    'value' => $children->id,
                                ];
                            }) : [
                            [
                                'category' => $category->{$name_column},
                                'value' => $category->id,
                            ],
                        ],
                ];
            });


        return view('admin.pages.advertisements.create', [
            'countries' => $countries,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse|void
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        if (!Auth::guard('admin')->user()->can('advertisements.add')) {
            return abort(404);
        }

        $this->validate($request, [
            'type' => ['required', 'in:any,website,mobile'],
            'users' => ['required', 'in:any,advertisers,customers'],
            'is_active' => ['required', 'boolean'],
            'advertiser_name' => ['nullable'],
            'advertiser_url' => ['nullable', 'url'],
            'advertiser_image' => ['nullable', 'image'],
            'content' => ['nullable'],
            'media' => ['required', 'array'],
            'media.*' => ['file', 'mimes:jpg,jpeg,png,bmp,gif,mp4,mov,ogg,qt,avi,wmv,flv,ts,3gp', 'max:100000'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['exists:governorates,id'],
            'cities' => ['nullable', 'array'],
            'cities.*' => ['exists:cities,id'],
            'starts_at' => ['nullable'],
            'ends_at' => ['nullable', 'after:starts_at'],
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('advertiser_image')) {
                $image = $request->file('advertiser_image')->storeAs('uploads/advertisements/avatars', md5($request->file('advertiser_image')->getClientOriginalName()));
                /*$image = Files::uploadRequestImage($request, 'uploads/advertisements/avatars', 'advertiser_image', true, true);*/
            } else {
                $image = null;
            }

            if ($request->has('categories')) {
                /*$categories = json_encode($request->get('categories'));*/
                $categories = $request->get('categories');
            } else {
                $categories = null;
            }


            if ($request->has('countries')) {
                $governorates = $request->get('countries');
            } else {
                $governorates = null;
            }

            $cities = $request->has('cities') ? $request->get('cities') : null;

            $advertisement = Advertisement::create([
                'type' => $request->get('type'),
                'users' => $request->get('users'),
                'advertiser_name' => $request->get('advertiser_name'),
                'advertiser_url' => $request->get('advertiser_url'),
                'advertiser_image' => $image,
                'content' => $request->has('content') ? $request->get('content') : null,
                'categories' => $categories,
                'governorates' => $governorates,
                'cities' => $cities,
                'starts_at' => $request->get('starts_at') ?? Carbon::now(),
                'ends_at' => $request->get('ends_at') ?? null,
                'is_active' => (bool)$request->get('is_active'),
            ]);

            if ($request->hasFile('media.*')) {
                foreach ($request->file('media') as $index => $media) {
                    $mime_type = $media->getMimeType();
                    if (strstr($mime_type, "video/")) {
                        $file_width = null;
                        $file_height = null;
                    } else if (strstr($mime_type, 'image/')) {
                        $file_width = Image::load($media)->getWidth();
                        $file_height = Image::load($media)->getHeight();
                        $temp_image = Files::uploadTempImage($request, 'uploads/media', "media.{$index}");
                        $media = storage_path("app/$temp_image");
                    } else {
                        $file_width = null;
                        $file_height = null;
                    }
                    $advertisement->addMedia($media)
                        ->withCustomProperties(['width' => $file_width, 'height' => $file_height])
                        ->toMediaCollection('advertisements');
                }
            }

            $advertisement->post()
                ->create();

            //Log role
            AdminLogs::log('add', 'advertisements', [
                'advertisement' => $advertisement
            ], "Add: new advertisement");

        } catch (Exception $e) {
            DB::rollBack();
            /*__('pages/advertisements/create.content.callbacks.error')*/
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
        DB::commit();
        return redirect()->back()->with(['success' => __('pages/advertisements/create.content.callbacks.success')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Application|Factory|View|void
     */
    public function edit($id)
    {
        if (!Auth::guard('admin')->user()->can('advertisements.edit')) {
            return abort(404);
        }

        $advertisement = Advertisement::findOrFail($id);

        //get language column to show
        $name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';

        //Get the groups with permissions within it
        $countries = Country::with('governorates')
            ->orderBy('order')
            ->get()
            ->map(function ($country) use ($name_column) {
                return [
                    'CountryName' => $country->{$name_column},
                    'CountryData' => $country->governorates()
                        ->orderBy('order')
                        ->get()
                        ->map(function ($governorate) use ($name_column) {
                            return [
                                'city' => $governorate->{$name_column},
                                'value' => $governorate->id,
                            ];
                        }),
                ];
            });

        //Get the groups with permissions within it
        $categories = Category::whereNull('parent_category_id')
            ->orderBy('order')
            ->get()
            ->map(function ($category) use ($name_column) {
                return [
                    'CategoryName' => $category->{$name_column},
                    'CategoryData' => ($category->has('childCategories') && count($category->childCategories) > 0) ?
                        $category->childCategories()
                            ->orderBy('order')
                            ->get()
                            ->map(function ($children) use ($name_column) {
                                return [
                                    'category' => $children->{$name_column},
                                    'value' => $children->id,
                                ];
                            }) : [
                            [
                                'category' => $category->{$name_column},
                                'value' => $category->id,
                            ],
                        ],
                ];
            });

        return view('admin.pages.advertisements.edit', [
            'advertisement' => $advertisement,
            'categories' => $categories,
            'countries' => $countries,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|void
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        if (!Auth::guard('admin')->user()->can('advertisements.edit')) {
            return abort(404);
        }

        $this->validate($request, [
            'type' => ['required', 'in:any,website,mobile'],
            'users' => ['required', 'in:any,advertisers,customers'],
            'is_active' => ['required', 'boolean'],
            'advertiser_name' => ['nullable'],
            'advertiser_url' => ['nullable', 'url'],
            'advertiser_image' => ['nullable', 'image'],
            'content' => ['nullable'],
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'mimes:jpg,jpeg,png,bmp,gif,mp4,mov,ogg,qt,avi,wmv,flv,ts,3gp', 'max:100000'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['exists:governorates,id'],
            'cities' => ['nullable', 'array'],
            'cities.*' => ['exists:cities,id'],
            'starts_at' => ['nullable'],
            'ends_at' => ['nullable', 'after:starts_at'],
        ]);

        $advertisement = Advertisement::findOrFail($id);

        $data = $request->all();
        DB::beginTransaction();
        try {
            if ($request->hasFile('advertiser_image')) {
                if ($advertisement->image) {
                    Files::deleteS3File($advertisement->image);
                }
                $data['advertiser_image'] = $request->file('advertiser_image')->storeAs('uploads/advertisements/avatars', md5($request->file('advertiser_image')->getClientOriginalName()));
                /*$data['advertiser_image'] = Files::uploadRequestImage($request, 'uploads/advertisements/avatars', 'advertiser_image', true, true);*/
            } else {
                $data['advertiser_image'] = $advertisement->image;
            }

            if ($request->has('categories')) {
                $data['categories'] = $request->get('categories');
            } else {
                $data['categories'] = null;
            }


            if ($request->has('countries')) {
                $data['governorates'] = $request->get('countries');
            } else {
                $data['governorates'] = null;
            }

            $data['cities'] = $request->has('cities') ? $request->get('cities') : null;

            if ($request->hasFile('media.*')) {
                foreach ($advertisement->getMedia('advertisements') as $media) {
                    $media->delete();
                }

                foreach ($request->file('media') as $index => $media) {
                    $mime_type = $media->getMimeType();
                    if (strstr($mime_type, "video/")) {
                        $file_width = null;
                        $file_height = null;
                    } else if (strstr($mime_type, 'image/')) {
                        $file_width = Image::load($media)->getWidth();
                        $file_height = Image::load($media)->getHeight();
                        $temp_image = Files::uploadTempImage($request, 'uploads/media', "media.{$index}");
                        $media = storage_path("app/$temp_image");
                    } else {
                        $file_width = null;
                        $file_height = null;
                    }
                    $advertisement->addMedia($media)->withCustomProperties(['width' => $file_width, 'height' => $file_height])->toMediaCollection('advertisements');
                }
            }

            //Log role
            AdminLogs::log('edit', 'advertisements', [
                'old' => $advertisement,
                'data' => $data,
            ], "Edit: advertisement #$id");


            $advertisement->update($data);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => __('pages/advertisements/edit.content.callbacks.error')]);
        }
        DB::commit();
        return redirect()->back()->with(['success' => __('pages/advertisements/edit.content.callbacks.success')]);
    }

    /**
     * @return Application|Factory|View|void
     */
    public function getSideAdvertisements()
    {
        if (!Auth::guard('admin')->user()->can('advertisements.inquiry')) {
            return abort(404);
        }
        //Log Action
        AdminLogs::log('inquiry', 'Side Advertisements');

        return view('admin.pages.advertisements.side.index');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function createSideAdvertisements()
    {
        if (!Auth::guard('admin')->user()->can('advertisements.add')) {
            return abort(404);
        }

        return view('admin.pages.advertisements.side.create');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function getSliderAdvertisements()
    {
        if (!Auth::guard('admin')->user()->can('advertisements.inquiry')) {
            return abort(404);
        }
        //Log Action
        AdminLogs::log('inquiry', 'Side Advertisements');

        return view('admin.pages.advertisements.slider.index');
    }

    /**
     * @return Application|Factory|View|void
     */
    public function createSliderAdvertisements()
    {
        if (!Auth::guard('admin')->user()->can('advertisements.add')) {
            return abort(404);
        }

        return view('admin.pages.advertisements.slider.create');
    }
}
