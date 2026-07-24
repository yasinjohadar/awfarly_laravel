<?php
/*
 * This trait used to encrypt/decrypt the attributes in the database.
*/

namespace App\Http\Traits;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use \Illuminate\Http\Response as Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait APIResponseTrait
{
    //array_filter_recursive_by_default
    private $array_filter_recursive_by_default = false;

    //Set empty data to null
    private $setNullEmptyData = false;

    //Success codes var
    private $apiSuccessCodes = [
        Res::HTTP_OK,
        Res::HTTP_CREATED,
        Res::HTTP_ACCEPTED,
    ];

    //Exception codes var
    private $apiExceptionCodes = [
        Res::HTTP_CONFLICT,
        Res::HTTP_UNPROCESSABLE_ENTITY,
        Res::HTTP_BAD_REQUEST,
    ];

    /**
     * @param null|array|string $arg [type, filter_data, throw_exception, message, data]
     * @param null $data
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Res
     */
    public function apiResponse($arg = null, $data = null)
    {
        //Set attributes
        $type = isset($arg['type']) && !!$this->checkGetType($arg['type']) ? $arg['type'] : null;
        $filter_data = isset($arg['filter_data']) ? (bool)$arg['filter_data'] : false;
        $throw_exception = isset($arg['throw_exception']) ? (bool)$arg['throw_exception'] : true;
        $message = isset($arg['message']) ? $arg['message'] : null;

        //Handle type
        if (!is_null($type) || (!is_null($arg) && !is_null($data))) {
            //Set type
            if (!is_null($arg) && !is_array($arg) && !is_null($data)) {
                $type = $arg;
            }
        }

        //Handle data
        if (is_null($data) && isset($arg['data'])) {
            $data = $arg['data'];
        } elseif (
            is_null($data) &&
            !is_null($arg) &&
            (!is_array($arg) ||
                !(
                    isset($arg['type']) ||
                    isset($arg['filter_data']) ||
                    isset($arg['message'])
                )
            )
        ) {
            $data = $arg;
        }

        //Set status code
        if (is_null($type)) {
            $status_code = isset($arg['status_code']) ? $arg['status_code'] : Res::HTTP_OK;
        } else {
            $status_code = $this->setStatusCode($type);
        }

        //Filter data[]
        $data = ((is_array($data) && !!$filter_data) ? $this->array_filter_recursive($data) : $data);

        //Set data if sent as array
        if (is_array($data) && array_key_exists('data', $data) && sizeof($data) === 1) {
            $data = $data['data'];
        }

        $response = $this->apiRawResponse($data, $message, [], $status_code);

        //throw exceptions
        if (in_array($status_code, $this->apiExceptionCodes) && $throw_exception) {
            throw new HttpResponseException($response);
        }

        return $response;
    }

    /**
     * Paginate data
     * @param $pagination
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Res
     */
    public function apiPaginateResponse($pagination, $reverse_data = false)
    {
        //Set pagination data
        $isFirst = $pagination->onFirstPage();
        $isLast = $pagination->currentPage() === $pagination->lastPage();
        $isNext = $pagination->hasMorePages();
        $isPrevious = (($pagination->currentPage() - 1) > 0);

        $current = $pagination->currentPage();
        $last = $pagination->lastPage();
        $next = ($isNext ? $current + 1 : null);
        $previous = ($isPrevious ? $current - 1 : null);

        $data = $pagination->items();

        //reverse data
        if ($reverse_data) {
            $data = array_reverse($data);
        }

        //If no page found
        if ($current > $last) {
            return $this->apiResponse(['type' => 'not found']);
        }

        //Set extra
        $extra = [
            'pagination' => [
                'meta' => [
                    'page' => [
                        "current" => $current,
                        "first" => 1,
                        "last" => $last,
                        "next" => $next,
                        "previous" => $previous,

                        "per" => $pagination->perPage(),
                        "from" => $pagination->firstItem(),
                        "to" => $pagination->lastItem(),

                        "count" => $pagination->count(),
                        "total" => $pagination->total(),

                        "isFirst" => $isFirst,
                        "isLast" => $isLast,
                        "isNext" => $isNext,
                        "isPrevious" => $isPrevious,
                    ],
                ],
                "links" => [
                    "path" => $pagination->path(),
                    "first" => $pagination->url(1),
                    "next" => ($isNext ? $pagination->url($next) : null),
                    "previous" => ($isPrevious ? $pagination->url($previous) : null),
                    "last" => $pagination->url($last),
                ],
            ],
        ];

        return $this->apiRawResponse($data, null, $extra);
    }

    //Validate
    public function apiValidate($data, $roles, $messages = [], $customAttributes = [])
    {
        //Validate data
        $validator = Validator::make($data, $roles, $messages, $customAttributes);

        //If validation fails
        if ($validator->fails()) {
            $this->apiBadRequestResponse($validator->errors()->all());
        }

        return $validator;
    }

    //die and debug
    public function apiDD($data)
    {
        return $this->apiResponse([
            'type' => 'Exception',
            'throw_exception' => true,
            'message' => 'Die and debug',
            'data' => $data,
        ]);
    }

