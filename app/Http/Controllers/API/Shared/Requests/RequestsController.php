<?php

namespace App\Http\Controllers\API\Shared\Requests;

use App\Helpers\Filter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Shared\Requests\ContactUsResource;
use App\Models\Requests\ContactForms;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RequestsController extends Controller
{
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function sendContactForm(Request $request)
    {
        //get data
        $data = $request->all();

        //validate data
        $this->apiValidate($data, [
            'type' => ['required', 'in:Enquiry,Complaint,Suggestion,Payments,Technical support,In-app advertising,Report a problem'],
            'name' => ['required'],
            'mobile' => ['required'],
            'whatsappMobile' => ['nullable'],
            'email' => ['nullable', 'email'],
            'message' => ['required'],
        ]);

        $data = [
            'type' => Filter::RemoveHtml($data['type']),
            'name' => Filter::RemoveHtml($data['name']),
            'mobile' => Filter::RemoveHtml($data['mobile']),
            'whatsappMobile' => Filter::RemoveHtml($data['whatsappMobile']),
            'email' => Filter::RemoveHtml($data['email']),
            'message' => nl2br(Filter::RemoveHtml($data['message'])),
        ];

        DB::beginTransaction();
        try {
            $data = ContactForms::create($data);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->apiExceptionResponse(__('api/shared/requests/requests.something-wrong'));
        }
        DB::commit();

        return $this->apiResponse([
            'message' => __('api/shared/requests/requests.message-sent'),
            'data' => ContactUsResource::make($data)
        ]);
    }
}