    //The exception response
    public function apiExceptionResponse($errors = null, $throw_exception = true)
    {
        //Set errors
        if (!is_null($errors)) {
            $errors = [
                'errors' => (is_array($errors) ? $errors : [$errors])
            ];
        }

        //Set default error message
        $default_error_message = 'Something went wrong please try again later!';

        return $this->apiResponse([
            'type' => 'Exception',
            'throw_exception' => $throw_exception,
            'message' => (is_null($errors) ? $default_error_message : null),
            'data' => $errors,
        ]);
    }

    //The bad request response
    public function apiBadRequestResponse($errors = null, $throw_exception = true)
    {
        //Set errors
        if (!is_null($errors)) {
            $errors = [
                'errors' => (is_array($errors) ? $errors : [$errors])
            ];
        }

        //Set default error message
        $default_error_message = 'Something went wrong please try again later!';

        return $this->apiResponse([
            'type' => 'Bad Request',
            'throw_exception' => $throw_exception,
            'message' => (is_null($errors) ? $default_error_message : null),
            'data' => $errors,
        ]);
    }

    //The main response
    private function apiRawResponse($data = null, $message = null, $extra = [], $status_code = Res::HTTP_OK)
    {
        //Filter data[]
        $data = (is_array($data) && $this->array_filter_recursive_by_default ? $this->array_filter_recursive($data) : $data);

        //Check if data is an empty array
        if ($this->setNullEmptyData) {
            if (
                (is_string($data) && !strlen($data)) ||
                (is_array($data) && !sizeof($data))
            ) {
                $data = null;
            }
        }

        $response = [
            'status' => $this->setStatus($status_code),
            'statusCode' => $status_code,
            'timestamp' => now()->timestamp,
            'message' => ($message == null ? $this->setMessage($status_code) : $message),
            'data' => $data,
        ];

        //Set extra response data
        if (!!sizeof($extra)) {
            $response = array_merge_recursive_distinct($response, $extra);
        }

        return response($response, $status_code);
    }

    //Check and get the type
    private function checkGetType($type = 'OK')
    {
        //If not string
        if (!is_string($type)) {
            return;
        }

        $type = trim($type);
        $type = strtolower($type);
        $type = str_replace('-', '', $type);
        $type = str_replace('.', '', $type);
        $type = str_replace(' ', '', $type);

        $types = [
            'ok',
            'created',
            'accepted',
            'notfound',
            'conflict',
            'badrequest',
            'exception',
            'unauthenticated',
        ];

        if (in_array($type, $types)) {
            return $type;
        }
    }

    //Set status from status_code
    private function setStatusCode($type = 'OK')
    {
        //Get type
        $type = $this->checkGetType($type);

        switch ($type) {
            case 'ok':
                $status_code = Res::HTTP_OK;
                break;
            case 'created':
                $status_code = Res::HTTP_CREATED;
                break;
            case 'accepted':
                $status_code = Res::HTTP_ACCEPTED;
                break;
            case 'notfound':
                $status_code = Res::HTTP_NOT_FOUND;
                break;
            case 'conflict':
                $status_code = Res::HTTP_CONFLICT;
                break;
            case 'badrequest':
                $status_code = Res::HTTP_BAD_REQUEST;
                break;
            case 'exception':
                $status_code = Res::HTTP_UNPROCESSABLE_ENTITY;
                break;
            case 'unauthenticated':
                $status_code = Res::HTTP_UNAUTHORIZED;
                break;
            default:
                $status_code = Res::HTTP_OK;
        }
        return $status_code;
    }

    //Set status from status_code
    private function setStatus($status_code = Res::HTTP_OK)
    {
        return (in_array($status_code, $this->apiSuccessCodes) ? true : false);
    }

    //Set message from status_code
    private function setMessage($status_code = Res::HTTP_OK)
    {
        switch ($status_code) {
            case Res::HTTP_OK:
                $message = 'OK';
                break;

            case Res::HTTP_CREATED:
                $message = 'Created';
                break;

            case Res::HTTP_ACCEPTED:
                $message = 'Accepted';
                break;

            case Res::HTTP_NOT_FOUND:
                $message = 'Not found!';
                break;

            case Res::HTTP_INTERNAL_SERVER_ERROR:
                $message = 'Internal server error!';
                break;

            case Res::HTTP_UNPROCESSABLE_ENTITY:
                $message = 'Unprocessable entity!';
                break;

            case Res::HTTP_UNAUTHORIZED:
                $message = 'Unauthorized!';
                break;

            case Res::HTTP_NO_CONTENT:
                $message = 'No content!';
                break;

            case Res::HTTP_BAD_REQUEST:
                $message = 'Bad Request!';
                break;

            case Res::HTTP_CONFLICT:
                $message = 'Conflict!';
                break;

            default:
                $message = 'Error';
        }

        return $message;
    }

    //Filter array and remove nulls
    private function array_filter_recursive($array, $callback = '')
    {
        foreach ($array as $key => & $value) {
            if (is_array($value)) {
                $value = $this->array_filter_recursive($value, $callback);
            } else {
                if (!empty($callback)) {
                    if (!$callback($value)) {
                        unset($array[$key]);
                    }
                } else {
                    if ((is_string($value) and !(bool)$value) or is_null($value)) {
                        unset($array[$key]);
                    }
                }
            }
        }
        unset($value);

        return $array;
    }
}
